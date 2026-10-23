<?php
namespace Joomla\Component\Efemerides\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;
class BandasModel extends ListModel
{
    public function __construct($config=[])
    {
        if (empty($config['filter_fields']))
            $config['filter_fields']=['id','nombre','pais','genero','state'];
        parent::__construct($config);
    }
    protected function getListQuery()
    {
        $db=$this->getDatabase();
        $q=$db->getQuery(true)->select('*')->from('#__efemerides_bandas');
        $s=$this->getState('filter.search','');
        if($s!==''){$s=$db->quote('%'.$db->escape($s,true).'%');$q->where("(nombre LIKE $s OR pais LIKE $s OR genero LIKE $s)");}
        $st=$this->getState('filter.state','');
        if($st!=='') $q->where('state='.(int)$st);
        $allowed=['id','nombre','pais','genero','state','fecha_formacion'];
        $col=$this->getState('list.ordering','nombre');
        if(!in_array($col,$allowed,true)) $col='nombre';
        $dir=strtoupper($this->getState('list.direction','ASC'))==='ASC'?'ASC':'DESC';
        $q->order($db->escape($col).' '.$dir);
        return $q;
    }
    protected function populateState($o='nombre',$d='ASC'): void
    {
        $this->setState('filter.search',$this->getUserStateFromRequest($this->context.'.filter.search','filter_search',''));
        $this->setState('filter.state', $this->getUserStateFromRequest($this->context.'.filter.state','filter_state',''));
        parent::populateState($o,$d);
    }
}
