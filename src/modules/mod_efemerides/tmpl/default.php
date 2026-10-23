<?php defined('_JEXEC') or die;

$mostrarArtista  = (bool)$params->get('mostrar_artista', 1);
$mostrarDesc     = (bool)$params->get('mostrar_descripcion', 1);
$mostrarCat      = (bool)$params->get('mostrar_categoria', 1);
$mostrarDetalles = (bool)$params->get('mostrar_detalles', 1);
$sufijo          = (string)$params->get('moduleclass_sfx', '');

$hoy        = new DateTime('today');
$anioActual = (int)$hoy->format('Y');
$dia        = $hoy->format('j');
$meses      = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$mesNombre  = $meses[(int)$hoy->format('n') - 1];

$catClass = static function(string $n): string {
    $n = mb_strtolower(trim($n));
    if (strpos($n,'conci')  !== false) return 'concierto';
    if (strpos($n,'naci')   !== false) return 'nacimiento';
    if (strpos($n,'defun')  !== false || strpos($n,'fallec') !== false) return 'defuncion';
    if (strpos($n,'lanz')   !== false || strpos($n,'disco')  !== false) return 'disco';
    return 'otros';
};

$catInfo = static function(string $clase): array {
    $map = [
        'concierto'  => ['#d4641a','Concierto','&#127928;'],
        'nacimiento' => ['#4caf50','Nacimiento','&#127981;'],
        'defuncion'  => ['#cc4444','Defunción','&#10013;'],
        'disco'      => ['#5ba3d9','Lanzamiento','&#128191;'],
        'otros'      => ['#888888','Efeméride','&#9733;'],
    ];
    return $map[$clase] ?? $map['otros'];
};

$jsonItems   = [];
$musicosFoto = $helper->getMusicosFoto();
$bandasFoto  = $helper->getBandasFoto();
$baseUrl     = rtrim(\Joomla\CMS\Uri\Uri::root(), '/');

foreach ($efemerides as $item) {
    $fv   = new DateTime((string)$item->fecha_evento);
    $cc   = $catClass((string)($item->categoria_nombre ?? ''));
    [$col,$catLabel,$ico] = $catInfo($cc);
    $hace = $anioActual - (int)$fv->format('Y');
    $jsonItems[] = [
        'titulo'   => (string)$item->titulo,
        'artista'  => (string)($item->artista ?? ''),
        'artFoto'  => (function() use ($item, $musicosFoto, $bandasFoto, $baseUrl) {
            $k = mb_strtolower(trim((string)($item->artista ?? '')));
            $f = $musicosFoto[$k]['foto'] ?? ($bandasFoto[$k]['foto'] ?? '');
            if (!$f) return '';
            return (strpos($f,'http')===0) ? $f : $baseUrl.'/'.$f;
        })(),
        'artUrl'   => (function() use ($item, $musicosFoto, $bandasFoto) {
            $k = mb_strtolower(trim((string)($item->artista ?? '')));
            if (!empty($musicosFoto[$k]['url'])) return $musicosFoto[$k]['url'];
            if (!empty($bandasFoto[$k]['url']))  return $bandasFoto[$k]['url'];
            return '';
        })(),
        'cat'      => $catLabel,
        'catColor' => $col,
        'ico'      => $ico,
        'anio'     => $fv->format('Y'),
        'fecha'    => $fv->format('d/m'),
        'hace'     => $hace,
        'desc'     => (string)($item->descripcion ?? ''),
        'detalle'  => (string)($item->detalle ?? ''),
        'disco'    => (string)($item->nombre_disco ?? ''),
        'lugar'    => (string)($item->lugar_concierto ?? ''),
        'asist'    => (int)($item->asistentes ?? 0),
    ];
}
$jd  = json_encode($jsonItems, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE);
$uid = 'em'.substr(md5(uniqid('',true)),0,6);
$total = count($jsonItems);

static $mefCss = false;
?>
<?php if (!$mefCss): $mefCss = true; ?>
<style>
/* ── Wrapper ─────────────────────────────────────────────────── */
.hmb-em-wrap{font-family:inherit;background:var(--dark, #0A0A0A);border:1px solid var(--steel, #1A1A1A);border-top:3px solid var(--fire, #CC2200);max-width:1200px;margin:0 auto;user-select:none}

/* ── Cabecera ────────────────────────────────────────────────── */
.hmb-em-head{display:flex;align-items:center;gap:12px;padding:10px 16px;background:var(--darker, #050505);border-bottom:1px solid var(--steel, #1A1A1A)}
.hmb-em-dia{font-size:2.6rem;font-weight:900;color:var(--fire, #CC2200);line-height:1}
.hmb-em-metxt{flex:1}
.hmb-em-mes{font-size:.95rem;font-weight:800;color:#fff;letter-spacing:.06em;line-height:1.2;text-transform:uppercase}
.hmb-em-sub{font-size:.58rem;color:var(--grey-4, #444444);letter-spacing:.12em;text-transform:uppercase;margin-top:2px}
.hmb-em-count{font-size:.68rem;background:var(--wine-dark, #1A0000);color:var(--fire, #CC2200);padding:3px 10px;border-radius:2px;font-weight:700;letter-spacing:.06em;white-space:nowrap}

/* ── Flechas ─────────────────────────────────────────────────── */
.hmb-em-arrow{
  position:absolute;top:50%;transform:translateY(-50%);
  width:44px;height:80px;
  background:var(--dark, #0A0A0A);border:1px solid var(--grey-2, #2A2A2A);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;z-index:10;color:var(--grey-5, #555555);font-size:1.5rem;
  outline:none;transition:background .18s,border-color .18s,color .18s,box-shadow .18s;
}
.hmb-em-arrow:hover{background:var(--wine-dark, #1A0000);border-color:var(--fire, #CC2200);color:var(--fire, #CC2200);box-shadow:0 0 14px rgba(204,0,0,.4)}
.hmb-em-arrow:active{transform:translateY(-50%) scale(.93)}
.hmb-em-prev{left:0;border-left:none}
.hmb-em-next{right:0;border-right:none}

/* ── Stage (zona entre flechas) ─────────────────────────────── */
.hmb-em-slider{position:relative}
.hmb-em-stage{margin:0 44px;padding:18px 20px 14px;min-height:160px;display:flex;flex-direction:column;gap:0}

/* Línea de color de categoría arriba */
.hmb-em-cat-bar{height:3px;margin:-18px -20px 14px;background:var(--fire, #CC2200)}

/* Año grande + fecha */
.hmb-em-year-row{display:flex;align-items:baseline;gap:10px;margin-bottom:4px}
.hmb-em-anio{font-size:2rem;font-weight:900;color:var(--fire, #CC2200);line-height:1}
.hmb-em-fecha{font-size:.75rem;color:var(--grey-5, #555555);font-weight:600}
.hmb-em-hace{font-size:.7rem;color:var(--grey-4, #444444);font-style:italic}

/* Badge categoría */
.hmb-em-badge{display:inline-flex;align-items:center;gap:5px;font-size:.62rem;font-weight:700;padding:2px 9px;border-radius:2px;letter-spacing:.06em;text-transform:uppercase;margin-bottom:10px}

/* Título */
.hmb-em-titulo{font-size:1.1rem;font-weight:800;color:#f0f0f0;line-height:1.35;margin-bottom:8px}

/* Artista */
.hmb-em-artista{font-size:.82rem;color:var(--gold-light, #DAA520);font-weight:700;margin-bottom:8px}

/* Descripción */
.hmb-em-desc{font-size:.84rem;color:var(--grey-8, #888888);line-height:1.7;border-left:3px solid var(--steel, #1A1A1A);padding-left:12px;margin-bottom:8px}

/* Detalles extra */
.hmb-em-extras{display:flex;flex-wrap:wrap;gap:8px;font-size:.72rem;color:var(--grey-5, #555555);border-top:1px solid var(--mid, #111111);padding-top:8px;margin-top:4px}
.hmb-em-extra{display:flex;align-items:center;gap:4px}

/* Transición */
.hmb-em-fade{opacity:0;transition:opacity .22s}
.hmb-em-in{opacity:1;transition:opacity .22s}

/* ── Pie: puntos ─────────────────────────────────────────────── */
.hmb-em-footer{display:flex;align-items:center;justify-content:center;gap:5px;padding:8px 12px;background:var(--darker, #050505);border-top:1px solid var(--mid, #111111)}
.hmb-em-dot{width:7px;height:7px;border-radius:50%;background:var(--grey-2, #2A2A2A);border:none;padding:0;cursor:pointer;outline:none;transition:all .2s}
.hmb-em-dot.on{background:var(--fire, #CC2200);transform:scale(1.4)}
.hmb-em-dot:hover:not(.on){background:var(--fire, #CC2200);opacity:.5}

/* ── Vacío ───────────────────────────────────────────────────── */
.hmb-em-vacio{padding:30px;text-align:center;color:var(--grey-5, #555555);font-size:.85rem;font-style:italic}

/* Responsive */
@media(max-width:500px){
  .hmb-em-arrow{width:34px}
  .hmb-em-stage{margin:0 34px;padding:14px 12px}
  .hmb-em-anio{font-size:1.6rem}
  .hmb-em-titulo{font-size:1rem}
}
</style>
<?php endif; ?>

<div class="hmb-em-wrap <?php echo htmlspecialchars($sufijo); ?>" id="<?php echo $uid; ?>">

  <!-- Cabecera con fecha de hoy -->
  <div class="hmb-em-head">
    <div class="hmb-em-dia"><?php echo $dia; ?></div>
    <div class="hmb-em-metxt">
      <div class="hmb-em-mes"><?php echo htmlspecialchars($mesNombre); ?></div>
      <div class="hmb-em-sub">Efemérides del día</div>
    </div>
    <?php if ($total > 0): ?>
    <div class="hmb-em-count" id="<?php echo $uid; ?>-count">1 / <?php echo $total; ?></div>
    <?php endif; ?>
  </div>

  <?php if ($total === 0): ?>
  <div class="hmb-em-vacio">No hay efemérides para hoy.</div>
  <?php else: ?>

  <div class="hmb-em-slider">

    <!-- Flecha izquierda -->
    <button class="hmb-em-arrow hmb-em-prev" id="<?php echo $uid; ?>-prev" aria-label="Anterior">&#10094;</button>

    <!-- Stage -->
    <div class="hmb-em-stage hmb-em-in" id="<?php echo $uid; ?>-stage">
      <div class="hmb-em-cat-bar" id="<?php echo $uid; ?>-bar"></div>
      <div class="hmb-em-year-row">
        <span class="hmb-em-anio"  id="<?php echo $uid; ?>-anio"></span>
        <span class="hmb-em-fecha" id="<?php echo $uid; ?>-fecha"></span>
        <span class="hmb-em-hace"  id="<?php echo $uid; ?>-hace"></span>
      </div>
      <div id="<?php echo $uid; ?>-badge" class="hmb-em-badge"></div>
      <div class="hmb-em-titulo"  id="<?php echo $uid; ?>-tit"></div>
      <div class="hmb-em-artista" id="<?php echo $uid; ?>-art" style="display:none"></div>
      <div class="hmb-em-desc"    id="<?php echo $uid; ?>-desc" style="display:none"></div>
      <div class="hmb-em-extras"  id="<?php echo $uid; ?>-ext" style="display:none"></div>
    </div>

    <!-- Flecha derecha -->
    <button class="hmb-em-arrow hmb-em-next" id="<?php echo $uid; ?>-next" aria-label="Siguiente">&#10095;</button>

  </div>

  <!-- Puntos -->
  <div class="hmb-em-footer" id="<?php echo $uid; ?>-dots"></div>

  <?php endif; ?>
</div>

<?php if ($total > 0): ?>
<script>
(function(){
  var DATA    = <?php echo $jd; ?>;
  var uid     = '<?php echo $uid; ?>';
  var stage   = document.getElementById(uid+'-stage');
  var bar     = document.getElementById(uid+'-bar');
  var anioEl  = document.getElementById(uid+'-anio');
  var fechaEl = document.getElementById(uid+'-fecha');
  var haceEl  = document.getElementById(uid+'-hace');
  var badgeEl = document.getElementById(uid+'-badge');
  var titEl   = document.getElementById(uid+'-tit');
  var artEl   = document.getElementById(uid+'-art');
  var descEl  = document.getElementById(uid+'-desc');
  var extEl   = document.getElementById(uid+'-ext');
  var cntEl   = document.getElementById(uid+'-count');
  var dotsEl  = document.getElementById(uid+'-dots');
  var btnPrev = document.getElementById(uid+'-prev');
  var btnNext = document.getElementById(uid+'-next');
  var current = 0;
  var dots    = [];
  var maxDots = Math.min(DATA.length, 12);
  var showArt  = <?php echo $mostrarArtista  ? 'true':'false'; ?>;
  var showDesc = <?php echo $mostrarDesc     ? 'true':'false'; ?>;
  var showDet  = <?php echo $mostrarDetalles ? 'true':'false'; ?>;

  // Crear puntos
  for (var i = 0; i < maxDots; i++) {
    (function(idx){
      var d = document.createElement('button');
      d.className = 'hmb-em-dot';
      d.setAttribute('aria-label','Efeméride '+(idx+1));
      d.addEventListener('click',function(){ goTo(idx); });
      dotsEl.appendChild(d);
      dots.push(d);
    })(i);
  }

  function updateDots(idx) {
    var di = DATA.length <= 12 ? idx : Math.round(idx/(DATA.length-1)*(maxDots-1));
    dots.forEach(function(d,i){ d.classList.toggle('on', i===di); });
  }

  function show(el, txt) {
    if (txt && txt.trim()) { el.innerHTML = txt; el.style.display=''; }
    else el.style.display = 'none';
  }

  function render(idx) {
    var d = DATA[idx];
    // Color
    bar.style.background = d.catColor;
    // Año
    anioEl.textContent  = d.anio;
    anioEl.style.color  = d.catColor;
    fechaEl.textContent = d.fecha;
    haceEl.textContent  = d.hace > 0 ? 'hace '+d.hace+' año'+(d.hace===1?'':'s') : 'este año';
    // Badge
    badgeEl.innerHTML   = d.ico + ' ' + d.cat;
    badgeEl.style.background = d.catColor + '22';
    badgeEl.style.color      = d.catColor;
    badgeEl.style.border     = '1px solid ' + d.catColor + '44';
    // Título
    titEl.textContent = d.titulo;
    // Artista con foto
    if (showArt && d.artista) {
        var artHtml = '';
        if (d.artFoto) {
            var imgTag = '<img src="'+d.artFoto+'" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid #444;margin-right:8px;vertical-align:middle" alt="">';
            artHtml = d.artUrl
                ? '<a href="'+d.artUrl+'" style="color:inherit;text-decoration:none">'+imgTag+d.artista+'</a>'
                : imgTag+d.artista;
        } else {
            artHtml = d.artUrl
                ? '<a href="'+d.artUrl+'" style="color:inherit;text-decoration:none">&#127928; '+d.artista+'</a>'
                : '&#127928; '+d.artista;
        }
        show(artEl, artHtml);
    } else {
        artEl.style.display='none';
    }
    // Descripción
    showDesc ? show(descEl, d.desc) : (descEl.style.display='none');
    // Extras
    if (showDet) {
      var extras = [];
      if (d.disco) extras.push('<span class="hmb-em-extra">&#128191; <em>' + d.disco + '</em></span>');
      if (d.lugar) extras.push('<span class="hmb-em-extra">&#128205; ' + d.lugar + '</span>');
      if (d.asist > 0) extras.push('<span class="hmb-em-extra">&#128101; ' + d.asist.toLocaleString() + ' asistentes</span>');
      if (extras.length) { extEl.innerHTML = extras.join(''); extEl.style.display='flex'; }
      else extEl.style.display = 'none';
    } else extEl.style.display='none';
    // Contador y puntos
    if (cntEl) cntEl.textContent = (idx+1) + ' / ' + DATA.length;
    updateDots(idx);
  }

  function goTo(idx, animate) {
    if (idx < 0) idx = DATA.length - 1;
    if (idx >= DATA.length) idx = 0;
    if (animate === false) { current = idx; render(idx); return; }
    stage.classList.remove('hmb-em-in');
    stage.classList.add('hmb-em-fade');
    setTimeout(function(){
      current = idx;
      render(idx);
      stage.classList.remove('hmb-em-fade');
      stage.classList.add('hmb-em-in');
    }, 220);
  }

  btnPrev.addEventListener('click', function(){ goTo(current - 1); });
  btnNext.addEventListener('click', function(){ goTo(current + 1); });

  // Swipe táctil
  var tx = 0;
  stage.addEventListener('touchstart', function(e){ tx = e.touches[0].clientX; }, {passive:true});
  stage.addEventListener('touchend',   function(e){
    var dx = e.changedTouches[0].clientX - tx;
    if (Math.abs(dx) > 40) goTo(dx < 0 ? current+1 : current-1);
  }, {passive:true});

  // Teclado
  document.getElementById(uid).addEventListener('keydown', function(e){
    if (e.key==='ArrowLeft')  goTo(current-1);
    if (e.key==='ArrowRight') goTo(current+1);
  });

  goTo(0, false);
})();
</script>
<?php endif; ?>
