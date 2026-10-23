<?php
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

$n        = $this->noticia;
$titulo   = $n->nombre_evento ?? $n->lugar ?? ('Noticia de ' . ($n->artista_name ?? ''));
$fecha    = !empty($n->fecha_evento) ? date('d M Y', strtotime($n->fecha_evento)) : '';
$fichaUrl = 'index.php?option=com_artistas&view=artista&id=' . $n->artista_id;

$tipoLabels = [
    'concierto'  => ['label' => '🎸 Concierto',      'color' => '#2196F3'],
    'disco'      => ['label' => '💿 Álbum / Single',  'color' => '#FF9800'],
    'entrevista' => ['label' => '🎤 Entrevista',       'color' => '#9C27B0'],
    'otro'       => ['label' => '📰 Noticia',          'color' => '#607D8B'],
];
$tipo = $tipoLabels[$n->tipo ?? 'otro'] ?? $tipoLabels['otro'];

$sUrl = urlencode(Uri::current());
$sTit = urlencode($titulo . ' | Heavy Metal Brothers');
?>
<div class="af-wrap" style="max-width:900px;margin:2rem auto;padding:0 1rem">

    <!-- CABECERA -->
    <?php if (!empty($n->artista_image)): ?>
    <div style="position:relative;border-radius:12px;overflow:hidden;margin-bottom:1.5rem;max-height:400px">
        <img src="<?= Uri::root() . htmlspecialchars($n->artista_image) ?>"
             style="width:100%;object-fit:cover;max-height:400px;display:block" alt="<?= htmlspecialchars($n->artista_name ?? '') ?>">
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.85) 0%,transparent 60%)"></div>
        <div style="position:absolute;bottom:1.5rem;left:1.5rem;right:1.5rem">
            <span style="background:<?= $tipo['color'] ?>;color:#fff;font-size:.78rem;font-weight:700;padding:3px 12px;border-radius:20px;text-transform:uppercase;letter-spacing:.5px">
                <?= $tipo['label'] ?>
            </span>
            <h1 style="color:#fff;font-size:1.8rem;font-weight:900;margin:.5rem 0 0;text-shadow:0 2px 8px rgba(0,0,0,.8)">
                <?= htmlspecialchars($titulo) ?>
            </h1>
        </div>
    </div>
    <?php else: ?>
    <div style="margin-bottom:1.5rem">
        <span style="background:<?= $tipo['color'] ?>;color:#fff;font-size:.78rem;font-weight:700;padding:3px 12px;border-radius:20px">
            <?= $tipo['label'] ?>
        </span>
        <h1 style="color:#f0e0c0;font-size:1.8rem;font-weight:900;margin:.5rem 0 0"><?= htmlspecialchars($titulo) ?></h1>
    </div>
    <?php endif; ?>

    <!-- META -->
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
        <?php if (!empty($n->artista_name)): ?>
        <a href="<?= $fichaUrl ?>" style="display:flex;align-items:center;gap:.5rem;color:#d4bb8a;text-decoration:none;font-weight:700">
            🎸 <?= htmlspecialchars($n->artista_name) ?>
        </a>
        <?php endif; ?>
        <?php if ($fecha): ?>
        <span style="color:#6a5f4a;font-size:.85rem">📅 <?= $fecha ?></span>
        <?php endif; ?>
        <?php if (!empty($n->ciudad)): ?>
        <span style="color:#6a5f4a;font-size:.85rem">📍 <?= htmlspecialchars($n->ciudad) ?></span>
        <?php endif; ?>
    </div>

    <!-- DESCRIPCIÓN -->
    <?php if (!empty($n->descripcion)): ?>
    <div style="background:rgba(10,8,6,.7);border:1px solid rgba(180,140,60,.2);border-radius:10px;padding:1.5rem;margin-bottom:1.5rem;color:#c8b89a;line-height:1.8;font-size:1rem">
        <?= nl2br(htmlspecialchars($n->descripcion)) ?>
    </div>
    <?php endif; ?>

    <!-- COMPARTIR -->
    <div style="display:flex;flex-wrap:wrap;gap:.6rem;margin-bottom:2rem">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $sUrl ?>" target="_blank" rel="noopener"
           style="background:#1877f2;color:#fff;padding:.45rem 1rem;border-radius:6px;text-decoration:none;font-size:.85rem;font-weight:700">Facebook</a>
        <a href="https://wa.me/?text=<?= $sTit ?>%20<?= $sUrl ?>" target="_blank" rel="noopener"
           style="background:#25d366;color:#fff;padding:.45rem 1rem;border-radius:6px;text-decoration:none;font-size:.85rem;font-weight:700">WhatsApp</a>
        <a href="https://twitter.com/intent/tweet?url=<?= $sUrl ?>&text=<?= $sTit ?>" target="_blank" rel="noopener"
           style="background:#000;color:#fff;padding:.45rem 1rem;border-radius:6px;text-decoration:none;font-size:.85rem;font-weight:700">X / Twitter</a>
        <a href="https://t.me/share/url?url=<?= $sUrl ?>&text=<?= $sTit ?>" target="_blank" rel="noopener"
           style="background:#2ca5e0;color:#fff;padding:.45rem 1rem;border-radius:6px;text-decoration:none;font-size:.85rem;font-weight:700">Telegram</a>
    </div>

    <!-- VOLVER -->
    <a href="javascript:history.back()" style="color:#d4bb8a;font-size:.9rem">← Volver</a>

</div>
