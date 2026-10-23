<?php defined('_JEXEC') or die;
// Helpers para enlazar artistas/bandas a sus fichas
$getArtFoto = static function(string $nombre, array $mFoto, array $bFoto): string {
    $key = mb_strtolower(trim($nombre));
    if (!empty($mFoto[$key])) return (string)$mFoto[$key];
    if (!empty($bFoto[$key])) return (string)$bFoto[$key];
    return '';
};
$_mBase = rtrim($this->musicoBase, '/');
$_bBase = rtrim($this->bandaBase, '/');
$getMusicoUrl = static function(string $nombre, array $map) use ($_mBase): string {
    $id = $map[mb_strtolower(trim($nombre))] ?? null;
    return $id ? ($_mBase.'?view=musico&id='.$id) : '';
};
$getBandaUrl = static function(string $nombre, array $map) use ($_bBase): string {
    $id = $map[mb_strtolower(trim($nombre))] ?? null;
    return $id ? ($_bBase.'?view=banda&id='.$id) : '';
};
use Joomla\CMS\Uri\Uri;

$hoy        = new DateTime('today');
$meses      = ['enero','febrero','marzo','abril','mayo','junio',
               'julio','agosto','septiembre','octubre','noviembre','diciembre'];
$mesNombre  = $meses[(int)$hoy->format('n') - 1];
$anioActual = (int)$hoy->format('Y');
$currentUrl = Uri::current();

$catInfo = static function(string $nombre): array {
    $n = mb_strtolower(trim($nombre));
    if (strpos($n,'conci')  !== false) return ['concierto',  '#d4641a','#2a1200','#6e3a0d'];
    if (strpos($n,'naci')   !== false) return ['nacimiento', '#4caf50','#0a1f0a','#0d3a0d'];
    if (strpos($n,'defun')  !== false ||
        strpos($n,'fallec') !== false) return ['defuncion',  '#cc4444','#1f0a0a','#660000'];
    if (strpos($n,'lanz')   !== false ||
        strpos($n,'disco')  !== false) return ['disco',      '#5ba3d9','#06122a','#0d3a6e'];
    return ['otros','#888888','#1a1a1a','#333333'];
};

// JSON para modal
$jsonItems = [];
foreach ($this->efemeridesHoy as $i => $item) {
    $fv = new DateTime((string)$item->fecha_evento);
    list($cc,$color,$bg) = $catInfo((string)($item->categoria_nombre ?? ''));
    $jsonItems[] = [
        'titulo'   => (string)$item->titulo,
        'artista'  => (string)($item->artista ?? ''),
        'cat'      => (string)($item->categoria_nombre ?? ''),
        'catColor' => $color, 'catBg' => $bg,
        'anio'     => $fv->format('Y'),
        'fecha'    => $fv->format('d/m/Y'),
        'hace'     => $anioActual - (int)$fv->format('Y'),
        'desc'     => (string)($item->descripcion ?? ''),
        'detalle'  => (string)($item->detalle ?? ''),
        'disco'    => (string)($item->nombre_disco ?? ''),
        'lugar'    => (string)($item->lugar_concierto ?? ''),
        'asist'    => (int)($item->asistentes ?? 0),
        'diff'     => isset($item->_diff_dias) ? (int)$item->_diff_dias : 0,
        'ocurr'    => isset($item->_ocurrencia) ? (string)$item->_ocurrencia : $fv->format('d/m'),
    ];
}
$jsonData = json_encode($jsonItems, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE);
?>

<!-- MODAL -->
<div id="hmb-ov" class="hmb-ov" role="dialog" aria-modal="true" aria-labelledby="hmb-mod-tit">
  <div class="hmb-modal" id="hmb-modal">
    <button class="hmb-modal-x" id="hmb-modal-x" aria-label="Cerrar">&times;</button>
    <div class="hmb-mod-top">
      <span class="hmb-mod-anio" id="hmb-mod-anio"></span>
      <span class="hmb-mod-ftxt" id="hmb-mod-ftxt"></span>
    </div>
    <span class="hmb-mod-badge" id="hmb-mod-badge"></span>
    <h2 class="hmb-mod-tit" id="hmb-mod-tit"></h2>
    <p class="hmb-mod-art" id="hmb-mod-art"></p>
    <div class="hmb-mod-sep"></div>
    <p class="hmb-mod-desc" id="hmb-mod-desc"></p>
    <div id="hmb-mod-ext"></div>
    <div class="hmb-mod-det" id="hmb-mod-det"></div>
  </div>
</div>

<div class="hmb-ef">

  <!-- HERO -->
  <div class="hmb-ef-hero">
    <div class="hmb-ef-hero-l">
      <span class="hmb-ef-hero-d"><?php echo $hoy->format('d'); ?></span>
      <div class="hmb-ef-hero-r">
        <span class="hmb-ef-hero-m"><?php echo strtoupper($mesNombre); ?></span>
        <span class="hmb-ef-hero-s">EN LA HISTORIA DEL ROCK &amp; METAL</span>
      </div>
    </div>
    <?php if (!empty($this->efemeridesHoy)): ?>
    <div class="hmb-ef-hero-c">
      <span class="hmb-ef-hero-cn"><?php echo count($this->efemeridesHoy); ?></span>
      <span class="hmb-ef-hero-ct">ef&eacute;merides<?php echo !$this->esModoHoy ? ' próximas' : ''; ?></span>
    </div>
    <?php endif; ?>
  </div>

  <!-- AVISO MODO PROXIMIDAD -->
  <?php if (!$this->esModoHoy && !empty($this->efemeridesHoy)): ?>
  <div class="hmb-ef-aviso">
    &#128197; No hay ef&eacute;merides exactas para el <strong><?php echo $hoy->format('d/m'); ?></strong>.
    Mostrando las m&aacute;s cercanas en el calendario.
  </div>
  <?php endif; ?>

  <!-- FILTROS -->
  <nav class="hmb-ef-nav">
    <a href="<?php echo htmlspecialchars($currentUrl); ?>"
       class="hmb-ef-nav-btn<?php echo $this->catFiltro===0?' hmb-ef-nav-on':''; ?>">Todos</a>
    <?php foreach ($this->categorias as $cat):
      list($cc,$color,$bg,$brd) = $catInfo((string)$cat->name);
      $on = $this->catFiltro === (int)$cat->id;
    ?>
    <a href="<?php echo htmlspecialchars($currentUrl); ?>?cat=<?php echo (int)$cat->id; ?>"
       class="hmb-ef-nav-btn<?php echo $on?' hmb-ef-nav-on':''; ?>"
       style="<?php echo $on?"background:{$bg};color:{$color};border-color:{$brd}":''; ?>">
      <?php echo htmlspecialchars((string)$cat->name); ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- TARJETAS -->
  <?php if (empty($this->efemeridesHoy)): ?>
  <div class="hmb-ef-vacio">
    <div style="font-size:2rem;margin-bottom:10px">&#129304;</div>
    <p>No hay ef&eacute;merides disponibles<?php echo $this->catFiltro>0?' en esta categor&iacute;a':''; ?>.</p>
  </div>
  <?php else: ?>
  <div class="hmb-ef-lista">
  <?php foreach ($this->efemeridesHoy as $i => $item):
    $fv   = new DateTime((string)$item->fecha_evento);
    $hay  = $anioActual - (int)$fv->format('Y');
    $cat  = (string)($item->categoria_nombre ?? '');
    list($cc,$color,$bg,$brd) = $catInfo($cat);
    $diff = isset($item->_diff_dias) ? (int)$item->_diff_dias : 0;
    $ocurr = isset($item->_ocurrencia) ? (string)$item->_ocurrencia : '';
  ?>
  <article class="hmb-ef-card hmb-ef-<?php echo $cc; ?>"
           data-index="<?php echo $i; ?>"
           style="animation-delay:<?php echo $i*70; ?>ms"
           role="button" tabindex="0">

    <!-- Línea de color superior -->
    <div class="hmb-ef-card-topline" style="background:<?php echo $color; ?>"></div>

    <!-- Panel izquierdo: año + badge proximidad -->
    <div class="hmb-ef-card-l" style="border-right-color:<?php echo $brd; ?>22">
      <span class="hmb-ef-card-anio"><?php echo $fv->format('Y'); ?></span>
      <?php if ($hay > 0): ?>
      <span class="hmb-ef-card-hace">Hace <?php echo $hay; ?> a&ntilde;o<?php echo $hay!==1?'s':''; ?></span>
      <?php endif; ?>
      <?php if (!$this->esModoHoy && $ocurr): ?>
        <?php if ($diff === 0): ?>
        <span class="hmb-ef-prox hmb-ef-prox-hoy">HOY</span>
        <?php elseif ($diff > 0): ?>
        <span class="hmb-ef-prox hmb-ef-prox-fut">en <?php echo abs($diff); ?>d</span>
        <?php else: ?>
        <span class="hmb-ef-prox hmb-ef-prox-pas">hace <?php echo abs($diff); ?>d</span>
        <?php endif; ?>
        <span class="hmb-ef-card-fecha"><?php echo htmlspecialchars($ocurr); ?></span>
      <?php endif; ?>
    </div>

    <!-- Panel derecho -->
    <div class="hmb-ef-card-r">
      <?php if ($cat !== ''): ?>
      <span class="hmb-ef-card-badge" style="color:<?php echo $color; ?>;background:<?php echo $bg; ?>;border-color:<?php echo $brd; ?>">
        <?php echo htmlspecialchars($cat); ?>
      </span>
      <?php endif; ?>
      <h2 class="hmb-ef-card-tit"><?php echo htmlspecialchars((string)$item->titulo); ?></h2>
      <?php if (!empty($item->artista)): ?>
      <?php
        $mUrl   = $getMusicoUrl((string)$item->artista, $this->musicosMap);
        $bUrl   = $getBandaUrl((string)$item->artista, $this->bandasMap);
        $lnkUrl = $mUrl ?: $bUrl;
        $foto   = $getArtFoto((string)$item->artista, $this->musicosFoto, $this->bandasFoto);
        $fotoUrl = '';
        if ($foto) {
            $fotoUrl = (strpos($foto,'http')===0) ? $foto : rtrim(Uri::root(),'/').'/'.$foto;
        }
      ?>
      <div class="hmb-ef-card-art-row">
        <?php if ($fotoUrl): ?>
        <?php if ($lnkUrl): ?>
        <a href="<?php echo htmlspecialchars($lnkUrl); ?>">
          <img src="<?php echo htmlspecialchars($fotoUrl); ?>" class="hmb-ef-art-foto" alt="">
        </a>
        <?php else: ?>
        <img src="<?php echo htmlspecialchars($fotoUrl); ?>" class="hmb-ef-art-foto" alt="">
        <?php endif; ?>
        <?php else: ?>
        <span class="hmb-ef-art-icon">&#127928;</span>
        <?php endif; ?>
        <?php if ($lnkUrl): ?>
        <a href="<?php echo htmlspecialchars($lnkUrl); ?>" class="hmb-ef-art-lnk"
           style="color:<?php echo $color; ?>">&#127928; <?php echo htmlspecialchars((string)$item->artista); ?></a>
        <?php else: ?>
        <strong class="hmb-ef-art-lnk" style="color:<?php echo $color; ?>">&#127928; <?php echo htmlspecialchars((string)$item->artista); ?></strong>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php if (!empty($item->descripcion)): ?>
      <blockquote class="hmb-ef-card-desc" style="border-left-color:<?php echo $color; ?>">
        <?php echo nl2br(htmlspecialchars((string)$item->descripcion)); ?>
      </blockquote>
      <?php endif; ?>
      <?php if (!empty($item->nombre_disco)): ?>
      <div class="hmb-ef-card-extra">&#128191; <?php echo htmlspecialchars((string)$item->nombre_disco); ?></div>
      <?php endif; ?>
      <?php if (!empty($item->lugar_concierto)): ?>
      <div class="hmb-ef-card-extra">&#128205; <?php echo htmlspecialchars((string)$item->lugar_concierto); ?>
        <?php if (!empty($item->asistentes)): ?>&nbsp;&middot;&nbsp;<?php echo number_format((int)$item->asistentes,0,',','.'); ?> asist.<?php endif; ?>
      </div>
      <?php endif; ?>
      <span class="hmb-ef-card-cta" style="color:<?php echo $color; ?>">Ver ficha &rsaquo;</span>
    </div>
  </article>
  <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="hmb-ef-footer">
    <a href="<?php echo htmlspecialchars($currentUrl); ?>?view=fallecidos" class="hmb-ef-footer-lnk">&#10013; Memorial de artistas fallecidos</a>
  </div>
</div>

<style>
.hmb-ef{font-family:inherit;max-width:900px;margin:0 auto}
.hmb-ef-hero{background:#0d0d0d;border-top:4px solid #cc0000;padding:22px 24px;margin-bottom:0;display:flex;align-items:center;justify-content:space-between}
.hmb-ef-hero-l{display:flex;align-items:center;gap:14px}
.hmb-ef-hero-d{font-size:3.6rem;font-weight:900;color:#cc0000;line-height:1;animation:hmb-p 3s ease-in-out infinite}
@keyframes hmb-p{0%,100%{opacity:1}50%{opacity:.65}}
.hmb-ef-hero-r{display:flex;flex-direction:column}
.hmb-ef-hero-m{font-size:1.4rem;font-weight:800;color:#fff;letter-spacing:.08em;line-height:1}
.hmb-ef-hero-s{font-size:.58rem;color:#555;letter-spacing:.12em;margin-top:4px;text-transform:uppercase}
.hmb-ef-hero-c{text-align:center}
.hmb-ef-hero-cn{display:block;font-size:2.2rem;font-weight:900;color:#d4af37;line-height:1}
.hmb-ef-hero-ct{font-size:.6rem;color:#555;letter-spacing:.1em;text-transform:uppercase}
/* Aviso proximidad */
.hmb-ef-aviso{background:#111;border:1px solid #2a2a2a;border-left:3px solid #d4af37;padding:10px 16px;font-size:.8rem;color:#888;margin-bottom:0}
.hmb-ef-aviso strong{color:#d4af37}
/* Filtros */
.hmb-ef-nav{display:flex;flex-wrap:wrap;gap:5px;padding:12px 0;border-bottom:1px solid #1a1a1a;margin-bottom:20px}
.hmb-ef-nav-btn{padding:5px 14px;background:#111;border:1px solid #2a2a2a;color:#666;text-decoration:none;font-size:.7rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;transition:all .2s}
.hmb-ef-nav-btn:hover{border-color:#cc0000;color:#cc0000}
.hmb-ef-nav-on{background:#cc0000 !important;border-color:#cc0000 !important;color:#fff !important}
/* Lista */
.hmb-ef-lista{display:flex;flex-direction:column;gap:12px;margin-bottom:22px}
/* Tarjeta */
.hmb-ef-card{display:flex;background:#111;border:1px solid #1e1e1e;position:relative;overflow:hidden;cursor:pointer;opacity:0;transform:translateY(14px);animation:hmb-in .45s ease forwards}
@keyframes hmb-in{to{opacity:1;transform:none}}
.hmb-ef-card:hover{background:#141414;border-color:#2a2a2a}
.hmb-ef-card:focus{outline:2px solid #cc0000;outline-offset:-2px}
.hmb-ef-card-topline{position:absolute;top:0;left:0;right:0;height:3px}
.hmb-ef-card-l{min-width:100px;max-width:100px;background:#0a0a0a;padding:18px 10px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;border-right:1px solid #1e1e1e}
.hmb-ef-card-anio{font-size:1.6rem;font-weight:900;color:#d4af37;line-height:1;display:block}
.hmb-ef-card-hace{font-size:.6rem;color:#555;margin-top:4px;display:block;line-height:1.3}
.hmb-ef-card-fecha{font-size:.62rem;color:#444;display:block;margin-top:3px;font-weight:600}
/* Badges de proximidad */
.hmb-ef-prox{display:inline-block;font-size:.56rem;font-weight:700;padding:2px 5px;border-radius:2px;margin-top:5px;letter-spacing:.04em;text-transform:uppercase}
.hmb-ef-prox-hoy{background:#cc0000;color:#fff}
.hmb-ef-prox-fut{background:#0d3a6e;color:#7ab3e0}
.hmb-ef-prox-pas{background:#1e1e1e;color:#666}
.hmb-ef-card-r{flex:1;padding:16px 20px;min-width:0}
.hmb-ef-card-badge{display:inline-block;font-size:.6rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:3px 9px;border:1px solid transparent;border-radius:2px;margin-bottom:8px}
.hmb-ef-card-tit{font-size:1rem;font-weight:700;color:#fff;margin:0 0 7px;line-height:1.4}
.hmb-ef-card-art{font-size:.82rem;color:#999;margin:0 0 9px}
.hmb-ef-card-art-row{display:flex;align-items:center;gap:10px;margin:8px 0}
.hmb-ef-art-foto{width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #333;flex-shrink:0;box-shadow:0 0 8px rgba(0,0,0,.5)}
.hmb-ef-art-icon{width:42px;height:42px;border-radius:50%;background:#1a1a1a;border:2px solid #333;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
.hmb-ef-art-lnk{text-decoration:none;font-weight:700}
.hmb-ef-art-lnk:hover{text-decoration:underline}
.hmb-ef-card-desc{font-size:.82rem;color:#777;line-height:1.7;border-left:3px solid #cc0000;padding-left:12px;margin:0 0 10px}
.hmb-ef-card-extra{font-size:.76rem;color:#666;margin-bottom:5px;display:flex;align-items:center;gap:6px}
.hmb-ef-card-cta{font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;display:block;margin-top:8px;opacity:0;transition:opacity .2s}
.hmb-ef-card:hover .hmb-ef-card-cta,.hmb-ef-card:focus .hmb-ef-card-cta{opacity:1}
.hmb-ef-vacio{background:#0d0d0d;border:1px solid #1e1e1e;padding:40px;text-align:center;color:#888;margin-bottom:20px}
.hmb-ef-footer{text-align:center;padding:14px;border-top:1px solid #1a1a1a}
.hmb-ef-footer-lnk{color:#cc0000;text-decoration:none;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
/* Modal */
.hmb-ov{position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;opacity:0;visibility:hidden;transition:opacity .3s,visibility .3s}
.hmb-ov.hmb-on{opacity:1;visibility:visible}
.hmb-modal{background:#111;border:1px solid #2a2a2a;border-top:4px solid #cc0000;max-width:600px;width:100%;max-height:90vh;overflow-y:auto;position:relative;padding:26px 26px 22px;transform:translateY(20px) scale(.98);transition:transform .3s}
.hmb-ov.hmb-on .hmb-modal{transform:none}
.hmb-modal-x{position:absolute;top:12px;right:12px;background:none;border:1px solid #2a2a2a;color:#888;font-size:1.3rem;width:34px;height:34px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;line-height:1;padding:0}
.hmb-modal-x:hover{background:#cc0000;border-color:#cc0000;color:#fff}
.hmb-mod-top{display:flex;align-items:baseline;gap:12px;margin-bottom:8px}
.hmb-mod-anio{font-size:2.6rem;font-weight:900;color:#d4af37;line-height:1}
.hmb-mod-ftxt{font-size:.78rem;color:#555;letter-spacing:.05em}
.hmb-mod-badge{display:inline-block;font-size:.6rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:3px 10px;border-radius:2px;margin-bottom:10px}
.hmb-mod-tit{font-size:1.25rem;font-weight:900;color:#fff;margin:0 0 7px;line-height:1.4;padding-right:30px}
.hmb-mod-art{font-size:.9rem;color:#d4af37;font-weight:700;margin:0 0 14px}
.hmb-mod-sep{height:1px;background:#cc0000;opacity:.35;margin-bottom:14px}
.hmb-mod-desc{font-size:.87rem;color:#999;line-height:1.8;margin:0 0 12px;border-left:3px solid #cc0000;padding-left:13px}
.hmb-mod-desc:empty{display:none}
.hmb-mod-extra{display:flex;gap:8px;font-size:.8rem;color:#888;background:#0a0a0a;padding:7px 11px;border:1px solid #1e1e1e;margin-bottom:6px}
.hmb-mod-elbl{color:#555;font-weight:700;min-width:56px}
.hmb-mod-eval{color:#bbb}
.hmb-mod-det{font-size:.83rem;color:#777;line-height:1.8;border-top:1px solid #1e1e1e;padding-top:12px;margin-top:8px}
.hmb-mod-det:empty{display:none}
.hmb-modal::-webkit-scrollbar{width:3px}
.hmb-modal::-webkit-scrollbar-thumb{background:#cc0000}
@media(max-width:560px){
  .hmb-ef-card{flex-direction:column}
  .hmb-ef-card-l{flex-direction:row;min-width:unset;max-width:unset;padding:10px 14px;gap:12px;border-right:none;border-bottom:1px solid #1e1e1e}
  .hmb-ef-hero-d{font-size:2.6rem}
}
</style>

<script>
(function(){
  var D=<?php echo $jsonData; ?>;
  var ov=document.getElementById('hmb-ov');
  var bx=document.getElementById('hmb-modal');
  var xb=document.getElementById('hmb-modal-x');
  var pv=null;
  function fmt(n){return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
  function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
  function open(i){
    var d=D[i];if(!d)return;
    pv=document.activeElement;
    document.getElementById('hmb-mod-anio').textContent=d.anio;
    document.getElementById('hmb-mod-ftxt').textContent=d.fecha+(d.hace>0?' · Hace '+d.hace+' año'+(d.hace!==1?'s':''):'');
    document.getElementById('hmb-mod-tit').textContent=d.titulo;
    document.getElementById('hmb-mod-art').textContent=d.artista?'🎸 '+d.artista:'';
    var b=document.getElementById('hmb-mod-badge');
    b.textContent=d.cat;b.style.color=d.catColor;b.style.background=d.catBg;
    document.getElementById('hmb-mod-desc').textContent=d.desc;
    var ex=document.getElementById('hmb-mod-ext');ex.innerHTML='';
    if(d.disco)ex.innerHTML+='<div class="hmb-mod-extra"><span class="hmb-mod-elbl">Disco:</span><span class="hmb-mod-eval">&#128191; '+esc(d.disco)+'</span></div>';
    if(d.lugar){var a=d.asist>0?' · '+fmt(d.asist)+' asistentes':'';ex.innerHTML+='<div class="hmb-mod-extra"><span class="hmb-mod-elbl">Lugar:</span><span class="hmb-mod-eval">&#128205; '+esc(d.lugar)+a+'</span></div>';}
    document.getElementById('hmb-mod-det').innerHTML=d.detalle||'';
    bx.scrollTop=0;
    ov.classList.add('hmb-on');
    document.body.style.overflow='hidden';
    setTimeout(function(){xb.focus();},320);
  }
  function close(){ov.classList.remove('hmb-on');document.body.style.overflow='';if(pv)setTimeout(function(){pv.focus();},100);}
  document.querySelectorAll('.hmb-ef-card').forEach(function(el){
    el.addEventListener('click',function(){open(parseInt(el.dataset.index));});
    el.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();open(parseInt(el.dataset.index));}});
  });
  xb.addEventListener('click',close);
  ov.addEventListener('click',function(e){if(e.target===ov)close();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&ov.classList.contains('hmb-on'))close();});
  bx.addEventListener('keydown',function(e){
    if(e.key!=='Tab')return;
    var f=bx.querySelectorAll('button,[href],[tabindex]:not([tabindex="-1"])');
    if(!f.length)return;
    var fi=f[0],la=f[f.length-1];
    if(e.shiftKey){if(document.activeElement===fi){e.preventDefault();la.focus();}}
    else{if(document.activeElement===la){e.preventDefault();fi.focus();}}
  });
})();
</script>
