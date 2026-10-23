<?php
namespace Joomla\Component\Artistas\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;

class GenerosController extends AdminController
{
    public function getModel($name = 'Genero', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
