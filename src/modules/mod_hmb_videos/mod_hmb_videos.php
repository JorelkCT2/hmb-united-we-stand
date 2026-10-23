<?php
defined('_JEXEC') or die;

use Joomla\Module\HmbVideos\Site\Helper\HmbVideosHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;

$helper  = new HmbVideosHelper();
$videos  = $helper->getVideos($params);
$folders = $helper->getFolders();

$doc = Factory::getApplication()->getDocument();
$doc->addStyleSheet('/media/com_hmb_videos/css/hmb-videos.css');
$doc->addScript('/media/com_hmb_videos/js/hmb-videos.js', ['version' => 'auto'], ['defer' => true]);

require ModuleHelper::getLayoutPath('mod_hmb_videos', $params->get('layout', 'default'));
