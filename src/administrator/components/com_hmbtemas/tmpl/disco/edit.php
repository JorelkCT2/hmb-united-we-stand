<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$siteRoot  = Uri::root();
$imagesDir = JPATH_ROOT . '/images';
$audios = []; $images = [];
$extAudio = ['mp3','ogg','wav']; $extImage = ['jpg','jpeg','png','gif','webp'];

if (is_dir($imagesDir)) {
    $rit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($imagesDir, FilesystemIterator::SKIP_DOTS));
    foreach ($rit as $file) {
        if (!$file->isFile()) continue;
        $ext  = strtolower($file->getExtension());
        $full = $file->getPathname();
        $rel  = str_replace('\\','/',ltrim(str_replace(JPATH_ROOT,'',$full),'/\\'));
        $e = ['path'=>$rel,'name'=>$file->getFilename(),'folder'=>basename(dirname($full))];
        if (in_array($ext,$extAudio,true)) $audios[]=$e;
        elseif (in_array($ext,$extImage,true)) $images[]=$e;
    }
}

// Cargar YouTube links existentes
$ytLinks = [];
if (!empty($this->item->youtube_links)) {
    $ytLinks = json_decode($this->item->youtube_links, true) ?: [];
}
?>
<form action="<?= Route::_('index.php?option=com_hmbtemas&view=disco&layout=edit&id='.(int)$this->item->id) ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

  <div class="row g-4">
    <div class="col-lg-8">

      <!-- Datos principales -->
      <div class="card mb-3">
        <div class="card-header fw-bold">💿 Datos del disco</div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nombre del álbum <span class="text-danger">*</span></label>
              <?= $this->form->getInput('titulo') ?>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Fecha</label>
              <?= $this->form->getInput('anno') ?>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Grupo / Banda <span class="text-danger">*</span></label>
              <?= $this->form->getInput('grupo') ?>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Productora / Sello</label>
              <?= $this->form->getInput('productora') ?>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Género musical</label>
            <?= $this->form->getInput('genero') ?>
          </div>
          <div>
            <label class="form-label fw-semibold">Descripción</label>
            <?= $this->form->getInput('descripcion') ?>
          </div>
        </div>
      </div>

      <!-- YouTube links dinámicos -->
      <div class="card">
        <div class="card-header fw-bold d-flex align-items-center justify-content-between">
          <span>▶ Enlaces de YouTube</span>
          <button type="button" class="btn btn-sm btn-danger" onclick="ytAnadir()">+ Añadir enlace</button>
        </div>
        <div class="card-body p-2" id="yt-lista">
          <?php foreach ($ytLinks as $idx => $yt): ?>
          <div class="yt-fila d-flex gap-2 mb-2 align-items-center">
            <input type="text" class="form-control" style="max-width:200px"
                   placeholder="Título (ej: Videoclip)"
                   value="<?= htmlspecialchars($yt['titulo'] ?? '') ?>">
            <input type="url" class="form-control"
                   placeholder="https://www.youtube.com/watch?v=..."
                   value="<?= htmlspecialchars($yt['url'] ?? '') ?>">
            <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0"
                    onclick="ytEliminar(this)">✕</button>
          </div>
          <?php endforeach; ?>
          <?php if (empty($ytLinks)): ?>
          <p class="text-muted small mb-0">Pulsa "+ Añadir enlace" para agregar videos de YouTube.</p>
          <?php endif; ?>
        </div>
        <input type="hidden" name="jform[youtube_links]" id="yt-hidden"
               value="<?= htmlspecialchars($this->item->youtube_links ?? '[]') ?>">
      </div>

    </div>

    <!-- Columna lateral -->
    <div class="col-lg-4">

      <!-- Portada -->
      <div class="card mb-3">
        <div class="card-header fw-bold">🖼️ Portada del álbum</div>
        <div class="card-body">

          <?php /* Preview en tiempo real */ ?>
          <div id="disco-cover-preview"
               style="<?= empty($this->item->portada)?'display:none':'' ?>;background:#111;border-radius:4px;overflow:hidden;margin-bottom:.75rem;aspect-ratio:1;max-width:200px;margin-left:auto;margin-right:auto">
            <img id="disco-cover-img"
                 src="<?= !empty($this->item->portada) ? (str_starts_with($this->item->portada,'http') ? htmlspecialchars($this->item->portada) : $siteRoot.htmlspecialchars($this->item->portada)) : '' ?>"
                 style="width:100%;height:100%;object-fit:cover;display:block" alt="Preview portada">
          </div>

          <?php /* Campo texto + selector 📁 */ ?>
          <label class="form-label small text-muted">Ruta local o URL externa</label>
          <div class="input-group mb-2">
            <?= $this->form->getInput('portada') ?>
            <button type="button" class="btn btn-danger"
                    onclick="discoSelector('image','jform_portada')" title="Seleccionar imagen">📁</button>
          </div>

        </div>
      </div>

      <!-- Audio -->
      <div class="card mb-3">
        <div class="card-header fw-bold">🎧 Audio</div>
        <div class="card-body">
          <label class="form-label small fw-semibold">MP3 (del servidor)</label>
          <div class="input-group mb-1">
            <?= $this->form->getInput('audio_file') ?>
            <button type="button" class="btn btn-danger"
                    onclick="discoSelector('audio','jform_audio_file')" title="Seleccionar MP3">📁</button>
          </div>
          <?php if (!empty($this->item->audio_file)): ?>
          <div class="mb-2 p-1 bg-light rounded">
            <code style="font-size:.72rem;word-break:break-all"><?= htmlspecialchars($this->item->audio_file) ?></code>
          </div>
          <?php endif; ?>
          <hr class="my-2">
          <label class="form-label small fw-semibold">
            ▶ O usar YouTube como fuente de audio
            <small class="text-muted fw-normal d-block fw-normal">Si se rellena, en la tarjeta se usa YouTube en vez del MP3</small>
          </label>
          <?= $this->form->getInput('youtube_audio_url') ?>
        </div>
      </div>

      <!-- Publicación -->
      <div class="card">
        <div class="card-header fw-bold">Publicación</div>
        <div class="card-body">
          <div class="mb-2">
            <label class="form-label">Estado</label>
            <?= $this->form->getInput('published') ?>
          </div>
          <div>
            <label class="form-label">Orden</label>
            <?= $this->form->getInput('ordering') ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" name="task" value="">
  <input type="hidden" name="jform[id]" value="<?= (int)$this->item->id ?>">
  <?= HTMLHelper::_('form.token') ?>
</form>

<!-- SELECTOR DE ARCHIVOS -->
<div id="discoOverlay" onclick="if(event.target.id==='discoOverlay')discoCerrar()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;overflow-y:auto;padding:2rem 1rem">
  <div style="background:#1a1a1a;border:1px solid #333;border-radius:4px;max-width:960px;margin:0 auto;display:flex;flex-direction:column;max-height:88vh">
    <div style="background:#111;border-bottom:2px solid #cc0000;padding:.6rem 1rem;display:flex;align-items:center;gap:.75rem;flex-shrink:0">
      <strong id="discoSelectorTit" style="color:#e0e0e0;flex:1;font-size:.9rem">Seleccionar</strong>
      <input type="text" id="discoSearch" placeholder="Buscar…"
             style="background:#222;border:1px solid #444;color:#e0e0e0;border-radius:3px;padding:.3rem .6rem;width:180px"
             oninput="discoFiltrar(this.value)">
      <button onclick="discoCerrar()" style="background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer">✕</button>
    </div>
    <div id="discoGrid" style="flex:1;overflow-y:auto;padding:.5rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.5rem"></div>
    <div style="background:#111;border-top:1px solid #333;padding:.6rem 1rem;display:flex;align-items:center;gap:.75rem;flex-shrink:0">
      <code id="discoRuta" style="flex:1;font-size:.75rem;color:#aaa;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">— ningún archivo seleccionado —</code>
      <button onclick="discoCerrar()" class="btn btn-secondary btn-sm">Cancelar</button>
      <button id="discoOk" onclick="discoConfirmar()" class="btn btn-danger" disabled>✓ Seleccionar</button>
    </div>
  </div>
</div>

<style>
.ds-fi{background:#222;border:2px solid #333;border-radius:3px;cursor:pointer;overflow:hidden;text-align:center}
.ds-fi:hover{border-color:#cc0000;background:#2a0000}
.ds-fi.sel{border-color:#cc0000;box-shadow:0 0 0 2px #cc0000}
.ds-prev{height:80px;display:flex;align-items:center;justify-content:center;background:#111;overflow:hidden}
.ds-prev img{width:100%;height:100%;object-fit:cover}
.ds-name{padding:.25rem .3rem;font-size:.65rem;color:#bbb;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ds-folder{font-size:.58rem;color:#555;padding-bottom:.25rem}
.yt-fila input{font-size:.85rem}
</style>

<script>
var dsAudios  = <?= json_encode(array_values($audios)) ?>;
var dsImages  = <?= json_encode(array_values($images)) ?>;
var dsSite    = '<?= $siteRoot ?>';
var dsTipo    = 'audio';
var dsCampoId = '';
var dsSel     = null;

window.discoSelector = function(tipo, campoId) {
  dsTipo = tipo; dsCampoId = campoId; dsSel = null;
  document.getElementById('discoOk').disabled = true;
  document.getElementById('discoRuta').textContent = '— ningún archivo seleccionado —';
  document.getElementById('discoSearch').value = '';
  document.getElementById('discoSelectorTit').textContent = tipo === 'audio' ? '🎧 Seleccionar MP3' : '🖼️ Seleccionar imagen';
  dsRender(tipo === 'audio' ? dsAudios : dsImages);
  document.getElementById('discoOverlay').style.display = 'block';
};
window.discoCerrar = function() { document.getElementById('discoOverlay').style.display = 'none'; };
window.discoFiltrar = function(q) {
  var lista = dsTipo === 'audio' ? dsAudios : dsImages;
  dsRender(q ? lista.filter(function(f){ return f.name.toLowerCase().includes(q.toLowerCase()); }) : lista);
};
window.discoConfirmar = function() {
  if (!dsSel) return;
  var c = document.getElementById(dsCampoId);
  if (c) { c.value = dsSel; c.dispatchEvent(new Event('input')); }
  if (dsCampoId === 'jform_portada') dsPreview(dsSel);
  discoCerrar();
};
function dsRender(files) {
  var g = document.getElementById('discoGrid');
  g.innerHTML = '';
  if (!files.length) { g.innerHTML = '<p style="color:#555;text-align:center;padding:2rem;grid-column:1/-1">Sin archivos.</p>'; return; }
  files.forEach(function(f) {
    var isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(f.name);
    var el = document.createElement('div'); el.className = 'ds-fi';
    el.innerHTML = '<div class="ds-prev">'+(isImg?'<img src="'+dsSite+encodeURI(f.path)+'" loading="lazy" alt="">':'<span style="font-size:2rem;color:#cc0000">♫</span>')+'</div>'
      +'<div class="ds-name" title="'+f.name+'">'+f.name+'</div><div class="ds-folder">'+f.folder+'</div>';
    el.addEventListener('click', function() {
      document.querySelectorAll('.ds-fi').forEach(function(x){x.classList.remove('sel');});
      el.classList.add('sel'); dsSel = f.path;
      document.getElementById('discoRuta').textContent = f.path;
      document.getElementById('discoOk').disabled = false;
    });
    el.addEventListener('dblclick', function() { dsSel = f.path; discoConfirmar(); });
    g.appendChild(el);
  });
}
function dsPreview(ruta) {
  var p = document.getElementById('disco-cover-preview');
  var i = document.getElementById('disco-cover-img');
  if (p && i) { i.src = ruta.startsWith('http')?ruta:dsSite+ruta; p.style.display=''; i.onerror=function(){p.style.display='none';}; }
}
// Preview en tiempo real - función global con acceso a dsSite (global)
function dsPreview(ruta) {
  var p = document.getElementById('disco-cover-preview');
  var i = document.getElementById('disco-cover-img');
  if (!p || !i) return;
  if (!ruta) { p.style.display = 'none'; return; }
  var src = ruta.startsWith('http') ? ruta : dsSite + ruta;
  i.src = src;
  i.onload  = function(){ p.style.display = ''; i.onload = null; };
  i.onerror = function(){ p.style.display = 'none'; i.onerror = null; };
}

// Activar preview al escribir en el campo portada
document.addEventListener('DOMContentLoaded', function() {
  var cpI = document.getElementById('jform_portada');
  if (cpI) {
    cpI.addEventListener('input',  function(){ dsPreview(this.value.trim()); });
    cpI.addEventListener('change', function(){ dsPreview(this.value.trim()); });
    // Si ya hay valor al cargar, mostrar preview
    if (cpI.value.trim()) dsPreview(cpI.value.trim());
  }
});

// YouTube dinámico
window.ytAnadir = function() {
  var lista = document.getElementById('yt-lista');
  var p = lista.querySelector('p');
  if (p) p.remove();
  var div = document.createElement('div');
  div.className = 'yt-fila d-flex gap-2 mb-2 align-items-center';
  div.innerHTML = '<input type="text" class="form-control" style="max-width:200px" placeholder="Título (ej: Videoclip)">'
    +'<input type="url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">'
    +'<button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" onclick="ytEliminar(this)">✕</button>';
  lista.appendChild(div);
  ytGuardar();
};
window.ytEliminar = function(btn) { btn.closest('.yt-fila').remove(); ytGuardar(); };
function ytGuardar() {
  var data = [];
  document.querySelectorAll('.yt-fila').forEach(function(f) {
    var ins = f.querySelectorAll('input');
    var titulo = ins[0].value.trim();
    var url    = ins[1].value.trim();
    if (url) data.push({titulo: titulo, url: url});
  });
  document.getElementById('yt-hidden').value = JSON.stringify(data);
}
document.addEventListener('input', function(e) {
  if (e.target.closest('.yt-fila')) ytGuardar();
});

// Guardar YT antes de submit
document.getElementById('adminForm').addEventListener('submit', function() { ytGuardar(); });

</script>
