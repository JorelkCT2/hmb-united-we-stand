<?php
defined('_JEXEC') or die;

$c          = $concert;
$showPoster = (int) $params->get('show_poster', 1);
$showPrices = (int) $params->get('show_prices', 1);
$ps         = htmlspecialchars($c->poster_url ?? '');
$uid        = 'ncb' . $module->id; // unique per module instance

$mon = ['','ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
$ts1 = strtotime($c->date_start);
$isMul = !empty($c->date_end) && $c->date_end !== $c->date_start;

$dateLabel = strtoupper(date('j', $ts1) . ' ' . $mon[(int)date('n', $ts1)] . ' ' . date('Y', $ts1));
if ($isMul) {
    $ts2 = strtotime($c->date_end);
    $dateLabel .= ' – ' . strtoupper(date('j', $ts2) . ' ' . $mon[(int)date('n', $ts2)]);
}

function nc_flag(string $iso): string {
    $iso = strtoupper(trim($iso));
    if (strlen($iso) !== 2) return '';
    $f = '';
    foreach (str_split($iso) as $ch) { $f .= mb_chr(0x1F1E6 + ord($ch) - ord('A')); }
    return $f;
}

$flag  = nc_flag($c->country_code);
$title = htmlspecialchars($c->title ?: (!empty($c->headlinersList) ? $c->headlinersList[0] : ''));
?>

<style>
#<?= $uid ?>{position:relative;overflow:hidden;border-radius:12px;background:var(--mid, #111111);color:#fff;
  font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;margin:0}
#<?= $uid ?> .nc-bg{position:absolute;inset:0;background-size:cover;background-position:center top;
  filter:blur(20px) brightness(.3) saturate(1.5);transform:scale(1.12)}
#<?= $uid ?> .nc-ov{position:absolute;inset:0;
  background:linear-gradient(120deg,rgba(0,0,0,.8) 0%,rgba(0,0,0,.45) 100%)}
#<?= $uid ?> .nc-in{position:relative;z-index:1;display:flex;align-items:flex-start;
  gap:1.5rem;padding:1.5rem;min-height:260px}
#<?= $uid ?> .nc-pw{flex-shrink:0;width:150px;height:220px;border-radius:8px;overflow:hidden;
  cursor:pointer;box-shadow:0 8px 32px rgba(0,0,0,.65)}
#<?= $uid ?> .nc-pi{width:100%;height:auto;display:block;
  animation:ncPan<?= $uid ?> 22s ease-in-out infinite alternate}
@keyframes ncPan<?= $uid ?>{0%{transform:translateY(0)}100%{transform:translateY(calc(-100% + 220px))}}
#<?= $uid ?> .nc-pw:hover .nc-pi{animation-play-state:paused}
#<?= $uid ?> .nc-nfo{flex:1;min-width:0}
#<?= $uid ?> .nc-lbl{font-size:.68rem;font-weight:700;letter-spacing:2.5px;color:var(--fire, #CC2200);
  text-transform:uppercase;margin-bottom:.5rem}
#<?= $uid ?> .nc-ttl{font-size:clamp(1.3rem,3.5vw,2rem);font-weight:800;margin:0 0 .3rem;
  color:#fff;line-height:1.15}
#<?= $uid ?> .nc-hl{color:#ff9500;font-weight:600;font-size:.9rem;margin-bottom:.2rem}
#<?= $uid ?> .nc-sup{color:var(--grey-9, #999999);font-size:.8rem;margin-bottom:.4rem}
#<?= $uid ?> .nc-dt{font-size:1rem;font-weight:800;color:var(--fire, #CC2200);margin:.5rem 0 .2rem;
  letter-spacing:.5px}
#<?= $uid ?> .nc-ven{font-size:.85rem;color:var(--grey-c, #CCCCCC);margin-bottom:.15rem}
#<?= $uid ?> .nc-pv{color:var(--grey-8, #888888)}
#<?= $uid ?> .nc-hr{font-size:.78rem;color:var(--grey-a, #AAAAAA);margin-bottom:.55rem}
#<?= $uid ?> .nc-prs{display:flex;flex-wrap:wrap;gap:.3rem;margin:.4rem 0 .2rem}
#<?= $uid ?> .nc-pr{background:rgba(230,51,41,.15);border:1px solid rgba(230,51,41,.4);
  color:var(--fire, #CC2200);border-radius:20px;padding:.18rem .6rem;font-size:.74rem;font-weight:600}
#<?= $uid ?> .nc-pr.sold{background:rgba(255,80,80,.1);border-color:rgba(255,80,80,.3);color:#f88}
#<?= $uid ?> .nc-stag{background:#7a2020;color:#fff;border-radius:8px;padding:1px 5px;
  font-size:.64rem;margin-left:3px}
#<?= $uid ?> .nc-dis{font-size:.64rem;color:var(--grey-5, #555555);font-style:italic;margin-bottom:.5rem}
#<?= $uid ?> .nc-act{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.65rem;align-items:center}
#<?= $uid ?> .nc-btn{display:inline-flex;align-items:center;gap:.25rem;font-size:.78rem;
  font-weight:700;padding:.32rem .85rem;border-radius:6px;cursor:pointer;
  text-decoration:none;border:none;line-height:1.4;white-space:nowrap;transition:opacity .2s}
#<?= $uid ?> .nc-btn:hover{opacity:.82;text-decoration:none}
#<?= $uid ?> .btix{background:var(--fire, #CC2200);color:#fff}
#<?= $uid ?> .bweb{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25)!important}
#<?= $uid ?> .bcal{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25)!important}
#<?= $uid ?> .bmore{background:transparent;color:var(--fire, #CC2200);border:1px solid var(--fire, #CC2200)!important}
#<?= $uid ?> .nc-cw{position:relative}
#<?= $uid ?> .nc-cm{display:none;position:absolute;bottom:110%;left:0;background:var(--steel, #1A1A1A);
  border:1px solid var(--grey-4, #444444);border-radius:8px;min-width:190px;z-index:999;overflow:hidden;
  box-shadow:0 4px 20px rgba(0,0,0,.7)}
#<?= $uid ?> .nc-cm.open{display:block}
#<?= $uid ?> .nc-cm a{display:block;padding:.5rem 1rem;color:var(--grey-c, #CCCCCC);text-decoration:none;font-size:.82rem}
#<?= $uid ?> .nc-cm a:hover{background:var(--steel, #1A1A1A);color:#fff}
@media(max-width:580px){
  #<?= $uid ?> .nc-in{flex-direction:column}
  #<?= $uid ?> .nc-pw{width:100%;height:180px}
}
/* Lightbox */
#<?= $uid ?>lb{display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);
  z-index:9999;align-items:center;justify-content:center}
#<?= $uid ?>lb.open{display:flex}
#<?= $uid ?>lb .lbw{position:relative;display:inline-flex;max-width:90vw;max-height:88vh}
#<?= $uid ?>lb img{max-width:90vw;max-height:88vh;border-radius:8px;display:block}
#<?= $uid ?>lb .lbc{position:absolute;top:8px;right:8px;width:32px;height:32px;
  border-radius:50%;background:rgba(0,0,0,.75);border:2px solid rgba(255,255,255,.8);
  color:#fff;font-size:1rem;cursor:pointer;display:flex;align-items:center;
  justify-content:center;z-index:1}
</style>

<div id="<?= $uid ?>">
    <?php if ($showPoster && $ps): ?>
    <div class="nc-bg" style="background-image:url('<?= $ps ?>')"></div>
    <div class="nc-ov"></div>
    <?php endif; ?>

    <div class="nc-in">
        <?php if ($showPoster && $ps): ?>
        <div class="nc-pw" onclick="<?= $uid ?>LbOpen()" title="Ver cartel completo">
            <img src="<?= $ps ?>" class="nc-pi" alt="Cartel <?= $title ?>" />
        </div>
        <?php endif; ?>

        <div class="nc-nfo">
            <div class="nc-lbl">🎸 Próximo Concierto</div>
            <h2 class="nc-ttl"><?= $title ?></h2>

            <?php if (!empty($c->headlinersList)): ?>
            <div class="nc-hl">⭐ <?= htmlspecialchars(implode(' · ', $c->headlinersList)) ?></div>
            <?php endif; ?>

            <?php if (!empty($c->support_bands)): ?>
            <div class="nc-sup">+ <?= htmlspecialchars($c->support_bands) ?></div>
            <?php endif; ?>

            <div class="nc-dt">📅 <?= $dateLabel ?></div>

            <div class="nc-ven">
                <?= $flag ?> <?= htmlspecialchars($c->city) ?>
                <?php if (!empty($c->province) && $c->province !== $c->city): ?>
                <span class="nc-pv">(<?= htmlspecialchars($c->province) ?>)</span>
                <?php endif; ?>
                <?php if (!empty($c->venue)): ?> — <?= htmlspecialchars($c->venue) ?><?php endif; ?>
            </div>

            <?php if (!empty($c->concert_time) || !empty($c->doors_time)): ?>
            <div class="nc-hr">
                <?php if (!empty($c->doors_time)): ?>🚪 <?= substr($c->doors_time, 0, 5) ?><?php endif; ?>
                <?php if (!empty($c->doors_time) && !empty($c->concert_time)): ?> &nbsp;·&nbsp; <?php endif; ?>
                <?php if (!empty($c->concert_time)): ?>🎶 <?= substr($c->concert_time, 0, 5) ?><?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($showPrices && !empty($c->pricesList)): ?>
            <div class="nc-prs">
                <?php foreach ($c->pricesList as $p): ?>
                <span class="nc-pr <?= !empty($p['sold_out']) ? 'sold' : '' ?>">
                    <?= htmlspecialchars($p['label']) ?>
                    <?php if (!empty($p['amount'])): ?>
                    · <strong><?= htmlspecialchars($p['amount']) ?><?php if (strpos($p['amount'], '€') === false && strpos($p['amount'], '$') === false): ?> €<?php endif; ?></strong>
                    <?php endif; ?>
                    <?php if (!empty($p['sold_out'])): ?><span class="nc-stag">AGOTADAS</span><?php endif; ?>
                </span>
                <?php endforeach; ?>
            </div>
            <div class="nc-dis">* Precios orientativos, pueden no incluir gastos de gestión</div>
            <?php endif; ?>

            <div class="nc-act">
                <?php if (!empty($c->ticket_url)): ?>
                <a href="<?= htmlspecialchars($c->ticket_url) ?>" target="_blank" rel="noopener" class="nc-btn btix">🎟️ Entradas</a>
                <?php endif; ?>
                <?php if (!empty($c->website_url)): ?>
                <a href="<?= htmlspecialchars($c->website_url) ?>" target="_blank" rel="noopener" class="nc-btn bweb">🌐 Web oficial</a>
                <?php endif; ?>
                <div class="nc-cw">
                    <button class="nc-btn bcal" onclick="document.getElementById('<?= $uid ?>cm').classList.toggle('open')">📅 Calendario ▾</button>
                    <div class="nc-cm" id="<?= $uid ?>cm">
                        <a href="<?= htmlspecialchars($c->gcUrl) ?>" target="_blank" rel="noopener">📅 Google Calendar</a>
                        <a href="<?= htmlspecialchars($c->icsUrl) ?>">📅 Outlook (.ics)</a>
                        <a href="<?= htmlspecialchars($c->icsUrl) ?>">🍎 Apple Calendar</a>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($c->detailUrl) ?>" class="nc-btn bmore">› Más info</a>
            </div>
        </div>
    </div>
</div>

<?php if ($showPoster && $ps): ?>
<div id="<?= $uid ?>lb" onclick="if(event.target===this)<?= $uid ?>LbClose()">
    <div class="lbw">
        <button class="lbc" onclick="<?= $uid ?>LbClose()">✕</button>
        <img src="<?= $ps ?>" alt="<?= $title ?>" onclick="event.stopPropagation()" />
    </div>
</div>
<script>
function <?= $uid ?>LbOpen(){document.getElementById('<?= $uid ?>lb').classList.add('open');document.body.style.overflow='hidden'}
function <?= $uid ?>LbClose(){document.getElementById('<?= $uid ?>lb').classList.remove('open');document.body.style.overflow=''}
document.addEventListener('keydown',function(e){if(e.key==='Escape')<?= $uid ?>LbClose()});
document.addEventListener('click',function(e){if(!e.target.closest('#<?= $uid ?> .nc-cw')){var m=document.getElementById('<?= $uid ?>cm');if(m)m.classList.remove('open')}});
</script>
<?php endif; ?>
