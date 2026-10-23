<?php
/**
 * HMB — Override vista de categorías
 * Tarjetas con slider de imágenes de artículos, estilo metal
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$db   = Factory::getDbo();
$root = rtrim(Uri::root(), '/');

/* En Joomla 6 $this->items es [parentId => [CategoryNode...]]
   Necesitamos los hijos directos de la categoría actual */
$items = [];
if (isset($this->item) && !empty($this->items[$this->item->id])) {
    $items = $this->items[$this->item->id];
} else {
    // Fallback: aplanar todo el árbol un nivel
    foreach ((array)$this->items as $group) {
        foreach ((array)$group as $cat) {
            $items[] = $cat;
        }
    }
}

/**
 * Obtiene las imágenes de los últimos artículos de una categoría
 */
function hmb_getCatImages(object $db, int $catId, string $root, int $limit = 6): array
{
    $query = $db->getQuery(true)
        ->select([$db->quoteName('images'), $db->quoteName('introtext'), $db->quoteName('fulltext')])
        ->from($db->quoteName('#__content'))
        ->where($db->quoteName('catid') . ' = ' . $catId)
        ->where($db->quoteName('state') . ' = 1')
        ->order($db->quoteName('featured') . ' DESC, ' . $db->quoteName('created') . ' DESC')
        ->setLimit($limit);
    $db->setQuery($query);
    $rows = $db->loadObjectList();

    $imgs = [];
    foreach ($rows as $row) {
        $img  = '';
        $data = json_decode($row->images ?? '');
        if ($data) {
            // ?: (no ??) para caer a image_fulltext cuando image_intro está vacío
            $img = $data->image_intro ?: ($data->image_fulltext ?? '');
        }
        // Fallback: primera imagen incrustada en el cuerpo del artículo
        if (!$img) {
            $html = ($row->introtext ?? '') . ($row->fulltext ?? '');
            if ($html && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
                $img = $m[1];
            }
        }
        if (!$img) continue;
        $u = hmb_imgUrl($img, $root);
        if ($u !== '') $imgs[] = $u;
    }
    return $imgs;
}

/**
 * Normaliza una ruta de imagen a URL absoluta usable:
 * quita el sufijo #joomlaImage://..., respeta URLs http(s) y codifica espacios
 */
function hmb_imgUrl(string $img, string $root): string
{
    $img = explode('#', $img)[0];
    if ($img === '') return '';
    if (!preg_match('~^https?://~i', $img)) {
        $img = $root . '/' . ltrim($img, '/');
    }
    return str_replace(' ', '%20', $img);
}

/**
 * URL de la categoría — acepta objeto o array
 */
function hmb_catUrl($item): string
{
    $id = is_array($item) ? (int)($item['id'] ?? 0) : (int)($item->id ?? 0);
    if (!$id) return '#';
    try {
        return Route::_('index.php?option=com_content&view=category&layout=blog&id=' . $id);
    } catch (\Throwable $e) {
        return '#';
    }
}

/**
 * Leer propiedad de objeto o array
 */
function hmb_prop($item, string $prop, $default = '')
{
    if (is_array($item))  return $item[$prop] ?? $default;
    if (is_object($item)) return $item->$prop ?? $default;
    return $default;
}

// Pre-cargar imágenes de todas las categorías
$catData = [];
foreach ($items as $item) {
    $id    = (int) hmb_prop($item, 'id');
    $alias = (string) hmb_prop($item, 'alias', '');
    if (!$id) continue;

    $imgs = hmb_getCatImages($db, $id, $root);

    // Fallback: buscar imagen en templates/hmb/images/cat-{alias}.jpg|png|webp
    if (empty($imgs)) {
        $tmplImgPath = JPATH_ROOT . '/templates/hmb/images/';
        $tmplImgUrl  = $root . '/templates/hmb/images/';
        $exts        = ['jpg','jpeg','png','webp'];
        foreach ($exts as $ext) {
            $file = $tmplImgPath . 'cat-' . $alias . '.' . $ext;
            if (file_exists($file)) {
                $imgs = [$tmplImgUrl . 'cat-' . $alias . '.' . $ext];
                break;
            }
        }
    }

    // Fallback genérico: cat-default.jpg|png|webp
    if (empty($imgs)) {
        $exts = ['jpg','jpeg','png','webp'];
        foreach ($exts as $ext) {
            $file = JPATH_ROOT . '/templates/hmb/images/cat-default.' . $ext;
            if (file_exists($file)) {
                $imgs = [$root . '/templates/hmb/images/cat-default.' . $ext];
                break;
            }
        }
    }

    $catData[$id] = [
        'imgs' => $imgs,
        'url'  => hmb_catUrl($item),
    ];
}
?>

<style>
/* ══════════════════════════════════════
   HMB — GRID DE CATEGORÍAS
══════════════════════════════════════ */
.hmb-cats{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:3px;
    background:#000;
    padding:3px;
}

/* Primera tarjeta: ancho completo */
.hmb-cat-card:first-child{
    grid-column:1 / -1;
}

/* Tarjeta */
.hmb-cat-card{
    position:relative;
    overflow:hidden;
    min-height:320px;
    cursor:pointer;
    background:var(--wine-black, #0A0000);
}
.hmb-cat-card:first-child{
    min-height:480px;
}
@media(max-width:768px){
    .hmb-cats{grid-template-columns:1fr;}
    .hmb-cat-card:first-child{min-height:300px;}
    .hmb-cat-card{min-height:240px;}
}

/* ── Slider de imágenes ── */
.hmb-cat-slides{
    position:absolute;
    inset:0;
    z-index:0;
}
.hmb-cat-slide{
    position:absolute;
    left:0;right:0;top:0;
    height:175%;                 /* más alta que la tarjeta: recorrido vertical */
    opacity:0;
    transition:opacity 1.2s ease;
    background-size:cover;
    background-position:center top;
    transform:translateY(0);
    animation:none;
}
.hmb-cat-slide.on{
    opacity:1;
    animation:hmb-kb 6s ease-out forwards;
}
.hmb-cat-slide.out{
    opacity:0;
    transition:opacity 1.4s ease;
}

/* Scroll vertical lento — de arriba a abajo */
@keyframes hmb-kb{
    0%  {transform:translateY(0);}
    100%{transform:translateY(-42.85%);}  /* (175-100)/175 → revela hasta abajo */
}

/* Slide sin imagen */
.hmb-cat-slide-empty{
    position:absolute;inset:0;
    background:
        repeating-linear-gradient(
            45deg,
            var(--wine-black, #0A0000) 0px, var(--wine-black, #0A0000) 2px,
            var(--wine-black, #0A0000) 2px, var(--wine-black, #0A0000) 20px
        );
}

/* ── Overlays ── */
.hmb-cat-overlay-bottom{
    position:absolute;
    inset:0;
    z-index:1;
    background:linear-gradient(
        to top,
        rgba(0,0,0,.95) 0%,
        rgba(0,0,0,.5)  45%,
        rgba(0,0,0,.15) 70%,
        transparent     100%
    );
}
.hmb-cat-overlay-side{
    position:absolute;
    inset:0;
    z-index:1;
    background:linear-gradient(
        to right,
        rgba(5,0,0,.6) 0%,
        transparent 60%
    );
}

/* Línea decorativa top */
.hmb-cat-card::before{
    content:'';
    position:absolute;
    top:0;left:0;right:0;
    height:3px;
    background:linear-gradient(90deg,var(--fire, #CC2200),var(--blood, #8B0000),transparent);
    z-index:10;
}
/* Línea lateral izquierda */
.hmb-cat-card::after{
    content:'';
    position:absolute;
    top:0;left:0;bottom:0;
    width:3px;
    background:linear-gradient(180deg,var(--fire, #CC2200),transparent);
    z-index:10;
}

/* ── Contenido ── */
.hmb-cat-content{
    position:absolute;
    bottom:0;left:0;right:0;
    z-index:5;
    padding:2rem 1.8rem 1.8rem;
}

/* Badge número de artículos */
.hmb-cat-count{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:rgba(139,0,0,.85);
    border:1px solid var(--fire, #CC2200);
    color:#fff;
    font-family:'Oswald',sans-serif;
    font-size:10px;
    font-weight:700;
    letter-spacing:2px;
    text-transform:uppercase;
    padding:3px 10px;
    margin-bottom:10px;
}
.hmb-cat-count-dot{
    width:5px;height:5px;
    border-radius:50%;
    background:var(--ember, #FF4500);
    animation:hmb-blink 1.4s ease-in-out infinite;
}
@keyframes hmb-blink{0%,100%{opacity:1;}50%{opacity:.3;}}

/* Título */
.hmb-cat-title{
    font-family:'Metal Mania',cursive;
    font-size:clamp(26px, 4vw, 54px);
    color:#fff;
    line-height:1;
    margin:0 0 10px;
    letter-spacing:2px;
    text-shadow:
        0 0 40px rgba(204,34,0,.6),
        0 2px 8px rgba(0,0,0,.9);
}
.hmb-cat-card:first-child .hmb-cat-title{
    font-size:clamp(36px, 6vw, 80px);
}

/* Separador rojo */
.hmb-cat-sep{
    width:50px;height:2px;
    background:linear-gradient(90deg,var(--fire, #CC2200),transparent);
    margin:0 0 12px;
}

/* Descripción */
.hmb-cat-desc{
    font-family:'Oswald',sans-serif;
    font-size:13px;
    color:rgba(200,200,200,.8);
    letter-spacing:1px;
    line-height:1.6;
    margin:0 0 18px;
    max-width:480px;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
}

/* Botón CTA */
.hmb-cat-cta{
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-family:'Oswald',sans-serif;
    font-size:11px;
    font-weight:700;
    letter-spacing:3px;
    text-transform:uppercase;
    color:var(--fire, #CC2200);
    text-decoration:none;
    border:1px solid var(--blood, #8B0000);
    padding:7px 18px;
    transition:all .25s;
    position:relative;
    overflow:hidden;
}
.hmb-cat-cta::before{
    content:'';
    position:absolute;
    inset:0;
    background:rgba(139,0,0,.0);
    transition:background .25s;
}
.hmb-cat-card:hover .hmb-cat-cta{
    color:#fff;
    border-color:var(--fire, #CC2200);
}
.hmb-cat-card:hover .hmb-cat-cta::before{
    background:rgba(139,0,0,.4);
}
.hmb-cat-cta-arrow{
    transition:transform .25s;
}
.hmb-cat-card:hover .hmb-cat-cta-arrow{
    transform:translateX(4px);
}

/* Indicadores de imagen */
.hmb-cat-dots{
    position:absolute;
    top:14px;right:14px;
    z-index:10;
    display:flex;gap:4px;
}
.hmb-cat-dot{
    width:5px;height:5px;
    border-radius:50%;
    background:rgba(255,255,255,.3);
    transition:background .3s;
}
.hmb-cat-dot.on{background:var(--fire, #CC2200);}

/* Sin zoom al hover: el scroll vertical lo hace la animación de cada slide */
.hmb-cat-slides{overflow:hidden;}

/* Nº de categoría decorativo */
.hmb-cat-num{
    position:absolute;
    top:14px;left:18px;
    z-index:10;
    font-family:'Metal Mania',cursive;
    font-size:11px;
    color:rgba(204,34,0,.5);
    letter-spacing:3px;
}
</style>

<?php if (empty($items)): ?>
<p style="color:#888;padding:2rem;font-family:'Oswald',sans-serif;letter-spacing:2px;">
    No hay categorías disponibles.
</p>
<?php return; endif; ?>

<div class="hmb-cats">
<?php
$catIndex = 0;
foreach ($items as $item):
    $catIndex++;
    $id    = (int) hmb_prop($item, 'id');
    $data  = $catData[$id] ?? ['imgs' => [], 'url' => '#'];
    $imgs  = $data['imgs'];
    $url   = $data['url'];
    $count = (int) hmb_prop($item, 'numitems', 0);
    $desc  = strip_tags((string) hmb_prop($item, 'description', ''));
    $title = (string) hmb_prop($item, 'title', '');
    $uid   = 'hmbc-' . $id;
?>
<div class="hmb-cat-card" data-uid="<?php echo $uid; ?>"
     onclick="window.location='<?php echo htmlspecialchars($url, ENT_QUOTES); ?>'">

    <!-- Número decorativo -->
    <span class="hmb-cat-num"><?php echo str_pad($catIndex, 2, '0', STR_PAD_LEFT); ?></span>

    <!-- Slider de imágenes -->
    <div class="hmb-cat-slides" id="<?php echo $uid; ?>-slides">
    <?php if (!empty($imgs)): ?>
        <?php foreach ($imgs as $ii => $img): ?>
        <div class="hmb-cat-slide<?php echo $ii === 0 ? ' on' : ''; ?>"
             style="background-image:url('<?php echo htmlspecialchars($img, ENT_QUOTES); ?>');">
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="hmb-cat-slide-empty"></div>
    <?php endif; ?>
    </div>

    <!-- Overlays -->
    <div class="hmb-cat-overlay-bottom"></div>
    <div class="hmb-cat-overlay-side"></div>

    <!-- Dots indicadores -->
    <?php if (count($imgs) > 1): ?>
    <div class="hmb-cat-dots" id="<?php echo $uid; ?>-dots">
        <?php foreach ($imgs as $ii => $img): ?>
        <div class="hmb-cat-dot<?php echo $ii === 0 ? ' on' : ''; ?>"></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Contenido -->
    <div class="hmb-cat-content">

        <div class="hmb-cat-count">
            <span class="hmb-cat-count-dot"></span>
            <?php echo $count; ?> artículo<?php echo $count !== 1 ? 's' : ''; ?>
        </div>

        <h2 class="hmb-cat-title"><?php echo htmlspecialchars($title, ENT_QUOTES); ?></h2>

        <div class="hmb-cat-sep"></div>

        <?php if ($desc): ?>
        <p class="hmb-cat-desc"><?php echo htmlspecialchars($desc, ENT_QUOTES); ?></p>
        <?php endif; ?>

        <a href="<?php echo htmlspecialchars($url, ENT_QUOTES); ?>"
           class="hmb-cat-cta"
           onclick="event.stopPropagation()">
            Ver artículos
            <span class="hmb-cat-cta-arrow">&#8594;</span>
        </a>

    </div>
</div>
<?php endforeach; ?>
</div>

<script>
(function(){
    /* Rotar imágenes de cada tarjeta de forma independiente */
    document.querySelectorAll('.hmb-cat-card[data-uid]').forEach(function(card){
        var uid    = card.dataset.uid;
        var slides = card.querySelectorAll('.hmb-cat-slide');
        var dots   = card.querySelectorAll('.hmb-cat-dot');
        if(slides.length < 2) return;

        var cur = 0;
        /* Offset aleatorio para que no todas cambien a la vez */
        var offset = Math.floor(Math.random() * 3000);

        setTimeout(function(){
            setInterval(function(){
                slides[cur].classList.remove('on');
                slides[cur].classList.add('out');
                dots[cur] && dots[cur].classList.remove('on');

                setTimeout(function(prevCur){
                    slides[prevCur] && slides[prevCur].classList.remove('out');
                }, 1500, cur);

                cur = (cur + 1) % slides.length;

                slides[cur].classList.add('on');
                dots[cur] && dots[cur].classList.add('on');

                /* Reiniciar animación Ken Burns */
                var s = slides[cur];
                s.style.animation = 'none';
                void s.offsetWidth;
                s.style.animation = 'hmb-kb 6s ease-out forwards';

            }, 4000);
        }, offset);
    });
})();
</script>
