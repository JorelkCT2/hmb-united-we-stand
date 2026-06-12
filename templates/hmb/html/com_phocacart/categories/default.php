<?php
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$t          = is_array($this->t ?? null) ? $this->t : [];
$categories = array_values($t['categories'] ?? []);
$imgBase    = rtrim(Uri::root(),'/').'/'.ltrim($t['path']['orig_rel_ds'] ?? 'images/phocacartcategories/','/');
$pageTitle  = 'Tienda';
if (!empty($this->p) && method_exists($this->p,'get'))
    $pageTitle = $this->p->get('page_title','Tienda') ?: 'Tienda';
$icons = ['🤘','💿','🎸','👕','🔥','⚡','💀','🎵'];
?>
<div class="hmb-shop-wrap">
<div class="hmb-container">
  <nav class="hmb-shop-breadcrumb">
    <a href="<?php echo Uri::root(); ?>">Inicio</a>
    <span class="sep">›</span>
    <span class="current"><?php echo htmlspecialchars($pageTitle,ENT_QUOTES,'UTF-8'); ?></span>
  </nav>
  <div class="hmb-shop-header">
    <h1><?php echo htmlspecialchars($pageTitle,ENT_QUOTES,'UTF-8'); ?></h1>
  </div>
</div>

<?php if (empty($categories)): ?>
<div class="hmb-container">
  <p style="color:#777;font-family:'Oswald',sans-serif;letter-spacing:2px;text-transform:uppercase;padding:3rem 0;">Sin categorías disponibles</p>
</div>
<?php else: ?>
<div class="hmb-cats-premium-wrap">
  <?php foreach ($categories as $i => $item):
    $title = htmlspecialchars($item->title ?? '',ENT_QUOTES,'UTF-8');
    $desc  = !empty($item->description) ? strip_tags($item->description) : '';
    $img   = !empty($item->image) ? htmlspecialchars($imgBase.$item->image,ENT_QUOTES,'UTF-8') : '';
    $link  = '#';
    if (!empty($item->link)) { try { $link = Route::_($item->link); } catch(\Throwable $e) {} }
    elseif (!empty($item->id)) { try { $link = Route::_('index.php?option=com_phocacart&view=category&id='.(int)$item->id.':'.($item->alias??'')); } catch(\Throwable $e) {} }
  ?>
  <a href="<?php echo $link; ?>" class="hmb-cat-premium" style="--delay:<?php echo $i*120; ?>ms">
    <div class="hmb-cat-p-bg">
      <?php if ($img): ?>
        <div class="hmb-cat-p-img" style="background-image:url('<?php echo $img; ?>')"></div>
      <?php else: ?>
        <div class="hmb-cat-p-img hmb-cat-p-noimg"><?php echo $icons[$i % count($icons)]; ?></div>
      <?php endif; ?>
      <div class="hmb-cat-p-overlay"></div>
    </div>
    <div class="hmb-cat-p-sparks" aria-hidden="true">
      <span class="sp sp1"></span><span class="sp sp2"></span>
      <span class="sp sp3"></span><span class="sp sp4"></span><span class="sp sp5"></span>
    </div>
    <div class="hmb-cat-p-sideline"></div>
    <div class="hmb-cat-p-content">
      <div class="hmb-cat-p-number"><?php printf('%02d',$i+1); ?></div>
      <div class="hmb-cat-p-text">
        <h2 class="hmb-cat-p-title"><?php echo $title; ?></h2>
        <?php if ($desc): ?>
          <p class="hmb-cat-p-desc"><?php echo htmlspecialchars($desc,ENT_QUOTES,'UTF-8'); ?></p>
        <?php endif; ?>
        <div class="hmb-cat-p-cta">
          <span class="hmb-cat-p-cta-text">Ver colección</span>
          <span class="hmb-cat-p-cta-arrow">
            <svg viewBox="0 0 28 8" fill="none"><line x1="0" y1="4" x2="22" y2="4" stroke="currentColor" stroke-width="1.5"/><polyline points="18,1 22,4 18,7" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
          </span>
        </div>
      </div>
    </div>
    <div class="hmb-cat-p-reflection"></div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<style>
.hmb-cats-premium-wrap{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,480px),1fr));gap:0;margin:0 0 4rem}
.hmb-cat-premium{position:relative;display:block;min-height:420px;overflow:hidden;text-decoration:none;opacity:0;transform:translateY(40px) scale(.98);animation:hmb-cat-p-in .7s cubic-bezier(.22,.68,0,1.2) var(--delay,0ms) forwards}
@keyframes hmb-cat-p-in{to{opacity:1;transform:translateY(0) scale(1)}}
.hmb-cat-premium+.hmb-cat-premium{border-left:1px solid rgba(255,255,255,.04)}
@media(max-width:959px){.hmb-cat-premium+.hmb-cat-premium{border-left:none;border-top:1px solid rgba(255,255,255,.04)}}
.hmb-cat-p-bg{position:absolute;inset:0;z-index:0}
.hmb-cat-p-img{position:absolute;inset:-8%;background-size:cover;background-position:center;filter:brightness(.55) saturate(.8);transition:transform .8s cubic-bezier(.25,.46,.45,.94),filter .6s ease;will-change:transform}
.hmb-cat-p-noimg{background:linear-gradient(135deg,#1a1a1a,#0d0000);display:flex;align-items:center;justify-content:center;font-size:5rem;color:#8B0000;filter:none;transition:color .4s}
.hmb-cat-premium:hover .hmb-cat-p-img{transform:scale(1.08) translateY(-2%);filter:brightness(.7) saturate(1.1)}
.hmb-cat-premium:hover .hmb-cat-p-noimg{color:#CC2200}
.hmb-cat-p-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(5,0,0,.95) 0%,rgba(5,0,0,.6) 40%,rgba(5,0,0,.15) 70%,transparent 100%),linear-gradient(to right,rgba(139,0,0,.2) 0%,transparent 50%);transition:background .5s}
.hmb-cat-premium:hover .hmb-cat-p-overlay{background:linear-gradient(to top,rgba(80,0,0,.92) 0%,rgba(20,0,0,.65) 40%,rgba(5,0,0,.2) 70%,transparent 100%),linear-gradient(to right,rgba(204,34,0,.25) 0%,transparent 60%)}
.hmb-cat-p-sideline{position:absolute;left:0;top:0;bottom:0;width:3px;background:linear-gradient(to bottom,transparent,#CC2200,#8B0000,transparent);transform:scaleY(0);transform-origin:top;transition:transform .5s cubic-bezier(.22,.68,0,1.2);z-index:3}
.hmb-cat-premium:hover .hmb-cat-p-sideline{transform:scaleY(1)}
.hmb-cat-p-sparks{position:absolute;bottom:0;left:0;right:0;height:120px;z-index:2;pointer-events:none;overflow:hidden}
.sp{position:absolute;bottom:-10px;width:3px;height:3px;border-radius:50%;background:#CC2200;opacity:0}
.hmb-cat-premium:hover .sp{animation:hmb-spark 1.2s ease-in-out infinite}
.sp1{left:15%}.sp2{left:30%;width:4px;height:4px;animation-delay:.2s!important}.sp3{left:50%;background:#FF4500;animation-delay:.4s!important}.sp4{left:70%;animation-delay:.1s!important}.sp5{left:85%;width:2px;height:2px;animation-delay:.3s!important}
@keyframes hmb-spark{0%{opacity:0;transform:translateY(0)}20%{opacity:.9}80%{opacity:.3}100%{opacity:0;transform:translateY(-90px) scale(.4)}}
.hmb-cat-p-content{position:absolute;bottom:0;left:0;right:0;z-index:4;padding:2.5rem;display:flex;align-items:flex-end;gap:1.5rem;transform:translateY(8px);transition:transform .45s cubic-bezier(.22,.68,0,1.2)}
.hmb-cat-premium:hover .hmb-cat-p-content{transform:translateY(0)}
.hmb-cat-p-number{font-family:'Metal Mania',cursive;font-size:4.5rem;line-height:1;color:rgba(204,34,0,.18);flex-shrink:0;transition:color .4s,transform .4s;user-select:none;transform:translateX(-6px)}
.hmb-cat-premium:hover .hmb-cat-p-number{color:rgba(204,34,0,.35);transform:translateX(0)}
.hmb-cat-p-text{flex:1}
.hmb-cat-p-title{font-family:'Metal Mania',cursive;font-size:clamp(1.8rem,3.5vw,2.6rem);color:#EFEFEF;letter-spacing:2px;margin:0 0 .6rem;text-shadow:0 2px 20px rgba(0,0,0,.8);line-height:1.1;transition:color .3s,text-shadow .3s}
.hmb-cat-premium:hover .hmb-cat-p-title{text-shadow:0 0 30px rgba(204,34,0,.4),0 2px 20px rgba(0,0,0,.8)}
.hmb-cat-p-desc{font-family:'Rajdhani',sans-serif;font-size:1rem;color:rgba(200,200,200,.75);line-height:1.5;margin:0 0 1.2rem;max-width:420px;opacity:.8;transform:translateY(6px);transition:opacity .4s .05s,transform .4s .05s}
.hmb-cat-premium:hover .hmb-cat-p-desc{opacity:1;transform:translateY(0)}
.hmb-cat-p-cta{display:inline-flex;align-items:center;gap:.7rem;opacity:0;transform:translateX(-12px);transition:opacity .35s .1s,transform .35s .1s}
.hmb-cat-premium:hover .hmb-cat-p-cta{opacity:1;transform:translateX(0)}
.hmb-cat-p-cta-text{font-family:'Oswald',sans-serif;font-size:.75rem;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:#CC2200}
.hmb-cat-p-cta-arrow{color:#CC2200;display:flex;align-items:center;transition:transform .3s}
.hmb-cat-premium:hover .hmb-cat-p-cta-arrow{transform:translateX(6px)}
.hmb-cat-p-reflection{position:absolute;bottom:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,#CC2200,#8B0000,transparent);transform:scaleX(0);transition:transform .5s .1s cubic-bezier(.22,.68,0,1.2);z-index:5}
.hmb-cat-premium:hover .hmb-cat-p-reflection{transform:scaleX(1)}
@media(max-width:768px){.hmb-cat-premium{min-height:320px}.hmb-cat-p-content{padding:1.75rem 1.5rem;gap:1rem}.hmb-cat-p-number{font-size:3rem}.hmb-cat-p-cta{opacity:1;transform:none}.hmb-cat-p-desc{opacity:.9;transform:none}}
</style>

<script>
(function(){
  document.querySelectorAll('.hmb-cat-premium').forEach(function(c){
    var img=c.querySelector('.hmb-cat-p-img');
    if(!img||img.classList.contains('hmb-cat-p-noimg'))return;
    c.addEventListener('mousemove',function(e){
      var r=c.getBoundingClientRect();
      img.style.transform='scale(1.08) translate('+((e.clientX-r.left)/r.width-.5)*12+'px,'+((e.clientY-r.top)/r.height-.5)*8+'px)';
    });
    c.addEventListener('mouseleave',function(){img.style.transform='';});
  });
})();
</script>
