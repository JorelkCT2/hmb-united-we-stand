<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Site\Service;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Mismas URLs que tenia la tienda de Phoca (las que ya conoce Google y los usuarios):
 *   /tienda                                              categorias
 *   /tienda/1-camisetas/5-camisetas-hombre               categoria (anidada)
 *   /tienda/1-camisetas/5-camisetas-hombre/6-logo-...    producto
 *   /tienda/checkout                                     finalizar compra
 */
class Router extends RouterView
{
    public function __construct(SiteApplication $app, AbstractMenu $menu)
    {
        $tienda = new RouterViewConfiguration('tienda');
        $this->registerView($tienda);

        $cat = new RouterViewConfiguration('categoria');
        $cat->setKey('id')->setParent($tienda)->setNestable();
        $this->registerView($cat);

        $prod = new RouterViewConfiguration('producto');
        $prod->setKey('id')->setParent($cat, 'catid');
        $this->registerView($prod);

        $checkout = new RouterViewConfiguration('checkout');
        $checkout->setParent($tienda);
        $this->registerView($checkout);

        $pedidos = new RouterViewConfiguration('pedidos');
        $pedidos->setParent($tienda);
        $this->registerView($pedidos);

        $pedido = new RouterViewConfiguration('pedido');
        $pedido->setKey('id')->setParent($pedidos);
        $this->registerView($pedido);

        parent::__construct($app, $menu);

        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
    }

    /** Hijo -> raiz, y la raiz como id 0 (asi lo espera StandardRules para vistas anidadas). */
    public function getCategoriaSegment($id, $query)
    {
        $ruta  = [];
        $c     = ShopHelper::categoria((int) $id);
        $vueltas = 0;

        while ($c && $vueltas++ < 10) {
            $ruta[(int) $c->id] = (int) $c->id . ':' . $c->alias;
            $c = (int) $c->parent_id ? ShopHelper::categoria((int) $c->parent_id) : null;
        }

        if (!$ruta) {
            return [];
        }

        $ruta[0] = '0:root';

        return $ruta;
    }

    public function getCategoriaId($segment, $query)
    {
        [$id, $alias] = array_pad(explode('-', (string) $segment, 2), 2, '');
        $c = ShopHelper::categoria((int) $id);

        if (!$c || $c->alias !== $alias || !(int) $c->published) {
            return false;
        }

        // Dentro de una categoria solo valen sus hijas; en el primer nivel, las principales
        $padre = isset($query['id']) ? (int) $query['id'] : 0;

        return ((int) $c->parent_id === $padre) ? (int) $id : false;
    }

    public function getProductoSegment($id, $query)
    {
        $p = ShopHelper::producto((int) $id);

        return $p ? [(int) $id => (int) $id . ':' . $p->alias] : [];
    }

    public function getProductoId($segment, $query)
    {
        [$id, $alias] = array_pad(explode('-', (string) $segment, 2), 2, '');
        $p = ShopHelper::producto((int) $id);

        return ($p && $p->alias === $alias) ? (int) $id : false;
    }

    public function getPedidoSegment($id, $query)
    {
        return [(int) $id => (string) (int) $id];
    }

    public function getPedidoId($segment, $query)
    {
        return (int) $segment;
    }
}
