<?php
namespace Joomla\Component\Efemerides\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class EfemeridesController extends AdminController
{
    protected $text_prefix = 'COM_EFEMERIDES_EFEMERIDE';

    public function getModel($name = 'Efemerid', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
