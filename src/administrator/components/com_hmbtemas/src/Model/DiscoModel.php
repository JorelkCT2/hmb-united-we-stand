<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class DiscoModel extends AdminModel
{
    public $typeAlias = 'com_hmbtemas.disco';

    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm('com_hmbtemas.disco', 'disco', [
            'control'   => 'jform',
            'load_data' => $loadData,
        ]);
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_hmbtemas.edit.disco.data', []);
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    public function getTable($name = 'Disco', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
