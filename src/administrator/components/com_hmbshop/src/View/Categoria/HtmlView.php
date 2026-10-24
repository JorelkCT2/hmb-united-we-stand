<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Categoria;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public $item = null;
    public $categorias = [];

    public function display($tpl = null): void
    {
        ToolbarHelper::title('Categoría', 'cart');
        $id = Factory::getApplication()->getInput()->getInt('id', 0);
        $this->item = $id ? ShopHelper::categoria($id) : null;
        $this->categorias = ShopHelper::categorias();

        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_hmbshop')) {
            ToolbarHelper::preferences('com_hmbshop');
        }

        parent::display($tpl);
    }
}
