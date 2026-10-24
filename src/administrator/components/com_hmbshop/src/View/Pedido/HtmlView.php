<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Pedido;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public $item = null;

    public function display($tpl = null): void
    {
        ToolbarHelper::title('Pedido', 'cart');
        $this->item = ShopHelper::pedido(Factory::getApplication()->getInput()->getInt('id', 0));

        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_hmbshop')) {
            ToolbarHelper::preferences('com_hmbshop');
        }

        parent::display($tpl);
    }
}
