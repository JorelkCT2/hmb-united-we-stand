<?php
namespace Joomla\Component\Efemerides\Site\View\Lista;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;

class HtmlView extends BaseHtmlView
{
    public array  $items      = [];
    public int    $total      = 0;
    public int    $limitstart = 0;
    public int    $limit      = 20;
    public array  $filtros    = [];
    public string $ordenCol   = 'e.fecha_evento';
    public string $ordenDir   = 'DESC';
    public array  $categorias = [];
    public array  $anios      = [];
    public array  $musicosMap = [];
    public array  $bandasMap  = [];
    public string $componentBase = '';

    public function display($tpl = null): void
    {
        $input = Factory::getApplication()->getInput();
        $db    = Factory::getContainer()->get('DatabaseDriver');

        $this->filtros = [
            'search' => trim((string)$input->getString('search','')),
            'cat'    => (int)$input->getInt('cat',0),
            'mes'    => (int)$input->getInt('mes',(int)date('n')),
            'anio'   => (int)$input->getInt('anio',0),
        ];
        $cols = ['e.fecha_evento','e.artista','e.titulo','c.name'];
        $col  = (string)$input->getString('ordenar','e.fecha_evento');
        $this->ordenCol = in_array($col,$cols,true)?$col:'e.fecha_evento';
        $dir = strtoupper((string)$input->getString('dir','DESC'));
        $this->ordenDir = $dir==='ASC'?'ASC':'DESC';
        $this->limit      = max(1,(int)$input->getInt('limit',20));
        $this->limitstart = max(0,(int)$input->getInt('limitstart',0));

        $query = $db->getQuery(true)
            ->select(['e.id','e.titulo','e.fecha_evento','e.artista','e.descripcion','e.detalle','e.nombre_disco','e.lugar_concierto','e.asistentes','c.name AS categoria_nombre'])
            ->from($db->quoteName('#__efemerides','e'))
            ->join('LEFT',$db->quoteName('#__efemerides_categorias','c').' ON c.id=e.categoria_id')
            ->where('e.state=1');

        $this->aplicarFiltros($query,$db);

        $cq = $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__efemerides','e'))
            ->join('LEFT',$db->quoteName('#__efemerides_categorias','c').' ON c.id=e.categoria_id')->where('e.state=1');
        $this->aplicarFiltros($cq,$db);
        $db->setQuery($cq);
        $this->total = (int)$db->loadResult();

        $query->order($db->escape($this->ordenCol).' '.$db->escape($this->ordenDir));
        $db->setQuery($query,$this->limitstart,$this->limit);
        $this->items = $db->loadObjectList() ?: [];

        $db->setQuery('SELECT id,name FROM #__efemerides_categorias WHERE state=1 ORDER BY ordering ASC');
        $this->categorias = $db->loadObjectList() ?: [];
        $db->setQuery('SELECT DISTINCT YEAR(fecha_evento) AS anio FROM #__efemerides WHERE state=1 ORDER BY anio DESC');
        $this->anios = $db->loadColumn() ?: [];

        // Mapas para enlaces
        $db->setQuery('SELECT id, nombre FROM #__efemerides_musicos WHERE state=1');
        foreach ($db->loadObjectList() ?: [] as $row)
            $this->musicosMap[mb_strtolower($row->nombre)] = (int)$row->id;
        $db->setQuery('SELECT id, nombre FROM #__efemerides_bandas WHERE state=1');
        foreach ($db->loadObjectList() ?: [] as $row)
            $this->bandasMap[mb_strtolower($row->nombre)] = (int)$row->id;

        // URL base del componente para los links
        $this->componentBase = $this->_getComponentBase();

        parent::display($tpl);
    }

    private function aplicarFiltros($q, $db): void
    {
        if ($this->filtros['search']!=='') {
            $s=$db->quote('%'.$db->escape($this->filtros['search'],true).'%');
            $q->where("(e.titulo LIKE $s OR e.artista LIKE $s OR e.nombre_disco LIKE $s OR e.lugar_concierto LIKE $s)");
        }
        if ($this->filtros['cat']>0) $q->where('e.categoria_id='.(int)$this->filtros['cat']);
        if ($this->filtros['mes']>0) $q->where('MONTH(e.fecha_evento)='.(int)$this->filtros['mes']);
        if ($this->filtros['anio']>0) $q->where('YEAR(e.fecha_evento)='.(int)$this->filtros['anio']);
    }

    private function _getComponentBase(): string
    {
        static $base = null;
        if ($base !== null) return $base;
        try {
            $app   = \Joomla\CMS\Factory::getApplication();
            $menu  = $app->getMenu();
            $comId = \Joomla\CMS\Component\ComponentHelper::getComponent('com_efemerides')->id;
            $items = $menu->getItems('component_id', $comId);
            if (!empty($items)) {
                $base = rtrim(\Joomla\CMS\Uri\Uri::root(), '/') . '/' . ltrim((string)$items[0]->route, '/');
                return $base;
            }
        } catch (\Throwable $e) {}
        $base = rtrim(\Joomla\CMS\Uri\Uri::root(), '/') . '/index.php?option=com_efemerides';
        return $base;
    }
}
