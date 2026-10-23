<?php defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
$cur      = Uri::current();
$siteRoot = rtrim(Uri::root(),'/');
$totalPags = $this->limit>0 ? (int)ceil($this->total/$this->limit) : 1;
$pagActual = $this->limit>0 ? (int)floor($this->start/$this->limit)+1 : 1;

$makeUrl = static function(array $extra=[]) use ($cur, &$that): string {
    return '';
};
// Construir URLs con las propiedades del view
$makeUrl = function(array $extra=[]) use ($cur): string {
    $p = ['view'=>'musicos'];
    if (!empty($GLOBALS['_mu_search'])) $p['search']=$GLOBALS['_mu_search'];
    if (!empty($GLOBALS['_mu_banda']))  $p['banda'] =$GLOBALS['_mu_banda'];
    foreach($extra as $k=>$v){ if($v===null||$v==='') unset($p[$k]); else $p[$k]=$v; }
    return $cur.'?'.http_build_query($p);
};
$GLOBALS['_mu_search'] = $this->search;
$GLOBALS['_mu_banda']  = $this->bandaFiltro ?: '';
?>
<div class="hmb-list-wrap">
  <div class="hmb-list-hero" style="border-top-color:#d4af37">
    <h1 class="hmb-list-h1" style="color:#d4af37">Músicos<span style="color:#fff"> &amp; Artistas</span></h1>
    <span class="hmb-list-total"><?php echo $this->total; ?> registros</span>
  </div>

  <form method="get" action="<?php echo htmlspecialchars($cur); ?>" class="hmb-list-form" id="mlf">
    <input type="hidden" name="view" value="musicos">
    <div class="hmb-list-form-row">
      <div class="hmb-list-search-g">
        <input type="text" name="search" value="<?php echo htmlspecialchars($this->search); ?>"
               placeholder="Buscar músico o banda..." class="hmb-list-input" autocomplete="off">
        <button type="submit" class="hmb-list-sbtn">&#128269;</button>
      </div>
      <select name="banda" class="hmb-list-sel" onchange="document.getElementById('mlf').submit()">
        <option value="">Todas las bandas</option>
        <?php foreach($this->bandas as $bd): ?>
        <option value="<?php echo $bd->id; ?>" <?php echo $this->bandaFiltro===(int)$bd->id?'selected':''; ?>>
          <?php echo htmlspecialchars($bd->nombre); ?>
        </option>
        <?php endforeach; ?>
      </select>
      <?php if($this->search || $this->bandaFiltro): ?>
      <a href="<?php echo htmlspecialchars($cur); ?>?view=musicos" class="hmb-list-clr">&#10005; Limpiar</a>
      <?php endif; ?>
    </div>
  </form>

  <?php if(empty($this->items)): ?>
  <div class="hmb-list-empty"><p>No se encontraron músicos.</p></div>
  <?php else: ?>

  <div class="hmb-musicos-grid">
    <?php foreach($this->items as $m):
      $foto=trim((string)($m->foto??''));
      if($foto&&strpos($foto,'http')!==0) $foto=$siteRoot.'/'.ltrim($foto,'/');
      $nacAnio=!empty($m->fecha_nacimiento)&&$m->fecha_nacimiento!=='0000-00-00'?date('Y',strtotime($m->fecha_nacimiento)):null;
      $defAnio=!empty($m->fecha_defuncion) &&$m->fecha_defuncion !=='0000-00-00'?date('Y',strtotime($m->fecha_defuncion)):null;
      $esSolista=(int)($m->solista??0)===1;
      $banda=$esSolista?'':($m->banda_nombre??$m->banda_texto??'');
    ?>
    <a href="<?php echo htmlspecialchars(rtrim($this->componentBase,'/').'?view=musico&id='.(int)$m->id); ?>"
       class="hmb-musico-card<?php echo $defAnio?' hmb-musico-card--def':''; ?>">
      <div class="hmb-musico-foto-w">
        <?php if($foto): ?>
        <img src="<?php echo htmlspecialchars($foto); ?>" alt="" class="hmb-musico-foto"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="hmb-musico-ph" style="display:none">&#127928;</div>
        <?php else: ?>
        <div class="hmb-musico-ph">&#127928;</div>
        <?php endif; ?>
        <?php if($defAnio): ?><span class="hmb-musico-rip">&#10013;</span><?php endif; ?>
        <?php if($esSolista): ?><span class="hmb-musico-sol">&#9733;</span><?php endif; ?>
      </div>
      <div class="hmb-musico-data">
        <span class="hmb-musico-nombre"><?php echo htmlspecialchars($m->nombre); ?></span>
        <?php if($esSolista): ?><span class="hmb-musico-banda hmb-musico-sol-txt">&#9733; Solista</span>
        <?php elseif($banda): ?><span class="hmb-musico-banda"><?php echo htmlspecialchars($banda); ?></span><?php endif; ?>
        <?php if($nacAnio): ?><span class="hmb-musico-anios"><?php echo $nacAnio; echo $defAnio?' – '.$defAnio:''; ?></span><?php endif; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Paginación -->
  <div class="hmb-list-pag">
    <span class="hmb-list-pag-info"><?php echo $this->start+1; ?>–<?php echo min($this->start+$this->limit,$this->total); ?> de <strong style="color:#d4af37"><?php echo $this->total; ?></strong></span>
    <div class="hmb-list-pag-nav">
      <?php if($pagActual>1): ?>
      <a href="<?php echo htmlspecialchars($cur.'?view=musicos&limitstart=0'.($this->search?'&search='.urlencode($this->search):'').($this->bandaFiltro?'&banda='.$this->bandaFiltro:'')); ?>" class="hmb-pb">&laquo;</a>
      <a href="<?php echo htmlspecialchars($cur.'?view=musicos&limitstart='.(($pagActual-2)*$this->limit).($this->search?'&search='.urlencode($this->search):'').($this->bandaFiltro?'&banda='.$this->bandaFiltro:'')); ?>" class="hmb-pb">&lsaquo;</a>
      <?php endif; ?>
      <?php for($p=max(1,$pagActual-2);$p<=min($totalPags,$pagActual+2);$p++):
        $pStart=($p-1)*$this->limit;
        $pUrl=$cur.'?view=musicos&limitstart='.$pStart.($this->search?'&search='.urlencode($this->search):'').($this->bandaFiltro?'&banda='.$this->bandaFiltro:'');
      ?>
      <a href="<?php echo htmlspecialchars($pUrl); ?>" class="hmb-pb<?php echo $p===$pagActual?' hmb-pb-on':''; ?>"><?php echo $p; ?></a>
      <?php endfor; ?>
      <?php if($pagActual<$totalPags):
        $nStart=$pagActual*$this->limit;
        $lStart=($totalPags-1)*$this->limit;
        $base=$cur.'?view=musicos'.($this->search?'&search='.urlencode($this->search):'').($this->bandaFiltro?'&banda='.$this->bandaFiltro:'');
      ?>
      <a href="<?php echo htmlspecialchars($base.'&limitstart='.$nStart); ?>" class="hmb-pb">&rsaquo;</a>
      <a href="<?php echo htmlspecialchars($base.'&limitstart='.$lStart); ?>" class="hmb-pb">&raquo;</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="hmb-ficha-footer">
    <a href="<?php echo $cur; ?>?view=bandas" class="hmb-lnk">&#127928; Ver bandas</a>
    &nbsp;·&nbsp;
    <a href="<?php echo $cur; ?>?view=efemerides" class="hmb-lnk">Efemérides del día</a>
  </div>
</div>

<style>
.hmb-list-wrap{max-width:1100px;margin:0 auto;font-family:inherit}
.hmb-list-hero{background:#0a0a0a;border:1px solid #1e1e1e;border-top:4px solid #d4af37;padding:18px 22px;display:flex;align-items:baseline;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:0}
.hmb-list-h1{font-size:1.4rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em;margin:0}
.hmb-list-total{font-size:.7rem;color:#555;letter-spacing:.1em}
.hmb-list-form{background:#0d0d0d;border:1px solid #1a1a1a;border-top:none;padding:10px 14px;margin-bottom:16px}
.hmb-list-form-row{display:flex;flex-wrap:wrap;gap:7px;align-items:center}
.hmb-list-search-g{flex:1;min-width:200px;display:flex;background:#111;border:1px solid #222}
.hmb-list-search-g:focus-within{border-color:#d4af37}
.hmb-list-input{flex:1;background:transparent;border:none;color:#ddd;padding:8px 11px;font-size:.8rem;outline:none}
.hmb-list-sbtn{background:#d4af37;border:none;color:#000;padding:8px 12px;cursor:pointer;font-weight:700;font-size:.9rem}
.hmb-list-sel{background:#111;border:1px solid #222;color:#aaa;padding:8px 10px;font-size:.78rem;cursor:pointer;outline:none}
.hmb-list-sel:focus{border-color:#d4af37}
.hmb-list-clr{padding:6px 12px;background:#1a1a1a;border:1px solid #333;color:#888;text-decoration:none;font-size:.72rem;font-weight:700}
.hmb-list-clr:hover{border-color:#d4af37;color:#d4af37}
.hmb-musicos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin-bottom:16px}
.hmb-musico-card{display:flex;flex-direction:column;align-items:center;background:#111;border:1px solid #1e1e1e;padding:16px 10px 12px;text-decoration:none;transition:all .2s;position:relative}
.hmb-musico-card:hover{background:#1a0a0a;border-color:#cc0000}
.hmb-musico-card--def{opacity:.8}
.hmb-musico-foto-w{position:relative;margin-bottom:10px}
.hmb-musico-foto{width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid #2a2a2a;display:block}
.hmb-musico-ph{width:80px;height:80px;border-radius:50%;background:#1a1a1a;border:2px solid #2a2a2a;display:flex;align-items:center;justify-content:center;font-size:1.8rem}
.hmb-musico-rip{position:absolute;bottom:0;right:0;background:#cc0000;color:#fff;font-size:.6rem;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center}
.hmb-musico-sol{position:absolute;top:0;right:0;background:#d4af37;color:#000;font-size:.55rem;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900}
.hmb-musico-sol-txt{color:#d4af37 !important;font-style:italic}
.hmb-musico-nombre{font-size:.82rem;font-weight:700;color:#e0e0e0;text-align:center;display:block;margin-bottom:4px;line-height:1.3}
.hmb-musico-banda{font-size:.7rem;color:#d4af37;text-align:center;display:block}
.hmb-musico-anios{font-size:.65rem;color:#555;text-align:center;display:block;margin-top:3px}
.hmb-list-pag{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:12px 0;border-top:1px solid #1a1a1a;margin-bottom:14px}
.hmb-list-pag-info{font-size:.75rem;color:#555}
.hmb-list-pag-nav{display:flex;gap:3px}
.hmb-pb{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 6px;background:#111;border:1px solid #1e1e1e;color:#777;text-decoration:none;font-size:.75rem;font-weight:700;transition:all .2s}
.hmb-pb:hover{border-color:#d4af37;color:#d4af37}
.hmb-pb-on{background:#d4af37 !important;border-color:#d4af37 !important;color:#000 !important}
.hmb-list-empty{padding:40px;text-align:center;color:#666;background:#0d0d0d;border:1px solid #1e1e1e;margin-bottom:14px}
.hmb-ficha-footer{text-align:center;padding:12px;border-top:1px solid #1a1a1a;font-size:.76rem}
.hmb-lnk{color:#cc0000;text-decoration:none;font-weight:700;letter-spacing:.05em;text-transform:uppercase;font-size:.72rem}
.hmb-lnk:hover{color:#ff2222}
@media(max-width:500px){.hmb-musicos-grid{grid-template-columns:repeat(auto-fill,minmax(120px,1fr))}}
</style>
