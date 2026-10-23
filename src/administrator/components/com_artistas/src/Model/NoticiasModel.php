<?php
namespace Joomla\Component\Artistas\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class NoticiasModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['n.id','n.tipo','n.titulo','a.name','n.created','n.state'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('n.*, a.name AS artista_name, u.username AS autor, COALESCE(NULLIF(n.titulo,\'\'), c.title, \'Sin título\') AS titulo_display')
              ->from('#__artistas_noticias AS n')
              ->leftJoin('#__artistas AS a ON a.id = n.artista_id')
              ->leftJoin('#__users AS u ON u.id = n.created_by')
              ->leftJoin('#__content AS c ON c.id = n.article_id');

        $search = $this->getState('filter.search');
        if ($search) {
            $query->where('(n.titulo LIKE ' . $db->quote('%' . $search . '%') . ' OR a.name LIKE ' . $db->quote('%' . $search . '%') . ')');
        }
        $state = $this->getState('filter.state');
        if (is_numeric($state) && $state !== '') {
            $query->where('n.state = ' . (int)$state);
        }
        $tipo = $this->getState('filter.tipo');
        if ($tipo) {
            $query->where('n.tipo = ' . $db->quote($tipo));
        }

        $orderCol  = $this->getState('list.ordering', 'n.created');
        $orderDirn = $this->getState('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $orderDirn);
        return $query;
    }

    protected function populateState($ordering = 'n.created', $direction = 'DESC'): void
    {
        $app = \Joomla\CMS\Factory::getApplication();
        $this->setState('filter.search', $app->getInput()->getString('filter_search', ''));
        $this->setState('filter.state',  $app->getInput()->getString('filter_state', ''));
        $this->setState('filter.tipo',   $app->getInput()->getString('filter_tipo', ''));
        parent::populateState($ordering, $direction);
    }
}
