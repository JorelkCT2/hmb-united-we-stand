<?php
namespace Joomla\Component\Concerts\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Concerts\Administrator\Helper\SyncHelper;

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

    /** Procesar CSV subido (mismo motor que las fuentes: detecta duplicados y rellena huecos) */
    public function upload(): void
    {
        $this->checkToken();
        $app  = Factory::getApplication();
        $file = $this->input->files->get('csv_file', [], 'array');
        $volver = Route::_('index.php?option=com_concerts&view=import', false);

        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $app->enqueueMessage('No se recibió ningún CSV válido.', 'error');
            $this->setRedirect($volver);

            return;
        }

        $eventos = SyncHelper::leerCsv((string) file_get_contents($file['tmp_name']), $this->input->getString('delimiter', 'auto'));

        if (!$eventos) {
            $app->enqueueMessage('El CSV está vacío o no tiene filas válidas (hacen falta título y fecha de inicio).', 'error');
            $this->setRedirect($volver);

            return;
        }

        $publicar = $this->input->getInt('publicar', 1) === 1;
        $c = ['nuevo' => 0, 'actualizado' => 0, 'igual' => 0];
        $err = 0;

        foreach ($eventos as $e) {
            try {
                $c[SyncHelper::guardar($e, 'csv', $publicar)]++;
            } catch (\Throwable $ex) {
                $err++;
            }
        }

        $app->enqueueMessage($c['nuevo'] . ' eventos nuevos, ' . $c['actualizado'] . ' actualizados, ' . $c['igual'] . ' ya existían' . ($err ? ', ' . $err . ' con error' : '') . '.', $err ? 'warning' : 'message');
        $this->setRedirect($volver);
    }
}
