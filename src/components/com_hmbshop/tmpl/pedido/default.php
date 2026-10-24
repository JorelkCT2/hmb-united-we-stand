<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use HeavyMetalBrothers\Component\Hmbshop\Site\Helper\Vista;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Site\View\Pedido\HtmlView $this */
$h = fn($s) => Vista::h($s);
$p = $this->pedido;
$m = ShopHelper::metodosPago();
?>
<div class="hmbs">
    <header class="hmbs-head"><h1>Pedido #<?php echo (int) $p->id; ?></h1></header>
    <p class="hmbs-ok">Gracias, <?php echo $h($p->nombre); ?>. Hemos registrado tu pedido (<?php echo $h(ShopHelper::ESTADOS[$p->estado] ?? $p->estado); ?>).</p>

    <?php if ($p->metodo_pago === 'recogida') : ?>
        <p class="hmbs-aviso"><?php echo $h(ShopHelper::params()->get('texto_recogida', 'Pagas al recoger tu pedido.')); ?></p>
    <?php elseif ($p->metodo_pago === 'transferencia') : ?>
        <p class="hmbs-aviso">Haz la transferencia de <strong><?php echo $h(ShopHelper::dinero((float) $p->importe_total)); ?></strong> al IBAN <strong><?php echo $h(ShopHelper::params()->get('iban', '')); ?></strong> indicando «Pedido <?php echo (int) $p->id; ?>».</p>
    <?php endif; ?>

    <table class="hmbs-tabla">
        <thead><tr><th>Producto</th><th class="hmbs-cen">Cant.</th><th class="hmbs-der">Importe</th></tr></thead>
        <tbody>
        <?php foreach ($p->lineas as $l) : ?>
            <tr><td><?php echo $h($l->producto_nombre); ?> <small><?php echo $h($l->etiqueta_variante); ?></small></td><td class="hmbs-cen"><?php echo (int) $l->cantidad; ?></td><td class="hmbs-der"><?php echo $h(ShopHelper::dinero((float) $l->precio_unitario * (int) $l->cantidad)); ?></td></tr>
        <?php endforeach; ?>
        <?php if ((float) $p->envio_coste > 0) : ?><tr><td><?php echo $h($p->envio_nombre ?: 'Envío'); ?></td><td></td><td class="hmbs-der"><?php echo $h(ShopHelper::dinero((float) $p->envio_coste)); ?></td></tr><?php endif; ?>
        </tbody>
        <tfoot><tr><th colspan="2" class="hmbs-der">Total (IVA incl.)</th><th class="hmbs-der"><?php echo $h(ShopHelper::dinero((float) $p->importe_total)); ?></th></tr></tfoot>
    </table>
    <p><a class="hmbs-btn" href="<?php echo Route::_('index.php?option=com_hmbshop&view=tienda'); ?>">Seguir comprando</a></p>
</div>
