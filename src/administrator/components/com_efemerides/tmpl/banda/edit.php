<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
HTMLHelper::_('behavior.formvalidator');

$foto          = (string)($this->item->foto ?? '');
$bandaActiva   = empty($this->item->fecha_disolucion)
                 || $this->item->fecha_disolucion === '0000-00-00'
                 || $this->item->fecha_disolucion === null;
$siteRoot      = rtrim(Uri::root(), '/');
?>
<form action="<?php echo Route::_('index.php?option=com_efemerides&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

  <div class="row">
    <!-- Col izquierda -->
    <div class="col-md-8">
      <div class="card bg-dark border-secondary mb-3">
        <div class="card-header border-secondary fw-bold text-primary">&#127928; Datos de la banda</div>
        <div class="card-body">

          <div class="mb-3">
            <label class="form-label required text-light">Nombre *</label>
            <?php echo $this->form->renderField('nombre'); ?>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label text-light">País de origen</label>
              <?php echo $this->form->renderField('pais'); ?>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label text-light">Género musical</label>
              <?php echo $this->form->renderField('genero'); ?>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label text-light">Año / Fecha de formación</label>
              <?php echo $this->form->renderField('fecha_formacion'); ?>
              <small class="text-muted">Puedes escribir solo el año (ej: 1968)</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label text-light">Disolución</label>
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="chk-activa" role="switch"
                       <?php echo $bandaActiva ? 'checked' : ''; ?>
                       onchange="toggleDisol(this.checked)">
                <label class="form-check-label text-light" for="chk-activa">Banda en activo</label>
              </div>
              <div id="wrap-disol" <?php echo $bandaActiva ? 'style="display:none"' : ''; ?>>
                <?php echo $this->form->renderField('fecha_disolucion'); ?>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-light">Wikipedia URL</label>
            <?php echo $this->form->renderField('wikipedia_url'); ?>
            <?php if (!empty($this->item->wikipedia_url)): ?>
            <a href="<?php echo htmlspecialchars((string)$this->item->wikipedia_url); ?>"
               target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
              Abrir Wikipedia &rarr;
            </a>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label text-light">Descripción / Historia</label>
            <?php echo $this->form->renderField('descripcion'); ?>
          </div>

        </div>
      </div>
    </div>

    <!-- Col derecha -->
    <div class="col-md-4">

      <!-- Foto -->
      <div class="card bg-dark border-secondary mb-3">
        <div class="card-header border-secondary fw-bold text-light">Foto / Logo</div>
        <div class="card-body">

          <!-- Preview actual -->
          <div id="foto-preview-wrap" style="text-align:center;margin-bottom:12px;<?php echo !$foto ? 'display:none' : ''; ?>">
            <img id="foto-preview"
                 src="<?php echo $foto ? htmlspecialchars($this->fotoAbsoluta($foto, $siteRoot)) : ''; ?>"
                 alt="Preview"
                 style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid #333">
          </div>

          <label class="form-label text-light">URL de la imagen</label>
          <div class="input-group">
            <?php echo $this->form->renderField('foto'); ?>
          </div>
          <small class="text-muted d-block mt-1">
            URL externa: <code>https://upload.wikimedia.org/...</code><br>
            Ruta interna: <code>images/bandas/nombre.jpg</code>
          </small>

          <button type="button" class="btn btn-outline-primary btn-sm mt-2 w-100" onclick="previewFoto()">
            &#128247; Vista previa
          </button>

        </div>
      </div>

      <!-- Estado -->
      <div class="card bg-dark border-secondary mb-3">
        <div class="card-header border-secondary fw-bold text-light">Publicación</div>
        <div class="card-body">
          <?php echo $this->form->renderField('state'); ?>
        </div>
      </div>

    </div>
  </div>

  <input type="hidden" name="task" value="">
  <input type="hidden" name="jform[id]" value="<?php echo (int)$this->item->id; ?>">
  <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
var siteRoot = '<?php echo addslashes($siteRoot); ?>';

function previewFoto() {
    var input = document.querySelector('[name="jform[foto]"]');
    if (!input) return;
    var url = input.value.trim();
    if (!url) { alert('Escribe primero una URL o ruta de imagen.'); return; }

    // Construir URL absoluta si es ruta relativa
    if (url.indexOf('http://') !== 0 && url.indexOf('https://') !== 0) {
        url = siteRoot + '/' + url.replace(/^\/+/, '');
    }

    var wrap = document.getElementById('foto-preview-wrap');
    var img  = document.getElementById('foto-preview');

    img.onerror = function() {
        wrap.style.display = 'none';
        alert('No se pudo cargar la imagen. Verifica la URL o ruta.');
    };
    img.onload = function() {
        wrap.style.display = 'block';
        img.onerror = null;
    };
    img.src = url;
}

function toggleDisol(activa) {
    var wrap = document.getElementById('wrap-disol');
    var cal  = wrap ? wrap.querySelector('input') : null;
    if (activa) {
        wrap.style.display = 'none';
        if (cal) { cal.value = ''; cal.disabled = true; }
    } else {
        wrap.style.display = '';
        if (cal) cal.disabled = false;
    }
}

// Al cargar, deshabilitar el campo de disolución si está activa
document.addEventListener('DOMContentLoaded', function() {
    var chk = document.getElementById('chk-activa');
    if (chk && chk.checked) {
        var cal = document.querySelector('#wrap-disol input');
        if (cal) cal.disabled = true;
    }
});
</script>
