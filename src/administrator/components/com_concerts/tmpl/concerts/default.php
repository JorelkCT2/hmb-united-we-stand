<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
?>
<form action="<?= Route::_('index.php?option=com_concerts') ?>"
      method="post" name="adminForm" id="adminForm">

    <!-- Filtros -->
    <div class="row mb-3 g-2 align-items-center">
        <div class="col-lg-3 col-md-6">
            <div class="input-group">
                <input type="text" name="filter_search" id="filter_search"
                       value="<?= htmlspecialchars($this->state->get('filter.search', '')) ?>"
                       placeholder="Buscar evento, banda, ciudad, sala…" class="form-control" />
                <button type="submit" class="btn btn-primary"><span class="icon-search" aria-hidden="true"></span></button>
                <a href="<?= Route::_('index.php?option=com_concerts&filter_search=&filter_state=&filter_tipo=&filter_fecha=proximos&filter_cartel=&filter_geo=&filter_origen=') ?>" class="btn btn-secondary" title="Limpiar filtros"><span class="icon-times" aria-hidden="true"></span></a>
            </div>
        </div>
        <?php
        $sel = fn(string $k, string $v): string => (string) $this->state->get('filter.' . $k) === $v ? 'selected' : '';
        ?>
        <div class="col-auto"><select name="filter_fecha" class="form-select" onchange="this.form.submit()">
            <option value="proximos" <?= $sel('fecha', 'proximos') ?>>Próximos</option>
            <option value="pasados" <?= $sel('fecha', 'pasados') ?>>Ya celebrados</option>
            <option value="" <?= $sel('fecha', '') ?>>Todas las fechas</option>
        </select></div>
        <div class="col-auto"><select name="filter_state" class="form-select" onchange="this.form.submit()">
            <option value="">— Estado —</option>
            <option value="pend" <?= $sel('state', 'pend') ?>>⚠ Pendientes de revisar</option>
            <option value="1" <?= $sel('state', '1') ?>>Publicado</option>
            <option value="0" <?= $sel('state', '0') ?>>Sin publicar</option>
            <option value="-1" <?= $sel('state', '-1') ?>>Cancelado</option>
        </select></div>
        <div class="col-auto"><select name="filter_tipo" class="form-select" onchange="this.form.submit()">
            <option value="">— Tipo —</option>
            <option value="concert" <?= $sel('tipo', 'concert') ?>>Conciertos</option>
            <option value="festival" <?= $sel('tipo', 'festival') ?>>Festivales</option>
        </select></div>
        <div class="col-auto"><select name="filter_origen" class="form-select" onchange="this.form.submit()">
            <option value="">— Origen —</option>
            <?php foreach ($this->origenes as $o) : ?><option value="<?= htmlspecialchars($o) ?>" <?= $sel('origen', $o) ?>><?= htmlspecialchars($o) ?></option><?php endforeach; ?>
        </select></div>
        <div class="col-auto"><select name="filter_cartel" class="form-select" onchange="this.form.submit()">
            <option value="">Cartel: todos</option><option value="0" <?= $sel('cartel', '0') ?>>Sin cartel</option>
        </select></div>
        <div class="col-auto"><select name="filter_geo" class="form-select" onchange="this.form.submit()">
            <option value="">Mapa: todos</option><option value="0" <?= $sel('geo', '0') ?>>Sin situar en el mapa</option>
        </select></div>
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
                <th>Mapa</th>
                <th>Cartel</th>
                <th>Entradas</th>
                <th>Estado</th>
                <th width="60">ID</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($this->items)) : ?>
            <tr>
                <td colspan="11" class="text-center py-4 text-muted">
                    No hay eventos con estos filtros.
                    <a href="<?= Route::_('index.php?option=com_concerts&task=concert.add') ?>">Crear uno</a>
                    o
                    <a href="<?= Route::_('index.php?option=com_concerts&view=import') ?>">importar o actualizar desde fuentes</a>.
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
                    <?php if (($item->event_type ?? '') === 'festival') : ?><span class="badge bg-info">Festival</span><?php endif; ?>
                    <?php if (!empty($item->source) && $item->source !== 'manual') : ?><span class="badge bg-secondary" title="Origen del dato"><?= htmlspecialchars($item->source) ?></span><?php endif; ?>
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
                <td class="text-center"><?= ((float) ($item->latitude ?? 0) != 0.0) ? '<span title="Situado en el mapa">📍</span>' : '<span class="text-muted" title="Sin coordenadas">—</span>' ?></td>
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
