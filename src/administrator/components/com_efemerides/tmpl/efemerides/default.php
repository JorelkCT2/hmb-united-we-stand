<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_efemerides&view=efemerides'); ?>" method="post" name="adminForm" id="adminForm">

    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" name="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                   class="form-control" placeholder="Buscar por título o artista...">
        </div>
        <div class="col-md-3">
            <select name="filter_categoria_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- Todas las categorías --</option>
                <?php foreach ($this->categorias as $cat): ?>
                    <option value="<?php echo $cat->id; ?>" <?php echo ($this->state->get('filter.categoria_id') == $cat->id) ? 'selected' : ''; ?>>
                        <?php echo $this->escape($cat->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="filter_state" class="form-select" onchange="this.form.submit()">
                <option value="">-- Estado --</option>
                <option value="1" <?php echo ($this->state->get('filter.state') === '1') ? 'selected' : ''; ?>>Publicado</option>
                <option value="0" <?php echo ($this->state->get('filter.state') === '0') ? 'selected' : ''; ?>>Despublicado</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary">
                <span class="icon-search"></span> Buscar
            </button>
            <a href="index.php?option=com_efemerides&view=efemerides" class="btn btn-secondary">
                <span class="icon-undo"></span> Limpiar
            </a>
        </div>
    </div>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th width="1%"><input type="checkbox" name="checkall-toggle" onclick="Joomla.checkAll(this)"></th>
                <th width="3%">ID</th>
                <th>Título</th>
                <th>Artista</th>
                <th>Categoría</th>
                <th>Fecha evento</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($this->items)): ?>
            <tr><td colspan="7" class="text-center">No hay efemérides. <a href="index.php?option=com_efemerides&view=efemerid&layout=edit">Crear una nueva</a>.</td></tr>
        <?php else: ?>
            <?php foreach ($this->items as $i => $item): ?>
            <tr>
                <td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                <td><?php echo $item->id; ?></td>
                <td>
                    <a href="index.php?option=com_efemerides&task=efemerid.edit&id=<?php echo $item->id; ?>">
                        <?php echo $this->escape($item->titulo); ?>
                    </a>
                    <?php if (!empty($item->nombre_disco)): ?>
                        <br><small class="text-muted"><span class="icon-music"></span> <?php echo $this->escape($item->nombre_disco); ?></small>
                    <?php endif; ?>
                    <?php if (!empty($item->lugar_concierto)): ?>
                        <br><small class="text-muted"><span class="icon-location"></span> <?php echo $this->escape($item->lugar_concierto); ?></small>
                    <?php endif; ?>
                </td>
                <td><?php echo $this->escape($item->artista); ?></td>
                <td><span class="badge bg-secondary"><?php echo $this->escape($item->categoria_nombre ?? '—'); ?></span></td>
                <td>
                    <?php
                    $fecha = new DateTime($item->fecha_evento);
                    echo $fecha->format('d/m/Y');
                    ?>
                    <br><small class="text-muted">hace <?php echo $fecha->format('Y') ? (date('Y') - $fecha->format('Y')) : '?'; ?> años</small>
                </td>
                <td><?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'efemerides.', true); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?php echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task"    value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="list[ordering]"  value="<?php echo $listOrder; ?>">
    <input type="hidden" name="list[direction]" value="<?php echo $listDirn; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
