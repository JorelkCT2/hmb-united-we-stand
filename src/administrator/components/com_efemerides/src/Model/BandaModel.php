<?php
namespace Joomla\Component\Efemerides\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
class BandaModel extends AdminModel
{
    public function getForm($data=[],$loadData=true)
    { return $this->loadForm('com_efemerides.banda','banda',['control'=>'jform','load_data'=>$loadData])?:false; }
    protected function loadFormData()
    { $d=Factory::getApplication()->getUserState('com_efemerides.edit.banda.data',[]); return empty($d)?$this->getItem():$d; }
    public function getTable($n='Banda',$p='Administrator',$o=[]) { return parent::getTable($n,$p,$o); }
}
