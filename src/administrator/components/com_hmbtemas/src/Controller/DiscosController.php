<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;

class DiscosController extends AdminController
{
    protected $text_prefix = 'COM_HMBTEMAS';

    public function getModel($name = 'Disco', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
