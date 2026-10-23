<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\Metalmap\Site\View\Map\HtmlView $this */

$wa = $this->document->getWebAssetManager();

// Leaflet CSS y JS desde CDN oficial (versión estable)
$wa->registerAndUseStyle(
    'leaflet',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    [],
    ['integrity' => 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=', 'crossorigin' => 'anonymous']
);
$wa->registerAndUseScript(
    'leaflet',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    [],
    ['integrity' => 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=', 'crossorigin' => 'anonymous']
);
$wa->registerAndUseScript(
    'leaflet.markercluster',
    'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js',
    [],
    [],
    ['leaflet']
);
$wa->registerAndUseStyle(
    'leaflet.markercluster',
    'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css'
);
$wa->registerAndUseStyle(
    'leaflet.markercluster.default',
    'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css'
);

// CSS y JS propios
$wa->registerAndUseStyle('com_metalmap.style', 'media/com_metalmap/css/metalmap.css', ['version' => '2.4.5']);
$wa->registerAndUseScript('com_metalmap.script', 'media/com_metalmap/js/metalmap.js', ['version' => '2.4.5'], ['defer' => true], ['leaflet', 'leaflet.markercluster']);

// Fuentes Google
$this->document->addStyleSheet('https://fonts.googleapis.com/css2?family=Metal+Mania&family=Cinzel:wght@500;700;900&family=Rajdhani:wght@400;600;700&display=swap');

// Pasar datos a JavaScript
$config = [
    'events'        => $this->events,
    'default_lat'   => (float) $this->params->get('default_lat', 50.5),
    'default_lng'   => (float) $this->params->get('default_lng', 10.0),
    'default_zoom'  => (int)   $this->params->get('default_zoom', 4),
    'tile_provider' => (string)$this->params->get('tile_provider', 'cartodb_dark'),
    'labels'        => [
        'website'       => Text::_('COM_METALMAP_LABEL_OFFICIAL_SITE'),
        'when'          => Text::_('COM_METALMAP_LABEL_WHEN'),
        'where'         => Text::_('COM_METALMAP_LABEL_WHERE'),
        'bands'         => Text::_('COM_METALMAP_LABEL_BANDS'),
        'no_events'     => Text::_('COM_METALMAP_LABEL_NO_EVENTS'),
    ],
];
$this->document->addScriptOptions('com_metalmap', $config);
?>

<div class="metalmap-wrapper">

    <!-- ─── HEADER ─────────────────────────────────────────────── -->
    <header class="mm-header">
        <div class="mm-header__decor mm-header__decor--left" aria-hidden="true"></div>
        <div class="mm-header__core">
            <div class="mm-header__skull" aria-hidden="true">
                <svg viewBox="0 0 64 64" width="44" height="44" fill="currentColor">
                    <path d="M32 4C18 4 8 14 8 28v10c0 4 2 7 6 8v8c0 2 1 4 4 4h4v-6c0-2 1-3 3-3s3 1 3 3v6h8v-6c0-2 1-3 3-3s3 1 3 3v6h4c3 0 4-2 4-4v-8c4-1 6-4 6-8V28C56 14 46 4 32 4zm-9 26a4 4 0 110-8 4 4 0 010 8zm18 0a4 4 0 110-8 4 4 0 010 8zM32 44l-4-6h8l-4 6z"/>
                </svg>
            </div>
            <h1 class="mm-header__title"><?= Text::_('COM_METALMAP_TITLE'); ?></h1>
            <p class="mm-header__subtitle"><?= Text::_('COM_METALMAP_SUBTITLE'); ?></p>
            <div class="mm-header__count">
                <span class="mm-header__count-num"><?= count($this->events); ?></span>
                <span class="mm-header__count-lbl"><?= Text::_('COM_METALMAP_LABEL_EVENTS'); ?></span>
            </div>
        </div>
        <div class="mm-header__decor mm-header__decor--right" aria-hidden="true"></div>
    </header>

    <!-- ─── BARRA DE FILTROS ───────────────────────────────────── -->
    <div class="mm-filters">
        <div class="mm-filters__search">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.7.7l.27.28v.79l5 5 1.49-1.49zM9.5 14C7 14 5 12 5 9.5S7 5 9.5 5 14 7 14 9.5 12 14 9.5 14z"/>
            </svg>
            <input type="text" id="mm-search" placeholder="<?= $this->escape(Text::_('COM_METALMAP_SEARCH_PLACEHOLDER')); ?>" autocomplete="off">
        </div>

        <div class="mm-filters__group">
            <button type="button" class="mm-pill mm-pill--active" data-filter="all">
                <?= Text::_('COM_METALMAP_FILTER_ALL'); ?>
            </button>
            <button type="button" class="mm-pill" data-filter="festival">
                🎪 <?= Text::_('COM_METALMAP_TYPE_FESTIVAL'); ?>
            </button>
            <button type="button" class="mm-pill" data-filter="concert">
                🎸 <?= Text::_('COM_METALMAP_TYPE_CONCERT'); ?>
            </button>
        </div>

        <?php if (!empty($this->countryList)) : ?>
            <select id="mm-country" class="mm-filters__select">
                <option value=""><?= Text::_('COM_METALMAP_FILTER_ALL_COUNTRIES'); ?></option>
                <?php foreach ($this->countryList as $code => $name) : ?>
                    <option value="<?= $this->escape($code); ?>"><?= $this->escape($name); ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <!-- ─── FILTRO POR FECHAS ─── -->
        <div class="mm-filters__dates">
            <label class="mm-filters__date-label">
                <span>📅 Desde</span>
                <input type="date" id="mm-date-from" class="mm-filters__date">
            </label>
            <label class="mm-filters__date-label">
                <span>Hasta</span>
                <input type="date" id="mm-date-to" class="mm-filters__date">
            </label>
            <button type="button" id="mm-date-apply" class="mm-btn-filter" title="Aplicar filtro de fechas">
                🔍 Filtrar
            </button>
            <button type="button" id="mm-date-clear" class="mm-pill" title="Limpiar fechas">✕</button>
        </div>

        <!-- ─── FILTROS RÁPIDOS POR ESTADO TEMPORAL ─── -->
        <div class="mm-filters__group">
            <button type="button" class="mm-pill" data-time-filter="upcoming" title="Próximos">
                <span class="mm-dot-tiny mm-dot-tiny--upcoming"></span> Próximos
            </button>
            <button type="button" class="mm-pill" data-time-filter="past" title="Pasados">
                <span class="mm-dot-tiny mm-dot-tiny--past"></span> Pasados
            </button>
        </div>
    </div>

    <!-- ─── MAPA + LISTA LATERAL ───────────────────────────────── -->
    <div class="mm-stage">
        <div class="mm-map-frame">
            <div class="mm-map-frame__corner mm-map-frame__corner--tl" aria-hidden="true"></div>
            <div class="mm-map-frame__corner mm-map-frame__corner--tr" aria-hidden="true"></div>
            <div class="mm-map-frame__corner mm-map-frame__corner--bl" aria-hidden="true"></div>
            <div class="mm-map-frame__corner mm-map-frame__corner--br" aria-hidden="true"></div>
            <div id="mm-map" class="mm-map"></div>
        </div>

        <aside class="mm-list">
            <div class="mm-list__header">
                <h3><?= Text::_('COM_METALMAP_LIST_TITLE'); ?></h3>
                <span class="mm-list__count" id="mm-list-count"><?= count($this->events); ?></span>
            </div>
            <div class="mm-list__body" id="mm-list-body">
                <!-- Inyectado por JS -->
            </div>
        </aside>
    </div>

    <!-- ─── LEGEND ─────────────────────────────────────────────── -->
    <div class="mm-legend">
        <span class="mm-legend__item">
            <span class="mm-legend__dot mm-legend__dot--upcoming"></span>
            Próximo
        </span>
        <span class="mm-legend__item">
            <span class="mm-legend__dot mm-legend__dot--past"></span>
            Finalizado
        </span>
        <span class="mm-legend__item">
            <span class="mm-legend__dot mm-legend__dot--mixed"></span>
            Grupo mixto
        </span>
        <span class="mm-legend__item">
            🎪 Festival · 🎸 Concierto
        </span>
        <span class="mm-legend__item mm-legend__credit">
            <?= Text::_('COM_METALMAP_TILES_BY'); ?>
        </span>
    </div>

    <!-- ─── MODAL FICHA EFEMERIDES ──────────────────────────────────
         Carga la ficha de banda o músico de com_efemerides dentro de
         un iframe (tmpl=component → sin cabecera/footer del sitio).
         Permite navegar entre las fichas con ficha del mismo evento.
    -->
    <div id="mm-modal" class="mm-modal" hidden aria-hidden="true" role="dialog" aria-labelledby="mm-modal-title">
        <div class="mm-modal__backdrop" data-mm-close></div>
        <div class="mm-modal__dialog" role="document">
            <header class="mm-modal__header">
                <button type="button" class="mm-modal__nav" id="mm-modal-prev" title="Anterior" aria-label="Anterior">‹</button>
                <h2 class="mm-modal__title" id="mm-modal-title">—</h2>
                <span class="mm-modal__counter" id="mm-modal-counter"></span>
                <button type="button" class="mm-modal__nav" id="mm-modal-next" title="Siguiente" aria-label="Siguiente">›</button>
                <button type="button" class="mm-modal__close" data-mm-close title="Cerrar (Esc)" aria-label="Cerrar">✕</button>
            </header>
            <div class="mm-modal__body">
                <div class="mm-modal__loader" id="mm-modal-loader">Cargando…</div>
                <iframe id="mm-modal-frame" class="mm-modal__frame" src="about:blank" loading="lazy"></iframe>
            </div>
        </div>
    </div>

</div>
