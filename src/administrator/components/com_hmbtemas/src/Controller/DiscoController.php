<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class DiscoController extends FormController
{
    protected $view_list  = 'discos';
    protected $view_item  = 'disco';
    protected $text_prefix = 'COM_HMBTEMAS';
}
