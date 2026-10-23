<?php
/**
 * @package     mod_sorteo
 * @author      MuJG
 * @license     GNU/GPL v2 or later
 * @version     1.0.1
 * @since       Joomla 6.0 / PHP 8.1
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

// Carga explícita del helper.
// Los módulos de terceros en Joomla 4/5/6 no siempre registran su namespace
// en el autoloader PSR-4 hasta que se limpia la caché. Esta línea lo garantiza.
require_once __DIR__ . '/src/Helper/ModSorteoHelper.php';

use MuJG\Module\Sorteo\Site\Helper\ModSorteoHelper;

/** @var \Joomla\CMS\Application\SiteApplication $app */
$app = Factory::getApplication();

// Cargar usuarios participantes
// $params y $module los inyecta Joomla automáticamente en el scope del módulo
$usuarios = ModSorteoHelper::getUsuarios($params);

// Registrar y cargar assets (CSS + JS) via WebAssetManager
$wa = $app->getDocument()->getWebAssetManager();

$wa->registerAndUseStyle(
    'mod_sorteo.style',
    'media/mod_sorteo/css/sorteo.css',
    ['version' => '1.0.1']
);

$wa->registerAndUseScript(
    'mod_sorteo.script',
    'media/mod_sorteo/js/sorteo.js',
    ['version' => '1.0.1'],
    ['defer' => true]
);

// Cargar el layout del módulo
require ModuleHelper::getLayoutPath('mod_sorteo', $params->get('layout', 'default'));
