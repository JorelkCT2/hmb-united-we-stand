<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');
?>
<form action="<?= Route::_('index.php?option=com_artistas&view=presupuestos') ?>" method="post" name="adminForm" id="adminForm">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">💶 Solicitudes de presupuesto</h4>
    <button type="button" class="btn btn-danger btn-sm"
        onclick="if(document.adminForm.boxchecked.value=='0'){alert('Selecciona al menos una solicitud.');}else if(confirm('¿Eliminar las solicitudes seleccionadas?')){Joomla.submitbutton('presupuestos.delete');}">
        🗑️ Eliminar seleccionadas
    </button>
</div>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="w-1 text-center"><input type="checkbox" name="checkall-toggle" onclick="Joomla.checkAll(this)"></th>
            <th>Artista</th><th>Cliente</th><th>Email</th><th>Tel</th><th>Evento</th><th>Fecha</th><th>Lugar</th><th class="text-center">Estado</th><th class="text-center">📧 Notif.</th><th>Recibido</th><th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($this->items as $i => $p): ?>
    <tr>
        <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $p->id) ?></td>
        <td><strong><?= htmlspecialchars($p->artista_name) ?></strong></td>
        <td><?= htmlspecialchars($p->client_name) ?></td>
        <td><a href="mailto:<?= htmlspecialchars($p->client_email) ?>"><?= htmlspecialchars($p->client_email) ?></a></td>
        <td><?= htmlspecialchars($p->client_phone) ?></td>
        <td><?= htmlspecialchars($p->event_type) ?></td>
        <td><?= $p->event_date ?></td>
        <td><?= htmlspecialchars($p->event_location) ?></td>
        <td class="text-center">
            <?php
            $estados = [0=>['danger','Nuevo'],1=>['info','En gestión'],2=>['success','Cerrado']];
            $e = $estados[$p->state] ?? $estados[0];
            ?>
            <div class="btn-group btn-group-sm">
                <?php foreach ($estados as $sv => [$sc, $sl]): ?>
                <a href="<?= Route::_('index.php?option=com_artistas&task=presupuestos.cambiarEstado&id='.$p->id.'&state='.$sv.'&'.HTMLHelper::_('form.token').'=1') ?>"
                   class="btn btn-<?= $sc ?><?= $p->state==$sv?' active':' opacity-50' ?>"
                   title="<?= $sl ?>"><?= $sl[0] ?></a>
                <?php endforeach; ?>
            </div>
        </td>
        <td class="text-center">
            <?php if (isset($p->email_enviado)): ?>
                <?php if ($p->email_enviado): ?>
                    <span title="Email enviado a <?= htmlspecialchars($p->email_artista ?? '') ?>" style="color:#28a745;font-size:1.1rem;cursor:help">✅</span>
                <?php elseif (!empty($p->email_artista)): ?>
                    <span title="Error al enviar a <?= htmlspecialchars($p->email_artista ?? '') ?>" style="color:#dc3545;font-size:1.1rem;cursor:help">❌</span>
                <?php else: ?>
                    <span title="El artista no tiene email registrado" style="color:#ffc107;font-size:1.1rem;cursor:help">⚠️</span>
                <?php endif; ?>
            <?php else: ?>
                <span style="color:#6c757d;font-size:.8rem">—</span>
            <?php endif; ?>
        </td>
        <td><small><?= date('d/m/Y H:i', strtotime($p->created)) ?></small></td>
        <td class="text-center">
            <?php if (!empty($p->message)): ?>
            <span title="<?= htmlspecialchars($p->message) ?>" style="cursor:help">💬</span>
            <?php endif; ?>
            <a href="<?= Route::_('index.php?option=com_artistas&task=presupuestos.delete&cid[]='.$p->id.'&'.HTMLHelper::_('form.token').'=1') ?>"
               class="btn btn-danger btn-sm ms-1"
               onclick="return confirm('¿Eliminar esta solicitud?')">✕</a>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($this->items)): ?>
    <tr><td colspan="11" class="text-center text-muted py-4">No hay solicitudes de presupuesto.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<?= $this->pagination->getListFooter() ?>
<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?= HTMLHelper::_('form.token') ?>
</form>
