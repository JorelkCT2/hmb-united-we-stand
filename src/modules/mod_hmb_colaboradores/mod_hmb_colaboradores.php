<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

$root = rtrim(Uri::root(), '/');

function hmb_colab_img(string $root, $val): string
{
    if (empty($val)) return '';
    if (is_object($val)) $val = $val->imagefile ?? '';
    if (is_string($val)) {
        $d = json_decode($val);
        if ($d && isset($d->imagefile)) $val = $d->imagefile;
    }
    $val = trim((string)$val);
    if (!$val) return '';
    return preg_match('/^https?:\/\//i', $val) ? $val : $root . '/' . ltrim($val, '/');
}

$titulo        = (string)$params->get('titulo', 'Colaboradores');
$mostrarTitulo = (int)$params->get('mostrar_titulo', 1);
$efectoGris    = (int)$params->get('efecto_gris', 1);
$tamano        = (int)$params->get('tamano_logo', 90);

$colaboradoresRaw = $params->get('colaboradores', []);
$colaboradores = [];

if (!empty($colaboradoresRaw)) {
    foreach ($colaboradoresRaw as $item) {
        $logoUrl = hmb_colab_img($root, $item->logo ?? '');
        if (!$logoUrl) continue;
        $colaboradores[] = [
            'logo'   => $logoUrl,
            'nombre' => (string)($item->nombre ?? ''),
            'url'    => trim((string)($item->url ?? '')),
        ];
    }
}

require ModuleHelper::getLayoutPath('mod_hmb_colaboradores', $params->get('layout', 'default'));
