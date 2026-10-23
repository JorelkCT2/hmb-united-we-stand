<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
HTMLHelper::_('behavior.formvalidator');

$esSolista   = (int)($this->item->solista ?? 0) === 1;
$haFallecido = !empty($this->item->fecha_defuncion)
               && $this->item->fecha_defuncion !== '0000-00-00'
               && $this->item->fecha_defuncion !== null;
$siteRoot    = rtrim(Uri::root(), '/');
$foto        = trim((string)($this->item->foto ?? ''));
$fotoAbs     = '';
if ($foto) {
    $fotoAbs = (strpos($foto,'http') === 0) ? $foto : $siteRoot.'/'.ltrim($foto,'/');
}
?>
<form action="<?php echo Route::_('index.php?option=com_efemerides&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

  <div class="row">
    <!-- Columna principal -->
    <div class="col-md-8">
      <div class="card bg-dark border-secondary mb-3">
        <div class="card-header border-secondary fw-bold text-warning">&#127928; Datos del músico</div>
        <div class="card-body">

          <!-- NOMBRE -->
          <div class="mb-3">
            <label class="form-label required text-light">Nombre *</label>
            <?php echo $this->form->renderField('nombre'); ?>
          </div>

          <!-- TOGGLE SOLISTA (estilo igual que "Todavía vive") -->
          <div class="mb-3">
            <div class="form-check form-switch d-flex align-items-center gap-2"
                 style="background:#111;border:1px solid #1e1e1e;border-left:3px solid #d4af37;padding:10px 14px">
              <input class="form-check-input" type="checkbox" role="switch"
                     id="chk-solista"
                     <?php echo $esSolista ? 'checked' : ''; ?>
                     onchange="toggleSolista(this.checked)"
                     style="width:2.5em;height:1.3em;cursor:pointer">
              <label class="form-check-label text-warning fw-bold" for="chk-solista"
                     style="font-size:.9rem;letter-spacing:.03em">
                &#9733; Artista Solista
              </label>
            </div>
            <!-- Campo oculto que guarda el valor real -->
            <input type="hidden" name="jform[solista]" id="jform-solista-val"
                   value="<?php echo $esSolista ? '1' : '0'; ?>">
          </div>

          <!-- CAMPOS DE BANDA -->
          <div id="wrap-banda" <?php echo $esSolista ? 'style="display:none"' : ''; ?>>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label text-light">Banda principal <small class="text-muted">(de la BD)</small></label>
                <?php echo $this->form->renderField('banda_id'); ?>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label text-light">Banda <small class="text-muted">(texto libre)</small></label>
                <?php echo $this->form->renderField('banda_texto'); ?>
              </div>
            </div>
          </div>

          <!-- SEGUNDA BANDA con toggle -->
          <?php
          $tieneBanda2  = !empty($this->item->banda_id_2) || !empty($this->item->banda_texto_2);
          $banda2IdVal  = (int)($this->item->banda_id_2 ?? 0);
          $banda2TxtVal = (string)($this->item->banda_texto_2 ?? '');
          ?>
          <!-- Campos hidden que SIEMPRE se envían (Joomla no los filtra) -->
          <input type="hidden" name="jform[banda_id_2]"    id="jf-banda2-id"  value="<?php echo $banda2IdVal ?: ''; ?>">
          <input type="hidden" name="jform[banda_texto_2]" id="jf-banda2-txt" value="<?php echo htmlspecialchars($banda2TxtVal); ?>">

          <div id="wrap-banda2" <?php echo $esSolista ? 'style="display:none"' : ''; ?>>
            <div style="border-top:1px solid #1e1e1e;margin-top:8px;padding-top:12px">
              <!-- Toggle segunda banda -->
              <div class="form-check form-switch d-flex align-items-center gap-2 mb-2"
                   style="background:#0d0d0d;border:1px solid #1e1e1e;border-left:3px solid #5ba3d9;padding:8px 12px">
                <input class="form-check-input" type="checkbox" role="switch"
                       id="chk-banda2"
                       <?php echo $tieneBanda2 ? 'checked' : ''; ?>
                       style="width:2.5em;height:1.3em;cursor:pointer">
                <label class="form-check-label text-info fw-bold" for="chk-banda2"
                       style="font-size:.88rem">
                  &#127928; ¿Pertenece también a otra banda?
                </label>
              </div>
              <!-- Campos SELECT/TEXT visibles — solo sincronizan los hidden -->
              <div id="wrap-banda2-fields" <?php echo !$tieneBanda2 ? 'style="display:none"' : ''; ?>>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Segunda banda <small class="text-muted">(de la BD)</small></label>
                    <select class="form-select" id="vis-banda2-id">
                      <option value="">-- Sin segunda banda --</option>
                      <?php
                      $db2 = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
                      $db2->setQuery('SELECT id, nombre FROM #__efemerides_bandas WHERE state=1 ORDER BY nombre ASC');
                      foreach ($db2->loadObjectList() ?: [] as $bb):
                      ?>
                      <option value="<?php echo (int)$bb->id; ?>"
                        <?php echo ($banda2IdVal === (int)$bb->id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($bb->nombre); ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Segunda banda <small class="text-muted">(texto libre)</small></label>
                    <input type="text" class="form-control" id="vis-banda2-txt"
                           value="<?php echo htmlspecialchars($banda2TxtVal); ?>"
                           placeholder="Nombre si no está en la BD">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- AVISO SOLISTA -->
          <div id="wrap-solista-msg" <?php echo !$esSolista ? 'style="display:none"' : ''; ?>>
            <div class="mb-3 px-3 py-2 d-flex align-items-center gap-2"
                 style="background:#1a1500;border:1px solid #d4af3755;color:#d4af37;font-size:.85rem">
              <span style="font-size:1.1rem">&#9733;</span>
              No se vincula a ninguna banda
            </div>
          </div>

          <!-- INSTRUMENTO + PAÍS -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label text-light">&#127928; Instrumento(s)</label>
              <?php echo $this->form->renderField('instrumento'); ?>
              <small class="text-muted">Ej: Guitarra, Bajo, Voz, Batería</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label text-light">&#127758; País de origen</label>
              <?php echo $this->form->renderField('pais'); ?>
            </div>
          </div>

          <!-- NACIMIENTO -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label text-light">Fecha de nacimiento</label>
              <?php echo $this->form->renderField('fecha_nacimiento'); ?>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label text-light">Lugar de nacimiento</label>
              <?php echo $this->form->renderField('lugar_nacimiento'); ?>
            </div>
          </div>

          <!-- DEFUNCIÓN -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="chk-vive" role="switch"
                       <?php echo !$haFallecido ? 'checked' : ''; ?>
                       onchange="toggleDef(this.checked)">
                <label class="form-check-label text-light" for="chk-vive">Todavía vive</label>
              </div>
              <div id="wrap-def" <?php echo !$haFallecido ? 'style="display:none"' : ''; ?>>
                <label class="form-label text-light">Fecha de defunción</label>
                <?php echo $this->form->renderField('fecha_defuncion'); ?>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div id="wrap-lugar-def" <?php echo !$haFallecido ? 'style="display:none"' : ''; ?>>
                <label class="form-label text-light">Lugar de defunción</label>
                <?php echo $this->form->renderField('lugar_defuncion'); ?>
              </div>
            </div>
          </div>

          <!-- WIKIPEDIA -->
          <div class="mb-3">
            <label class="form-label text-light">Wikipedia URL</label>
            <?php echo $this->form->renderField('wikipedia_url'); ?>
            <?php if (!empty($this->item->wikipedia_url)): ?>
            <a href="<?php echo htmlspecialchars((string)$this->item->wikipedia_url); ?>"
               target="_blank" class="btn btn-sm btn-outline-secondary mt-1">Abrir &rarr;</a>
            <?php endif; ?>
          </div>

          <!-- BIOGRAFÍA -->
          <div class="mb-3">
            <label class="form-label text-light">Biografía</label>
            <?php echo $this->form->renderField('descripcion'); ?>
          </div>

        </div>
      </div>
    </div>

    <!-- Columna derecha -->
    <div class="col-md-4">

      <!-- FOTO con preview -->
      <div class="card bg-dark border-secondary mb-3">
        <div class="card-header border-secondary fw-bold text-light">Foto</div>
        <div class="card-body">

          <!-- Preview circular -->
          <div id="foto-preview-wrap"
               style="text-align:center;margin-bottom:14px;<?php echo !$fotoAbs ? 'display:none' : ''; ?>">
            <img id="foto-preview-img"
                 src="<?php echo htmlspecialchars($fotoAbs); ?>"
                 alt=""
                 style="width:130px;height:130px;object-fit:cover;border-radius:50%;border:3px solid #cc0000;display:block;margin:0 auto">
          </div>
          <!-- Placeholder si no hay foto -->
          <div id="foto-preview-ph"
               style="text-align:center;margin-bottom:14px;<?php echo $fotoAbs ? 'display:none' : ''; ?>">
            <div style="width:130px;height:130px;border-radius:50%;background:#111;border:3px solid #2a2a2a;display:flex;align-items:center;justify-content:center;font-size:3rem;margin:0 auto">&#127928;</div>
          </div>

          <label class="form-label text-light">URL de la imagen</label>
          <?php echo $this->form->renderField('foto'); ?>
          <small class="text-muted d-block mt-1">URL externa (https://...) o ruta interna (images/...)</small>
          <button type="button" class="btn btn-outline-warning btn-sm mt-2 w-100"
                  onclick="previewFoto()">&#128247; Previsualizar</button>
        </div>
      </div>

      <!-- ESTADO -->
      <div class="card bg-dark border-secondary mb-3">
        <div class="card-header border-secondary fw-bold text-light">Publicación</div>
        <div class="card-body"><?php echo $this->form->renderField('state'); ?></div>
      </div>

    </div>
  </div>

  <input type="hidden" name="task" value="">
  <input type="hidden" name="jform[id]" value="<?php echo (int)$this->item->id; ?>">
  <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
var _origBandaId  = '';
var _origBandaTxt = '';

function toggleSolista(esSolista) {
    var hid = document.getElementById('jform-solista-val');
    var wb  = document.getElementById('wrap-banda');
    var wm  = document.getElementById('wrap-solista-msg');
    var sel = document.querySelector('[name="jform[banda_id]"]');
    var txt = document.querySelector('[name="jform[banda_texto]"]');

    hid.value = esSolista ? '1' : '0';
    wb.style.display = esSolista ? 'none' : '';
    var wb2 = document.getElementById('wrap-banda2');
    if(wb2) wb2.style.display = esSolista ? 'none' : '';
    // Si se activa solista, también limpiar segunda banda
    if (esSolista) {
        var chkB2 = document.getElementById('chk-banda2');
        if (chkB2 && chkB2.checked) { chkB2.checked = false; }
        toggleBanda2(false);
    }
    wm.style.display = esSolista ? '' : 'none';

    if (esSolista) {
        if (sel) { _origBandaId  = sel.value; sel.value = ''; }
        if (txt) { _origBandaTxt = txt.value; txt.value = ''; }
    } else {
        if (sel) sel.value = _origBandaId;
        if (txt) txt.value = _origBandaTxt;
    }
}

function toggleBanda2(activa) {
    var fields = document.getElementById('wrap-banda2-fields');
    if (fields) fields.style.display = activa ? '' : 'none';

    var hidId  = document.getElementById('jf-banda2-id');
    var hidTxt = document.getElementById('jf-banda2-txt');
    var visId  = document.getElementById('vis-banda2-id');
    var visTxt = document.getElementById('vis-banda2-txt');

    if (!activa) {
        // Toggle OFF → limpiar hidden fields (se guardarán como NULL)
        if (hidId)  hidId.value  = '';
        if (hidTxt) hidTxt.value = '';
    } else {
        // Toggle ON → sincronizar hidden desde los visibles
        if (hidId  && visId)  hidId.value  = visId.value;
        if (hidTxt && visTxt) hidTxt.value = visTxt.value;
    }
}

// Sincronizar en tiempo real cuando el usuario cambia los selects
document.addEventListener('DOMContentLoaded', function() {
    var visId  = document.getElementById('vis-banda2-id');
    var visTxt = document.getElementById('vis-banda2-txt');
    var chkB2  = document.getElementById('chk-banda2');

    if (visId) visId.addEventListener('change', function() {
        var h = document.getElementById('jf-banda2-id');
        if (h) h.value = this.value;
    });
    if (visTxt) visTxt.addEventListener('input', function() {
        var h = document.getElementById('jf-banda2-txt');
        if (h) h.value = this.value;
    });
    if (chkB2) {
        chkB2.addEventListener('change', function() {
            toggleBanda2(this.checked);
        });
    }
});

function toggleDef(vive) {
    var wd  = document.getElementById('wrap-def');
    var wld = document.getElementById('wrap-lugar-def');
    wd.style.display  = vive ? 'none' : '';
    wld.style.display = vive ? 'none' : '';
    var cal = wd.querySelector('input[type="text"], input[type="date"]');
    if (cal) cal.disabled = vive;
}

function previewFoto() {
    var input = document.querySelector('[name="jform[foto]"]');
    if (!input || !input.value.trim()) { showPh(); return; }
    var url = input.value.trim();
    if (url.indexOf('http://') !== 0 && url.indexOf('https://') !== 0) {
        url = _siteRoot + '/' + url.replace(/^\/+/, '');
    }
    var img  = document.getElementById('foto-preview-img');
    var wrap = document.getElementById('foto-preview-wrap');
    var ph   = document.getElementById('foto-preview-ph');
    img.onerror = function() { showPh(); };
    img.onload  = function() { wrap.style.display=''; ph.style.display='none'; img.onerror=null; };
    img.src = url;
}

function showPh() {
    document.getElementById('foto-preview-wrap').style.display = 'none';
    document.getElementById('foto-preview-ph').style.display   = '';
}

document.addEventListener('DOMContentLoaded', function() {
    // Guardar valores originales ANTES de activar el toggle
    var sel = document.querySelector('[name="jform[banda_id]"]');
    var txt = document.querySelector('[name="jform[banda_texto]"]');
    if (sel) _origBandaId  = sel.value;
    if (txt) _origBandaTxt = txt.value;

    // Aplicar estado inicial del checkbox solista
    var chkSol = document.getElementById('chk-solista');
    if (chkSol) {
        chkSol.addEventListener('change', function() {
            toggleSolista(this.checked);
        });
        // Estado inicial: si ya es solista, vaciar banda SIN guardar original
        if (chkSol.checked) {
            document.getElementById('wrap-banda').style.display       = 'none';
            document.getElementById('wrap-solista-msg').style.display = '';
        }
    }

    // Toggle defunción inicial
    var chkVive = document.getElementById('chk-vive');
    if (chkVive && chkVive.checked) {
        var cal = document.querySelector('#wrap-def input');
        if (cal) cal.disabled = true;
    }
    if (chkVive) {
        chkVive.addEventListener('change', function() {
            toggleDef(this.checked);
        });
    }

    // Preview automático de foto
    var fotoInput = document.querySelector('[name="jform[foto]"]');
    if (fotoInput) {
        fotoInput.addEventListener('blur', function() {
            if (this.value.trim()) previewFoto();
        });
    }
});
</script>
