<?php defined('_JEXEC') or die; ?>
<form action="<?= \Joomla\CMS\Router\Route::_('index.php?option=com_hmbtemas&view=temas') ?>"
      method="post" name="adminForm" id="adminForm">

  <div class="row">
    <div class="col-md-12">

      <?php /* Buscador */ ?>
      <div class="mb-3 d-flex gap-2">
        <input type="text" name="filter[search]" id="filter_search"
               class="form-control" style="max-width:320px"
               value="<?= htmlspecialchars($this->state->get('filter.search', ''), ENT_QUOTES) ?>"
               placeholder="Buscar por título o álbum…">
        <select name="filter[published]" class="form-select" style="max-width:160px"
                onchange="document.getElementById('adminForm').submit()">
          <option value="">— Estado —</option>
          <option value="1" <?= $this->state->get('filter.published') === '1' ? 'selected' : '' ?>>Publicado</option>
          <option value="0" <?= $this->state->get('filter.published') === '0' ? 'selected' : '' ?>>No publicado</option>
        </select>
        <button type="submit" class="btn btn-primary">Buscar</button>
        <a href="<?= \Joomla\CMS\Router\Route::_('index.php?option=com_hmbtemas&view=temas') ?>"
           class="btn btn-secondary">Limpiar</a>
      </div>

      <table class="table table-striped table-hover" id="temaslist">
        <thead>
          <tr>
            <th style="width:1%"><input type="checkbox" onclick="Joomla.checkAll(this)"></th>
            <th style="width:4%">#</th>
            <th>Título</th>
            <th>Álbum</th>
            <th style="width:6%">Año</th>
            <th style="width:5%">Pista</th>
            <th style="width:6%">Duración</th>
            <th style="width:5%">Audio</th>
            <th style="width:5%">Portada</th>
            <th style="width:7%">Estado</th>
            <th style="width:5%">ID</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($this->items)): ?>
          <tr><td colspan="11" class="text-center text-muted py-4">No hay temas. <a href="<?= \Joomla\CMS\Router\Route::_('index.php?option=com_hmbtemas&task=tema.add') ?>">Añade el primero</a>.</td></tr>
          <?php else: foreach ($this->items as $i => $item):
            $s = (int) $item->duration;
            $dur = $s ? sprintf('%d:%02d', intdiv($s,60), $s%60) : '—';
          ?>
          <tr>
            <td><input type="checkbox" name="cid[]" value="<?= $item->id ?>" onclick="Joomla.isChecked(this.checked)"></td>
            <td><?= $item->ordering ?></td>
            <td>
              <a href="<?= \Joomla\CMS\Router\Route::_('index.php?option=com_hmbtemas&task=tema.edit&id=' . $item->id) ?>">
                <strong><?= htmlspecialchars($item->title) ?></strong>
              </a>
            </td>
            <td><?= htmlspecialchars($item->album) ?></td>
            <td><?= $item->album_year ?: '—' ?></td>
            <td class="text-center"><?= $item->track_number ?: '—' ?></td>
            <td class="text-center"><?= $dur ?></td>
            <td class="text-center">
              <?php if ($item->audio_file): ?>
                <span class="badge bg-success" title="<?= htmlspecialchars($item->audio_file) ?>">✓</span>
              <?php else: ?>
                <span class="badge bg-secondary">—</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if ($item->cover_image): ?>
                <img src="<?= \Joomla\CMS\Uri\Uri::root() . htmlspecialchars($item->cover_image) ?>"
                     width="36" height="36" style="object-fit:cover;border-radius:2px" alt="">
              <?php else: ?>
                <span class="badge bg-secondary">—</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <?php if ($item->published): ?>
                <a href="<?= \Joomla\CMS\Router\Route::_('index.php?option=com_hmbtemas&task=temas.unpublish&cid[]='.$item->id.'&'.Session::getFormToken().'=1') ?>"
                   class="badge bg-success text-decoration-none">Publicado</a>
              <?php else: ?>
                <a href="<?= \Joomla\CMS\Router\Route::_('index.php?option=com_hmbtemas&task=temas.publish&cid[]='.$item->id.'&'.Session::getFormToken().'=1') ?>"
                   class="badge bg-secondary text-decoration-none">Oculto</a>
              <?php endif; ?>
            </td>
            <td><?= $item->id ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>

      <?= $this->pagination->getListFooter() ?>
    </div>
  </div>

  <input type="hidden" name="task" value="">
  <input type="hidden" name="boxchecked" value="0">
  <?= \Joomla\CMS\HTML\HTMLHelper::_('form.token') ?>
</form>
