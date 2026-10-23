<?php
defined('_JEXEC') or die;

$temas          = $this->temas;
$albums         = $this->albums;
$params         = $this->params;
$titulo         = $params->get('titulo', 'Nuestros Temas');
$subtitulo      = $params->get('subtitulo', '');
$mostrarPlayer  = (bool) $params->get('mostrar_player', 1);
$mostrarSpotify = (bool) $params->get('mostrar_spotify', 1);
$mostrarYoutube = (bool) $params->get('mostrar_youtube', 1);
$primero        = $temas[0] ?? null;
?>
<section class="hmb-temas" aria-label="<?= htmlspecialchars($titulo) ?>">

  <div class="hmb-temas__header">
    <div class="hmb-temas__deco-line"></div>
    <div class="hmb-temas__deco-icon" aria-hidden="true">
      <svg viewBox="0 0 48 48" width="36" height="36" fill="none"
           stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
        <circle cx="24" cy="20" r="14"/>
        <ellipse cx="24" cy="33" rx="9" ry="4.5"/>
        <line x1="19" y1="33" x2="19" y2="38"/>
        <line x1="24" y1="33" x2="24" y2="38"/>
        <line x1="29" y1="33" x2="29" y2="38"/>
        <ellipse cx="18.5" cy="19.5" rx="3.5" ry="4.5" fill="currentColor" opacity=".7" stroke="none"/>
        <ellipse cx="29.5" cy="19.5" rx="3.5" ry="4.5" fill="currentColor" opacity=".7" stroke="none"/>
        <path d="M20.5 28 Q24 30.5 27.5 28" stroke-width="1.2" fill="none"/>
      </svg>
    </div>
    <h2 class="hmb-temas__title"><?= htmlspecialchars($titulo) ?></h2>
    <?php if ($subtitulo): ?>
    <p class="hmb-temas__subtitle"><?= htmlspecialchars($subtitulo) ?></p>
    <?php endif; ?>
    <div class="hmb-temas__deco-line"></div>
  </div>

  <?php if (count($albums) > 1): ?>
  <div class="hmb-temas__filtros" role="group" aria-label="Filtrar por álbum">
    <button class="hmb-btn-filtro hmb-btn-filtro--active" data-album="all" type="button">TODOS</button>
    <?php foreach ($albums as $album): ?>
    <button class="hmb-btn-filtro" data-album="<?= htmlspecialchars($album) ?>" type="button">
      <?= strtoupper(htmlspecialchars($album)) ?>
    </button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($mostrarPlayer && $primero): ?>
  <div class="hmb-player" id="hmb-player" role="region" aria-label="Reproductor">
    <div class="hmb-player__cover-wrap">
      <?php if ($primero->cover_url): ?>
      <img class="hmb-player__cover" id="hmb-player-cover"
           src="<?= htmlspecialchars($primero->cover_url) ?>"
           alt="Portada" width="110" height="110">
      <?php else: ?>
      <span id="hmb-player-cover" style="font-size:2.5rem;color:#444">💿</span>
      <?php endif; ?>
    </div>
    <div class="hmb-player__info">
      <span class="hmb-player__label">AHORA SUENA</span>
      <strong class="hmb-player__nombre" id="hmb-player-nombre"><?= htmlspecialchars($primero->title) ?></strong>
      <span class="hmb-player__tiempo" id="hmb-player-tiempo">00:00 / 00:00</span>
      <div class="hmb-player__barra-wrap" id="hmb-player-barra"
           role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
        <div class="hmb-player__barra-prog" id="hmb-player-prog"></div>
      </div>
    </div>
    <div class="hmb-player__controls">
      <button class="hmb-player__btn" id="hmb-btn-prev" type="button" onclick="hmbPrev()" aria-label="Anterior">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
      </button>
      <button class="hmb-player__btn hmb-player__btn--main" id="hmb-btn-play" type="button" onclick="hmbPlay()" aria-label="Play/Pausa">
        <svg id="hmb-play-icon" width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
      </button>
      <button class="hmb-player__btn" id="hmb-btn-next" type="button" onclick="hmbNext()" aria-label="Siguiente">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M16 6h2v12h-2zm-3.5 6L4 6v12z"/></svg>
      </button>
    </div>
    <audio id="hmb-audio" preload="none" style="display:none"></audio>
  </div>
  <?php endif; ?>

  <?php if ($temas): ?>
  <div class="hmb-temas__lista" id="hmb-lista" role="list">
    <?php $albumActual = null; ?>
    <?php foreach ($temas as $i => $tema): ?>
      <?php if ($tema->album !== $albumActual): ?>
        <?php if ($albumActual !== null): ?></div><?php endif; ?>
        <?php $albumActual = $tema->album; ?>
        <div class="hmb-album" data-album="<?= htmlspecialchars($tema->album) ?>" role="listitem">
          <?php if ($tema->album): ?>
          <div class="hmb-album__header">
            <span class="hmb-album__nombre"><?= strtoupper(htmlspecialchars($tema->album)) ?></span>
            <?php if ($tema->album_year): ?>
            <span class="hmb-album__year"><?= (int)$tema->album_year ?></span>
            <?php endif; ?>
          </div>
          <?php endif; ?>
      <?php endif; ?>

      <article class="hmb-tema"
               data-id="<?= (int)$tema->id ?>"
               data-src="<?= htmlspecialchars($tema->audio_url) ?>"
               data-titulo="<?= htmlspecialchars($tema->title) ?>"
               data-cover="<?= htmlspecialchars($tema->cover_url) ?>"
               data-dur="<?= htmlspecialchars($tema->duration_fmt) ?>"
               role="article">
        <span class="hmb-tema__num"><?= $tema->track_number ?: ($i + 1) ?></span>
        <div class="hmb-tema__info">
          <span class="hmb-tema__titulo"><?= htmlspecialchars($tema->title) ?></span>
          <?php if ($tema->description): ?>
          <span class="hmb-tema__desc"><?= htmlspecialchars(mb_substr(strip_tags($tema->description), 0, 80)) ?><?php if (mb_strlen(strip_tags($tema->description)) > 80): ?>…<?php endif; ?></span>
          <?php endif; ?>
        </div>
        <div class="hmb-tema__links">
          <?php if ($tema->audio_url): ?>
          <a class="hmb-link-social hmb-link-descarga"
             href="<?= htmlspecialchars($tema->audio_url) ?>"
             download="<?= htmlspecialchars($tema->title) ?>.mp3"
             title="Descargar MP3" aria-label="Descargar <?= htmlspecialchars($tema->title) ?>">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
              <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
            </svg>
          </a>
          <?php endif; ?>
          <?php if ($mostrarSpotify && $tema->spotify_url): ?>
          <a class="hmb-link-social hmb-link-social--spotify"
             href="<?= htmlspecialchars($tema->spotify_url) ?>"
             target="_blank" rel="noopener noreferrer" aria-label="Spotify">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.36-.66.48-1.021.24-2.82-1.74-6.36-2.1-10.561-1.14-.418.12-.779-.18-.899-.54-.12-.42.18-.78.54-.9 4.56-1.02 8.52-.6 11.64 1.32.42.18.479.66.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.02.6-1.14C9.6 9.9 15 10.56 18.72 12.84c.361.18.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.3c-.6.18-1.2-.18-1.38-.72-.18-.6.18-1.2.72-1.38C9.06 5.7 16.2 5.94 20.461 8.521c.539.3.719 1.02.419 1.56-.3.421-1.02.599-1.559.3z"/></svg>
          </a>
          <?php endif; ?>
          <?php if ($mostrarYoutube && $tema->youtube_url): ?>
          <a class="hmb-link-social hmb-link-social--youtube"
             href="<?= htmlspecialchars($tema->youtube_url) ?>"
             target="_blank" rel="noopener noreferrer" aria-label="YouTube">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
          </a>
          <?php endif; ?>
        </div>
        <span class="hmb-tema__dur"><?= htmlspecialchars($tema->duration_fmt) ?></span>
        <span class="hmb-tema__playing-ic" aria-hidden="true">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" class="hmb-ic-sound"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
        </span>
      </article>
    <?php endforeach; ?>
    <?php if ($albumActual !== null): ?></div><?php endif; ?>
  </div>
  <?php else: ?>
  <p class="hmb-temas__empty">No hay temas publicados aún.</p>
  <?php endif; ?>

</section>

<style>
:root{--hmb-bg:#0a0a0a;--hmb-s:#111;--hmb-s2:#181818;--hmb-bd:#2a2a2a;--hmb-red:#cc0000;--hmb-rh:#e60000;--hmb-cr:#e8ddd0;--hmb-mu:#777;--hmb-mu2:#444;--hmb-ft:'Cinzel','Palatino Linotype',Georgia,serif}
.hmb-temas{background:var(--hmb-bg);color:var(--hmb-cr);padding:3rem 1.5rem 3.5rem;font-family:system-ui,sans-serif}
.hmb-temas__header{text-align:center;display:flex;flex-direction:column;align-items:center;gap:.5rem;margin-bottom:2.5rem}
.hmb-temas__deco-line{width:200px;height:1px;background:linear-gradient(90deg,transparent,#444,transparent)}
.hmb-temas__deco-icon{color:var(--hmb-mu)}
.hmb-temas__title{font-family:var(--hmb-ft);font-size:clamp(1.6rem,4vw,2.4rem);font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--hmb-cr);margin:0;line-height:1.15}
.hmb-temas__subtitle{font-size:.72rem;letter-spacing:.25em;text-transform:uppercase;color:var(--hmb-mu);margin:0}
.hmb-temas__filtros{display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;margin-bottom:2rem}
.hmb-btn-filtro{background:transparent;color:var(--hmb-mu);border:1px solid var(--hmb-bd);padding:.4rem 1.1rem;border-radius:2px;font-size:.68rem;letter-spacing:.14em;cursor:pointer;transition:all .2s}
.hmb-btn-filtro:hover,.hmb-btn-filtro--active{background:var(--hmb-red);border-color:var(--hmb-red);color:#fff}
.hmb-player{display:grid;grid-template-columns:110px 1fr auto;align-items:center;gap:1.5rem;background:var(--hmb-s);border:1px solid var(--hmb-bd);border-left:3px solid var(--hmb-red);padding:1.25rem 1.5rem;margin-bottom:2rem;border-radius:2px}
.hmb-player__cover-wrap{width:110px;height:110px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:var(--hmb-s2);border-radius:2px}
.hmb-player__cover{width:110px;height:110px;object-fit:cover;display:block;border-radius:2px;filter:brightness(.85)}
.hmb-player__info{display:flex;flex-direction:column;gap:.35rem;min-width:0}
.hmb-player__label{font-size:.63rem;letter-spacing:.25em;color:var(--hmb-red);text-transform:uppercase}
.hmb-player__nombre{font-family:var(--hmb-ft);font-size:1.05rem;color:var(--hmb-cr);letter-spacing:.06em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hmb-player__tiempo{font-family:'Courier New',monospace;font-size:.7rem;color:var(--hmb-mu)}
.hmb-player__barra-wrap{height:3px;background:var(--hmb-bd);border-radius:2px;cursor:pointer;margin-top:.3rem}
.hmb-player__barra-prog{height:100%;background:var(--hmb-red);border-radius:2px;width:0%;transition:width .4s linear}
.hmb-player__controls{display:flex;align-items:center;gap:.6rem}
.hmb-player__btn{background:transparent;border:1px solid var(--hmb-bd);color:var(--hmb-mu);width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;flex-shrink:0}
.hmb-player__btn:hover{border-color:var(--hmb-red);color:var(--hmb-red)}
.hmb-player__btn--main{background:var(--hmb-red);border-color:var(--hmb-red);color:#fff;width:48px;height:48px}
.hmb-player__btn--main:hover{background:var(--hmb-rh);border-color:var(--hmb-rh)}
.hmb-album{margin-bottom:1.5rem}
.hmb-album__header{display:flex;align-items:baseline;gap:.75rem;padding:.5rem 0 .5rem 1rem;border-bottom:1px solid var(--hmb-bd);margin-bottom:.25rem}
.hmb-album__nombre{font-family:var(--hmb-ft);font-size:.78rem;letter-spacing:.18em;color:var(--hmb-cr)}
.hmb-album__year{font-size:.7rem;color:var(--hmb-mu)}
.hmb-tema{display:grid;grid-template-columns:2rem 1fr auto auto 1.2rem;align-items:center;gap:.75rem;padding:.65rem 1rem;border-bottom:1px solid #1a1a1a;cursor:pointer;transition:background .15s}
.hmb-tema:last-child{border-bottom:none}
.hmb-tema:hover{background:var(--hmb-s2)}
.hmb-tema--activo{background:#150000}
.hmb-tema--activo .hmb-tema__titulo{color:var(--hmb-red)}
.hmb-tema--activo .hmb-tema__num{opacity:0}
.hmb-tema__num{font-size:.72rem;color:var(--hmb-mu2);text-align:right}
.hmb-tema__info{min-width:0}
.hmb-tema__titulo{font-family:var(--hmb-ft);font-size:.88rem;letter-spacing:.05em;color:var(--hmb-cr);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:color .15s}
.hmb-tema__desc{font-size:.7rem;color:var(--hmb-mu);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hmb-tema__links{display:flex;gap:.35rem;align-items:center}
.hmb-link-social{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;border:1px solid var(--hmb-bd);text-decoration:none;color:var(--hmb-mu);transition:all .15s}
.hmb-link-social--spotify:hover{border-color:#1DB954;color:#1DB954}
.hmb-link-descarga{border-color:#555}
.hmb-link-descarga:hover{border-color:#d4a017;color:#d4a017}
.hmb-link-social--youtube:hover{border-color:var(--hmb-red);color:var(--hmb-red)}
.hmb-tema__dur{font-family:'Courier New',monospace;font-size:.72rem;color:var(--hmb-mu);min-width:2.8rem;text-align:right}
.hmb-tema__playing-ic{display:flex;align-items:center;justify-content:center;color:var(--hmb-red);opacity:0;transition:opacity .2s;width:1.2rem}
.hmb-tema--activo .hmb-tema__playing-ic{opacity:1}
.hmb-ic-sound{animation:hmb-pulse 1.2s ease-in-out infinite}
@keyframes hmb-pulse{0%,100%{opacity:.4}50%{opacity:1}}
.hmb-temas__empty{text-align:center;color:var(--hmb-mu);letter-spacing:.1em;text-transform:uppercase;font-size:.8rem;padding:3rem}
@media(max-width:640px){
  .hmb-player{grid-template-columns:70px 1fr;grid-template-rows:auto auto}
  .hmb-player__controls{grid-column:1/-1;justify-content:center}
  .hmb-player__cover-wrap,.hmb-player__cover{width:70px;height:70px}
  .hmb-tema{grid-template-columns:1.5rem 1fr auto 1.2rem}
  .hmb-tema__dur{display:none}
}
</style>

<?php if ($mostrarPlayer): ?>
<script>
var hmbAudio   = null;
var hmbIdx     = 0;
var hmbTemas   = [];

window.addEventListener('load', function() {
  hmbAudio = document.getElementById('hmb-audio');
  hmbTemas = Array.from(document.querySelectorAll('.hmb-tema'));
  if (hmbTemas.length) hmbCargar(0, false);
});

function hmbPlay() {
  if (!hmbAudio) return;
  if (!hmbAudio.src || hmbAudio.src === window.location.href) { hmbCargar(0, true); return; }
  if (hmbAudio.paused) { hmbAudio.play(); hmbIcon(true); }
  else                 { hmbAudio.pause(); hmbIcon(false); }
}
function hmbPrev() { hmbCargar(hmbIdx - 1, true); }
function hmbNext() { hmbCargar(hmbIdx + 1, true); }

function hmbCargar(i, auto) {
  if (!hmbAudio || i < 0 || i >= hmbTemas.length) return;
  hmbIdx = i;
  var t = hmbTemas[i];
  hmbTemas.forEach(function(el){ el.classList.remove('hmb-tema--activo'); });
  t.classList.add('hmb-tema--activo');

  var src    = t.dataset.src    || '';
  var titulo = t.dataset.titulo || '';
  var cover  = t.dataset.cover  || '';
  var dur    = t.dataset.dur    || '0:00';

  var elNombre = document.getElementById('hmb-player-nombre');
  var elTiempo = document.getElementById('hmb-player-tiempo');
  var elProg   = document.getElementById('hmb-player-prog');
  var elCover  = document.getElementById('hmb-player-cover');

  if (elNombre) elNombre.textContent = titulo;
  if (elTiempo) elTiempo.textContent = '0:00 / ' + dur;
  if (elProg)   elProg.style.width   = '0%';
  if (elCover && elCover.tagName === 'IMG' && cover) elCover.src = cover;

  if (auto && src) {
    hmbAudio.src = src;
    hmbAudio.load();
    hmbAudio.play().catch(function(e){ console.error('HMB:', e.message); });
    hmbIcon(true);
  } else {
    hmbIcon(false);
  }
}

function hmbIcon(playing) {
  var el = document.getElementById('hmb-play-icon');
  if (el) el.innerHTML = playing
    ? '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>'
    : '<path d="M8 5v14l11-7z"/>';
}

document.addEventListener('click', function(e) {
  var tema = e.target.closest('.hmb-tema');
  if (!tema) return;
  if (e.target.closest('.hmb-link-social')) return;
  var i = Array.from(document.querySelectorAll('.hmb-tema')).indexOf(tema);
  if (i === hmbIdx && hmbAudio && !hmbAudio.paused) { hmbAudio.pause(); hmbIcon(false); return; }
  hmbCargar(i, true);
});

document.addEventListener('DOMContentLoaded', function() {
  var audio = document.getElementById('hmb-audio');
  if (!audio) return;

  audio.addEventListener('timeupdate', function() {
    if (!audio.duration) return;
    var p = (audio.currentTime / audio.duration) * 100;
    var prog  = document.getElementById('hmb-player-prog');
    var barra = document.getElementById('hmb-player-barra');
    var tiempo = document.getElementById('hmb-player-tiempo');
    if (prog)  prog.style.width = p + '%';
    if (barra) barra.setAttribute('aria-valuenow', Math.round(p));
    if (tiempo) {
      var fmt = function(s){ s=Math.floor(s); return Math.floor(s/60)+':'+String(s%60).padStart(2,'0'); };
      tiempo.textContent = fmt(audio.currentTime) + ' / ' + fmt(audio.duration);
    }
  });

  audio.addEventListener('ended', function() { hmbCargar(hmbIdx + 1, true); });

  var barra = document.getElementById('hmb-player-barra');
  if (barra) barra.addEventListener('click', function(e) {
    if (!audio.duration) return;
    var r = barra.getBoundingClientRect();
    audio.currentTime = ((e.clientX - r.left) / r.width) * audio.duration;
  });

  document.querySelectorAll('.hmb-btn-filtro').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.hmb-btn-filtro').forEach(function(b){ b.classList.remove('hmb-btn-filtro--active'); });
      btn.classList.add('hmb-btn-filtro--active');
      var al = btn.dataset.album;
      document.querySelectorAll('.hmb-album').forEach(function(g) {
        g.style.display = (al === 'all' || g.dataset.album === al) ? '' : 'none';
      });
    });
  });
});
</script>
<?php endif; ?>

