<?php
namespace Joomla\Component\Efemerides\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class EfemeridesModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'titulo', 'artista', 'fecha_evento', 'categoria_id', 'state'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select([
            'e.*',
            'c.name AS categoria_nombre',
        ])
        ->from($db->quoteName('#__efemerides', 'e'))
        ->join('LEFT', $db->quoteName('#__efemerides_categorias', 'c') . ' ON c.id = e.categoria_id');

        // Filter: state
        $state = $this->getState('filter.state', '');
        if ($state !== '') {
            $query->where('e.state = ' . (int)$state);
        }

        // Filter: categoria
        $cat = $this->getState('filter.categoria_id', '');
        if ($cat !== '') {
            $query->where('e.categoria_id = ' . (int)$cat);
        }

        // Filter: search
        $search = $this->getState('filter.search', '');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(e.titulo LIKE ' . $search . ' OR e.artista LIKE ' . $search . ')');
        }

        // Filter: month/day
        $mesdia = $this->getState('filter.mesdia', '');
        if (!empty($mesdia)) {
            [$mes, $dia] = explode('-', $mesdia);
            $query->where('MONTH(e.fecha_evento) = ' . (int)$mes)
                  ->where('DAY(e.fecha_evento) = ' . (int)$dia);
        }

        $orderCol  = $this->getState('list.ordering', 'e.fecha_evento');
        $orderDir  = $this->getState('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDir));

        return $query;
    }

    protected function populateState($ordering = 'e.fecha_evento', $direction = 'DESC'): void
    {
        $this->setState('filter.search',      $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', ''));
        $this->setState('filter.state',       $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', ''));
        $this->setState('filter.categoria_id',$this->getUserStateFromRequest($this->context . '.filter.categoria_id', 'filter_categoria_id', ''));
        parent::populateState($ordering, $direction);
    }
}
