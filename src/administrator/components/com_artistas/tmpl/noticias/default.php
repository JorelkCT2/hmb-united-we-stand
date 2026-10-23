<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
HTMLHelper::_('behavior.multiselect');
?>
<form action="<?= Route::_('index.php?option=com_artistas&view=noticias') ?>" method="post" name="adminForm" id="adminForm">

<div class="row mb-3 g-2 align-items-center">
    <div class="col-md-4">
        <input type="text" name="filter_search" class="form-control" placeholder="🔍 Buscar..."
               value="<?= htmlspecialchars($this->state->get('filter.search','')) ?>"
               onchange="this.form.submit()">
    </div>
    <div class="col-md-2">
        <select name="filter_tipo" class="form-select" onchange="this.form.submit()">
            <option value="">— Tipo —</option>
            <option value="concierto" <?= $this->state->get('filter.tipo')==='concierto'?'selected':'' ?>>🎤 Conciertos</option>
            <option value="album" <?= $this->state->get('filter.tipo')==='album'?'selected':'' ?>>💿 Álbumes</option>
        </select>
    </div>
    <div class="col-md-2">
        <select name="filter_state" class="form-select" onchange="this.form.submit()">
            <option value="">— Estado —</option>
            <option value="1" <?= $this->state->get('filter.state')==='1'?'selected':'' ?>>✓ Publicadas</option>
            <option value="0" <?= $this->state->get('filter.state')==='0'?'selected':'' ?>>⏸ Despublicadas</option>
        </select>
    </div>
    <div class="col-md-4 d-flex gap-1">
        <button type="button" class="btn btn-success btn-sm" onclick="doTask('noticias.publish')">✓ Publicar</button>
        <button type="button" class="btn btn-warning btn-sm" onclick="doTask('noticias.unpublish')">⏸ Despublicar</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="doTask('noticias.delete', '¿Eliminar seleccionadas?')">🗑️ Eliminar</button>
    </div>
</div>

<table class="table table-striped table-hover table-sm">
    <thead>
        <tr>
            <th style="width:30px"><input type="checkbox" id="cb-all" onchange="toggleAll(this)"></th>
            <th>Artista</th>
            <th>Título</th>
            <th>Tipo</th>
            <th>Fecha</th>
            <th>Lugar / Disco / Provincia</th>
            <th class="text-center">Estado</th>
            <th>Creado</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($this->items as $n): ?>
    <tr>
        <td><input type="checkbox" name="cid[]" value="<?= $n->id ?>" class="cb-item" onchange="updateCount()"></td>
        <td><strong><?= htmlspecialchars($n->artista_name) ?></strong></td>
        <td>
            <a href="<?= Route::_('index.php?option=com_artistas&view=noticia&id=' . $n->id) ?>" style="text-decoration:none">
                <small><?= htmlspecialchars($n->titulo ?? '-') ?></small>
            </a>
        </td>
        <td>
            <?= $n->tipo === 'concierto'
                ? '<span class="badge bg-primary">🎤 Concierto</span>'
                : '<span class="badge bg-warning text-dark">💿 Álbum</span>' ?>
        </td>
        <td><?= $n->fecha_evento ? date('d/m/Y', strtotime($n->fecha_evento)) : '-' ?></td>
        <td>
            <small>
            <?php if ($n->lugar): ?><?= htmlspecialchars($n->lugar) ?><?php endif; ?>
            <?php if ($n->provincia): ?> (<?= htmlspecialchars($n->provincia) ?>)<?php endif; ?>
            <?php if ($n->nombre_disco): ?>💿 <?= htmlspecialchars($n->nombre_disco) ?><?php endif; ?>
            </small>
        </td>
        <td class="text-center">
            <a href="<?= Route::_('index.php?option=com_artistas&task=noticias.togglestate&id=' . $n->id . '&state=' . (1 - $n->state) . '&' . Session::getFormToken() . '=1') ?>"
               title="Cambiar estado">
                <?= $n->state == 1 ? '<span class="badge bg-success">✓</span>' : '<span class="badge bg-secondary">⏸</span>' ?>
            </a>
        </td>
        <td><small><?= date('d/m/Y H:i', strtotime($n->created)) ?></small></td>
        <td class="text-center" style="white-space:nowrap">
            <a href="<?= Route::_('index.php?option=com_artistas&view=noticia&id=' . $n->id) ?>"
               class="btn btn-sm btn-outline-primary" title="Editar noticia">✏️</a>
            <a href="<?= Route::_('index.php?option=com_artistas&task=noticias.delete&cid[]=' . $n->id . '&' . Session::getFormToken() . '=1') ?>"
               class="btn btn-sm btn-outline-danger ms-1"
               onclick="return confirm('¿Eliminar esta noticia?')">✕</a>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($this->items)): ?>
    <tr><td colspan="9" class="text-center text-muted py-4">No hay noticias aún.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<?= $this->pagination->getListFooter() ?>

<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?= HTMLHelper::_('form.token') ?>
</form>

<script>
function toggleAll(cb) {
    document.querySelectorAll('.cb-item').forEach(c => c.checked = cb.checked);
    updateCount();
}
function updateCount() {
    const n = document.querySelectorAll('.cb-item:checked').length;
    document.querySelector('[name=boxchecked]').value = n;
}
function doTask(task, confirm_msg) {
    const checked = document.querySelectorAll('.cb-item:checked').length;
    if (!checked) { alert('Selecciona al menos una noticia.'); return; }
    if (confirm_msg && !confirm(confirm_msg)) return;
    document.querySelector('[name=task]').value = task;
    document.getElementById('adminForm').submit();
}
</script>
