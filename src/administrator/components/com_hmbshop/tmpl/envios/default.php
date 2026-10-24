<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Envios\HtmlView $this */
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?>
<div class="container-fluid p-3">
    <p class="text-end"><a class="btn btn-success" href="<?php echo Route::_('index.php?option=com_hmbshop&view=envio'); ?>">+ Nueva forma de envío</a></p>
    <table class="table table-striped align-middle">
        <thead><tr><th>Forma de envío</th><th class="text-end">Coste</th><th class="text-center">Pide dirección</th><th class="text-center">Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($this->items as $e) : ?>
            <tr>
                <td><a href="<?php echo Route::_('index.php?option=com_hmbshop&view=envio&id=' . (int) $e->id); ?>"><strong><?php echo $h($e->nombre); ?></strong></a><br><small class="text-muted"><?php echo $h($e->descripcion); ?></small></td>
                <td class="text-end"><?php echo $h(ShopHelper::dinero((float) $e->coste)); ?></td>
                <td class="text-center"><?php echo $e->pide_direccion ? 'Sí' : 'No'; ?></td>
                <td class="text-center"><?php echo $e->published ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-secondary">Oculta</span>'; ?></td>
                <td class="text-end"><form method="post" action="<?php echo Route::_('index.php?option=com_hmbshop&task=tienda.borrarEnvio&id=' . (int) $e->id); ?>" onsubmit="return confirm('¿Eliminar?');"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-sm btn-outline-danger">Eliminar</button></form></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$this->items) : ?><tr><td colspan="5" class="text-center text-muted p-4">No hay formas de envío.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
