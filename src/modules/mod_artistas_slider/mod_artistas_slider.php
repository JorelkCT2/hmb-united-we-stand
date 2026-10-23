<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

$db     = Factory::getContainer()->get('DatabaseDriver');
$params = new Registry($module->params);

$ordenar       = $params->get('ordenar', 'hits');
$limite        = (int)$params->get('limite', 12);
$soloPubl      = (int)$params->get('solo_publicados', 1);
$autoplay      = (int)$params->get('autoplay', 1);
$velocidad     = (int)$params->get('velocidad', 3000);
$cardsVisibles = max(2, (int)$params->get('cards_visibles', 5));

$orderMap = [
    'hits'    => 'a.id DESC',
    'created' => 'a.created DESC',
    'name'    => 'a.name ASC',
    'rating'  => 'a.rating DESC',
    'random'  => 'RAND()',
];
$order = $orderMap[$ordenar] ?? 'a.hits DESC';
$where = $soloPubl ? 'a.state=1' : 'a.state IN (0,1)';

$db->setQuery("SELECT a.id, a.name, a.image, a.genre
    FROM #__artistas a WHERE $where ORDER BY $order LIMIT " . (int)$limite);
$artistas = $db->loadObjectList() ?: [];

// Find artista menu item Itemid
$fichaItemid = 0;
try {
    $menu  = Factory::getApplication()->getMenu();
    $items = $menu->getItems('component', 'com_artistas');
    foreach ((array)$items as $mitem) {
        if (strpos($mitem->link ?? '', 'view=artista') !== false) {
            $fichaItemid = (int)$mitem->id;
            break;
        }
    }
} catch (\Exception $e) {}

$moduleId = 'mas-' . (int)$module->id;

require ModuleHelper::getLayoutPath('mod_artistas_slider', 'default');
