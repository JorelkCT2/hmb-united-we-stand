<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Site\View\Tienda;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $categorias = [];

    public function display($tpl = null): void
    {
        $this->categorias = array_values(array_filter(ShopHelper::categorias(true), fn($c) => (int) $c->parent_id === 0));
        $this->getDocument()->setTitle('Tienda');

        $wa = $this->getDocument()->getWebAssetManager();
        $css = fn($f) => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/css/' . $f) ?: 1);
        $wa->registerAndUseStyle('hmbshop.core', 'media/com_hmbshop/css/phoca-core.css', ['version' => $css('phoca-core.css')]);
        $wa->registerAndUseStyle('hmbshop.css', 'media/com_hmbshop/css/shop.css', ['version' => $css('shop.css')]);
        $wa->registerAndUseScript('hmbshop.js', 'media/com_hmbshop/js/shop.js', ['version' => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/js/shop.js') ?: 1)], ['defer' => true]);

        parent::display($tpl);
    }
}
