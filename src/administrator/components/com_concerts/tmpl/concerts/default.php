<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
?>
<form action="<?= Route::_('index.php?option=com_concerts') ?>"
      method="post" name="adminForm" id="adminForm">

    <!-- Search bar -->
    <div class="row mb-3 g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" name="filter_search" id="filter_search"
                       value="<?= htmlspecialchars($this->state->get('filter.search', '')) ?>"
                       placeholder="Buscar evento, artista, ciudad, sala…" class="form-control" />
                <button type="submit" class="btn btn-primary">
                    <span class="icon-search" aria-hidden="true"></span>
                </button>
                <a href="<?= Route::_('index.php?option=com_concerts') ?>"
                   class="btn btn-secondary" title="Limpiar filtro">
                    <span class="icon-times" aria-hidden="true"></span>
                </a>
            </div>
        </div>
        <div class="col-md-2">
            <select name="filter_state" class="form-select" onchange="this.form.submit()">
                <option value="">— Estado —</option>
                <option value="1"  <?= $this->state->get('filter.state') === '1'  ? 'selected' : '' ?>>Publicado</option>
                <option value="0"  <?= $this->state->get('filter.state') === '0'  ? 'selected' : '' ?>>Sin publicar</option>
                <option value="-1" <?= $this->state->get('filter.state') === '-1' ? 'selected' : '' ?>>Cancelado</option>
            </select>
        </div>
    </div>

    <table class="table table-striped table-hover" id="concertList">
        <thead class="table-dark">
            <tr>
                <th width="20">
                    <input type="checkbox" onclick="Joomla.checkAll(this)" />
                </th>
                <th>Evento</th>
                <th>Fecha inicio</th>
                <th>Recinto</th>
                <th>Ciudad</th>
                <th>País</th>
                <th>Cartel</th>
                <th>Entradas</th>
                <th>Estado</th>
                <th width="60">ID</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($this->items)) : ?>
            <tr>
                <td colspan="10" class="text-center py-4 text-muted">
                    No hay conciertos.
                    <a href="<?= Route::_('index.php?option=com_concerts&task=concert.add') ?>">Crear uno</a>
                    o
                    <a href="<?= Route::_('index.php?option=com_concerts&view=import') ?>">importar CSV</a>.
                </td>
            </tr>
        <?php else : ?>
            <?php foreach ($this->items as $i => $item) :
                $stColors = [1 => 'success', 0 => 'secondary', -1 => 'danger'];
                $stLabels = [1 => 'Publicado', 0 => 'Sin pub.', -1 => 'Cancelado'];
                $col   = $stColors[(int)$item->state] ?? 'secondary';
                $label = $stLabels[(int)$item->state] ?? '?';
                $multiday = !empty($item->date_end) && $item->date_end !== $item->date_start;
            ?>
            <tr>
                <td><?= HTMLHelper::_('grid.id', $i, $item->id) ?></td>
                <td>
                    <a href="<?= Route::_('index.php?option=com_concerts&task=concert.edit&id='.$item->id) ?>"
                       class="fw-bold">
                        <?= htmlspecialchars($item->title ?: $item->band) ?>
                    </a>
                    <?php if (!empty($item->genre)) : ?>
                        <br><small class="text-muted"><?= htmlspecialchars($item->genre) ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?= htmlspecialchars($item->date_start) ?>
                    <?php if ($multiday) : ?>
                        <br><small class="text-info">→ <?= htmlspecialchars($item->date_end) ?></small>
                    <?php endif; ?>
                    <?php if (!empty($item->concert_time)) : ?>
                        <br><small class="text-muted">🕐 <?= substr($item->concert_time, 0, 5) ?></small>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item->venue) ?></td>
                <td><?= htmlspecialchars($item->city) ?>
                    <?php if (!empty($item->province)) : ?>
                        <br><small class="text-muted"><?= htmlspecialchars($item->province) ?></small>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item->country_name) ?></td>
                <td>
                    <?php if (!empty($item->poster_url)) : ?>
                        <a href="<?= htmlspecialchars($item->poster_url) ?>" target="_blank"
                           class="btn btn-sm btn-outline-secondary">🖼 Ver</a>
                    <?php else : ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($item->ticket_url)) : ?>
                        <a href="<?= htmlspecialchars($item->ticket_url) ?>" target="_blank"
                           class="btn btn-sm btn-outline-primary">🎟 Link</a>
                    <?php else : ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td><span class="badge bg-<?= $col ?>"><?= $label ?></span></td>
                <td><?= $item->id ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?= $this->pagination->getListFooter() ?>

    <input type="hidden" name="task"       value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?= HTMLHelper::_('form.token') ?>
</form>
