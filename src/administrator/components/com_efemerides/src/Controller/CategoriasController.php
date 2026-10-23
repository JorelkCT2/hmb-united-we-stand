<?php
namespace Joomla\Component\Efemerides\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class CategoriasController extends AdminController
{
    protected $text_prefix = 'COM_EFEMERIDES_CATEGORIA';

    public function getModel($name = 'Categoria', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
