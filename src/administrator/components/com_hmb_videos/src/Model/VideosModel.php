<?php
namespace Joomla\Component\HmbVideos\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

class VideosModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'a.id', 'title', 'a.title', 'state', 'a.state', 'folder_id', 'a.folder_id', 'ordering', 'a.ordering'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Explicit columns - avoid id collision with JOIN
        $query->select([
                'a.id',
                'a.title',
                'a.alias',
                'a.youtube_id',
                'a.folder_id',
                'a.band',
                'a.year',
                'a.tags',
                'a.state',
                'a.featured',
                'a.ordering',
                'a.hits',
                'a.mobile',
                'a.created',
                'f.title AS folder_title',
            ])
            ->from($db->quoteName('#__hmb_videos', 'a'))
            ->join('LEFT', $db->quoteName('#__hmb_folders', 'f') . ' ON f.id = a.folder_id');

        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where($db->quoteName('a.state') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        }

        $folderId = $this->getState('filter.folder_id');
        if (is_numeric($folderId) && $folderId > 0) {
            $query->where($db->quoteName('a.folder_id') . ' = :fid')
                ->bind(':fid', $folderId, ParameterType::INTEGER);
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $s = '%' . trim($search) . '%';
            $query->where('(' . $db->quoteName('a.title') . ' LIKE :s OR ' . $db->quoteName('a.band') . ' LIKE :s2)')
                ->bind(':s', $s)->bind(':s2', $s);
        }

        $query->order($db->escape($this->state->get('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->state->get('list.direction', 'ASC')));
        return $query;
    }
}
