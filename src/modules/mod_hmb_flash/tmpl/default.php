<?php defined('_JEXEC') or die;

// Anchos por tamaño
$anchos = ['small' => 320, 'medium' => 440, 'large' => 580];
$ancho  = $anchos[$tamano] ?? 440;

// Tamaño de fuente del texto
$fSizes = ['small' => '13px', 'medium' => '15px', 'large' => '17px'];
$fSize  = $fSizes[$tamano] ?? '15px';

// Altura máxima imagen
$imgH = ['small' => '160px', 'medium' => '220px', 'large' => '300px'];
$imgHeight = $imgH[$tamano] ?? '220px';
?>
<style>
/* ══════════════════════════════════
   HMB FLASH — <?php echo $mid; ?>
══════════════════════════════════ */
#<?php echo $mid; ?>-overlay{
    display:none;
    position:fixed;inset:0;
    background:rgba(0,0,0,.72);
    z-index:98000;
    animation:hfbo .35s ease;
}
#<?php echo $mid; ?>-overlay.open{ display:block; }
@keyframes hfbo{ from{opacity:0} to{opacity:1} }

#<?php echo $mid; ?>{
    position:fixed;
    z-index:98001;
    width:<?php echo $ancho; ?>px;
    max-width:calc(100vw - 24px);
    background:#060000;
    border:2px solid var(--blood, #8B0000);
    border-top:4px solid var(--fire, #CC2200);
    box-shadow:0 0 60px rgba(139,0,0,.5), 0 20px 60px rgba(0,0,0,.9);
    display:none;
    flex-direction:column;
    overflow:hidden;
    border-radius:3px;

    /* Desktop: lateral */
    <?php if($posicion === 'right'): ?>
    top:50%;right:20px;transform:translateY(-50%) translateX(30px);
    <?php else: ?>
    top:50%;left:20px;transform:translateY(-50%) translateX(-30px);
    <?php endif; ?>

    transition:opacity .4s, transform .4s;
    opacity:0;
}
#<?php echo $mid; ?>.open{
    display:flex;
    opacity:1;
    transform:translateY(-50%) translateX(0) !important;
}

/* Móvil: centrado */
@media(max-width:768px){
    #<?php echo $mid; ?>{
        top:50%;left:50%;
        transform:translate(-50%,-50%) scale(.96) !important;
        right:auto;
    }
    #<?php echo $mid; ?>.open{
        transform:translate(-50%,-50%) scale(1) !important;
    }
}

/* ── Cabecera ── */
.<?php echo $mid; ?>-head{
    background:linear-gradient(135deg,var(--wine-dark, #1A0000),var(--wine-black, #0A0000));
    border-bottom:2px solid var(--wine, #3A0000);
    padding:14px 16px 12px;
    display:flex;align-items:flex-start;gap:10px;
    position:relative;
}
.<?php echo $mid; ?>-badge{
    flex-shrink:0;
    background:linear-gradient(135deg,var(--fire, #CC2200),var(--blood, #8B0000));
    color:#fff;
    font-family:'Oswald',sans-serif;
    font-size:8px;font-weight:700;letter-spacing:2px;
    padding:3px 8px;
    display:flex;align-items:center;gap:4px;
    margin-top:2px;
}
.<?php echo $mid; ?>-badge-dot{
    width:5px;height:5px;border-radius:50%;
    background:#fff;
    animation:hfblink 1s ease-in-out infinite;
}
@keyframes hfblink{0%,100%{opacity:1;}50%{opacity:.3;}}
.<?php echo $mid; ?>-titulo{
    font-family:'Metal Mania',cursive,'Oswald',sans-serif;
    font-size:clamp(16px,3vw,22px);
    color:#fff;
    line-height:1.1;
    letter-spacing:1px;
    flex:1;
    text-shadow:0 0 20px rgba(204,34,0,.5);
    margin:0;
}

/* Botón cerrar */
.<?php echo $mid; ?>-close{
    position:absolute;
    top:10px;right:10px;
    background:var(--wine-dark, #1A0000);
    border:1px solid var(--blood, #8B0000);
    color:var(--fire, #CC2200);
    width:32px;height:32px;
    border-radius:3px;
    font-size:18px;line-height:1;
    cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    transition:background .2s,color .2s;
    flex-shrink:0;
}
.<?php echo $mid; ?>-close:hover{ background:var(--blood, #8B0000);color:#fff; }

/* ── Imagen ── */
.<?php echo $mid; ?>-img{
    width:100%;
    max-height:<?php echo $imgHeight; ?>;
    overflow:hidden;
    border-bottom:2px solid var(--wine-dark, #1A0000);
    position:relative;
}
.<?php echo $mid; ?>-img img{
    width:100%;height:100%;
    object-fit:cover;display:block;
    filter:brightness(.9) contrast(1.05);
}
.<?php echo $mid; ?>-img::after{
    content:'';
    position:absolute;bottom:0;left:0;right:0;height:40%;
    background:linear-gradient(to top,#060000,transparent);
    pointer-events:none;
}

/* ── Texto ── */
.<?php echo $mid; ?>-body{
    padding:16px 18px;
    font-family:'Oswald',sans-serif;
    font-size:<?php echo $fSize; ?>;
    color:rgba(220,220,220,.9);
    line-height:1.7;
    letter-spacing:.5px;
}

/* ── Barra autoclose ── */
.<?php echo $mid; ?>-bar-wrap{
    background:var(--wine-black, #0A0000);
    border-top:1px solid var(--wine-dark, #1A0000);
    padding:8px 14px;
    display:none;
    align-items:center;
    gap:10px;
}
.<?php echo $mid; ?>-bar-wrap.visible{ display:flex; }
.<?php echo $mid; ?>-bar-bg{
    flex:1;height:3px;
    background:var(--wine-dark, #1A0000);border-radius:2px;overflow:hidden;
}
.<?php echo $mid; ?>-bar-fill{
    height:100%;width:100%;
    background:linear-gradient(90deg,var(--fire, #CC2200),var(--ember, #FF4500));
    transform-origin:left;
    transition:transform linear;
}
.<?php echo $mid; ?>-bar-txt{
    font-family:'Oswald',sans-serif;
    font-size:10px;letter-spacing:1px;color:var(--grey-4, #444444);
    white-space:nowrap;flex-shrink:0;
}

/* ── Esquinas decorativas ── */
#<?php echo $mid; ?>::before,#<?php echo $mid; ?>::after{
    content:'';position:absolute;
    width:12px;height:12px;
    border-color:var(--fire, #CC2200);border-style:solid;
}
#<?php echo $mid; ?>::before{ top:4px;left:4px;border-width:2px 0 0 2px; }
#<?php echo $mid; ?>::after{  bottom:4px;right:4px;border-width:0 2px 2px 0; }
</style>

<?php if ($overlay): ?>
<div id="<?php echo $mid; ?>-overlay"></div>
<?php endif; ?>

<div id="<?php echo $mid; ?>" role="dialog" aria-modal="true" aria-label="<?php echo $titulo ?: 'Notificación'; ?>">

    <!-- Cabecera -->
    <div class="<?php echo $mid; ?>-head">
        <div class="<?php echo $mid; ?>-badge">
            <span class="<?php echo $mid; ?>-badge-dot"></span>
            URGENTE
        </div>
        <?php if ($titulo): ?>
        <h2 class="<?php echo $mid; ?>-titulo"><?php echo $titulo; ?></h2>
        <?php endif; ?>
        <button class="<?php echo $mid; ?>-close" id="<?php echo $mid; ?>-close" aria-label="Cerrar">&#10005;</button>
    </div>

    <!-- Imagen -->
    <?php if ($imgUrl): ?>
    <div class="<?php echo $mid; ?>-img">
        <?php if ($link): ?>
        <a href="<?php echo $link; ?>" style="display:block;line-height:0">
        <?php endif; ?>
        <img src="<?php echo htmlspecialchars($imgUrl, ENT_QUOTES); ?>"
             alt="<?php echo $titulo; ?>"
             loading="eager"
             style="<?php echo $link ? 'cursor:pointer' : ''; ?>">
        <?php if ($link): ?>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Texto -->
    <?php if ($texto): ?>
    <div class="<?php echo $mid; ?>-body"><?php echo $texto; ?></div>
    <?php endif; ?>

    <!-- Barra autoclose -->
    <div class="<?php echo $mid; ?>-bar-wrap<?php echo $autoclose ? ' visible' : ''; ?>"
         id="<?php echo $mid; ?>-barwrap">
        <div class="<?php echo $mid; ?>-bar-bg">
            <div class="<?php echo $mid; ?>-bar-fill" id="<?php echo $mid; ?>-bar"></div>
        </div>
        <span class="<?php echo $mid; ?>-bar-txt" id="<?php echo $mid; ?>-txt"></span>
    </div>

</div>

<script>
(function(){
  var MID      = '<?php echo $mid; ?>';
  var HASH     = '<?php echo $hash; ?>';
  var SESION   = <?php echo $sesion ? 'true' : 'false'; ?>;
  var AUTOCLOSE= <?php echo $autoclose ? 'true' : 'false'; ?>;
  var SECS     = <?php echo $autoclose_s; ?>;
  var OVERLAY  = <?php echo $overlay ? 'true' : 'false'; ?>;
  var KEY      = 'hmb_flash_' + HASH;

  var box      = document.getElementById(MID);
  var closeBtn = document.getElementById(MID+'-close');
  var overlay  = OVERLAY ? document.getElementById(MID+'-overlay') : null;
  var barFill  = document.getElementById(MID+'-bar');
  var barTxt   = document.getElementById(MID+'-txt');
  var timer    = null;

  /* Comprobar si ya fue cerrado en esta sesión */
  if(SESION && sessionStorage.getItem(KEY) === 'closed'){
    return;
  }

  /* Abrir con pequeño delay para que la animación se vea */
  setTimeout(open, 400);

  function open(){
    if(overlay) overlay.classList.add('open');
    box.classList.add('open');
    document.body.style.overflow = OVERLAY ? 'hidden' : '';
    if(AUTOCLOSE) startCountdown();
  }

  function close(){
    box.classList.remove('open');
    if(overlay) overlay.classList.remove('open');
    document.body.style.overflow = '';
    clearInterval(timer);
    if(SESION) sessionStorage.setItem(KEY, 'closed');
  }

  /* Cerrar al pulsar botón */
  closeBtn.addEventListener('click', close);

  /* Cerrar con ESC */
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') close();
  });

  /* Countdown */
  function startCountdown(){
    var remaining = SECS;
    if(barFill){
      barFill.style.transition = 'none';
      barFill.style.transform  = 'scaleX(1)';
      void barFill.offsetWidth;
      barFill.style.transition = 'transform ' + SECS + 's linear';
      barFill.style.transform  = 'scaleX(0)';
    }
    updateTxt(remaining);
    timer = setInterval(function(){
      remaining--;
      updateTxt(remaining);
      if(remaining <= 0){
        clearInterval(timer);
        close();
      }
    }, 1000);
  }

  function updateTxt(s){
    if(barTxt) barTxt.textContent = s + 's';
  }

})();
</script>
