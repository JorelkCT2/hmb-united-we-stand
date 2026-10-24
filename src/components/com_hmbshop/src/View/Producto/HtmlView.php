<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Site\View\Producto;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $producto = null;
    public $categoria = null;

    public function display($tpl = null): void
    {
        $in = Factory::getApplication()->getInput();
        $this->producto = ShopHelper::producto($in->getInt('id', 0));

        if (!$this->producto || !(int) $this->producto->published) {
            throw new \Exception('Producto no encontrado', 404);
        }

        $p = $this->producto;
        $cid = $in->getInt('catid', 0) ?: (int) $p->categoria_id;
        $this->categoria = $cid ? ShopHelper::categoria($cid) : null;
        $this->getDocument()->setTitle($p->meta_title ?: ($p->titulo_largo ?: $p->nombre));

        if ($p->meta_desc) {
            $this->getDocument()->setDescription($p->meta_desc);
        }

        $wa = $this->getDocument()->getWebAssetManager();
        $css = fn($f) => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/css/' . $f) ?: 1);
        $wa->registerAndUseStyle('hmbshop.core', 'media/com_hmbshop/css/phoca-core.css', ['version' => $css('phoca-core.css')]);
        $wa->registerAndUseStyle('hmbshop.css', 'media/com_hmbshop/css/shop.css', ['version' => $css('shop.css')]);
        $wa->registerAndUseScript('hmbshop.js', 'media/com_hmbshop/js/shop.js', ['version' => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/js/shop.js') ?: 1)], ['defer' => true]);

        parent::display($tpl);
    }
}
