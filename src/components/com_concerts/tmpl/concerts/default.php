<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$app    = Factory::getApplication();
$doc    = $app->getDocument();
$base   = Uri::base(true);
$itemId = $app->input->getInt('Itemid', 0);
$doc->addStyleSheet($base . '/components/com_concerts/assets/css/concerts.css?v=302');
$doc->addScript($base . '/components/com_concerts/assets/js/concerts.js?v=302', [], ['defer' => true]);

$esc = static fn($s): string => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');

$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$mesCorto = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
$dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

$flag = static function (string $c): string {
    $c = strtoupper(trim($c));
    if (strlen($c) !== 2) return '';
    $f = '';
    foreach (str_split($c) as $ch) { $f .= mb_chr(0x1F1E6 + ord($ch) - ord('A')); }

    return $f;
};

$iid        = $itemId ? '&Itemid=' . $itemId : '';
$agendaUrl  = 'index.php?option=com_concerts&view=concerts' . $iid;
$pastUrl    = $agendaUrl . '&past=1';
$postersUrl = 'index.php?option=com_concerts&view=posters' . $iid;
$hoy        = new DateTime('today');

$stats = $this->stats;
$prox  = $stats['proximo'] ? new DateTime($stats['proximo']) : null;
?>
<div class="cc-wrap cc2<?= $this->past ? ' is-past' : '' ?>">

<header class="cc2-hero">
    <span class="cc2-eyebrow"><?= $this->past ? 'Archivo' : 'España · próximas fechas' ?></span>
    <h1 class="cc2-title"><?= $this->past ? 'Conciertos celebrados' : 'Agenda de conciertos' ?></h1>
    <div class="cc2-stats">
        <div><b><?= (int) $stats['total'] ?></b><span><?= $this->past ? 'celebrados' : 'próximos conciertos' ?></span></div>
        <div><b><?= (int) $stats['ciudades'] ?></b><span>ciudades</span></div>
        <?php if ($prox && !$this->past): ?>
        <div><b><?= $prox->format('j') ?> <?= $mesCorto[(int) $prox->format('n')] ?></b><span>el próximo</span></div>
        <?php endif; ?>
    </div>
</header>

<nav class="cc-tabs">
    <a href="<?= $esc($agendaUrl) ?>" class="cc-tab<?= $this->past ? '' : ' active' ?>">📅 Agenda</a>
    <a href="<?= $esc($postersUrl) ?>" class="cc-tab">🖼️ Carteles</a>
    <?php if ($this->mapUrl): ?><a href="<?= $esc($this->mapUrl) ?>" class="cc-tab">🗺️ Mapa</a><?php endif; ?>
    <?php if ($this->past): ?>
        <a href="<?= $esc($agendaUrl) ?>" class="cc-tab cc-tab--right">← Volver a los próximos</a>
    <?php elseif ($stats['hayPasados']): ?>
        <a href="<?= $esc($pastUrl) ?>" class="cc-tab cc-tab--right">Ver celebrados →</a>
    <?php endif; ?>
</nav>

<?php if (empty($this->grouped)): ?>
<div class="cc-empty cc2-empty">
    <div class="ce-ico">🎸</div>
    <h2><?= $this->past ? 'Todavía no hay conciertos celebrados.' : 'No hay conciertos próximos ahora mismo.' ?></h2>
    <p>Estamos recopilando nuevas fechas. Mientras tanto, mira los carteles del año<?= $stats['hayPasados'] && !$this->past ? ' o repasa lo que ya se ha celebrado' : '' ?>.</p>
    <p class="cc2-empty__acts">
        <a class="cc2-btn cc2-btn--fire" href="<?= $esc($postersUrl) ?>">Ver carteles</a>
        <?php if ($stats['hayPasados'] && !$this->past): ?><a class="cc2-btn" href="<?= $esc($pastUrl) ?>">Ver celebrados</a><?php endif; ?>
    </p>
</div>
<?php else: ?>

<section class="cc2-filters" aria-label="Filtros">
    <label class="cc2-search"><span aria-hidden="true">🔍</span>
        <input type="search" id="cc2-q" placeholder="Banda, ciudad o sala" autocomplete="off">
    </label>
    <select id="cc2-tipo" aria-label="Tipo de evento">
        <option value="">Conciertos y festivales</option>
        <option value="concert">Solo conciertos</option>
        <option value="festival">Solo festivales</option>
    </select>
    <select id="cc2-prov" aria-label="Provincia">
        <option value="">Todas las provincias</option>
        <?php foreach ($this->provinces as $p => $n): ?><option value="<?= $esc($p) ?>"><?= $esc($p) ?> (<?= (int) $n ?>)</option><?php endforeach; ?>
    </select>
    <div class="cc2-months" id="cc2-months" role="group" aria-label="Mes">
        <button type="button" class="on" data-mes="">Todos</button>
        <?php foreach ($this->months as $m => $n): [$y, $mm] = explode('-', $m); ?>
        <button type="button" data-mes="<?= $esc($m) ?>"><?= $mesCorto[(int) $mm] ?> <?= substr($y, 2) ?></button>
        <?php endforeach; ?>
    </div>
    <span class="cc2-count" id="cc2-count" aria-live="polite"></span>
</section>

<div id="cc2-list">
<?php foreach ($this->grouped as $mes => $porDia): [$y, $mm] = explode('-', $mes); $nMes = $this->months[$mes]; ?>
<section class="cc2-month" data-mes="<?= $esc($mes) ?>">
    <h2 class="cc2-month__h"><?= $meses[(int) $mm] ?> <?= $y ?> <small><?= $nMes ?> concierto<?= $nMes !== 1 ? 's' : '' ?></small></h2>

    <?php foreach ($porDia as $fecha => $conciertos):
        $d    = new DateTime($fecha);
        $diff = (int) $hoy->diff($d)->format('%r%a');
    ?>
    <?php foreach ($conciertos as $c):
        $titulo = $c->title ?: $c->band;
        $mul    = !empty($c->date_end) && $c->date_end !== $c->date_start;
        $can    = (int) $c->state === -1;
        $ps     = trim((string) ($c->poster_url ?? ''));
        if ($ps !== '' && !preg_match('#^https?://#i', $ps)) $ps = Uri::root() . ltrim($ps, '/');   // los carteles propios pueden guardarse como ruta relativa
        $det    = 'index.php?option=com_concerts&view=concert&id=' . (int) $c->id . $iid;
        $ics    = 'index.php?option=com_concerts&task=display.ics&id=' . (int) $c->id;
        $buscar = mb_strtolower($titulo . ' ' . ($c->support_bands ?? '') . ' ' . $c->city . ' ' . ($c->venue ?? '') . ' ' . ($c->province ?? ''), 'UTF-8');

        $prices = [];
        if (!empty($c->prices)) {
            $dec = json_decode($c->prices, true);
            if (is_array($dec)) $prices = array_values(array_filter($dec, fn($p) => !empty($p['label']) || !empty($p['amount'])));
        }

        $gcTitle = urlencode($titulo . ($c->city ? ' — ' . $c->city : ''));
        $gcDs    = str_replace('-', '', $c->date_start) . (!empty($c->concert_time) ? 'T' . str_replace(':', '', substr($c->concert_time, 0, 5)) . '00' : '');
        $gcDe    = $mul ? str_replace('-', '', $c->date_end) . 'T235959' : $gcDs;
        $gcLoc   = urlencode(implode(', ', array_filter([$c->venue, $c->city, $c->country_name])));
        $gcUrl   = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gcTitle}&dates={$gcDs}/{$gcDe}&location={$gcLoc}";

        $cuando = '';
        if (!$this->past) {
            if ($diff === 0) $cuando = 'Hoy';
            elseif ($diff === 1) $cuando = 'Mañana';
            elseif ($diff > 1 && $diff <= 14) $cuando = 'En ' . $diff . ' días';
        }
    ?>
    <article class="cc2-ev<?= $can ? ' is-cancelled' : '' ?>" data-tipo="<?= $esc($c->event_type ?? 'concert') ?>" data-mes="<?= $esc($mes) ?>" data-prov="<?= $esc($c->province ?? '') ?>" data-q="<?= $esc($buscar) ?>">
        <div class="cc2-date" aria-label="<?= $esc($dias[(int) $d->format('w')] . ' ' . $d->format('j') . ' de ' . $meses[(int) $d->format('n')]) ?>">
            <span class="cc2-date__dow"><?= $dias[(int) $d->format('w')] ?></span>
            <b><?= $d->format('j') ?></b>
            <span class="cc2-date__mon"><?= $mesCorto[(int) $d->format('n')] ?></span>
            <?php if ($mul): ?><span class="cc2-date__end">→ <?= (new DateTime($c->date_end))->format('j') ?> <?= $mesCorto[(int) (new DateTime($c->date_end))->format('n')] ?></span><?php endif; ?>
        </div>

        <?php if ($ps): ?>
        <img class="cc2-thumb" src="<?= $esc($ps) ?>" alt="Cartel de <?= $esc($titulo) ?>" loading="lazy"
             onclick="CcLb.open(this.src, <?= $esc(json_encode($titulo, JSON_UNESCAPED_UNICODE)) ?>)" onerror="this.remove()">
        <?php endif; ?>

        <div class="cc2-main">
            <a class="cc2-name" href="<?= $esc($det) ?>"><?= $esc($titulo) ?></a>
            <?php if (!empty($c->support_bands)): ?><div class="cc2-sup">+ <?= $esc($c->support_bands) ?></div>
            <?php elseif (!empty($c->lineup)): $lu = array_slice(array_map('trim', explode(',', $c->lineup)), 0, 8); ?><div class="cc2-sup"><?= $esc(implode(' · ', $lu)) ?><?= substr_count($c->lineup, ',') >= 8 ? ' …' : '' ?></div><?php endif; ?>
            <div class="cc2-where">
                <?php $fl = $flag((string) $c->country_code); if ($fl && $c->country_code !== 'ES') echo $fl . ' '; ?>
                <b><?= $esc($c->city) ?></b><?php if (!empty($c->province) && $c->province !== $c->city): ?><span class="cc2-prov"> · <?= $esc($c->province) ?></span><?php endif; ?>
                <?php if (!empty($c->venue)): ?><span class="cc2-venue"> — <?= $esc($c->venue) ?></span><?php endif; ?>
            </div>
            <div class="cc2-badges">
                <?php if (($c->event_type ?? '') === 'festival'): ?><span class="cc2-badge cc2-badge--fest">Festival</span><?php endif; ?>
                <?php if ($cuando): ?><span class="cc2-badge cc2-badge--soon"><?= $esc($cuando) ?></span><?php endif; ?>
                <?php if ($can): ?><span class="cc2-badge cc2-badge--bad">Cancelado</span><?php endif; ?>
                <?php if (!empty($c->doors_time)): ?><span class="cc2-badge">Puertas <?= substr($c->doors_time, 0, 5) ?></span><?php endif; ?>
                <?php if (!empty($c->concert_time)): ?><span class="cc2-badge">Concierto <?= substr($c->concert_time, 0, 5) ?></span><?php endif; ?>
                <?php foreach (array_slice($prices, 0, 3) as $p): ?>
                <span class="cc2-badge cc2-badge--price<?= !empty($p['sold_out']) ? ' is-sold' : '' ?>"><?= $esc($p['label'] ?? '') ?><?php if (!empty($p['amount'])): ?> <?= $esc($p['amount']) ?><?= (strpos($p['amount'], '€') === false && strpos($p['amount'], '$') === false) ? ' €' : '' ?><?php endif; ?><?= !empty($p['sold_out']) ? ' · agotadas' : '' ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="cc2-acts">
            <?php if (!empty($c->ticket_url) && !$this->past): ?><a class="cc2-btn cc2-btn--fire" href="<?= $esc($c->ticket_url) ?>" target="_blank" rel="noopener nofollow">🎟 Entradas</a><?php endif; ?>
            <?php if (!empty($c->website_url)): ?><a class="cc2-btn" href="<?= $esc($c->website_url) ?>" target="_blank" rel="noopener nofollow">Web</a><?php endif; ?>
            <?php if (!$this->past): ?>
            <div class="cc-cal-wrap">
                <button type="button" class="cc2-btn" onclick="ccToggleCal(this)" aria-label="Añadir al calendario">📅 ▾</button>
                <div class="cc-cal-menu">
                    <a href="<?= $esc($gcUrl) ?>" target="_blank" rel="noopener">Google Calendar</a>
                    <a href="<?= $esc($ics) ?>">Outlook / Apple (.ics)</a>
                </div>
            </div>
            <?php endif; ?>
            <a class="cc2-btn" href="<?= $esc($det) ?>">Más info</a>
        </div>
    </article>
    <?php endforeach; ?>
    <?php endforeach; ?>
</section>
<?php endforeach; ?>
<p class="cc2-none" id="cc2-none" hidden>Ningún concierto coincide con tu búsqueda. <a href="#" id="cc2-reset">Quitar filtros</a></p>
</div>

<?php endif; ?>
</div>

<div class="cc-lb" id="ccLightbox" onclick="if(event.target===this)CcLb.close()">
    <div class="cc-lb-img-wrap">
        <button class="cc-lb-close" onclick="CcLb.close()" title="Cerrar (Esc)">✕</button>
        <img class="cc-lb-img" src="" alt="" onclick="event.stopPropagation()" />
    </div>
    <div class="cc-lb-cap"></div>
</div>
