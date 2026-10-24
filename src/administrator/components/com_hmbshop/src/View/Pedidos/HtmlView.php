<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Pedidos;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $estado = '';

    public function display($tpl = null): void
    {
        ToolbarHelper::title('Pedidos de la tienda', 'cart');
        $this->estado = (string) Factory::getApplication()->getInput()->getCmd('estado', '');
        $this->items = ShopHelper::pedidos($this->estado);

        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_hmbshop')) {
            ToolbarHelper::preferences('com_hmbshop');
        }

        parent::display($tpl);
    }
}
