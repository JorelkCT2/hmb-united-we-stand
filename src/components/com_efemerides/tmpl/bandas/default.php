<?php defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
$cur      = Uri::current();
$siteRoot = rtrim(Uri::root(),'/');
$totalPags = $this->limit>0 ? (int)ceil($this->total/$this->limit) : 1;
$pagActual = $this->limit>0 ? (int)floor($this->start/$this->limit)+1 : 1;
$base = $cur.'?view=bandas'.($this->search?'&search='.urlencode($this->search):'').($this->generoFiltro?'&genero='.urlencode($this->generoFiltro):'');
?>
<div class="hmb-list-wrap">
  <div class="hmb-list-hero" style="border-top-color:#cc0000">
    <h1 class="hmb-list-h1">Bandas<span style="color:#cc0000"> Rock &amp; Metal</span></h1>
    <span class="hmb-list-total"><?php echo $this->total; ?> bandas</span>
  </div>

  <form method="get" action="<?php echo htmlspecialchars($cur); ?>" class="hmb-list-form" id="blf">
    <input type="hidden" name="view" value="bandas">
    <div class="hmb-list-form-row">
      <div class="hmb-list-search-g" style="border-color:#222">
        <input type="text" name="search" value="<?php echo htmlspecialchars($this->search); ?>"
               placeholder="Buscar banda, país, género..." class="hmb-list-input" autocomplete="off">
        <button type="submit" class="hmb-list-sbtn" style="background:#cc0000;color:#fff">&#128269;</button>
      </div>
      <select name="genero" class="hmb-list-sel" onchange="document.getElementById('blf').submit()">
        <option value="">Todos los géneros</option>
        <?php foreach($this->generos as $g): ?>
        <option value="<?php echo htmlspecialchars($g); ?>" <?php echo $this->generoFiltro===$g?'selected':''; ?>>
          <?php echo htmlspecialchars($g); ?>
        </option>
        <?php endforeach; ?>
      </select>
      <?php if($this->search || $this->generoFiltro): ?>
      <a href="<?php echo $cur; ?>?view=bandas" class="hmb-list-clr">&#10005; Limpiar</a>
      <?php endif; ?>
    </div>
  </form>

  <?php if(empty($this->items)): ?>
  <div class="hmb-list-empty"><p>No se encontraron bandas.</p></div>
  <?php else: ?>

  <div class="hmb-bandas-grid">
    <?php foreach($this->items as $b):
      $foto=trim((string)($b->foto??''));
      if($foto&&strpos($foto,'http')!==0) $foto=$siteRoot.'/'.ltrim($foto,'/');
      $anoForm=!empty($b->fecha_formacion)&&$b->fecha_formacion!=='0000-00-00'?date('Y',strtotime($b->fecha_formacion)):null;
      $anoDisol=!empty($b->fecha_disolucion)&&$b->fecha_disolucion!=='0000-00-00'?date('Y',strtotime($b->fecha_disolucion)):null;
    ?>
    <a href="<?php echo htmlspecialchars($cur); ?>?view=banda&id=<?php echo $b->id; ?>"
       class="hmb-banda-card<?php echo $anoDisol?' hmb-banda-card--dis':''; ?>">
      <div class="hmb-banda-logo-c">
        <?php if($foto): ?>
        <img src="<?php echo htmlspecialchars($foto); ?>" alt="" class="hmb-banda-logo-img"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="hmb-banda-logo-ph" style="display:none">&#127928;</div>
        <?php else: ?>
        <div class="hmb-banda-logo-ph">&#127928;</div>
        <?php endif; ?>
      </div>
      <div class="hmb-banda-card-data">
        <span class="hmb-banda-card-nombre"><?php echo htmlspecialchars($b->nombre); ?></span>
        <?php if($b->genero): ?><span class="hmb-banda-card-genero"><?php echo htmlspecialchars(mb_substr($b->genero,0,28)); ?></span><?php endif; ?>
        <div class="hmb-banda-card-meta">
          <?php if($b->pais): ?><span>&#127758; <?php echo htmlspecialchars($b->pais); ?></span><?php endif; ?>
          <?php if($anoForm): ?><span><?php echo $anoForm; echo $anoDisol?' – '.$anoDisol:' – act.'; ?></span><?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="hmb-list-pag">
    <span class="hmb-list-pag-info"><?php echo $this->start+1; ?>–<?php echo min($this->start+$this->limit,$this->total); ?> de <strong style="color:#cc0000"><?php echo $this->total; ?></strong></span>
    <div class="hmb-list-pag-nav">
      <?php if($pagActual>1): ?>
      <a href="<?php echo htmlspecialchars($base.'&limitstart=0'); ?>" class="hmb-pb">&laquo;</a>
      <a href="<?php echo htmlspecialchars($base.'&limitstart='.(($pagActual-2)*$this->limit)); ?>" class="hmb-pb">&lsaquo;</a>
      <?php endif; ?>
      <?php for($p=max(1,$pagActual-2);$p<=min($totalPags,$pagActual+2);$p++): ?>
      <a href="<?php echo htmlspecialchars($base.'&limitstart='.(($p-1)*$this->limit)); ?>"
         class="hmb-pb<?php echo $p===$pagActual?' hmb-pb-on':''; ?>"><?php echo $p; ?></a>
      <?php endfor; ?>
      <?php if($pagActual<$totalPags): ?>
      <a href="<?php echo htmlspecialchars($base.'&limitstart='.($pagActual*$this->limit)); ?>" class="hmb-pb">&rsaquo;</a>
      <a href="<?php echo htmlspecialchars($base.'&limitstart='.(($totalPags-1)*$this->limit)); ?>" class="hmb-pb">&raquo;</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="hmb-ficha-footer">
    <a href="<?php echo $cur; ?>?view=musicos" class="hmb-lnk">&#127928; Ver músicos</a>
    &nbsp;·&nbsp;
    <a href="<?php echo $cur; ?>?view=efemerides" class="hmb-lnk">Efemérides del día</a>
  </div>
</div>

<style>
.hmb-list-wrap{max-width:1100px;margin:0 auto;font-family:inherit}
.hmb-list-hero{background:#0a0a0a;border:1px solid #1e1e1e;border-top:4px solid #cc0000;padding:18px 22px;display:flex;align-items:baseline;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:0}
.hmb-list-h1{font-size:1.4rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em;margin:0;color:#fff}
.hmb-list-total{font-size:.7rem;color:#555;letter-spacing:.1em}
.hmb-list-form{background:#0d0d0d;border:1px solid #1a1a1a;border-top:none;padding:10px 14px;margin-bottom:16px}
.hmb-list-form-row{display:flex;flex-wrap:wrap;gap:7px;align-items:center}
.hmb-list-search-g{flex:1;min-width:200px;display:flex;background:#111;border:1px solid #222}
.hmb-list-search-g:focus-within{border-color:#cc0000}
.hmb-list-input{flex:1;background:transparent;border:none;color:#ddd;padding:8px 11px;font-size:.8rem;outline:none}
.hmb-list-sel{background:#111;border:1px solid #222;color:#aaa;padding:8px 10px;font-size:.78rem;cursor:pointer;outline:none}
.hmb-list-clr{padding:6px 12px;background:#1a1a1a;border:1px solid #333;color:#888;text-decoration:none;font-size:.72rem;font-weight:700}
.hmb-list-clr:hover{border-color:#cc0000;color:#cc0000}
.hmb-bandas-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px;margin-bottom:16px}
.hmb-banda-card{display:flex;flex-direction:column;background:#111;border:1px solid #1e1e1e;text-decoration:none;transition:all .2s;overflow:hidden}
.hmb-banda-card:hover{border-color:#cc0000}
.hmb-banda-card--dis{opacity:.75}
.hmb-banda-logo-c{background:#0a0a0a;height:110px;display:flex;align-items:center;justify-content:center;padding:12px;border-bottom:1px solid #1e1e1e}
.hmb-banda-logo-img{max-height:85px;max-width:160px;object-fit:contain;filter:brightness(.9);display:block}
.hmb-banda-logo-ph{font-size:2.5rem;color:#2a2a2a;width:100%;height:100%;display:flex;align-items:center;justify-content:center}
.hmb-banda-card-data{padding:10px 12px}
.hmb-banda-card-nombre{display:block;font-size:.9rem;font-weight:800;color:#e0e0e0;margin-bottom:4px}
.hmb-banda-card-genero{display:block;font-size:.68rem;color:#d4af37;margin-bottom:6px;font-weight:600}
.hmb-banda-card-meta{display:flex;gap:10px;font-size:.68rem;color:#555;flex-wrap:wrap}
.hmb-list-pag{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:12px 0;border-top:1px solid #1a1a1a;margin-bottom:14px}
.hmb-list-pag-info{font-size:.75rem;color:#555}
.hmb-list-pag-nav{display:flex;gap:3px}
.hmb-pb{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 6px;background:#111;border:1px solid #1e1e1e;color:#777;text-decoration:none;font-size:.75rem;font-weight:700;transition:all .2s}
.hmb-pb:hover{border-color:#cc0000;color:#cc0000}
.hmb-pb-on{background:#cc0000 !important;border-color:#cc0000 !important;color:#fff !important}
.hmb-list-empty{padding:40px;text-align:center;color:#666;background:#0d0d0d;border:1px solid #1e1e1e;margin-bottom:14px}
.hmb-ficha-footer{text-align:center;padding:12px;border-top:1px solid #1a1a1a;font-size:.76rem}
.hmb-lnk{color:#cc0000;text-decoration:none;font-weight:700;letter-spacing:.05em;text-transform:uppercase;font-size:.72rem}
.hmb-lnk:hover{color:#ff2222}
@media(max-width:500px){.hmb-bandas-grid{grid-template-columns:repeat(auto-fill,minmax(150px,1fr))}}
</style>
