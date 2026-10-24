<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Productos;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $categorias = [];

    public function display($tpl = null): void
    {
        ToolbarHelper::title('Productos de la tienda', 'cart');
        $this->items = ShopHelper::productos(['buscar' => (string) Factory::getApplication()->getInput()->getString('buscar', ''), 'categoria' => Factory::getApplication()->getInput()->getInt('categoria', 0)]);
        $this->categorias = ShopHelper::categorias();

        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_hmbshop')) {
            ToolbarHelper::preferences('com_hmbshop');
        }

        parent::display($tpl);
    }
}
