<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

/**
 * Procesa el valor de un campo media de Joomla 6
 * Puede llegar como string plano, JSON string u objeto
 */
function hmb_carrusel_parse_media($value)
{
    if (empty($value)) {
        return '';
    }
    // Si es objeto (Joomla lo ha decodificado ya)
    if (is_object($value)) {
        return isset($value->imagefile) ? $value->imagefile : '';
    }
    // Si es string, intentar JSON
    if (is_string($value)) {
        $decoded = json_decode($value);
        if ($decoded !== null && isset($decoded->imagefile)) {
            return $decoded->imagefile;
        }
        // String plano (ruta o vacío)
        return $value;
    }
    return '';
}

// Recopilar las fotos de los params
$fotos = [];

for ($i = 1; $i <= 10; $i++) {
    $prefix = 'f' . $i;

    $mediaSrc = $params->get($prefix . '_media', '');
    $urlSrc   = trim($params->get($prefix . '_url', ''));
    $titulo   = htmlspecialchars(trim($params->get($prefix . '_titulo', '')), ENT_QUOTES, 'UTF-8');

    // Resolver URL de la imagen
    $src = '';

    $mediaPath = hmb_carrusel_parse_media($mediaSrc);
    if (!empty($mediaPath)) {
        // Ruta relativa a la raíz de Joomla
        $src = Uri::root() . ltrim($mediaPath, '/');
    } elseif (!empty($urlSrc)) {
        $src = $urlSrc;
    }

    if (!empty($src)) {
        $fotos[] = [
            'src'    => htmlspecialchars($src, ENT_QUOTES, 'UTF-8'),
            'titulo' => $titulo,
            'index'  => count($fotos),
        ];
    }
}

// Configuración general
$cfg = [
    'autoplay'     => (int) $params->get('autoplay', 1),
    'interval'     => (int) $params->get('interval', 4000),
    'altura'       => (int) $params->get('altura', 420),
    'show_thumbs'  => (int) $params->get('show_thumbs', 1),
    'show_caption' => (int) $params->get('show_caption', 1),
    'modid'        => 'hmb-car-' . $module->id,
];

require ModuleHelper::getLayoutPath('mod_hmb_carrusel', $params->get('layout', 'default'));
