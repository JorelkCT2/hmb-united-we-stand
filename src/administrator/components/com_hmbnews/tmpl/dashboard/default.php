<?php defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;

// $this->params, $this->token, $this->apiKey, $this->catId, $this->num, $this->ajaxBase
?>
<div id="hmb-wrap">

<?php if (!$this->apiKey): ?>
<div class="hmb-alert">
    ⚠ <strong>Sin API Key:</strong>
    Ve a <a href="<?php echo Route::_('index.php?option=com_config&view=component&component=com_hmbnews'); ?>" class="hmb-link">Ajustes del componente</a> e introduce tu clave de Anthropic.
    Buscar noticias es gratis; la clave solo se usa al redactar las que elijas.
</div>
<?php endif; ?>

<div class="hmb-grid">

 <!-- COLUMNA IZQUIERDA -->
 <div class="hmb-left">

  <div class="hmb-card">
   <div class="hmb-head"><span>📡</span><h2>Fuentes de Noticias</h2></div>
   <div class="hmb-body">
    <p class="hmb-hint">Se leen por su RSS (gratis). El feed se detecta solo la primera vez.</p>
    <div id="src-list"><div class="hmb-loading">Cargando…</div></div>
    <div class="hmb-add-box">
     <p class="hmb-sub">Añadir fuente</p>
     <div class="hmb-add-row">
      <input type="text" id="ns-name" class="hmb-inp" placeholder="Nombre (ej: Metal Hammer)"/>
      <input type="text" id="ns-url"  class="hmb-inp" placeholder="Dominio (ej: metalhammer.com)"/>
      <button class="hmb-btn hmb-add" onclick="hmbAddSource()">+ Añadir</button>
     </div>
    </div>
   </div>
  </div>

  <div class="hmb-card">
   <div class="hmb-head"><span>🔎</span><h2>Buscar noticias (gratis)</h2></div>
   <div class="hmb-body">
    <div class="hmb-row">
     <label class="hmb-lbl">Palabra o banda (opcional)</label>
     <input type="text" id="hmb-q" class="hmb-inp" placeholder="ej: Metallica, festival, nuevo álbum"/>
    </div>
    <div class="hmb-row">
     <label class="hmb-lbl">Antigüedad máxima (días)</label>
     <input type="number" id="hmb-dias" class="hmb-inp hmb-sm" value="7" min="1" max="60"/>
    </div>
    <div class="hmb-row">
     <label class="hmb-chk"><input type="checkbox" id="hmb-solo"/> Solo bandas de la casa y temas prioritarios</label>
    </div>
    <div class="hmb-row">
     <label class="hmb-lbl">Categoría Joomla (ID)</label>
     <input type="number" id="hmb-catid" class="hmb-inp hmb-sm" value="<?php echo (int) $this->catId; ?>" min="1"/>
    </div>
    <div class="hmb-row">
     <label class="hmb-chk"><input type="checkbox" id="hmb-pub"/> Publicar directamente (si no, quedan como borrador)</label>
    </div>
    <button id="hmb-imp-btn" class="hmb-import-btn" onclick="hmbBuscar()">
     <span id="hmb-imp-lbl">🔎 BUSCAR NOTICIAS</span>
    </button>
   </div>
  </div>

  <div class="hmb-card" id="hmb-log-card" style="display:none">
   <div class="hmb-head"><span>📋</span><h2>Progreso</h2>
    <button class="hmb-ghost" style="margin-left:auto" onclick="document.getElementById('hmb-log-card').style.display='none'">✕</button>
   </div>
   <div class="hmb-body"><div id="hmb-log" class="hmb-log"></div></div>
  </div>

 </div><!-- /left -->

 <!-- COLUMNA DERECHA -->
 <div class="hmb-right">

  <div class="hmb-card" id="hmb-cand-card" style="display:none">
   <div class="hmb-head">
    <span>🗞</span><h2>Candidatas</h2><span id="hmb-cand-cnt" class="hmb-badge">0</span>
    <button class="hmb-ghost" style="margin-left:auto" onclick="hmbSelTodo(true)" title="Marcar todas">☑</button>
    <button class="hmb-ghost" onclick="hmbSelTodo(false)" title="Desmarcar">☐</button>
   </div>
   <div class="hmb-body">
    <p class="hmb-hint">Marca las que quieras. La IA solo redacta las marcadas (≈ 0,003 € cada una) y descarga su imagen real.</p>
    <div id="cand-list"></div>
    <button id="hmb-go-btn" class="hmb-import-btn" onclick="hmbImportarSel()" disabled>
     <span id="hmb-go-lbl">✍ REDACTAR E IMPORTAR LAS MARCADAS</span>
    </button>
   </div>
  </div>

  <div class="hmb-card">
   <div class="hmb-head">
    <span>📰</span><h2>Artículos Importados</h2>
    <span id="hmb-cnt" class="hmb-badge">0</span>
    <button class="hmb-ghost" style="margin-left:auto" onclick="hmbRepararImg()" title="Descargar la imagen real de los que la tienen rota">🖼 Reparar imágenes</button>
    <button class="hmb-ghost" onclick="hmbLoadArticles()" title="Recargar">↻</button>
   </div>
   <div class="hmb-body hmb-art-body">
    <div id="art-cont"><div class="hmb-loading">Cargando…</div></div>
   </div>
  </div>
 </div>

</div><!-- /grid -->

<!-- Modal editar fuente -->
<div id="hmb-modal-bg" class="hmb-modal-bg" style="display:none" onclick="hmbCloseModal()">
 <div class="hmb-modal" onclick="event.stopPropagation()">
  <div class="hmb-head"><span>✎</span><h3>Editar fuente</h3>
   <button class="hmb-ghost" style="margin-left:auto" onclick="hmbCloseModal()">✕</button>
  </div>
  <div class="hmb-body">
   <input type="hidden" id="edit-id"/>
   <div class="hmb-row"><label class="hmb-lbl">Nombre</label><input type="text" id="edit-name" class="hmb-inp"/></div>
   <div class="hmb-row"><label class="hmb-lbl">Dominio</label><input type="text" id="edit-url"  class="hmb-inp"/></div>
   <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
    <button class="hmb-btn hmb-save" onclick="hmbSaveEdit()">Guardar</button>
    <button class="hmb-btn" onclick="hmbCloseModal()">Cancelar</button>
   </div>
  </div>
 </div>
</div>

</div><!-- /hmb-wrap -->

<script>
window.HMB={ajax:'<?php echo $this->ajaxBase; ?>',token:'<?php echo $this->token; ?>',hasKey:<?php echo $this->apiKey ? 'true' : 'false'; ?>,state:<?php echo (int) ($this->params->get('article_state', 0)); ?>};
</script>
