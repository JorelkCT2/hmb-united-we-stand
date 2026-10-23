<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$app    = Factory::getApplication();
$doc    = $app->getDocument();
$base   = Uri::base(true);
$itemId = $app->input->getInt('Itemid', 0);
$doc->addStyleSheet($base . '/components/com_concerts/assets/css/concerts.css');
$doc->addScript($base . '/components/com_concerts/assets/js/concerts.js', [], ['defer'=>true]);

$c    = $this->item;
$ics  = 'index.php?option=com_concerts&task=display.ics&id='.$c->id;
$ps   = htmlspecialchars($c->poster_url ?? '');
$back = 'index.php?option=com_concerts&view=concerts'.($itemId?'&Itemid='.$itemId:'');

// Flag emoji
function dflag2(string $x): string {
    $x=strtoupper(trim($x)); if(strlen($x)!==2) return '🌍';
    $f=''; foreach(str_split($x) as $ch){$f.=mb_chr(0x1F1E6+ord($ch)-ord('A'));} return $f;
}

$mon = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$ts1 = strtotime($c->date_start);
$dlbl = date('j',$ts1).' de '.$mon[(int)date('n',$ts1)].' de '.date('Y',$ts1);
if (!empty($c->date_end)&&$c->date_end!==$c->date_start) {
    $ts2=strtotime($c->date_end);
    $dlbl.=' — '.date('j',$ts2).' de '.$mon[(int)date('n',$ts2)].' de '.date('Y',$ts2);
}

$hls    = (!empty($c->headliners)&&$c->headliners[0]==='[')?(json_decode($c->headliners,true)??[]):[];
$prices = !empty($c->prices) ? array_filter(json_decode($c->prices,true)??[],fn($p)=>!empty($p['label'])||!empty($p['amount'])) : [];
$can    = (int)$c->state===-1;
$mul    = !empty($c->date_end)&&$c->date_end!==$c->date_start;

// Resolve genre IDs → names from DB
$genreNames = [];
if (!empty($c->genre_ids)) {
    $ids = array_values(array_filter(array_map('intval', explode(',', $c->genre_ids))));
    if ($ids) {
        $db = Factory::getContainer()->get('DatabaseDriver');
        try {
            $genreNames = $db->setQuery(
                'SELECT name FROM #__artistas_generos WHERE id IN ('.implode(',',$ids).') AND state=1 ORDER BY name ASC'
            )->loadColumn();
        } catch (\Exception $e) { $genreNames = []; }
    }
}

// Google Calendar URL
$gcTitle = urlencode(($c->title ?: (!empty($hls)?$hls[0]:'')).($c->city?' — '.$c->city:''));
$gcDs = str_replace('-','',$c->date_start).(!empty($c->concert_time)?'T'.str_replace(':','',substr($c->concert_time,0,5)).'00':'');
$gcDe = $mul ? str_replace('-','',$c->date_end).'T235959' : $gcDs;
$gcLoc = urlencode(implode(', ',array_filter([$c->venue,$c->city,$c->country_name])));
$gcUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gcTitle}&dates={$gcDs}/{$gcDe}&location={$gcLoc}";

$btnStyle = 'style="display:inline-flex;align-items:center;gap:.3rem;font-size:.82rem;font-weight:600;padding:.35rem .9rem;line-height:1.4;text-transform:none;letter-spacing:0;border-radius:5px;cursor:pointer;text-decoration:none;white-space:nowrap;border:none"';
?>
<div class="cc-wrap">
<div style="margin-bottom:.9rem;font-size:.84rem">
    <a href="<?= htmlspecialchars($back) ?>" style="color:var(--cc-accent)">← Agenda de conciertos</a>
</div>

<?php if ($can): ?>
<div style="background:#3a1010;border:1px solid #7a2020;border-radius:8px;padding:.7rem 1rem;margin-bottom:1rem;color:#f88;font-weight:700">❌ EVENTO CANCELADO</div>
<?php endif; ?>

<div class="cc-detail">

    <?php if ($ps): ?>
    <!-- POSTER: banner con paneo vertical lento que revela el cartel completo -->
    <div class="cc-detail-img cc-poster-banner"
         onclick="CcLb.open('<?= $ps ?>','<?= htmlspecialchars($c->title?:($hls?$hls[0]:''),ENT_QUOTES) ?>')"
         title="Clic para ampliar el cartel">
        <img src="<?= $ps ?>"
             alt="Cartel"
             class="cc-poster-pan" />
        <div class="cc-poster-hint">🔍 Clic para ver el cartel completo</div>
    </div>
    <?php endif; ?>

    <div class="cc-detail-body">

        <!-- TÍTULO PRINCIPAL -->
        <h1 style="font-size:1.9rem;font-weight:800;color:#fff;margin:0 0 .2rem">
            <?= htmlspecialchars($c->title ?: (!empty($hls)?$hls[0]:$c->band)) ?>
        </h1>

        <!-- Cabezas de cartel -->
        <?php if (!empty($hls)): ?>
        <div style="color:var(--cc-accent2);font-weight:600;margin-bottom:.4rem;font-size:1rem">
            ⭐ <?= htmlspecialchars(implode(' · ',$hls)) ?>
        </div>
        <?php endif; ?>

        <!-- Teloneros -->
        <?php if (!empty($c->support_bands)): ?>
        <div style="color:#777;margin-bottom:.5rem;font-size:.88rem">Con: <?= htmlspecialchars($c->support_bands) ?></div>
        <?php endif; ?>

        <!-- Géneros -->
        <?php if (!empty($genreNames)): ?>
        <div class="cc-tags" style="margin-bottom:.75rem">
            <?php foreach ($genreNames as $gn): ?>
            <span class="cc-tag"><?= htmlspecialchars($gn) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($mul): ?>
        <div class="cc-tags" style="margin-bottom:.75rem"><span class="cc-multiday">📅 Festival multi-día</span></div>
        <?php endif; ?>

        <hr class="cc-sep"/>

        <div class="cc-info-row"><span class="ci-ico">📅</span>
            <div><div class="ci-lbl">Fecha</div><div class="ci-val"><?= $dlbl ?></div></div>
        </div>

        <?php if (!empty($c->doors_time)||!empty($c->concert_time)): ?>
        <div class="cc-info-row"><span class="ci-ico">🕐</span>
            <div><div class="ci-lbl">Horario</div>
            <div class="ci-val">
                <?php
                if (!empty($c->doors_time)) echo 'Puertas: '.substr($c->doors_time,0,5);
                if (!empty($c->doors_time)&&!empty($c->concert_time)) echo ' · ';
                if (!empty($c->concert_time)) echo 'Inicio: '.substr($c->concert_time,0,5);
                ?>
            </div>
            <div class="cc-price-disclaimer">* Estos precios son orientativos y pueden no incluir gastos de gestión dependiendo del festival o concierto.</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="cc-info-row"><span class="ci-ico"><?= dflag2($c->country_code) ?></span>
            <div><div class="ci-lbl">Lugar</div>
            <div class="ci-val">
                <?= htmlspecialchars($c->venue) ?> · <?= htmlspecialchars($c->city) ?>
                <?php if (!empty($c->province)&&$c->province!==$c->city): ?> (<?= htmlspecialchars($c->province) ?>)<?php endif; ?>
                · <?= htmlspecialchars($c->country_name) ?>
            </div>
            <?php if (!empty($c->address)): ?><div class="ci-lbl"><?= htmlspecialchars($c->address) ?></div><?php endif; ?>
        </div></div>

        <?php if (!empty($prices)): ?>
        <div class="cc-info-row"><span class="ci-ico">🎟️</span>
            <div><div class="ci-lbl">Precios</div>
            <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.3rem">
                <?php foreach ($prices as $p): ?>
                <div style="border-radius:8px;padding:.3rem .7rem;font-size:.82rem;
                     background:<?= !empty($p['sold_out'])?'#2a1a1a':'rgba(230,51,41,.12)' ?>;
                     border:1px solid <?= !empty($p['sold_out'])?'#7a2020':'rgba(230,51,41,.35)' ?>">
                    <strong style="color:<?= !empty($p['sold_out'])?'#f88':'var(--cc-accent)' ?>"><?= htmlspecialchars($p['label']) ?></strong>
                    <?php if (!empty($p['amount'])): ?><span style="color:#ccc"> — <?= htmlspecialchars($p['amount']) ?><?php if(strpos($p['amount'],'€')===false&&strpos($p['amount'],'$')===false): ?> €<?php endif; ?></span><?php endif; ?>
                    <?php if (!empty($p['sold_out'])): ?><span style="color:#f88;font-size:.72rem;font-weight:700"> · AGOTADAS</span><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div></div>
        </div>
        <?php endif; ?>

        <hr class="cc-sep"/>

        <!-- Botones de acción -->
        <div class="cc-actions" style="gap:.5rem;margin-bottom:1.1rem;flex-wrap:wrap">
            <?php if (!empty($c->ticket_url)): ?>
            <a href="<?= htmlspecialchars($c->ticket_url) ?>" target="_blank" rel="noopener"
               class="btn-cc btn-tix" <?= $btnStyle ?>>🎟️ Comprar entradas</a>
            <?php endif; ?>
            <?php if (!empty($c->website_url)): ?>
            <a href="<?= htmlspecialchars($c->website_url) ?>" target="_blank" rel="noopener"
               class="btn-cc btn-web" <?= $btnStyle ?>>🌐 Web oficial</a>
            <?php endif; ?>
            <?php if ($ps): ?>
            <button class="btn-cc btn-poster" <?= $btnStyle ?>
                    onclick="CcLb.open('<?= $ps ?>','<?= htmlspecialchars($c->title?:($hls?$hls[0]:''),ENT_QUOTES) ?>')">
                🖼️ Ver cartel completo
            </button>
            <?php endif; ?>
        </div>

        <!-- Calendario desplegable -->
        <div class="cc-cal-wrap" style="margin-bottom:1rem">
            <button type="button" class="btn-cc btn-cal-toggle" <?= $btnStyle ?>
                    onclick="ccToggleCal(this)">📅 Añadir a tu calendario ▾</button>
            <div class="cc-cal-menu">
                <a href="<?= $gcUrl ?>" target="_blank" rel="noopener">📅 Google Calendar</a>
                <a href="<?= htmlspecialchars($ics) ?>">📅 Outlook (.ics)</a>
                <a href="<?= htmlspecialchars($ics) ?>">🍎 Apple Calendar</a>
            </div>
        </div>

        <?php if (!empty($c->description)): ?>
        <hr class="cc-sep"/>
        <p class="cc-sec-title">Sobre el evento</p>
        <div style="color:#ccc;font-size:.9rem;line-height:1.75"><?= $c->description ?></div>
        <?php endif; ?>

    </div>
</div>
</div>

<!-- LIGHTBOX -->
<div class="cc-lb" id="ccLightbox" onclick="if(event.target===this)CcLb.close()">
    <div class="cc-lb-img-wrap">
        <button class="cc-lb-close" onclick="CcLb.close()" title="Cerrar (Esc)">✕</button>
        <img class="cc-lb-img" src="" alt="" onclick="event.stopPropagation()" />
    </div>
    <div class="cc-lb-cap"></div>
</div>
