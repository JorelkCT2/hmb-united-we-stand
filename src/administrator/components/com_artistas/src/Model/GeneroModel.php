<?php
namespace Joomla\Component\Artistas\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;

class GeneroModel extends AdminModel
{
    public $typeAlias = 'com_artistas.genero';

    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm('com_artistas.genero', 'genero', ['control' => 'jform', 'load_data' => $loadData]) ?: false;
    }

    public function getTable($name = 'Genero', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
