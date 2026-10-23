<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Module\HmbTemas\Site\Helper\HmbTemasHelper;
use Joomla\CMS\Helper\ModuleHelper;

$temas  = HmbTemasHelper::getTemas($params);
$albums = HmbTemasHelper::getAlbums($temas);

require ModuleHelper::getLayoutPath('mod_hmbtemas', $params->get('layout', 'default'));
