<?php
/**
 * HMB — Override layout BLOG de categoría (com_content / category / blog)
 * Mismo grid de tarjetas metal que el listado (default_articles.php),
 * para que al entrar a una categoría se vea idéntico al listado.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$root = rtrim(Uri::root(), '/');

/**
 * Extrae la URL de la imagen de intro/portada de un artículo
 */
if (!function_exists('hmb_article_img')) {
    function hmb_article_img(object $article, string $root): string
    {
        $raw = $article->images ?? '';
        if (!$raw) return '';
        $data = is_string($raw) ? json_decode($raw) : $raw;
        if (!$data) return '';
        $img = $data->image_intro ?: ($data->image_fulltext ?? '');
        if (!$img) return '';
        return preg_match('/^https?:\/\//i', $img) ? $img : $root . '/' . ltrim($img, '/');
    }
}

/**
 * URL del artículo
 */
if (!function_exists('hmb_article_url')) {
    function hmb_article_url(object $article): string
    {
        try {
            return Route::_(RouteHelper::getArticleRoute($article->id, $article->catid, $article->language));
        } catch (\Throwable $e) {
            return Route::_('index.php?option=com_content&view=article&id=' . $article->id);
        }
    }
}

// El layout Blog reparte los artículos en lead/intro/link. Los unimos todos.
$items = [];
foreach (['lead_items', 'intro_items', 'link_items'] as $bucket) {
    if (!empty($this->$bucket) && is_array($this->$bucket)) {
        $items = array_merge($items, $this->$bucket);
    }
}
// Fallback por si la vista expone $this->items directamente
if (empty($items) && !empty($this->items) && is_array($this->items)) {
    $items = $this->items;
}

$category   = $this->category ?? null;
$pagination = $this->pagination ?? null;
?>

<style>
/* ══════════════════════════════════════════
   HMB — LISTADO DE ARTÍCULOS (layout BLOG)
══════════════════════════════════════════ */
.hmb-art-list{
    background:#050000;
    padding:2rem 0;
    font-family:'Oswald',Arial,sans-serif;
}

/* Cabecera de categoría */
.hmb-art-header{
    max-width:1200px;margin:0 auto 2.5rem;padding:0 1.5rem;
    border-left:4px solid #CC2200;
}
.hmb-art-header h1{
    font-family:'Metal Mania',cursive;
    font-size:clamp(28px,5vw,52px);
    color:#fff;letter-spacing:3px;margin:0 0 .3rem;
    text-shadow:0 0 30px rgba(204,34,0,.5);
}
.hmb-art-header p{
    font-size:14px;color:#555;letter-spacing:1px;margin:0;
}

/* Grid */
.hmb-art-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
    gap:3px;
    max-width:1200px;
    margin:0 auto;
    padding:0 1.5rem;
}
@media(max-width:680px){
    .hmb-art-grid{grid-template-columns:1fr;padding:0 .75rem;}
}

/* Tarjeta */
.hmb-art-card{
    position:relative;
    overflow:hidden;
    min-height:260px;
    cursor:pointer;
    background:#0a0000;
    display:flex;flex-direction:column;
    text-decoration:none;
}
.hmb-art-card:hover{text-decoration:none;}

/* Imagen de fondo */
.hmb-art-bg{
    position:absolute;inset:0;
    background-size:cover;
    background-position:center;
    transition:transform .5s ease,filter .5s ease;
    filter:brightness(.45) saturate(1.1);
}
.hmb-art-card:hover .hmb-art-bg{
    transform:scale(1.06);
    filter:brightness(.6) saturate(1.3);
}

/* Sin imagen — patrón decorativo */
.hmb-art-bg-empty{
    position:absolute;inset:0;
    background:
        repeating-linear-gradient(
            45deg,
            #0d0000 0px,#0d0000 1px,
            #060000 1px,#060000 14px
        );
}
.hmb-art-bg-empty::after{
    content:'';position:absolute;inset:0;
    background:radial-gradient(ellipse at 60% 40%, rgba(139,0,0,.25), transparent 70%);
}

/* Overlay gradiente */
.hmb-art-overlay{
    position:absolute;inset:0;
    background:linear-gradient(
        160deg,
        rgba(0,0,0,.1) 0%,
        rgba(5,0,0,.5) 40%,
        rgba(5,0,0,.92) 100%
    );
    transition:background .4s;
}
.hmb-art-card:hover .hmb-art-overlay{
    background:linear-gradient(
        160deg,
        rgba(0,0,0,.05) 0%,
        rgba(5,0,0,.4) 40%,
        rgba(5,0,0,.88) 100%
    );
}

/* Borde superior rojo al hover */
.hmb-art-card::before{
    content:'';position:absolute;top:0;left:0;right:0;
    height:3px;
    background:linear-gradient(90deg,#CC2200,#8B0000,transparent);
    z-index:5;
    transform:scaleX(0);transform-origin:left;
    transition:transform .35s ease;
}
.hmb-art-card:hover::before{transform:scaleX(1);}

/* Contenido */
.hmb-art-content{
    position:relative;z-index:3;
    margin-top:auto;padding:1.4rem 1.4rem 1.2rem;
}

/* Categoría + fecha */
.hmb-art-meta{
    display:flex;align-items:center;gap:8px;
    margin-bottom:.7rem;
    flex-wrap:wrap;
}
.hmb-art-cat{
    font-size:9px;font-weight:700;letter-spacing:2px;
    text-transform:uppercase;
    background:rgba(204,34,0,.8);
    color:#fff;
    padding:3px 8px;
    border-radius:1px;
}
.hmb-art-date{
    font-size:10px;letter-spacing:1px;
    color:rgba(180,180,180,.6);
}

/* Título */
.hmb-art-title{
    font-family:'Metal Mania',cursive;
    font-size:clamp(17px,2.5vw,24px);
    color:#fff;
    line-height:1.2;
    letter-spacing:1px;
    margin:0 0 .7rem;
    text-shadow:0 2px 8px rgba(0,0,0,.8);
    display:-webkit-box;
    -webkit-line-clamp:3;
    -webkit-box-orient:vertical;
    overflow:hidden;
    transition:color .25s;
}
.hmb-art-card:hover .hmb-art-title{
    color:#FF6644;
}

/* Intro texto */
.hmb-art-intro{
    font-size:12px;color:rgba(180,180,180,.7);
    line-height:1.6;letter-spacing:.3px;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
    margin-bottom:.9rem;
}

/* CTA */
.hmb-art-cta{
    display:inline-flex;align-items:center;gap:6px;
    font-size:10px;letter-spacing:2px;text-transform:uppercase;
    color:#CC2200;
    border:1px solid rgba(139,0,0,.5);
    padding:5px 14px;
    transition:all .25s;
}
.hmb-art-card:hover .hmb-art-cta{
    color:#fff;
    background:rgba(139,0,0,.4);
    border-color:#CC2200;
}
.hmb-art-cta-arrow{transition:transform .25s;}
.hmb-art-card:hover .hmb-art-cta-arrow{transform:translateX(4px);}

/* Paginación */
.hmb-art-pag{
    max-width:1200px;margin:2.5rem auto 0;padding:0 1.5rem;
    display:flex;justify-content:center;align-items:center;gap:6px;
}
.hmb-art-pag .pagination{
    display:flex;gap:4px;flex-wrap:wrap;justify-content:center;
    list-style:none;margin:0;padding:0;
}
.hmb-art-pag .pagination li a,
.hmb-art-pag .pagination li span{
    display:flex;align-items:center;justify-content:center;
    min-width:36px;height:36px;
    background:#0a0000;
    border:1px solid #2a0000;
    color:#888;
    font-family:'Oswald',sans-serif;
    font-size:12px;letter-spacing:1px;
    text-decoration:none;
    padding:0 8px;
    transition:all .2s;
}
.hmb-art-pag .pagination li a:hover{
    background:#1a0000;border-color:#CC2200;color:#fff;
}
.hmb-art-pag .pagination li.active span,
.hmb-art-pag .pagination li.active a{
    background:#CC2200;border-color:#CC2200;color:#fff;
}
.hmb-art-pag .pagination li.disabled span{
    opacity:.3;cursor:default;
}

/* Sin artículos */
.hmb-art-empty{
    text-align:center;padding:4rem 2rem;color:#333;
    font-family:'Oswald',sans-serif;letter-spacing:2px;
}
</style>

<div class="hmb-art-list">

    <?php if ($category): ?>
    <div class="hmb-art-header">
        <h1><?php echo htmlspecialchars($category->title ?? '', ENT_QUOTES); ?></h1>
        <?php if (!empty($category->description)): ?>
        <p><?php echo strip_tags($category->description); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
    <div class="hmb-art-empty">
        <p>&#9760; No hay artículos disponibles</p>
    </div>
    <?php else: ?>

    <div class="hmb-art-grid">
    <?php foreach ($items as $item):
        $url    = hmb_article_url($item);
        $imgUrl = hmb_article_img($item, $root);
        $intro  = strip_tags($item->introtext ?? '');
        $intro  = mb_strlen($intro) > 120 ? mb_substr($intro, 0, 120) . '…' : $intro;
        $fecha  = '';
        try {
            $fecha = \Joomla\CMS\HTML\HTMLHelper::date($item->publish_up ?? $item->created, 'd M Y');
        } catch (\Throwable $e) {}
    ?>
    <a href="<?php echo htmlspecialchars($url, ENT_QUOTES); ?>" class="hmb-art-card">

        <?php if ($imgUrl): ?>
        <div class="hmb-art-bg" style="background-image:url('<?php echo htmlspecialchars($imgUrl, ENT_QUOTES); ?>')"></div>
        <?php else: ?>
        <div class="hmb-art-bg-empty"></div>
        <?php endif; ?>
        <div class="hmb-art-overlay"></div>

        <div class="hmb-art-content">
            <div class="hmb-art-meta">
                <?php if (!empty($item->category_title)): ?>
                <span class="hmb-art-cat"><?php echo htmlspecialchars($item->category_title, ENT_QUOTES); ?></span>
                <?php endif; ?>
                <?php if ($fecha): ?>
                <span class="hmb-art-date">&#9670; <?php echo $fecha; ?></span>
                <?php endif; ?>
            </div>

            <h2 class="hmb-art-title"><?php echo htmlspecialchars($item->title, ENT_QUOTES); ?></h2>

            <?php if ($intro): ?>
            <p class="hmb-art-intro"><?php echo htmlspecialchars($intro, ENT_QUOTES); ?></p>
            <?php endif; ?>

            <span class="hmb-art-cta">
                Leer más <span class="hmb-art-cta-arrow">&#8594;</span>
            </span>
        </div>

    </a>
    <?php endforeach; ?>
    </div>

    <?php endif; ?>

    <?php if ($pagination && $pagination->pagesTotal > 1): ?>
    <div class="hmb-art-pag">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
    <?php endif; ?>

</div>
