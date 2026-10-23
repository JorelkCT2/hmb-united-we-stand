<?php
namespace Joomla\Component\Artistas\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

class RouteHelper
{
    private static $itemids = [];

    /**
     * Devuelve la URL correcta para una vista del componente,
     * buscando el menu item correspondiente para que Gantry5 funcione.
     */
    public static function getUrl(string $view, array $params = []): string
    {
        $itemid = self::getItemid($view);
        $url = 'index.php?option=com_artistas&view=' . $view;
        if ($itemid) {
            $url .= '&Itemid=' . $itemid;
        }
        foreach ($params as $k => $v) {
            $url .= '&' . $k . '=' . urlencode($v);
        }
        return $url;
    }

    /**
     * Busca el Itemid del menu item que corresponde a una vista.
     */
    public static function getItemid(string $view): int
    {
        if (isset(self::$itemids[$view])) {
            return self::$itemids[$view];
        }

        $menu = Factory::getApplication()->getMenu();
        try {
            $compId = ComponentHelper::getComponent('com_artistas')->id;
            $items  = $menu->getItems('component_id', $compId);
        } catch (\Exception $e) {
            return 0;
        }

        foreach ((array)$items as $item) {
            // Exact match: view=artista must not match view=artistas
            $link = $item->link;
            if (strpos($link, 'view=' . $view . '&') !== false
                || strpos($link, 'view=' . $view . ' ') !== false
                || substr($link, -strlen('view=' . $view)) === 'view=' . $view) {
                self::$itemids[$view] = (int)$item->id;
                return (int)$item->id;
            }
        }

        self::$itemids[$view] = 0;
        return 0;
    }
}
