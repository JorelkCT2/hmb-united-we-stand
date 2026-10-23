<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

require_once __DIR__ . '/src/Helper/EfemeridesHelper.php';

$limite = (int)$params->get('limite', 5);

// Leer las categorías seleccionadas (puede ser array, string separada por comas, o vacío)
$rawCats = $params->get('categorias', []);

// Normalizar a array de enteros
if (is_string($rawCats) && $rawCats !== '') {
    $catIds = array_map('intval', explode(',', $rawCats));
} elseif (is_array($rawCats)) {
    $catIds = array_map('intval', $rawCats);
} else {
    $catIds = [];
}

// Filtrar ceros y valores inválidos
$catIds = array_filter($catIds, function($id){ return $id > 0; });
$catIds = array_values($catIds);

$helper     = new \Joomla\Module\Efemerides\Helper\EfemeridesHelper();
$efemerides = $helper->getProximas($limite, $catIds);

require ModuleHelper::getLayoutPath('mod_efemerides', $params->get('layout', 'default'));
