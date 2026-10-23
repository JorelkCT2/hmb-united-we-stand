<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

$m       = $this->musico;
$user    = Factory::getApplication()->getIdentity();
$itemid  = RouteHelper::getItemid('musico');
$saveUrl = 'index.php?option=com_artistas&task=musico.save' . ($itemid ? '&Itemid='.$itemid : '');

$provincias = ['Álava','Albacete','Alicante','Almería','Asturias','Ávila','Badajoz','Barcelona','Burgos','Cáceres','Cádiz','Cantabria','Castellón','Ceuta','Ciudad Real','Córdoba','La Coruña','Cuenca','Girona','Granada','Guadalajara','Gipuzkoa','Huelva','Huesca','Islas Baleares','Jaén','León','Lleida','La Rioja','Lugo','Madrid','Málaga','Melilla','Murcia','Navarra','Ourense','Palencia','Las Palmas','Pontevedra','Salamanca','Santa Cruz de Tenerife','Segovia','Sevilla','Soria','Tarragona','Teruel','Toledo','Valencia','Valladolid','Vizcaya','Zamora','Zaragoza'];
// Load genres from DB
try {
    $__dbGen = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
    $__dbGen->setQuery('SELECT name FROM #__artistas_generos WHERE state=1 ORDER BY ordering ASC, name ASC');
    $todosEstilos = $__dbGen->loadColumn() ?: [];
} catch (\Exception $e) {
    $todosEstilos = ['Folk Metal','Heavy Metal','Hard Rock','Rock','Metal','Punk','Variado'];
}
$estilosActivos = json_decode($m->estilos ?? '[]', true) ?: [];

// Parse existing instruments
$instOpciones = ['','Voz','Guitarra','Bajo','Batería','Teclado'];
$instGuardados = array_filter(array_map('trim', explode(',', $m->instrumentos ?? '')));
$instSelects = []; $instOtro = '';
foreach ($instGuardados as $inst) {
    if (in_array($inst, $instOpciones)) $instSelects[] = $inst;
    else $instOtro = $inst;
}
while (count($instSelects) < 4) $instSelects[] = '';
?>
<style>
.mu { color:var(--cream, #E8E0D5); max-width:780px; margin:0 auto; }
.mu-card { background:rgba(10,8,6,.82); border:1px solid rgba(180,140,80,.3); border-radius:10px; margin-bottom:1.5rem; overflow:hidden; }
.mu-card-h { background:rgba(100,70,20,.5); padding:.7rem 1.2rem; font-weight:700; color:var(--gold-pale, #D4BB8A); text-transform:uppercase; font-size:.85rem; letter-spacing:.4px; }
.mu-card-b { padding:1.2rem 1.4rem; }
.mu-label { display:block; color:var(--gold-dim, #7A6030); font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.3px; margin-bottom:3px; }
.mu-ctrl { width:100%; background:rgba(255,255,255,.06)!important; border:1px solid rgba(180,140,60,.3)!important; color:var(--cream, #E8E0D5)!important; border-radius:6px; padding:.45rem .7rem; font-size:.9rem; }
.mu-ctrl:focus { border-color:rgba(200,160,60,.8)!important; outline:none!important; color:#fff!important; }
.mu-ctrl option { background:var(--steel, #1A1A1A); color:var(--cream, #E8E0D5); }
.mu-btn { background:linear-gradient(135deg,var(--gold, #B8860B),var(--gold, #B8860B)); border:none; color:#fff; font-weight:700; padding:.7rem 2rem; border-radius:7px; cursor:pointer; font-size:.95rem; }
.estilo-tag { display:inline-flex; align-items:center; gap:4px; background:rgba(180,120,20,.25); border:1px solid rgba(180,140,60,.3); color:var(--gold-pale, #D4BB8A); border-radius:4px; padding:3px 10px; margin:3px; font-size:.82rem; cursor:pointer; }
.estilo-tag input { accent-color:var(--gold, #B8860B); }
</style>

<div class="mu">
    <div class="text-center mb-4">
        <h1 style="color:#fff;font-size:2rem;font-weight:800">🎸 <?= $m ? 'Mi perfil de músico' : 'Registrarme como músico' ?></h1>
        <p style="color:#c9b99a"><?= $m ? 'Actualiza tu perfil para que las bandas te encuentren.' : 'Crea tu perfil y encuentra banda.' ?></p>
    </div>

    <?php if ($m && $m->state == 1): ?>
    <div style="background:rgba(30,120,60,.25);border:1px solid rgba(60,160,80,.3);border-radius:8px;padding:.8rem 1.2rem;color:#8feba8;margin-bottom:1.2rem">
        ✓ Tu perfil está activo y visible en el <a href="index.php?option=com_artistas&view=musicos<?= $itemid ? '&Itemid='.$itemid : '' ?>" style="color:#8feba8">directorio de músicos</a>.
    </div>
    <?php endif; ?>

    <form action="<?= $saveUrl ?>" method="post" enctype="multipart/form-data" id="form-musico">
        <input type="hidden" name="id" value="<?= (int)($m->id ?? 0) ?>">

        <div class="mu-card">
            <div class="mu-card-h">👤 Datos personales</div>
            <div class="mu-card-b">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="mu-label">Nombre artístico / real *</label>
                        <input type="text" name="nombre" class="mu-ctrl" value="<?= htmlspecialchars($m->nombre ?? '') ?>" required placeholder="Ej: Carlos García">
                    </div>
                    <div class="col-md-6">
                        <label class="mu-label">Instrumento(s) que tocas *</label>
                        <div class="row g-1">
                            <?php for ($__ii=0; $__ii<4; $__ii++): ?>
                            <div class="col-6">
                                <select name="inst_select[]" class="mu-ctrl" style="font-size:.82rem">
                                    <option value="">-- Instrumento <?= $__ii+1 ?> --</option>
                                    <?php foreach (['Voz','Guitarra','Bajo','Batería','Teclado'] as $__io): ?>
                                    <option value="<?= htmlspecialchars($__io) ?>" <?= ($instSelects[$__ii]??'') === $__io ? 'selected':'' ?>><?= htmlspecialchars($__io) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endfor; ?>
                            <div class="col-12">
                                <input type="text" name="inst_otro" class="mu-ctrl" value="<?= htmlspecialchars($instOtro) ?>" placeholder="Otro instrumento (escríbelo aquí)">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="mu-label">Provincia</label>
                        <select name="provincia" class="mu-ctrl">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($provincias as $p): ?>
                            <option value="<?= htmlspecialchars($p) ?>" <?= ($m->provincia ?? '') === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="mu-label">Ciudad</label>
                        <input type="text" name="ciudad" class="mu-ctrl" value="<?= htmlspecialchars($m->ciudad ?? '') ?>" placeholder="Ej: Murcia capital, Cartagena...">
                    </div>
                    <div class="col-12">
                        <label class="mu-label">Sobre mí / experiencia</label>
                        <textarea name="bio" class="mu-ctrl" rows="4" placeholder="Cuéntanos tu experiencia, cuántos años llevas tocando, qué bandas has tenido, qué buscas..."><?= htmlspecialchars($m->bio ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="mu-card">
            <div class="mu-card-h">🎵 Estilos musicales</div>
            <div class="mu-card-b">
                <p style="color:#8a7a60;font-size:.82rem;margin-bottom:.8rem">Marca los estilos en los que te manejas:</p>
                <div>
                    <?php foreach ($todosEstilos as $est): ?>
                    <label class="estilo-tag">
                        <input type="checkbox" name="estilos[]" value="<?= htmlspecialchars($est) ?>"
                            <?= in_array($est, $estilosActivos) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($est) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="mt-2">
                    <input type="text" name="estilos[]" class="mu-ctrl" style="max-width:300px" placeholder="Otro estilo (escríbelo aquí)">
                </div>
            </div>
        </div>

        <div class="mu-card">
            <div class="mu-card-h">📷 Foto</div>
            <div class="mu-card-b">
                <?php if (!empty($m->foto)): ?>
                <img src="<?= Uri::root() . htmlspecialchars($m->foto) ?>" style="height:100px;width:100px;object-fit:cover;border-radius:50%;margin-bottom:8px;display:block">
                <?php endif; ?>
                <input type="hidden" name="foto_existing" value="<?= htmlspecialchars($m->foto ?? '') ?>">
                <label for="foto_file" style="display:inline-block;background:rgba(180,120,20,.4);border:1px solid rgba(200,150,50,.4);color:#f5c842;padding:.4rem 1rem;border-radius:6px;cursor:pointer;font-size:.85rem">📷 <?= empty($m->foto) ? 'Subir foto' : 'Cambiar foto' ?></label>
                <input type="file" id="foto_file" name="foto" accept="image/*" style="display:none">
            </div>
        </div>

        <div class="mu-card">
            <div class="mu-card-h">📬 Contacto y notificaciones</div>
            <div class="mu-card-b">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="mu-label">Email de contacto</label>
                        <input type="email" name="email" class="mu-ctrl" value="<?= htmlspecialchars($m->email ?? $user->email ?? '') ?>" placeholder="tu@email.com">
                    </div>
                    <div class="col-md-6">
                        <label class="mu-label">WhatsApp (con prefijo país)</label>
                        <input type="tel" name="whatsapp" class="mu-ctrl" value="<?= htmlspecialchars($m->whatsapp ?? '') ?>" placeholder="Ej: +34 612 345 678">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="mu-label">Notificarme cuando haya coincidencias por:</label>
                        <div class="d-flex gap-3 mt-1">
                            <label style="color:#d4bb8a;cursor:pointer">
                                <input type="checkbox" name="notificar_email" value="1" <?= ($m->notificar_email ?? 1) ? 'checked' : '' ?> style="accent-color:#b87d1e;margin-right:5px">
                                📧 Email
                            </label>
                            <label style="color:#d4bb8a;cursor:pointer">
                                <input type="checkbox" name="notificar_whatsapp" value="1" <?= ($m->notificar_whatsapp ?? 0) ? 'checked' : '' ?> style="accent-color:#b87d1e;margin-right:5px">
                                💬 WhatsApp
                            </label>
                        </div>
                        <p style="color:#6a5f4a;font-size:.78rem;margin-top:4px">Te avisamos cuando una banda busque músico con tu perfil.</p>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="mu-btn">💾 <?= $m ? 'Actualizar perfil' : 'Crear perfil de músico' ?></button>
        <?= HTMLHelper::_('form.token') ?>
    </form>
</div>
