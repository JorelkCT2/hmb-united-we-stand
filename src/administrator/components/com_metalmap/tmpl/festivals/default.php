<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Metalmap\Administrator\View\Festivals\HtmlView $this */

// Estado actual
$listOrder = (string) $this->state->get('list.ordering',  'a.start_date');
$listDirn  = strtoupper((string) $this->state->get('list.direction', 'ASC'));
$listStart = (int) $this->state->get('list.start',  0);
$listLimit = (int) $this->state->get('list.limit',  20);
$searchVal = (string) $this->state->get('filter.search', '');

// Paginación: total y nº de páginas
$totalItems = $this->pagination ? (int) $this->pagination->total : count($this->items);
$totalPages = $listLimit > 0 ? (int) ceil($totalItems / $listLimit) : 1;
$currentPage = $listLimit > 0 ? (int) floor($listStart / $listLimit) + 1 : 1;

/**
 * Construye una URL del listado con todos los params persistentes + overrides.
 * IMPORTANTE: Route::_($url, false) → no aplica htmlspecialchars al output,
 * porque ya lo hacemos nosotros en el template con htmlspecialchars().
 * Si no, doble encoding → URL inválida.
 */
$urlWith = function (array $overrides = []) use ($listOrder, $listDirn, $listStart, $listLimit, $searchVal) {
    $params = array_merge([
        'option'           => 'com_metalmap',
        'view'             => 'festivals',
        'filter_search'    => $searchVal,
        'filter_order'     => $listOrder,
        'filter_order_Dir' => $listDirn,
        'limitstart'       => $listStart,
        'limit'            => $listLimit,
    ], $overrides);

    $query = [];
    foreach ($params as $k => $v) {
        if ($v === '' || $v === null) continue;
        $query[] = $k . '=' . urlencode((string) $v);
    }
    // false → Route NO codifica & como &amp;. Lo hacemos nosotros en el href.
    return Route::_('index.php?' . implode('&', $query), false);
};

/**
 * Cabecera ordenable.
 * Al cambiar de columna o invertir dirección, reset limitstart=0.
 */
$sortLink = function (string $label, string $column, string $extraCls = '', string $width = '') use ($listOrder, $listDirn, $urlWith) {
    $isActive = ($listOrder === $column);
    $newDir   = $isActive && $listDirn === 'ASC' ? 'DESC' : 'ASC';
    $arrow    = $isActive ? ($listDirn === 'ASC' ? ' ▲' : ' ▼') : '';

    $url = $urlWith([
        'filter_order'     => $column,
        'filter_order_Dir' => $newDir,
        'limitstart'       => 0,
    ]);

    $style = $width !== '' ? ' style="width:' . $width . '"' : '';
    $cls   = trim('sortable-header ' . $extraCls);

    return '<th scope="col" class="' . $cls . '"' . $style . '>'
         . '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="text-decoration-none">'
         . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
         . '<span class="sort-arrow">' . $arrow . '</span>'
         . '</a></th>';
};
?>

<style>
    .sortable-header a { color: inherit !important; font-weight: 600; cursor: pointer; }
    .sortable-header a:hover { color: #cc0000 !important; text-decoration: underline !important; }
    .sort-arrow { color: #cc0000; font-size: .85em; margin-left: 4px; }

    .mm-pager { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin: 1rem 0; }
    .mm-pager__info { color: var(--bs-secondary-color, #888); margin-right: auto; font-size: .9em; }
    .mm-pager a, .mm-pager span.mm-pager__page {
        display: inline-block;
        padding: .35rem .7rem;
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 4px;
        text-decoration: none;
        color: inherit;
        min-width: 36px;
        text-align: center;
    }
    .mm-pager a:hover { background: rgba(204,0,0,.2); border-color: #cc0000; color: #fff; }
    .mm-pager .mm-pager__current { background: #cc0000; color: #fff; border-color: #cc0000; font-weight: bold; }
    .mm-pager .mm-pager__disabled { opacity: .35; pointer-events: none; }

    /* ─── MARCADOR DE VERIFICACIÓN ─── */
    .mm-template-marker {
        background: #cc0000;
        color: #fff;
        font-family: monospace;
        font-weight: bold;
        padding: 8px 14px;
        margin-bottom: 12px;
        border-radius: 4px;
        font-size: 14px;
        letter-spacing: 1px;
        text-align: center;
    }
</style>

<?php // marcador y debug retirados — bug resuelto en versión 2.5 ?>

<form action="<?= Route::_('index.php?option=com_metalmap&view=festivals'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">

                <!-- Búsqueda manual -->
                <div class="js-stools clearfix mb-3">
                    <div class="js-stools-container-bar">
                        <input type="text"
                               name="filter_search"
                               id="filter_search"
                               value="<?= $this->escape($searchVal); ?>"
                               class="form-control"
                               style="max-width:400px;display:inline-block"
                               placeholder="<?= $this->escape(Text::_('JSEARCH_FILTER')); ?>">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-search" aria-hidden="true"></span> <?= Text::_('JSEARCH_FILTER_SUBMIT'); ?>
                        </button>
                        <a href="<?= $urlWith(['filter_search' => '', 'limitstart' => 0]); ?>" class="btn btn-secondary">
                            <span class="icon-times" aria-hidden="true"></span> <?= Text::_('JSEARCH_FILTER_CLEAR'); ?>
                        </a>
                    </div>
                </div>

                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <?= Text::_('COM_METALMAP_NO_FESTIVALS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table table-striped" id="festivalList">
                        <thead>
                            <tr>
                                <td style="width:1%" class="text-center">
                                    <?= HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <?= $sortLink(Text::_('JSTATUS'),                       'a.state',      'text-center', '5%'); ?>
                                <?= $sortLink(Text::_('COM_METALMAP_FIELD_NAME'),       'a.name'); ?>
                                <?= $sortLink(Text::_('COM_METALMAP_FIELD_EVENT_TYPE'), 'a.event_type', '', '10%'); ?>
                                <?= $sortLink(Text::_('COM_METALMAP_FIELD_COUNTRY'),    'a.country',    '', '15%'); ?>
                                <?= $sortLink(Text::_('COM_METALMAP_FIELD_START_DATE'), 'a.start_date', '', '10%'); ?>
                                <th scope="col" style="width:10%" class="text-center">
                                    <?= Text::_('COM_METALMAP_FIELD_COORDS'); ?>
                                </th>
                                <?= $sortLink(Text::_('JGRID_HEADING_ID'),              'a.id',         'text-center', '5%'); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item) :
                                $editUrl = Route::_('index.php?option=com_metalmap&task=festival.edit&id=' . (int) $item->id);
                            ?>
                                <tr class="row<?= $i % 2; ?>">
                                    <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $item->id); ?></td>
                                    <td class="text-center">
                                        <?= $item->state == 1
                                            ? '<span class="badge bg-success">✓</span>'
                                            : '<span class="badge bg-secondary">—</span>'; ?>
                                    </td>
                                    <td>
                                        <a href="<?= $editUrl; ?>"><strong><?= $this->escape($item->name); ?></strong></a>
                                        <?php if (!empty($item->city)) : ?>
                                            <div class="small text-muted"><?= $this->escape($item->city); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $item->event_type === 'concert' ? 'info' : 'danger'; ?>">
                                            <?= $this->escape(ucfirst($item->event_type)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($item->country_code)) : ?>
                                            <img src="https://flagcdn.com/w20/<?= strtolower($this->escape($item->country_code)); ?>.png"
                                                 alt="<?= $this->escape($item->country); ?>"
                                                 width="20"
                                                 style="vertical-align:middle;margin-right:6px"
                                                 onerror="this.style.display='none'">
                                        <?php endif; ?>
                                        <?= $this->escape($item->country); ?>
                                    </td>
                                    <td><?= $item->start_date ? HTMLHelper::_('date', $item->start_date, 'd M Y') : '—'; ?></td>
                                    <td class="text-center small">
                                        <?= number_format((float) $item->latitude, 4); ?>,<br>
                                        <?= number_format((float) $item->longitude, 4); ?>
                                    </td>
                                    <td class="text-center"><?= (int) $item->id; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- ── PAGINACIÓN CUSTOM — preserva orden y búsqueda ── -->
                    <?php if ($totalPages > 1) : ?>
                        <nav class="mm-pager" aria-label="Paginación">
                            <span class="mm-pager__info">
                                <?= sprintf('Página %d de %d · %d resultados', $currentPage, $totalPages, $totalItems); ?>
                            </span>

                            <?php // ‹‹ Primera
                            if ($currentPage > 1) : ?>
                                <a href="<?= $urlWith(['limitstart' => 0]); ?>" title="Primera">««</a>
                                <a href="<?= $urlWith(['limitstart' => max(0, ($currentPage - 2) * $listLimit)]); ?>" title="Anterior">‹</a>
                            <?php else : ?>
                                <span class="mm-pager__page mm-pager__disabled">««</span>
                                <span class="mm-pager__page mm-pager__disabled">‹</span>
                            <?php endif; ?>

                            <?php // Ventana de páginas alrededor de la actual
                            $startPage = max(1, $currentPage - 3);
                            $endPage   = min($totalPages, $currentPage + 3);
                            for ($p = $startPage; $p <= $endPage; $p++) :
                                if ($p === $currentPage) : ?>
                                    <span class="mm-pager__page mm-pager__current"><?= $p; ?></span>
                                <?php else : ?>
                                    <a href="<?= $urlWith(['limitstart' => ($p - 1) * $listLimit]); ?>"><?= $p; ?></a>
                                <?php endif;
                            endfor; ?>

                            <?php // Siguiente / Última
                            if ($currentPage < $totalPages) : ?>
                                <a href="<?= $urlWith(['limitstart' => $currentPage * $listLimit]); ?>" title="Siguiente">›</a>
                                <a href="<?= $urlWith(['limitstart' => ($totalPages - 1) * $listLimit]); ?>" title="Última">»»</a>
                            <?php else : ?>
                                <span class="mm-pager__page mm-pager__disabled">›</span>
                                <span class="mm-pager__page mm-pager__disabled">»»</span>
                            <?php endif; ?>

                            <!-- Selector de items por página -->
                            <select onchange="window.location.href=this.value" class="form-select form-select-sm" style="width:auto;margin-left:8px">
                                <?php foreach ([10, 20, 30, 50, 100, 200] as $lim) : ?>
                                    <option value="<?= $urlWith(['limit' => $lim, 'limitstart' => 0]); ?>"
                                            <?= $lim === $listLimit ? 'selected' : ''; ?>>
                                        <?= $lim; ?> / página
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </nav>
                    <?php endif; ?>

                <?php endif; ?>

                <!-- Hidden values con estado actual -->
                <input type="hidden" name="option"           value="com_metalmap">
                <input type="hidden" name="view"             value="festivals">
                <input type="hidden" name="filter_order"     value="<?= $this->escape($listOrder); ?>">
                <input type="hidden" name="filter_order_Dir" value="<?= $this->escape($listDirn); ?>">
                <input type="hidden" name="limitstart"        value="0">
                <input type="hidden" name="limit"             value="<?= (int) $listLimit; ?>">
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?= HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
