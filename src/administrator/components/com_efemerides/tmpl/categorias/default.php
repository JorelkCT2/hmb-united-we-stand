<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');
?>
<form action="<?php echo Route::_('index.php?option=com_efemerides&view=categorias'); ?>" method="post" name="adminForm" id="adminForm">
    <table class="table table-striped">
        <thead>
            <tr>
                <th width="1%"><input type="checkbox" name="checkall-toggle" onclick="Joomla.checkAll(this)"></th>
                <th>Nombre de la categoría</th>
                <th width="10%">Orden</th>
                <th width="10%">Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($this->items)): ?>
            <tr><td colspan="4" class="text-center">No hay categorías.</td></tr>
        <?php else: ?>
            <?php foreach ($this->items as $i => $item): ?>
            <tr>
                <td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                <td>
                    <a href="index.php?option=com_efemerides&task=categoria.edit&id=<?php echo $item->id; ?>">
                        <?php echo $this->escape($item->name); ?>
                    </a>
                </td>
                <td><?php echo $item->ordering; ?></td>
                <td><?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'categorias.', true); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
