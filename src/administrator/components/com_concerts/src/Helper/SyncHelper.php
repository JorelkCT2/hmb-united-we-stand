<?php
namespace Joomla\Component\Concerts\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

/**
 * Fuente unica de eventos (#__concerts): importa de ICS, JSON-LD (schema.org), Ticketmaster y CSV,
 * detecta duplicados, geocodifica ciudades (OpenStreetMap/Nominatim con cache) y deja los eventos
 * nuevos para revisar. No usa IA: todo es lectura de datos estructurados.
 */
final class SyncHelper
{
    public const PAISES = [
        'ES' => 'España', 'DE' => 'Alemania', 'AT' => 'Austria', 'BE' => 'Bélgica', 'BG' => 'Bulgaria', 'HR' => 'Croacia',
        'CZ' => 'República Checa', 'DK' => 'Dinamarca', 'SK' => 'Eslovaquia', 'SI' => 'Eslovenia', 'EE' => 'Estonia',
        'FI' => 'Finlandia', 'FR' => 'Francia', 'GR' => 'Grecia', 'HU' => 'Hungría', 'IE' => 'Irlanda', 'IS' => 'Islandia',
        'IT' => 'Italia', 'LV' => 'Letonia', 'LT' => 'Lituania', 'LU' => 'Luxemburgo', 'MT' => 'Malta', 'NO' => 'Noruega',
        'NL' => 'Países Bajos', 'PL' => 'Polonia', 'PT' => 'Portugal', 'GB' => 'Reino Unido', 'RO' => 'Rumanía',
        'SE' => 'Suecia', 'CH' => 'Suiza', 'US' => 'Estados Unidos', 'CA' => 'Canadá', 'MX' => 'México', 'AR' => 'Argentina',
        'BR' => 'Brasil', 'CL' => 'Chile', 'CY' => 'Chipre',
    ];

    public const TIPOS_FUENTE = [
        'ticketmaster' => 'Ticketmaster (API oficial)',
        'jsonld'       => 'Web con datos estructurados (JSON-LD / schema.org)',
        'ics'          => 'Calendario iCal (.ics)',
        'csvurl'       => 'CSV publicado en una dirección web',
        'rss'          => 'RSS (sigue cada enlace y lee sus datos)',
    ];

    // ── utilidades ───────────────────────────────────────────────────────

    public static function db()
    {
        return Factory::getContainer()->get('DatabaseDriver');
    }

    public static function config()
    {
        return ComponentHelper::getParams('com_concerts');
    }

    public static function norm(string $s): string
    {
        $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', strtolower($s))));
    }

    public static function slug(string $s): string
    {
        return str_replace(' ', '-', self::norm($s));
    }

    public static function claveDedupe(string $titulo, string $fecha, string $ciudad): string
    {
        return substr(self::slug($titulo) . '|' . $fecha . '|' . self::slug($ciudad), 0, 190);
    }

    /** Parecido entre dos titulos (0..1): solapamiento de palabras o inclusion. */
    public static function parecido(string $a, string $b): float
    {
        $a = self::norm($a);
        $b = self::norm($b);

        if ($a === '' || $b === '') {
            return 0.0;
        }

        if ($a === $b) {
            return 1.0;
        }

        if ((strlen($a) >= 5 && strpos($b, $a) !== false) || (strlen($b) >= 5 && strpos($a, $b) !== false)) {
            return 0.9;
        }

        $pa = array_filter(explode(' ', $a), fn($w) => strlen($w) > 2);
        $pb = array_filter(explode(' ', $b), fn($w) => strlen($w) > 2);

        if (!$pa || !$pb) {
            return 0.0;
        }

        return count(array_intersect($pa, $pb)) / min(count($pa), count($pb));
    }

    /** @return array{code:int,body:string,err:string} */
    public static function http(string $url, int $timeout = 25, array $cabeceras = []): array
    {
        $out = ['code' => 0, 'body' => '', 'err' => ''];

        if (!preg_match('#^https?://#i', $url)) {
            $out['err'] = 'Dirección no válida';

            return $out;
        }

        $host = (string) parse_url($url, PHP_URL_HOST);

        if ($host === '' || strcasecmp($host, 'localhost') === 0
            || (filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
            $out['err'] = 'Dirección no permitida';

            return $out;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_ENCODING       => '',
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; HMB-Agenda/1.0; +https://www.heavymetalbrothers.com)',
            CURLOPT_HTTPHEADER     => array_merge(['Accept: text/html,application/json,text/calendar,application/xml,*/*;q=0.8', 'Accept-Language: es,en;q=0.8'], $cabeceras),
        ]);
        $body = curl_exec($ch);
        $out['code'] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $out['err']  = curl_errno($ch) ? curl_error($ch) : '';
        curl_close($ch);
        $out['body'] = is_string($body) ? substr($body, 0, 6000000) : '';

        return $out;
    }

    private static function fecha(?string $s): ?string
    {
        $s = trim((string) $s);

        if ($s === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'Ymd'] as $f) {
            $d = \DateTime::createFromFormat($f, substr($s, 0, strlen(date($f))));

            if ($d && $d->format($f) === substr($s, 0, strlen($d->format($f)))) {
                return $d->format('Y-m-d');
            }
        }

        $t = strtotime($s);

        return $t ? date('Y-m-d', $t) : null;
    }

    private static function hora(?string $s): ?string
    {
        if ($s && preg_match('/(\d{1,2}):(\d{2})/', $s, $m) && (int) $m[1] < 24 && !($m[1] === '00' && $m[2] === '00')) {
            return sprintf('%02d:%s', (int) $m[1], $m[2]);
        }

        return null;
    }

    // ── lectores ─────────────────────────────────────────────────────────

    /** @return array<int,array<string,mixed>> */
    public static function leerIcs(string $txt): array
    {
        $txt  = preg_replace("/\r?\n[ \t]/", '', str_replace("\r\n", "\n", $txt));
        $out  = [];

        if (!preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $txt, $bloques)) {
            return [];
        }

        foreach ($bloques[1] as $b) {
            $campo = static function (string $k) use ($b): string {
                return preg_match('/^' . $k . '(?:;[^:\n]*)?:(.*)$/mi', $b, $m)
                    ? trim(str_replace(['\\,', '\\;', '\\n', '\\N'], [',', ';', "\n", "\n"], $m[1])) : '';
            };

            $ini = $campo('DTSTART');
            $fin = $campo('DTEND');
            $f1  = self::fecha(substr($ini, 0, 8));

            if (!$f1) {
                continue;
            }

            $f2 = $fin ? self::fecha(substr($fin, 0, 8)) : null;

            // en eventos de dia completo DTEND es exclusivo
            if ($f2 && strlen($ini) === 8 && $f2 > $f1) {
                $f2 = date('Y-m-d', strtotime($f2 . ' -1 day'));
            }

            $loc    = $campo('LOCATION');
            $partes = array_values(array_filter(array_map('trim', explode(',', $loc))));

            $out[] = [
                'title'       => $campo('SUMMARY'),
                'date_start'  => $f1,
                'date_end'    => ($f2 && $f2 > $f1) ? $f2 : null,
                'time'        => strlen($ini) > 8 ? self::hora(substr($ini, 9, 2) . ':' . substr($ini, 11, 2)) : null,
                'venue'       => $partes[0] ?? '',
                'city'        => $partes[1] ?? '',
                'country_code' => '',
                'url'         => $campo('URL'),
                'description' => $campo('DESCRIPTION'),
                'ref'         => 'ics:' . ($campo('UID') ?: md5($campo('SUMMARY') . $f1)),
            ];
        }

        return $out;
    }

    /** @return array<int,array<string,mixed>> */
    public static function leerJsonLd(string $html, string $base = ''): array
    {
        $out = [];

        if (!preg_match_all('#<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $bloques)) {
            return [];
        }

        $recoger = function ($nodo) use (&$recoger, &$out, $base): void {
            if (!is_array($nodo)) {
                return;
            }

            if (isset($nodo['@graph'])) {
                $recoger($nodo['@graph']);
            }

            if (isset($nodo['itemListElement'])) {
                foreach ((array) $nodo['itemListElement'] as $it) {
                    $recoger($it['item'] ?? $it);
                }
            }

            if (array_keys($nodo) === range(0, count($nodo) - 1)) {
                foreach ($nodo as $n) {
                    $recoger($n);
                }

                return;
            }

            $tipos = (array) ($nodo['@type'] ?? []);

            foreach ($tipos as $t) {
                if (is_string($t) && preg_match('/Event$|Festival$/i', $t)) {
                    $e = self::eventoDeJsonLd($nodo, $base);

                    if ($e) {
                        $out[] = $e;
                    }

                    break;
                }
            }
        };

        foreach ($bloques[1] as $json) {
            $d = json_decode(html_entity_decode(trim($json), ENT_QUOTES, 'UTF-8'), true);

            if ($d !== null) {
                $recoger($d);
            }
        }

        return $out;
    }

    private static function eventoDeJsonLd(array $n, string $base): ?array
    {
        $f1 = self::fecha((string) ($n['startDate'] ?? ''));

        if (!$f1 || empty($n['name'])) {
            return null;
        }

        $f2   = self::fecha((string) ($n['endDate'] ?? ''));
        $loc  = $n['location'] ?? [];
        $loc  = isset($loc[0]) ? $loc[0] : $loc;
        $dir  = is_array($loc['address'] ?? null) ? $loc['address'] : [];
        $geo  = is_array($loc['geo'] ?? null) ? $loc['geo'] : [];
        $pais = (string) ($dir['addressCountry'] ?? '');
        $pais = is_array($dir['addressCountry'] ?? null) ? (string) ($dir['addressCountry']['name'] ?? '') : $pais;
        $perf = [];

        foreach ((array) ($n['performer'] ?? []) as $p) {
            $perf[] = is_array($p) ? (string) ($p['name'] ?? '') : (string) $p;
        }

        $perf  = array_values(array_filter(array_map('trim', $perf)));
        $img   = $n['image'] ?? '';
        $img   = is_array($img) ? (is_string($img[0] ?? null) ? $img[0] : ($img['url'] ?? ($img[0]['url'] ?? ''))) : $img;
        $oferta = $n['offers'] ?? [];
        $oferta = isset($oferta[0]) ? $oferta[0] : $oferta;
        $url   = (string) ($n['url'] ?? '');

        if ($url !== '' && $base !== '' && !preg_match('#^https?://#', $url)) {
            $url = rtrim($base, '/') . '/' . ltrim($url, '/');
        }

        return [
            'title'        => (string) $n['name'],
            'bands'        => $perf,
            'date_start'   => $f1,
            'date_end'     => ($f2 && $f2 > $f1) ? $f2 : null,
            'time'         => self::hora(substr((string) $n['startDate'], 11, 5)),
            'venue'        => (string) ($loc['name'] ?? ''),
            'city'         => (string) ($dir['addressLocality'] ?? ''),
            'province'     => (string) ($dir['addressRegion'] ?? ''),
            'country_code' => strlen($pais) === 2 ? strtoupper($pais) : '',
            'address'      => (string) ($dir['streetAddress'] ?? ''),
            'lat'          => isset($geo['latitude']) ? (float) $geo['latitude'] : null,
            'lng'          => isset($geo['longitude']) ? (float) $geo['longitude'] : null,
            'poster'       => is_string($img) ? $img : '',
            'ticket_url'   => (string) ($oferta['url'] ?? ''),
            'url'          => $url,
            'description'  => trim(strip_tags((string) ($n['description'] ?? ''))),
            'cancelado'    => stripos((string) ($n['eventStatus'] ?? ''), 'Cancel') !== false,
            'tipo'         => preg_match('/Festival/i', (string) json_encode($n['@type'] ?? '')) ? 'festival' : 'concert',
            'ref'          => 'ld:' . ($url ?: md5($n['name'] . $f1)),
        ];
    }

    /**
     * Ticketmaster Discovery API (clave gratuita en developer.ticketmaster.com).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function leerTicketmaster(array $p, ?string &$error = null): array
    {
        $key = trim((string) ($p['apikey'] ?? ''));

        if ($key === '') {
            $error = 'Falta la clave de Ticketmaster (Opciones del componente).';

            return [];
        }

        $out    = [];
        $meses  = max(1, min(24, (int) ($p['meses'] ?? 12)));
        $params = [
            'apikey'             => $key,
            'countryCode'        => strtoupper((string) ($p['pais'] ?? 'ES')),
            'classificationName' => (string) (($p['generos'] ?? '') ?: 'metal,hard rock'),
            'size'               => 200,
            'sort'               => 'date,asc',
            'startDateTime'      => gmdate('Y-m-d\TH:i:s\Z'),
            'endDateTime'        => gmdate('Y-m-d\TH:i:s\Z', strtotime("+$meses months")),
        ];

        if (!empty($p['keyword'])) {
            $params['keyword'] = (string) $p['keyword'];
        }

        for ($pag = 0; $pag < 5; $pag++) {
            $params['page'] = $pag;
            $r = self::http('https://app.ticketmaster.com/discovery/v2/events.json?' . http_build_query($params), 30);
            $d = json_decode($r['body'], true);

            if ($r['code'] !== 200 || !is_array($d)) {
                $error = 'Ticketmaster respondió HTTP ' . $r['code'] . ($r['err'] ? ' (' . $r['err'] . ')' : '') . '.';
                break;
            }

            foreach ($d['_embedded']['events'] ?? [] as $e) {
                $ini   = $e['dates']['start']['localDate'] ?? null;
                $venue = $e['_embedded']['venues'][0] ?? [];
                $att   = array_values(array_filter(array_map(fn($a) => $a['name'] ?? '', $e['_embedded']['attractions'] ?? [])));
                $foto  = '';
                $ancho = 0;

                foreach ($e['images'] ?? [] as $im) {
                    if (($im['width'] ?? 0) > $ancho && ($im['width'] ?? 0) <= 1400) {
                        $ancho = (int) $im['width'];
                        $foto  = (string) ($im['url'] ?? '');
                    }
                }

                $precio = '';

                if (!empty($e['priceRanges'][0])) {
                    $pr     = $e['priceRanges'][0];
                    $precio = isset($pr['min']) ? number_format((float) $pr['min'], 0, ',', '.') . ' ' . ($pr['currency'] ?? '') : '';
                }

                if (!$ini) {
                    continue;
                }

                $out[] = [
                    'title'        => (string) ($att[0] ?? $e['name']),
                    'bands'        => $att,
                    'date_start'   => $ini,
                    'time'         => self::hora($e['dates']['start']['localTime'] ?? ''),
                    'venue'        => (string) ($venue['name'] ?? ''),
                    'city'         => (string) ($venue['city']['name'] ?? ''),
                    'province'     => (string) ($venue['state']['name'] ?? ''),
                    'country_code' => strtoupper((string) ($venue['country']['countryCode'] ?? $params['countryCode'])),
                    'address'      => (string) ($venue['address']['line1'] ?? ''),
                    'lat'          => isset($venue['location']['latitude']) ? (float) $venue['location']['latitude'] : null,
                    'lng'          => isset($venue['location']['longitude']) ? (float) $venue['location']['longitude'] : null,
                    'poster'       => $foto,
                    'ticket_url'   => (string) ($e['url'] ?? ''),
                    'url'          => '',
                    'precio'       => $precio,
                    'description'  => '',
                    'cancelado'    => (($e['dates']['status']['code'] ?? '') === 'cancelled'),
                    'tipo'         => 'concert',
                    'ref'          => 'tm:' . $e['id'],
                ];
            }

            if ($pag + 1 >= (int) ($d['page']['totalPages'] ?? 1)) {
                break;
            }

            usleep(300000);
        }

        return $out;
    }

    /** @return array<int,array<string,mixed>> */
    public static function leerCsv(string $contenido, string $delim = 'auto'): array
    {
        $contenido = ltrim($contenido, "\xEF\xBB\xBF");

        if ($delim === 'auto') {
            $muestra = substr($contenido, 0, 2048);
            $c       = [';' => substr_count($muestra, ';'), ',' => substr_count($muestra, ','), "\t" => substr_count($muestra, "\t")];
            arsort($c);
            $delim = array_key_first($c);
        }

        $h = fopen('php://temp', 'r+');
        fwrite($h, $contenido);
        rewind($h);
        $cab = fgetcsv($h, 0, $delim, '"', '');

        if (!$cab) {
            return [];
        }

        $cab = array_map(fn($x) => strtolower(trim((string) $x)), $cab);
        $out = [];

        while (($f = fgetcsv($h, 0, $delim, '"', '')) !== false) {
            if (!array_filter($f)) {
                continue;
            }

            $f = array_pad($f, count($cab), '');
            $d = array_combine($cab, array_slice($f, 0, count($cab)));
            $hl = array_values(array_filter(array_map('trim', explode('|', (string) ($d['headliners'] ?? '')))));
            $titulo = trim((string) ($d['title'] ?? '')) ?: ($hl[0] ?? trim((string) ($d['name'] ?? '')));
            $f1 = self::fecha($d['date_start'] ?? ($d['start_date'] ?? ''));

            if ($titulo === '' || !$f1) {
                continue;
            }

            $lat = trim((string) ($d['latitude'] ?? ''));
            $lng = trim((string) ($d['longitude'] ?? ''));
            $apoyo = trim((string) ($d['support_bands'] ?? ''));

            $out[] = [
                'title'        => $titulo,
                'bands'        => array_merge($hl, $apoyo !== '' ? array_map('trim', explode(',', $apoyo)) : array_map('trim', array_filter(explode(',', (string) ($d['bands'] ?? ''))))),
                'support'      => $apoyo,
                'date_start'   => $f1,
                'date_end'     => self::fecha($d['date_end'] ?? ($d['end_date'] ?? '')),
                'time'         => self::hora($d['concert_time'] ?? ''),
                'doors'        => self::hora($d['doors_time'] ?? ''),
                'venue'        => trim((string) ($d['venue'] ?? '')),
                'city'         => trim((string) ($d['city'] ?? '')),
                'province'     => trim((string) ($d['province'] ?? '')),
                'country_code' => strtoupper(substr(trim((string) ($d['country_code'] ?? '')), 0, 2)),
                'address'      => trim((string) ($d['address'] ?? '')),
                'lat'          => $lat !== '' ? (float) str_replace(',', '.', $lat) : null,
                'lng'          => $lng !== '' ? (float) str_replace(',', '.', $lng) : null,
                'poster'       => trim((string) ($d['poster_url'] ?? '')),
                'ticket_url'   => trim((string) ($d['ticket_url'] ?? '')),
                'url'          => trim((string) ($d['website_url'] ?? ($d['website'] ?? ''))),
                'description'  => trim((string) ($d['description'] ?? '')),
                'tipo'         => in_array(strtolower((string) ($d['event_type'] ?? '')), ['festival'], true) ? 'festival' : 'concert',
                'prices_raw'   => trim((string) ($d['prices'] ?? '')),
                'genre_ids'    => trim((string) ($d['genre_ids'] ?? '')),
                'ref'          => '',
            ];
        }

        return $out;
    }

    // ── guardado con deteccion de duplicados ─────────────────────────────

    /** Busca el mismo evento ya guardado: por referencia de origen, por clave exacta o por parecido (misma fecha y ciudad). */
    public static function buscarDuplicado(array $e): ?object
    {
        $db = self::db();

        if (!empty($e['ref'])) {
            $r = $db->setQuery($db->getQuery(true)->select('*')->from('#__concerts')->where('source_ref = ' . $db->quote($e['ref']))->setLimit(1))->loadObject();

            if ($r) {
                return $r;
            }
        }

        $clave = self::claveDedupe($e['title'], $e['date_start'], $e['city'] ?? '');
        $r     = $db->setQuery($db->getQuery(true)->select('*')->from('#__concerts')->where('dedupe_key = ' . $db->quote($clave))->setLimit(1))->loadObject();

        if ($r) {
            return $r;
        }

        // mismo dia (o dentro de un festival de varios dias) y misma ciudad con titulo parecido
        $fecha = $db->quote($e['date_start']);
        $cand  = $db->setQuery($db->getQuery(true)->select('*')->from('#__concerts')
            ->where("($fecha BETWEEN date_start AND COALESCE(date_end, date_start))")
            ->setLimit(60))->loadObjectList();
        $ciudad = self::norm((string) ($e['city'] ?? ''));

        foreach ($cand as $c) {
            $mismaCiudad = $ciudad === '' || self::norm((string) $c->city) === $ciudad;
            $lineup      = (string) ($c->title . ' ' . $c->support_bands . ' ' . ($c->lineup ?? ''));
            $match       = max(self::parecido($e['title'], (string) $c->title), self::parecido($e['title'], (string) $c->band));

            if (!$match) {
                // el titular puede figurar en el cartel de un festival ya guardado
                $match = (strlen(self::norm($e['title'])) >= 5 && strpos(self::norm($lineup), self::norm($e['title'])) !== false && $c->event_type === 'festival') ? 0.8 : 0;
            }

            if ($mismaCiudad && $match >= 0.6) {
                return $c;
            }
        }

        return null;
    }

    /**
     * Inserta o actualiza un evento. Devuelve 'nuevo', 'actualizado' o 'igual'.
     * Nunca pisa datos que ya existan (se rellenan solo los huecos).
     */
    public static function guardar(array $e, string $origen, bool $publicar): string
    {
        $db   = self::db();
        $now  = Factory::getDate()->toSql();
        $cc   = strtoupper(trim((string) ($e['country_code'] ?? ''))) ?: 'ES';
        $tipo = ($e['tipo'] ?? 'concert') === 'festival' ? 'festival' : 'concert';

        $bands   = array_values(array_unique(array_filter(array_map('trim', (array) ($e['bands'] ?? [])))));
        $titular = $bands[0] ?? $e['title'];
        $apoyo   = $e['support'] ?? implode(', ', array_slice($bands, 1));

        $dup = self::buscarDuplicado($e);

        if ($dup) {
            $cambios = ['last_seen' => $now];
            $rellenar = [
                'venue' => $e['venue'] ?? '', 'province' => $e['province'] ?? '', 'poster_url' => $e['poster'] ?? '',
                'ticket_url' => $e['ticket_url'] ?? '', 'website_url' => $e['url'] ?? '', 'concert_time' => $e['time'] ?? '',
                'doors_time' => $e['doors'] ?? '', 'address' => $e['address'] ?? '', 'support_bands' => $apoyo,
                'description' => $e['description'] ?? '',
            ];

            foreach ($rellenar as $campo => $valor) {
                if ($valor !== '' && $valor !== null && trim((string) ($dup->$campo ?? '')) === '') {
                    $cambios[$campo] = $valor;
                }
            }

            if (!empty($e['date_end']) && empty($dup->date_end)) {
                $cambios['date_end'] = $e['date_end'];
            }

            if ($bands && empty($dup->lineup) && count($bands) > 1) {
                $cambios['lineup'] = implode(', ', $bands);
            }

            if (!empty($e['lat']) && !empty($e['lng']) && !(float) ($dup->latitude ?? 0)) {
                $cambios['latitude']   = $e['lat'];
                $cambios['longitude']  = $e['lng'];
                $cambios['geo_status'] = 'ok';
            }

            if (empty($dup->source_ref) && !empty($e['ref'])) {
                $cambios['source_ref'] = $e['ref'];
            }

            if (!empty($e['cancelado']) && (int) $dup->state !== -1) {
                $cambios['state'] = -1;
            }

            $real = array_diff_key($cambios, ['last_seen' => 1]);
            $sets = [];

            foreach ($cambios as $k => $v) {
                $sets[] = $db->quoteName($k) . ' = ' . ($v === null ? 'NULL' : $db->quote((string) $v));
            }

            $db->setQuery('UPDATE #__concerts SET ' . implode(', ', $sets) . ', modified = ' . $db->quote($now) . ' WHERE id = ' . (int) $dup->id)->execute();

            return $real ? 'actualizado' : 'igual';
        }

        $alias = self::slug($e['title'] . '-' . ($e['city'] ?? '') . '-' . $e['date_start']);
        $n     = 0;

        while ((int) $db->setQuery($db->getQuery(true)->select('COUNT(*)')->from('#__concerts')->where('alias = ' . $db->quote($alias . ($n ? '-' . $n : ''))))->loadResult() > 0) {
            $n++;
        }

        $alias .= $n ? '-' . $n : '';

        $row = (object) [
            'title'         => $e['title'],
            'alias'         => $alias,
            'band'          => $titular,
            'headliners'    => '',
            'support_bands' => $apoyo ?: null,
            'lineup'        => count($bands) > 1 ? implode(', ', $bands) : null,
            'genre_ids'     => $e['genre_ids'] ?? '',
            'date_start'    => $e['date_start'],
            'date_end'      => $e['date_end'] ?? null,
            'concert_time'  => $e['time'] ?? null,
            'doors_time'    => $e['doors'] ?? null,
            'venue'         => (string) ($e['venue'] ?? ''),
            'city'          => (string) ($e['city'] ?? ''),
            'province'      => ($e['province'] ?? '') ?: null,
            'country_code'  => $cc,
            'country_name'  => self::PAISES[$cc] ?? $cc,
            'address'       => ($e['address'] ?? '') ?: null,
            'prices'        => self::precios($e),
            'ticket_url'    => ($e['ticket_url'] ?? '') ?: null,
            'website_url'   => ($e['url'] ?? '') ?: null,
            'poster_url'    => ($e['poster'] ?? '') ?: null,
            'description'   => ($e['description'] ?? '') ?: null,
            'state'         => !empty($e['cancelado']) ? -1 : ($publicar ? 1 : 0),
            'ordering'      => 0,
            'created'       => $now,
            'modified'      => $now,
            'event_type'    => $tipo,
            'latitude'      => !empty($e['lat']) ? $e['lat'] : null,
            'longitude'     => !empty($e['lng']) ? $e['lng'] : null,
            'geo_status'    => !empty($e['lat']) ? 'ok' : null,
            'source'        => $origen,
            'source_ref'    => ($e['ref'] ?? '') ?: null,
            'dedupe_key'    => self::claveDedupe($e['title'], $e['date_start'], $e['city'] ?? ''),
            'last_seen'     => $now,
        ];

        $db->insertObject('#__concerts', $row);

        return 'nuevo';
    }

    private static function precios(array $e): string
    {
        $out = [];

        if (!empty($e['prices_raw'])) {
            foreach (explode('|', $e['prices_raw']) as $p) {
                $x = explode(':', $p, 2);

                if (trim($x[0]) !== '') {
                    $out[] = ['label' => trim($x[0]), 'amount' => trim($x[1] ?? ''), 'sold_out' => 0];
                }
            }
        } elseif (!empty($e['precio'])) {
            $out[] = ['label' => 'Desde', 'amount' => $e['precio'], 'sold_out' => 0];
        }

        return $out ? json_encode($out, JSON_UNESCAPED_UNICODE) : '';
    }

    // ── geocodificacion (OpenStreetMap / Nominatim, con cache) ───────────

    /** @return array{lat:float,lng:float,provincia:string}|null */
    public static function geocodificar(string $ciudad, string $provincia, string $cc, bool $permitirRed = true): ?array
    {
        $ciudad = trim($ciudad);

        if ($ciudad === '') {
            return null;
        }

        $db    = self::db();
        $cc    = strtoupper($cc ?: 'ES');
        $clave = substr(self::norm($ciudad) . '|' . self::norm($provincia) . '|' . $cc, 0, 190);
        $c     = $db->setQuery($db->getQuery(true)->select('*')->from('#__hmb_geocache')->where('clave = ' . $db->quote($clave)))->loadObject();

        if ($c) {
            return $c->estado === 'ok' ? ['lat' => (float) $c->lat, 'lng' => (float) $c->lng, 'provincia' => (string) $c->provincia] : null;
        }

        if (!$permitirRed) {
            return null;
        }

        $email = trim((string) self::config()->get('contacto', 'info@heavymetalbrothers.com'));
        $q     = http_build_query([
            'q'              => $ciudad . ($provincia ? ', ' . $provincia : '') . ', ' . (self::PAISES[$cc] ?? $cc),
            'format'         => 'json', 'limit' => 1, 'addressdetails' => 1, 'countrycodes' => strtolower($cc), 'accept-language' => 'es',
        ]);
        $r = self::http('https://nominatim.openstreetmap.org/search?' . $q, 20, ['User-Agent: HMB-Agenda/1.0 (' . $email . ')']);
        usleep(1100000); // politica de uso: maximo 1 consulta por segundo

        $d = json_decode($r['body'], true);

        if ($r['code'] !== 200 || !is_array($d)) {
            return null; // fallo de red: no se cachea para reintentar
        }

        $f = new \stdClass();
        $f->clave = $clave;
        $f->creado = Factory::getDate()->toSql();

        if (!empty($d[0]['lat'])) {
            $a = $d[0]['address'] ?? [];
            $f->lat       = (float) $d[0]['lat'];
            $f->lng       = (float) $d[0]['lon'];
            $f->provincia = (string) ($a['province'] ?? ($a['county'] ?? ($a['state'] ?? '')));
            $f->pais      = $cc;
            $f->estado    = 'ok';
        } else {
            $f->estado = 'fail';
        }

        $db->insertObject('#__hmb_geocache', $f);

        return $f->estado === 'ok' ? ['lat' => $f->lat, 'lng' => $f->lng, 'provincia' => $f->provincia] : null;
    }

    /**
     * Pone coordenadas (y provincia, si falta) a los eventos que no las tienen.
     * Hace como mucho $max consultas nuevas por llamada.
     *
     * @return array{hechos:int,fallos:int,pendientes:int}
     */
    public static function geocodificarPendientes(int $max = 15): array
    {
        $db   = self::db();
        $ciudades = $db->setQuery(
            "SELECT city, COALESCE(province,'') AS province, country_code, COUNT(*) AS n FROM #__concerts
             WHERE city <> '' AND (latitude IS NULL OR latitude = 0) AND (geo_status IS NULL OR geo_status <> 'fail')
             GROUP BY city, COALESCE(province,''), country_code ORDER BY n DESC LIMIT 400"
        )->loadObjectList();

        $hechos = $fallos = $red = 0;

        foreach ($ciudades as $c) {
            $clave = substr(self::norm($c->city) . '|' . self::norm($c->province) . '|' . strtoupper($c->country_code ?: 'ES'), 0, 190);
            $enCache = (int) $db->setQuery($db->getQuery(true)->select('COUNT(*)')->from('#__hmb_geocache')->where('clave = ' . $db->quote($clave)))->loadResult() > 0;

            if (!$enCache && $red >= $max) {
                continue;
            }

            $g = self::geocodificar($c->city, $c->province, $c->country_code, $red < $max);

            if (!$enCache) {
                $red++;
            }

            $where = 'city = ' . $db->quote($c->city) . ' AND COALESCE(province, ' . $db->quote('') . ') = ' . $db->quote($c->province)
                . ' AND country_code = ' . $db->quote($c->country_code) . ' AND (latitude IS NULL OR latitude = 0)';

            if ($g) {
                $sets = 'latitude = ' . (float) $g['lat'] . ', longitude = ' . (float) $g['lng'] . ", geo_status = 'ok'";

                if ($c->province === '' && $g['provincia'] !== '' && strtoupper($c->country_code) === 'ES') {
                    $sets .= ', province = ' . $db->quote($g['provincia']);
                }

                $db->setQuery("UPDATE #__concerts SET $sets WHERE $where")->execute();
                $hechos += (int) $c->n;
            } elseif ($enCache) {
                $db->setQuery("UPDATE #__concerts SET geo_status = 'fail' WHERE $where")->execute();
                $fallos += (int) $c->n;
            }
        }

        $pend = (int) $db->setQuery("SELECT COUNT(*) FROM #__concerts WHERE city <> '' AND (latitude IS NULL OR latitude = 0) AND (geo_status IS NULL OR geo_status <> 'fail')")->loadResult();

        return ['hechos' => $hechos, 'fallos' => $fallos, 'pendientes' => $pend];
    }

    // ── filtro de estilo (para fuentes generalistas) ─────────────────────

    private const TERMINOS_METAL = ['metal', 'rock', 'thrash', 'death', 'black', 'doom', 'heavy', 'hardcore', 'punk', 'stoner', 'prog', 'grind', 'core', 'sludge', 'hard rock', 'festival rock'];

    /** @var array<string,bool>|null */
    private static ?array $bandasConocidas = null;

    private static function bandasConocidas(): array
    {
        if (self::$bandasConocidas === null) {
            $db  = self::db();
            $nom = [];

            foreach (['SELECT nombre FROM #__efemerides_bandas', 'SELECT name FROM #__artistas', 'SELECT DISTINCT band FROM #__concerts WHERE band <> ' . $db->quote('')] as $sql) {
                try {
                    foreach ($db->setQuery($sql)->loadColumn() ?: [] as $n) {
                        $k = self::norm((string) $n);

                        if (strlen($k) >= 4) {
                            $nom[$k] = true;
                        }
                    }
                } catch (\Throwable $e) {
                }
            }

            self::$bandasConocidas = $nom;
        }

        return self::$bandasConocidas;
    }

    /** ¿Parece un evento de metal / rock duro? (por palabras de estilo o por bandas ya conocidas en la web) */
    public static function esMetal(array $e, string $extra = ''): bool
    {
        $texto = self::norm($e['title'] . ' ' . implode(' ', (array) ($e['bands'] ?? [])) . ' ' . substr((string) ($e['description'] ?? ''), 0, 400));

        foreach (array_merge(self::TERMINOS_METAL, array_filter(array_map('trim', explode(',', strtolower($extra))))) as $t) {
            if ($t !== '' && preg_match('/(^| )' . preg_quote(self::norm($t), '/') . '( |$)/', $texto)) {
                return true;
            }
        }

        $conocidas = self::bandasConocidas();

        // el titulo puede juntar varias bandas ("Amon Amarth + Orbit Culture", "A · B, C"): se miran por separado
        $nombres = (array) ($e['bands'] ?? []);

        foreach (preg_split('/\s+[+·&\/—–-]\s+|,\s*|\s+y\s+|\s+and\s+/iu', (string) $e['title']) ?: [] as $parte) {
            $nombres[] = $parte;
        }

        foreach (array_merge([$e['title']], $nombres) as $b) {
            if (isset($conocidas[self::norm((string) $b)])) {
                return true;
            }
        }

        return false;
    }

    // ── fuentes ──────────────────────────────────────────────────────────

    /**
     * Ejecuta una fuente: lee, normaliza, guarda con deduplicacion y registra el resultado.
     *
     * @return array{nuevos:int,actualizados:int,iguales:int,leidos:int,errores:array<int,string>}
     */
    public static function ejecutarFuente(object $src): array
    {
        @set_time_limit(240);
        $db  = self::db();
        $res = ['nuevos' => 0, 'actualizados' => 0, 'iguales' => 0, 'leidos' => 0, 'descartados' => 0, 'errores' => []];
        $p   = json_decode((string) $src->params, true) ?: [];
        $eventos = [];
        $err = null;

        switch ($src->type) {
            case 'ticketmaster':
                $p['apikey'] = (string) (($p['apikey'] ?? '') ?: self::config()->get('ticketmaster_key', ''));
                $eventos     = self::leerTicketmaster($p, $err);
                break;

            case 'ics':
                $r = self::http($src->url);
                $r['code'] === 200 ? $eventos = self::leerIcs($r['body']) : $err = 'HTTP ' . $r['code'] . ' ' . $r['err'];
                break;

            case 'csvurl':
                $r = self::http($src->url);
                $r['code'] === 200 ? $eventos = self::leerCsv($r['body']) : $err = 'HTTP ' . $r['code'] . ' ' . $r['err'];
                break;

            case 'rss':
                $r = self::http($src->url);

                if ($r['code'] !== 200) {
                    $err = 'HTTP ' . $r['code'] . ' ' . $r['err'];
                    break;
                }

                preg_match_all('#<link>\s*(?:<!\[CDATA\[)?\s*(https?://[^<\]\s]+)#i', $r['body'], $m);
                $enlaces = array_slice(array_unique($m[1] ?? []), 1, (int) ($p['max_paginas'] ?? 25));

                foreach ($enlaces as $l) {
                    $pg = self::http($l, 20);
                    $eventos = array_merge($eventos, $pg['code'] === 200 ? self::leerJsonLd($pg['body'], $l) : []);
                }

                break;

            default: // jsonld
                $r = self::http($src->url);

                if ($r['code'] !== 200) {
                    $err = 'HTTP ' . $r['code'] . ' ' . $r['err'];
                    break;
                }

                $eventos = self::leerJsonLd($r['body'], $src->url);

                // pagina de listado sin datos propios: seguir los enlaces que cumplan el patron indicado
                if (!$eventos && !empty($p['seguir'])) {
                    preg_match_all('#href=["\']([^"\']+)["\']#i', $r['body'], $m);
                    $vistos = [];

                    foreach ($m[1] as $href) {
                        if (@preg_match('#' . $p['seguir'] . '#i', $href) && count($vistos) < (int) ($p['max_paginas'] ?? 25)) {
                            $abs = preg_match('#^https?://#', $href) ? $href : rtrim((string) preg_replace('#^(https?://[^/]+).*$#', '$1', $src->url), '/') . '/' . ltrim($href, '/');
                            $vistos[$abs] = true;
                        }
                    }

                    foreach (array_keys($vistos) as $l) {
                        $pg = self::http($l, 20);
                        $eventos = array_merge($eventos, $pg['code'] === 200 ? self::leerJsonLd($pg['body'], $l) : []);
                    }
                }
        }

        if ($err) {
            $res['errores'][] = $err;
        }

        $hoy = date('Y-m-d');
        $cc0 = strtoupper((string) ($p['pais'] ?? ''));

        foreach ($eventos as $e) {
            $res['leidos']++;
            $e['title'] = trim(html_entity_decode((string) ($e['title'] ?? ''), ENT_QUOTES, 'UTF-8'));

            if ($e['title'] === '' || empty($e['date_start']) || ($e['date_end'] ?? $e['date_start']) < $hoy) {
                continue; // sin datos o ya pasado
            }

            if (($e['city'] ?? '') === '' && ($e['venue'] ?? '') === '') {
                $res['errores'][] = 'Sin lugar: ' . $e['title'];
                continue;
            }

            if (empty($e['country_code']) && $cc0) {
                $e['country_code'] = $cc0;
            }

            if (!empty($p['solo_pais']) && strtoupper($e['country_code'] ?? '') !== strtoupper((string) $p['solo_pais'])) {
                continue;
            }

            // fuentes generalistas: solo lo que parece metal / rock duro (Ticketmaster y CSV ya vienen filtrados)
            $filtrar = $src->type === 'ticketmaster' ? !empty($p['filtro_metal']) : ($src->type !== 'csvurl' && ($p['filtro_metal'] ?? 1));

            if ($filtrar && !self::esMetal($e, (string) ($p['palabras'] ?? ''))) {
                $res['descartados'] = ($res['descartados'] ?? 0) + 1;
                continue;
            }

            try {
                $r = self::guardar($e, $src->type, !empty($src->auto_publish));
                $res[['nuevo' => 'nuevos', 'actualizado' => 'actualizados', 'igual' => 'iguales'][$r]]++;
            } catch (\Throwable $ex) {
                $res['errores'][] = $e['title'] . ': ' . $ex->getMessage();
            }
        }

        $estado = $res['errores'] && !$res['leidos'] ? 'Error: ' . $res['errores'][0] : ($res['leidos'] . ' leídos' . ($res['errores'] ? ' · ' . count($res['errores']) . ' avisos' : ''));
        $db->setQuery('UPDATE #__concerts_sources SET last_run = ' . $db->quote(Factory::getDate()->toSql())
            . ', last_status = ' . $db->quote(substr($estado, 0, 290)) . ', last_new = ' . (int) $res['nuevos']
            . ', last_updated = ' . (int) $res['actualizados'] . ' WHERE id = ' . (int) $src->id)->execute();

        return $res;
    }

    /** Lo que hace la tarea programada: todas las fuentes activas y un lote de geocodificacion. */
    public static function ejecutarTodas(): array
    {
        $db     = self::db();
        $fuentes = $db->setQuery($db->getQuery(true)->select('*')->from('#__concerts_sources')->where('active = 1')->order('ordering, id'))->loadObjectList();
        $out    = ['fuentes' => [], 'geo' => []];

        foreach ($fuentes as $f) {
            $out['fuentes'][$f->name] = self::ejecutarFuente($f);
        }

        $out['geo'] = self::geocodificarPendientes(25);

        return $out;
    }
}
