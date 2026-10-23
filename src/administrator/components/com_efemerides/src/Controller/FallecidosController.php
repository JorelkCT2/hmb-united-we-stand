<?php
namespace Joomla\Component\Efemerides\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class FallecidosController extends AdminController
{
    protected $text_prefix = 'COM_EFEMERIDES_FALLECIDO';

    public function getModel($name = 'Fallecido', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
