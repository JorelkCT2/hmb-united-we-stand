<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Envios;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public $items = [];

    public function display($tpl = null): void
    {
        ToolbarHelper::title('Formas de envío', 'cart');
        $this->items = ShopHelper::envios();

        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_hmbshop')) {
            ToolbarHelper::preferences('com_hmbshop');
        }

        parent::display($tpl);
    }
}
