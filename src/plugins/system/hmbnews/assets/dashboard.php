<?php defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;

// Variables injected from hmbimporter.php:
// $hmbParams, $hmbToken, $hmbApiKey, $hmbCatId, $hmbNum, $hmbAjax
?>
<div id="hmb-wrap">

<?php if (!$hmbApiKey): ?>
<div class="hmb-alert">
    ⚠ <strong>Sin API Key:</strong>
    Ve a <a href="<?php echo Route::_('index.php?option=com_config&view=component&component=com_hmb'); ?>" class="hmb-link">Ajustes del componente</a> e introduce tu clave de Anthropic.
</div>
<?php endif; ?>

<div class="hmb-grid">

 <!-- COLUMNA IZQUIERDA -->
 <div class="hmb-left">

  <div class="hmb-card">
   <div class="hmb-head"><span>📡</span><h2>Fuentes de Noticias</h2></div>
   <div class="hmb-body">
    <p class="hmb-hint">Activa o desactiva fuentes. Solo se usarán las activas.</p>
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
   <div class="hmb-head"><span>⚙</span><h2>Opciones de Importación</h2></div>
   <div class="hmb-body">
    <div class="hmb-row">
     <label class="hmb-lbl">Artículos a buscar</label>
     <input type="number" id="hmb-num" class="hmb-inp hmb-sm" value="<?php echo $hmbNum; ?>" min="1" max="15"/>
    </div>
    <div class="hmb-row">
     <label class="hmb-lbl">Tipo de contenido</label>
     <select id="hmb-type" class="hmb-sel">
      <option value="news">🗞 Últimas noticias</option>
      <option value="reviews">💿 Reseñas de álbumes</option>
      <option value="lists">📋 Listas y rankings</option>
      <option value="videos">🎥 Vídeos y directos</option>
      <option value="interviews">🎤 Entrevistas</option>
     </select>
    </div>
    <div class="hmb-row">
     <label class="hmb-lbl">ID de categoría Joomla</label>
     <input type="number" id="hmb-catid" class="hmb-inp hmb-sm" value="<?php echo $hmbCatId; ?>" min="1"/>
    </div>
    <button id="hmb-imp-btn" class="hmb-import-btn" onclick="hmbImport()">
     <span id="hmb-imp-lbl">⚡ IMPORTAR AHORA</span>
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
  <div class="hmb-card">
   <div class="hmb-head">
    <span>📰</span><h2>Artículos Importados</h2>
    <span id="hmb-cnt" class="hmb-badge">0</span>
    <button class="hmb-ghost" style="margin-left:auto" onclick="hmbLoadArticles()" title="Recargar">↻</button>
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
window.HMB={ajax:'<?php echo $hmbAjax; ?>',token:'<?php echo $hmbToken; ?>',hasKey:<?php echo $hmbApiKey ? 'true' : 'false'; ?>};
</script>
