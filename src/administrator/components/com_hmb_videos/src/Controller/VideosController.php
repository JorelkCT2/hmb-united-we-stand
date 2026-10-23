<?php
namespace Joomla\Component\HmbVideos\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

class VideosController extends AdminController
{
    protected $text_prefix = 'COM_HMB_VIDEOS_VIDEO';

    public function getModel($name = 'Video', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    private function listUrl(): string
    {
        return Uri::base() . 'index.php?option=com_hmb_videos&view=videos';
    }

    public function delete()
    {
        $this->checkToken('request');
        $ids = array_filter((array) $this->input->get('cid', [], 'int'));
        if (!empty($ids)) {
            $this->getModel('Video', 'Administrator')->delete($ids);
        }
        $this->setRedirect($this->listUrl());
        return true;
    }

    public function publish()
    {
        $this->checkToken('request');
        $ids = array_filter((array) $this->input->get('cid', [], 'int'));
        $this->getModel('Video', 'Administrator')->publish($ids, 1);
        $this->setRedirect($this->listUrl());
        return true;
    }

    public function unpublish()
    {
        $this->checkToken('request');
        $ids = array_filter((array) $this->input->get('cid', [], 'int'));
        $this->getModel('Video', 'Administrator')->publish($ids, 0);
        $this->setRedirect($this->listUrl());
        return true;
    }

    public function toggleMobile()
    {
        $id    = $this->input->getInt('id', 0);
        $value = $this->input->getInt('mobile_value', 0);

        if ($id > 0) {
            $db = Factory::getDbo();
            $db->setQuery(
                'UPDATE `#__hmb_videos` SET `mobile` = ' . (int)$value .
                ' WHERE `id` = ' . (int)$id
            )->execute();
        }

        $this->setRedirect($this->listUrl());
        return true;
    }
}
