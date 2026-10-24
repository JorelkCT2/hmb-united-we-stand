<?php
namespace Joomla\Component\Concerts\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Concerts\Administrator\Helper\SyncHelper;

/**
 * Punto de entrada para la tarea programada del hosting (cron):
 *   index.php?option=com_concerts&task=sync.cron&key=LA_CLAVE
 * Lee todas las fuentes activas y situa en el mapa los eventos sin coordenadas.
 * Sin clave configurada en las opciones del componente, queda desactivado.
 */
class SyncController extends BaseController
{
    public function cron(): void
    {
        $app    = Factory::getApplication();
        $esperada = trim((string) SyncHelper::config()->get('cron_key', ''));
        $dada     = (string) $this->input->getString('key', '');

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if ($esperada === '' || !hash_equals($esperada, $dada)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'clave no válida o tarea desactivada']);
            $app->close();

            return;
        }

        try {
            $r = SyncHelper::ejecutarTodas();
            echo json_encode(['ok' => true] + $r, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }

        $app->close();
    }
}
