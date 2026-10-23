<?php defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$db   = Factory::getContainer()->get('DatabaseDriver');
$ext  = ['jpg','jpeg','png','gif','webp','avif'];
$skip = ['thumbs','.thumbs','cache','tmp','sampledata'];

// Álbumes de BD
try {
    $q = $db->getQuery(true)
        ->select(['id','title','description','folder_path','cover_image'])
        ->from('#__hmgallery_albums')->where('state=1')->order('ordering ASC, title ASC');
    $dbAlbums = $db->setQuery($q)->loadObjectList() ?: [];
} catch(\Exception) { $dbAlbums = []; }

// Fallback filesystem
if (empty($dbAlbums)) {
    foreach (@scandir(JPATH_ROOT.'/images') ?: [] as $item) {
        if ($item==='.'||$item==='..'||str_starts_with($item,'.')) continue;
        if (in_array(strtolower($item),$skip,true)) continue;
        $full = JPATH_ROOT.'/images/'.$item;
        if (!is_dir($full)) continue;
        $rel = 'images/'.$item; $cover = null;
        foreach (@scandir($full)?:[] as $f) {
            if (in_array(strtolower(pathinfo($f,PATHINFO_EXTENSION)),$ext,true)) { $cover=$rel.'/'.$f; break; }
        }
        $alb=new \stdClass(); $alb->id=abs(crc32($rel))%999999;
        $alb->title=ucwords(str_replace(['-','_'],' ',$item));
        $alb->description=''; $alb->folder_path=$rel; $alb->cover_image=$cover;
        $dbAlbums[]=$alb;
    }
}

// Construir datos con fotos — todo en PHP, nada en JS
$albums = [];
foreach ($dbAlbums as $alb) {
    $photos = [];
    $abs = JPATH_ROOT.'/'.ltrim($alb->folder_path,'/');
    if (is_dir($abs)) {
        foreach (@scandir($abs)?:[] as $f) {
            if ($f==='.'||$f==='..') continue;
            if (!is_file($abs.'/'.$f)) continue;
            if (!in_array(strtolower(pathinfo($f,PATHINFO_EXTENSION)),$ext,true)) continue;
            $rel = ltrim($alb->folder_path.'/'.$f,'/');
            $photos[] = ['url'=>Uri::root().str_replace(' ','%20',$rel), 'title'=>htmlspecialchars(pathinfo($f,PATHINFO_FILENAME),ENT_QUOTES,'UTF-8')];
        }
    }
    usort($photos, fn($a,$b)=>strcmp($a['url'],$b['url']));

    $coverRaw = $alb->cover_image ?? '';
    $clean = strpos($coverRaw,'#')!==false ? substr($coverRaw,0,strpos($coverRaw,'#')) : $coverRaw;
    $cover = $clean ? (str_starts_with($clean,'http') ? $clean : Uri::root().str_replace(' ','%20',ltrim($clean,'/'))) : ($photos[0]['url'] ?? '');

    $albums[] = [
        'id'     => (int)$alb->id,
        'title'  => htmlspecialchars(strip_tags($alb->title ?? ''), ENT_QUOTES, 'UTF-8'),
        'desc'   => htmlspecialchars(strip_tags($alb->description ?? ''), ENT_QUOTES, 'UTF-8'),
        'cover'  => htmlspecialchars($cover, ENT_QUOTES, 'UTF-8'),
        'photos' => $photos,
    ];
}
?>
<style>
.hmg-wrap{background:#000;color:#ccc;font-family:'Share Tech Mono',monospace;min-height:100vh}
.hmg-hdr{text-align:center;padding:2rem 1rem 1.5rem;background:linear-gradient(180deg,#110000,#000);border-bottom:1px solid #330000}
.hmg-hdr__skull{font-size:2.5rem;display:block;filter:drop-shadow(0 0 12px #cc0000);animation:sk 3s ease-in-out infinite}
@keyframes sk{0%,100%{transform:scale(1)}50%{transform:scale(1.08)}}
.hmg-hdr__ttl{font-family:'Metal Mania',cursive;font-size:clamp(1.8rem,5vw,3.5rem);color:#cc0000;text-shadow:0 0 20px rgba(204,0,0,.5);margin:.3rem 0}
.hmg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.2rem;padding:1.5rem;max-width:1400px;margin:0 auto}
.hmg-card{background:#111;border:1px solid #222;border-top:3px solid #cc0000;cursor:pointer;transition:all .2s;overflow:hidden}
.hmg-card:hover{border-color:#cc0000;box-shadow:0 0 20px rgba(204,0,0,.3);transform:translateY(-3px)}
.hmg-card__img{aspect-ratio:4/3;overflow:hidden;position:relative;background:#0a0a0a}
.hmg-card__img img{width:100%;height:100%;object-fit:cover;transition:transform .4s,filter .3s;filter:grayscale(20%)}
.hmg-card:hover .hmg-card__img img{transform:scale(1.05);filter:grayscale(0%)}
.hmg-card__ph{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;color:#330000}
.hmg-card__ov{position:absolute;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .3s}
.hmg-card:hover .hmg-card__ov{opacity:1}
.hmg-card__ov span{font-family:'Metal Mania',cursive;color:#cc0000;font-size:1rem;letter-spacing:.1em;border:1px solid #cc0000;padding:.4rem 1rem;background:rgba(0,0,0,.8)}
.hmg-card__info{padding:.8rem 1rem}
.hmg-card__ttl{font-family:'Cinzel',serif;font-size:1rem;font-weight:700;color:#ccc;text-transform:uppercase;margin:0 0 .3rem}
.hmg-card:hover .hmg-card__ttl{color:#cc0000}
.hmg-card__dsc{font-size:.75rem;color:#666;margin:0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.hmg-alb{display:none;max-width:1400px;margin:0 auto;padding:1.5rem}
.hmg-alb.on{display:block}
.hmg-back{display:inline-flex;align-items:center;gap:.5rem;margin-bottom:1.2rem;padding:.5rem 1.2rem;background:transparent;border:1px solid #330000;color:#cc0000;font-family:'Cinzel',serif;font-size:.8rem;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;transition:all .2s}
.hmg-back:hover{background:#330000;border-color:#cc0000}
.hmg-alb__ttl{font-family:'Metal Mania',cursive;font-size:2rem;color:#cc0000;text-shadow:0 0 15px rgba(204,0,0,.4);margin:0 0 .3rem}
.hmg-alb__dsc{color:#777;font-size:.85rem;margin-bottom:1rem}
.hmg-tbar{display:flex;align-items:center;margin-bottom:1rem;padding:.5rem .8rem;background:#111;border:1px solid #1a1a1a;font-family:'Cinzel',serif;color:#555;font-size:.8rem}
.hmg-photos{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.8rem}
.hmg-photo{position:relative;aspect-ratio:4/3;overflow:hidden;background:#111;cursor:pointer;border:1px solid #1a1a1a}
.hmg-photo:hover{border-color:#cc0000;box-shadow:0 0 10px rgba(204,0,0,.3)}
.hmg-photo img{width:100%;height:100%;object-fit:cover;transition:transform .4s,filter .3s;filter:grayscale(15%)}
.hmg-photo:hover img{transform:scale(1.05);filter:grayscale(0%) brightness(.85)}
.hmg-photo__ov{position:absolute;inset:0;background:rgba(204,0,0,.15);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .3s;font-size:2rem;color:#fff}
.hmg-photo:hover .hmg-photo__ov{opacity:1}
.hmg-photo__cap{position:absolute;bottom:0;left:0;right:0;padding:.25rem .4rem;background:rgba(0,0,0,.8);font-size:.65rem;color:#888;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transform:translateY(100%);transition:transform .3s}
.hmg-photo:hover .hmg-photo__cap{transform:translateY(0)}
.hmg-lb{position:fixed;inset:0;z-index:9999;display:none;flex-direction:column;align-items:center;justify-content:center}
.hmg-lb.on{display:flex}
.hmg-lb__bg{position:absolute;inset:0;background:rgba(0,0,0,.95)}
.hmg-lb__box{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center}
.hmg-lb__img{max-width:calc(100vw - 6rem);max-height:calc(100vh - 8rem);object-fit:contain;box-shadow:0 0 40px rgba(204,0,0,.3)}
.hmg-lb__close{position:fixed;top:1rem;right:1.5rem;background:none;border:1px solid #cc0000;color:#cc0000;font-size:1.4rem;width:2.5rem;height:2.5rem;cursor:pointer;display:flex;align-items:center;justify-content:center}
.hmg-lb__close:hover{background:#cc0000;color:#fff}
.hmg-lb__nav{position:fixed;top:50%;transform:translateY(-50%);background:rgba(0,0,0,.7);border:1px solid #330000;color:#cc0000;font-size:1.8rem;padding:.7rem .5rem;cursor:pointer}
.hmg-lb__nav:hover{border-color:#cc0000}
.hmg-lb__prev{left:.5rem}.hmg-lb__next{right:.5rem}
.hmg-lb__cap{margin-top:.8rem;font-family:'Cinzel',serif;color:#aaa;font-size:.85rem}
.hmg-lb__cnt{color:#555;font-size:.75rem}
@media(max-width:600px){.hmg-grid{grid-template-columns:1fr}.hmg-photos{grid-template-columns:repeat(2,1fr)}}
</style>

<div class="hmg-wrap">
<header class="hmg-hdr">
  <span class="hmg-hdr__skull">&#9760;</span>
  <div class="hmg-hdr__ttl">&#9658; Galería de Fotos &#9668;</div>
  <div>&#128293;&#128293;&#128293;</div>
</header>

<!-- ROOT -->
<div id="hmg-root">
<?php if (empty($albums)): ?>
  <div style="text-align:center;padding:4rem;color:#555"><span style="font-size:3rem;display:block">&#128128;</span>No se encontraron álbumes.</div>
<?php else: ?>
  <div class="hmg-grid">
  <?php foreach ($albums as $i => $alb): ?>
    <article class="hmg-card" onclick="hmgOpen(<?= $i ?>)">
      <div class="hmg-card__img">
        <?php if ($alb['cover']): ?>
          <img src="<?= $alb['cover'] ?>" alt="<?= $alb['title'] ?>" loading="lazy"/>
        <?php else: ?>
          <div class="hmg-card__ph">&#9760;</div>
        <?php endif; ?>
        <div class="hmg-card__ov"><span>ENTRAR &#9654;</span></div>
      </div>
      <div class="hmg-card__info">
        <h2 class="hmg-card__ttl"><?= $alb['title'] ?></h2>
        <?php if ($alb['desc']): ?><p class="hmg-card__dsc"><?= $alb['desc'] ?></p><?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
<?php endif; ?>
</div>

<!-- ALBUM VIEWS — pre-renderizadas en PHP, sin JSON -->
<?php foreach ($albums as $i => $alb): ?>
<div class="hmg-alb" id="hmg-alb-<?= $i ?>">
  <button class="hmg-back" onclick="hmgBack()">&#9664; Volver a la Galería</button>
  <div class="hmg-alb__ttl"><?= $alb['title'] ?></div>
  <?php if ($alb['desc']): ?><div class="hmg-alb__dsc"><?= $alb['desc'] ?></div><?php endif; ?>
  <div class="hmg-tbar">&#9760; <?= count($alb['photos']) ?> Fotos</div>
  <div class="hmg-photos">
  <?php foreach ($alb['photos'] as $j => $p): ?>
    <div class="hmg-photo" onclick="hmgLb(<?= $i ?>,<?= $j ?>)">
      <img src="<?= $p['url'] ?>" alt="<?= $p['title'] ?>"/>
      <div class="hmg-photo__ov">&#8853;</div>
      <span class="hmg-photo__cap"><?= $p['title'] ?></span>
    </div>
  <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<!-- LIGHTBOX -->
<div class="hmg-lb" id="hmg-lb">
  <div class="hmg-lb__bg" onclick="hmgLbClose()"></div>
  <div class="hmg-lb__box">
    <button class="hmg-lb__close" onclick="hmgLbClose()">&#10005;</button>
    <button class="hmg-lb__nav hmg-lb__prev" onclick="hmgLbNav(-1)">&#10094;</button>
    <button class="hmg-lb__nav hmg-lb__next" onclick="hmgLbNav(1)">&#10095;</button>
    <img class="hmg-lb__img" id="hmg-lb-img" src="" alt=""/>
    <div class="hmg-lb__cap" id="hmg-lb-cap"></div>
    <div class="hmg-lb__cnt" id="hmg-lb-cnt"></div>
  </div>
</div>
</div>

<?php
// Photo data for lightbox — only URLs and titles, safe strings, no HTML
$lbData = [];
foreach ($albums as $alb) {
    $lbData[] = array_map(fn($p) => [$p['url'], $p['title']], $alb['photos']);
}
// Use data attribute to avoid ANY risk of breaking script tag
$safeJson = base64_encode(json_encode($lbData));
?>
<div id="hmg-data" data-albums="<?= $safeJson ?>" style="display:none"></div>

<script>
var _d = JSON.parse(atob(document.getElementById('hmg-data').dataset.albums));
var _ci = -1, _pi = -1;

function hmgOpen(i) {
  document.getElementById('hmg-root').style.display = 'none';
  document.getElementById('hmg-alb-'+i).classList.add('on');
  _ci = i;
  window.scrollTo(0,0);
}
function hmgBack() {
  if (_ci >= 0) document.getElementById('hmg-alb-'+_ci).classList.remove('on');
  document.getElementById('hmg-root').style.display = '';
  _ci = -1;
  window.scrollTo(0,0);
}
function hmgLb(ai, pi) {
  _ci = ai; _pi = pi;
  document.getElementById('hmg-lb').classList.add('on');
  document.body.style.overflow = 'hidden';
  hmgLbShow();
}
function hmgLbClose() {
  document.getElementById('hmg-lb').classList.remove('on');
  document.body.style.overflow = '';
}
function hmgLbNav(d) {
  if (_ci < 0) return;
  var photos = _d[_ci];
  _pi = (_pi + d + photos.length) % photos.length;
  hmgLbShow();
}
function hmgLbShow() {
  var p = _d[_ci][_pi];
  document.getElementById('hmg-lb-img').src = p[0];
  document.getElementById('hmg-lb-cap').textContent = p[1];
  document.getElementById('hmg-lb-cnt').textContent = (_pi+1)+' / '+_d[_ci].length;
}
document.addEventListener('keydown', function(e) {
  if (!document.getElementById('hmg-lb').classList.contains('on')) return;
  if (e.key==='ArrowLeft')  { e.preventDefault(); hmgLbNav(-1); }
  if (e.key==='ArrowRight') { e.preventDefault(); hmgLbNav(1); }
  if (e.key==='Escape')     { e.preventDefault(); hmgLbClose(); }
});
var _tx=0;
document.getElementById('hmg-lb').addEventListener('touchstart',function(e){_tx=e.changedTouches[0].clientX;},{passive:true});
document.getElementById('hmg-lb').addEventListener('touchend',function(e){var dx=e.changedTouches[0].clientX-_tx;if(Math.abs(dx)>60)hmgLbNav(dx<0?1:-1);},{passive:true});
</script>
