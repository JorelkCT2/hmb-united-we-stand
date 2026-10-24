<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Pedidos\HtmlView $this */
$h     = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$color = ['pendiente' => 'warning text-dark', 'preparado' => 'info text-dark', 'entregado' => 'success', 'cancelado' => 'secondary'];
?>
<div class="container-fluid p-3">
    <p class="d-flex gap-2 flex-wrap">
        <a class="btn btn-sm <?php echo $this->estado === '' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="<?php echo Route::_('index.php?option=com_hmbshop&view=pedidos'); ?>">Todos</a>
        <?php foreach (ShopHelper::ESTADOS as $k => $v) : ?>
            <a class="btn btn-sm <?php echo $this->estado === $k ? 'btn-primary' : 'btn-outline-primary'; ?>" href="<?php echo Route::_('index.php?option=com_hmbshop&view=pedidos&estado=' . $k); ?>"><?php echo $h($v); ?></a>
        <?php endforeach; ?>
    </p>
    <table class="table table-striped align-middle">
        <thead><tr><th>Nº</th><th>Fecha</th><th>Cliente</th><th>Pago</th><th class="text-end">Total</th><th class="text-center">Estado</th></tr></thead>
        <tbody>
        <?php foreach ($this->items as $p) : ?>
            <tr>
                <td><a href="<?php echo Route::_('index.php?option=com_hmbshop&view=pedido&id=' . (int) $p->id); ?>"><strong>#<?php echo (int) $p->id; ?></strong></a></td>
                <td><?php echo $h($p->created); ?></td>
                <td><?php echo $h($p->nombre); ?><br><small class="text-muted"><?php echo $h($p->email); ?></small></td>
                <td><?php echo $h($p->metodo_pago); ?></td>
                <td class="text-end"><?php echo $h(ShopHelper::dinero((float) $p->importe_total)); ?></td>
                <td class="text-center"><span class="badge bg-<?php echo $color[$p->estado] ?? 'secondary'; ?>"><?php echo $h(ShopHelper::ESTADOS[$p->estado] ?? $p->estado); ?></span></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$this->items) : ?><tr><td colspan="6" class="text-center text-muted p-4">No hay pedidos.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
