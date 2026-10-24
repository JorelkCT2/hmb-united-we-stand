<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Pedido\HtmlView $this */
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$p = $this->item;

if (!$p) {
    echo '<div class="alert alert-warning m-3">Pedido no encontrado.</div>';

    return;
}
?>
<div class="container-fluid p-3" style="max-width:900px">
    <h2>Pedido #<?php echo (int) $p->id; ?> <small class="text-muted"><?php echo $h($p->created); ?></small></h2>
    <div class="row g-3 mb-3">
        <div class="col-md-6"><div class="card"><div class="card-body"><h5>Cliente</h5>
            <?php echo $h($p->nombre); ?><br><a href="mailto:<?php echo $h($p->email); ?>"><?php echo $h($p->email); ?></a><br><?php echo $h($p->telefono); ?>
            <?php if ($p->direccion) : ?><hr class="my-2"><?php echo $h($p->direccion); ?><br><?php echo $h($p->cp . ' ' . $p->ciudad); ?><?php endif; ?>
        </div></div></div>
        <div class="col-md-6"><div class="card"><div class="card-body"><h5>Entrega y pago</h5>
            Entrega: <strong><?php echo $h($p->envio_nombre); ?></strong> (<?php echo $h(ShopHelper::dinero((float) $p->envio_coste)); ?>)<br>
            Pago: <strong><?php echo $h($p->metodo_pago); ?></strong><br>
            <form method="post" class="d-flex gap-2 mt-2" action="<?php echo Route::_('index.php?option=com_hmbshop&task=tienda.cambiarEstado'); ?>">
                <?php echo HTMLHelper::_('form.token'); ?><input type="hidden" name="id" value="<?php echo (int) $p->id; ?>">
                <select name="estado" class="form-select form-select-sm">
                    <?php foreach (ShopHelper::ESTADOS as $k => $v) : ?><option value="<?php echo $k; ?>" <?php echo $p->estado === $k ? 'selected' : ''; ?>><?php echo $h($v); ?></option><?php endforeach; ?>
                </select><button class="btn btn-sm btn-primary">Cambiar</button>
            </form>
        </div></div></div>
    </div>
    <table class="table">
        <thead><tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-end">Precio</th><th class="text-end">Importe</th></tr></thead>
        <tbody>
        <?php foreach ($p->lineas as $l) : ?>
            <tr><td><?php echo $h($l->producto_nombre); ?> <small class="text-muted"><?php echo $h($l->etiqueta_variante); ?></small></td>
                <td class="text-center"><?php echo (int) $l->cantidad; ?></td>
                <td class="text-end"><?php echo $h(ShopHelper::dinero((float) $l->precio_unitario)); ?></td>
                <td class="text-end"><?php echo $h(ShopHelper::dinero((float) $l->precio_unitario * (int) $l->cantidad)); ?></td></tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><th colspan="3" class="text-end">Total (IVA incl. <?php echo $h(ShopHelper::dinero((float) $p->iva_total)); ?>)</th><th class="text-end"><?php echo $h(ShopHelper::dinero((float) $p->importe_total)); ?></th></tr></tfoot>
    </table>
    <a class="btn btn-outline-secondary" href="<?php echo Route::_('index.php?option=com_hmbshop&view=pedidos'); ?>">Volver</a>
</div>
