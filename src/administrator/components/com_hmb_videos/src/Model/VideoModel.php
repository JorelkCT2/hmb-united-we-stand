<?php
namespace Joomla\Component\HmbVideos\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class VideoModel extends AdminModel
{
    public $typeAlias = 'com_hmb_videos.video';

    public function getTable($name = 'Video', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($name, $prefix, $config);
    }

    protected function canEditState($record)
    {
        return Factory::getApplication()->getIdentity()->authorise('core.admin')
            || Factory::getApplication()->getIdentity()->authorise('core.edit.state', 'com_hmb_videos');
    }

    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm('com_hmb_videos.video', 'video', ['control' => 'jform', 'load_data' => $loadData]);
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_hmb_videos.edit.video.data', []);
        return empty($data) ? $this->getItem() : $data;
    }
}
