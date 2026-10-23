<?php
namespace Joomla\Component\Efemerides\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class FraseModel extends AdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm(
            'com_efemerides.frase', 'frase',
            ['control' => 'jform', 'load_data' => $loadData]
        ) ?: false;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_efemerides.edit.frase.data', []);
        return empty($data) ? $this->getItem() : $data;
    }

    public function getTable($name = 'Frase', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    public function save($data)
    {
        // El campo 'autor' del form es el ID del músico seleccionado (SQL field devuelve el value=id)
        // Necesitamos guardar el NOMBRE en autor y rellenar las fechas desde el músico
        $autorId = (int)($data['autor'] ?? 0);

        if ($autorId > 0) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery(
                'SELECT nombre, fecha_nacimiento, fecha_defuncion FROM #__efemerides_musicos
                 WHERE id = ' . $autorId . ' AND state = 1 LIMIT 1'
            );
            $musico = $db->loadObject();
            if ($musico) {
                $data['musico_id']       = $autorId;
                $data['autor']           = $musico->nombre;
                $data['fecha_nacimiento'] = ($musico->fecha_nacimiento && $musico->fecha_nacimiento !== '0000-00-00')
                                            ? $musico->fecha_nacimiento : null;
                $data['fecha_defuncion']  = ($musico->fecha_defuncion  && $musico->fecha_defuncion  !== '0000-00-00')
                                            ? $musico->fecha_defuncion  : null;
            }
        }

        return parent::save($data);
    }

    protected function prepareTable($table): void
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        if (empty($table->id)) {
            $table->created = $now;
        }
        $table->modified = $now;
        if (empty($table->fecha_nacimiento) || $table->fecha_nacimiento === '0000-00-00') {
            $table->fecha_nacimiento = null;
        }
        if (empty($table->fecha_defuncion) || $table->fecha_defuncion === '0000-00-00') {
            $table->fecha_defuncion = null;
        }
    }
}
