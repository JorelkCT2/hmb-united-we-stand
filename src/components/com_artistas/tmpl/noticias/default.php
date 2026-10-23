<?php defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

$noticias      = $this->noticias;
$artistaNombre = $this->artistaNombre;
$currentUser   = Factory::getApplication()->getIdentity();
$miPerfilItemid = RouteHelper::getItemid('miperfil');

// IDs de artistas que pertenecen al usuario logueado
$__misArtistas = [];
if (!$currentUser->guest) {
    try {
        $__db2 = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $__db2->setQuery('SELECT id FROM #__artistas WHERE created_by=' . (int)$currentUser->id);
        $__misArtistas = $__db2->loadColumn() ?: [];
    } catch (\Exception $e) {}
}
?>
<style>
.an { color:#e8e0d5; }
.an-card { background:rgba(10,8,6,.82); border:1px solid rgba(180,140,80,.3); border-radius:10px; margin-bottom:1.2rem; overflow:hidden; display:flex; gap:0; }
.an-img { width:180px; min-width:180px; object-fit:cover; }
.an-body { padding:1.2rem 1.4rem; flex:1; }
.an-badge-c { background:#0d6efd; color:#fff; font-size:.72rem; font-weight:700; padding:2px 8px; border-radius:4px; }
.an-badge-a { background:#ffc107; color:#000; font-size:.72rem; font-weight:700; padding:2px 8px; border-radius:4px; }
.an-title { color:#f0e0c0; font-size:1.05rem; font-weight:700; margin:.4rem 0 .3rem; }
.an-intro { color:#9a8a6a; font-size:.88rem; }
.an-artista { color:#6a5f4a; font-size:.8rem; }
@media(max-width:600px){ .an-img { display:none; } }
</style>

<div class="an">
    <div class="text-center mb-4">
        <h1 style="color:#fff;font-size:1.8rem;font-weight:800">
            📰 <?= $artistaNombre ? 'Noticias de ' . htmlspecialchars($artistaNombre) : 'Noticias de nuestros grupos' ?>
        </h1>
    </div>

    <?php if (empty($noticias)): ?>
    <div style="text-align:center;padding:3rem;color:#6a5f4a">
        <div style="font-size:3rem;margin-bottom:1rem">📰</div>
        <p>Aún no hay noticias publicadas.</p>
    </div>
    <?php else: ?>
    <?php foreach ($noticias as $n):
        $artImg = !empty($n->artista_image) ? Uri::root() . $n->artista_image : '';
        $fichaUrl = 'index.php?option=com_artistas&view=artista&id=' . $n->artista_id
            . (RouteHelper::getItemid('artista') ? '&Itemid=' . RouteHelper::getItemid('artista') : '');
        $artUrl = 'index.php?option=com_content&view=article&id=' . $n->article_id . '&catid=19';
    ?>
    <div class="an-card">
        <?php if ($artImg): ?>
        <a href="<?= $fichaUrl ?>">
            <img src="<?= htmlspecialchars($artImg) ?>" class="an-img" alt="<?= htmlspecialchars($n->artista_name) ?>">
        </a>
        <?php endif; ?>
        <div class="an-body">
            <span class="<?= $n->tipo === 'concierto' ? 'an-badge-c' : 'an-badge-a' ?>">
                <?= $n->tipo === 'concierto' ? '🎤 Concierto' : '💿 Álbum / Single' ?>
            </span>
            <div class="an-title"><?= htmlspecialchars($n->title) ?></div>
            <div class="an-artista">
                Por <a href="<?= $fichaUrl ?>" style="color:#d4bb8a"><?= htmlspecialchars($n->artista_name) ?></a>
                · <?= date('d/m/Y', strtotime($n->created)) ?>
            </div>
            <?php if (!empty($n->introtext)): ?>
            <div class="an-intro mt-2"><?= strip_tags($n->introtext, '<p><strong><em><br>') ?></div>
            <?php endif; ?>
            <a href="<?= $artUrl ?>" style="color:#d4bb8a;font-size:.82rem;display:inline-block;margin-top:.5rem">Leer más →</a>
            <?php if (!$currentUser->guest && in_array($n->artista_id, $__misArtistas)): ?>
            <a href="index.php?option=com_artistas&view=miperfil<?= $miPerfilItemid ? '&Itemid='.$miPerfilItemid : '' ?>#noticias"
               style="display:inline-block;margin-top:.3rem;margin-left:.8rem;background:rgba(180,130,20,.3);border:1px solid rgba(200,160,50,.4);color:#f5c842;font-size:.78rem;padding:2px 10px;border-radius:4px;text-decoration:none">
                ✏️ Editar noticia
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
