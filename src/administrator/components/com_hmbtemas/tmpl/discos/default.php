<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
HTMLHelper::_('behavior.multiselect');
?>
<form action="<?= Route::_('index.php?option=com_hmbtemas&view=discos') ?>"
      method="post" name="adminForm" id="adminForm">

  <div class="d-flex flex-wrap gap-2 mb-3">
    <input type="text" name="filter[search]" class="form-control" style="max-width:280px"
           value="<?= $this->escape($this->state->get('filter.search','')) ?>"
           placeholder="Buscar por título o grupo…">
    <select name="filter[published]" class="form-select" style="max-width:160px"
            onchange="this.form.submit()">
      <option value="">— Todos —</option>
      <option value="1" <?= $this->state->get('filter.published')==='1'?'selected':'' ?>>Publicado</option>
      <option value="0" <?= $this->state->get('filter.published')==='0'?'selected':'' ?>>Oculto</option>
    </select>
    <button type="submit" class="btn btn-primary">Buscar</button>
    <a href="<?= Route::_('index.php?option=com_hmbtemas&view=discos') ?>"
       class="btn btn-secondary">Limpiar</a>
  </div>

  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th class="w-1"><?= HTMLHelper::_('grid.checkall') ?></th>
        <th class="w-1">Estado</th>
        <th>Título / Grupo</th>
        <th>Productora</th>
        <th class="w-5">Año</th>
        <th class="w-5">Portada</th>
        <th class="w-5">Audio</th>
        <th class="w-5">YouTube</th>
        <th class="w-3">ID</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($this->items)): ?>
      <tr><td colspan="9" class="text-center py-4 text-muted">
        No hay discos. <a href="<?= Route::_('index.php?option=com_hmbtemas&task=disco.add') ?>">Añade el primero</a>.
      </td></tr>
    <?php else: foreach ($this->items as $i => $item): ?>
      <tr>
        <td><?= HTMLHelper::_('grid.id', $i, $item->id) ?></td>
        <td><?= HTMLHelper::_('jgrid.published', $item->published, $i, 'discos.', true, 'cb') ?></td>
        <td>
          <a href="<?= Route::_('index.php?option=com_hmbtemas&task=disco.edit&id='.(int)$item->id) ?>">
            <strong><?= $this->escape($item->titulo) ?></strong>
          </a><br>
          <small class="text-muted"><?= $this->escape($item->grupo) ?></small>
        </td>
        <td><?= $this->escape($item->productora) ?></td>
        <td class="text-center"><?= $item->anno ?: '—' ?></td>
        <td class="text-center">
          <?php $pRaw = $item->portada ?? ''; if ($pRaw): ?>
            <img src="<?= $pRaw && str_starts_with($pRaw,'http') ? htmlspecialchars($pRaw) : Uri::root().htmlspecialchars($pRaw) ?>"
                 width="44" height="44" style="object-fit:cover;border-radius:3px;border:1px solid #dee2e6" alt="">
          <?php else: ?>—<?php endif; ?>
        </td>
        <td class="text-center">
          <?= $item->audio_file ? '<span class="badge bg-success">✓ MP3</span>' : '<span class="badge bg-light text-muted border">—</span>' ?>
        </td>
        <td class="text-center">
          <?php
          $yt = $item->youtube_links ? json_decode($item->youtube_links, true) : [];
          echo $yt ? '<span class="badge bg-danger">'.count($yt).' YT</span>' : '<span class="badge bg-light text-muted border">—</span>';
          ?>
        </td>
        <td class="text-muted small"><?= $item->id ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>

  <?= $this->pagination->getListFooter() ?>
  <input type="hidden" name="task" value="">
  <input type="hidden" name="boxchecked" value="0">
  <?= HTMLHelper::_('form.token') ?>
</form>
