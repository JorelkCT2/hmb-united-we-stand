<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Site\View\Pedido;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $pedido = null;

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $in = $app->getInput();
        $p = ShopHelper::pedido($in->getInt('id', 0));
        $u = $app->getIdentity();
        $dueno = $p && (int) $u->id > 0 && (int) $p->user_id === (int) $u->id;
        $conToken = $p && hash_equals(ShopHelper::pedidoToken($p), (string) $in->getString('t', ''));

        if (!$p || (!$dueno && !$conToken)) {
            throw new \Exception('Pedido no encontrado', 404);
        }

        $this->pedido = $p;
        $this->getDocument()->setTitle('Pedido #' . (int) $p->id);

        $wa = $this->getDocument()->getWebAssetManager();
        $css = fn($f) => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/css/' . $f) ?: 1);
        $wa->registerAndUseStyle('hmbshop.core', 'media/com_hmbshop/css/phoca-core.css', ['version' => $css('phoca-core.css')]);
        $wa->registerAndUseStyle('hmbshop.css', 'media/com_hmbshop/css/shop.css', ['version' => $css('shop.css')]);
        $wa->registerAndUseScript('hmbshop.js', 'media/com_hmbshop/js/shop.js', ['version' => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/js/shop.js') ?: 1)], ['defer' => true]);

        parent::display($tpl);
    }
}
