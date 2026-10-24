<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Site\View\Categoria;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $categoria = null;
    public $padre = null;
    public $hijas = [];
    public $productos = [];

    public function display($tpl = null): void
    {
        $id = Factory::getApplication()->getInput()->getInt('id', 0);
        $this->categoria = ShopHelper::categoria($id);

        if (!$this->categoria || !(int) $this->categoria->published) {
            throw new \Exception('Categoría no encontrada', 404);
        }

        $this->padre = (int) $this->categoria->parent_id ? ShopHelper::categoria((int) $this->categoria->parent_id) : null;
        $this->hijas = array_values(array_filter(ShopHelper::categorias(true), fn($c) => (int) $c->parent_id === $id));
        $this->productos = ShopHelper::productos(['solo_publicados' => 1, 'categoria' => $id, 'exacta' => 1]);
        $c = $this->categoria;
        $this->getDocument()->setTitle($c->meta_title ?: ($c->titulo_largo ?: $c->nombre));

        if ($c->meta_desc) {
            $this->getDocument()->setDescription($c->meta_desc);
        }

        $wa = $this->getDocument()->getWebAssetManager();
        $css = fn($f) => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/css/' . $f) ?: 1);
        $wa->registerAndUseStyle('hmbshop.core', 'media/com_hmbshop/css/phoca-core.css', ['version' => $css('phoca-core.css')]);
        $wa->registerAndUseStyle('hmbshop.css', 'media/com_hmbshop/css/shop.css', ['version' => $css('shop.css')]);
        $wa->registerAndUseScript('hmbshop.js', 'media/com_hmbshop/js/shop.js', ['version' => (string) (@filemtime(JPATH_ROOT . '/media/com_hmbshop/js/shop.js') ?: 1)], ['defer' => true]);

        parent::display($tpl);
    }
}
