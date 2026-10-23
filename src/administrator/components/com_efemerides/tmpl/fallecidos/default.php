<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');
?>
<form action="<?php echo Route::_('index.php?option=com_efemerides&view=fallecidos'); ?>" method="post" name="adminForm" id="adminForm">

    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" name="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                   class="form-control" placeholder="Buscar por artista...">
        </div>
        <div class="col-md-3">
            <input type="number" name="filter_year" value="<?php echo $this->escape($this->state->get('filter.year')); ?>"
                   class="form-control" placeholder="Año de fallecimiento">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary"><span class="icon-search"></span> Buscar</button>
            <a href="index.php?option=com_efemerides&view=fallecidos" class="btn btn-secondary"><span class="icon-undo"></span></a>
        </div>
    </div>

    <?php
    // Agrupar por año
    $byYear = [];
    foreach ($this->items as $item) {
        $year = $item->fecha_fallecimiento ? date('Y', strtotime($item->fecha_fallecimiento)) : 'Sin fecha';
        $byYear[$year][] = $item;
    }
    krsort($byYear);
    ?>

    <?php foreach ($byYear as $year => $items): ?>
    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><span class="icon-star"></span> <?php echo $year; ?> — <?php echo count($items); ?> artistas</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th width="1%"><input type="checkbox" onclick="Joomla.checkAll(this)"></th>
                        <th width="80px">Foto</th>
                        <th>Artista</th>
                        <th>Fecha</th>
                        <th>Wikipedia</th>
                        <th>Efeméride</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                    <td>
                        <?php if (!empty($item->foto)): ?>
                            <img src="<?php echo htmlspecialchars($item->foto); ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:50%;">
                        <?php else: ?>
                            <span class="icon-user-circle" style="font-size:40px;color:#ccc;"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="index.php?option=com_efemerides&task=fallecido.edit&id=<?php echo $item->id; ?>">
                            <strong><?php echo $this->escape($item->artista); ?></strong>
                        </a>
                        <?php if (!empty($item->notas)): ?>
                            <br><small class="text-muted"><?php echo $this->escape(mb_substr($item->notas, 0, 80)); ?>...</small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item->fecha_fallecimiento ? date('d/m/Y', strtotime($item->fecha_fallecimiento)) : '—'; ?></td>
                    <td>
                        <?php if (!empty($item->wikipedia_url)): ?>
                            <a href="<?php echo htmlspecialchars($item->wikipedia_url); ?>" target="_blank" class="btn btn-xs btn-outline-secondary">
                                <span class="icon-wikipedia"></span> Wikipedia
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item->efemeride_titulo): ?>
                            <small><?php echo $this->escape(mb_substr($item->efemeride_titulo, 0, 40)); ?></small>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'fallecidos.', true); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($this->items)): ?>
        <div class="alert alert-info">No hay artistas fallecidos registrados. Se añaden automáticamente cuando la efeméride es de categoría "Defunciones".</div>
    <?php endif; ?>

    <?php echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
