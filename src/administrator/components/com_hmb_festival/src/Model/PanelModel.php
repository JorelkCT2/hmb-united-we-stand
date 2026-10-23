<?php
namespace Hmb\Component\Festival\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class PanelModel extends BaseDatabaseModel
{
    public function getStats(): array
    {
        $db    = $this->getDatabase();
        $stats = [];

        $map = [
            'checkins' => '#__hmb_fest_checkins',
            'horns'    => '#__hmb_fest_horns',
            'matches'  => '#__hmb_fest_matches',
            'messages' => '#__hmb_fest_messages',
            'reports'  => '#__hmb_fest_reports',
        ];

        foreach ($map as $key => $table) {
            try {
                $db->setQuery('SELECT COUNT(*) FROM ' . $db->quoteName($table));
                $stats[$key] = (int) $db->loadResult();
            } catch (\Throwable $e) {
                $stats[$key] = 0;
            }
        }

        return $stats;
    }

    public function getReports(): array
    {
        $db = $this->getDatabase();

        try {
            $query = $db->getQuery(true)
                ->select('r.*, u1.username AS reporter, u2.username AS reported')
                ->from($db->quoteName('#__hmb_fest_reports', 'r'))
                ->join('LEFT', $db->quoteName('#__users', 'u1') . ' ON u1.id = r.user_id')
                ->join('LEFT', $db->quoteName('#__users', 'u2') . ' ON u2.id = r.reported_id')
                ->order('r.created DESC')
                ->setLimit(50);
            $db->setQuery($query);

            return (array) $db->loadObjectList();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
