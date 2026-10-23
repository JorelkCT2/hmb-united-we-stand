<?php
namespace Joomla\Module\HmbVideos\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\Database\ParameterType;

class HmbVideosHelper
{
    public function getVideos(Registry $params): array
    {
        $db       = Factory::getContainer()->get('DatabaseDriver');
        $query    = $db->getQuery(true);
        $folderId = (int) $params->get('folder_id', 0);
        $limit    = (int) $params->get('limit', 6);
        $featured = (int) $params->get('show_featured_only', 0);
        $ordering = $params->get('ordering', 'ordering');

        $query->select(['v.*', 'f.title AS folder_title'])
            ->from($db->quoteName('#__hmb_videos', 'v'))
            ->join('LEFT', $db->quoteName('#__hmb_folders', 'f') . ' ON f.id = v.folder_id')
            ->where($db->quoteName('v.state') . ' = 1');

        if ($folderId > 0) {
            $query->where($db->quoteName('v.folder_id') . ' = :fid')
                ->bind(':fid', $folderId, ParameterType::INTEGER);
        }

        if ($featured) {
            $one = 1;
            $query->where($db->quoteName('v.featured') . ' = :feat')
                ->bind(':feat', $one, ParameterType::INTEGER);
        }

        match ($ordering) {
            'id'   => $query->order($db->quoteName('v.id') . ' DESC'),
            'hits' => $query->order($db->quoteName('v.hits') . ' DESC'),
            'rand' => $query->order('RAND()'),
            default => $query->order($db->quoteName('v.ordering') . ' ASC'),
        };

        return $db->setQuery($query, 0, $limit)->loadObjectList() ?: [];
    }

    public function getFolders(): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
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
