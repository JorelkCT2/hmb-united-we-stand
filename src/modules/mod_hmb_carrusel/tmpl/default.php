<?php defined('_JEXEC') or die;
if (empty($fotos)) {
    return;
}
$id          = $cfg['modid'];
$nFotos      = count($fotos);
$altura      = $cfg['altura'];
$showThumbs  = $cfg['show_thumbs'];
$showCaption = $cfg['show_caption'];
$autoplay    = $cfg['autoplay'];
$interval    = $cfg['interval'];
?>
<style>
/* ══════════════════════════════════════════
   HMB CARRUSEL — <?php echo $id; ?>
══════════════════════════════════════════ */
#<?php echo $id; ?>{
    --cr:var(--fire, #CC2200);--cr2:var(--blood, #8B0000);--cdk:var(--wine-black, #0A0000);--cdkst:var(--wine-black, #0A0000);
    --cth:72px;
    font-family:'Oswald',Arial,sans-serif;
    user-select:none;
    position:relative;
}

/* Marco exterior */
.<?php echo $id; ?>-wrap{
    position:relative;
    background:var(--cdk);
    border:2px solid var(--cr2);
    border-top:3px solid var(--cr);
    overflow:hidden;
}

/* Pista de slides */
.<?php echo $id; ?>-track{
    position:relative;
    width:100%;
    height:<?php echo $altura; ?>px;
    overflow:hidden;
}
@media(max-width:600px){
    .<?php echo $id; ?>-track{ height:<?php echo round($altura * 0.6); ?>px; }
}

/* Cada slide */
.<?php echo $id; ?>-slide{
    position:absolute;
    inset:0;
    opacity:0;
    transition:opacity .6s ease;
    cursor:zoom-in;
}
.<?php echo $id; ?>-slide.active{
    opacity:1;
    z-index:2;
}
.<?php echo $id; ?>-slide img{
    width:100%;
    height:100%;
    object-fit:contain;
    display:block;
    transition:transform .4s ease;
}
.<?php echo $id; ?>-slide:hover img{
    transform:scale(1.02);
}

/* Overlay degradado inferior */
.<?php echo $id; ?>-overlay{
    position:absolute;
    bottom:0;left:0;right:0;
    height:80px;
    background:linear-gradient(to top,rgba(5,0,0,.9) 0%,transparent 100%);
    pointer-events:none;
    z-index:3;
}

/* Título del slide */
.<?php echo $id; ?>-caption{
    position:absolute;
    bottom:<?php echo $showThumbs ? 'calc(var(--cth) + 12px)' : '12px'; ?>;
    left:0;right:0;
    text-align:center;
    z-index:5;
    padding:0 60px;
}
.<?php echo $id; ?>-caption span{
    font-family:'Metal Mania',cursive,'Oswald',sans-serif;
    font-size:clamp(13px,2vw,18px);
    color:#fff;
    letter-spacing:2px;
    text-shadow:0 0 12px rgba(204,34,0,.8),0 2px 4px rgba(0,0,0,.9);
}

/* Botones nav */
.<?php echo $id; ?>-btn{
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    z-index:10;
    background:rgba(5,0,0,.7);
    border:1px solid var(--cr2);
    color:var(--fire, #CC2200);
    font-size:22px;
    line-height:1;
    width:44px;height:44px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;
    transition:background .2s,border-color .2s,color .2s;
    border-radius:2px;
}
.<?php echo $id; ?>-btn:hover{
    background:var(--cr2);
    border-color:var(--cr);
    color:#fff;
}
.<?php echo $id; ?>-prev{left:10px;}
.<?php echo $id; ?>-next{right:10px;}

/* Botón ampliar */
.<?php echo $id; ?>-zoom{
    position:absolute;
    top:10px;right:10px;
    z-index:10;
    background:rgba(5,0,0,.7);
    border:1px solid var(--cr2);
    color:var(--fire, #CC2200);
    font-size:16px;
    width:36px;height:36px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;
    border-radius:2px;
    transition:background .2s,color .2s;
}
.<?php echo $id; ?>-zoom:hover{background:var(--cr2);color:#fff;}

/* Dots */
.<?php echo $id; ?>-dots{
    display:flex;justify-content:center;gap:6px;
    padding:8px 0 6px;
    background:var(--cdkst);
}
.<?php echo $id; ?>-dot{
    width:8px;height:8px;
    border-radius:50%;
    background:var(--grey-3, #333333);
    border:1px solid var(--wine, #3A0000);
    cursor:pointer;
    transition:background .2s,transform .2s;
}
.<?php echo $id; ?>-dot.active{
    background:var(--cr);
    transform:scale(1.3);
}

/* Tira de miniaturas */
.<?php echo $id; ?>-thumbs{
    display:flex;
    gap:3px;
    background:var(--cdkst);
    padding:4px;
    border-top:2px solid var(--cr2);
    overflow-x:auto;
    scrollbar-width:thin;
    scrollbar-color:var(--cr2) var(--cdk);
    height:var(--cth);
    box-sizing:content-box;
}
.<?php echo $id; ?>-thumb{
    flex:0 0 auto;
    width:<?php echo $showThumbs ? round($altura * 0.18) : '80'; ?>px;
    height:var(--cth);
    overflow:hidden;
    cursor:pointer;
    border:2px solid transparent;
    transition:border-color .2s,opacity .2s;
    opacity:.55;
}
.<?php echo $id; ?>-thumb.active{
    border-color:var(--cr);
    opacity:1;
}
.<?php echo $id; ?>-thumb img{
    width:100%;height:100%;
    object-fit:cover;
    display:block;
    transition:transform .3s;
}
.<?php echo $id; ?>-thumb:hover{ opacity:.85; }
.<?php echo $id; ?>-thumb:hover img{ transform:scale(1.08); }

/* Barra de progreso autoplay */
.<?php echo $id; ?>-bar{
    position:absolute;
    bottom:0;left:0;
    height:2px;
    background:var(--cr);
    z-index:6;
    width:0%;
    transition:none;
}

/* ── LIGHTBOX ── */
.<?php echo $id; ?>-lb{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.92);
    z-index:99999;
    align-items:center;
    justify-content:center;
    flex-direction:column;
}
.<?php echo $id; ?>-lb.open{ display:flex; }

.<?php echo $id; ?>-lb-inner{
    position:relative;
    max-width:95vw;
    max-height:90vh;
    display:flex;
    align-items:center;
    justify-content:center;
}
.<?php echo $id; ?>-lb-inner img{
    max-width:95vw;
    max-height:85vh;
    width:auto;
    height:auto;
    object-fit:contain;
    display:block;
    border:2px solid var(--cr2);
    box-shadow:0 0 60px rgba(139,0,0,.5);
}
.<?php echo $id; ?>-lb-close{
    position:fixed;
    top:16px;right:16px;
    background:var(--cr2);
    border:1px solid var(--cr);
    color:#fff;
    font-size:22px;
    width:42px;height:42px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;
    border-radius:2px;
    z-index:100000;
    transition:background .2s;
}
.<?php echo $id; ?>-lb-close:hover{ background:var(--cr); }

.<?php echo $id; ?>-lb-prev,
.<?php echo $id; ?>-lb-next{
    position:fixed;
    top:50%;transform:translateY(-50%);
    background:rgba(5,0,0,.8);
    border:1px solid var(--cr2);
    color:var(--cr);
    font-size:28px;
    width:52px;height:52px;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;
    border-radius:2px;
    transition:background .2s,color .2s;
    z-index:100000;
}
.<?php echo $id; ?>-lb-prev:hover,
.<?php echo $id; ?>-lb-next:hover{ background:var(--cr2);color:#fff; }
.<?php echo $id; ?>-lb-prev{ left:10px; }
.<?php echo $id; ?>-lb-next{ right:10px; }

.<?php echo $id; ?>-lb-caption{
    font-family:'Metal Mania',cursive,'Oswald',sans-serif;
    color:var(--grey-b, #BBBBBB);
    font-size:14px;
    letter-spacing:2px;
    margin-top:12px;
    text-align:center;
    text-shadow:0 0 8px rgba(204,34,0,.5);
    min-height:20px;
}
.<?php echo $id; ?>-lb-counter{
    font-family:'Oswald',sans-serif;
    color:var(--grey-5, #555555);
    font-size:11px;
    letter-spacing:2px;
    margin-top:4px;
}
</style>

<!-- ══ MÓDULO ══ -->
<div id="<?php echo $id; ?>">

  <!-- CARRUSEL -->
  <div class="<?php echo $id; ?>-wrap">

    <!-- Pista -->
    <div class="<?php echo $id; ?>-track" id="<?php echo $id; ?>-track">
      <?php foreach ($fotos as $i => $foto): ?>
      <div class="<?php echo $id; ?>-slide<?php echo $i === 0 ? ' active' : ''; ?>"
           data-index="<?php echo $i; ?>">
        <img src="<?php echo $foto['src']; ?>"
             alt="<?php echo $foto['titulo'] ?: ('Foto ' . ($i + 1)); ?>"
             loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
      </div>
      <?php endforeach; ?>

      <!-- Overlay degradado -->
      <div class="<?php echo $id; ?>-overlay"></div>

      <!-- Título -->
      <?php if ($showCaption): ?>
      <div class="<?php echo $id; ?>-caption" id="<?php echo $id; ?>-cap">
        <span><?php echo $fotos[0]['titulo']; ?></span>
      </div>
      <?php endif; ?>

      <!-- Barra progreso -->
      <div class="<?php echo $id; ?>-bar" id="<?php echo $id; ?>-bar"></div>

      <!-- Botones nav -->
      <button class="<?php echo $id; ?>-btn <?php echo $id; ?>-prev" aria-label="Anterior">&#8249;</button>
      <button class="<?php echo $id; ?>-btn <?php echo $id; ?>-next" aria-label="Siguiente">&#8250;</button>

      <!-- Botón ampliar -->
      <button class="<?php echo $id; ?>-zoom" id="<?php echo $id; ?>-zoom" aria-label="Ampliar">&#9974;</button>
    </div>

    <!-- Dots indicadores -->
    <div class="<?php echo $id; ?>-dots">
      <?php foreach ($fotos as $i => $foto): ?>
      <div class="<?php echo $id; ?>-dot<?php echo $i === 0 ? ' active' : ''; ?>"
           data-index="<?php echo $i; ?>"></div>
      <?php endforeach; ?>
    </div>

    <!-- Miniaturas -->
    <?php if ($showThumbs): ?>
    <div class="<?php echo $id; ?>-thumbs" id="<?php echo $id; ?>-thumbs">
      <?php foreach ($fotos as $i => $foto): ?>
      <div class="<?php echo $id; ?>-thumb<?php echo $i === 0 ? ' active' : ''; ?>"
           data-index="<?php echo $i; ?>">
        <img src="<?php echo $foto['src']; ?>"
             alt="<?php echo $foto['titulo'] ?: ('Foto ' . ($i + 1)); ?>"
             loading="lazy">
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div><!-- /wrap -->

</div><!-- /módulo -->

<!-- ══ LIGHTBOX ══ -->
<div class="<?php echo $id; ?>-lb" id="<?php echo $id; ?>-lb">
  <button class="<?php echo $id; ?>-lb-close" id="<?php echo $id; ?>-lb-close">&#10005;</button>
  <button class="<?php echo $id; ?>-lb-prev"  id="<?php echo $id; ?>-lb-prev">&#8249;</button>
  <button class="<?php echo $id; ?>-lb-next"  id="<?php echo $id; ?>-lb-next">&#8250;</button>
  <div class="<?php echo $id; ?>-lb-inner">
    <img src="" alt="" id="<?php echo $id; ?>-lb-img">
  </div>
  <div class="<?php echo $id; ?>-lb-caption" id="<?php echo $id; ?>-lb-cap"></div>
  <div class="<?php echo $id; ?>-lb-counter" id="<?php echo $id; ?>-lb-counter"></div>
</div>

<script>
(function(){
  var ID       = '<?php echo $id; ?>';
  var FOTOS    = <?php echo json_encode(array_values($fotos)); ?>;
  var N        = FOTOS.length;
  var INTERVAL = <?php echo $interval; ?>;
  var AUTOPLAY = <?php echo $autoplay; ?> === 1;

  if(N === 0) return;

  var current = 0;
  var timer   = null;
  var barAnim = null;

  /* ── Referencias DOM ── */
  function el(s){ return document.getElementById(ID + s); }
  function qa(s){ return document.getElementById(ID).querySelectorAll('.' + ID + s); }

  var slides  = qa('-slide');
  var dots    = qa('-dot');
  var thumbs  = qa('-thumb');
  var capEl   = el('-cap');
  var barEl   = el('-bar');
  var lbEl    = el('-lb');
  var lbImg   = el('-lb-img');
  var lbCap   = el('-lb-cap');
  var lbCnt   = el('-lb-counter');

  /* ── Ir a slide N ── */
  function goTo(n, fromUser){
    slides[current].classList.remove('active');
    dots[current]  && dots[current].classList.remove('active');
    thumbs[current] && thumbs[current].classList.remove('active');

    current = (n + N) % N;

    slides[current].classList.add('active');
    dots[current]  && dots[current].classList.add('active');
    thumbs[current] && thumbs[current].classList.add('active');

    /* Centrar miniatura activa */
    if(thumbs[current]){
      thumbs[current].scrollIntoView({behavior:'smooth', block:'nearest', inline:'center'});
    }

    /* Caption */
    if(capEl){
      capEl.querySelector('span').textContent = FOTOS[current].titulo || '';
    }

    /* Barra progreso */
    if(barEl && AUTOPLAY){
      barEl.style.transition = 'none';
      barEl.style.width      = '0%';
      void barEl.offsetWidth; /* reflow */
      barEl.style.transition = 'width ' + INTERVAL + 'ms linear';
      barEl.style.width      = '100%';
    }

    /* Reiniciar autoplay si el usuario navegó */
    if(fromUser && AUTOPLAY) startAutoplay();
  }

  /* ── Autoplay ── */
  function startAutoplay(){
    clearInterval(timer);
    timer = setInterval(function(){ goTo(current + 1, false); }, INTERVAL);
    if(barEl){
      barEl.style.transition = 'none';
      barEl.style.width      = '0%';
      void barEl.offsetWidth;
      barEl.style.transition = 'width ' + INTERVAL + 'ms linear';
      barEl.style.width      = '100%';
    }
  }
  function stopAutoplay(){
    clearInterval(timer);
    if(barEl){ barEl.style.transition='none'; barEl.style.width='0%'; }
  }

  /* ── Botones prev / next del carrusel ── */
  var prevBtn = document.querySelector('#' + ID + ' .' + ID + '-prev');
  var nextBtn = document.querySelector('#' + ID + ' .' + ID + '-next');
  if(prevBtn) prevBtn.addEventListener('click', function(){ goTo(current - 1, true); });
  if(nextBtn) nextBtn.addEventListener('click', function(){ goTo(current + 1, true); });

  /* ── Dots ── */
  dots.forEach(function(d){
    d.addEventListener('click', function(){
      goTo(parseInt(this.getAttribute('data-index')), true);
    });
  });

  /* ── Miniaturas ── */
  thumbs.forEach(function(t){
    t.addEventListener('click', function(){
      goTo(parseInt(this.getAttribute('data-index')), true);
    });
  });

  /* ── Click en slide → abrir lightbox ── */
  slides.forEach(function(s){
    s.addEventListener('click', function(){
      openLb(parseInt(this.getAttribute('data-index')));
    });
  });

  /* ── Botón zoom ── */
  var zoomBtn = el('-zoom');
  if(zoomBtn) zoomBtn.addEventListener('click', function(){ openLb(current); });

  /* ── Pausa autoplay en hover ── */
  var track = el('-track');
  if(track){
    track.addEventListener('mouseenter', stopAutoplay);
    track.addEventListener('mouseleave', function(){
      if(AUTOPLAY) startAutoplay();
    });
  }

  /* ══════════ LIGHTBOX ══════════ */
  var lbCurrent = 0;

  function openLb(idx){
    lbCurrent = idx;
    updateLb();
    lbEl.classList.add('open');
    document.body.style.overflow = 'hidden';
    stopAutoplay();
  }
  function closeLb(){
    lbEl.classList.remove('open');
    document.body.style.overflow = '';
    if(AUTOPLAY) startAutoplay();
  }
  function updateLb(){
    var f = FOTOS[lbCurrent];
    lbImg.src = f.src;
    lbImg.alt = f.titulo || ('Foto ' + (lbCurrent + 1));
    if(lbCap) lbCap.textContent  = f.titulo || '';
    if(lbCnt) lbCnt.textContent  = (lbCurrent + 1) + ' / ' + N;
  }
  function lbGo(dir){
    lbCurrent = (lbCurrent + dir + N) % N;
    updateLb();
  }

  el('-lb-close').addEventListener('click', closeLb);
  el('-lb-prev') .addEventListener('click', function(){ lbGo(-1); });
  el('-lb-next') .addEventListener('click', function(){ lbGo(1);  });

  /* Cerrar al click en overlay */
  lbEl.addEventListener('click', function(e){
    if(e.target === lbEl) closeLb();
  });

  /* Teclado */
  document.addEventListener('keydown', function(e){
    if(!lbEl.classList.contains('open')) return;
    if(e.key === 'Escape')     closeLb();
    if(e.key === 'ArrowLeft')  lbGo(-1);
    if(e.key === 'ArrowRight') lbGo(1);
  });

  /* ══════════ SWIPE (móvil) ══════════ */
  function addSwipe(target, onLeft, onRight){
    var sx = 0;
    target.addEventListener('touchstart', function(e){ sx = e.touches[0].clientX; }, {passive:true});
    target.addEventListener('touchend', function(e){
      var dx = e.changedTouches[0].clientX - sx;
      if(Math.abs(dx) > 40){
        if(dx < 0) onLeft();
        else        onRight();
      }
    }, {passive:true});
  }

  if(track)  addSwipe(track, function(){ goTo(current+1,true); }, function(){ goTo(current-1,true); });
  if(lbEl)   addSwipe(lbEl,  function(){ lbGo(1); },            function(){ lbGo(-1); });

  /* ══════════ INICIO ══════════ */
  goTo(0, false);
  if(AUTOPLAY) startAutoplay();

})();
</script>
