<?php
namespace Joomla\Component\Efemerides\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class FallecidosModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'artista', 'fecha_fallecimiento', 'state'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select(['f.*', 'e.titulo AS efemeride_titulo'])
              ->from($db->quoteName('#__efemerides_fallecidos', 'f'))
              ->join('LEFT', $db->quoteName('#__efemerides', 'e') . ' ON e.id = f.efemeride_id');

        $search = $this->getState('filter.search', '');
        if (!empty($search)) {
            $s = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('f.artista LIKE ' . $s);
        }

        $year = $this->getState('filter.year', '');
        if (!empty($year)) {
            $query->where('YEAR(f.fecha_fallecimiento) = ' . (int)$year);
        }

        $state = $this->getState('filter.state', '');
        if ($state !== '') {
            $query->where('f.state = ' . (int)$state);
        }

        $query->order('YEAR(f.fecha_fallecimiento) DESC, f.artista ASC');
        return $query;
    }

    protected function populateState($ordering = 'f.fecha_fallecimiento', $direction = 'DESC'): void
    {
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', ''));
        $this->setState('filter.year',   $this->getUserStateFromRequest($this->context . '.filter.year', 'filter_year', ''));
        $this->setState('filter.state',  $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', ''));
        parent::populateState($ordering, $direction);
    }
}
