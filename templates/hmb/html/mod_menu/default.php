<?php
/**
 * HMB Template Override — mod_menu/default.php
 * Menú horizontal limpio sin "Más acerca de"
 */
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

// Renderiza la lista de items recursivamente
function hmbRenderMenu($items, $level = 1) {
    if (empty($items)) return;
    $class = $level === 1 ? 'hmb-nav-list' : 'hmb-nav-dropdown';
    echo '<ul class="' . $class . '">';
    foreach ($items as $item) {
        // Ignorar separadores y encabezados
        if ($item->type === 'separator' || $item->type === 'heading') continue;

        $hasChildren = !empty($item->children);
        $liClass = [];
        if ($item->active || $item->current) $liClass[] = 'active';
        if ($hasChildren) $liClass[] = 'has-children';

        echo '<li class="' . implode(' ', $liClass) . '">';

        // Enlace principal
        if ($item->type === 'url' || $item->type === 'component' || $item->type === 'alias') {
            $url = $item->flink ?? '#';
            $target = !empty($item->browserNav) && $item->browserNav == 1 ? ' target="_blank" rel="noopener"' : '';
            $ariaCurrent = ($item->current || $item->active) ? ' aria-current="page"' : '';
            echo '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '"' . $target . $ariaCurrent . '>';
            echo htmlspecialchars($item->title, ENT_QUOTES);
            if ($hasChildren) echo '<span class="hmb-nav-caret" aria-hidden="true">▾</span>';
            echo '</a>';
        } else {
            // Tipo externo o especial
            echo '<a href="' . htmlspecialchars($item->flink ?? '#', ENT_QUOTES) . '">';
            echo htmlspecialchars($item->title, ENT_QUOTES);
            if ($hasChildren) echo '<span class="hmb-nav-caret" aria-hidden="true">▾</span>';
            echo '</a>';
        }

        // Submenú — sin el "Más acerca de" padre
        if ($hasChildren) {
            hmbRenderMenu($item->children, $level + 1);
        }

        echo '</li>';
    }
    echo '</ul>';
}

// Construir árbol de items desde $list (variable de Joomla)
// $list viene del módulo como array plano con ->level y ->parent_id
$tree = [];
$indexed = [];

foreach ($list as $item) {
    $item->children = [];
    $indexed[$item->id] = $item;
}

foreach ($indexed as $item) {
    if ($item->level > 1 && isset($indexed[$item->parent_id])) {
        $indexed[$item->parent_id]->children[] = $item;
    } else {
        $tree[] = $item;
    }
}

hmbRenderMenu($tree, 1);
