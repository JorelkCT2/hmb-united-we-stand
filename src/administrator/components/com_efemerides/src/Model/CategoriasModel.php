<?php
namespace Joomla\Component\Efemerides\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class CategoriasModel extends ListModel
{
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('*')->from('#__efemerides_categorias')->order('ordering ASC');
        return $query;
    }
}
