<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
$foto = (string)($this->item->foto ?? '');
$fotoInterna = (string)($this->item->foto_interna ?? '');
$preview = $foto ?: $fotoInterna;
?>
<form action="<?php echo Route::_('index.php?option=com_efemerides&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">
<div class="row">
  <div class="col-md-8">
    <div class="card mb-3">
      <div class="card-header fw-bold">Datos del artista fallecido</div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label required">Artista / Banda *</label>
          <?php echo $this->form->renderField('artista'); ?>
        </div>
        <div class="mb-3">
          <label class="form-label">Fecha de fallecimiento</label>
          <?php echo $this->form->renderField('fecha_fallecimiento'); ?>
        </div>
        <div class="mb-3">
          <label class="form-label">Efeméride relacionada</label>
          <?php echo $this->form->renderField('efemeride_id'); ?>
        </div>
        <div class="mb-3">
          <label class="form-label">Enlace Wikipedia</label>
          <?php echo $this->form->renderField('wikipedia_url'); ?>
          <?php if (!empty($this->item->wikipedia_url)): ?>
          <a href="<?php echo htmlspecialchars((string)$this->item->wikipedia_url); ?>"
             target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
            Abrir Wikipedia &rarr;
          </a>
          <?php endif; ?>
        </div>
        <div class="mb-3">
          <label class="form-label">Notas</label>
          <?php echo $this->form->renderField('notas'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card mb-3">
      <div class="card-header fw-bold">Foto del artista</div>
      <div class="card-body">

        <!-- Preview -->
        <div id="foto-preview-wrap" style="margin-bottom:12px;<?php echo !$preview ? 'display:none' : ''; ?>">
          <img id="foto-preview"
               src="<?php echo $preview ? htmlspecialchars($preview) : ''; ?>"
               alt="Preview"
               style="max-width:100%;border-radius:6px;border:1px solid #dee2e6">
        </div>

        <!-- Opción A: Media Manager -->
        <div class="mb-3">
          <label class="form-label fw-bold">Opción A &mdash; Media Manager de Joomla</label>
          <p class="text-muted" style="font-size:.82rem">Selecciona una imagen de la biblioteca de medios.</p>
          <?php echo $this->form->renderField('foto_interna'); ?>
        </div>

        <div class="alert alert-secondary py-2 text-center" style="font-size:.8rem">ó</div>

        <!-- Opción B: URL directa -->
        <div class="mb-3">
          <label class="form-label fw-bold">Opción B &mdash; URL de la imagen</label>
          <p class="text-muted" style="font-size:.82rem">
            Ruta relativa: <code>images/artistas/foto.jpg</code><br>
            URL externa: <code>https://upload.wikimedia.org/...</code>
          </p>
          <?php echo $this->form->renderField('foto'); ?>
          <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="previewFoto()">
            Vista previa
          </button>
        </div>

      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header fw-bold">Publicación</div>
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
function previewFoto() {
    var url = document.querySelector('[name="jform[foto]"]').value.trim();
    if (!url) return;
    var wrap = document.getElementById('foto-preview-wrap');
    var img  = document.getElementById('foto-preview');
    // Si es ruta relativa, añadir raíz del sitio
    if (url.indexOf('http') !== 0 && url.indexOf('/') !== 0) {
        url = '<?php echo rtrim(\Joomla\CMS\Uri\Uri::root(), '/'); ?>/' + url;
    }
    img.src = url;
    wrap.style.display = 'block';
}
// Actualizar preview con foto_interna cuando cambia el media manager
document.addEventListener('DOMContentLoaded', function() {
    var mediaField = document.querySelector('[name="jform[foto_interna]"]');
    if (mediaField) {
        var observer = new MutationObserver(function() {
            var val = mediaField.value;
            if (val) {
                var img = document.getElementById('foto-preview');
                var wrap = document.getElementById('foto-preview-wrap');
                img.src = '<?php echo rtrim(\Joomla\CMS\Uri\Uri::root(), '/'); ?>/' + val.replace(/^\//, '');
                wrap.style.display = 'block';
            }
        });
        observer.observe(mediaField, {attributes: true, attributeFilter: ['value']});
    }
});
</script>
