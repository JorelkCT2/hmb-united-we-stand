<?php
defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$basefolder = $params->get('basefolder', 'noticias');
$maximages  = (int) $params->get('maximages', 3);

$images = ModFotosNoticiaHelper::getImages($basefolder, $maximages);

require JModuleHelper::getLayoutPath('mod_fotos_noticia', 'default');
