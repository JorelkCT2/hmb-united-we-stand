<?php
namespace Joomla\Component\HmbVideos\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

class VideosModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['ordering', 'a.ordering', 'title', 'a.title', 'hits', 'a.hits'];
        }
        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.ordering', $direction = 'ASC'): void
    {
        $app = Factory::getApplication();
        $this->setState('filter.folder_id', $app->getInput()->getInt('folder_id', 0));
        $this->setState('filter.search',    $app->getInput()->getString('search', ''));
        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select(['a.*', 'f.title AS folder_title'])
            ->from($db->quoteName('#__hmb_videos', 'a'))
            ->join('LEFT', $db->quoteName('#__hmb_folders', 'f') . ' ON f.id = a.folder_id')
            ->where($db->quoteName('a.state') . ' = 1');

        $folderId = (int) $this->getState('filter.folder_id');
        if ($folderId > 0) {
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

    public function getFolderTree(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['f.*', 'COUNT(v.id) AS video_count'])
            ->from($db->quoteName('#__hmb_folders', 'f'))
            ->join('LEFT', $db->quoteName('#__hmb_videos', 'v') . ' ON v.folder_id = f.id AND v.state = 1')
            ->where($db->quoteName('f.state') . ' = 1')
            ->group(['f.id'])
            ->order('f.parent_id ASC, f.ordering ASC');
        return $db->setQuery($query)->loadObjectList() ?: [];
    }
}
