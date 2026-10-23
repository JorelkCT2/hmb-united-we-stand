<?php defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;

// Variables injected from hmbimporter.php:
// $this->params, $this->token, $this->apiKey, $this->catId, $this->num, $this->ajaxBase
?>
<div id="hmb-wrap">

<?php if (!$this->apiKey): ?>
<div class="hmb-alert">
    ⚠ <strong>Sin API Key:</strong>
    Ve a <a href="<?php echo Route::_('index.php?option=com_config&view=component&component=com_hmbnews'); ?>" class="hmb-link">Ajustes del componente</a> e introduce tu clave de Anthropic.
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
     <input type="number" id="hmb-num" class="hmb-inp hmb-sm" value="2" min="1" max="15"/>
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
     
                <label style="display:block;margin-top:12px;font-size:12px;color:#888;">Región de noticias</label>
                <select id="hmbRegion" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;background:#ffffff;color:#222222;">
                    <option value="europe" selected style="color:#222222;background:#ffffff;">Europa</option>
                    <option value="all" style="color:#222222;background:#ffffff;">Internacional (todas)</option>
                    <option value="northam" style="color:#222222;background:#ffffff;">Norteamerica</option>
                    <option value="southam" style="color:#222222;background:#ffffff;">Sudamerica / Latinoamerica</option>
                </select>
<label class="hmb-lbl">ID de categoría Joomla</label>
     <input type="number" id="hmb-catid" class="hmb-inp hmb-sm" value="<?php echo $this->catId; ?>" min="1"/>
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
window.HMB={ajax:'<?php echo $this->ajaxBase; ?>',token:'<?php echo $this->token; ?>',hasKey:<?php echo $this->apiKey ? 'true' : 'false'; ?>};
</script>

<script>
(function() {
    // Añadir boton "Limpiar historial" al lado del icono refresh
    function attach() {
        const refreshIcon = document.querySelector('#hmbArticlesRefresh, .hmb-articles-refresh, [data-hmb-refresh]');
        let panel = document.querySelector('#hmbArticlesPanel, .hmb-articles-panel');
        if (!panel) {
            // Fallback: buscar el panel por texto
            const panels = document.querySelectorAll('.hmb-panel, .panel, .card');
            for (const p of panels) {
                if (p.textContent.toLowerCase().includes('artículos importados') || p.textContent.toLowerCase().includes('articulos importados')) {
                    panel = p; break;
                }
            }
        }
        if (!panel) return;
        if (panel.querySelector('.hmb-clear-history-btn')) return;

        const header = panel.querySelector('.hmb-panel-header, .panel-header, .card-header, h2, h3') || panel.firstElementChild;
        if (!header) return;

        const btn = document.createElement('button');
        btn.className = 'hmb-clear-history-btn';
        btn.type = 'button';
        btn.textContent = '🗑 Limpiar historial';
        btn.style.cssText = 'background:#7c1f1f;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;font-size:12px;margin-left:auto;';
        btn.onclick = async () => {
            if (!confirm('¿Borrar TODOS los artículos importados? Esto eliminará los artículos de Joomla y vaciará el historial. No se puede deshacer.')) return;
            btn.disabled = true; btn.textContent = 'Borrando…';
            try {
                const fd = new FormData();
                fd.append(<?php echo json_encode($this->token); ?>, '1');
                const res = await fetch(<?php echo json_encode($this->ajaxBase . 'clearHistory'); ?>, { method: 'POST', body: fd, credentials: 'same-origin' });
                const data = await res.json();
                if (data.success) {
                    alert('✓ ' + data.message);
                    location.reload();
                } else {
                    alert('✗ ' + (data.message || 'Error'));
                    btn.disabled = false; btn.textContent = '🗑 Limpiar historial';
                }
            } catch (e) {
                alert('Error: ' + e.message);
                btn.disabled = false; btn.textContent = '🗑 Limpiar historial';
            }
        };
        header.style.display = 'flex';
        header.style.alignItems = 'center';
        header.appendChild(btn);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attach);
    } else {
        attach();
    }
    setTimeout(attach, 1000);
    setTimeout(attach, 3000);
})();
</script>
