<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.multiselect');
?>

<form action="<?= Route::_('index.php?option=com_hmbtemas&view=temas') ?>"
      method="post" name="adminForm" id="adminForm">

  <?php /* Buscador */ ?>
  <div class="d-flex flex-wrap gap-2 mb-3">
    <input type="text" name="filter[search]" id="filter_search"
           class="form-control" style="max-width:300px"
           value="<?= $this->escape($this->state->get('filter.search', '')) ?>"
           placeholder="Buscar por título o álbum…">

    <select name="filter[published]" class="form-select" style="max-width:160px"
            onchange="this.form.submit()">
      <option value="">— Todos los estados —</option>
      <option value="1" <?= $this->state->get('filter.published') === '1' ? 'selected' : '' ?>>Publicado</option>
      <option value="0" <?= $this->state->get('filter.published') === '0' ? 'selected' : '' ?>>No publicado</option>
    </select>

    <button type="submit" class="btn btn-primary">Buscar</button>
    <a href="<?= Route::_('index.php?option=com_hmbtemas&view=temas') ?>"
       class="btn btn-secondary">Limpiar</a>
  </div>

  <table class="table table-striped table-hover" id="temaslist">
    <thead>
      <tr>
        <th class="w-1 text-center">
          <?= HTMLHelper::_('grid.checkall') ?>
        </th>
        <th class="w-5 text-center">Estado</th>
        <th>Título</th>
        <th>Álbum</th>
        <th class="w-5 text-center">Año</th>
        <th class="w-5 text-center">Pista</th>
        <th class="w-7 text-center">Duración</th>
        <th class="w-5 text-center">Audio</th>
        <th class="w-5 text-center">Portada</th>
        <th class="w-3 text-center">ID</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($this->items)) : ?>
      <tr>
        <td colspan="10" class="text-center py-4 text-muted">
          No hay temas todavía.
          <a href="<?= Route::_('index.php?option=com_hmbtemas&task=tema.add') ?>">
            Añade el primero aquí.
          </a>
        </td>
      </tr>
    <?php else : ?>
      <?php foreach ($this->items as $i => $item) :
        $s   = (int) $item->duration;
        $dur = $s ? sprintf('%d:%02d', intdiv($s, 60), $s % 60) : '—';
        $editUrl = Route::_('index.php?option=com_hmbtemas&task=tema.edit&id=' . (int) $item->id);
      ?>
      <tr>
        <td class="text-center">
          <?= HTMLHelper::_('grid.id', $i, $item->id) ?>
        </td>
        <td class="text-center">
          <?= HTMLHelper::_('jgrid.published', $item->published, $i, 'temas.', true, 'cb') ?>
        </td>
        <td>
          <a href="<?= $editUrl ?>">
            <strong><?= $this->escape($item->title) ?></strong>
          </a>
        </td>
        <td><?= $this->escape($item->album) ?></td>
        <td class="text-center"><?= $item->album_year ?: '—' ?></td>
        <td class="text-center"><?= $item->track_number ?: '—' ?></td>
        <td class="text-center font-monospace"><?= $dur ?></td>
        <td class="text-center">
          <?php if ($item->audio_file) : ?>
            <span class="badge bg-success" title="<?= $this->escape($item->audio_file) ?>">✓ MP3</span>
          <?php else : ?>
            <span class="badge bg-light text-muted border">—</span>
          <?php endif; ?>
        </td>
        <td class="text-center">
          <?php if ($item->cover_image) : ?>
            <img src="<?= Uri::root() . $this->escape($item->cover_image) ?>"
                 width="40" height="40"
                 style="object-fit:cover;border-radius:3px;border:1px solid #dee2e6"
                 alt="">
          <?php else : ?>
            <span class="badge bg-light text-muted border">—</span>
          <?php endif; ?>
        </td>
        <td class="text-center text-muted small"><?= $item->id ?></td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>

  <?= $this->pagination->getListFooter() ?>

  <input type="hidden" name="task"       value="">
  <input type="hidden" name="boxchecked" value="0">
  <?= HTMLHelper::_('form.token') ?>
</form>
