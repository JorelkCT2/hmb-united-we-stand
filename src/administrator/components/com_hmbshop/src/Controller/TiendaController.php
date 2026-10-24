<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Administrator\Controller;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Todas las acciones de escritura del admin (task=tienda.xxx). Cada una exige token y
 * permiso de gestion, hace su cambio y vuelve a la pantalla de origen con un mensaje.
 */
class TiendaController extends BaseController
{
    private function listo(): void
    {
        $this->checkToken();

        if (!Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_hmbshop')) {
            throw new \RuntimeException('Sin permiso.', 403);
        }
    }

    private function volver(string $url, string $msg = '', string $tipo = 'message'): void
    {
        $app = Factory::getApplication();

        if ($msg !== '') {
            $app->enqueueMessage($msg, $tipo);
        }

        // false: la URL va tal cual (sin &amp;), que si no se pierde el parametro view
        $app->redirect('index.php?option=com_hmbshop&' . $url);
    }

    private function hacer(callable $f, string $okUrl, string $okMsg, string $errUrl): void
    {
        $this->listo();

        try {
            $f();
        } catch (\Throwable $e) {
            $this->volver($errUrl, $e->getMessage(), 'error');

            return;
        }

        $this->volver($okUrl, $okMsg);
    }

    private function datos(): array
    {
        return (array) Factory::getApplication()->getInput()->post->getArray();
    }

    private function id(string $campo = 'id'): int
    {
        return Factory::getApplication()->getInput()->getInt($campo, 0);
    }

    // ---- productos
    public function guardarProducto(): void
    {
        $this->listo();

        try {
            $d  = $this->datos();
            $id = ShopHelper::guardarProducto($d);

            // variantes: stock y visibilidad de cada una
            foreach ((array) ($d['stock'] ?? []) as $vid => $s) {
                ShopHelper::guardarStock((int) $vid, max(0, (int) $s), !empty($d['vpub'][$vid]));
            }

            if (!empty($_FILES['foto']['tmp_name'])) {
                $err = ShopHelper::subirFoto($id, $_FILES['foto']);

                if ($err !== '') {
                    $this->volver('view=producto&id=' . $id, 'Producto guardado, pero la foto no: ' . $err, 'warning');

                    return;
                }
            }
        } catch (\Throwable $e) {
            $this->volver('view=producto&id=' . (int) ($this->datos()['id'] ?? 0), $e->getMessage(), 'error');

            return;
        }

        $this->volver('view=producto&id=' . $id, 'Producto guardado.');
    }

    public function borrarProducto(): void
    {
        $id = $this->id();
        $this->hacer(fn() => ShopHelper::borrarProducto($id), 'view=productos', 'Producto eliminado.', 'view=producto&id=' . $id);
    }

    public function anadirEje(): void
    {
        $d = $this->datos();
        $id = (int) ($d['producto_id'] ?? 0);
        $this->hacer(fn() => ShopHelper::anadirEje($id, (string) ($d['eje_nombre'] ?? ''), (string) ($d['eje_valores'] ?? ''), !empty($d['eje_obligatorio'])),
            'view=producto&id=' . $id, 'Eje añadido y variantes regeneradas.', 'view=producto&id=' . $id);
    }

    public function anadirValores(): void
    {
        $d = $this->datos();
        $id = (int) ($d['producto_id'] ?? 0);
        $this->hacer(function () use ($d, $id) {
            ShopHelper::anadirValores((int) ($d['eje_id'] ?? 0), (string) ($d['valores'] ?? ''));
            ShopHelper::regenerarVariantes($id);
        }, 'view=producto&id=' . $id, 'Valores añadidos y variantes regeneradas.', 'view=producto&id=' . $id);
    }

    public function borrarEje(): void
    {
        $p = $this->id('producto_id');
        $e = $this->id('eje_id');
        $this->hacer(fn() => ShopHelper::borrarEje($e), 'view=producto&id=' . $p, 'Eje eliminado.', 'view=producto&id=' . $p);
    }

    public function borrarValor(): void
    {
        $p = $this->id('producto_id');
        $v = $this->id('valor_id');
        $this->hacer(fn() => ShopHelper::borrarValor($v), 'view=producto&id=' . $p, 'Valor eliminado.', 'view=producto&id=' . $p);
    }

    public function borrarFoto(): void
    {
        $p = $this->id('producto_id');
        $f = $this->id('foto_id');
        $this->hacer(fn() => ShopHelper::borrarFoto($f), 'view=producto&id=' . $p, 'Foto eliminada.', 'view=producto&id=' . $p);
    }

    public function fotoPrincipal(): void
    {
        $p = $this->id('producto_id');
        $f = $this->id('foto_id');
        $this->hacer(fn() => ShopHelper::fotoPrincipal($f), 'view=producto&id=' . $p, 'Foto principal cambiada.', 'view=producto&id=' . $p);
    }

    // ---- categorias
    public function guardarCategoria(): void
    {
        $d = $this->datos();
        $this->hacer(fn() => ShopHelper::guardarCategoria($d), 'view=categorias', 'Categoría guardada.', 'view=categoria&id=' . (int) ($d['id'] ?? 0));
    }

    public function borrarCategoria(): void
    {
        $this->listo();
        $msg = ShopHelper::borrarCategoria($this->id());
        $this->volver('view=categorias', $msg !== '' ? $msg : 'Categoría eliminada.', $msg !== '' ? 'error' : 'message');
    }

    // ---- envios
    public function guardarEnvio(): void
    {
        $d = $this->datos();
        $this->hacer(fn() => ShopHelper::guardarEnvio($d), 'view=envios', 'Forma de envío guardada.', 'view=envio&id=' . (int) ($d['id'] ?? 0));
    }

    public function borrarEnvio(): void
    {
        $id = $this->id();
        $this->hacer(fn() => ShopHelper::borrarEnvio($id), 'view=envios', 'Forma de envío eliminada.', 'view=envios');
    }

    // ---- pedidos
    public function cambiarEstado(): void
    {
        $d = $this->datos();
        $id = (int) ($d['id'] ?? 0);
        $this->hacer(fn() => ShopHelper::cambiarEstado($id, (string) ($d['estado'] ?? '')), 'view=pedido&id=' . $id, 'Estado actualizado.', 'view=pedido&id=' . $id);
    }
}
