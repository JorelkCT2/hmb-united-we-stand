<?php
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

if (empty($this->subcategories) || (int)$this->t['cv_display_subcategories'] < 1) return;

$imgBase = rtrim(Uri::root(),'/') . '/' . ltrim($this->t['pathcat']['orig_rel_ds'] ?? 'images/phocacartcategories/', '/');
$icons   = ['🤘','💿','🎸','👕','🔥','⚡','💀','🎵'];
$j       = 0;
?>
<div class="hmb-subcats-wrap">
<?php foreach ($this->subcategories as $i => $v):
    if ($j >= (int)$this->t['cv_display_subcategories']) break;
    $title = htmlspecialchars($v->title ?? '', ENT_QUOTES, 'UTF-8');

    // Image — use orig path directly like the categories override does
    $img = '';
    if (!empty($v->image)) {
        $img = htmlspecialchars($imgBase . $v->image, ENT_QUOTES, 'UTF-8');
    }

    // Link — use item link property first, then construct manually
    $link = '#';
    if (!empty($v->link)) {
        try { $link = Route::_($v->link); } catch(\Throwable $e) {}
    } elseif (!empty($v->id)) {
        $link = Uri::root() . 'index.php/tienda/' . (int)$v->id . '-' . ($v->alias ?? '');
        try {
            $link = Route::_('index.php?option=com_phocacart&view=category&id=' . (int)$v->id . ':' . ($v->alias ?? ''));
        } catch(\Throwable $e) {}
    }
?>
<a href="<?php echo $link; ?>" class="hmb-subcat-card" style="--delay:<?php echo $j*100; ?>ms">
    <div class="hmb-subcat-bg">
        <?php if ($img): ?>
        <div class="hmb-subcat-img" style="background-image:url('<?php echo $img; ?>')"></div>
        <?php else: ?>
        <div class="hmb-subcat-img hmb-subcat-noimg"><?php echo $icons[$j % count($icons)]; ?></div>
        <?php endif; ?>
        <div class="hmb-subcat-overlay"></div>
    </div>
    <div class="hmb-subcat-content">
        <h3 class="hmb-subcat-title"><?php echo $title; ?></h3>
        <span class="hmb-subcat-cta">Ver colección →</span>
    </div>
</a>
<?php $j++; endforeach; ?>
</div>
<div class="ph-hr"></div>

<style>
.hmb-subcats-wrap{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:3px;margin:1rem 0 1.5rem}
.hmb-subcat-card{position:relative;display:block;min-height:320px;overflow:hidden;text-decoration:none;opacity:0;transform:translateY(20px);animation:hmb-sub-in .5s cubic-bezier(.22,.68,0,1.2) var(--delay,0ms) forwards}
@keyframes hmb-sub-in{to{opacity:1;transform:translateY(0)}}
.hmb-subcat-bg{position:absolute;inset:0}
.hmb-subcat-img{position:absolute;inset:0;background-size:cover;background-position:center;filter:brightness(.55) saturate(.8);transition:transform .6s,filter .4s}
.hmb-subcat-noimg{background:linear-gradient(135deg,#1a1a1a,#0d0000);display:flex;align-items:center;justify-content:center;font-size:3rem;color:#8B0000;filter:none}
.hmb-subcat-card:hover .hmb-subcat-img{transform:scale(1.06);filter:brightness(.7) saturate(1.1)}
.hmb-subcat-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(5,0,0,.92) 0%,rgba(5,0,0,.3) 50%,transparent 100%);transition:background .4s}
.hmb-subcat-card:hover .hmb-subcat-overlay{background:linear-gradient(to top,rgba(60,0,0,.9) 0%,rgba(10,0,0,.4) 50%,transparent 100%)}
.hmb-subcat-content{position:absolute;bottom:0;left:0;right:0;padding:1.5rem;z-index:2;border-left:3px solid transparent;transition:border-color .3s}
.hmb-subcat-card:hover .hmb-subcat-content{border-left-color:#CC2200}
.hmb-subcat-title{font-family:'Metal Mania',cursive;font-size:1.5rem;color:#EFEFEF;letter-spacing:2px;margin:0 0 .5rem;text-shadow:0 2px 10px rgba(0,0,0,.8)}
.hmb-subcat-card:hover .hmb-subcat-title{color:#fff;text-shadow:0 0 20px rgba(204,34,0,.4)}
.hmb-subcat-cta{font-family:'Oswald',sans-serif;font-size:.72rem;letter-spacing:2px;text-transform:uppercase;color:#CC2200;opacity:0;transform:translateY(6px);display:block;transition:opacity .3s,transform .3s}
.hmb-subcat-card:hover .hmb-subcat-cta{opacity:1;transform:translateY(0)}
@media(max-width:600px){.hmb-subcat-card{min-height:240px}}
</style>
