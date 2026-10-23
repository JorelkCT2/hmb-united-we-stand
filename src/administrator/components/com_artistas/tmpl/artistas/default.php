<?php defined('_JEXEC') or die; use Joomla\CMS\HTML\HTMLHelper; use Joomla\CMS\Router\Route; HTMLHelper::_('behavior.multiselect'); ?>
<form action="<?= Route::_('index.php?option=com_artistas&view=artistas') ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="filter_search" value="<?= $this->escape($this->state->get('filter.search')) ?>"
                               placeholder="Buscar por nombre…" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <select name="filter_state" class="form-select" onchange="this.form.submit()">
                            <option value="" <?= $this->state->get('filter.state') === '' ? 'selected' : '' ?>>— Todos los estados —</option>
                            <option value="1" <?= $this->state->get('filter.state') == '1' ? 'selected' : '' ?>>✓ Publicados</option>
                            <option value="0" <?= $this->state->get('filter.state') === '0' ? 'selected' : '' ?>>⏳ Pendientes</option>
                            <option value="-1" <?= $this->state->get('filter.state') == '-1' ? 'selected' : '' ?>>✕ Archivados</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        <a href="<?= Route::_('index.php?option=com_artistas&view=artistas') ?>" class="btn btn-secondary">Limpiar</a>
                    </div>
                </div>

                <table class="table table-striped" id="artistaList">
                    <thead>
                    <tr>
                        <th class="w-1 text-center">
                            <input type="checkbox" name="checkall-toggle" title="Seleccionar todo"
                                   onclick="Joomla.checkAll(this)">
                        </th>
                        <th>Nombre</th>
                        <th>Género</th>
                        <th>Localización</th>
                        <th>Usuario vinculado</th>
                        <th class="text-center">Valoración</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">ID</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->items as $i => $item): ?>
                        <tr>
                            <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $item->id) ?></td>
                            <td>
                                <a href="<?= Route::_('index.php?option=com_artistas&task=artista.edit&id=' . $item->id) ?>">
                                    <?= $this->escape($item->name) ?>
                                </a>
                            </td>
                            <td><?= $this->escape($item->genre) ?></td>
                            <td><?= $this->escape($item->location) ?></td>
                            <td class="text-center">
                                <?= number_format((float)$item->rating, 1) ?> ⭐ (<?= (int)$item->review_count ?>)
                            </td>
                            <td class="text-center">
                                <?= HTMLHelper::_('jgrid.published', $item->state, $i, 'artistas.', true, 'cb') ?>
                            </td>
                            <td class="text-center"><?= $item->id ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($this->items)): ?>
                        <tr><td colspan="7" class="text-center">No se encontraron artistas.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <?= $this->pagination->getListFooter() ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?= HTMLHelper::_('form.token') ?>
</form>
