<?php
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;

if (empty($artistas)) return;

$titulo = $module->title ?? '';
$mostrarTitulo = ($module->showtitle ?? 1) == 1;
?>
<?php if ($mostrarTitulo && $titulo): ?>
<div style="text-align:center;margin-bottom:1.2rem">
    <h2 style="display:inline-block;font-size:1.8rem;font-weight:900;color:#d4bb8a;text-transform:uppercase;letter-spacing:2px;text-shadow:0 0 20px rgba(200,160,60,.4),2px 2px 0 rgba(0,0,0,.8);margin:0;padding:0 1rem;border-bottom:2px solid rgba(180,140,60,.4);padding-bottom:.4rem">
        <?= htmlspecialchars($titulo) ?>
    </h2>
</div>
<?php endif; ?>
?>
<style>
#<?= $moduleId ?> {
    position: relative;
    overflow: hidden;
    padding: 0 50px;
}
#<?= $moduleId ?> .mas-track {
    display: flex;
    gap: 12px;
    transition: transform .45s cubic-bezier(.25,.46,.45,.94);
    will-change: transform;
}
#<?= $moduleId ?> .mas-card {
    flex: 0 0 calc((100% - <?= ($cardsVisibles-1)*12 ?>px) / <?= $cardsVisibles ?>);
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    aspect-ratio: 3/4;
    background: #111;
    border: 1px solid rgba(180,140,60,.25);
    box-shadow: 0 4px 18px rgba(0,0,0,.6);
    transition: transform .25s, box-shadow .25s;
}
#<?= $moduleId ?> .mas-card:hover {
    transform: translateY(-6px) scale(1.03);
    box-shadow: 0 10px 30px rgba(0,0,0,.8), 0 0 0 1px rgba(200,160,60,.4);
}
#<?= $moduleId ?> .mas-card img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .4s;
}
#<?= $moduleId ?> .mas-card:hover img { transform: scale(1.07); }
#<?= $moduleId ?> .mas-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.92) 0%, rgba(0,0,0,.3) 50%, transparent 100%);
}
#<?= $moduleId ?> .mas-name {
    position: absolute; bottom: 0; left: 0; right: 0;
    padding: .6rem .8rem;
    color: #fff;
    font-weight: 800;
    font-size: .85rem;
    text-align: center;
    text-shadow: 0 1px 4px rgba(0,0,0,.9);
    letter-spacing: .5px;
    text-transform: uppercase;
    line-height: 1.2;
}
#<?= $moduleId ?> .mas-genre {
    font-size: .68rem;
    color: #d4bb8a;
    font-weight: 400;
    text-transform: none;
    letter-spacing: 0;
    display: block;
    margin-top: 2px;
}
#<?= $moduleId ?> .mas-no-img {
    width:100%; height:100%;
    display:flex; align-items:center; justify-content:center;
    font-size: 3.5rem;
    background: linear-gradient(135deg, #1a1208, #2a1e08);
}
.mas-btn {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(180,140,60,.25);
    border: 1px solid rgba(200,160,60,.4);
    color: #d4bb8a;
    width: 42px; height: 42px;
    border-radius: 50%;
    font-size: 1.3rem;
    cursor: pointer;
    z-index: 10;
    display: flex; align-items: center; justify-content: center;
    transition: background .2s;
}
.mas-btn:hover { background: rgba(180,140,60,.55); color: #fff; }
.mas-btn-prev { left: 4px; }
.mas-btn-next { right: 4px; }
.mas-dots {
    display: flex; justify-content: center; gap: 6px; margin-top: 10px;
}
.mas-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: rgba(180,140,60,.3);
    border: 1px solid rgba(200,160,60,.4);
    cursor: pointer; transition: background .2s;
}
.mas-dot.active { background: #d4bb8a; }

@media (max-width: 768px) {
    #<?= $moduleId ?> .mas-card {
        flex: 0 0 calc((100% - 24px) / 3);
    }
}
@media (max-width: 480px) {
    #<?= $moduleId ?> .mas-card {
        flex: 0 0 calc((100% - 12px) / 2);
    }
}
</style>

<div style="position:relative">
    <button class="mas-btn mas-btn-prev" onclick="masSlide('<?= $moduleId ?>',-1)" aria-label="Anterior">&#8249;</button>
    <div id="<?= $moduleId ?>">
        <div class="mas-track" id="<?= $moduleId ?>-track">
            <?php foreach ($artistas as $a):
                $url = 'index.php?option=com_artistas&view=artista&id=' . $a->id . ($fichaItemid ? '&Itemid='.$fichaItemid : '');
            ?>
            <div class="mas-card" onclick="location.href='<?= $url ?>'">
                <?php if (!empty($a->image)): ?>
                <img src="<?= Uri::root() . htmlspecialchars($a->image) ?>"
                     alt="<?= htmlspecialchars($a->name) ?>" loading="lazy">
                <?php else: ?>
                <img src="https://heavymetalbrothers.com/images/fx/sinimagen.jpg" style="width:100%;height:100%;object-fit:cover" alt="">
                <?php endif; ?>
                <div class="mas-overlay"></div>
                <a href="<?= $url ?>" class="mas-name" onclick="event.stopPropagation()">
                    <?= htmlspecialchars($a->name) ?>
                    <?php if (!empty($a->genre)): ?>
                    <span class="mas-genre"><?= htmlspecialchars($a->genre) ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <button class="mas-btn mas-btn-next" onclick="masSlide('<?= $moduleId ?>',1)" aria-label="Siguiente">&#8250;</button>
    <div class="mas-dots" id="<?= $moduleId ?>-dots"></div>
</div>

<script>
(function(){
    var mid    = '<?= $moduleId ?>';
    var track  = document.getElementById(mid + '-track');
    var cards  = track.querySelectorAll('.mas-card');
    var total  = cards.length;
    var vis    = <?= $cardsVisibles ?>;
    var curr   = 0;
    var maxSlide = Math.max(0, total - vis);
    var autoplay = <?= $autoplay ? 'true' : 'false' ?>;
    var speed   = <?= $velocidad ?>;
    var timer;

    // Build dots
    var dotsEl = document.getElementById(mid + '-dots');
    var pages  = Math.ceil(total / vis);
    for (var i = 0; i < pages; i++) {
        var dot = document.createElement('span');
        dot.className = 'mas-dot' + (i === 0 ? ' active' : '');
        dot.setAttribute('data-page', i);
        dot.addEventListener('click', function(){ goTo(parseInt(this.getAttribute('data-page')) * vis); });
        dotsEl.appendChild(dot);
    }

    function goTo(n) {
        curr = Math.max(0, Math.min(n, maxSlide));
        var cardW = cards[0].offsetWidth + 12;
        track.style.transform = 'translateX(-' + (curr * cardW) + 'px)';
        dotsEl.querySelectorAll('.mas-dot').forEach(function(d, i) {
            d.classList.toggle('active', i === Math.floor(curr / vis));
        });
    }

    window.masSlide = window.masSlide || function(id, dir) {
        if (id !== mid) return;
        goTo(curr + dir * vis);
        resetTimer();
    };
    // Override to support multiple sliders
    var prevMasSlide = window.masSlide;
    window.masSlide = function(id, dir) {
        if (id === mid) { goTo(curr + dir * vis); resetTimer(); }
        else if (prevMasSlide) prevMasSlide(id, dir);
    };

    function resetTimer() {
        clearInterval(timer);
        if (autoplay) timer = setInterval(function(){ goTo(curr >= maxSlide ? 0 : curr + vis); }, speed);
    }

    // Touch/swipe support
    var startX = 0;
    track.addEventListener('touchstart', function(e){ startX = e.touches[0].clientX; }, {passive:true});
    track.addEventListener('touchend', function(e){
        var diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) { goTo(curr + (diff > 0 ? vis : -vis)); resetTimer(); }
    }, {passive:true});

    resetTimer();
})();
</script>
