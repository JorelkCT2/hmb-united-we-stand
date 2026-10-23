<?php
namespace Joomla\Component\Artistas\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class GenerosModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id','g.id','name','g.name','state','g.state','ordering','g.ordering'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('g.*')
            ->from($db->quoteName('#__artistas_generos', 'g'));

        $state = $this->getState('filter.state');
        if (is_numeric($state)) {
            $query->where('g.state = ' . (int)$state);
        }
        $query->order('g.ordering ASC, g.name ASC');
        return $query;
    }
}
