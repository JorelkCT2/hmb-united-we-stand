<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>

<form action="<?= Route::_('index.php?option=com_hmbtemas&view=tema&layout=edit&id=' . (int) $this->item->id) ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

  <div class="row g-4">

    <?php /* ── Columna principal ── */ ?>
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <span style="font-size:1.3rem">🎵</span>
          <strong>Datos del tema</strong>
        </div>
        <div class="card-body">

          <?php /* Título */ ?>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="jform_title">
              Título <span class="text-danger">*</span>
            </label>
            <?= $this->form->getInput('title') ?>
          </div>

          <?php /* Álbum */ ?>
          <div class="row g-3 mb-3">
            <div class="col-md-7">
              <label class="form-label fw-semibold" for="jform_album">Álbum</label>
              <?= $this->form->getInput('album') ?>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold" for="jform_album_year">Año</label>
              <?= $this->form->getInput('album_year') ?>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold" for="jform_track_number">Pista nº</label>
              <?= $this->form->getInput('track_number') ?>
            </div>
          </div>

          <?php /* Duración */ ?>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="jform_duration">
              Duración <small class="text-muted fw-normal">(formato mm:ss, ej: 3:47)</small>
            </label>
            <?= $this->form->getInput('duration') ?>
          </div>

          <?php /* Descripción */ ?>
          <div class="mb-0">
            <label class="form-label fw-semibold" for="jform_description">Descripción</label>
            <?= $this->form->getInput('description') ?>
            <small class="text-muted">Breve descripción visible en la web (opcional).</small>
          </div>

        </div>
      </div>

      <?php /* ── Card enlaces ── */ ?>
      <div class="card mt-3">
        <div class="card-header d-flex align-items-center gap-2">
          <span style="font-size:1.3rem">🔗</span>
          <strong>Enlaces externos</strong>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="jform_spotify_url">
              <img src="https://open.spotify.com/favicon.ico" width="16" height="16" alt=""> Spotify URL
            </label>
            <?= $this->form->getInput('spotify_url') ?>
          </div>
          <div class="mb-0">
            <label class="form-label" for="jform_youtube_url">
              <img src="https://www.youtube.com/favicon.ico" width="16" height="16" alt=""> YouTube URL
            </label>
            <?= $this->form->getInput('youtube_url') ?>
          </div>
        </div>
      </div>
    </div>

    <?php /* ── Columna lateral ── */ ?>
    <div class="col-lg-4">

      <?php /* ── Archivo de audio ── */ ?>
      <div class="card mb-3">
        <div class="card-header d-flex align-items-center gap-2">
          <span style="font-size:1.3rem">🎧</span>
          <strong>Archivo de audio</strong>
        </div>
        <div class="card-body">
          <?= $this->form->getInput('audio_file') ?>
          <small class="text-muted d-block mt-2">
            Sube el MP3 al gestor de medios en <code>/media/hmb/audio/</code> y selecciónalo aquí.
          </small>
          <?php if (!empty($this->item->audio_file)): ?>
          <div class="mt-2 p-2 bg-light rounded">
            <small class="text-muted">Actual:</small><br>
            <code style="font-size:.78rem;word-break:break-all"><?= htmlspecialchars($this->item->audio_file) ?></code>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <?php /* ── Portada ── */ ?>
      <div class="card mb-3">
        <div class="card-header d-flex align-items-center gap-2">
          <span style="font-size:1.3rem">🖼️</span>
          <strong>Portada del álbum</strong>
        </div>
        <div class="card-body">
          <?php if (!empty($this->item->cover_image)): ?>
          <img src="<?= \Joomla\CMS\Uri\Uri::root() . htmlspecialchars($this->item->cover_image) ?>"
               class="img-fluid rounded mb-2"
               style="max-height:140px;object-fit:cover"
               alt="Portada actual">
          <?php endif; ?>
          <?= $this->form->getInput('cover_image') ?>
          <small class="text-muted d-block mt-1">
            Recomendado: imagen cuadrada, mínimo 300×300 px.
          </small>
        </div>
      </div>

      <?php /* ── Estado y orden ── */ ?>
      <div class="card">
        <div class="card-header"><strong>Publicación</strong></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="jform_published">Estado</label>
            <?= $this->form->getInput('published') ?>
          </div>
          <div class="mb-0">
            <label class="form-label" for="jform_ordering">Orden</label>
            <?= $this->form->getInput('ordering') ?>
            <small class="text-muted">Menor número = aparece antes.</small>
          </div>
        </div>
      </div>

    </div>
  </div>

  <input type="hidden" name="task" value="">
  <input type="hidden" name="jform[id]" value="<?= (int) $this->item->id ?>">
  <?= HTMLHelper::_('form.token') ?>
</form>
