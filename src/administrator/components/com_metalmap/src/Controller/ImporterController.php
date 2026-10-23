<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

final class ImporterController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }

    public function upload(): void
    {
        $this->checkToken();

        $app   = $this->app;
        $files = $app->getInput()->files;
        $file  = $files->get('csv_file', null, 'raw');

        if (!$file || empty($file['tmp_name']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
            $app->enqueueMessage('No se ha subido ningún archivo o hubo error en la subida (código: ' . ($file['error'] ?? '?') . ')', 'error');
            $app->redirect(Route::_('index.php?option=com_metalmap&view=importer', false));
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'], true)) {
            $app->enqueueMessage('Archivo no permitido. Sube .csv o .txt', 'error');
            $app->redirect(Route::_('index.php?option=com_metalmap&view=importer', false));
            return;
        }

        $tmpDir = $app->get('tmp_path', JPATH_ROOT . '/tmp');
        $dest   = $tmpDir . '/metalmap_import_' . bin2hex(random_bytes(8)) . '.csv';

        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            $app->enqueueMessage('Error moviendo el archivo subido a ' . $tmpDir, 'error');
            $app->redirect(Route::_('index.php?option=com_metalmap&view=importer', false));
            return;
        }

        $delimiter      = $app->getInput()->getString('delimiter', ',');
        $updateExisting = (bool) $app->getInput()->getInt('update_existing', 1);

        /** @var \Joomla\Component\Metalmap\Administrator\Model\ImporterModel $model */
        $model  = $this->getModel('Importer', 'Administrator');
        $result = $model->import($dest, $updateExisting, $delimiter === ';' ? ';' : ',');

        @unlink($dest);

        // Mensaje principal
        $msg = sprintf('🤘 Importación: %d nuevos, %d actualizados.', $result['inserted'], $result['updated']);
        $type = ($result['inserted'] + $result['updated']) > 0 ? 'success' : 'warning';
        $app->enqueueMessage($msg, $type);

        // Mostrar líneas de debug
        if (!empty($result['debug'])) {
            foreach ($result['debug'] as $d) {
                $app->enqueueMessage('ℹ️ ' . $d, 'info');
            }
        }

        // Mostrar TODOS los errores (sin recortar) para diagnosticar
        if (!empty($result['errors'])) {
            foreach (array_slice($result['errors'], 0, 30) as $err) {
                $app->enqueueMessage('⚠️ ' . $err, 'warning');
            }
            if (count($result['errors']) > 30) {
                $app->enqueueMessage('...y ' . (count($result['errors']) - 30) . ' errores más.', 'warning');
            }
        }

        $app->redirect(Route::_('index.php?option=com_metalmap&view=festivals', false));
    }
}
