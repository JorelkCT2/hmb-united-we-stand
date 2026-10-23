<?php
namespace Joomla\Component\Efemerides\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class EfemeridModel extends AdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_efemerides.efemerid',
            'efemerid',
            ['control' => 'jform', 'load_data' => $loadData]
        );
        return $form ?: false;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_efemerides.edit.efemerid.data', []);
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    public function getTable($name = 'Efemerid', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    protected function prepareTable($table): void
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        if (empty($table->id)) {
            $table->created  = $now;
            $table->created_by = Factory::getApplication()->getIdentity()->id;
        }
        $table->modified    = $now;
        $table->modified_by = Factory::getApplication()->getIdentity()->id;
    }
}
