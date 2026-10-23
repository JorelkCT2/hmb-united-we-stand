<?php
namespace Joomla\Component\HmbVideos\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

class FoldersModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'a.id', 'title', 'a.title', 'state', 'a.state', 'ordering', 'a.ordering'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Explicit columns to avoid id collision
        $query->select([
                'a.id',
                'a.title',
                'a.alias',
                'a.parent_id',
                'a.state',
                'a.ordering',
                'a.description',
                'p.title AS parent_title',
            ])
            ->from($db->quoteName('#__hmb_folders', 'a'))
            ->join('LEFT', $db->quoteName('#__hmb_folders', 'p') . ' ON p.id = a.parent_id');

        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where($db->quoteName('a.state') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $s = '%' . trim($search) . '%';
            $query->where($db->quoteName('a.title') . ' LIKE :s')->bind(':s', $s);
        }

        $query->order($db->escape($this->state->get('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->state->get('list.direction', 'ASC')));
        return $query;
    }
}
