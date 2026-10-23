<?php
defined('_JEXEC') or die;
$discos = $this->discos;
?>
<section class="hmb-discos">

<?php if (empty($discos)): ?>
  <p class="hmb-discos__empty">No hay discos publicados aún.</p>
<?php else: ?>

<div class="hmb-discos__grid">
<?php foreach ($discos as $d): ?>
<article class="hmb-disco-card" id="disco-<?= $d->id ?>">

  <?php /* Portada */ ?>
  <div class="hmb-disco-card__cover">
    <?php if ($d->portada_url): ?>
    <img src="<?= htmlspecialchars($d->portada_url) ?>"
         alt="<?= htmlspecialchars($d->titulo) ?>" loading="lazy">
    <?php else: ?>
    <div class="hmb-disco-card__cover-placeholder">💿</div>
    <?php endif; ?>

    <?php /* Overlay con botón play */ ?>
    <?php if ($d->audio_url || $d->youtube_audio_id): ?>
    <div class="hmb-disco-card__overlay">
      <button class="hmb-disco-play-btn"
              onclick="discoPlay(<?= $d->id ?>, '<?= htmlspecialchars($d->audio_url, ENT_QUOTES) ?>', '<?= htmlspecialchars($d->youtube_audio_id, ENT_QUOTES) ?>')"
              aria-label="Reproducir <?= htmlspecialchars($d->titulo) ?>">
        <svg id="disco-play-icon-<?= $d->id ?>" width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
          <path d="M8 5v14l11-7z"/>
        </svg>
      </button>
    </div>
    <?php endif; ?>
  </div>

  <?php /* Info del disco */ ?>
  <div class="hmb-disco-card__body">
    <h3 class="hmb-disco-card__titulo"><?= htmlspecialchars($d->titulo) ?></h3>
    <p class="hmb-disco-card__grupo"><?= htmlspecialchars($d->grupo) ?></p>
    <?php if (!empty($d->genero)): ?>
    <p class="hmb-disco-card__genero"><?= htmlspecialchars($d->genero) ?></p>
    <?php endif; ?>

    <div class="hmb-disco-card__meta">
      <?php if ($d->productora): ?>
      <span class="hmb-disco-card__meta-item">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
        <?= htmlspecialchars($d->productora) ?>
      </span>
      <?php endif; ?>
      <?php if ($d->anno): ?>
      <span class="hmb-disco-card__meta-item">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
        <?= $d->anno ?>
      </span>
      <?php endif; ?>
    </div>

    <?php /* Mini player */ ?>
    <?php if ($d->audio_url): ?>
    <div class="hmb-disco-card__player" id="disco-player-<?= $d->id ?>" style="display:none">
      <div class="hmb-disco-card__player-inner">
        <span class="hmb-disco-card__now">▶ Reproduciendo</span>
        <div class="hmb-disco-card__bar-wrap" onclick="discoSeek(<?= $d->id ?>, event)"
             id="disco-bar-<?= $d->id ?>">
          <div class="hmb-disco-card__bar-prog" id="disco-prog-<?= $d->id ?>"></div>
        </div>
        <span class="hmb-disco-card__time" id="disco-time-<?= $d->id ?>">0:00</span>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($d->youtube_audio_id): ?>
    <div id="disco-yt-player-<?= $d->id ?>" style="display:none;margin-top:.4rem">
      <iframe id="disco-yt-iframe-<?= $d->id ?>"
              width="100%" height="80"
              src=""
              data-src="https://www.youtube.com/embed/<?= htmlspecialchars($d->youtube_audio_id) ?>?autoplay=1&rel=0"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope"
              allowfullscreen
              style="border-radius:3px;border:1px solid #cc0000"></iframe>
    </div>
    <?php endif; ?>

    <?php /* Descripción */ ?>
    <?php if ($d->descripcion): ?>
    <p class="hmb-disco-card__desc"><?= htmlspecialchars(mb_substr(strip_tags($d->descripcion),0,120)) ?>
      <?php if (mb_strlen(strip_tags($d->descripcion))>120): ?>…<?php endif; ?>
    </p>
    <?php endif; ?>

    <?php /* Enlaces YouTube */ ?>
    <?php if (!empty($d->yt_links)): ?>
    <div class="hmb-disco-card__yt">
      <span class="hmb-disco-card__yt-label">Ver en YouTube:</span>
      <div class="hmb-disco-card__yt-links">
        <?php foreach ($d->yt_links as $yt): ?>
        <?php if (!empty($yt['url'])): ?>
        <a class="hmb-disco-card__yt-link"
           href="<?= htmlspecialchars($yt['url']) ?>"
           target="_blank" rel="noopener noreferrer">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1C24 15.9 24 12 24 12s0-3.9-.5-5.8zM9.5 15.6V8.4L15.8 12l-6.3 3.6z"/></svg>
          <?= htmlspecialchars($yt['titulo'] ?: 'Ver vídeo') ?>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</article>
<?php endforeach; ?>
</div>

<?php endif; ?>

<!-- Audio global -->
<audio id="disco-audio-global" preload="none" style="display:none"></audio>

</section>

<style>
.hmb-discos{background:#0a0a0a;padding:2.5rem 1.5rem;min-height:400px}
.hmb-discos__empty{text-align:center;color:#555;font-size:.85rem;padding:4rem;text-transform:uppercase;letter-spacing:.1em}
.hmb-discos__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.5rem;max-width:1200px;margin:0 auto}
/* Tarjeta */
.hmb-disco-card{background:#111;border:1px solid #222;border-radius:4px;overflow:hidden;transition:transform .2s,border-color .2s;display:flex;flex-direction:column}
.hmb-disco-card:hover{transform:translateY(-3px);border-color:#cc0000}
/* Portada */
.hmb-disco-card__cover{position:relative;aspect-ratio:1;overflow:hidden;background:#1a1a1a;flex-shrink:0}
.hmb-disco-card__cover img{width:100%;height:100%;object-fit:cover;transition:filter .3s}
.hmb-disco-card:hover .hmb-disco-card__cover img{filter:brightness(.6)}
.hmb-disco-card__cover-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:4rem;color:#333}
.hmb-disco-card__overlay{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s}
.hmb-disco-card:hover .hmb-disco-card__overlay{opacity:1}
/* Botón play sobre portada */
.hmb-disco-play-btn{background:#cc0000;border:none;border-radius:50%;width:52px;height:52px;display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;transition:transform .2s,background .2s;box-shadow:0 2px 12px rgba(0,0,0,.6)}
.hmb-disco-play-btn:hover{background:#e60000;transform:scale(1.1)}
/* Cuerpo */
.hmb-disco-card__body{padding:.85rem;display:flex;flex-direction:column;gap:.4rem;flex:1}
.hmb-disco-card__titulo{font-family:'Cinzel',Georgia,serif;font-size:.9rem;letter-spacing:.05em;color:#e8ddd0;margin:0;line-height:1.3}
.hmb-disco-card__grupo{font-size:.75rem;color:#cc0000;margin:0;letter-spacing:.05em;text-transform:uppercase}
.hmb-disco-card__meta{display:flex;flex-wrap:wrap;gap:.5rem}
.hmb-disco-card__meta-item{font-size:.68rem;color:#666;display:flex;align-items:center;gap:.25rem}
.hmb-disco-card__genero{font-size:.68rem;color:#888;margin:0;font-style:italic;letter-spacing:.03em}
/* Mini player */
.hmb-disco-card__player{background:#0d0000;border:1px solid #cc0000;border-radius:3px;padding:.4rem .6rem;margin-top:.25rem}
.hmb-disco-card__player-inner{display:flex;align-items:center;gap:.5rem}
.hmb-disco-card__now{font-size:.62rem;color:#cc0000;white-space:nowrap;letter-spacing:.05em}
.hmb-disco-card__bar-wrap{flex:1;height:3px;background:#333;border-radius:2px;cursor:pointer}
.hmb-disco-card__bar-prog{height:100%;background:#cc0000;border-radius:2px;width:0%;transition:width .4s linear}
.hmb-disco-card__time{font-family:monospace;font-size:.62rem;color:#777;white-space:nowrap}
/* Descripción */
.hmb-disco-card__desc{font-size:.72rem;color:#666;margin:0;line-height:1.5}
/* YouTube */
.hmb-disco-card__yt{margin-top:.25rem}
.hmb-disco-card__yt-label{font-size:.62rem;color:#555;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.3rem}
.hmb-disco-card__yt-links{display:flex;flex-wrap:wrap;gap:.3rem}
.hmb-disco-card__yt-link{display:inline-flex;align-items:center;gap:.3rem;background:#1a0000;border:1px solid #cc0000;color:#cc0000;font-size:.68rem;padding:.2rem .5rem;border-radius:3px;text-decoration:none;transition:all .15s}
.hmb-disco-card__yt-link:hover{background:#cc0000;color:#fff}
/* Responsive */
@media(max-width:600px){.hmb-discos__grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem}}
</style>

<script>
(function(){
  var globalAudio = document.getElementById('disco-audio-global');
  var activoId    = null;

  function fmt(s){ s=Math.floor(s||0); return Math.floor(s/60)+':'+String(s%60).padStart(2,'0'); }

  function setIcon(id, playing) {
    var el = document.getElementById('disco-play-icon-'+id);
    if (el) el.innerHTML = playing
      ? '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>'
      : '<path d="M8 5v14l11-7z"/>';
  }

  function showPlayer(id, show) {
    var p = document.getElementById('disco-player-'+id);
    if (p) p.style.display = show ? '' : 'none';
  }

  window.discoPlay = function(id, src, ytId) {
    // Parar el anterior
    if (activoId && activoId !== id) {
      globalAudio.pause();
      setIcon(activoId, false);
      showPlayer(activoId, false);
      // Parar iframe YouTube anterior
      var prevIframe = document.getElementById('disco-yt-iframe-'+activoId);
      if (prevIframe) { prevIframe.src = ''; document.getElementById('disco-yt-player-'+activoId).style.display='none'; }
    }

    if (activoId === id) {
      // Toggle: parar si ya está activo
      if (ytId) {
        var ytDiv = document.getElementById('disco-yt-player-'+id);
        var ytF   = document.getElementById('disco-yt-iframe-'+id);
        if (ytDiv && ytDiv.style.display !== 'none') {
          ytF.src = ''; ytDiv.style.display = 'none'; setIcon(id, false); activoId = null; return;
        }
      } else if (!globalAudio.paused) {
        globalAudio.pause(); setIcon(id, false); activoId = null; return;
      }
    }

    activoId = id;

    if (ytId) {
      // Reproducir con YouTube embed
      var ytDiv = document.getElementById('disco-yt-player-'+id);
      var ytF   = document.getElementById('disco-yt-iframe-'+id);
      if (ytDiv && ytF) {
        ytF.src = ytF.dataset.src;
        ytDiv.style.display = '';
      }
      setIcon(id, true);
    } else if (src) {
      // Reproducir con HTML5 audio
      globalAudio.src = src;
      globalAudio.load();
      globalAudio.play().catch(function(){});
      setIcon(id, true);
      showPlayer(id, true);
    }
  };

  window.discoSeek = function(id, e) {
    if (!globalAudio.duration) return;
    var bar = document.getElementById('disco-bar-'+id);
    var r   = bar.getBoundingClientRect();
    globalAudio.currentTime = ((e.clientX - r.left) / r.width) * globalAudio.duration;
  };

  globalAudio.addEventListener('timeupdate', function() {
    if (!activoId || !globalAudio.duration) return;
    var pct  = (globalAudio.currentTime / globalAudio.duration) * 100;
    var prog = document.getElementById('disco-prog-'+activoId);
    var time = document.getElementById('disco-time-'+activoId);
    if (prog) prog.style.width = pct + '%';
    if (time) time.textContent = fmt(globalAudio.currentTime) + ' / ' + fmt(globalAudio.duration);
  });

  globalAudio.addEventListener('ended', function() {
    if (activoId) { setIcon(activoId, false); }
  });
})();
</script>
