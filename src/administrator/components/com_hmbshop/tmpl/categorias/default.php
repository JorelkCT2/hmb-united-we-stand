<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Categorias\HtmlView $this */
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?>
<div class="container-fluid p-3">
    <p class="text-end"><a class="btn btn-success" href="<?php echo Route::_('index.php?option=com_hmbshop&view=categoria'); ?>">+ Nueva categoría</a></p>
    <table class="table table-striped align-middle">
        <thead><tr><th>Categoría</th><th>Alias</th><th class="text-center">Orden</th><th class="text-center">Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($this->items as $c) : ?>
            <tr>
                <td><?php echo str_repeat('— ', (int) $c->nivel); ?><a href="<?php echo Route::_('index.php?option=com_hmbshop&view=categoria&id=' . (int) $c->id); ?>"><strong><?php echo $h($c->nombre); ?></strong></a></td>
                <td class="text-muted"><?php echo $h($c->alias); ?></td>
                <td class="text-center"><?php echo (int) $c->ordering; ?></td>
                <td class="text-center"><?php echo $c->published ? '<span class="badge bg-success">Publicada</span>' : '<span class="badge bg-secondary">Oculta</span>'; ?></td>
                <td class="text-end">
                    <form method="post" action="<?php echo Route::_('index.php?option=com_hmbshop&task=tienda.borrarCategoria&id=' . (int) $c->id); ?>" onsubmit="return confirm('¿Eliminar la categoría?');">
                        <?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$this->items) : ?><tr><td colspan="5" class="text-center text-muted p-4">No hay categorías.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
