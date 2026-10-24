<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Site\View\Pedidos;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $pedidos = [];

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $u = $app->getIdentity();

        if ($u->guest) {
            $app->enqueueMessage('Inicia sesión para ver tus pedidos.', 'notice');
            $app->redirect(\Joomla\CMS\Router\Route::_('index.php?option=com_users&view=login&return=' . base64_encode('index.php?option=com_hmbshop&view=pedidos'), false));
        }

        $this->pedidos = ShopHelper::pedidosDeUsuario((int) $u->id);
        $this->getDocument()->setTitle('Mis pedidos');

        $wa = $this->getDocument()->getWebAssetManager();
        $css = fn($f) => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/css/' . $f) ?: 1);
        $wa->registerAndUseStyle('hmbshop.core', 'media/com_hmbshop/css/phoca-core.css', ['version' => $css('phoca-core.css')]);
        $wa->registerAndUseStyle('hmbshop.css', 'media/com_hmbshop/css/shop.css', ['version' => $css('shop.css')]);
        $wa->registerAndUseScript('hmbshop.js', 'media/com_hmbshop/js/shop.js', ['version' => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/js/shop.js') ?: 1)], ['defer' => true]);

        parent::display($tpl);
    }
}
