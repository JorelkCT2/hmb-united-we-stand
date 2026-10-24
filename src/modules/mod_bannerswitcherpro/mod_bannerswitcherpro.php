<?php
/**
 * HMB Banner Switcher Pro v3
 * @package  HeavyMetalBrothers.Module.BannerSwitcherPro
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/helper.php';

$slides   = ModBannerSwitcherProHelper::getSlides($params);
$doc      = Factory::getApplication()->getDocument();
$mediaUrl = Uri::root(true) . '/media/mod_bannerswitcherpro/';
$moduleId = 'hmb-slider-' . $module->id;

// Version = fecha del fichero: sin esto el navegador se quedaba con el CSS/JS antiguo tras cada cambio
$vCss = (string) (@filemtime(JPATH_ROOT . '/media/mod_bannerswitcherpro/css/mod_bannerswitcherpro.css') ?: 1);
$vJs  = (string) (@filemtime(JPATH_ROOT . '/media/mod_bannerswitcherpro/js/mod_bannerswitcherpro.js') ?: 1);

$doc->addStyleSheet($mediaUrl . 'css/mod_bannerswitcherpro.css', ['version' => $vCss]);
$doc->addScript($mediaUrl . 'js/mod_bannerswitcherpro.js', ['version' => $vJs], ['defer' => true]);

require ModuleHelper::getLayoutPath('mod_bannerswitcherpro', $params->get('layout', 'default'));
