<?php
namespace Joomla\Component\HmbVideos\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Uri\Uri;

class VideoController extends FormController
{
    protected $view_list = 'videos';

    public function getModel($name = 'Video', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    private function base(): string
    {
        return Uri::base();
    }

    public function add()
    {
        $this->checkToken('request');
        $this->app->setUserState('com_hmb_videos.edit.video.data', null);
        $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=video&layout=edit&id=0');
        return true;
    }

    public function edit($key = null, $urlVar = null)
    {
        // No checkToken - edit is a GET navigation, token not required
        $id = $this->input->getInt('id', 0);
        $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=video&layout=edit&id=' . $id);
        return true;
    }

    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();

        $model = $this->getModel('Video', 'Administrator');
        $data  = $this->input->post->get('jform', [], 'array');

        // Fix: ensure folder_id is an integer
        if (!isset($data['folder_id']) || $data['folder_id'] === '' || $data['folder_id'] === null) {
            $data['folder_id'] = 0;
        }
        $data['folder_id'] = (int)$data['folder_id'];

        // Extract YouTube ID from full URL if needed
        if (!empty($data['youtube_id'])) {
            if (str_contains($data['youtube_id'], 'youtube.com')) {
                parse_str(parse_url($data['youtube_id'], PHP_URL_QUERY), $p);
                if (!empty($p['v'])) $data['youtube_id'] = $p['v'];
            } elseif (str_contains($data['youtube_id'], 'youtu.be')) {
                $data['youtube_id'] = basename(parse_url($data['youtube_id'], PHP_URL_PATH));
            }
        }

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
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=videos');
            return false;
        }

        $validData = $model->validate($form, $data);
        if ($validData === false) {
            foreach ($model->getErrors() as $error) {
                $this->app->enqueueMessage($error instanceof \Exception ? $error->getMessage() : $error, 'warning');
            }
            $this->app->setUserState('com_hmb_videos.edit.video.data', $data);
            $id = (int)($data['id'] ?? 0);
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=video&layout=edit&id=' . $id);
            return false;
        }

        if (!$model->save($validData)) {
            $this->app->enqueueMessage($model->getError(), 'error');
            $id = (int)($validData['id'] ?? 0);
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=video&layout=edit&id=' . $id);
            return false;
        }

        $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_SAVE_SUCCESS'));
        $this->app->setUserState('com_hmb_videos.edit.video.data', null);

        $task = $this->getTask();
        if ($task === 'apply') {
            $id = $model->getState('video.id', (int)($validData['id'] ?? 0));
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=video&layout=edit&id=' . $id);
        } elseif ($task === 'save2new') {
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=video&layout=edit&id=0');
        } else {
            $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=videos');
        }

        return true;
    }

    public function cancel($key = null)
    {
        $this->checkToken();
        $this->app->setUserState('com_hmb_videos.edit.video.data', null);
        $this->setRedirect($this->base() . 'index.php?option=com_hmb_videos&view=videos');
        return true;
    }

    public function apply()
    {
        return $this->save();
    }
}
