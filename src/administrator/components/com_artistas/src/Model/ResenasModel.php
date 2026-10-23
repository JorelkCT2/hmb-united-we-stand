<?php
namespace Joomla\Component\Artistas\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class ResenasModel extends ListModel
{
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('r.*, a.name AS artista_name')
              ->from($db->quoteName('#__artistas_resenas', 'r'))
              ->leftJoin($db->quoteName('#__artistas', 'a') . ' ON a.id = r.artista_id');

        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('r.state = ' . (int) $state);
        }
        $query->order('r.created DESC');
        return $query;
    }

    public function approve(array $ids): bool
    {
        $db = $this->getDatabase();
        foreach ($ids as $id) {
            $db->setQuery('UPDATE ' . $db->quoteName('#__artistas_resenas') . ' SET state=1 WHERE id=' . (int)$id)->execute();
        }
        $this->recalcRatings($ids);
        return true;
    }

    public function delete(array $ids): bool
    {
        $db = $this->getDatabase();
        foreach ($ids as $id) {
            $db->setQuery('DELETE FROM ' . $db->quoteName('#__artistas_resenas') . ' WHERE id=' . (int)$id)->execute();
        }
        return true;
    }

    private function recalcRatings(array $resenaIds): void
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('DISTINCT artista_id')
            ->from('#__artistas_resenas')
            ->where('id IN (' . implode(',', array_map('intval', $resenaIds)) . ')');
        $db->setQuery($query);
        $artistaIds = $db->loadColumn();
        foreach ($artistaIds as $aid) {
            $q = 'SELECT AVG(rating), COUNT(id) FROM ' . $db->quoteName('#__artistas_resenas') .
                 ' WHERE artista_id=' . (int)$aid . ' AND state=1';
            $db->setQuery($q);
            [$avg, $cnt] = $db->loadRow();
            $db->setQuery('UPDATE ' . $db->quoteName('#__artistas') .
                          ' SET rating=' . round((float)$avg, 2) . ', review_count=' . (int)$cnt .
                          ' WHERE id=' . (int)$aid)->execute();
        }
    }
}
