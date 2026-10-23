<?php
namespace Joomla\Component\Hmgallery\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

class AlbumsModel extends ListModel
{
    public function __construct(array $config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id','a.id','title','a.title','state','a.state','ordering','a.ordering'];
        }
        parent::__construct($config);
    }

    protected function getListQuery(): \Joomla\Database\DatabaseQuery
    {
        $db = $this->getDatabase();
        $q  = $db->getQuery(true);
        $q->select(['a.id','a.title','a.alias','a.folder_path','a.cover_image','a.parent_id','a.ordering','a.state','a.hits'])
          ->from($db->quoteName('#__hmgallery_albums', 'a'));
        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $q->where($db->quoteName('a.state') . ' = :state')->bind(':state', $state, ParameterType::INTEGER);
        }
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $s = '%' . $db->escape($search, true) . '%';
            $q->where($db->quoteName('a.title') . ' LIKE :search')->bind(':search', $s);
        }
        $q->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
        return $q;
    }

    protected function populateState($ordering = 'a.ordering', $direction = 'asc'): void
    {
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
        $this->setState('filter.state',  $this->getUserStateFromRequest($this->context . '.filter.state',  'filter_state',  '', 'cmd'));
        parent::populateState($ordering, $direction);
    }
}
