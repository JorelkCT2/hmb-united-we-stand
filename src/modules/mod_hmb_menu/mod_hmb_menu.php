<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;

$app      = Factory::getApplication();
$moduleId = 'hmb-menu-' . $module->id;
$menuType = $params->get('menutype', 'mainmenu');
$active   = $app->getMenu()->getActive();
$activeId = $active ? $active->id : 0;

// Niveles de acceso del usuario actual
$user       = $app->getIdentity();
$viewLevels = $user->getAuthorisedViewLevels();
$levels     = implode(',', array_map('intval', $viewLevels));

$db    = Factory::getDbo();
$query = $db->getQuery(true)
    ->select('id, title, alias, link, type, level, parent_id, browserNav, access, params')
    ->from($db->quoteName('#__menu'))
    ->where($db->quoteName('menutype')  . ' = ' . $db->quote($menuType))
    ->where($db->quoteName('published') . ' = 1')
    ->where($db->quoteName('client_id') . ' = 0')
    ->where($db->quoteName('level')     . ' <= 2')
    ->where($db->quoteName('access')    . ' IN (' . $levels . ')')
    ->order($db->quoteName('lft') . ' ASC');

$db->setQuery($query);
$allItems = $db->loadObjectList() ?: [];

$topItems = [];
$children = [];

foreach ($allItems as $item) {
    if ($item->type === 'url') {
        $item->flink = $item->link;
    } elseif (in_array($item->type, ['separator', 'heading'])) {
        $item->flink = '#';
    } else {
        try {
            $item->flink = Route::_($item->link . '&Itemid=' . $item->id);
        } catch (\Exception $e) {
            $item->flink = '#';
        }
    }

    if ($item->level == 1) {
        $topItems[] = $item;
    } else {
        $children[$item->parent_id][] = $item;
    }
}

require ModuleHelper::getLayoutPath('mod_hmb_menu', 'default');
