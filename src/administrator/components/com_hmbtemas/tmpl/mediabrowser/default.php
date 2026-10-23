<?php
defined('_JEXEC') or die;

$isAudio = $this->type === 'audio';
$title   = $isAudio ? '🎧 Seleccionar MP3' : '🖼️ Seleccionar imagen';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $title ?></title>
<link rel="stylesheet" href="<?= $this->siteRoot ?>media/vendor/bootstrap/css/bootstrap.min.css">
<style>
  body { background:#1a1a1a; color:#e0e0e0; font-family:system-ui,sans-serif; padding:0; margin:0; }
  .hmb-topbar { background:#111; border-bottom:2px solid #cc0000; padding:.6rem 1rem;
                display:flex; align-items:center; gap:.75rem; position:sticky; top:0; z-index:10; }
  .hmb-topbar h6 { margin:0; color:#e8ddd0; font-size:.95rem; flex:1; }
  .hmb-search { background:#222; border:1px solid #333; color:#e0e0e0; border-radius:4px;
                padding:.35rem .7rem; font-size:.85rem; width:220px; }
  .hmb-search::placeholder { color:#666; }
  .hmb-search:focus { outline:none; border-color:#cc0000; }
  .hmb-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr));
              gap:.75rem; padding:1rem; }
  .hmb-item { background:#222; border:1px solid #333; border-radius:4px; overflow:hidden;
              cursor:pointer; transition:all .15s; }
  .hmb-item:hover { border-color:#cc0000; background:#2a0000; }
  .hmb-item.selected { border-color:#cc0000; background:#2a0000; box-shadow:0 0 0 2px #cc0000; }
  .hmb-item__preview { height:100px; display:flex; align-items:center; justify-content:center;
                        background:#1a1a1a; overflow:hidden; }
  .hmb-item__preview img { width:100%; height:100%; object-fit:cover; }
  .hmb-item__preview .hmb-audio-icon { font-size:2.5rem; color:#cc0000; }
  .hmb-item__info { padding:.4rem .5rem; }
  .hmb-item__name { font-size:.72rem; color:#ccc; white-space:nowrap; overflow:hidden;
                    text-overflow:ellipsis; display:block; }
  .hmb-item__folder { font-size:.65rem; color:#666; display:block; }
  .hmb-footer { position:sticky; bottom:0; background:#111; border-top:1px solid #333;
                padding:.6rem 1rem; display:flex; align-items:center; gap:.75rem; }
  .hmb-selected-path { flex:1; font-family:monospace; font-size:.78rem; color:#aaa;
                        white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .hmb-empty { text-align:center; color:#555; padding:3rem; grid-column:1/-1; }
  .btn-accept { background:#cc0000; border-color:#cc0000; color:#fff; font-weight:600;
                letter-spacing:.05em; min-width:130px; }
  .btn-accept:hover { background:#e60000; border-color:#e60000; color:#fff; }
  .btn-accept:disabled { background:#555; border-color:#555; cursor:not-allowed; }
</style>
</head>
<body>

<div class="hmb-topbar">
  <h6><?= $title ?></h6>
  <input type="text" class="hmb-search" id="hmb-search" placeholder="Buscar archivo…">
</div>

<div class="hmb-grid" id="hmb-grid">
  <?php if (empty($this->files)) : ?>
    <div class="hmb-empty">
      <?php if ($isAudio) : ?>
        No se encontraron archivos MP3/OGG en <code>/images/</code>.<br>
        Súbelos desde <strong>Contenidos → Medios</strong>.
      <?php else : ?>
        No se encontraron imágenes en <code>/images/</code>.
      <?php endif; ?>
    </div>
  <?php else : ?>
    <?php foreach ($this->files as $file) :
      $isImg = in_array($file['ext'], ['jpg','jpeg','png','gif','webp','svg']);
    ?>
    <div class="hmb-item" data-path="<?= htmlspecialchars($file['path']) ?>"
         data-name="<?= htmlspecialchars($file['name']) ?>"
         onclick="seleccionar(this)">
      <div class="hmb-item__preview">
        <?php if ($isImg) : ?>
          <img src="<?= $this->siteRoot . htmlspecialchars($file['path']) ?>"
               alt="<?= htmlspecialchars($file['name']) ?>" loading="lazy">
        <?php else : ?>
          <span class="hmb-audio-icon">♫</span>
        <?php endif; ?>
      </div>
      <div class="hmb-item__info">
        <span class="hmb-item__name" title="<?= htmlspecialchars($file['name']) ?>">
          <?= htmlspecialchars($file['name']) ?>
        </span>
        <span class="hmb-item__folder"><?= htmlspecialchars($file['folder']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="hmb-footer">
  <span class="hmb-selected-path" id="hmb-path-display">— ningún archivo seleccionado —</span>
  <button type="button" class="btn btn-accept" id="btn-aceptar"
          onclick="aceptar()" disabled>
    ✓ Seleccionar
  </button>
  <button type="button" class="btn btn-secondary btn-sm" onclick="window.close()">
    Cancelar
  </button>
</div>

<script>
var fieldId  = '<?= htmlspecialchars($this->fieldId) ?>';
var selected = null;

function seleccionar(el) {
  document.querySelectorAll('.hmb-item').forEach(function(i){ i.classList.remove('selected'); });
  el.classList.add('selected');
  selected = el.dataset.path;
  document.getElementById('hmb-path-display').textContent = selected;
  document.getElementById('btn-aceptar').disabled = false;
}

function aceptar() {
  if (!selected) return;
  try {
    var campo = window.opener.document.getElementById(fieldId);
    if (campo) {
      campo.value = selected;
      campo.dispatchEvent(new Event('input', { bubbles: true }));
    }
  } catch(e) {
    alert('Ruta copiada: ' + selected + '\n\nPégala manualmente en el campo.');
  }
  window.close();
}

// Búsqueda en tiempo real
document.getElementById('hmb-search').addEventListener('input', function(){
  var q = this.value.toLowerCase();
  document.querySelectorAll('.hmb-item').forEach(function(el){
    var name = (el.dataset.name || '').toLowerCase();
    el.style.display = name.includes(q) ? '' : 'none';
  });
});

// Doble clic = seleccionar y aceptar directamente
document.querySelectorAll('.hmb-item').forEach(function(el){
  el.addEventListener('dblclick', function(){ seleccionar(this); aceptar(); });
});
</script>
</body>
</html>
