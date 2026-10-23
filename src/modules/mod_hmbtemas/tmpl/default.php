<?php
defined('_JEXEC') or die;

$titulo         = $params->get('titulo', 'Nuestros Temas');
$mostrarSpotify = (bool) $params->get('mostrar_spotify', 1);
$mostrarYoutube = (bool) $params->get('mostrar_youtube', 1);
$total          = count($temas);
?>
<style>
.hmbw{display:block;width:100%;box-sizing:border-box;background:var(--dark, #0A0A0A);color:var(--cream, #E8E0D5);font-family:system-ui,sans-serif;border:1px solid var(--steel2, #222222)}
.hmbw-head{text-align:center;padding:.55rem .5rem;border-bottom:1px solid var(--grey-2, #2A2A2A);background:var(--mid, #111111)}
.hmbw-head span{font-family:'Cinzel',Georgia,serif;font-size:.82rem;letter-spacing:.2em;text-transform:uppercase;color:var(--cream, #E8E0D5)}
.hmbw-fila{display:grid;grid-template-columns:1.6rem 1fr auto auto;align-items:center;gap:.4rem;padding:.5rem .65rem;border-bottom:1px solid var(--steel, #1A1A1A);cursor:pointer;transition:background .15s}
.hmbw-fila:hover{background:#161616}
.hmbw-fila.act .hmbw-t{color:var(--fire, #CC2200)}
.hmbw-fila.act .hmbw-n{opacity:0}
.hmbw-n{font-size:.65rem;color:var(--grey-4, #444444);text-align:right}
.hmbw-info{min-width:0}
.hmbw-t{font-family:'Cinzel',Georgia,serif;font-size:.78rem;color:var(--cream, #E8E0D5);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:color .15s}
.hmbw-a{font-size:.62rem;color:var(--grey-5, #555555);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hmbw-btns{display:flex;gap:.3rem;align-items:center}
.hmbw-ico{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;border:1px solid var(--grey-2, #2A2A2A);text-decoration:none;color:var(--grey-5, #555555);transition:all .15s;flex-shrink:0}
.hmbw-ico:hover{border-color:var(--fire, #CC2200);color:var(--fire, #CC2200)}
.hmbw-dl:hover{border-color:var(--gold-light, #DAA520) !important;color:var(--gold-light, #DAA520) !important}
.hmbw-sp:hover{border-color:#1DB954 !important;color:#1DB954 !important}
.hmbw-dur{font-family:monospace;font-size:.65rem;color:var(--grey-5, #555555);min-width:2.4rem;text-align:right;white-space:nowrap}
.hmbw-sound{display:inline-flex;align-items:center;color:var(--fire, #CC2200);opacity:0;width:.8rem}
.hmbw-fila.act .hmbw-sound{opacity:1}
.hmbw-pulse{animation:hmb-p 1.2s ease-in-out infinite}
@keyframes hmb-p{0%,100%{opacity:.3}50%{opacity:1}}
/* Extra oculto por defecto — display:none es más fiable que max-height */
#hmbw-extra{display:none}
.hmbw-btn{width:100%;background:var(--mid, #111111);border:none;border-top:1px solid var(--grey-2, #2A2A2A);
  color:var(--grey-6, #666666);font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;
  padding:.45rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.35rem;
  transition:color .2s}
.hmbw-btn:hover{color:var(--fire, #CC2200)}
.hmbw-btn.open{color:var(--fire, #CC2200);border-top-color:var(--fire, #CC2200);background:#120000}
</style>

<div class="hmbw">

  <div class="hmbw-head"><span><?= htmlspecialchars($titulo) ?></span></div>

  <audio id="hmbw-audio" preload="none" style="display:none"></audio>

  <?php if ($temas): ?>

    <?php /* Primera canción siempre visible */ ?>
    <?php $t = $temas[0]; ?>
    <?= hmbwFila($t, 1, true, $mostrarSpotify, $mostrarYoutube) ?>

    <?php /* Resto colapsado */ ?>
    <?php if ($total > 1): ?>
    <div id="hmbw-extra" hidden>
      <button onclick="hmbwToggle()"
              style="width:100%;background:#120000;border:none;border-bottom:2px solid #cc0000;
                     color:#cc0000;font-size:.68rem;letter-spacing:.1em;text-transform:uppercase;
                     padding:.45rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.35rem">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M7 14l5-5 5 5z"/></svg>
        Cerrar lista
      </button>
      <?php foreach ($temas as $i => $t): ?>
        <?php if ($i === 0) continue; ?>
        <?= hmbwFila($t, $t->track_number ?: $i + 1, false, $mostrarSpotify, $mostrarYoutube) ?>
      <?php endforeach; ?>
    </div>

    <button class="hmbw-btn" id="hmbw-btn" onclick="hmbwToggle()">
      <svg id="hmbw-ico" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"
           style="transition:transform .25s"><path d="M7 10l5 5 5-5z"/></svg>
      <span id="hmbw-txt">Ver todas (<?= $total - 1 ?> más)</span>
    </button>
    <?php endif; ?>

  <?php else: ?>
    <p style="text-align:center;color:#555;font-size:.75rem;padding:1rem 0">Sin temas publicados.</p>
  <?php endif; ?>

</div>

<?php
function hmbwFila($t, $num, $activa, $spotify, $youtube) {
    $cls = $activa ? 'hmbw-fila act' : 'hmbw-fila';
    ob_start(); ?>
    <div class="<?= $cls ?>"
         data-src="<?= htmlspecialchars($t->audio_url) ?>"
         data-titulo="<?= htmlspecialchars($t->title) ?>"
         onclick="hmbwClick(this,event)">
      <span class="hmbw-n"><?= $num ?></span>
      <div class="hmbw-info">
        <span class="hmbw-t"><?= htmlspecialchars($t->title) ?></span>
        <?php if ($t->album): ?>
        <span class="hmbw-a"><?= htmlspecialchars($t->album) ?></span>
        <?php endif; ?>
      </div>
      <div class="hmbw-btns">
        <?php if ($t->audio_url): ?>
        <a class="hmbw-ico hmbw-dl" href="<?= htmlspecialchars($t->audio_url) ?>"
           download="<?= htmlspecialchars($t->title) ?>.mp3"
           onclick="event.stopPropagation()" title="Descargar">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
        </a>
        <?php endif; ?>
        <?php if ($spotify && $t->spotify_url): ?>
        <a class="hmbw-ico hmbw-sp" href="<?= htmlspecialchars($t->spotify_url) ?>"
           target="_blank" rel="noopener" onclick="event.stopPropagation()" title="Spotify">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.36-.66.48-1.021.24-2.82-1.74-6.36-2.1-10.561-1.14-.42.12-.78-.18-.9-.54-.12-.42.18-.78.54-.9 4.56-1.02 8.52-.6 11.64 1.32.42.18.48.66.3 1.02zm1.44-3.3c-.3.42-.84.6-1.26.3-3.24-1.98-8.16-2.58-11.94-1.38-.48.12-1.02-.12-1.14-.6-.12-.48.12-1.02.6-1.14C9.6 9.9 15 10.56 18.72 12.84c.36.18.54.78.24 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.3c-.6.18-1.2-.18-1.38-.72-.18-.6.18-1.2.72-1.38C9.06 5.7 16.2 5.94 20.46 8.52c.54.3.72 1.02.42 1.56-.3.42-1.02.6-1.56.3z"/></svg>
        </a>
        <?php endif; ?>
        <?php if ($youtube && $t->youtube_url): ?>
        <a class="hmbw-ico" href="<?= htmlspecialchars($t->youtube_url) ?>"
           target="_blank" rel="noopener" onclick="event.stopPropagation()" title="YouTube">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1C24 15.9 24 12 24 12s0-3.9-.5-5.8zM9.5 15.6V8.4L15.8 12l-6.3 3.6z"/></svg>
        </a>
        <?php endif; ?>
      </div>
      <div style="display:flex;align-items:center;gap:.3rem">
        <span class="hmbw-dur"><?= htmlspecialchars($t->duration_fmt) ?></span>
        <span class="hmbw-sound">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="hmbw-pulse"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/></svg>
        </span>
      </div>
    </div>
    <?php return ob_get_clean();
}
?>

<script>
(function(){
  var audio  = document.getElementById('hmbw-audio');
  var filas  = [];
  var activo = 0;

  window.addEventListener('load', function(){
    filas = Array.from(document.querySelectorAll('.hmbw-fila'));
  });

  window.hmbwClick = function(el, e) {
    if (e && e.target.closest('.hmbw-ico')) return;
    if (!audio) return;
    filas.forEach(function(f){ f.classList.remove('act'); });
    el.classList.add('act');
    var i   = filas.indexOf(el);
    var src = el.dataset.src || '';
    if (i === activo && !audio.paused) { audio.pause(); return; }
    activo = i;
    if (src) { audio.src = src; audio.load(); audio.play().catch(function(){}); }
  };

  window.hmbwToggle = function() {
    var extra = document.getElementById('hmbw-extra');
    var btn   = document.getElementById('hmbw-btn');
    var ico   = document.getElementById('hmbw-ico');
    var txt   = document.getElementById('hmbw-txt');
    if (!extra) return;
    var open = extra.hasAttribute("hidden");
    if (open) { extra.removeAttribute("hidden"); extra.style.display="block"; } else { extra.setAttribute("hidden",""); extra.style.display="none"; }
    btn.classList.toggle('open', open);
    ico.style.transform = open ? 'rotate(180deg)' : 'rotate(0)';
    txt.textContent     = open ? '▲ Cerrar lista' : 'Ver todas (<?= $total - 1 ?> más)';
    // Reiniciar lista de filas para incluir las nuevas visibles
    filas = Array.from(document.querySelectorAll('.hmbw-fila'));
  };

  audio && audio.addEventListener('ended', function(){
    var next = filas[activo + 1];
    if (next) hmbwClick(next, null);
  });
})();
</script>
