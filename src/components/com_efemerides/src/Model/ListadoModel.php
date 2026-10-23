<?php
namespace Joomla\Component\Efemerides\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Pagination\Pagination;

class ListadoModel extends BaseDatabaseModel
{
    private array  $filtros  = [];
    private string $ordenCol = 'e.fecha_evento';
    private string $ordenDir = 'DESC';
    private int    $limit    = 20;
    private int    $start    = 0;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $input = Factory::getApplication()->getInput();

        // Filtros desde GET
        $this->filtros = [
            'search' => trim((string)$input->getString('search', '')),
            'cat'    => (int)$input->getInt('cat', 0),
            'mes'    => (int)$input->getInt('mes', (int)date('n')),  // mes actual por defecto
            'anio'   => (int)$input->getInt('anio', 0),
        ];

        // Ordenación
        $cols = ['e.fecha_evento', 'e.artista', 'e.titulo', 'c.name'];
        $col  = (string)$input->getString('ordenar', 'e.fecha_evento');
        $this->ordenCol = in_array($col, $cols, true) ? $col : 'e.fecha_evento';
        $dir = strtoupper((string)$input->getString('dir', 'DESC'));
        $this->ordenDir = ($dir === 'ASC') ? 'ASC' : 'DESC';

        // Paginación
        $this->limit = max(1, (int)$input->getInt('limit', 20));
        $this->start = max(0, (int)$input->getInt('limitstart', 0));
    }

    private function buildQuery(bool $countOnly = false)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        if ($countOnly) {
            $query->select('COUNT(*)');
        } else {
            $query->select([
                'e.id','e.titulo','e.fecha_evento','e.artista',
                'e.descripcion','e.detalle','e.nombre_disco',
                'e.lugar_concierto','e.asistentes',
                'c.name AS categoria_nombre',
            ]);
        }

        $query->from($db->quoteName('#__efemerides','e'))
              ->join('LEFT', $db->quoteName('#__efemerides_categorias','c').' ON c.id = e.categoria_id')
              ->where('e.state = 1');

        // Búsqueda
        if ($this->filtros['search'] !== '') {
            $s = $db->quote('%'.$db->escape($this->filtros['search'], true).'%');
            $query->where("(e.titulo LIKE $s OR e.artista LIKE $s OR e.nombre_disco LIKE $s OR e.lugar_concierto LIKE $s)");
        }

        // Categoría
        if ($this->filtros['cat'] > 0) {
            $query->where('e.categoria_id = '.(int)$this->filtros['cat']);
        }

        // Mes
        if ($this->filtros['mes'] > 0) {
            $query->where('MONTH(e.fecha_evento) = '.(int)$this->filtros['mes']);
        }

        // Año
        if ($this->filtros['anio'] > 0) {
            $query->where('YEAR(e.fecha_evento) = '.(int)$this->filtros['anio']);
        }

        return $query;
    }

    public function getItems(): array
    {
        $db    = $this->getDatabase();
        $query = $this->buildQuery();
        $query->order($db->escape($this->ordenCol).' '.$db->escape($this->ordenDir));
        $db->setQuery($query, $this->start, $this->limit);
        return $db->loadObjectList() ?: [];
    }

    public function getTotal(): int
    {
        $db = $this->getDatabase();
        $db->setQuery($this->buildQuery(true));
        return (int)$db->loadResult();
    }

    public function getPagination(): Pagination
    {
        return new Pagination($this->getTotal(), $this->start, $this->limit);
    }

    public function getFiltros(): array   { return $this->filtros; }
    public function getOrdenCol(): string { return $this->ordenCol; }
    public function getOrdenDir(): string { return $this->ordenDir; }
    public function getLimit(): int       { return $this->limit; }

    public function getCategorias(): array
    {
        $db = $this->getDatabase();
        $db->setQuery('SELECT id, name FROM #__efemerides_categorias WHERE state=1 ORDER BY ordering ASC');
        return $db->loadObjectList() ?: [];
    }

    public function getAnios(): array
    {
        $db = $this->getDatabase();
        $db->setQuery('SELECT DISTINCT YEAR(fecha_evento) AS anio FROM #__efemerides WHERE state=1 ORDER BY anio DESC');
        return $db->loadColumn() ?: [];
    }
}
