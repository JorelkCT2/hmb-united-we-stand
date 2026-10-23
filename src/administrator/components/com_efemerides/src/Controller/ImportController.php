<?php
namespace Joomla\Component\Efemerides\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

class ImportController extends BaseController
{
    public function display($cachable = false, $urlparams = []): static
    {
        $this->input->set('view', 'import');
        return parent::display($cachable, $urlparams);
    }

    public function process(): void
    {
        $this->checkToken();
        $app = Factory::getApplication();
        $file = $this->input->files->get('csv_file', null, 'raw');

        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $app->enqueueMessage(Text::_('COM_EFEMERIDES_IMPORT_ERROR_FILE'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_efemerides&view=import', false));
            return;
        }

        /** @var \Joomla\Component\Efemerides\Administrator\Model\ImportModel $model */
        $model = $this->getModel('Import', 'Administrator');
        $result = $model->importCsv($file['tmp_name']);

        if ($result['success']) {
            $app->enqueueMessage(
                Text::sprintf('COM_EFEMERIDES_IMPORT_SUCCESS', $result['imported'], $result['skipped']),
                'success'
            );
        } else {
            $app->enqueueMessage($result['message'], 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_efemerides&view=efemerides', false));
    }
}
