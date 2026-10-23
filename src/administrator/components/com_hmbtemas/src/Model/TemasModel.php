<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class TemasModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'title', 'album', 'album_year', 'track_number', 'published', 'ordering'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['t.id','t.title','t.album','t.album_year','t.track_number',
                      't.duration','t.audio_file','t.cover_image',
                      't.spotify_url','t.youtube_url','t.published','t.ordering'])
            ->from($db->quoteName('#__hmb_temas', 't'));

        // Filtro de búsqueda
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $like = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(' . $db->quoteName('t.title') . ' LIKE ' . $like
                . ' OR ' . $db->quoteName('t.album') . ' LIKE ' . $like . ')');
        }

        // Filtro publicado
        $published = $this->getState('filter.published', '');
        if ($published !== '') {
            $query->where($db->quoteName('t.published') . ' = ' . (int) $published);
        }

        // Orden
        $orderCol = $this->state->get('list.ordering', 'album_year');
        $orderDir = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDir));

        return $query;
    }
}
