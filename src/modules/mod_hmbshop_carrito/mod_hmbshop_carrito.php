<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$wa = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('hmbshop.core', 'media/com_hmbshop/css/phoca-core.css', ['version' => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/css/phoca-core.css') ?: 1)]);

require ModuleHelper::getLayoutPath('mod_hmbshop_carrito', $params->get('layout', 'default'));
