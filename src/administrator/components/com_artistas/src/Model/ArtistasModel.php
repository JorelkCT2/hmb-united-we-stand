<?php
namespace Joomla\Component\Artistas\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class ArtistasModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'a.id', 'name', 'a.name', 'state', 'a.state', 'genre', 'a.genre', 'location', 'a.location', 'ordering', 'a.ordering'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select('a.id, a.name, a.alias, a.genre, a.location, a.state, a.rating, a.review_count, a.ordering, a.created, a.created_by, u.username')
              ->from($db->quoteName('#__artistas', 'a'))
              ->leftJoin('#__users AS u ON u.id = a.created_by');

        // Filter state - empty = todos, numeric = filtrar por ese estado
        $state = $this->getState('filter.state');
        if (is_numeric($state) && $state !== '') {
            $query->where($db->quoteName('a.state') . ' = ' . (int) $state);
        }
        // NO filtrar por state si está vacío → muestra publicados Y pendientes

        // Filter search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $query->where($db->quoteName('a.name') . ' LIKE ' . $db->quote('%' . $db->escape($search) . '%'));
        }

        // Filter genre
        $genre = $this->getState('filter.genre');
        if (!empty($genre)) {
            $query->where($db->quoteName('a.genre') . ' = ' . $db->quote($genre));
        }

        $orderCol  = $this->getState('list.ordering', 'a.ordering');
        $orderDirn = $this->getState('list.direction', 'ASC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
    {
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
        $this->setState('filter.state',  $this->getUserStateFromRequest($this->context . '.filter.state',  'filter_state', '', 'string'));
        $this->setState('filter.genre',  $this->getUserStateFromRequest($this->context . '.filter.genre',  'filter_genre',  '', 'string'));
        parent::populateState($ordering, $direction);
    }
}
