<?php
defined('_JEXEC') or die;
use Joomla\CMS\Helper\ModuleHelper;

require_once __DIR__ . '/src/Helper/FrasesHelper.php';

$autorFiltro    = trim((string)$params->get('autor_filtro', ''));
$helper         = new \Joomla\Module\FrasesRockeras\Helper\FrasesHelper();
$todasLasFrases = $helper->getTodasLasFrases($autorFiltro ?: null);

require ModuleHelper::getLayoutPath('mod_frases_rockeras', $params->get('layout', 'default'));
