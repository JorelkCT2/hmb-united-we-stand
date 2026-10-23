<?php
namespace Joomla\Component\HmbVideos\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Uri\Uri;

class FoldersController extends AdminController
{
    protected $text_prefix = 'COM_HMB_VIDEOS_FOLDER';

    public function getModel($name = 'Folder', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    private function listUrl(): string
    {
        return Uri::base() . 'index.php?option=com_hmb_videos&view=folders';
    }

    public function delete()
    {
        $this->checkToken();
        $ids = array_filter((array) $this->input->get('cid', [], 'int'));

        if (empty($ids)) {
            $this->app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'warning');
            $this->setRedirect($this->listUrl());
            return false;
        }

        $model = $this->getModel('Folder', 'Administrator');
        if ($model->delete($ids)) {
            $this->app->enqueueMessage(Text::plural('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED', count($ids)));
        } else {
            $this->app->enqueueMessage(Text::sprintf('JLIB_APPLICATION_SAVE_SUCCESS'));
        }

        $this->setRedirect($this->listUrl());
        return true;
    }

    public function publish()
    {
        $this->checkToken();
        $ids = array_filter((array) $this->input->get('cid', [], 'int'));
        $model = $this->getModel('Folder', 'Administrator');
        $model->publish($ids, 1);
        $this->setRedirect($this->listUrl());
        return true;
    }

    public function unpublish()
    {
        $this->checkToken();
        $ids = array_filter((array) $this->input->get('cid', [], 'int'));
        $model = $this->getModel('Folder', 'Administrator');
        $model->publish($ids, 0);
        $this->setRedirect($this->listUrl());
        return true;
    }
}
