<?php
namespace Joomla\Component\Efemerides\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class FrasesModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id','frase','autor','fecha_nacimiento','fecha_defuncion','state'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)->select('*')->from('#__efemerides_frases');

        $search = $this->getState('filter.search','');
        if ($search !== '') {
            $s = $db->quote('%'.$db->escape($search,true).'%');
            $query->where("(frase LIKE $s OR autor LIKE $s)");
        }
        $state = $this->getState('filter.state','');
        if ($state !== '') $query->where('state = '.(int)$state);

        $autor = $this->getState('filter.autor','');
        if ($autor !== '') $query->where('autor = '.$db->quote($autor));

        $allowed = ['id','frase','autor','fecha_nacimiento','fecha_defuncion','state'];
        $col = $this->getState('list.ordering','id');
        if (!in_array($col, $allowed, true)) $col = 'id';
        $dir = strtoupper($this->getState('list.direction','DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $query->order($db->escape($col).' '.$dir);

        return $query;
    }

    protected function populateState($ord='id', $dir='DESC'): void
    {
        // Dejar que el padre gestione paginación y ordenación correctamente
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context.'.filter.search','filter_search',''));
        $this->setState('filter.state',  $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', ''));
        $this->setState('filter.autor',  $this->getUserStateFromRequest($this->context.'.filter.autor', 'filter_autor', ''));

        parent::populateState($ord, $dir);
    }

    public function getAutores(): array
    {
        $db = $this->getDatabase();
        $db->setQuery('SELECT DISTINCT autor FROM #__efemerides_frases ORDER BY autor ASC');
        return $db->loadColumn() ?: [];
    }
}
