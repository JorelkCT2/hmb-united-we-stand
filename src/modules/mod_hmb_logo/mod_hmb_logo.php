<?php
/**
 * HMB Logo Module
 * @package  HeavyMetalBrothers.Module.HmbLogo
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

$app      = Factory::getApplication();
$doc      = $app->getDocument();
$mediaUrl = Uri::root(true) . '/media/mod_hmb_logo/';
$moduleId = 'hmb-logo-' . $module->id;

// Params
$logoImage    = $params->get('logo_image', '');
$logoAlt      = $params->get('logo_alt', $app->get('sitename'));
$logoLink     = $params->get('logo_link', Uri::root());
$logoHeight   = (int) $params->get('logo_height', 80);
$logoHeightMd = (int) $params->get('logo_height_tablet', 60);
$logoHeightSm = (int) $params->get('logo_height_mobile', 44);
$effect       = $params->get('effect', 'glow');
$openNewTab   = (bool) $params->get('open_new_tab', 0);
$showDesktop  = (int) $params->get('show_desktop', 1);
$showTablet   = (int) $params->get('show_tablet', 1);
$showMobile   = (int) $params->get('show_mobile', 1);

// Normalizar ruta de imagen
if (is_object($logoImage) && isset($logoImage->path)) {
    $logoImage = $logoImage->path;
}
$logoImage = is_string($logoImage) ? $logoImage : '';

if (!empty($logoImage)) {
    // Si es ruta relativa, añadir base URL
    if (strpos($logoImage, 'http') !== 0) {
        $logoImageUrl = Uri::root() . ltrim($logoImage, '/');
    } else {
        $logoImageUrl = $logoImage;
    }
} else {
    $logoImageUrl = '';
}

$doc->addStyleSheet($mediaUrl . 'css/mod_hmb_logo.css', ['version' => 'auto']);
$doc->addScript($mediaUrl . 'js/mod_hmb_logo.js', ['version' => 'auto'], ['defer' => true]);

// Inyectar alturas directamente con !important — evita conflictos con CSS externo y template
$visibilityCss = '';
if (!$showDesktop) {
    $visibilityCss .= "@media (min-width: 1025px) { #$moduleId { display:none!important; } }\n";
}
if (!$showTablet) {
    $visibilityCss .= "@media (min-width: 768px) and (max-width: 1024px) { #$moduleId { display:none!important; } }\n";
}
if (!$showMobile) {
    $visibilityCss .= "@media (max-width: 767px) { #$moduleId { display:none!important; } }\n";
}

$doc->addStyleDeclaration("
#$moduleId {
  --logo-h:    {$logoHeight}px;
  --logo-h-md: {$logoHeightMd}px;
  --logo-h-sm: {$logoHeightSm}px;
}
#$moduleId .hmb-logo-img,
#$moduleId .hmb-logo-img-mirror {
  height: {$logoHeight}px !important;
  max-height: {$logoHeight}px !important;
  min-height: 0 !important;
  width: auto !important;
}
@media (max-width: 1024px) {
  #$moduleId .hmb-logo-img,
  #$moduleId .hmb-logo-img-mirror {
    height: {$logoHeightMd}px !important;
    max-height: {$logoHeightMd}px !important;
  }
}
@media (max-width: 767px) {
  #$moduleId .hmb-logo-img,
  #$moduleId .hmb-logo-img-mirror {
    height: {$logoHeightSm}px !important;
    max-height: {$logoHeightSm}px !important;
  }
}
$visibilityCss
");

require ModuleHelper::getLayoutPath('mod_hmb_logo', $params->get('layout', 'default'));
