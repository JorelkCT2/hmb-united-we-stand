<?php defined('_JEXEC') or die;
$_base = rtrim($this->componentBase, '/');
$getMusicoUrl = static function(string $n, array $m) use ($_base): string {
    $id=$m[mb_strtolower(trim($n))]??null;
    return $id ? ($_base . '?view=musico&id=' . (int)$id) : '';
};
$getBandaUrl = static function(string $n, array $m) use ($_base): string {
    $id=$m[mb_strtolower(trim($n))]??null;
    return $id ? ($_base . '?view=banda&id=' . (int)$id) : '';
};
use Joomla\CMS\Uri\Uri;

$currentUrl = Uri::current();
$anioActual = (int)date('Y');

$search    = $this->filtros['search'];
$catActiva = $this->filtros['cat'];
$mesActivo = $this->filtros['mes'];
$anioActivo= $this->filtros['anio'];
$ordenCol  = $this->ordenCol;
$ordenDir  = $this->ordenDir;
$limit     = $this->limit;
$total     = $this->total;

$meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
          7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

$makeUrl = static function(array $extra) use ($currentUrl,$search,$catActiva,$mesActivo,$anioActivo,$ordenCol,$ordenDir,$limit): string {
    $p = ['view'=>'lista'];
    if ($search)     $p['search']  = $search;
    if ($catActiva)  $p['cat']     = $catActiva;
    if ($mesActivo)  $p['mes']     = $mesActivo;
    if ($anioActivo) $p['anio']    = $anioActivo;
    $p['ordenar'] = $ordenCol;
    $p['dir']     = $ordenDir;
    $p['limit']   = $limit;
    foreach ($extra as $k=>$v) {
        if ($v===null||$v==='') unset($p[$k]); else $p[$k]=$v;
    }
    return $currentUrl.'?'.http_build_query($p);
};

$urlOrden = static function(string $col) use ($currentUrl,$search,$catActiva,$mesActivo,$anioActivo,$ordenCol,$ordenDir,$limit): string {
    $newDir = ($ordenCol===$col && $ordenDir==='ASC') ? 'DESC' : 'ASC';
    $p = array_filter(['view'=>'lista','search'=>$search,'cat'=>$catActiva?:null,
        'mes'=>$mesActivo?:null,'anio'=>$anioActivo?:null,
        'ordenar'=>$col,'dir'=>$newDir,'limit'=>$limit,'limitstart'=>0],
        static function($v){ return $v!==null&&$v!==''&&$v!==0; });
    return $currentUrl.'?'.http_build_query($p);
};

$iconOrden = static function(string $col) use ($ordenCol,$ordenDir): string {
    if ($ordenCol!==$col) return '<span class="ei-si">⇅</span>';
    return $ordenDir==='ASC' ? '<span class="ei-sa">▲</span>' : '<span class="ei-sa">▼</span>';
};

$catInfo = static function(string $n): array {
    $n = mb_strtolower(trim($n));
    if (strpos($n,'conci') !==false) return ['#d4641a','#2a1200'];
    if (strpos($n,'naci')  !==false) return ['#4caf50','#0a1f0a'];
    if (strpos($n,'defun') !==false||strpos($n,'fallec')!==false) return ['#cc4444','#1f0a0a'];
    if (strpos($n,'lanz')  !==false||strpos($n,'disco') !==false) return ['#5ba3d9','#06122a'];
    return ['#888','#1a1a1a'];
};

// Stats por categoría
$statsCat = [];
foreach ($this->items as $item) {
    $c = (string)($item->categoria_nombre ?? 'Otros');
    $statsCat[$c] = ($statsCat[$c] ?? 0) + 1;
}

// JSON modal
$jItems = [];
foreach ($this->items as $item) {
    $fv = new DateTime((string)$item->fecha_evento);
    list($col,$bg) = $catInfo((string)($item->categoria_nombre ?? ''));
    $jItems[] = [
        'titulo' =>(string)$item->titulo, 'artista'=>(string)($item->artista??''),
        'cat'    =>(string)($item->categoria_nombre??''), 'catColor'=>$col,'catBg'=>$bg,
        'anio'   =>$fv->format('Y'), 'fecha'=>$fv->format('d/m/Y'),
        'hace'   =>$anioActual-(int)$fv->format('Y'),
        'desc'   =>(string)($item->descripcion??''), 'detalle'=>(string)($item->detalle??''),
        'disco'  =>(string)($item->nombre_disco??''), 'lugar'=>(string)($item->lugar_concierto??''),
        'asist'  =>(int)($item->asistentes??0),
    ];
}
$jd = json_encode($jItems, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE);

$totalPags = $limit > 0 ? (int)ceil($total/$limit) : 1;
$pagActual = $limit > 0 ? (int)floor($this->limitstart/$limit)+1 : 1;
?>
<!– MODAL –>
<div id="ei-ov" class="ei-ov" role="dialog" aria-modal="true">
  <div class="ei-modal" id="ei-modal">
    <button class="ei-mx" id="ei-mx">&times;</button>
    <div class="ei-mtop"><span class="ei-manio" id="ei-manio"></span><span class="ei-mftxt" id="ei-mftxt"></span></div>
    <span class="ei-mbadge" id="ei-mbadge"></span>
    <h2 class="ei-mtit" id="ei-mtit"></h2>
    <p class="ei-mart" id="ei-mart"></p>
    <div class="ei-msep"></div>
    <p class="ei-mdesc" id="ei-mdesc"></p>
    <div id="ei-mext"></div>
    <div class="ei-mdet" id="ei-mdet"></div>
  </div>
</div>

<div class="ei-wrap" id="ei-wrap-root">

  <!-- ===== HERO ===== -->
  <div class="ei-hero">
    <div class="ei-hero-llama ei-hero-llama-l"></div>
    <div class="ei-hero-llama ei-hero-llama-r"></div>
    <div class="ei-hero-content">
      <div class="ei-hero-titulo">
        <span class="ei-hero-rock">Rock &amp; Metal</span>
        <h1 class="ei-hero-h1">ARCHIVO DE EFEMÉRIDES</h1>
        <p class="ei-hero-sub">HISTORIA · MÚSICA · LEGADO</p>
      </div>
      <!-- Stats -->
      <div class="ei-stats">
        <div class="ei-stat">
          <span class="ei-stat-n"><?php echo $total; ?></span>
          <span class="ei-stat-l">total</span>
        </div>
        <?php foreach (array_slice($statsCat, 0, 3, true) as $cn => $cnt):
          list($col) = $catInfo($cn);
        ?>
        <div class="ei-stat">
          <span class="ei-stat-n" style="color:<?php echo $col; ?>"><?php echo $cnt; ?></span>
          <span class="ei-stat-l"><?php echo htmlspecialchars(mb_substr($cn,0,12)); ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- ===== FILTROS ===== -->
  <form method="get" action="<?php echo htmlspecialchars($currentUrl); ?>" class="ei-form" id="ei-form">
    <input type="hidden" name="view"    value="lista">
    <input type="hidden" name="ordenar" value="<?php echo htmlspecialchars($ordenCol); ?>">
    <input type="hidden" name="dir"     value="<?php echo htmlspecialchars($ordenDir); ?>">
    <input type="hidden" name="limit"   value="<?php echo $limit; ?>">
    <input type="hidden" name="limitstart" value="0">

    <div class="ei-form-inner">
      <div class="ei-fg ei-fg-search">
        <span class="ei-search-ico">&#128269;</span>
        <input type="text" name="search" id="ei-search"
               value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Buscar título, artista, disco, lugar..."
               class="ei-input" autocomplete="off">
        <?php if ($search): ?>
        <button type="button" class="ei-input-clear" onclick="document.getElementById('ei-search').value='';document.getElementById('ei-form').submit()">&#10005;</button>
        <?php endif; ?>
      </div>

      <div class="ei-selects">
        <div class="ei-sel-wrap">
          <select name="mes" class="ei-select" onchange="document.getElementById('ei-form').submit()">
            <option value="0">Todos los meses</option>
            <?php foreach ($meses as $n=>$nm): ?>
            <option value="<?php echo $n; ?>" <?php echo $mesActivo===$n?'selected':''; ?>><?php echo $nm; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ei-sel-wrap">
          <select name="anio" class="ei-select" onchange="document.getElementById('ei-form').submit()">
            <option value="0">Todos los años</option>
            <?php foreach ($this->anios as $a): ?>
            <option value="<?php echo (int)$a; ?>" <?php echo $anioActivo===(int)$a?'selected':''; ?>><?php echo (int)$a; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ei-sel-wrap">
          <select name="cat" class="ei-select" onchange="document.getElementById('ei-form').submit()">
            <option value="0">Todas las categorías</option>
            <?php foreach ($this->categorias as $cat): ?>
            <option value="<?php echo (int)$cat->id; ?>" <?php echo $catActiva===(int)$cat->id?'selected':''; ?>><?php echo htmlspecialchars((string)$cat->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ei-sel-wrap">
          <select name="limit" class="ei-select ei-select-sm" onchange="document.getElementById('ei-form').submit()">
            <?php foreach ([10,20,50,100] as $lv): ?>
            <option value="<?php echo $lv; ?>" <?php echo $limit===$lv?'selected':''; ?>><?php echo $lv; ?> / pág</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="ei-form-actions">
        <button type="submit" class="ei-btn-buscar">Buscar</button>
        <?php if ($search||$catActiva||$anioActivo||($mesActivo&&$mesActivo!==(int)date('n'))): ?>
        <a href="<?php echo htmlspecialchars($currentUrl); ?>?view=lista" class="ei-btn-clear-all">&#10005; Limpiar</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Chips de filtros activos -->
    <?php if ($search||$catActiva||$anioActivo||$mesActivo): ?>
    <div class="ei-chips">
      <?php if ($mesActivo): ?><span class="ei-chip">&#128197; <?php echo $meses[$mesActivo]??''; ?></span><?php endif; ?>
      <?php if ($anioActivo): ?><span class="ei-chip">&#128337; <?php echo $anioActivo; ?></span><?php endif; ?>
      <?php if ($catActiva): ?>
        <?php foreach ($this->categorias as $c): if ((int)$c->id===$catActiva): ?>
        <span class="ei-chip">&#127991; <?php echo htmlspecialchars((string)$c->name); ?></span>
        <?php endif; endforeach; ?>
      <?php endif; ?>
      <?php if ($search): ?><span class="ei-chip">&#128269; "<?php echo htmlspecialchars(mb_substr($search,0,20)); ?>"</span><?php endif; ?>
      <span class="ei-chip ei-chip-result"><?php echo $total; ?> resultado<?php echo $total!==1?'s':''; ?></span>
    </div>
    <?php endif; ?>
  </form>

  <!-- ===== TABLA ===== -->
  <?php if (empty($this->items)): ?>
  <div class="ei-empty">
    <div class="ei-empty-ico">&#129304;</div>
    <p>No hay efemérides con los filtros aplicados.</p>
    <a href="<?php echo htmlspecialchars($currentUrl); ?>?view=lista" class="ei-lnk">Quitar todos los filtros</a>
  </div>
  <?php else: ?>

  <div class="ei-table-wrap">
    <table class="ei-table">
      <thead>
        <tr>
          <th class="ei-th"><a href="<?php echo htmlspecialchars($urlOrden('e.fecha_evento')); ?>" class="ei-sort">Fecha <?php echo $iconOrden('e.fecha_evento'); ?></a></th>
          <th class="ei-th ei-th-ev"><a href="<?php echo htmlspecialchars($urlOrden('e.titulo')); ?>" class="ei-sort">Evento <?php echo $iconOrden('e.titulo'); ?></a></th>
          <th class="ei-th ei-th-art"><a href="<?php echo htmlspecialchars($urlOrden('e.artista')); ?>" class="ei-sort">Artista <?php echo $iconOrden('e.artista'); ?></a></th>
          <th class="ei-th ei-th-cat"><a href="<?php echo htmlspecialchars($urlOrden('c.name')); ?>" class="ei-sort">Categoría <?php echo $iconOrden('c.name'); ?></a></th>
          <th class="ei-th ei-th-ex">Extra</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($this->items as $i => $item):
        $fv  = new DateTime((string)$item->fecha_evento);
        $hay = $anioActual - (int)$fv->format('Y');
        $cat = (string)($item->categoria_nombre ?? '');
        list($col,$bg) = $catInfo($cat);
      ?>
      <tr class="ei-row" data-idx="<?php echo $i; ?>" role="button" tabindex="0">
        <td class="ei-td ei-td-fecha">
          <div class="ei-fecha-dia"><?php echo $fv->format('d/m'); ?></div>
          <div class="ei-fecha-anio"><?php echo $fv->format('Y'); ?></div>
          <?php if ($hay>0): ?><div class="ei-fecha-hace"><?php echo $hay; ?>a</div><?php endif; ?>
        </td>
        <td class="ei-td ei-td-ev">
          <span class="ei-ev-tit"><?php echo htmlspecialchars((string)$item->titulo); ?></span>
        </td>
        <td class="ei-td ei-td-art">
          <?php
          $art=(string)($item->artista??'');
          if($art){
            $mUrl=$getMusicoUrl($art,$this->musicosMap);
            $bUrl=$getBandaUrl($art,$this->bandasMap);
            $lUrl=$mUrl?:$bUrl;
            if($lUrl):?><a href="<?php echo htmlspecialchars($lUrl);?>" class="ei-art-lnk"><?php echo htmlspecialchars($art);?></a>
            <?php else: echo htmlspecialchars($art); endif;
          } else { echo '—'; } ?>
        </td>
        <td class="ei-td">
          <?php if ($cat): ?>
          <span class="ei-badge" style="color:<?php echo $col; ?>;background:<?php echo $bg; ?>;border-color:<?php echo $col; ?>22">
            <?php echo htmlspecialchars($cat); ?>
          </span>
          <?php endif; ?>
        </td>
        <td class="ei-td ei-td-ex">
          <?php if (!empty($item->nombre_disco)): ?>
          <span class="ei-extra-pill">&#128191; <?php echo htmlspecialchars(mb_substr((string)$item->nombre_disco,0,25)); ?></span>
          <?php elseif (!empty($item->lugar_concierto)): ?>
          <span class="ei-extra-pill">&#128205; <?php echo htmlspecialchars(mb_substr((string)$item->lugar_concierto,0,25)); ?></span>
          <?php else: ?><span class="ei-nd">—</span><?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== PAGINACIÓN ===== -->
  <div class="ei-pag">
    <div class="ei-pag-info">
      <span class="ei-pag-desde"><?php echo $this->limitstart+1; ?></span>
      <span class="ei-pag-sep">–</span>
      <span class="ei-pag-hasta"><?php echo min($this->limitstart+$limit,$total); ?></span>
      <span class="ei-pag-de">de</span>
      <span class="ei-pag-total"><?php echo $total; ?></span>
    </div>

    <div class="ei-pag-nav">
      <?php if ($pagActual>1): ?>
      <a href="<?php echo htmlspecialchars($makeUrl(['limitstart'=>0])); ?>" class="ei-pb ei-pb-arrow" title="Primera">&#171;</a>
      <a href="<?php echo htmlspecialchars($makeUrl(['limitstart'=>($pagActual-2)*$limit])); ?>" class="ei-pb ei-pb-arrow" title="Anterior">&#8249;</a>
      <?php endif; ?>

      <?php
      $ini = max(1,$pagActual-2); $fin = min($totalPags,$pagActual+2);
      if ($ini>1) echo '<span class="ei-pag-dots">…</span>';
      for ($p=$ini;$p<=$fin;$p++):
        $st = ($p-1)*$limit;
        $on = $p===$pagActual;
      ?>
      <a href="<?php echo htmlspecialchars($makeUrl(['limitstart'=>$st])); ?>"
         class="ei-pb<?php echo $on?' ei-pb-on':''; ?>"><?php echo $p; ?></a>
      <?php endfor;
      if ($fin<$totalPags) echo '<span class="ei-pag-dots">…</span>';
      ?>

      <?php if ($pagActual<$totalPags): ?>
      <a href="<?php echo htmlspecialchars($makeUrl(['limitstart'=>$pagActual*$limit])); ?>" class="ei-pb ei-pb-arrow" title="Siguiente">&#8250;</a>
      <a href="<?php echo htmlspecialchars($makeUrl(['limitstart'=>($totalPags-1)*$limit])); ?>" class="ei-pb ei-pb-arrow" title="Última">&#187;</a>
      <?php endif; ?>
    </div>

    <div class="ei-pag-lim">
      Página <strong><?php echo $pagActual; ?></strong> de <strong><?php echo $totalPags; ?></strong>
    </div>
  </div>

  <?php endif; ?>

  <div class="ei-footer">
    <a href="<?php echo htmlspecialchars(Uri::current()); ?>" class="ei-footer-lnk">&#8592; Efemérides del día</a>
    <span class="ei-footer-sep">·</span>
    <a href="<?php echo htmlspecialchars(Uri::current()); ?>?view=fallecidos" class="ei-footer-lnk">&#10013; Memorial</a>
  </div>

</div><!-- /ei-wrap -->

<style>
/* ===== BASE ===== */
.ei-wrap{font-family:inherit;max-width:1100px;margin:0 auto}

/* ===== HERO ===== */
.ei-hero{position:relative;background:var(--dark, #0A0A0A) !important;border-top:4px solid var(--fire, #CC2200);overflow:hidden;padding:0;margin-bottom:0}
.ei-hero-llama{position:absolute;top:0;width:180px;height:100%;background:linear-gradient(90deg,rgba(204,60,0,.15),transparent);pointer-events:none}
.ei-hero-llama-l{left:0}
.ei-hero-llama-r{right:0;transform:scaleX(-1)}
.ei-hero-content{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;padding:22px 28px;flex-wrap:wrap;gap:16px}
.ei-hero-rock{font-size:.65rem;font-weight:700;letter-spacing:.2em;color:var(--fire, #CC2200);text-transform:uppercase;display:block;margin-bottom:4px}
.ei-hero-h1{font-size:1.6rem;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:.06em;margin:0 0 4px;line-height:1}
.ei-hero-sub{font-size:.58rem;color:var(--grey-4, #444444);letter-spacing:.18em;margin:0}
/* Stats */
.ei-stats{display:flex;gap:20px;align-items:center}
.ei-stat{text-align:center;padding:10px 16px;background:var(--mid, #111111) !important;border:1px solid var(--steel, #1A1A1A);border-top:2px solid var(--fire, #CC2200);min-width:70px}
.ei-stat-n{display:block;font-size:1.6rem;font-weight:900;color:var(--gold-light, #DAA520);line-height:1}
.ei-stat-l{display:block;font-size:.58rem;color:var(--grey-5, #555555);text-transform:uppercase;letter-spacing:.08em;margin-top:3px}

/* ===== FORM ===== */
.ei-form{background:var(--mid, #111111) !important;border:1px solid var(--steel, #1A1A1A);border-top:none;padding:14px 18px}
.ei-form-inner{display:flex;flex-wrap:wrap;gap:8px;align-items:stretch}
.ei-fg-search{flex:1;min-width:240px;position:relative;display:flex;align-items:center;background:var(--mid, #111111) !important;border:1px solid var(--steel2, #222222)}
.ei-fg-search:focus-within{border-color:var(--fire, #CC2200)}
.ei-search-ico{position:absolute;left:12px;color:var(--grey-5, #555555);font-size:.9rem;pointer-events:none}
.ei-input{flex:1;background:transparent;border:none;color:var(--grey-d, #DDDDDD);padding:9px 36px 9px 36px;font-size:.82rem;outline:none;min-width:0}
.ei-input::placeholder{color:var(--grey-4, #444444)}
.ei-input-clear{position:absolute;right:10px;background:none;border:none;color:var(--grey-5, #555555);cursor:pointer;font-size:.9rem;padding:4px;line-height:1}
.ei-input-clear:hover{color:var(--fire, #CC2200)}
.ei-selects{display:flex;flex-wrap:wrap;gap:6px}
.ei-sel-wrap{position:relative}
.ei-select{background:var(--mid, #111111) !important;border:1px solid var(--steel2, #222222);color:var(--grey-a, #AAAAAA);padding:9px 10px;font-size:.78rem;cursor:pointer;outline:none;height:100%}
.ei-select:focus{border-color:var(--fire, #CC2200)}
.ei-select-sm{min-width:90px}
.ei-form-actions{display:flex;gap:6px;align-items:center}
.ei-btn-buscar{background:var(--fire, #CC2200);border:none;color:#fff;padding:9px 18px;font-size:.8rem;font-weight:700;cursor:pointer;letter-spacing:.05em;text-transform:uppercase;transition:background .2s}
.ei-btn-buscar:hover{background:#aa0000}
.ei-btn-clear-all{background:var(--steel, #1A1A1A) !important;border:1px solid var(--grey-3, #333333);color:var(--grey-8, #888888);padding:8px 12px;text-decoration:none;font-size:.76rem;font-weight:700;white-space:nowrap;transition:all .2s}
.ei-btn-clear-all:hover{border-color:var(--fire, #CC2200);color:var(--fire, #CC2200)}
/* Chips */
.ei-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}
.ei-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:var(--steel, #1A1A1A) !important;border:1px solid var(--grey-2, #2A2A2A);color:var(--grey-8, #888888);font-size:.7rem;border-radius:20px}
.ei-chip-result{background:#cc000022;border-color:#cc000044;color:#cc6666;font-weight:700}

/* ===== TABLA ===== */
.ei-table-wrap{overflow-x:auto;border:1px solid var(--steel, #1A1A1A);border-top:none}
.ei-table{width:100%;border-collapse:collapse;min-width:580px;background:var(--mid, #111111) !important}
.ei-th{background:var(--dark, #0A0A0A) !important;border-bottom:1px solid var(--steel, #1A1A1A);padding:0;white-space:nowrap}
.ei-sort{display:flex;align-items:center;gap:5px;padding:10px 14px;color:var(--grey-6, #666666);text-decoration:none;font-size:.68rem;font-weight:700;letter-spacing:.09em;text-transform:uppercase;transition:color .2s;white-space:nowrap}
.ei-sort:hover{color:var(--fire, #CC2200)}
.ei-si{color:var(--grey-2, #2A2A2A);font-size:.65rem}
.ei-sa{color:var(--fire, #CC2200);font-size:.65rem}
.ei-row{border-bottom:1px solid var(--dark, #0A0A0A);cursor:pointer;transition:background .12s,transform .12s;background:var(--mid, #111111) !important}
.ei-row:hover{background:#2a0a0a !important}
.ei-row:hover .ei-ev-tit{color:#fff}
.ei-row:focus{outline:1px solid var(--fire, #CC2200);outline-offset:-1px}
.ei-td{padding:10px 14px;vertical-align:middle;font-size:.82rem;color:var(--grey-b, #BBBBBB)}
.ei-td-fecha{white-space:nowrap;min-width:72px}
.ei-fecha-dia{font-size:.95rem;font-weight:800;color:var(--gold-light, #DAA520);line-height:1}
.ei-fecha-anio{font-size:.68rem;color:var(--grey-6, #666666);margin-top:1px}
.ei-fecha-hace{font-size:.6rem;color:var(--grey-4, #444444)}
.ei-td-ev{max-width:280px}
.ei-ev-tit{font-weight:600;color:var(--grey-d, #DDDDDD);line-height:1.35;transition:color .15s;display:block}
.ei-td-art{color:var(--grey-9, #999999);max-width:170px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ei-badge{font-size:.6rem;font-weight:700;padding:3px 8px;border-radius:2px;letter-spacing:.07em;text-transform:uppercase;white-space:nowrap;border:1px solid transparent}
.ei-td-ex{font-size:.74rem;color:var(--grey-5, #555555);max-width:170px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ei-extra-pill{color:var(--text-dim, #777777)}
.ei-nd{color:var(--grey-2, #2A2A2A)}

/* ===== PAGINACIÓN ===== */
.ei-pag{background:var(--mid, #111111) !important;padding:14px;border:1px solid var(--steel, #1A1A1A);border-top:none;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 0;border-top:1px solid var(--steel, #1A1A1A)}
.ei-pag-info{font-size:.76rem;color:var(--grey-5, #555555);display:flex;align-items:baseline;gap:4px}
.ei-pag-desde,.ei-pag-hasta{color:var(--gold-light, #DAA520);font-weight:700}
.ei-pag-total{color:var(--grey-a, #AAAAAA);font-weight:700}
.ei-pag-de{color:var(--grey-4, #444444)}
.ei-pag-sep{color:var(--grey-3, #333333)}
.ei-pag-nav{display:flex;flex-wrap:wrap;gap:3px;align-items:center}
.ei-pb{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 6px;background:var(--mid, #111111) !important;border:1px solid var(--steel, #1A1A1A);color:var(--text-dim, #777777);text-decoration:none;font-size:.78rem;font-weight:700;transition:all .2s}
.ei-pb:hover{border-color:var(--fire, #CC2200);color:var(--fire, #CC2200);background:var(--wine-dark, #1A0000)}
.ei-pb-on{background:var(--fire, #CC2200) !important;border-color:var(--fire, #CC2200) !important;color:#fff !important}
.ei-pb-arrow{font-size:.85rem;color:var(--grey-5, #555555)}
.ei-pag-dots{color:var(--grey-3, #333333);padding:0 4px;font-size:.8rem;line-height:34px}
.ei-pag-lim{font-size:.72rem;color:var(--grey-4, #444444)}
.ei-pag-lim strong{color:var(--grey-8, #888888)}

/* ===== EMPTY / FOOTER ===== */
.ei-empty{background:var(--mid, #111111) !important;border:1px solid var(--steel, #1A1A1A);padding:48px;text-align:center;color:var(--grey-6, #666666)}
.ei-empty-ico{font-size:2.5rem;margin-bottom:12px}
.ei-lnk{color:var(--fire, #CC2200);text-decoration:none;font-size:.8rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase}
.ei-footer{background:var(--mid, #111111) !important;border:1px solid var(--steel, #1A1A1A);border-top:none;text-align:center;padding:14px;border-top:1px solid var(--steel, #1A1A1A);margin-top:4px;display:flex;align-items:center;justify-content:center;gap:12px}
.ei-footer-sep{color:var(--grey-3, #333333)}
.ei-footer-lnk{color:var(--fire, #CC2200);text-decoration:none;font-size:.76rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.ei-footer-lnk:hover{color:#ff2222}

/* ===== MODAL ===== */
.ei-ov{position:fixed;inset:0;background:rgba(0,0,0,.93) !important;z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;opacity:0;visibility:hidden;transition:opacity .3s,visibility .3s}
.ei-ov.ei-on{opacity:1;visibility:visible}
.ei-modal{background:var(--mid, #111111) !important;border:1px solid var(--steel2, #222222);border-top:4px solid var(--fire, #CC2200);max-width:580px;width:100%;max-height:90vh;overflow-y:auto;position:relative;padding:24px 26px 20px;transform:translateY(16px) scale(.98);transition:transform .3s}
.ei-ov.ei-on .ei-modal{transform:none}
.ei-mx{position:absolute;top:12px;right:12px;background:none;border:1px solid var(--steel2, #222222);color:var(--grey-8, #888888);font-size:1.3rem;width:34px;height:34px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;line-height:1;padding:0}
.ei-mx:hover{background:var(--fire, #CC2200);border-color:var(--fire, #CC2200);color:#fff}
.ei-mtop{display:flex;align-items:baseline;gap:12px;margin-bottom:7px}
.ei-manio{font-size:2.5rem;font-weight:900;color:var(--gold-light, #DAA520);line-height:1}
.ei-mftxt{font-size:.76rem;color:var(--grey-5, #555555)}
.ei-mbadge{display:inline-block;font-size:.6rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:3px 9px;border-radius:2px;margin-bottom:10px}
.ei-mtit{font-size:1.2rem;font-weight:900;color:#fff;margin:0 0 6px;line-height:1.4;padding-right:28px}
.ei-mart{font-size:.88rem;color:var(--gold-light, #DAA520);font-weight:700;margin:0 0 12px}
.ei-msep{height:1px;background:var(--fire, #CC2200);opacity:.3;margin-bottom:12px}
.ei-mdesc{font-size:.85rem;color:var(--grey-9, #999999);line-height:1.8;margin:0 0 12px;border-left:3px solid var(--fire, #CC2200);padding-left:12px}
.ei-mdesc:empty{display:none}
.ei-mrow{display:flex;gap:8px;font-size:.78rem;color:var(--grey-8, #888888);background:var(--dark, #0A0A0A) !important;padding:7px 11px;border:1px solid var(--steel, #1A1A1A);margin-bottom:5px}
.ei-ml{color:var(--grey-5, #555555);font-weight:700;min-width:52px}
.ei-mv{color:var(--grey-b, #BBBBBB)}
.ei-mdet{font-size:.82rem;color:var(--text-dim, #777777);line-height:1.8;border-top:1px solid var(--steel, #1A1A1A);padding-top:11px;margin-top:8px}
.ei-mdet:empty{display:none}
.ei-modal::-webkit-scrollbar{width:3px}
.ei-modal::-webkit-scrollbar-thumb{background:var(--fire, #CC2200)}

/* ===== RESPONSIVE ===== */
@media(max-width:700px){
  .ei-form-inner{flex-direction:column}
  .ei-selects{flex-wrap:wrap}
  .ei-stats{gap:8px}
  .ei-stat{min-width:55px;padding:8px 10px}
  .ei-stat-n{font-size:1.2rem}
  .ei-th-ex,.ei-td-ex{display:none}
  .ei-pag{flex-direction:column;align-items:flex-start}
  .ei-hero-content{flex-direction:column;align-items:flex-start}
}
@media(max-width:480px){
  .ei-th-art,.ei-td-art{display:none}
}
</style>

<script>
(function(){
  var D=<?php echo $jd; ?>;
  var ov=document.getElementById('ei-ov');
  var bx=document.getElementById('ei-modal');
  var mx=document.getElementById('ei-mx');
  var pv=null;
  function fmt(n){return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
  function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
  function open(i){
    var d=D[i];if(!d)return;pv=document.activeElement;
    document.getElementById('ei-manio').textContent=d.anio;
    document.getElementById('ei-mftxt').textContent=d.fecha+(d.hace>0?' · Hace '+d.hace+' año'+(d.hace!==1?'s':''):'');
    document.getElementById('ei-mtit').textContent=d.titulo;
    document.getElementById('ei-mart').textContent=d.artista?'🎸 '+d.artista:'';
    var b=document.getElementById('ei-mbadge');b.textContent=d.cat;b.style.color=d.catColor;b.style.background=d.catBg;
    document.getElementById('ei-mdesc').textContent=d.desc;
    var ex=document.getElementById('ei-mext');ex.innerHTML='';
    if(d.disco)ex.innerHTML+='<div class="ei-mrow"><span class="ei-ml">Disco:</span><span class="ei-mv">&#128191; '+esc(d.disco)+'</span></div>';
    if(d.lugar){var a=d.asist>0?' · '+fmt(d.asist)+' asistentes':'';ex.innerHTML+='<div class="ei-mrow"><span class="ei-ml">Lugar:</span><span class="ei-mv">&#128205; '+esc(d.lugar)+a+'</span></div>';}
    document.getElementById('ei-mdet').innerHTML=d.detalle||'';
    bx.scrollTop=0;ov.classList.add('ei-on');document.body.style.overflow='hidden';
    setTimeout(function(){mx.focus();},300);
  }
  function close(){ov.classList.remove('ei-on');document.body.style.overflow='';if(pv)setTimeout(function(){pv.focus();},100);}
  document.querySelectorAll('.ei-row').forEach(function(el){
    el.addEventListener('click',function(){open(parseInt(el.dataset.idx));});
    el.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();open(parseInt(el.dataset.idx));}});
  });
  mx.addEventListener('click',close);
  ov.addEventListener('click',function(e){if(e.target===ov)close();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&ov.classList.contains('ei-on'))close();});
  bx.addEventListener('keydown',function(e){
    if(e.key!=='Tab')return;
    var f=bx.querySelectorAll('button,[href],[tabindex]:not([tabindex="-1"])');
    if(!f.length)return;var fi=f[0],la=f[f.length-1];
    if(e.shiftKey){if(document.activeElement===fi){e.preventDefault();la.focus();}}
    else{if(document.activeElement===la){e.preventDefault();fi.focus();}}
  });
})();
</script>
