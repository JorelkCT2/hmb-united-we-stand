<?php
namespace Joomla\Module\Efemerides\Helper;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

class EfemeridesHelper
{
    private static string $musicoBase = '';
    private static string $bandaBase  = '';
    private static bool   $basesReady = false;

    private function computeBases(): void
    {
        if (self::$basesReady) return;
        self::$basesReady = true;

        $root = rtrim(Uri::root(), '/');

        try {
            $menu  = Factory::getApplication()->getMenu();
            $comId = ComponentHelper::getComponent('com_efemerides')->id;
            $items = $menu->getItems('component_id', $comId);

            foreach ($items as $mi) {
                $route = trim((string)($mi->route ?? ''), '/');
                if ($route === '') continue;   // saltar raíz y vacíos

                $url  = $root . '/' . $route;
                $link = (string)($mi->link ?? '');

                // Primera ruta válida como fallback para ambos
                if (self::$musicoBase === '') self::$musicoBase = $url;
                if (self::$bandaBase  === '') self::$bandaBase  = $url;

                if (strpos($link, 'view=musicos') !== false) self::$musicoBase = $url;
                if (strpos($link, 'view=bandas')  !== false) self::$bandaBase  = $url;
            }
        } catch (\Throwable $e) {}

        // Fallback si no se encontró nada
        if (self::$musicoBase === '') self::$musicoBase = $root . '/index.php?option=com_efemerides';
        if (self::$bandaBase  === '') self::$bandaBase  = $root . '/index.php?option=com_efemerides';
    }

    public function getMusicoUrl(int $id): string
    {
        $this->computeBases();
        $sep = strpos(self::$musicoBase, '?') !== false ? '&' : '?';
        return self::$musicoBase . $sep . 'view=musico&id=' . $id;
    }

    public function getBandaUrl(int $id): string
    {
        $this->computeBases();
        $sep = strpos(self::$bandaBase, '?') !== false ? '&' : '?';
        return self::$bandaBase . $sep . 'view=banda&id=' . $id;
    }

    public function getDebugBase(): string
    {
        $this->computeBases();
        return self::$musicoBase;
    }

    public function getMusicosFoto(): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('SELECT id, nombre, foto FROM #__efemerides_musicos WHERE state=1');
        $map = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $map[mb_strtolower(trim($row->nombre))] = [
                'id'   => (int)$row->id,
                'foto' => (string)($row->foto ?? ''),
                'url'  => $this->getMusicoUrl((int)$row->id),
            ];
        }
        return $map;
    }

    public function getBandasFoto(): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('SELECT id, nombre, foto FROM #__efemerides_bandas WHERE state=1');
        $map = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $map[mb_strtolower(trim($row->nombre))] = [
                'id'   => (int)$row->id,
                'foto' => (string)($row->foto ?? ''),
                'url'  => $this->getBandaUrl((int)$row->id),
            ];
        }
        return $map;
    }

    public function getProximas(int $limit = 5, array $catIds = []): array
    {
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $hoy    = new \DateTime('today');
        $refDoy = (int)$hoy->format('z');

        $query = $db->getQuery(true);
        $query->select(['e.*', 'c.name AS categoria_nombre'])
              ->from($db->quoteName('#__efemerides', 'e'))
              ->join('LEFT',
                  $db->quoteName('#__efemerides_categorias', 'c') .
                  ' ON c.id = e.categoria_id')
              ->where('e.state = 1');

        if (!empty($catIds)) {
            $in = implode(',', array_map('intval', $catIds));
            $query->where("e.categoria_id IN ({$in})");
        }

        $db->setQuery($query);
        $all = $db->loadObjectList() ?: [];
        if (empty($all)) return [];

        $seen = []; $unique = [];
        foreach ($all as $item) {
            $d   = new \DateTime((string)$item->fecha_evento);
            $key = mb_strtolower(trim((string)($item->artista ?? ''))) . '|' . $d->format('m-d');
            if (!isset($seen[$key])) { $seen[$key] = true; $unique[] = $item; }
        }

        foreach ($unique as $item) {
            $d    = new \DateTime((string)$item->fecha_evento);
            $doy  = (int)$d->format('z');
            $diff = $doy - $refDoy;
            if ($diff < -182) $diff += 365;
            if ($diff > 182)  $diff -= 365;
            $item->_diff_dias = $diff;
        }

        usort($unique, function($a, $b) {
            return abs($a->_diff_dias) - abs($b->_diff_dias);
        });

        return array_slice($unique, 0, $limit);
    }
}
