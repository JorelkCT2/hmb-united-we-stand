<?php
namespace Joomla\Component\Concerts\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Controlador plural — gestiona acciones sobre la lista:
 * delete, publish, unpublish, checkin, etc.
 */
class ConcertsController extends AdminController
{
    protected $text_prefix = 'COM_CONCERTS';

    public function getModel($name = 'Concert', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
