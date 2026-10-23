<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$app    = Factory::getApplication();
$doc    = $app->getDocument();
$base   = Uri::base(true);
$itemId = $app->input->getInt('Itemid', 0);
$doc->addStyleSheet($base . '/components/com_concerts/assets/css/concerts.css');
$doc->addScript($base . '/components/com_concerts/assets/js/concerts.js', [], ['defer' => true]);

function ccFlag(string $c): string {
    $c = strtoupper(trim($c));
    if (strlen($c) !== 2) return '🌍';
    $f = '';
    foreach (str_split($c) as $ch) { $f .= mb_chr(0x1F1E6 + ord($ch) - ord('A')); }
    return $f;
}
function ccDay(string $d): array {
    $t = strtotime($d);
    return [
        'n' => date('j', $t),
        'm' => ['','ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'][(int)date('n',$t)],
        'y' => date('Y', $t),
        'w' => ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'][(int)date('w',$t)],
    ];
}

$EU = ['ES'=>'🇪🇸 España','DE'=>'🇩🇪 Alemania','AT'=>'🇦🇹 Austria','BE'=>'🇧🇪 Bélgica',
       'BG'=>'🇧🇬 Bulgaria','HR'=>'🇭🇷 Croacia','CZ'=>'🇨🇿 Rep. Checa','DK'=>'🇩🇰 Dinamarca',
       'SK'=>'🇸🇰 Eslovaquia','SI'=>'🇸🇮 Eslovenia','EE'=>'🇪🇪 Estonia','FI'=>'🇫🇮 Finlandia',
       'FR'=>'🇫🇷 Francia','GR'=>'🇬🇷 Grecia','HU'=>'🇭🇺 Hungría','IE'=>'🇮🇪 Irlanda',
       'IS'=>'🇮🇸 Islandia','IT'=>'🇮🇹 Italia','LV'=>'🇱🇻 Letonia','LT'=>'🇱🇹 Lituania',
       'LU'=>'🇱🇺 Luxemburgo','MT'=>'🇲🇹 Malta','NO'=>'🇳🇴 Noruega','NL'=>'🇳🇱 Países Bajos',
       'PL'=>'🇵🇱 Polonia','PT'=>'🇵🇹 Portugal','GB'=>'🇬🇧 Reino Unido','RO'=>'🇷🇴 Rumanía',
       'SE'=>'🇸🇪 Suecia','CH'=>'🇨🇭 Suiza'];

$f          = $this->filters;
$grouped    = $this->grouped;
$total      = array_sum(array_map('count', $grouped));
$iid        = $itemId ? '&Itemid=' . $itemId : '';
$agendaUrl  = 'index.php?option=com_concerts&view=concerts' . $iid;
$postersUrl = 'index.php?option=com_concerts&view=posters'  . $iid;

$btnS = 'style="display:inline-flex;align-items:center;gap:.25rem;font-size:.78rem;font-weight:600;padding:.22rem .72rem;line-height:1.4;text-transform:none;letter-spacing:0;border-radius:5px;cursor:pointer;text-decoration:none;white-space:nowrap;border:none"';
?>
<div class="cc-wrap">

<div class="cc-tabs">
    <a href="<?= htmlspecialchars($agendaUrl) ?>" class="cc-tab active">📅 Agenda</a>
    <a href="<?= htmlspecialchars($postersUrl) ?>" class="cc-tab">🖼️ Carteles</a>
</div>

<!-- FILTROS -->
<form method="get" class="cc-filters">
    <input type="hidden" name="option"  value="com_concerts" />
    <input type="hidden" name="view"    value="concerts" />
    <?php if ($itemId): ?><input type="hidden" name="Itemid" value="<?= $itemId ?>" /><?php endif; ?>

    <div class="cc-filter-group">
        <label>País</label>
        <select name="country_code">
            <option value="" <?= ($f['country_code'] === '') ? 'selected' : '' ?>>Todos los países</option>
            <?php foreach ($EU as $code => $label): ?>
            <option value="<?= $code ?>" <?= ($f['country_code'] === $code) ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (!empty($this->provinces)): ?>
    <div class="cc-filter-group">
        <label>Provincia</label>
        <select name="province">
            <option value="">Todas</option>
            <?php foreach ($this->provinces as $p): ?>
            <option value="<?= htmlspecialchars($p) ?>" <?= ($f['province'] === $p) ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <?php if (!empty($this->genres)): ?>
    <div class="cc-filter-group">
        <label>Género</label>
        <select name="genre_id">
            <option value="0">Todos</option>
            <?php foreach ($this->genres as $gId => $gName): ?>
            <option value="<?= (int)$gId ?>" <?= ((int)$f['genre_id'] === (int)$gId) ? 'selected' : '' ?>><?= htmlspecialchars($gName) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <div class="cc-filter-group">
        <label>Desde</label>
        <input type="date" name="date_from" value="<?= htmlspecialchars($f['date_from']) ?>" />
    </div>
    <div class="cc-filter-group">
        <label>Hasta</label>
        <input type="date" name="date_to" value="<?= htmlspecialchars($f['date_to']) ?>" />
    </div>
    <button type="submit" class="btn-ff">🔍 Filtrar</button>
    <a href="<?= htmlspecialchars($agendaUrl) ?>" class="btn-fr">✕</a>
</form>

<?php if ($total > 0): ?>
<p style="color:#666;font-size:.82rem;margin-bottom:.25rem"><?= $total ?> concierto<?= $total !== 1 ? 's' : '' ?></p>
<?php endif; ?>

<?php if (empty($grouped)): ?>
<div class="cc-empty"><div class="ce-ico">🎸</div><p>No hay conciertos para estos filtros.</p></div>
<?php else: ?>

<?php foreach ($grouped as $date => $concerts): $fd = ccDay($date); ?>
<div class="cc-day-hdr">
    <div class="cc-day-box">
        <div class="cc-day-num"><?= $fd['n'] ?></div>
        <div class="cc-day-mon"><?= $fd['m'] ?> '<?= substr($fd['y'], 2) ?></div>
    </div>
    <span class="cc-day-dow"><?= $fd['w'] ?></span>
    <div class="cc-day-line"></div>
</div>

<?php foreach ($concerts as $c):
    $ps   = htmlspecialchars($c->poster_url ?? '');
    $flag = ccFlag($c->country_code);
    $det  = 'index.php?option=com_concerts&view=concert&id=' . $c->id . $iid;
    $ics  = 'index.php?option=com_concerts&task=display.ics&id=' . $c->id;
    $mul  = !empty($c->date_end) && $c->date_end !== $c->date_start;
    $can  = (int)$c->state === -1;
    $hls  = (!empty($c->headliners) && $c->headliners[0] === '[') ? (json_decode($c->headliners, true) ?? []) : [];

    $prices = [];
    if (!empty($c->prices)) {
        $decoded = json_decode($c->prices, true);
        if (is_array($decoded)) {
            $prices = array_values(array_filter($decoded, fn($p) => !empty($p['label']) || !empty($p['amount'])));
        }
    }

    $genreNames = [];
    if (!empty($c->genre_ids) && !empty($this->genres)) {
        foreach (explode(',', $c->genre_ids) as $gid) {
            $gid = (int)trim($gid);
            if ($gid && isset($this->genres[$gid])) {
                $genreNames[] = $this->genres[$gid];
            }
        }
    }

    $gcTitle = urlencode(($c->title ?: (!empty($hls) ? $hls[0] : '')) . ($c->city ? ' — ' . $c->city : ''));
    $gcDs = str_replace('-', '', $c->date_start) . (!empty($c->concert_time) ? 'T' . str_replace(':', '', substr($c->concert_time, 0, 5)) . '00' : '');
    $gcDe = ($mul) ? str_replace('-', '', $c->date_end) . 'T235959' : $gcDs;
    $gcLoc = urlencode(implode(', ', array_filter([$c->venue, $c->city, $c->country_name])));
    $gcUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gcTitle}&dates={$gcDs}/{$gcDe}&location={$gcLoc}";
?>
<div class="cc-card<?= $can ? ' cc-cancelled-card' : '' ?>">

    <?php if ($ps): ?>
    <img src="<?= $ps ?>" class="cc-thumb"
         onclick="CcLb.open('<?= $ps ?>', '<?= htmlspecialchars($c->title ?: (!empty($hls) ? $hls[0] : ''), ENT_QUOTES) ?>')"
         loading="lazy" alt="Cartel" title="Clic para ampliar cartel" />
    <?php else: ?>
    <div class="cc-nothumb">🎸</div>
    <?php endif; ?>

    <div class="cc-body">

        <a href="<?= htmlspecialchars($det) ?>" class="cc-band cc-title-main">
            <?= htmlspecialchars($c->title ?: (!empty($hls) ? $hls[0] : $c->band)) ?>
        </a>

        <?php if (!empty($hls)): ?>
        <div class="cc-hl">+ <?= htmlspecialchars(implode(', ', $hls)) ?></div>
        <?php endif; ?>

        <?php if (!empty($c->support_bands)): ?>
        <div class="cc-support"><?= htmlspecialchars($c->support_bands) ?></div>
        <?php endif; ?>

        <div class="cc-venue">
            <?= $flag ?> <strong><?= htmlspecialchars($c->city) ?></strong>
            <?php if (!empty($c->province) && $c->province !== $c->city): ?>
            <span style="color:#555"> · <?= htmlspecialchars($c->province) ?></span>
            <?php endif; ?>
            <?php if (!empty($c->venue)): ?>
            <span style="color:#888"> — <?= htmlspecialchars($c->venue) ?></span>
            <?php endif; ?>
        </div>

        <!-- Precios -->
        <?php if (!empty($prices)): ?>
        <div class="cc-prices">
            <?php foreach ($prices as $p): ?>
            <span class="cc-price-badge <?= !empty($p['sold_out']) ? 'cc-price-sold' : '' ?>">
                <?= htmlspecialchars($p['label']) ?>
                <?php if (!empty($p['amount'])): ?> · <strong><?= htmlspecialchars($p['amount']) ?><?php if(strpos($p['amount'],'€')===false&&strpos($p['amount'],'$')===false): ?> €<?php endif; ?></strong><?php endif; ?>
                <?php if (!empty($p['sold_out'])): ?><span class="cc-soldout-tag">AGOTADAS</span><?php endif; ?>
            </span>
            <?php endforeach; ?>
        </div>
        <div class="cc-price-disclaimer">* Precios orientativos, pueden no incluir gastos de gestión</div>
        <?php endif; ?>

        <!-- Géneros -->
        <?php if (!empty($genreNames)): ?>
        <div class="cc-tags">
            <?php foreach ($genreNames as $gn): ?>
            <span class="cc-tag"><?= htmlspecialchars($gn) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Horario y badges -->
        <div class="cc-meta">
            <?php if (!empty($c->doors_time)): ?><span class="cc-badge">🚪 <?= substr($c->doors_time, 0, 5) ?></span><?php endif; ?>
            <?php if (!empty($c->concert_time)): ?><span class="cc-badge">🎶 <?= substr($c->concert_time, 0, 5) ?></span><?php endif; ?>
            <?php if ($can): ?><span class="cc-cancelled">❌ CANCELADO</span><?php endif; ?>
            <?php if ($mul): ?><span class="cc-multiday">📅 Hasta <?= $c->date_end ?></span><?php endif; ?>
        </div>

        <!-- Botones -->
        <div class="cc-actions">
            <?php if (!empty($c->ticket_url)): ?>
            <a href="<?= htmlspecialchars($c->ticket_url) ?>" target="_blank" rel="noopener" class="btn-cc btn-tix" <?= $btnS ?>>🎟️ Entradas</a>
            <?php endif; ?>
            <?php if (!empty($c->website_url)): ?>
            <a href="<?= htmlspecialchars($c->website_url) ?>" target="_blank" rel="noopener" class="btn-cc btn-web" <?= $btnS ?>>🌐 Web oficial</a>
            <?php endif; ?>
            <div class="cc-cal-wrap">
                <button type="button" class="btn-cc btn-cal-toggle" <?= $btnS ?> onclick="ccToggleCal(this)">📅 Añadir al calendario ▾</button>
                <div class="cc-cal-menu">
                    <a href="<?= $gcUrl ?>" target="_blank" rel="noopener">📅 Google Calendar</a>
                    <a href="<?= htmlspecialchars($ics) ?>">📅 Outlook (.ics)</a>
                    <a href="<?= htmlspecialchars($ics) ?>">🍎 Apple Calendar</a>
                </div>
            </div>
            <a href="<?= htmlspecialchars($det) ?>" class="btn-cc btn-detail" <?= $btnS ?>>› Más info</a>
        </div>

    </div>
</div>
<?php endforeach; ?>
<?php endforeach; ?>
<?php endif; ?>

</div>

<div class="cc-lb" id="ccLightbox" onclick="if(event.target===this)CcLb.close()">
    <div class="cc-lb-img-wrap">
        <button class="cc-lb-close" onclick="CcLb.close()" title="Cerrar (Esc)">✕</button>
        <img class="cc-lb-img" src="" alt="" onclick="event.stopPropagation()" />
    </div>
    <div class="cc-lb-cap"></div>
</div>
