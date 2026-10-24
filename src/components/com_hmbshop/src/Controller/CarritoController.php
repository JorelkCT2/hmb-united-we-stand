<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Site\Controller;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** Acciones del carrito y del pedido (task=carrito.xxx). Todas exigen token. */
class CarritoController extends BaseController
{
    private function ir(string $url, string $msg = '', string $tipo = 'message'): void
    {
        $app = Factory::getApplication();

        if ($msg !== '') {
            $app->enqueueMessage($msg, $tipo);
        }

        // xhtml=false: la URL de la redireccion no puede llevar &amp;
        $app->redirect(Route::_($url, false));
    }

    public function anadir(): void
    {
        $this->checkToken();
        $in       = Factory::getApplication()->getInput();
        $pid      = $in->getInt('producto_id', 0);
        $elegidos = array_map('intval', (array) $in->get('eje', [], 'array'));
        $vid      = ShopHelper::varianteDe($pid, $elegidos);

        if (!$vid) {
            $this->ir('index.php?option=com_hmbshop&view=producto&id=' . $pid, 'Elige todas las opciones del producto.', 'warning');

            return;
        }

        $err = ShopHelper::carritoAnadir($vid, $in->getInt('cantidad', 1));

        if ($err !== '') {
            $this->ir('index.php?option=com_hmbshop&view=producto&id=' . $pid, $err, 'warning');

            return;
        }

        // como en la tienda de antes: se queda donde estaba (la caja del carrito se actualiza sola)
        $app = Factory::getApplication();
        $app->enqueueMessage('Producto añadido al carrito.', 'message');
        $ref = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $app->redirect(strpos($ref, Uri::root()) === 0 ? $ref : Route::_('index.php?option=com_hmbshop&view=checkout', false));
    }

    public function cambiar(): void
    {
        $this->checkToken();
        $in = Factory::getApplication()->getInput();

        ShopHelper::carritoCambiar($in->getInt('variante_id', 0), $in->getInt('quantity', 1));

        $this->ir('index.php?option=com_hmbshop&view=checkout');
    }

    public function quitar(): void
    {
        $this->checkToken();
        ShopHelper::carritoCambiar(Factory::getApplication()->getInput()->getInt('variante_id', 0), 0);
        $this->ir('index.php?option=com_hmbshop&view=checkout');
    }

    public function confirmar(): void
    {
        $this->checkToken();
        $in      = Factory::getApplication()->getInput();
        $cliente = [
            'nombre' => $in->getString('nombre', ''), 'email' => $in->getString('email', ''), 'telefono' => $in->getString('telefono', ''),
            'direccion' => $in->getString('direccion', ''), 'ciudad' => $in->getString('ciudad', ''), 'cp' => $in->getString('cp', ''),
        ];
        [$ok, $res] = ShopHelper::crearPedido($cliente, $in->getInt('envio_id', 0), $in->getCmd('metodo', ''));

        if (!$ok) {
            Factory::getApplication()->setUserState('hmbshop.cliente', $cliente);
            $this->ir('index.php?option=com_hmbshop&view=checkout', (string) $res, 'error');

            return;
        }

        Factory::getApplication()->setUserState('hmbshop.cliente', null);
        $p = ShopHelper::pedido((int) $res);
        $this->ir('index.php?option=com_hmbshop&view=pedido&id=' . (int) $res . '&t=' . ShopHelper::pedidoToken($p), '¡Pedido recibido!');
    }
}
