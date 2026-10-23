<?php
namespace Joomla\Component\Efemerides\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class MusicoModel extends AdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm(
            'com_efemerides.musico', 'musico',
            ['control' => 'jform', 'load_data' => $loadData]
        ) ?: false;
    }

    protected function loadFormData()
    {
        $d = Factory::getApplication()->getUserState('com_efemerides.edit.musico.data', []);
        return empty($d) ? $this->getItem() : $d;
    }

    public function getTable($n = 'Musico', $p = 'Administrator', $o = [])
    {
        return parent::getTable($n, $p, $o);
    }

    /**
     * Inyectar los campos que Joomla filtraría (solista, banda_id_2, banda_texto_2)
     * directamente desde el POST, saltándonos el filtro del XML.
     */
    public function save($data)
    {
        $input = Factory::getApplication()->getInput();
        $post  = $input->post->get('jform', [], 'array');

        // Campos que Joomla puede filtrar — los tomamos del POST directamente
        foreach (['solista', 'banda_id_2', 'banda_texto_2'] as $field) {
            if (isset($post[$field])) {
                $data[$field] = $post[$field];
            }
        }

        return parent::save($data);
    }
}
