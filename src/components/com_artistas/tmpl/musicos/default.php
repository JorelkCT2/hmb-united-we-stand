<?php
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

$items     = $this->items;
$user      = Factory::getApplication()->getIdentity();
$input     = Factory::getApplication()->getInput();
$itemid    = RouteHelper::getItemid('musicos');
$musicoUrl = 'index.php?option=com_artistas&view=musico' . (RouteHelper::getItemid('musico') ? '&Itemid='.RouteHelper::getItemid('musico') : '');

$provincias = ['Álava','Albacete','Alicante','Almería','Asturias','Ávila','Badajoz','Barcelona','Burgos','Cáceres','Cádiz','Cantabria','Castellón','Ciudad Real','Córdoba','La Coruña','Cuenca','Girona','Granada','Guadalajara','Gipuzkoa','Huelva','Huesca','Islas Baleares','Jaén','León','Lleida','La Rioja','Lugo','Madrid','Málaga','Melilla','Murcia','Navarra','Ourense','Palencia','Las Palmas','Pontevedra','Salamanca','Santa Cruz de Tenerife','Segovia','Sevilla','Soria','Tarragona','Teruel','Toledo','Valencia','Valladolid','Vizcaya','Zamora','Zaragoza'];
?>
<style>
.mu-list { color:#e8e0d5; }
.mu-card { background:rgba(10,8,6,.82); border:1px solid rgba(180,140,80,.3); border-radius:10px; padding:1.2rem; margin-bottom:1rem; display:flex; gap:1.2rem; align-items:flex-start; }
.mu-avatar { width:70px; min-width:70px; height:70px; border-radius:50%; object-fit:cover; }
.mu-avatar-ph { width:70px; min-width:70px; height:70px; border-radius:50%; background:rgba(180,140,60,.15); display:flex; align-items:center; justify-content:center; font-size:2rem; }
.mu-nombre { color:#f0e0c0; font-weight:700; font-size:1rem; margin-bottom:2px; }
.mu-inst { color:#d4bb8a; font-size:.88rem; }
.mu-meta { color:#6a5f4a; font-size:.78rem; margin-top:4px; }
.mu-badge { display:inline-block; background:rgba(180,120,20,.25); border:1px solid rgba(180,140,60,.3); color:#d4bb8a; border-radius:4px; padding:1px 8px; font-size:.75rem; margin:2px; }
</style>

<div class="mu-list">
    <div class="text-center mb-4">
        <h1 style="color:#fff;font-size:1.8rem;font-weight:800">🎸 Músicos buscando banda</h1>
        <p style="color:#c9b99a">Encuentra el músico que necesitas para tu grupo</p>
    </div>

    <!-- Filtros -->
    <form action="index.php" method="get" style="background:rgba(10,8,6,.6);border:1px solid rgba(180,140,60,.2);border-radius:8px;padding:1rem;margin-bottom:1.5rem">
        <input type="hidden" name="option" value="com_artistas">
        <input type="hidden" name="view" value="musicos">
        <?php if ($itemid): ?><input type="hidden" name="Itemid" value="<?= $itemid ?>"><?php endif; ?>
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="filter_instrumento" class="form-control" placeholder="🎸 Instrumento..." value="<?= htmlspecialchars($input->getString('filter_instrumento','')) ?>" style="background:rgba(255,255,255,.06);border:1px solid rgba(180,140,60,.3);color:#f0e8d8">
            </div>
            <div class="col-md-4">
                <select name="filter_provincia" style="width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(180,140,60,.3);color:#f0e8d8;border-radius:6px;padding:.4rem .7rem">
                    <option value="">📍 Todas las provincias</option>
                    <?php foreach ($provincias as $p): ?>
                    <option value="<?= htmlspecialchars($p) ?>" <?= $input->getString('filter_provincia')===$p?'selected':'' ?>><?= htmlspecialchars($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="filter_estilo" class="form-control" placeholder="🎵 Estilo..." value="<?= htmlspecialchars($input->getString('filter_estilo','')) ?>" style="background:rgba(255,255,255,.06);border:1px solid rgba(180,140,60,.3);color:#f0e8d8">
            </div>
            <div class="col-md-1">
                <button type="submit" style="background:linear-gradient(135deg,#b87d1e,#8a5a10);border:none;color:#fff;font-weight:700;padding:.45rem 1rem;border-radius:6px;cursor:pointer;width:100%">🔍</button>
            </div>
        </div>
    </form>

    <?php if (!$user->guest): ?>
    <div style="text-align:right;margin-bottom:1rem">
        <a href="<?= $musicoUrl ?>" style="background:rgba(30,120,60,.4);border:1px solid rgba(60,160,80,.4);color:#8feba8;padding:.5rem 1.2rem;border-radius:6px;text-decoration:none;font-size:.85rem">
            🎸 <?= Factory::getContainer()->get('DatabaseDriver')->setQuery('SELECT id FROM #__artistas_musicos WHERE user_id='.(int)$user->id.' AND state=1')->loadResult() ? 'Editar mi perfil de músico' : 'Crear mi perfil de músico' ?>
        </a>
    </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
    <div style="text-align:center;padding:3rem;color:#6a5f4a">
        <div style="font-size:3rem">🎸</div>
        <p>No hay músicos registrados con esos criterios.</p>
    </div>
    <?php else: ?>
    <?php foreach ($items as $m):
        $estilos = json_decode($m->estilos ?? '[]', true) ?: [];
    ?>
    <div class="mu-card">
        <?php if (!empty($m->foto)): ?>
        <img src="<?= Uri::root() . htmlspecialchars($m->foto) ?>" class="mu-avatar" alt="<?= htmlspecialchars($m->nombre) ?>">
        <?php else: ?>
        <div class="mu-avatar-ph">🎸</div>
        <?php endif; ?>
        <div style="flex:1">
            <div class="mu-nombre"><?= htmlspecialchars($m->nombre) ?></div>
            <div class="mu-inst">🎵 <?= htmlspecialchars($m->instrumentos) ?></div>
            <div class="mu-meta">
                <?php if ($m->provincia): ?>📍 <?= htmlspecialchars($m->provincia) ?><?php if ($m->ciudad): ?> · <?= htmlspecialchars($m->ciudad) ?><?php endif; ?><?php endif; ?>
            </div>
            <?php if (!empty($estilos)): ?>
            <div class="mt-1">
                <?php foreach (array_slice($estilos, 0, 5) as $e): ?>
                <span class="mu-badge"><?= htmlspecialchars($e) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($m->bio)): ?>
            <div style="color:#9a8a6a;font-size:.85rem;margin-top:.5rem"><?= htmlspecialchars(mb_substr($m->bio, 0, 150)) ?><?= mb_strlen($m->bio) > 150 ? '...' : '' ?></div>
            <?php endif; ?>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <?php if ($m->email): ?><a href="mailto:<?= htmlspecialchars($m->email) ?>" style="background:rgba(180,120,20,.3);color:#d4bb8a;padding:3px 12px;border-radius:4px;font-size:.8rem;text-decoration:none">📧 Email</a><?php endif; ?>
                <?php if ($m->whatsapp): ?><a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$m->whatsapp) ?>" target="_blank" style="background:rgba(37,211,102,.2);color:#25d166;padding:3px 12px;border-radius:4px;font-size:.8rem;text-decoration:none">💬 WhatsApp</a><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
