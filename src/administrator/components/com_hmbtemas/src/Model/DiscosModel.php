<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class DiscosModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id','grupo','titulo','anno','published','ordering'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(['id','grupo','titulo','productora','anno','portada','audio_file','published','ordering'])
            ->from($db->quoteName('#__hmb_discos'));

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $like = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(' . $db->quoteName('titulo') . ' LIKE ' . $like
                . ' OR '  . $db->quoteName('grupo')  . ' LIKE ' . $like . ')');
        }
        $published = $this->getState('filter.published', '');
        if ($published !== '') {
            $query->where($db->quoteName('published') . ' = ' . (int) $published);
        }
        $query->order($db->escape($this->state->get('list.ordering', 'anno')) . ' '
                    . $db->escape($this->state->get('list.direction', 'DESC')));
        return $query;
    }
}
