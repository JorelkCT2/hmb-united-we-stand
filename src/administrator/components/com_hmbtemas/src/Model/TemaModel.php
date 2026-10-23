<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class TemaModel extends AdminModel
{
    public $typeAlias = 'com_hmbtemas.tema';

    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm('com_hmbtemas.tema', 'tema', [
            'control'   => 'jform',
            'load_data' => $loadData,
        ]);
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_hmbtemas.edit.tema.data', []);
        if (empty($data)) {
            $data = $this->getItem();
            // Convertir segundos → mm:ss para mostrar en el formulario
            if ($data && !empty($data->duration) && is_numeric($data->duration)) {
                $s = (int) $data->duration;
                $data->duration = sprintf('%d:%02d', intdiv($s, 60), $s % 60);
            }
        }
        return $data;
    }

    public function getTable($name = 'Tema', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
