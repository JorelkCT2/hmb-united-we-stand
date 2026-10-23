<?php
namespace Joomla\Component\HmbVideos\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Uri\Uri;

class FolderController extends FormController
{
    protected $view_list = 'folders';

    public function getModel($name = 'Folder', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    // Direct redirect helper - Uri::base() avoids Route::_ SEF transformation
    private function base(): string
    {
        return Uri::base();
    }

    public function add()
    {
        $this->checkToken('request');
        $this->app->setUserState('com_hmb_videos.edit.folder.data', null);
        $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=folder&layout=edit&id=0');
        return true;
    }

    public function edit($key = null, $urlVar = null)
    {
        // No checkToken - edit is a GET navigation, token not required
        $id = $this->input->getInt('id', 0);
        $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=folder&layout=edit&id=' . $id);
        return true;
    }

    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        $model = $this->getModel('Folder', 'Administrator');
        $data  = $this->input->post->get('jform', [], 'array');

        // Fix: ensure parent_id is an integer (not null/empty)
        if (!isset($data['parent_id']) || $data['parent_id'] === '' || $data['parent_id'] === null) {
            $data['parent_id'] = 0;
        }
        $data['parent_id'] = (int)$data['parent_id'];

        // Set timestamps
        $now = Factory::getDate()->toSql();
        if (empty($data['id']) || $data['id'] == 0) {
            $data['created']    = $now;
            $data['created_by'] = Factory::getUser()->id;
        }
        $data['modified']    = $now;
        $data['modified_by'] = Factory::getUser()->id;

        $form = $model->getForm($data, false);
        if (!$form) {
            $this->app->enqueueMessage($model->getError(), 'error');
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=folders');
            return false;
        }

        $validData = $model->validate($form, $data);
        if ($validData === false) {
            foreach ($model->getErrors() as $error) {
                $this->app->enqueueMessage($error instanceof \Exception ? $error->getMessage() : $error, 'warning');
            }
            $this->app->setUserState('com_hmb_videos.edit.folder.data', $data);
            $id = (int)($data['id'] ?? 0);
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=folder&layout=edit&id=' . $id);
            return false;
        }

        if (!$model->save($validData)) {
            $this->app->enqueueMessage($model->getError(), 'error');
            $id = (int)($validData['id'] ?? 0);
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=folder&layout=edit&id=' . $id);
            return false;
        }

        $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_SAVE_SUCCESS'));
        $this->app->setUserState('com_hmb_videos.edit.folder.data', null);

        // After save: where to go?
        $task = $this->getTask();
        if ($task === 'apply') {
            $id = $model->getState('folder.id', (int)($validData['id'] ?? 0));
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=folder&layout=edit&id=' . $id);
        } elseif ($task === 'save2new') {
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=folder&layout=edit&id=0');
        } else {
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=folders');
        }

        return true;
    }

    public function cancel($key = null)
    {
        $this->checkToken();
        $this->app->setUserState('com_hmb_videos.edit.folder.data', null);
        $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=folders');
        return true;
    }

    public function apply()
    {
        return $this->save();
    }
}
