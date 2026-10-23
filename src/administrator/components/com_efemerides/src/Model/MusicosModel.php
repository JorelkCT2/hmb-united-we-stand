<?php
namespace Joomla\Component\Efemerides\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;
class MusicosModel extends ListModel
{
    public function __construct($config=[])
    {
        if (empty($config['filter_fields']))
            $config['filter_fields']=['id','nombre','banda_texto','state'];
        parent::__construct($config);
    }
    protected function getListQuery()
    {
        $db=$this->getDatabase();
        $q=$db->getQuery(true)
            ->select(['m.*','b.nombre AS banda_nombre'])
            ->from($db->quoteName('#__efemerides_musicos','m'))
            ->join('LEFT',$db->quoteName('#__efemerides_bandas','b').' ON b.id=m.banda_id');
        $s=$this->getState('filter.search','');
        if($s!==''){$s=$db->quote('%'.$db->escape($s,true).'%');$q->where("(m.nombre LIKE $s OR m.banda_texto LIKE $s)");}
        $st=$this->getState('filter.state','');
        if($st!=='') $q->where('m.state='.(int)$st);
        $allowed=['id','nombre','banda_texto','state','fecha_nacimiento'];
        $col=$this->getState('list.ordering','nombre');
        if(!in_array($col,$allowed,true)) $col='nombre';
        $dir=strtoupper($this->getState('list.direction','ASC'))==='ASC'?'ASC':'DESC';
        $q->order($db->escape('m.'.$col).' '.$dir);
        return $q;
    }
    protected function populateState($o='nombre',$d='ASC'): void
    {
        $this->setState('filter.search',$this->getUserStateFromRequest($this->context.'.filter.search','filter_search',''));
        $this->setState('filter.state', $this->getUserStateFromRequest($this->context.'.filter.state','filter_state',''));
        parent::populateState($o,$d);
    }
}
