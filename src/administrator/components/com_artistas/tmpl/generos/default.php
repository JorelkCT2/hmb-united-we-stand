<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');
?>
<form action="<?= Route::_('index.php?option=com_artistas&view=generos') ?>" method="post" name="adminForm" id="adminForm">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🎵 Géneros musicales <small class="text-muted fs-6">(<?= count($this->items) ?> géneros)</small></h4>
    <a href="<?= Route::_('index.php?option=com_artistas&task=genero.add') ?>" class="btn btn-success btn-sm">➕ Nuevo género</a>
</div>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="w-1 text-center"><input type="checkbox" name="checkall-toggle" onclick="Joomla.checkAll(this)"></th>
            <th>Nombre</th>
            <th class="text-center" style="width:80px">Orden</th>
            <th class="text-center" style="width:120px">Activo</th>
            <th class="text-center" style="width:60px">ID</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($this->items as $i => $item): ?>
    <tr>
        <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $item->id) ?></td>
        <td>
            <a href="<?= Route::_('index.php?option=com_artistas&task=genero.edit&id=' . $item->id) ?>"
               class="fw-semibold">
                🎵 <?= htmlspecialchars($item->name) ?>
            </a>
        </td>
        <td class="text-center text-muted"><?= (int)$item->ordering ?></td>
        <td class="text-center">
            <?= HTMLHelper::_('jgrid.published', $item->state, $i, 'generos.', true, 'cb') ?>
        </td>
        <td class="text-center text-muted"><?= $item->id ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($this->items)): ?>
    <tr><td colspan="5" class="text-center text-muted py-4">No hay géneros. <a href="<?= Route::_('index.php?option=com_artistas&task=genero.add') ?>">Crea el primero</a>.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<?= $this->pagination->getListFooter() ?>
<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?= HTMLHelper::_('form.token') ?>
</form>
