<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use HeavyMetalBrothers\Component\Hmbshop\Site\Helper\Vista;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Site\View\Pedidos\HtmlView $this */
$h = fn($s) => Vista::h($s);
?>
<div class="hmbs">
    <header class="hmbs-head"><h1>Mis pedidos</h1></header>
    <?php if (!$this->pedidos) : ?>
        <p class="hmbs-vacio">Todavía no has hecho ningún pedido.</p>
    <?php else : ?>
    <table class="hmbs-tabla">
        <thead><tr><th>Nº</th><th>Fecha</th><th>Estado</th><th class="hmbs-der">Total</th></tr></thead>
        <tbody>
        <?php foreach ($this->pedidos as $p) : ?>
            <tr>
                <td><a href="<?php echo Route::_('index.php?option=com_hmbshop&view=pedido&id=' . (int) $p->id); ?>">#<?php echo (int) $p->id; ?></a></td>
                <td><?php echo $h($p->created); ?></td>
                <td><?php echo $h(ShopHelper::ESTADOS[$p->estado] ?? $p->estado); ?></td>
                <td class="hmbs-der"><?php echo $h(ShopHelper::dinero((float) $p->importe_total)); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
