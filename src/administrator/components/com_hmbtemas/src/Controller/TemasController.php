<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Controlador de lista — gestiona acciones en masa:
 * publish, unpublish, delete, reorder, etc.
 *
 * Joomla lo invoca cuando task = "temas.publish", "temas.delete", etc.
 */
class TemasController extends AdminController
{
    protected $text_prefix = 'COM_HMBTEMAS';

    public function getModel($name = 'Tema', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
