<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$item = $this->item;
if (!$item) { return; }

$decode = function($v) {
    if (is_array($v)) return $v;
    if (empty($v)) return [];
    $r = json_decode($v, true);
    return is_array($r) ? $r : [];
};

$formats     = $decode($item->formats);
$events      = $decode($item->events);
$componentes = $decode($item->componentes);
$provincias  = $decode($item->provincias_destino);
$gallery     = $decode($item->gallery);
$videos      = $decode($item->videos);
$stars       = (int)round((float)($item->rating ?? 0));

if (empty($videos) && !empty($item->video_url)) {
    $videos = [$item->video_url];
}

$embedUrl = function(?string $url): ?string {
    if (!$url) return null;
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\?]+)/', $url, $m))
        return 'https://www.youtube.com/embed/' . $m[1];
    if (preg_match('/vimeo\.com\/(\d+)/', $url, $m))
        return 'https://player.vimeo.com/video/' . $m[1];
    return null;
};

$eventLabels = [
    'bodas'=>'💍 Bodas','bautizos'=>'🍼 Bautizos/Comuniones','cumpleanos'=>'🎂 Cumpleaños',
    'corporativos'=>'🏢 Corporativos','festivales'=>'🎪 Festivales',
    'medievales'=>'🏰 Medievales','privados'=>'🎵 Conciertos privados',
    'fiestas_patronales'=>'🎉 Fiestas patronales',
    'restaurantes'=>'🍽️ Restaurantes','inauguraciones'=>'✂️ Inauguraciones'
];

$img = !empty($item->image) ? Uri::root() . $item->image : '';
$tipoLabels = ['solista'=>'🎤 Solista','propias'=>'🎵 Canciones propias','versiones'=>'🎸 Versiones','ambas'=>'🎶 Propias y versiones'];
$tipoBandaVal = isset($item->tipo_banda) && $item->tipo_banda ? $item->tipo_banda : 'propias'; // solista, propias, versiones, ambas
$tipoLabel  = $tipoLabels[$tipoBandaVal] ?? '🎵 Canciones propias';

// Noticias de la banda
$__noticias = [];
try {
    $__dbN = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
    $__dbN->setQuery('SELECT n.tipo, c.title, c.introtext, c.created, c.alias, c.id as article_id
        FROM #__artistas_noticias n
        LEFT JOIN #__content c ON c.id = n.article_id
        WHERE n.artista_id = ' . (int)$item->id . ' AND c.state = 1
        ORDER BY c.created DESC LIMIT 10');
    $__noticias = $__dbN->loadObjectList() ?: [];
} catch (\Exception $e) {}

// Artistas relacionados
$relacionados = [];
// Itemid del menu item de ficha
$__menu2   = \Joomla\CMS\Factory::getApplication()->getMenu();
$__mitems  = $__menu2->getItems('component_id', \Joomla\CMS\Component\ComponentHelper::getComponent('com_artistas')->id);
$__itemid  = '';
foreach ($__mitems as $__mi) {
    if (strpos($__mi->link, 'view=artista') !== false) {
        $__itemid = '&Itemid=' . $__mi->id;
        break;
    }
}
try {
    $model = $this->getModel();
    if (method_exists($model, 'getRelacionados')) {
        $relacionados = $model->getRelacionados($item->genre ?? '', (int)$item->id);
    }
} catch(\Exception $e) {}

// Redes sociales
$socials = [];
if (!empty($item->social_instagram)) $socials[] = ['url'=>$item->social_instagram,'label'=>'Instagram','color'=>'#E1306C','svg'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>'];
if (!empty($item->social_facebook))  $socials[] = ['url'=>$item->social_facebook, 'label'=>'Facebook', 'color'=>'#1877F2','svg'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>'];
if (!empty($item->social_youtube))   $socials[] = ['url'=>$item->social_youtube,  'label'=>'YouTube',   'color'=>'#FF0000','svg'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/></svg>'];
if (!empty($item->social_tiktok))    $socials[] = ['url'=>$item->social_tiktok,   'label'=>'TikTok',    'color'=>'#e8e0d5','svg'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>'];
?>

<style>
.af { color:#e8e0d5; }
.af-hero { position:relative; height:420px; border-radius:12px; overflow:hidden; margin-bottom:1.5rem; }
.af-hero img {
    width:100%; height:100%;
    object-fit:cover; object-position:center top;
    filter:brightness(.6);
    animation: heroPan 12s ease-in-out infinite alternate;
    transform-origin: center center;
}
@keyframes heroPan {
    0%   { object-position: center top;    transform: scale(1.08); }
    50%  { object-position: center center; transform: scale(1.03); }
    100% { object-position: center bottom; transform: scale(1.08); }
}
.af-hero-body { position:absolute; bottom:0; left:0; padding:1.5rem; color:#fff; }
.af-card { background:rgba(10,8,6,.82); border:1px solid rgba(180,140,80,.3); border-radius:10px; margin-bottom:1.2rem; overflow:hidden; backdrop-filter:blur(4px); }
.af-card-h { background:rgba(180,120,30,.6); border-bottom:1px solid rgba(200,150,50,.3); padding:.75rem 1.2rem; font-weight:700; font-size:.9rem; color:#fff; text-transform:uppercase; letter-spacing:.5px; }
.af-card-b { padding:1.2rem 1.4rem; }
.af-badge { display:inline-block; background:rgba(180,120,20,.35); border:1px solid rgba(180,140,60,.4); color:#d4bb8a; font-size:.75rem; font-weight:700; padding:2px 10px; border-radius:4px; text-transform:uppercase; letter-spacing:.5px; margin-bottom:.5rem; }
.af-stars { color:#f5c842; letter-spacing:-1px; }
.af-stars-e { color:#3a3020; letter-spacing:-1px; }
.af-prov-badge { display:inline-block; background:rgba(255,255,255,.06); border:1px solid rgba(180,140,60,.25); color:#9a8a6a; font-size:.78rem; padding:2px 8px; border-radius:3px; margin:2px; }
.af-sidebar { background:rgba(10,8,6,.85); border:1px solid rgba(180,140,80,.3); border-radius:10px; overflow:hidden; }
.af-sidebar-h { background:linear-gradient(135deg,#b87d1e,#8a5a10); padding:1rem; text-align:center; color:#fff; font-weight:700; }
.af-sidebar-b { padding:1.2rem; }
.af-btn-phone { display:block; text-align:center; background:rgba(20,120,50,.5); border:1px solid rgba(40,160,70,.4); color:#8feba8; padding:.6rem; border-radius:6px; text-decoration:none; margin-bottom:.5rem; font-weight:600; }
.af-btn-web { display:block; text-align:center; background:rgba(255,255,255,.06); border:1px solid rgba(180,140,60,.3); color:#c9b890; padding:.4rem; border-radius:6px; text-decoration:none; font-size:.85rem; margin-bottom:.8rem; }
.af-social { display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; border-radius:8px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15); transition:all .2s; text-decoration:none; }
.af-social:hover { background:rgba(255,255,255,.18); transform:scale(1.1); }
.af-form-ctrl { background:rgba(255,255,255,.07)!important; border:1px solid rgba(180,140,60,.4)!important; color:#f0e8d8!important; border-radius:6px; width:100%; padding:.4rem .6rem; margin-bottom:.5rem; font-size:.85rem; }
.af-form-ctrl::placeholder { color:#6a5f4a!important; }
.af-btn-submit { background:linear-gradient(135deg,#b87d1e,#8a5a10); border:1px solid rgba(200,150,60,.5); color:#fff; font-weight:700; width:100%; padding:.7rem; border-radius:6px; cursor:pointer; }
.af-resena-stars { display:flex; flex-direction:row-reverse; justify-content:flex-end; }
.af-resena-stars input { display:none; }
.af-resena-stars label { font-size:1.6rem; color:#ccc; cursor:pointer; }
.af-resena-stars input:checked ~ label, .af-resena-stars label:hover, .af-resena-stars label:hover ~ label { color:#f5c842; }
</style>

<div class="af">

    <!-- HERO -->
    <div class="af-hero">
        <?php if ($img): ?>
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item->name) ?>">
        <?php else: ?>
        <div style="width:100%;height:100%;background:#1a1208;"></div>
        <?php endif; ?>
        <div class="af-hero-body">
            <span class="af-badge"><?= htmlspecialchars($item->genre ?? '') ?></span>
            <span class="af-badge ms-1"><?= $tipoLabel ?></span>
            <?php if (!empty($item->provincia_origen)): ?>
            <span class="af-badge ms-2">📍 <?= htmlspecialchars($item->provincia_origen) ?></span>
            <?php endif; ?>
            <h1 class="display-5 fw-bold mb-1" style="color:#fff;text-shadow:0 2px 12px rgba(0,0,0,.8)"><?= htmlspecialchars($item->name) ?></h1>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="af-stars fs-4"><?= str_repeat('★', $stars) ?></span>
                <span class="af-stars-e fs-4"><?= str_repeat('★', 5 - $stars) ?></span>
                <span style="color:#ccc"><?= number_format((float)($item->rating ?? 0), 1) ?>/5 (<?= (int)$item->review_count ?> reseñas)</span>
                <?php if (!empty($item->location)): ?>
                <span style="color:#ccc">📍 <?= htmlspecialchars($item->location) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">

            <?php if (!empty($item->short_desc)): ?>
            <div class="af-card">
                <div class="af-card-b" style="font-size:1.05rem;color:#d4bb8a;font-style:italic">
                    <?= htmlspecialchars($item->short_desc) ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($item->description)): ?>
            <div class="af-card">
                <div class="af-card-h">🎸 Sobre <?= htmlspecialchars($item->name) ?></div>
                <div class="af-card-b"><?= $item->description ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($item->biografia)): ?>
            <div class="af-card">
                <div class="af-card-h">📖 Biografía</div>
                <div class="af-card-b"><?= $item->biografia ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($item->influencias)): ?>
            <div class="af-card">
                <div class="af-card-h">🎵 Influencias</div>
                <div class="af-card-b"><p class="fst-italic mb-0" style="color:#c9b890"><?= htmlspecialchars($item->influencias) ?></p></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($componentes) && ($item->tipo_banda ?? '') !== 'solista'): ?>
            <div class="af-card">
                <div class="af-card-h">👥 Integrantes</div>
                <div class="af-card-b">
                    <ul style="list-style:none;padding:0;margin:0;columns:2;gap:1rem;">
                        <?php foreach ($componentes as $comp): ?>
                        <li style="padding:.4rem 0;border-bottom:1px solid rgba(180,140,60,.15);break-inside:avoid;">
                            <span style="color:#f0e0c0;font-weight:600"><?= htmlspecialchars($comp['nombre'] ?? '') ?></span>
                            <?php if (!empty($comp['instrumento'])): ?>
                            <span style="color:#8a7a60;font-size:.85rem"> — <?= htmlspecialchars($comp['instrumento']) ?></span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($formats)): ?>
            <div class="af-card">
                <div class="af-card-h">⚡ Formatos</div>
                <div class="af-card-b">
                    <?php foreach ($formats as $f): ?>
                    <span class="badge bg-primary fs-6 px-3 py-2 me-1 mb-1"><?= htmlspecialchars(ucfirst($f)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($events)): ?>
            <div class="af-card">
                <div class="af-card-h">📅 Tipos de eventos</div>
                <div class="af-card-b">
                    <div class="row row-cols-2 row-cols-md-3 g-2">
                        <?php foreach ($events as $ev): ?>
                        <div class="col"><div class="border rounded p-2 text-center small" style="border-color:rgba(180,140,60,.3)!important;color:#c9b890"><?= htmlspecialchars($eventLabels[$ev] ?? ucfirst($ev)) ?></div></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($provincias)): ?>
            <div class="af-card">
                <div class="af-card-h">🗺️ Zona de actuación</div>
                <div class="af-card-b">
                    <?php if (in_array('toda_espana', $provincias)): ?>
                        <span class="badge bg-success fs-6 px-3 py-2">🇪🇸 Toda España</span>
                    <?php else: ?>
                        <?php foreach ($provincias as $prov): ?><span class="af-prov-badge"><?= htmlspecialchars($prov) ?></span><?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($gallery)): ?>
            <div class="af-card">
                <div class="af-card-h">📸 Galería</div>
                <div class="af-card-b">
                    <div class="row g-2">
                        <?php foreach ($gallery as $i => $gImg): ?>
                        <div class="col-4 col-md-3">
                            <a href="<?= Uri::root() . htmlspecialchars($gImg) ?>" target="_blank">
                                <img src="<?= Uri::root() . htmlspecialchars($gImg) ?>" class="img-fluid rounded" style="height:110px;object-fit:cover;width:100%;" alt="Foto <?= $i+1 ?>" loading="lazy">
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($videos)): ?>
            <div class="af-card">
                <div class="af-card-h">🎬 Vídeos</div>
                <div class="af-card-b">
                    <?php foreach ($videos as $i => $vid):
                        $embed = $embedUrl($vid);
                        if (!$embed) continue;
                    ?>
                    <div class="<?= $i > 0 ? 'mt-3' : '' ?>">
                        <div class="ratio ratio-16x9"><iframe src="<?= htmlspecialchars($embed) ?>" allowfullscreen title="Vídeo <?= $i+1 ?>"></iframe></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- DISCOGRAFÍA -->
            <?php
            $discografia = is_array($item->discografia ?? null) ? $item->discografia : json_decode($item->discografia ?? '[]', true);
            if (!empty($discografia)): ?>
            <div class="af-card">
                <div class="af-card-h">💿 Discografía</div>
                <div class="af-card-b">
                    <div class="row g-3">
                    <?php foreach ($discografia as $disco): ?>
                        <div class="col-6 col-md-3 text-center">
                            <?php if (!empty($disco['portada'])): ?>
                            <img src="<?= Uri::root() . htmlspecialchars($disco['portada']) ?>"
                                 style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;margin-bottom:8px;box-shadow:0 4px 12px rgba(0,0,0,.5)">
                            <?php else: ?>
                            <div style="width:100%;aspect-ratio:1;background:rgba(180,140,60,.1);border:1px solid rgba(180,140,60,.2);border-radius:8px;margin-bottom:8px;display:flex;align-items:center;justify-content:center;font-size:2.5rem">💿</div>
                            <?php endif; ?>
                            <div style="color:#f0e0c0;font-weight:700;font-size:.9rem"><?= htmlspecialchars($disco['titulo']) ?></div>
                            <?php if (!empty($disco['anio'])): ?><div style="color:#8a7a60;font-size:.8rem"><?= htmlspecialchars($disco['anio']) ?></div><?php endif; ?>
                            <?php if (!empty($disco['productora'])): ?><div style="color:#6a5f4a;font-size:.75rem"><?= htmlspecialchars($disco['productora']) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- NOTICIAS -->
            <?php if (!empty($__noticias)): ?>
            <div class="af-card" id="noticias">
                <div class="af-card-h">📰 Noticias y eventos</div>
                <div class="af-card-b">
                    <?php
                    $__currentUserId = \Joomla\CMS\Factory::getApplication()->getIdentity()->id;
                    $__miPerfilItemid = \Joomla\Component\Artistas\Site\Helper\RouteHelper::getItemid('miperfil');
                    foreach ($__noticias as $__n):
                        $__isMine = $__currentUserId && ($__currentUserId == ($item->created_by ?? 0));
                    ?>
                    <div class="border-bottom pb-3 mb-3" style="border-color:rgba(180,140,60,.2)!important">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <span class="badge <?= $__n->tipo==='concierto'?'bg-primary':'bg-warning text-dark' ?> mb-1">
                                    <?= $__n->tipo==='concierto'?'🎤 Concierto':'💿 Álbum/Single' ?>
                                </span>
                                <h4 style="color:#f0e0c0;font-size:1rem;margin:0 0 .3rem"><?= htmlspecialchars($__n->title) ?></h4>
                                <small style="color:#6a5f4a"><?= date('d/m/Y', strtotime($__n->created)) ?></small>
                            </div>
                        </div>
                        <?php if (!empty($__n->introtext)): ?>
                        <div style="color:#c9b890;font-size:.9rem;margin-top:.5rem">
                            <?= $__n->introtext ?>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex gap-2 align-items-center flex-wrap mt-1">
                            <a href="/index.php?option=com_content&view=article&id=<?= $__n->article_id ?>&catid=19"
                               style="color:#d4bb8a;font-size:.82rem">Leer más →</a>
                            <?php if ($__isMine): ?>
                            <a href="index.php?option=com_artistas&view=miperfil<?= $__miPerfilItemid ? '&Itemid='.$__miPerfilItemid : '' ?>#noticias"
                               style="background:rgba(180,130,20,.3);border:1px solid rgba(200,160,50,.4);color:#f5c842;font-size:.78rem;padding:2px 10px;border-radius:4px;text-decoration:none">
                               ✏️ Editar
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- RESEÑAS -->
            <div class="af-card" id="resenas">
                <div class="af-card-h">⭐ Valoraciones</div>
                <div class="af-card-b">
                    <?php if (!empty($item->resenas)): ?>
                        <?php foreach ($item->resenas as $r): ?>
                        <div class="border-bottom pb-3 mb-3" style="border-color:rgba(180,140,60,.2)!important">
                            <div class="d-flex justify-content-between">
                                <strong style="color:#f0e0c0"><?= htmlspecialchars($r->reviewer_name) ?></strong>
                                <span>
                                    <span class="af-stars"><?= str_repeat('★', (int)$r->rating) ?></span>
                                    <span class="af-stars-e"><?= str_repeat('★', 5-(int)$r->rating) ?></span>
                                </span>
                            </div>
                            <?php if (!empty($r->event_type)): ?><small style="color:#8a7a60">Evento: <?= htmlspecialchars($r->event_type) ?></small><br><?php endif; ?>
                            <p class="mt-1 mb-0" style="color:#c9b890"><?= nl2br(htmlspecialchars($r->review)) ?></p>
                            <small style="color:#6a5f4a"><?= date('d/m/Y', strtotime($r->created)) ?></small>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#6a5f4a">Aún no hay reseñas. ¡Sé el primero!</p>
                    <?php endif; ?>

                    <div class="mt-4 pt-3" style="border-top:1px solid rgba(180,140,60,.2)">
                        <h4 class="h6 mb-3" style="color:#d4bb8a">Deja tu valoración</h4>
                        <form action="index.php?option=com_artistas&task=resena.submit" method="post">
                            <div class="row g-2">
                                <div class="col-md-6"><input type="text" name="reviewer_name" class="af-form-ctrl" placeholder="Tu nombre *" required></div>
                                <div class="col-md-6"><input type="email" name="reviewer_email" class="af-form-ctrl" placeholder="Tu email (no se publica)"></div>
                                <div class="col-12">
                                    <div class="af-resena-stars">
                                        <?php for ($s=5;$s>=1;$s--): ?>
                                        <input type="radio" name="rating" value="<?=$s?>" id="rs<?=$s?>" <?=$s===5?'checked':''?>>
                                        <label for="rs<?=$s?>">★</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="col-md-6"><input type="text" name="event_type" class="af-form-ctrl" placeholder="Tipo de evento"></div>
                                <div class="col-12"><textarea name="review" class="af-form-ctrl" rows="3" placeholder="Tu reseña *" required></textarea></div>
                            </div>
                            <input type="hidden" name="artista_id" value="<?= (int)$item->id ?>">
                                                        <button type="submit" style="background:rgba(180,130,20,.4);border:1px solid rgba(200,160,60,.4);color:#f5c842;padding:.4rem 1rem;border-radius:6px;cursor:pointer;margin-top:.5rem;">Enviar reseña</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <!-- SIDEBAR -->
        <div class="col-lg-4">
            <div class="af-sidebar sticky-top" style="top:80px">
                <div class="af-sidebar-h">💶 Solicitar presupuesto</div>
                <div class="af-sidebar-b">

                    <?php if (!empty($socials)): ?>
                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        <?php foreach ($socials as $s): ?>
                        <a href="<?= htmlspecialchars($s['url']) ?>" target="_blank" rel="noopener"
                           class="af-social" title="<?= $s['label'] ?>"
                           style="color:<?= $s['color'] ?>">
                            <?= $s['svg'] ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($item->website)): ?>
                    <a href="<?= htmlspecialchars($item->website) ?>" target="_blank" rel="noopener" class="af-btn-web">🌐 Web oficial</a>
                    <?php endif; ?>

                    <?php if (!empty($item->email)): ?>
                    <?php
                        $mailSubject = rawurlencode('Solicitud de presupuesto a ' . $item->name . ' para evento');
                        $mailtoUrl   = 'mailto:' . rawurlencode($item->email) . '?subject=' . $mailSubject;
                    ?>
                    <a href="<?= $mailtoUrl ?>" class="af-btn-submit"
                       style="display:block;text-align:center;text-decoration:none;padding:.8rem;margin-bottom:.8rem">
                        ✉️ Solicitar presupuesto
                    </a>
                    <?php endif; ?>

                    <?php if (!empty($item->phone)): ?>
                    <div style="margin-bottom:.8rem">
                        <button type="button" onclick="toggleWA(this)"
                            style="width:100%;background:transparent;border:1px dashed rgba(180,140,60,.3);color:#6a5f4a;padding:.5rem;border-radius:6px;cursor:pointer;font-size:.82rem;transition:all .2s">
                            ¿Prefieres WhatsApp? <span style="font-size:.75rem">▼</span>
                        </button>
                        <div id="wa-btn" style="display:none;margin-top:.4rem">
                            <a href="https://wa.me/<?= preg_replace('/\D/','',$item->phone) ?>?text=<?= rawurlencode('Hola, me interesa solicitar presupuesto a ' . $item->name . ' para un evento.') ?>"
                               target="_blank" rel="noopener"
                               style="display:block;text-align:center;background:#25D366;color:#fff;font-weight:700;padding:.7rem;border-radius:6px;text-decoration:none;font-size:.9rem">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16" style="margin-right:6px;vertical-align:middle"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                Abrir WhatsApp
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form action="index.php?option=com_artistas&task=presupuesto.submit" method="post">
                        <input type="text"  name="client_name"    class="af-form-ctrl" placeholder="Tu nombre *" required>
                        <input type="email" name="client_email"   class="af-form-ctrl" placeholder="Tu email *" required>
                        <select name="event_type" class="af-form-ctrl">
                            <option value="">Tipo de evento…</option>
                            <?php foreach ($eventLabels as $k=>$v): ?><option value="<?=$k?>"><?=$v?></option><?php endforeach; ?>
                            <option value="Otro">Otro</option>
                        </select>
                        <input type="date" name="event_date"      class="af-form-ctrl">
                        <input type="text" name="event_location"  class="af-form-ctrl" placeholder="Lugar del evento">
                        <textarea name="message" class="af-form-ctrl" rows="3" placeholder="Cuéntanos más…"></textarea>
                        <input type="hidden" name="artista_id" value="<?= (int)$item->id ?>">
                                                <button type="submit" class="af-btn-submit">Pide presupuesto</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($relacionados)): ?>
    <div class="mt-5">
        <h3 class="h5 mb-3" style="color:#d4bb8a">🎶 Artistas similares</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem">
            <?php foreach ($relacionados as $rel):
                $__relUrl = 'index.php?option=com_artistas&view=artista&id=' . $rel->id . ($__itemid ?? '');
            ?>
            <a href="<?= $__relUrl ?>" style="text-decoration:none">
                <div style="background:rgba(10,8,6,.85);border:1px solid rgba(180,140,80,.3);border-radius:10px;overflow:hidden;transition:transform .2s;cursor:pointer" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
                    <?php if (!empty($rel->image)): ?>
                    <img src="<?= Uri::root() . htmlspecialchars($rel->image) ?>" style="width:100%;aspect-ratio:1;object-fit:cover" alt="<?= htmlspecialchars($rel->name) ?>">
                    <?php else: ?>
                    <div style="width:100%;aspect-ratio:1;background:rgba(180,140,60,.1);display:flex;align-items:center;justify-content:center;font-size:2.5rem">🎸</div>
                    <?php endif; ?>
                    <div style="padding:.6rem .8rem">
                        <div style="color:#f0e0c0;font-weight:700;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($rel->name) ?></div>
                        <?php if (!empty($rel->location)): ?>
                        <div style="color:#6a5f4a;font-size:.75rem">📍 <?= htmlspecialchars($rel->location) ?></div>
                        <?php endif; ?>
                        <div style="color:#b87d1e;font-size:.75rem;margin-top:4px">Ver ficha →</div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
<script>
function toggleWA(btn) {
    const box = document.getElementById('wa-btn');
    const arrow = btn.querySelector('span');
    if (box.style.display === 'none') {
        box.style.display = 'block';
        arrow.textContent = '▲';
        btn.style.borderColor = 'rgba(37,211,102,.4)';
        btn.style.color = '#25D366';
    } else {
        box.style.display = 'none';
        arrow.textContent = '▼';
        btn.style.borderColor = 'rgba(180,140,60,.3)';
        btn.style.color = '#6a5f4a';
    }
}
</script>

<?php
// Schema.org MusicGroup JSON-LD
$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'MusicGroup',
    'name'     => $item->name,
    'genre'    => $item->genre ?? '',
    'url'      => \Joomla\CMS\Uri\Uri::current(),
];
if (!empty($item->image))            $schema['image']              = \Joomla\CMS\Uri\Uri::root() . $item->image;
if (!empty($item->short_desc))       $schema['description']        = strip_tags($item->short_desc);
if (!empty($item->email))            $schema['email']              = $item->email;
if (!empty($item->website))          $schema['sameAs'][]           = $item->website;
if (!empty($item->social_instagram)) $schema['sameAs'][]           = $item->social_instagram;
if (!empty($item->social_facebook))  $schema['sameAs'][]           = $item->social_facebook;
if (!empty($item->social_youtube))   $schema['sameAs'][]           = $item->social_youtube;
if (!empty($item->provincia_origen)) $schema['foundingLocation']   = ['@type'=>'Place','name'=>$item->provincia_origen.', Espana'];
$mpComp2 = is_array($item->componentes ?? null) ? $item->componentes : json_decode($item->componentes ?? '[]', true);
foreach (($mpComp2 ?: []) as $c) {
    if (!empty($c['nombre'])) $schema['member'][] = ['@type'=>'OrganizationRole','member'=>['@type'=>'Person','name'=>$c['nombre']],'roleName'=>$c['instrumento']??''];
}
echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) . '</script>';
?>
