<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$siteRoot = Uri::root();

$audios = [];
$images = [];
$extAudio = ['mp3','ogg','wav','m4a'];
$extImage = ['jpg','jpeg','png','gif','webp'];
$imagesDir = JPATH_ROOT . '/images';

if (is_dir($imagesDir)) {
    $rit = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($imagesDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($rit as $file) {
        if (!$file->isFile()) continue;
        $ext  = strtolower($file->getExtension());
        $full = $file->getPathname();
        $rel  = str_replace('\\', '/', ltrim(str_replace(JPATH_ROOT, '', $full), '/\\'));
        $entry = ['path' => $rel, 'name' => $file->getFilename(), 'folder' => basename(dirname($full))];
        if (in_array($ext, $extAudio, true))     $audios[] = $entry;
        elseif (in_array($ext, $extImage, true)) $images[] = $entry;
    }
}
?>
<form action="<?= Route::_('index.php?option=com_hmbtemas&view=tema&layout=edit&id=' . (int) $this->item->id) ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card mb-3">
        <div class="card-header fw-bold">🎵 Datos del tema</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" for="jform_title">Título <span class="text-danger">*</span></label>
            <?= $this->form->getInput('title') ?>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="jform_album">Álbum</label>
              <?= $this->form->getInput('album') ?>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold" for="jform_album_year">Año</label>
              <?= $this->form->getInput('album_year') ?>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold" for="jform_track_number">N.º pista</label>
              <?= $this->form->getInput('track_number') ?>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="jform_duration">
              Duración <small class="text-muted fw-normal">(mm:ss — ej: 3:47)</small>
            </label>
            <?= $this->form->getInput('duration') ?>
          </div>
          <div>
            <label class="form-label fw-semibold" for="jform_description">Descripción</label>
            <?= $this->form->getInput('description') ?>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-header fw-bold">🔗 Enlaces externos</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="jform_spotify_url">Spotify URL</label>
            <?= $this->form->getInput('spotify_url') ?>
          </div>
          <div>
            <label class="form-label" for="jform_youtube_url">YouTube URL</label>
            <?= $this->form->getInput('youtube_url') ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-header fw-bold">🎧 Archivo de audio (MP3)</div>
        <div class="card-body">
          <div class="input-group mb-1">
            <?= $this->form->getInput('audio_file') ?>
            <button type="button" class="btn btn-danger"
                    onclick="abrirSelectorHmb('audio')" title="Seleccionar MP3">📁</button>
          </div>
          <?php if (!empty($this->item->audio_file)): ?>
          <div class="mt-1 p-2 bg-light rounded border">
            <small class="text-muted">Actual:</small><br>
            <code style="font-size:.75rem;word-break:break-all"><?= htmlspecialchars($this->item->audio_file) ?></code>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header fw-bold">🖼️ Portada del álbum</div>
        <div class="card-body">
          <div id="hmb-cover-preview" style="<?= empty($this->item->cover_image) ? 'display:none' : '' ?>" class="mb-2">
            <img id="hmb-cover-img"
                 src="<?= !empty($this->item->cover_image) ? $siteRoot . htmlspecialchars($this->item->cover_image) : '' ?>"
                 class="img-fluid rounded" style="max-height:150px;width:100%;object-fit:cover" alt="">
          </div>
          <?= $this->form->getInput('cover_image') ?>
        </div>
      </div>

      <div class="card">
        <div class="card-header fw-bold">Publicación</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="jform_published">Estado</label>
            <?= $this->form->getInput('published') ?>
          </div>
          <div>
            <label class="form-label" for="jform_ordering">Orden</label>
            <?= $this->form->getInput('ordering') ?>
            <div class="form-text">Número más bajo = aparece antes.</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" name="task"      value="">
  <input type="hidden" name="jform[id]" value="<?= (int) $this->item->id ?>">
  <?= HTMLHelper::_('form.token') ?>
</form>

<!-- MODAL -->
<div class="modal fade" id="hmbSelectorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content" style="background:#1a1a1a;color:#e0e0e0;border:1px solid #333">
      <div class="modal-header" style="background:#111;border-bottom:2px solid #cc0000">
        <h5 class="modal-title" id="hmbSelectorTitulo">Seleccionar archivo</h5>
        <div class="ms-auto d-flex gap-2 align-items-center">
          <input type="text" id="hmbSelectorBuscar" placeholder="Buscar…"
                 class="form-control form-control-sm"
                 style="background:#222;border-color:#444;color:#e0e0e0;width:200px"
                 oninput="filtrarArchivosHmb(this.value)">
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body p-2" style="min-height:350px">
        <div id="hmbSelectorGrid"
             style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.6rem;padding:.5rem">
        </div>
        <p id="hmbSelectorVacio" class="text-center text-muted py-5" style="display:none">
          No se encontraron archivos en <code>/images/</code>.
        </p>
      </div>
      <div class="modal-footer" style="background:#111;border-top:1px solid #333">
        <code id="hmbSelectorRuta" class="me-auto text-muted" style="font-size:.8rem">
          — ningún archivo seleccionado —
        </code>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="hmbSelectorOk"
                onclick="confirmarSeleccionHmb()" disabled>✓ Seleccionar</button>
      </div>
    </div>
  </div>
</div>

<style>
.hmb-fi{background:#222;border:2px solid #333;border-radius:4px;cursor:pointer;overflow:hidden;transition:all .15s;text-align:center}
.hmb-fi:hover{border-color:#cc0000;background:#2a0000}
.hmb-fi.hmb-sel{border-color:#cc0000;background:#2a0000;box-shadow:0 0 0 2px #cc0000}
.hmb-fi-prev{height:90px;display:flex;align-items:center;justify-content:center;background:#111;overflow:hidden}
.hmb-fi-prev img{width:100%;height:100%;object-fit:cover}
.hmb-fi-ic{font-size:2.2rem;color:#cc0000}
.hmb-fi-name{padding:.3rem .4rem;font-size:.68rem;color:#bbb;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hmb-fi-folder{font-size:.6rem;color:#555;padding-bottom:.3rem}
/* Overlay propio sin depender de bootstrap.Modal JS */
#hmbOverlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;overflow-y:auto;padding:2rem 1rem}
#hmbOverlay.active{display:block}
#hmbBox{background:#1a1a1a;border:1px solid #333;border-radius:4px;max-width:960px;margin:0 auto;display:flex;flex-direction:column;max-height:90vh}
#hmbBoxHead{background:#111;border-bottom:2px solid #cc0000;padding:.6rem 1rem;display:flex;align-items:center;gap:.75rem;flex-shrink:0}
#hmbBoxHead h5{margin:0;color:#e0e0e0;flex:1;font-size:.95rem}
#hmbBoxSearch{background:#222;border:1px solid #444;color:#e0e0e0;border-radius:3px;padding:.3rem .6rem;font-size:.85rem;width:200px}
#hmbBoxGrid{flex:1;overflow-y:auto;padding:.5rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.6rem}
#hmbBoxFoot{background:#111;border-top:1px solid #333;padding:.6rem 1rem;display:flex;align-items:center;gap:.75rem;flex-shrink:0}
#hmbBoxRuta{flex:1;font-family:monospace;font-size:.78rem;color:#aaa;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
</style>

<!-- Overlay propio (sin bootstrap.Modal JS) -->
<div id="hmbOverlay" onclick="hmbCerrarSiOverlay(event)">
  <div id="hmbBox">
    <div id="hmbBoxHead">
      <h5 id="hmbBoxTitulo">Seleccionar archivo</h5>
      <input type="text" id="hmbBoxSearch" placeholder="Buscar…" oninput="hmbFiltrar(this.value)">
      <button type="button" onclick="hmbCerrar()"
              style="background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer">✕</button>
    </div>
    <div id="hmbBoxGrid"></div>
    <div id="hmbBoxFoot">
      <code id="hmbBoxRuta">— ningún archivo seleccionado —</code>
      <button type="button" class="btn btn-secondary btn-sm" onclick="hmbCerrar()">Cancelar</button>
      <button type="button" class="btn btn-danger" id="hmbBoxOk" onclick="hmbConfirmar()" disabled>✓ Seleccionar</button>
    </div>
  </div>
</div>

<script>
(function(){
  var AUDIOS   = <?= json_encode(array_values($audios)) ?>;
  var IMAGES   = <?= json_encode(array_values($images)) ?>;
  var SITEROOT = '<?= $siteRoot ?>';
  var tipoActual = 'audio';
  var rutaSel    = null;

  window.abrirSelectorHmb = function(tipo) {
    tipoActual = tipo;
    rutaSel    = null;
    document.getElementById('hmbBoxOk').disabled = true;
    document.getElementById('hmbBoxRuta').textContent = '— ningún archivo seleccionado —';
    document.getElementById('hmbBoxSearch').value = '';
    document.getElementById('hmbBoxTitulo').textContent = tipo === 'audio' ? '🎧 Seleccionar MP3' : '🖼️ Seleccionar imagen';
    hmbRender(tipo === 'audio' ? AUDIOS : IMAGES);
    document.getElementById('hmbOverlay').classList.add('active');
  };

  window.hmbCerrar = function() {
    document.getElementById('hmbOverlay').classList.remove('active');
  };

  window.hmbCerrarSiOverlay = function(e) {
    if (e.target.id === 'hmbOverlay') hmbCerrar();
  };

  window.hmbFiltrar = function(q) {
    var lista = tipoActual === 'audio' ? AUDIOS : IMAGES;
    q = q.toLowerCase();
    hmbRender(q ? lista.filter(function(f){ return f.name.toLowerCase().includes(q); }) : lista);
  };

  window.hmbConfirmar = function() {
    if (!rutaSel) return;
    var campo = document.getElementById(tipoActual === 'audio' ? 'jform_audio_file' : 'jform_cover_image');
    if (campo) {
      campo.value = rutaSel;
      campo.dispatchEvent(new Event('input'));
      if (tipoActual === 'image') hmbPreview(rutaSel);
    }
    hmbCerrar();
  };

  function hmbPreview(ruta) {
    var p = document.getElementById('hmb-cover-preview');
    var i = document.getElementById('hmb-cover-img');
    if (p && i) {
      i.src = ruta.startsWith('http') ? ruta : SITEROOT + ruta;
      p.style.display = '';
      i.onerror = function(){ p.style.display = 'none'; };
    }
  }

  var campoCover = document.getElementById('jform_cover_image');
  if (campoCover) campoCover.addEventListener('input', function(){ hmbPreview(this.value); });

  function hmbRender(files) {
    var grid = document.getElementById('hmbBoxGrid');
    grid.innerHTML = '';
    if (!files.length) {
      grid.innerHTML = '<p style="color:#555;text-align:center;padding:3rem;grid-column:1/-1">No se encontraron archivos.</p>';
      return;
    }
    files.forEach(function(f) {
      var esImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(f.name);
      var el = document.createElement('div');
      el.className = 'hmb-fi';
      el.innerHTML =
        '<div class="hmb-fi-prev">' +
          (esImg ? '<img src="' + SITEROOT + encodeURI(f.path) + '" loading="lazy" alt="">'
                 : '<span class="hmb-fi-ic">♫</span>') +
        '</div>' +
        '<div class="hmb-fi-name" title="' + f.name + '">' + f.name + '</div>' +
        '<div class="hmb-fi-folder">' + f.folder + '</div>';
      el.addEventListener('click', function() {
        document.querySelectorAll('.hmb-fi').forEach(function(x){ x.classList.remove('hmb-sel'); });
        el.classList.add('hmb-sel');
        rutaSel = f.path;
        document.getElementById('hmbBoxRuta').textContent = f.path;
        document.getElementById('hmbBoxOk').disabled = false;
      });
      el.addEventListener('dblclick', function() { rutaSel = f.path; hmbConfirmar(); });
      grid.appendChild(el);
    });
  }
})();
</script>