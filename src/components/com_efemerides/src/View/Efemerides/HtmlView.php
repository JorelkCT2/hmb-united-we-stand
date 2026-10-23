<?php
namespace Joomla\Component\Efemerides\Site\View\Efemerides;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public array  $efemeridesHoy = [];
    public array  $categorias    = [];
    public int    $catFiltro     = 0;
    public bool   $esModoHoy     = true;
    public array  $musicosMap    = [];  // nombre → id
    public array  $musicosFoto   = [];  // nombre → foto
    public array  $bandasMap     = [];  // nombre → id
    public array  $bandasFoto    = [];  // nombre → logo
    public string $musicoBase    = '';
    public string $bandaBase     = '';

    public function display($tpl = null): void
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $hoy   = new \DateTime('today');
        $this->catFiltro = (int)$app->getInput()->getInt('cat', 0);

        $db->setQuery('SELECT id, name FROM #__efemerides_categorias WHERE state=1 ORDER BY ordering ASC');
        $this->categorias = $db->loadObjectList() ?: [];

        // Mapa músicos y bandas para enlazar + fotos
        $db->setQuery('SELECT id, nombre, foto FROM #__efemerides_musicos WHERE state=1');
        foreach ($db->loadObjectList() ?: [] as $row) {
            $key = mb_strtolower($row->nombre);
            $this->musicosMap[$key] = (int)$row->id;
            $this->musicosFoto[$key] = (string)($row->foto ?? '');
        }
        $db->setQuery('SELECT id, nombre, foto AS logo FROM #__efemerides_bandas WHERE state=1');
        foreach ($db->loadObjectList() ?: [] as $row) {
            $key = mb_strtolower($row->nombre);
            $this->bandasMap[$key] = (int)$row->id;
            $this->bandasFoto[$key] = (string)($row->logo ?? '');
        }

        $q = $db->getQuery(true)
            ->select(['e.*','c.name AS categoria_nombre'])
            ->from($db->quoteName('#__efemerides','e'))
            ->join('LEFT', $db->quoteName('#__efemerides_categorias','c').' ON c.id=e.categoria_id')
            ->where('e.state=1')
            ->where('MONTH(e.fecha_evento)='.(int)$hoy->format('m'))
            ->where('DAY(e.fecha_evento)='.(int)$hoy->format('d'))
            ->order('e.fecha_evento DESC');
        if ($this->catFiltro>0) $q->where('e.categoria_id='.$this->catFiltro);
        $db->setQuery($q);
        $exactas = $db->loadObjectList() ?: [];

        if (!empty($exactas)) {
            $this->efemeridesHoy = $exactas;
            $this->esModoHoy     = true;
        } else {
            $this->esModoHoy     = false;
            $this->efemeridesHoy = $this->getProximas($db, $hoy, $this->catFiltro);
        }

        // Bases de URL para musico y banda
        $root = rtrim(\Joomla\CMS\Uri\Uri::root(), '/');
        $this->musicoBase = $root . '/index.php?option=com_efemerides';
        $this->bandaBase  = $root . '/index.php?option=com_efemerides';
        try {
            $menuApp  = \Joomla\CMS\Factory::getApplication()->getMenu();
            $comIdUrl = \Joomla\CMS\Component\ComponentHelper::getComponent('com_efemerides')->id;
            foreach ($menuApp->getItems('component_id', $comIdUrl) as $mi) {
                $lnk   = (string)($mi->link ?? '');
                $route = $root . '/' . ltrim((string)$mi->route, '/');
                if (strpos($lnk, 'view=musicos') !== false || strpos($lnk, 'view=musico') !== false) {
                    $this->musicoBase = $route;
                }
                if (strpos($lnk, 'view=bandas') !== false || strpos($lnk, 'view=banda') !== false) {
                    $this->bandaBase = $route;
                }
            }
        } catch (\Throwable $e) {}

        parent::display($tpl);
    }

    private function getProximas($db, \DateTime $hoy, int $catFiltro, int $limit = 10): array
    {
        $anio   = (int)$hoy->format('Y');
        $refDoy = (int)$hoy->format('z');
        $q = $db->getQuery(true)
            ->select(['e.*','c.name AS categoria_nombre'])
            ->from($db->quoteName('#__efemerides','e'))
            ->join('LEFT', $db->quoteName('#__efemerides_categorias','c').' ON c.id=e.categoria_id')
            ->where('e.state=1');
        if ($catFiltro>0) $q->where('e.categoria_id='.$catFiltro);
        $db->setQuery($q);
        $all = $db->loadObjectList() ?: [];
        foreach ($all as $item) {
            $d    = new \DateTime((string)$item->fecha_evento);
            $occ  = \DateTime::createFromFormat('Y-m-d', $anio.'-'.$d->format('m-d')) ?: \DateTime::createFromFormat('Y-m-d', $anio.'-02-28');
            $diff = (int)$occ->format('z') - $refDoy;
            if ($diff > 182) $diff -= 365;
            if ($diff < -182) $diff += 365;
            $item->_diff_dias  = $diff;
            $item->_ocurrencia = $occ->format('d/m');
        }
        usort($all, function($a,$b) { return abs($a->_diff_dias) - abs($b->_diff_dias); });
        return array_slice($all, 0, $limit);
    }
}
