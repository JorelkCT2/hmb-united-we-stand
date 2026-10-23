<?php
defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

$input    = Factory::getApplication()->getInput();
$search   = $input->getString('filter_search', '');
$genre    = $input->getString('filter_genre', '');
$location = $input->getString('filter_location', '');
$ordering = $input->getString('filter_order', 'a.name');
$dirn     = $input->getString('filter_dirn', 'ASC');
$nextDirn = $dirn === 'ASC' ? 'DESC' : 'ASC';

function sortIcon(string $col, string $current, string $dirn): string {
    if ($col !== $current) return '<span style="opacity:.3;font-size:.75rem;">⇅</span>';
    return $dirn === 'ASC' ? '<span style="color:#f5c842;font-size:.8rem;">▲</span>' : '<span style="color:#f5c842;font-size:.8rem;">▼</span>';
}
function sortUrl(string $col, string $current, string $dirn): string {
    $app     = Factory::getApplication();
    $input   = $app->getInput();
    $newDirn = ($col === $current && $dirn === 'ASC') ? 'DESC' : 'ASC';
    $itemid  = \Joomla\Component\Artistas\Site\Helper\RouteHelper::getItemid('artistas');
    return 'index.php?option=com_artistas&view=artistas'
        . ($itemid ? '&Itemid=' . $itemid : '')
        . '&filter_search=' . urlencode($input->getString('filter_search',''))
        . '&filter_genre='  . urlencode($input->getString('filter_genre',''))
        . '&filter_location=' . urlencode($input->getString('filter_location',''))
        . '&filter_order=' . $col
        . '&filter_dirn='  . $newDirn;
}

// Cargar géneros para el filtro
try {
    $db = Factory::getContainer()->get('DatabaseDriver');
    $db->setQuery('SELECT name FROM #__artistas_generos WHERE state=1 ORDER BY ordering ASC, name ASC');
    $generosDB = $db->loadColumn() ?: [];
} catch (\Exception $e) {
    $generosDB = [];
}
?>

<style>
/* ===== ARTISTAS LISTADO - DARK THEME ===== */
.al-wrap { color: #e8e0d5; }

/* Filtros */
.al-filtros {
    background: rgba(10,8,6,0.85);
    border: 1px solid rgba(180,140,80,.35);
    border-radius: 10px;
    padding: 18px 20px;
    margin-bottom: 24px;
    backdrop-filter: blur(6px);
}
.al-filtros .form-control,
.al-filtros .form-select {
    background: rgba(255,255,255,.07) !important;
    border: 1px solid rgba(180,140,60,.4) !important;
    color: #f0e8d8 !important;
    border-radius: 6px;
}
.al-filtros .form-control::placeholder { color: #6a5f4a !important; }
.al-filtros .form-control:focus,
.al-filtros .form-select:focus {
    background: rgba(255,255,255,.12) !important;
    border-color: rgba(200,160,60,.85) !important;
    box-shadow: 0 0 0 3px rgba(180,130,30,.25) !important;
    color: #fff !important;
}
.al-filtros .form-select option { background:#1a1208; color:#f0e0c0; }
.al-btn-search {
    background: linear-gradient(135deg,#b00000,#7a0000);
    border: 1px solid rgba(180,40,40,.5);
    color: #fff;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    border-radius: 6px;
    padding: 10px 28px;
    cursor: pointer;
    transition: all .2s;
    font-family: inherit;
}
.al-btn-search:hover { background: linear-gradient(135deg,#cc0000,#900000); transform:translateY(-1px); box-shadow:0 4px 14px rgba(180,0,0,.4); }
.al-btn-reset {
    background: transparent;
    border: 1px solid rgba(180,140,60,.4);
    color: #c9b890;
    border-radius: 6px;
    padding: 10px 16px;
    cursor: pointer;
    font-size: .85rem;
    transition: all .2s;
    text-decoration: none;
    display: inline-block;
}
.al-btn-reset:hover { border-color:rgba(200,160,60,.8); color:#f5c842; }

/* Barra de ordenación */
.al-sort-bar {
    background: rgba(10,8,6,.7);
    border: 1px solid rgba(180,140,80,.2);
    border-radius: 8px;
    padding: 10px 16px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.al-sort-bar span { color:#8a7a60; font-size:.82rem; margin-right:4px; }
.al-sort-btn {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(180,140,60,.25);
    color: #c9b890;
    border-radius: 5px;
    padding: 4px 12px;
    font-size: .82rem;
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
    display:inline-flex;
    align-items:center;
    gap:5px;
}
.al-sort-btn:hover { background:rgba(180,130,20,.2); border-color:rgba(200,160,60,.5); color:#f5c842; }
.al-sort-btn.active { background:rgba(180,130,20,.25); border-color:rgba(200,160,60,.6); color:#f5c842; }

/* Contador */
.al-count { color:#6a5f4a; font-size:.85rem; margin-left:auto; }

/* Grid de artistas */
.al-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:20px; }

/* Tarjeta */
.al-card {
    background: rgba(10,8,6,.82);
    border: 1px solid rgba(180,140,80,.3);
    border-radius: 10px;
    overflow: hidden;
    backdrop-filter: blur(4px);
    transition: transform .2s, border-color .2s, box-shadow .2s;
    display: flex;
    flex-direction: column;
}
.al-card:hover {
    transform: translateY(-5px);
    border-color: rgba(200,160,60,.6);
    box-shadow: 0 8px 28px rgba(0,0,0,.5);
}
.al-card__img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
    filter: brightness(.85);
    transition: filter .3s;
}
.al-card:hover .al-card__img { filter: brightness(1); }

.al-card__body { padding: 14px 16px; flex:1; }
.al-card__genre {
    display: inline-block;
    background: rgba(180,120,20,.35);
    border: 1px solid rgba(180,140,60,.4);
    color: #d4bb8a;
    font-size: .75rem;
    font-weight: 700;
    padding: 2px 10px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 8px;
}
.al-card__name {
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 1px 4px rgba(0,0,0,.6);
    margin: 0 0 4px;
    line-height: 1.2;
}
.al-card__name a { color: inherit; text-decoration: none; }
.al-card__name a:hover { color: #f5c842; }
.al-card__desc { color: #8a7a60; font-size: .85rem; margin: 6px 0 0; line-height: 1.4; }

.al-card__footer {
    padding: 10px 16px 14px;
    border-top: 1px solid rgba(180,140,80,.15);
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.al-card__meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .82rem;
}
.al-stars { color: #f5c842; letter-spacing: -1px; }
.al-stars-empty { color: #3a3020; }
.al-rating-val { color: #8a7a60; font-size: .78rem; margin-left: 4px; }
.al-location { color: #6a5f4a; font-size: .8rem; }
.al-formats { display:flex; flex-wrap:wrap; gap:4px; }
.al-fmt-badge {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(180,140,60,.25);
    color: #9a8a6a;
    font-size: .72rem;
    padding: 1px 8px;
    border-radius: 3px;
}
.al-btn-ficha {
    display: block;
    text-align: center;
    background: linear-gradient(135deg,rgba(180,120,20,.5),rgba(120,80,10,.5));
    border: 1px solid rgba(180,140,60,.4);
    color: #f0d890;
    font-weight: 700;
    font-size: .85rem;
    padding: 8px;
    border-radius: 6px;
    text-decoration: none;
    letter-spacing: .5px;
    text-transform: uppercase;
    transition: all .2s;
}
.al-btn-ficha:hover { background:linear-gradient(135deg,rgba(200,140,30,.7),rgba(140,90,15,.7)); color:#fff; border-color:rgba(220,170,60,.7); }

/* Vacío */
.al-empty {
    background: rgba(10,8,6,.7);
    border: 1px dashed rgba(180,140,80,.3);
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    color: #6a5f4a;
    grid-column: 1/-1;
}
.al-empty a { color: #d4bb8a; }

/* Paginación */
.al-pagination { margin-top: 28px; display:flex; justify-content:center; }
.al-pagination .pagination { gap: 4px; }
.al-pagination .page-link { background:rgba(10,8,6,.8); border-color:rgba(180,140,60,.3); color:#c9b890; }
.al-pagination .page-link:hover { background:rgba(180,130,20,.3); color:#f5c842; border-color:rgba(200,160,60,.5); }
.al-pagination .page-item.active .page-link { background:rgba(180,120,20,.6); border-color:rgba(200,160,60,.6); color:#fff; }
</style>

<div class="al-wrap">

    <!-- FILTROS -->
    <div class="al-filtros">
        <form id="artistas-search-form" action="index.php" method="get">
            <input type="hidden" name="option" value="com_artistas">
            <input type="hidden" name="view" value="artistas">
            <?php if ($this->state->get('itemid') || ($__artItemid = \Joomla\Component\Artistas\Site\Helper\RouteHelper::getItemid('artistas'))): ?>
            <input type="hidden" name="Itemid" value="<?= $__artItemid ?? $this->state->get('itemid') ?>">
            <?php endif; ?>
            <input type="hidden" name="option" value="com_artistas">
            <input type="hidden" name="view" value="artistas">
            <input type="hidden" name="filter_order" value="<?= htmlspecialchars($ordering) ?>">
            <input type="hidden" name="filter_dirn" value="<?= htmlspecialchars($dirn) ?>">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="filter_search" value="<?= htmlspecialchars($search) ?>"
                           class="form-control" placeholder="🔍 Buscar por nombre, género o ciudad…">
                </div>
                <div class="col-md-3">
                    <select name="filter_genre" class="form-select">
                        <option value="">🎵 Todos los géneros</option>
                        <?php foreach ($generosDB as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>" <?= $genre === $g ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="filter_location" class="form-select">
                        <option value="">📍 Todas las provincias</option>
                        <?php foreach (['Álava','Albacete','Alicante','Almería','Asturias','Ávila','Badajoz','Barcelona','Burgos','Cáceres','Cádiz','Cantabria','Castellón','Ceuta','Ciudad Real','Córdoba','La Coruña','Cuenca','Girona','Granada','Guadalajara','Gipuzkoa','Huelva','Huesca','Islas Baleares','Jaén','León','Lleida','La Rioja','Lugo','Madrid','Málaga','Melilla','Murcia','Navarra','Ourense','Palencia','Las Palmas','Pontevedra','Salamanca','Santa Cruz de Tenerife','Segovia','Sevilla','Soria','Tarragona','Teruel','Toledo','Valencia','Valladolid','Vizcaya','Zamora','Zaragoza'] as $__prov): ?>
                        <option value="<?= htmlspecialchars($__prov) ?>" <?= $location === $__prov ? 'selected' : '' ?>><?= htmlspecialchars($__prov) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="al-btn-search flex-grow-1">Buscar</button>
                    <a href="<?= Route::_('index.php?option=com_artistas&view=artistas') ?>" class="al-btn-reset">✕</a>
                </div>
            </div>
        </form>
    </div>

    <!-- BARRA ORDENACIÓN -->
    <div class="al-sort-bar">
        <span>Ordenar por:</span>
        <a href="<?= sortUrl('a.name', $ordering, $dirn) ?>"
           class="al-sort-btn <?= $ordering === 'a.name' ? 'active' : '' ?>">
            Nombre <?= sortIcon('a.name', $ordering, $dirn) ?>
        </a>
        <a href="<?= sortUrl('a.genre', $ordering, $dirn) ?>"
           class="al-sort-btn <?= $ordering === 'a.genre' ? 'active' : '' ?>">
            Género <?= sortIcon('a.genre', $ordering, $dirn) ?>
        </a>
        <a href="<?= sortUrl('a.rating', $ordering, $dirn) ?>"
           class="al-sort-btn <?= $ordering === 'a.rating' ? 'active' : '' ?>">
            Valoración <?= sortIcon('a.rating', $ordering, $dirn) ?>
        </a>
        <a href="<?= sortUrl('a.location', $ordering, $dirn) ?>"
           class="al-sort-btn <?= $ordering === 'a.location' ? 'active' : '' ?>">
            Provincia <?= sortIcon('a.location', $ordering, $dirn) ?>
        </a>
        <a href="<?= sortUrl('a.created', $ordering, $dirn) ?>"
           class="al-sort-btn <?= $ordering === 'a.created' ? 'active' : '' ?>">
            Más recientes <?= sortIcon('a.created', $ordering, $dirn) ?>
        </a>
        <span class="al-count"><?= count($this->items) ?> artista(s)</span>
    </div>

    <!-- GRID -->
    <div class="al-grid">
        <?php
        // Buscar el Itemid del menu item de la ficha de artista
        $__menu    = \Joomla\CMS\Factory::getApplication()->getMenu();
        $__items   = $__menu->getItems('component_id', \Joomla\CMS\Component\ComponentHelper::getComponent('com_artistas')->id);
        $__itemid  = '';
        foreach ($__items as $__mi) {
            if (strpos($__mi->link, 'view=artista') !== false) {
                $__itemid = '&Itemid=' . $__mi->id;
                break;
            }
        }
        ?>
        <?php foreach ($this->items as $item):
            $img      = !empty($item->image) ? Uri::root() . $item->image : 'https://placehold.co/400x220/0a0806/c9b890?text=' . urlencode($item->name);
            $url      = 'index.php?option=com_artistas&view=artista&id=' . $item->id . $__itemid;
            $formats  = !empty($item->formats) ? (json_decode($item->formats, true) ?: []) : [];
            $stars    = (int)round($item->rating);
        ?>
        <div class="al-card">
            <a href="<?= $url ?>">
                <img src="<?= $img ?>" class="al-card__img" alt="<?= htmlspecialchars($item->name) ?>" loading="lazy">
            </a>
            <div class="al-card__body">
                <div class="al-card__genre"><?= htmlspecialchars($item->genre) ?></div>
                <h3 class="al-card__name">
                    <a href="<?= $url ?>"><?= htmlspecialchars($item->name) ?></a>
                </h3>
                <?php if (!empty($item->short_desc)): ?>
                <p class="al-card__desc"><?= htmlspecialchars(mb_substr($item->short_desc, 0, 90)) ?><?= mb_strlen($item->short_desc) > 90 ? '…' : '' ?></p>
                <?php endif; ?>
            </div>
            <div class="al-card__footer">
                <div class="al-card__meta">
                    <div>
                        <span class="al-stars"><?= str_repeat('★', $stars) ?></span><span class="al-stars-empty"><?= str_repeat('★', 5 - $stars) ?></span>
                        <span class="al-rating-val">(<?= (int)$item->review_count ?>)</span>
                    </div>
                    <span class="al-location">📍 <?= htmlspecialchars($item->location) ?></span>
                </div>
                <?php if (!empty($formats)): ?>
                <div class="al-formats">
                    <?php foreach ($formats as $f): ?>
                    <span class="al-fmt-badge"><?= htmlspecialchars(ucfirst($f)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <a href="<?= $url ?>" class="al-btn-ficha">Ver ficha completa</a>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($this->items)): ?>
        <div class="al-empty">
            <div style="font-size:3rem;margin-bottom:12px;">🎸</div>
            <p>No se encontraron artistas con los criterios seleccionados.</p>
            <a href="<?= Route::_('index.php?option=com_artistas&view=artistas') ?>">Ver todos los artistas</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- PAGINACIÓN -->
    <?php if ($this->pagination->pagesTotal > 1): ?>
    <div class="al-pagination">
        <?= $this->pagination->getPagesLinks() ?>
    </div>
    <?php endif; ?>

</div>
