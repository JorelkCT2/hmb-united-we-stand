<?php
namespace Joomla\Component\Concerts\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

class ImportController extends BaseController
{
    public function display($cachable = false, $urlparams = []): static
    {
        $this->input->set('view', 'import');
        return parent::display($cachable, $urlparams);
    }

    /** Descargar plantilla CSV actualizada */
    public function template(): void
    {
        // Columnas actuales del componente (sin band, con genre_ids y prices)
        $header = [
            'title',        // Título del evento (obligatorio)
            'headliners',   // Cabezas de cartel separadas por | (ej: Iron Maiden|Sabaton)
            'support_bands',// Teloneras separadas por coma
            'genre_ids',    // IDs de géneros separados por coma (de mujg_artistas_generos)
            'date_start',   // Fecha inicio YYYY-MM-DD (obligatorio)
            'date_end',     // Fecha fin YYYY-MM-DD (solo festivales multi-día)
            'concert_time', // Hora inicio HH:MM
            'doors_time',   // Apertura de puertas HH:MM
            'venue',        // Nombre del recinto
            'city',         // Ciudad
            'province',     // Provincia
            'country_code', // ES, DE, FR... (defecto ES)
            'address',      // Dirección completa
            'prices',       // Precios: "Abono:235€|Día1:99€|Día2:99€" (label:precio separados por |)
            'ticket_url',   // URL compra entradas
            'website_url',  // Web oficial del evento
            'poster_url',   // URL del cartel (PNG o JPG)
            'description',  // Descripción / notas
        ];

        $example = [
            'Rock Imperium Festival 2026',
            'Iron Maiden|Sabaton|Anthrax|Testament',
            'Blues Pills, Crazy Lixx, Domine',
            '1,3',          // IDs de géneros (Heavy Metal=1, Rock=3, ejemplo)
            '2026-07-03',
            '2026-07-05',
            '16:00',
            '15:00',
            'Parque El Batel',
            'Cartagena',
            'Murcia',
            'ES',
            'Paseo Alfonso XII s/n',
            'Abono 3 días:235€|Viernes:99€|Sábado:99€|Domingo:99€',
            'https://rockimperiumfestival.es/entradas',
            'https://rockimperiumfestival.es',
            'https://rockimperiumfestival.es/cartel.jpg',
            'Festival de metal en Cartagena',
        ];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_conciertos.csv"');
        $f = fopen('php://output', 'w');
        fprintf($f, "\xEF\xBB\xBF"); // UTF-8 BOM
        fputcsv($f, $header);
        fputcsv($f, $example);
        fclose($f);
        Factory::getApplication()->close();
    }

    /** Procesar CSV subido */
    public function upload(): void
    {
        $this->checkToken();
        $app  = Factory::getApplication();
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $file = $this->input->files->get('csv_file', [], 'array');

        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $app->enqueueMessage('No se recibió ningún CSV válido.', 'error');
            $this->setRedirect(Route::_('index.php?option=com_concerts&view=import', false));
            return;
        }

        // Detect delimiter
        $delim = $this->input->getString('delimiter', 'auto');
        if ($delim === 'auto') {
            $s = file_get_contents($file['tmp_name'], false, null, 0, 2048);
            $c = [';'=>substr_count($s,';'),','=>substr_count($s,','),"\t"=>substr_count($s,"\t")];
            arsort($c); $delim = array_key_first($c);
        }

        $h = fopen($file['tmp_name'], 'r');
        if (fread($h, 3) !== "\xEF\xBB\xBF") fseek($h, 0);

        $header = fgetcsv($h, 4000, $delim);
        if (!$header) {
            $app->enqueueMessage('CSV vacío o mal formado.', 'error');
            $this->setRedirect(Route::_('index.php?option=com_concerts&view=import', false));
            return;
        }
        $header = array_map(fn($x) => strtolower(trim($x)), $header);

        $cn = ['ES'=>'España','DE'=>'Alemania','AT'=>'Austria','BE'=>'Bélgica','FR'=>'Francia',
               'IT'=>'Italia','PT'=>'Portugal','GB'=>'Reino Unido','NL'=>'Países Bajos',
               'PL'=>'Polonia','SE'=>'Suecia','NO'=>'Noruega','CH'=>'Suiza','HR'=>'Croacia',
               'GR'=>'Grecia','HU'=>'Hungría','CZ'=>'República Checa','RO'=>'Rumanía',
               'SK'=>'Eslovaquia','SI'=>'Eslovenia','IE'=>'Irlanda','IS'=>'Islandia',
               'DK'=>'Dinamarca','FI'=>'Finlandia','EE'=>'Estonia','LV'=>'Letonia',
               'LT'=>'Lituania','LU'=>'Luxemburgo','MT'=>'Malta','CY'=>'Chipre','BG'=>'Bulgaria'];

        $ins = $skip = 0;
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $uid = $app->getIdentity()->id;

        while (($row = fgetcsv($h, 4000, $delim)) !== false) {
            if (empty(array_filter($row))) continue;
            if (count($row) < count($header)) $row = array_pad($row, count($header), '');
            $d = array_combine($header, array_slice($row, 0, count($header)));

            // title OR headliners required
            $title = trim($d['title'] ?? '');
            $hlRaw = trim($d['headliners'] ?? '');
            if (empty($title) && empty($hlRaw)) { $skip++; continue; }

            // date_start required
            if (empty(trim($d['date_start'] ?? ''))) { $skip++; continue; }
            try { $ds = (new \DateTime(trim($d['date_start'])))->format('Y-m-d'); }
            catch (\Exception) { $skip++; continue; }

            $de = '';
            if (!empty($d['date_end'])) {
                try { $de=(new \DateTime(trim($d['date_end'])))->format('Y-m-d'); } catch(\Exception){}
            }

            // Headliners: pipe-separated → JSON array
            $hls = [];
            if ($hlRaw) {
                $hls = array_values(array_filter(array_map('trim', explode('|', $hlRaw))));
            }
            $hlJson = json_encode($hls, JSON_UNESCAPED_UNICODE);

            // band = first headliner or title
            $band = !empty($hls) ? $hls[0] : $title;

            // genre_ids: already comma-separated IDs
            $genreIds = trim($d['genre_ids'] ?? '');

            // prices: "Abono:235€|Día1:99€" → JSON array
            $pricesJson = '';
            $pricesRaw = trim($d['prices'] ?? '');
            if ($pricesRaw) {
                $pricesArr = [];
                foreach (explode('|', $pricesRaw) as $pr) {
                    $parts = explode(':', $pr, 2);
                    if (count($parts) === 2) {
                        $pricesArr[] = ['label'=>trim($parts[0]),'amount'=>trim($parts[1]),'sold_out'=>0];
                    } elseif (count($parts) === 1 && !empty(trim($parts[0]))) {
                        $pricesArr[] = ['label'=>trim($parts[0]),'amount'=>'','sold_out'=>0];
                    }
                }
                $pricesJson = json_encode($pricesArr, JSON_UNESCAPED_UNICODE);
            }

            $city  = trim($d['city'] ?? '');
            $code  = strtoupper(trim($d['country_code'] ?? 'ES'));
            $alias = ApplicationHelper::stringURLSafe(($title?:$band).'-'.$city.'-'.$ds);

            if (empty($title)) $title = $band.($city?' — '.$city:'');

            $rec = [
                'title'        => $title,
                'alias'        => $alias,
                'band'         => $band,
                'headliners'   => $hlJson,
                'support_bands'=> trim($d['support_bands'] ?? ''),
                'genre_ids'    => $genreIds,
                'date_start'   => $ds,
                'date_end'     => $de,
                'concert_time' => trim($d['concert_time'] ?? ''),
                'doors_time'   => trim($d['doors_time'] ?? ''),
                'venue'        => trim($d['venue'] ?? ''),
                'city'         => $city,
                'province'     => trim($d['province'] ?? ''),
                'country_code' => $code,
                'country_name' => $cn[$code] ?? $code,
                'address'      => trim($d['address'] ?? ''),
                'prices'       => $pricesJson,
                'ticket_url'   => trim($d['ticket_url'] ?? ''),
                'website_url'  => trim($d['website_url'] ?? ''),
                'poster_url'   => trim($d['poster_url'] ?? ''),
                'description'  => trim($d['description'] ?? ''),
                'state'        => 1,
                'ordering'     => 0,
                'created'      => $now,
                'modified'     => $now,
                'created_by'   => $uid,
            ];

            try {
                $db->setQuery($db->getQuery(true)
                    ->insert($db->qn('#__concerts'))
                    ->columns(array_map([$db,'qn'], array_keys($rec)))
                    ->values(implode(',', array_map([$db,'q'], $rec)))
                )->execute();
                $ins++;
            } catch(\Exception $e) { $skip++; }
        }
        fclose($h);
        $app->enqueueMessage("✅ $ins importados, $skip omitidos.", 'success');
        $this->setRedirect(Route::_('index.php?option=com_concerts&view=concerts', false));
    }
}
