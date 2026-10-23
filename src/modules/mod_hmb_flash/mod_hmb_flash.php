<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

// No mostrar si está desactivado
if (!(int)$params->get('activo', 1)) {
    return;
}

// Parsear imagen
function hmb_flash_img($value): string
{
    if (empty($value)) return '';
    if (is_object($value)) return $value->imagefile ?? '';
    if (is_string($value)) {
        $dec = json_decode($value);
        if ($dec && isset($dec->imagefile)) return $dec->imagefile;
        return $value;
    }
    return '';
}

$tamano       = $params->get('tamano', 'medium');
$titulo       = htmlspecialchars(trim((string)$params->get('titulo', '')), ENT_QUOTES, 'UTF-8');
$texto        = htmlspecialchars(trim((string)$params->get('texto', '')), ENT_QUOTES, 'UTF-8');
$posicion     = $params->get('posicion', 'right');
$autoclose    = (int)$params->get('autoclose', 0);
$autoclose_s  = (int)$params->get('autoclose_seg', 8);
$overlay      = (int)$params->get('overlay', 1);
$sesion       = (int)$params->get('sesion', 1);

$imgPath      = hmb_flash_img($params->get('foto', ''));
$imgUrl       = '';
if ($imgPath) {
    $imgUrl = preg_match('/^https?:\/\//i', $imgPath)
        ? $imgPath
        : rtrim(Uri::root(), '/') . '/' . ltrim($imgPath, '/');
}

// Link de la imagen
$link = trim((string)$params->get('link', ''));
$link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

// Límites de caracteres
$limites = ['small' => 80, 'medium' => 150, 'large' => 250];
$charLimit = $limites[$tamano] ?? 150;

// Truncar texto si supera el límite
$textoRaw = trim((string)$params->get('texto', ''));
if (mb_strlen($textoRaw) > $charLimit) {
    $textoRaw = mb_substr($textoRaw, 0, $charLimit - 1) . '…';
}
$texto = htmlspecialchars($textoRaw, ENT_QUOTES, 'UTF-8');

// ID único para el módulo
$mid = 'hmb-flash-' . $module->id;

// Hash del contenido para la sesión (si cambia el contenido, vuelve a mostrarse)
$hash = substr(md5($titulo . $texto . $imgUrl . $tamano), 0, 8);

require ModuleHelper::getLayoutPath('mod_hmb_flash', $params->get('layout', 'default'));
