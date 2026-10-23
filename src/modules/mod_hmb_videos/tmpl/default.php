<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$style       = $params->get('layout_style', 'grid');
$showTitle   = (bool) $params->get('show_title', 1);
$showBand    = (bool) $params->get('show_band', 1);
$showViewAll = (bool) $params->get('show_view_all', 1);
$itemId      = (int) $params->get('view_all_itemid', 0);
$modId       = 'hmb-mod-' . $module->id;

$rootFolders = array_filter($folders, fn($f) => (int)$f->parent_id === 0);
$subByParent = [];
foreach ($folders as $f) {
    if ((int)$f->parent_id > 0) $subByParent[$f->parent_id][] = $f;
}

if (empty($videos)) return;
?>
<div class="hmb-module" id="<?= $modId ?>">
<style>
#<?= $modId ?> .hmb-card-thumb{position:relative;width:100%;padding-bottom:56.25%;height:0;overflow:hidden;background:#000;display:block}
#<?= $modId ?> .hmb-thumb-img{position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block}
#<?= $modId ?> .hmb-mod-grid--grid{grid-template-columns:repeat(auto-fill,minmax(250px,1fr))}
#<?= $modId ?> .hmb-mod-grid--list .hmb-card-thumb{padding-bottom:0;height:120px}

#<?= $modId ?> .hmb-mod-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)) !important;
    gap: 1rem !important;
}
#<?= $modId ?> .hmb-mod-card {
    max-width: 100% !important;
}
</style>


    <?php if (count($folders) > 1) : ?>
    <div class="hmb-mod-folders">
        <?php foreach ($rootFolders as $folder) : ?>
        <a href="<?= Route::_('index.php?option=com_hmb_videos&view=videos&folder_id=' . $folder->id . ($itemId ? '&Itemid=' . $itemId : '')) ?>"
           class="hmb-mod-ftag">
            <?= htmlspecialchars($folder->title) ?>
            <span class="hmb-mod-fcount"><?= (int)$folder->video_count ?></span>
        </a>
        <?php if (isset($subByParent[$folder->id])) : foreach ($subByParent[$folder->id] as $sub) : ?>
        <a href="<?= Route::_('index.php?option=com_hmb_videos&view=videos&folder_id=' . $sub->id . ($itemId ? '&Itemid=' . $itemId : '')) ?>"
           class="hmb-mod-ftag hmb-mod-ftag--sub">
            ↳ <?= htmlspecialchars($sub->title) ?>
            <span class="hmb-mod-fcount"><?= (int)$sub->video_count ?></span>
        </a>
        <?php endforeach; endif ?>
        <?php endforeach ?>
    </div>
    <?php endif ?>

    <div class="hmb-mod-grid hmb-mod-grid--<?= htmlspecialchars($style) ?>">
        <?php foreach ($videos as $i => $video) : ?>
        <?php $ytId = htmlspecialchars($video->youtube_id, ENT_QUOTES); ?>
        <div class="hmb-mod-card hmb-video-card"
            data-youtube-id="<?= $ytId ?>"
            data-title="<?= htmlspecialchars($video->title, ENT_QUOTES) ?>"
            data-band="<?= htmlspecialchars($video->band ?? '', ENT_QUOTES) ?>"
            data-desc="<?= htmlspecialchars(strip_tags($video->description ?? ''), ENT_QUOTES) ?>"
            data-mobile="<?= (int)($video->mobile ?? 0) ?>">

            <div class="hmb-card-thumb" style="overflow:hidden;width:100%;height:0;padding-bottom:56.25%;position:relative;background:#000;">
                <img src="https://img.youtube.com/vi/<?= $ytId ?>/hqdefault.jpg"
                     alt="<?= htmlspecialchars($video->title, ENT_QUOTES) ?>"
                     loading="lazy" class="hmb-thumb-img" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;">
                <div class="hmb-hover-player"
                     data-src="https://www.youtube.com/embed/<?= $ytId ?>?autoplay=1&mute=1&controls=0&loop=1&playlist=<?= $ytId ?>&rel=0&modestbranding=1">
                    <iframe class="hmb-hover-iframe" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>
                <div class="hmb-card-overlay">
                    <button class="hmb-play-btn" aria-label="Play"><span class="hmb-play-icon">▶</span></button>
                </div>
                <?php if ($video->featured) : ?><div class="hmb-featured-badge">⭐</div><?php endif ?>
                <?php if ($video->genre) : ?><div class="hmb-genre-badge"><?= htmlspecialchars($video->genre) ?></div><?php endif ?>
            </div>

            <?php if ($showTitle || $showBand) : ?>
            <div class="hmb-card-info">
                <?php if ($showTitle) : ?><h4 class="hmb-card-title"><?= htmlspecialchars($video->title) ?></h4><?php endif ?>
                <?php if ($showBand && $video->band) : ?><p class="hmb-card-band">🎸 <?= htmlspecialchars($video->band) ?></p><?php endif ?>
            </div>
            <?php endif ?>
        </div>
        <?php endforeach ?>
    </div>

    <?php if ($showViewAll) : ?>
    <div class="hmb-mod-footer">
        <a href="<?= Uri::base() ?>index.php?option=com_hmb_videos&view=videos"
           class="hmb-mod-viewall">🤘 <?= Text::_('MOD_HMB_VIDEOS_VIEW_ALL') ?> 🤘</a>
    </div>
    <?php endif ?>

    <!-- Modal propio del módulo -->
    <div class="hmb-modal-overlay" id="hmbModal-<?= $module->id ?>" role="dialog" aria-modal="true">
        <div class="hmb-modal-container">
            <div class="hmb-modal-header">
                <div class="hmb-modal-title-wrap">
                    <h2 class="hmb-modal-title" id="hmbMT-<?= $module->id ?>">—</h2>
                    <p class="hmb-modal-band" id="hmbMB-<?= $module->id ?>"></p>
                </div>
                <button class="hmb-modal-close" id="hmbMC-<?= $module->id ?>">✖</button>
            </div>
            <div class="hmb-modal-body">
                <div class="hmb-iframe-wrap">
                    <iframe id="hmbMI-<?= $module->id ?>" src="" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
                <div class="hmb-modal-desc" id="hmbMD-<?= $module->id ?>"></div>
            </div>
            <div class="hmb-modal-footer">
                <button class="hmb-btn-prev" id="hmbMP-<?= $module->id ?>">◀ <?= Text::_('MOD_HMB_VIDEOS_PREV') ?></button>
                <button class="hmb-btn-next" id="hmbMN-<?= $module->id ?>"><?= Text::_('MOD_HMB_VIDEOS_NEXT') ?> ▶</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
var sid=<?= json_encode((string)$module->id) ?>;
var wrap=document.getElementById('hmb-mod-'+sid);
if(!wrap)return;
var modal=document.getElementById('hmbModal-'+sid);
var iframe=document.getElementById('hmbMI-'+sid);
var titleEl=document.getElementById('hmbMT-'+sid);
var bandEl=document.getElementById('hmbMB-'+sid);
var descEl=document.getElementById('hmbMD-'+sid);
var closeBtn=document.getElementById('hmbMC-'+sid);
var prevBtn=document.getElementById('hmbMP-'+sid);
var nextBtn=document.getElementById('hmbMN-'+sid);
var cards=Array.from(wrap.querySelectorAll('.hmb-mod-card'));
var cur=-1,hm=new Map();
function open(i){if(i<0||i>=cards.length)return;cur=i;var c=cards[i],yt=c.dataset.youtubeId;titleEl.textContent=c.dataset.title||'';bandEl.textContent=c.dataset.band?'🎸 '+c.dataset.band:'';descEl.textContent=c.dataset.desc||'';iframe.src='https://www.youtube.com/embed/'+yt+'?autoplay=1&rel=0&modestbranding=1';prevBtn.disabled=(cur===0);nextBtn.disabled=(cur===cards.length-1);modal.classList.add('is-open');document.body.style.overflow='hidden';closeBtn.focus();}
function close(){modal.classList.remove('is-open');setTimeout(function(){iframe.src='';},350);document.body.style.overflow='';cur=-1;}
closeBtn.addEventListener('click',close);
modal.addEventListener('click',function(e){if(e.target===modal)close();});
prevBtn.addEventListener('click',function(){open(cur-1);});
nextBtn.addEventListener('click',function(){open(cur+1);});
cards.forEach(function(card,i){
    var player=card.querySelector('.hmb-hover-player');
    var hif=player?player.querySelector('.hmb-hover-iframe'):null;
    card.addEventListener('mouseenter',function(){if(!hif)return;var t=setTimeout(function(){if(!hif.src&&player.dataset.src)hif.src=player.dataset.src;},350);hm.set(card,t);});
    card.addEventListener('mouseleave',function(){clearTimeout(hm.get(card));if(hif)hif.src='';});
    card.addEventListener('click',function(e){if(e.target.tagName==='A')return;open(i);});
});
document.addEventListener('keydown',function(e){if(!modal.classList.contains('is-open'))return;if(e.key==='Escape')close();if(e.key==='ArrowLeft')open(cur-1);if(e.key==='ArrowRight')open(cur+1);});
})();
</script>
