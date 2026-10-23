<?php
namespace Joomla\Module\FrasesRockeras\Helper;
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class FrasesHelper
{
    private static ?string $base = null;

    public function getTodasLasFrases(?string $autor = null): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $q  = $db->getQuery(true)
            ->select(['f.id','f.frase','f.autor','f.fecha_nacimiento','f.fecha_defuncion',
                      'm.id AS musico_id','m.foto AS musico_foto','m.instrumento AS musico_instrumento'])
            ->from($db->quoteName('#__efemerides_frases', 'f'))
            ->join('LEFT', $db->quoteName('#__efemerides_musicos', 'm') . ' ON m.nombre = f.autor AND m.state = 1')
            ->where('f.state = 1')
            ->order('RAND()');
        if ($autor !== null) {
            $q->where('f.autor = ' . $db->quote($autor));
        }
        $db->setQuery($q);
        $rows = $db->loadObjectList() ?: [];

        if (self::$base === null) {
            self::$base = $this->findBase();
        }
        $root = rtrim(Uri::root(), '/');

        foreach ($rows as $row) {
            $row->musico_url = '';
            $row->foto_url   = '';
            $mid = (int)($row->musico_id ?? 0);
            if ($mid > 0) {
                $row->musico_url = self::$base . '?view=musico&id=' . $mid;
                $foto = trim((string)($row->musico_foto ?? ''));
                if ($foto !== '') {
                    $row->foto_url = strpos($foto, 'http') === 0 ? $foto : $root . '/' . ltrim($foto, '/');
                }
            }
        }
        return $rows;
    }

    private function findBase(): string
    {
        $root = rtrim(Uri::root(), '/');
        try {
            $app   = Factory::getApplication();
            $menu  = $app->getMenu();
            $comId = \Joomla\CMS\Component\ComponentHelper::getComponent('com_efemerides')->id;
            $items = $menu->getItems('component_id', $comId);
            if (!empty($items)) {
                return $root . '/' . ltrim((string)$items[0]->route, '/');
            }
        } catch (\Throwable $e) {}
        return $root . '/index.php?option=com_efemerides';
    }
}
