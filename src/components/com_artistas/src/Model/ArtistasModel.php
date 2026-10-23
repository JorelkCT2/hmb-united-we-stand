<?php
namespace Joomla\Component\Artistas\Site\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

class ArtistasModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id','a.id','name','a.name','genre','a.genre','location','a.location','rating','a.rating','created','a.created','review_count','a.review_count'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('a.id, a.name, a.alias, a.short_desc, a.genre, COALESCE(NULLIF(a.provincia_origen,\'\'), NULLIF(a.location,\'\'), \'\') AS location, a.formats, a.events, a.image, a.rating, a.review_count, a.created')
              ->from($db->quoteName('#__artistas', 'a'))
              ->where('a.state = 1');

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $safe = $db->quote('%' . $db->escape($search) . '%');
            $query->where('(a.name LIKE ' . $safe . ' OR a.genre LIKE ' . $safe . ' OR a.location LIKE ' . $safe . ')');
        }

        $genre = $this->getState('filter.genre');
        if (!empty($genre)) {
            $query->where('a.genre = ' . $db->quote($genre));
        }

        $location = $this->getState('filter.location');
        if (!empty($location)) {
            // Busca en provincia_origen o en location (texto libre)
            $safeLoc = $db->quote('%' . $db->escape($location) . '%');
            $exactLoc = $db->quote($location);
            $query->where('(a.provincia_origen = ' . $exactLoc . ' OR a.location LIKE ' . $safeLoc . ' OR a.provincias_destino LIKE ' . $safeLoc . ')');
        }

        // Columnas permitidas para ordenar
        $allowed = ['a.name','a.genre','a.location','a.rating','a.review_count','a.created'];
        $orderCol  = $this->getState('list.ordering', 'a.name');
        if (!in_array($orderCol, $allowed)) { $orderCol = 'a.name'; }

        $orderDirn = strtoupper($this->getState('list.direction', 'ASC'));
        if (!in_array($orderDirn, ['ASC','DESC'])) { $orderDirn = 'ASC'; }

        $query->order($db->escape($orderCol) . ' ' . $orderDirn);
        return $query;
    }

    protected function populateState($ordering = 'a.name', $direction = 'ASC')
    {
        $app = Factory::getApplication();
        $this->setState('filter.search',   $app->getInput()->getString('filter_search', ''));
        $this->setState('filter.genre',    $app->getInput()->getString('filter_genre', ''));
        $this->setState('filter.location', $app->getInput()->getString('filter_location', ''));

        // Ordenación desde URL
        $order = $app->getInput()->getString('filter_order', $ordering);
        $dirn  = strtoupper($app->getInput()->getString('filter_dirn', $direction));
        if (!in_array($dirn, ['ASC','DESC'])) { $dirn = 'ASC'; }

        $this->setState('list.ordering',  $order);
        $this->setState('list.direction', $dirn);

        parent::populateState($order, $dirn);
    }

    public function getGenres(): array
    {
        $db = $this->getDatabase();
        try {
            $db->setQuery('SELECT name FROM #__artistas_generos WHERE state=1 ORDER BY ordering ASC, name ASC');
            return $db->loadColumn() ?: [];
        } catch (\Exception $e) {
            $db->setQuery('SELECT DISTINCT genre FROM #__artistas WHERE state=1 AND genre != ' . $db->quote('') . ' ORDER BY genre ASC');
            return $db->loadColumn() ?: [];
        }
    }
}
