<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');
?>
<form action="<?= Route::_('index.php?option=com_artistas&view=resenas') ?>" method="post" name="adminForm" id="adminForm">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">⭐ Reseñas</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-success btn-sm"
            onclick="if(document.adminForm.boxchecked.value=='0'){alert('Selecciona al menos una reseña.');}else if(confirm('¿Aprobar las reseñas seleccionadas?')){Joomla.submitbutton('resenas.aprobar');}">
            ✓ Aprobar seleccionadas
        </button>
        <button type="button" class="btn btn-danger btn-sm"
            onclick="if(document.adminForm.boxchecked.value=='0'){alert('Selecciona al menos una reseña.');}else if(confirm('¿Eliminar las reseñas seleccionadas?')){Joomla.submitbutton('resenas.delete');}">
            🗑️ Eliminar seleccionadas
        </button>
    </div>
</div>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="w-1 text-center"><input type="checkbox" name="checkall-toggle" onclick="Joomla.checkAll(this)"></th>
            <th>Artista</th><th>Revisor</th><th>Valoración</th><th>Reseña</th><th>Evento</th><th class="text-center">Estado</th><th>Fecha</th><th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($this->items as $i => $r): ?>
    <tr class="<?= $r->state==0 ? 'table-warning' : '' ?>">
        <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $r->id) ?></td>
        <td><?= htmlspecialchars($r->artista_name) ?></td>
        <td>
            <strong><?= htmlspecialchars($r->reviewer_name) ?></strong>
            <?php if (!empty($r->reviewer_email)): ?>
            <br><small><a href="mailto:<?= htmlspecialchars($r->reviewer_email) ?>"><?= htmlspecialchars($r->reviewer_email) ?></a></small>
            <?php endif; ?>
        </td>
        <td class="text-warning"><?= str_repeat('★', (int)$r->rating) ?><span class="text-muted"><?= str_repeat('★', 5-(int)$r->rating) ?></span></td>
        <td style="max-width:250px"><small><?= htmlspecialchars(mb_substr($r->review, 0, 120)) ?><?= mb_strlen($r->review)>120?'…':'' ?></small></td>
        <td><small><?= htmlspecialchars($r->event_type) ?></small></td>
        <td class="text-center">
            <?php if ($r->state==1): ?>
                <span class="badge bg-success">✓ Aprobada</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark">⏳ Pendiente</span>
            <?php endif; ?>
        </td>
        <td><small><?= date('d/m/Y', strtotime($r->created)) ?></small></td>
        <td class="text-center" style="white-space:nowrap">
            <?php if ($r->state==0): ?>
            <a href="<?= Route::_('index.php?option=com_artistas&task=resenas.aprobar&cid[]='.$r->id.'&'.HTMLHelper::_('form.token').'=1') ?>"
               class="btn btn-success btn-sm">✓ Aprobar</a>
            <?php endif; ?>
            <a href="<?= Route::_('index.php?option=com_artistas&task=resenas.delete&cid[]='.$r->id.'&'.HTMLHelper::_('form.token').'=1') ?>"
               class="btn btn-danger btn-sm ms-1"
               onclick="return confirm('¿Eliminar esta reseña?')">✕</a>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($this->items)): ?>
    <tr><td colspan="9" class="text-center text-muted py-4">No hay reseñas.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<?= $this->pagination->getListFooter() ?>
<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?= HTMLHelper::_('form.token') ?>
</form>
