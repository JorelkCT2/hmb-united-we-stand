<?php
namespace Joomla\Component\Artistas\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class PresupuestosModel extends ListModel
{
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('p.*')
              ->from($db->quoteName('#__artistas_presupuestos', 'p'));

        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('p.state = ' . (int) $state);
        }
        $query->order('p.created DESC');
        return $query;
    }
}
