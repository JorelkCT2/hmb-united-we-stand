<?php
namespace Joomla\Component\Hmbnews\Administrator\Helper;

defined('_JEXEC') or die;

/**
 * Parte "gratis" del importador: lee los RSS de las fuentes, filtra lo que no interesa,
 * descarga la imagen real de cada noticia y extrae el texto. La IA solo se usa despues,
 * y solo para las noticias que se elijan.
 */
class FeedHelper
{
    public const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';

    /** Terminos que indican que la noticia va de metal / rock duro (fuentes generalistas). */
    private const TERMINOS = [
        'metal', 'thrash', 'doom', 'death', 'black metal', 'deathcore', 'metalcore', 'grindcore', 'sludge', 'stoner',
        'hard rock', 'heavy', 'prog', 'hardcore', 'punk', 'riff', 'headbang', 'moshpit', 'mosh', 'wacken', 'hellfest',
        'download festival', 'graspop', 'resurrection', 'rock in rio', 'knotfest', 'ozzfest', 'ozzy', 'black sabbath',
        'iron maiden', 'metallica', 'megadeth', 'slayer', 'anthrax', 'judas priest', 'motorhead', 'motörhead', 'pantera',
        'slipknot', 'korn', 'tool', 'opeth', 'mastodon', 'gojira', 'sepultura', 'dio', 'deep purple', 'ac/dc', 'kiss',
        'guns n', 'maiden', 'sabbath', 'nightwish', 'sabaton', 'rammstein', 'trivium', 'lamb of god', 'in flames',
        'arch enemy', 'dimmu', 'behemoth', 'amon amarth', 'tour', 'gira', 'festival', 'album', 'single', 'nuevo disco',
        'frontman', 'vocalist', 'guitarist', 'drummer', 'bassist', 'new song', 'new video', 'new single', 'lineup', 'line-up', 'reunion', 'setlist', 'cantante', 'guitarrista', 'bateria', 'concierto',
    ];

    private const PALABRAS_BASURA = [
        'black friday', 'cyber monday', 'deal', 'deals', 'discount', '% off', 'giveaway', 'sorteo', 'quiz', 'horoscope',
        'horóscopo', 'wordle', 'taylor swift', 'beyonce', 'beyoncé', 'kardashian', 'trump', 'biden', 'politics', 'political', 'recipe',
        'receta', 'netflix', 'streaming guide', 'best guitar', 'best amp', 'best pedal', 'review: guitar', 'unboxing', 'coupon', 'best new gear', ' gear ', 'pedal', 'pedals', ' amp ', 'amps', 'effects expander', 'wireless guitar', 'telecaster', 'stratocaster', 'les paul', 'fender', 'gibson', 'epiphone', 'ibanez', 'pickup', 'chopping board',
    ];

    // ── HTTP ─────────────────────────────────────────────────────────────

    /** @return array{code:int,body:string,type:string,url:string,err:string} */
    public static function http(string $url, int $timeout = 20, string $referer = '', int $maxBytes = 2500000): array
    {
        $out = ['code' => 0, 'body' => '', 'type' => '', 'url' => $url, 'err' => ''];

        if (!preg_match('#^https?://#i', $url) || !function_exists('curl_init')) {
            $out['err'] = 'URL no valida';

            return $out;
        }

        $host = (string) parse_url($url, PHP_URL_HOST);

        if ($host === '' || strcasecmp($host, 'localhost') === 0 || (filter_var($host, FILTER_VALIDATE_IP)
            && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
            $out['err'] = 'Direccion no permitida';

            return $out;
        }

        $body = '';
        $ch   = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_ENCODING       => '',
            CURLOPT_USERAGENT      => self::UA,
            CURLOPT_REFERER        => $referer,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_HTTPHEADER     => ['Accept: text/html,application/xhtml+xml,application/xml,image/*;q=0.9,*/*;q=0.8', 'Accept-Language: en,es;q=0.8'],
            CURLOPT_WRITEFUNCTION  => static function ($c, $chunk) use (&$body, $maxBytes) {
                $body .= $chunk;

                return strlen($body) > $maxBytes ? 0 : strlen($chunk);
            },
        ]);
        curl_exec($ch);
        $out['code'] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $out['type'] = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $out['url']  = (string) (curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $url);
        $out['err']  = curl_errno($ch) && strlen($body) <= $maxBytes ? curl_error($ch) : '';
        $out['body'] = $body;
        curl_close($ch);

        return $out;
    }

    // ── Feeds ────────────────────────────────────────────────────────────

    public static function baseDe(string $dominio): string
    {
        $dominio = trim($dominio);

        if (!preg_match('#^https?://#i', $dominio)) {
            $dominio = 'https://' . $dominio;
        }

        $p = parse_url($dominio);

        return ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '');
    }

    public static function hostDe(string $url): string
    {
        return preg_replace('/^www\./i', '', strtolower((string) parse_url(self::baseDe($url), PHP_URL_HOST)));
    }

    /** Busca la URL del RSS de un sitio (autodescubrimiento y rutas habituales). */
    public static function descubrirFeed(string $dominio): string
    {
        $base  = self::baseDe($dominio);
        $cand  = [];
        $home  = self::http($base . '/', 15);

        if ($home['code'] === 200 && preg_match_all('#<link[^>]+type=["\']application/(?:rss|atom)\+xml["\'][^>]*>#i', $home['body'], $m)) {
            foreach ($m[0] as $tag) {
                if (preg_match('#href=["\']([^"\']+)#i', $tag, $h) && stripos($h[1], 'comments') === false) {
                    $cand[] = self::absoluta($h[1], $base);
                }
            }
        }

        foreach (['/feed/', '/feed', '/rss', '/rss.xml', '/feed.xml', '/feeds/all.rss', '/feeds.xml'] as $ruta) {
            $cand[] = $base . $ruta;
        }

        foreach (array_unique($cand) as $u) {
            $r = self::http($u, 15, '', 1500000);

            if ($r['code'] === 200 && (strpos($r['body'], '<item') !== false || strpos($r['body'], '<entry') !== false)) {
                return $u;
            }
        }

        return '';
    }

    public static function absoluta(string $href, string $base): string
    {
        $href = html_entity_decode(trim($href), ENT_QUOTES, 'UTF-8');

        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }

        if (strpos($href, '//') === 0) {
            return 'https:' . $href;
        }

        return rtrim($base, '/') . '/' . ltrim($href, '/');
    }

    /** @return array<int,array<string,mixed>> */
    public static function leerFeed(string $xml): array
    {
        if ($xml === '') {
            return [];
        }

        $prev = libxml_use_internal_errors(true);
        $doc  = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        libxml_use_internal_errors($prev);

        if (!$doc) {
            return [];
        }

        $items = isset($doc->channel->item) ? $doc->channel->item : (isset($doc->entry) ? $doc->entry : []);
        $out   = [];

        foreach ($items as $it) {
            $ns      = $it->getNamespaces(true);
            $media   = isset($ns['media']) ? $it->children($ns['media']) : null;
            $content = isset($ns['content']) ? (string) $it->children($ns['content'])->encoded : '';
            $dc      = isset($ns['dc']) ? $it->children($ns['dc']) : null;

            $link = (string) $it->link;

            if ($link === '' && isset($it->link['href'])) {
                $link = (string) $it->link['href'];
            }

            $desc = $content !== '' ? $content : ((string) ($it->description ?? '') ?: (string) ($it->summary ?? '') ?: (string) ($it->content ?? ''));
            $img  = '';

            if ($media) {
                foreach (['content', 'thumbnail'] as $k) {
                    if (isset($media->$k) && isset($media->$k->attributes()->url)) {
                        $img = (string) $media->$k->attributes()->url;
                        break;
                    }
                }
            }

            if ($img === '' && isset($it->enclosure) && stripos((string) $it->enclosure['type'], 'image') === 0) {
                $img = (string) $it->enclosure['url'];
            }

            if ($img === '' && preg_match('#<img[^>]+src=["\']([^"\']+)#i', $desc, $m)) {
                $img = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            }

            $fecha = (string) ($it->pubDate ?? '') ?: (string) ($it->published ?? '') ?: (string) ($it->updated ?? '') ?: ($dc ? (string) $dc->date : '');
            $ts    = $fecha !== '' ? strtotime($fecha) : false;

            $out[] = [
                'titulo' => self::limpiar((string) $it->title),
                'url'    => trim($link),
                'fecha'  => $ts ?: 0,
                'texto'  => self::limpiar($desc),
                'cats'   => self::categorias($it),
                'imagen' => trim($img),
            ];
        }

        return $out;
    }

    private static function categorias(\SimpleXMLElement $it): string
    {
        $c = [];

        foreach ($it->category ?? [] as $cat) {
            $c[] = (string) $cat;
        }

        return implode(' ', $c);
    }

    public static function limpiar(string $html): string
    {
        $t = html_entity_decode(strip_tags(preg_replace('#<(script|style)[^>]*>.*?</\1>#is', ' ', $html)), ENT_QUOTES, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $t));
    }

    // ── Relevancia ───────────────────────────────────────────────────────

    public static function normalizar(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');

        return strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', 'ö' => 'o', 'ä' => 'a']);
    }

    public static function lista(string $csv): array
    {
        return array_values(array_filter(array_map(static fn($x) => self::normalizar(trim($x)), preg_split('/[,\n;]+/', $csv))));
    }

    public static function basuraPorDefecto(): string
    {
        return implode(', ', self::PALABRAS_BASURA);
    }

    /**
     * @param array<int,string> $bandas   nombres de bandas de la casa (maxima prioridad)
     * @return array{puntos:int,motivo:string,descartar:bool}
     */
    public static function puntuar(array $it, bool $generalista, array $excluir, array $prioritarias, array $bandas): array
    {
        $h       = ' ' . self::normalizar($it['titulo'] . ' ' . ($it['cats'] ?? '') . ' ' . mb_substr($it['texto'], 0, 600)) . ' ';
        $motivos = [];
        $puntos  = 0;

        foreach ($excluir as $x) {
            if ($x !== '' && self::contiene($h, $x)) {
                return ['puntos' => 0, 'motivo' => 'descartada: «' . $x . '»', 'descartar' => true];
            }
        }

        foreach ($bandas as $b) {
            if (mb_strlen($b) > 2 && strpos($h, ' ' . $b . ' ') !== false) {
                $puntos += 6;
                $motivos[] = 'banda de la casa: ' . $b;
                break;
            }
        }

        foreach ($prioritarias as $p) {
            if ($p !== '' && self::contiene($h, $p)) {
                $puntos += 3;
                $motivos[] = 'tema prioritario: ' . $p;
                break;
            }
        }

        $hits = 0;

        foreach (self::TERMINOS as $t) {
            if (strpos($h, self::normalizar($t)) !== false && ++$hits >= 3) {
                break;
            }
        }

        if ($generalista && $hits === 0 && $puntos === 0) {
            return ['puntos' => 0, 'motivo' => 'sin relacion con el metal', 'descartar' => true];
        }

        $puntos += $hits;

        if ($it['fecha'] && time() - $it['fecha'] < 172800) {
            $puntos += 1;
            $motivos[] = 'de las ultimas 48 h';
        }

        return ['puntos' => $puntos, 'motivo' => implode(' · ', $motivos), 'descartar' => false];
    }

    /** Busca una palabra o frase completa (no dentro de otra: «amp» no casa con «champion»). */
    public static function contiene(string $h, string $x): bool
    {
        return (bool) preg_match('/(?<![a-z0-9])' . preg_quote($x, '/') . '(?![a-z0-9])/u', $h);
    }

    /** Palabras significativas del titulo, para detectar la misma noticia en varias fuentes. */
    public static function palabrasTitulo(string $t): array
    {
        static $vacias = ['with', 'that', 'from', 'this', 'their', 'after', 'about', 'will', 'have', 'been', 'into', 'band', 'metal',
            'album', 'new', 'para', 'sobre', 'como', 'tras', 'entre', 'nuevo', 'nueva', 'disco', 'announce', 'announces', 'releases', 'reveals'];
        $p = preg_split('/[^a-z0-9]+/', self::normalizar($t));

        return array_values(array_unique(array_filter($p, static fn($w) => strlen($w) > 3 && !in_array($w, $vacias, true))));
    }

    public static function parecidas(array $a, array $b): float
    {
        if (!$a || !$b) {
            return 0.0;
        }

        return count(array_intersect($a, $b)) / min(count($a), count($b));
    }

    // ── Pagina de la noticia ─────────────────────────────────────────────

    /** @return array{texto:string,imagen:string,titulo:string,code:int} */
    public static function leerPagina(string $url): array
    {
        $r   = self::http($url, 25, '', 1800000);
        $out = ['texto' => '', 'imagen' => '', 'titulo' => '', 'code' => $r['code']];

        if ($r['code'] !== 200 || $r['body'] === '') {
            return $out;
        }

        $base = self::baseDe($r['url']);
        $html = $r['body'];

        foreach (['#<meta[^>]+(?:property|name)=["\'](?:og:image(?::secure_url)?|twitter:image(?::src)?)["\'][^>]*>#i', '#<link[^>]+rel=["\']image_src["\'][^>]*>#i'] as $re) {
            if (preg_match_all($re, $html, $m)) {
                foreach ($m[0] as $tag) {
                    if (preg_match('#(?:content|href)=["\']([^"\']+)#i', $tag, $c)) {
                        $out['imagen'] = self::absoluta($c[1], $base);
                        break 2;
                    }
                }
            }
        }

        if (preg_match('#<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)#i', $html, $m)) {
            $out['titulo'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        }

        $prev = libxml_use_internal_errors(true);
        $dom  = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $xp = new \DOMXPath($dom);

        foreach ($xp->query('//script|//style|//nav|//aside|//footer|//form|//noscript|//figure|//*[contains(@class,"related")]|//*[contains(@class,"newsletter")]|//*[contains(@class,"comment")]') as $n) {
            $n->parentNode && $n->parentNode->removeChild($n);
        }

        $raiz = $xp->query('//article')->item(0) ?: $xp->query('//main')->item(0) ?: $xp->query('//body')->item(0);
        $txt  = [];

        if ($raiz) {
            foreach ($xp->query('.//p', $raiz) as $p) {
                $t = trim(preg_replace('/\s+/u', ' ', $p->textContent));

                if (mb_strlen($t) > 50 && !preg_match('/^(subscribe|sign up|follow us|click here|read more|copyright|all rights)/i', $t)) {
                    $txt[] = $t;
                }
            }
        }

        $out['texto'] = mb_substr(implode("\n\n", $txt), 0, 6500);

        // Si no hay <article> util, el primer <img> grande de la noticia sirve de respaldo
        if ($out['imagen'] === '' && $raiz) {
            foreach ($xp->query('.//img[@src]', $raiz) as $img) {
                $w = (int) $img->getAttribute('width');

                if ($w === 0 || $w >= 400) {
                    $out['imagen'] = self::absoluta($img->getAttribute('src'), $base);
                    break;
                }
            }
        }

        return $out;
    }

    // ── Imagen local ─────────────────────────────────────────────────────

    /** Descarga la imagen, la valida y la guarda redimensionada. Devuelve la ruta relativa o ''. */
    public static function guardarImagen(string $url, string $referer, string $slug): string
    {
        if ($url === '' || !function_exists('imagecreatefromstring')) {
            return '';
        }

        $r = self::http($url, 25, $referer, 8000000);

        if ($r['code'] !== 200 || strlen($r['body']) < 6000) {
            return '';
        }

        $info = @getimagesizefromstring($r['body']);

        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true) || $info[0] < 400) {
            return '';
        }

        $src = @imagecreatefromstring($r['body']);

        if (!$src) {
            return '';
        }

        $w = imagesx($src);
        $h = imagesy($src);

        if ($w > 1200) {
            $nh  = (int) round($h * 1200 / $w);
            $dst = imagecreatetruecolor(1200, $nh);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, 1200, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }

        // Quita transparencias (PNG/WEBP) sobre fondo negro para guardar en JPG
        $lienzo = imagecreatetruecolor(imagesx($src), imagesy($src));
        imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 0, 0, 0));
        imagecopy($lienzo, $src, 0, 0, 0, 0, imagesx($src), imagesy($src));
        imagedestroy($src);

        $rel = 'images/noticias/' . date('Y-m');
        $dir = JPATH_ROOT . '/' . $rel;

        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            imagedestroy($lienzo);

            return '';
        }

        $fichero = $dir . '/' . substr($slug, 0, 80) . '-' . substr(md5($url), 0, 6) . '.jpg';
        $ok      = imagejpeg($lienzo, $fichero, 82);
        imagedestroy($lienzo);

        return $ok ? $rel . '/' . basename($fichero) : '';
    }

    /** "arch enemy, nuevo álbum" -> "#ArchEnemy, #NuevoÁlbum" (listo para copiar). */
    public static function hashtags(string $etiquetas): string
    {
        $out = [];

        foreach (preg_split('/[,;\n]+/', $etiquetas) as $t) {
            $t = trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $t));

            if ($t === '') {
                continue;
            }

            $h = '#' . str_replace(' ', '', mb_convert_case($t, MB_CASE_TITLE, 'UTF-8'));
            $out[mb_strtolower($h, 'UTF-8')] = $h;
        }

        return implode(', ', $out);
    }

    public static function slug(string $s): string
    {
        $s = preg_replace('/[^a-z0-9\s\-]/', '', self::normalizar($s));

        return trim(substr(preg_replace('/[\s\-]+/', '-', $s), 0, 100), '-') ?: 'noticia';
    }

    // ── IA (solo redaccion, sin busqueda web) ────────────────────────────

    /** @return array{ok:bool,error?:string,data?:array<string,mixed>,coste?:float,tokens?:string} */
    public static function redactar(string $apiKey, string $modelo, array $n): array
    {
        $prompt = "Eres redactor de heavymetalbrothers.com, una web de metal en español de España.\n\n"
            . "A partir SOLO de los datos de la noticia fuente, escribe una noticia original:\n"
            . "- 150-300 palabras, tono de aficionado informado, español natural. No traduzcas frase a frase ni copies la estructura del original.\n"
            . "- NO inventes nada: ni fechas, ni cifras, ni citas, ni nombres que no aparezcan en el texto. Si faltan datos, escribe menos.\n"
            . "- Mantén en su idioma original los nombres de bandas, álbumes y canciones.\n"
            . "- Como mucho 1-2 citas literales (traducidas) entre comillas.\n"
            . "- Si la noticia NO va de música metal / rock duro / guitarras o es publicidad, ofertas, compras o política, responde relevante=false.\n\n"
            . "Devuelve SOLO un objeto JSON válido, sin markdown:\n"
            . "{\"relevante\":true,\"motivo\":\"\",\"titulo\":\"máx 90 caracteres, sin clickbait\",\"intro\":\"1-2 frases, 160-220 caracteres\","
            . "\"contenido\":\"HTML con solo <p> y <h3>, sin enlaces\",\"etiquetas\":\"5-8 etiquetas separadas por coma (banda, género, tipo de noticia)\","
            . "\"metadesc\":\"máx 155 caracteres\"}\n\n"
            . "FUENTE: " . $n['fuente'] . "\nFECHA: " . $n['fecha'] . "\nTÍTULO ORIGINAL: " . $n['titulo'] . "\n\nTEXTO ORIGINAL:\n" . $n['texto'];

        $body = '';
        $code = 0;
        $err  = '';

        for ($i = 1; $i <= 3; $i++) {
            $ch = curl_init('https://api.anthropic.com/v1/messages');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 90,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'x-api-key: ' . $apiKey, 'anthropic-version: 2023-06-01'],
                CURLOPT_POSTFIELDS     => json_encode([
                    'model'       => $modelo,
                    'max_tokens'  => 1800,
                    'temperature' => 0.4,
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                ], JSON_UNESCAPED_UNICODE),
            ]);
            $body = (string) curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);

            if ($code === 200 || !in_array($code, [0, 429, 500, 503, 529], true)) {
                break;
            }

            sleep($i * 2);
        }

        if ($err !== '' && $code === 0) {
            return ['ok' => false, 'error' => 'Error de conexion con la API: ' . $err];
        }

        $d = json_decode($body, true);

        if ($code !== 200) {
            return ['ok' => false, 'error' => 'API: ' . ($d['error']['message'] ?? ('HTTP ' . $code))];
        }

        $texto = '';

        foreach ((array) ($d['content'] ?? []) as $b) {
            if (($b['type'] ?? '') === 'text') {
                $texto .= $b['text'];
            }
        }

        $texto = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($texto)));
        $j     = json_decode($texto, true);

        if (!is_array($j) && preg_match('/\{[\s\S]*\}/u', $texto, $m)) {
            $j = json_decode($m[0], true);
        }

        if (!is_array($j)) {
            return ['ok' => false, 'error' => 'La IA no devolvio un JSON valido.'];
        }

        $in  = (int) ($d['usage']['input_tokens'] ?? 0);
        $out = (int) ($d['usage']['output_tokens'] ?? 0);

        return ['ok' => true, 'data' => $j, 'tokens' => $in . '+' . $out, 'coste' => ($in * 1.0 + $out * 5.0) / 1000000];
    }
}
