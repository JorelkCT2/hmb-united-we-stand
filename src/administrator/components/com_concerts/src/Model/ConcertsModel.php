<?php
namespace Joomla\Component\Concerts\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class ConcertsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id','band','city','date_start','state'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $q  = $db->getQuery(true)->select('a.*')->from($db->qn('#__concerts','a'));

        $state = $this->getState('filter.state');
        if (is_numeric($state)) $q->where('a.state = '.(int)$state);

        $search = $this->getState('filter.search');
        if ($search) {
            $like = $db->q('%'.$db->escape($search,true).'%');
            $q->where("(a.title LIKE $like OR a.band LIKE $like OR a.headliners LIKE $like "
                     . "OR a.support_bands LIKE $like OR a.city LIKE $like "
                     . "OR a.venue LIKE $like OR a.address LIKE $like)");
        }
        $q->order('a.date_start ASC');
        return $q;
    }

    protected function populateState($ordering = 'a.date_start', $direction = 'ASC'): void
    {
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context.'.filter.search','filter_search',''));
        $this->setState('filter.state',  $this->getUserStateFromRequest($this->context.'.filter.state','filter_state',''));
        parent::populateState($ordering, $direction);
    }
}
