<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

$item   = $this->item;
$user   = Factory::getApplication()->getIdentity();

// Itemids
$__miItemid   = \Joomla\Component\Artistas\Site\Helper\RouteHelper::getItemid('miperfil');
$__fichaItemid = \Joomla\Component\Artistas\Site\Helper\RouteHelper::getItemid('artista');

$saveAction   = 'index.php?option=com_artistas&task=miperfil.save'   . ($__miItemid ? '&Itemid='.$__miItemid : '');
$deleteAction = 'index.php?option=com_artistas&task=miperfil.delete' . ($__miItemid ? '&Itemid='.$__miItemid : '');

// Genres
try {
    $__db = Factory::getContainer()->get('DatabaseDriver');
    $__db->setQuery('SELECT name FROM #__artistas_generos WHERE state=1 ORDER BY ordering,name');
    $generos = $__db->loadColumn() ?: [];
} catch (\Exception $e) {
    $generos = [];
}

$provincias = ['Álava','Albacete','Alicante','Almería','Asturias','Ávila','Badajoz','Barcelona','Burgos','Cáceres','Cádiz','Cantabria','Castellón','Ceuta','Ciudad Real','Córdoba','La Coruña','Cuenca','Girona','Granada','Guadalajara','Gipuzkoa','Huelva','Huesca','Islas Baleares','Jaén','León','Lleida','La Rioja','Lugo','Madrid','Málaga','Melilla','Murcia','Navarra','Ourense','Palencia','Las Palmas','Pontevedra','Salamanca','Santa Cruz de Tenerife','Segovia','Sevilla','Soria','Tarragona','Teruel','Toledo','Valencia','Valladolid','Vizcaya','Zamora','Zaragoza'];

$decode = function($v) {
    if (is_array($v)) return $v;
    if (empty($v)) return [];
    $r = json_decode($v, true);
    return is_array($r) ? $r : [];
};

$gallery = array_filter($decode($item->gallery ?? ''), function($g) use ($item) {
    return $g !== ($item->image ?? '');
});
$gallery = array_values($gallery);

$_noticiasData = [];
try {
    $__db->setQuery("SELECT n.id, n.tipo, n.imagen, n.fecha_evento, n.lugar, n.provincia, n.nombre_disco, n.descripcion
        FROM #__artistas_noticias n WHERE n.artista_id=" . (int)$item->id . " AND n.state=1 ORDER BY n.created DESC");
    $__noticias = $__db->loadObjectList() ?: [];
    foreach ($__noticias as $__nn) {
        $_noticiasData[$__nn->id] = [
            'tipo'  => $__nn->tipo, 'fecha' => $__nn->fecha_evento ?? '',
            'lugar' => $__nn->lugar ?? '', 'prov' => $__nn->provincia ?? '',
            'disco' => $__nn->nombre_disco ?? '', 'desc' => strip_tags($__nn->descripcion ?? ''),
            'img'   => !empty($__nn->imagen) ? Uri::root() . $__nn->imagen : '',
            'img_rel' => $__nn->imagen ?? '',
        ];
    }
} catch (\Exception $e) { $__noticias = []; }
?>
<style>
.mp { color:#e8e0d5; }
.mp-card { background:rgba(10,8,6,.82); border:1px solid rgba(180,140,80,.3); border-radius:10px; margin-bottom:1.5rem; overflow:hidden; }
.mp-card-h { background:rgba(100,70,20,.5); padding:.7rem 1.2rem; font-weight:700; font-size:.85rem; color:#d4bb8a; text-transform:uppercase; letter-spacing:.4px; display:flex; justify-content:space-between; align-items:center; }
.mp-card-b { padding:1.2rem 1.4rem; }
.mp-label { display:block; color:#8a7a60; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.3px; margin-bottom:3px; }
.mp-ctrl { width:100%; background:rgba(255,255,255,.06)!important; border:1px solid rgba(180,140,60,.3)!important; color:#f0e8d8!important; border-radius:6px; padding:.45rem .7rem; font-size:.9rem; margin-bottom:0; }
.mp-ctrl:focus { border-color:rgba(200,160,60,.8)!important; outline:none!important; color:#fff!important; }
.mp-ctrl option { background:#1a1208; color:#f0e0c0; }
.mp-btn-save { background:linear-gradient(135deg,#b87d1e,#8a5a10); border:1px solid rgba(200,150,60,.5); color:#fff; font-weight:700; font-size:.9rem; padding:.6rem 2rem; border-radius:7px; cursor:pointer; }
.mp-btn-save:hover { background:linear-gradient(135deg,#d4941e,#a06a15); }
.mp-status-ok { background:rgba(30,120,60,.25); border:1px solid rgba(60,160,80,.3); border-radius:8px; padding:.7rem 1.2rem; color:#8feba8; font-size:.9rem; margin-bottom:1rem; }
.mp-status-pend { background:rgba(180,120,20,.25); border:1px solid rgba(200,160,50,.3); border-radius:8px; padding:.7rem 1.2rem; color:#f5c842; font-size:.9rem; margin-bottom:1rem; }
</style>

<div class="mp">

<?php if ($item->state == 1): ?>
<div class="mp-status-ok">✓ Tu ficha está <strong>publicada</strong> y visible.
    <?php $__fichaUrl = 'index.php?option=com_artistas&view=artista&id=' . $item->id . ($__fichaItemid ? '&Itemid='.$__fichaItemid : ''); ?>
    <a href="<?= $__fichaUrl ?>" style="color:#8feba8;margin-left:.5rem">Ver ficha pública →</a>
</div>
<?php else: ?>
<div class="mp-status-pend">⏳ Tu ficha está <strong>pendiente de aprobación</strong>. Te avisaremos por email.</div>
<?php endif; ?>

<!-- ==================== BLOQUE 1: EDITAR FICHA ==================== -->
<div class="mp-card">
    <div class="mp-card-h">✏️ Editar ficha del artista</div>
    <div class="mp-card-b">
        <form id="form-ficha" action="<?= 'index.php?option=com_artistas&task=miperfil.save' . ($__miItemid ? '&Itemid='.$__miItemid : '') ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)$item->id ?>">

            <!-- Datos básicos -->
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="mp-label">Nombre del grupo / artista *</label>
                    <input type="text" name="name" class="mp-ctrl" value="<?= htmlspecialchars($item->name ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="mp-label">Género musical *</label>
                    <select name="genre" class="mp-ctrl" required>
                        <?php foreach ($generos as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>" <?= ($item->genre ?? '') === $g ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="mp-label">Tipo de repertorio</label>
                    <select name="tipo_banda" class="mp-ctrl">
                        <option value="solista" <?= ($item->tipo_banda ?? '') === 'solista' ? 'selected' : '' ?>>🎤 Solista</option>
                        <option value="propias" <?= ($item->tipo_banda ?? 'propias') === 'propias' ? 'selected' : '' ?>>🎵 Canciones propias</option>
                        <option value="versiones" <?= ($item->tipo_banda ?? '') === 'versiones' ? 'selected' : '' ?>>🎸 Versiones</option>
                        <option value="ambas" <?= ($item->tipo_banda ?? '') === 'ambas' ? 'selected' : '' ?>>🎶 Ambas</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="mp-label">Descripción breve *</label>
                    <textarea name="short_desc" class="mp-ctrl" rows="2" required><?= htmlspecialchars($item->short_desc ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="mp-label">Provincia de origen</label>
                    <select name="provincia_origen" class="mp-ctrl">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($provincias as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>" <?= ($item->provincia_origen ?? '') === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="mp-label">Email de contacto</label>
                    <input type="email" name="email" class="mp-ctrl" value="<?= htmlspecialchars($item->email ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="mp-label">Teléfono</label>
                    <input type="tel" name="phone" class="mp-ctrl" value="<?= htmlspecialchars($item->phone ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="mp-label">Descripción completa / Biografía</label>
                    <textarea name="description" class="mp-ctrl" rows="5"><?= htmlspecialchars($item->description ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="mp-label">Influencias</label>
                    <textarea name="influencias" class="mp-ctrl" rows="2"><?= htmlspecialchars($item->influencias ?? '') ?></textarea>
                </div>
                <!-- INTEGRANTES -->
                <div class="col-12">
                    <label class="mp-label">Integrantes de la banda</label>
                    <div id="integrantes-list">
                    <?php
                    $mpComp = $item->componentes ?? '';
                    if (is_string($mpComp)) $mpComp = json_decode($mpComp, true);
                    if (!is_array($mpComp) || empty($mpComp)) $mpComp = [['nombre'=>'','instrumento'=>'']];
                    $mpCi = 0;
                    foreach ($mpComp as $mpC):
                    ?>
                        <div class="row g-1 mb-1 integrante-row">
                            <div class="col-5"><input type="text" name="componentes[<?= $mpCi ?>][nombre]" class="mp-ctrl" value="<?= htmlspecialchars($mpC['nombre'] ?? '') ?>" placeholder="Nombre"></div>
                            <div class="col-5"><input type="text" name="componentes[<?= $mpCi ?>][instrumento]" class="mp-ctrl" value="<?= htmlspecialchars($mpC['instrumento'] ?? '') ?>" placeholder="Instrumento"></div>
                            <div class="col-2"><button type="button" onclick="this.closest('.integrante-row').remove()" style="background:rgba(180,30,30,.4);border:none;color:#faa;border-radius:6px;padding:.4rem .7rem;cursor:pointer;width:100%">✕</button></div>
                        </div>
                    <?php $mpCi++; endforeach; ?>
                    </div>
                    <button type="button" onclick="addIntegrante()" style="background:rgba(30,120,60,.3);border:1px solid rgba(60,160,80,.3);color:#8feba8;border-radius:6px;padding:.3rem 1rem;cursor:pointer;font-size:.82rem;margin-top:4px">➕ Añadir integrante</button>
                </div>

                <!-- VIDEOS -->
                <div class="col-12">
                    <label class="mp-label">Vídeos (URLs de YouTube / Vimeo)</label>
                    <div id="videos-list">
                    <?php
                    $mpVids = $item->videos ?? '';
                    if (is_string($mpVids)) $mpVids = json_decode($mpVids, true);
                    if (!is_array($mpVids) || empty($mpVids)) $mpVids = [''];
                    foreach ($mpVids as $mpV):
                    ?>
                        <div class="row g-1 mb-1 video-row">
                            <div class="col-10"><input type="text" name="videos[]" class="mp-ctrl" value="<?= htmlspecialchars(is_string($mpV) ? $mpV : '') ?>" placeholder="https://www.youtube.com/watch?v=..."></div>
                            <div class="col-2"><button type="button" onclick="this.closest('.video-row').remove()" style="background:rgba(180,30,30,.4);border:none;color:#faa;border-radius:6px;padding:.4rem .7rem;cursor:pointer;width:100%">✕</button></div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addVideo()" style="background:rgba(30,120,60,.3);border:1px solid rgba(60,160,80,.3);color:#8feba8;border-radius:6px;padding:.3rem 1rem;cursor:pointer;font-size:.82rem;margin-top:4px">➕ Añadir vídeo</button>
                </div>

                <div class="col-md-6">
                    <label class="mp-label">Web propia</label>
                    <input type="text" name="website" class="mp-ctrl" value="<?= htmlspecialchars($item->website ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="mp-label">Instagram</label>
                    <input type="text" name="social_instagram" class="mp-ctrl" value="<?= htmlspecialchars($item->social_instagram ?? '') ?>" placeholder="https://instagram.com/...">
                </div>
                <div class="col-md-6">
                    <label class="mp-label">Facebook</label>
                    <input type="text" name="social_facebook" class="mp-ctrl" value="<?= htmlspecialchars($item->social_facebook ?? '') ?>" placeholder="https://facebook.com/...">
                </div>
                <div class="col-md-6">
                    <label class="mp-label">YouTube</label>
                    <input type="text" name="social_youtube" class="mp-ctrl" value="<?= htmlspecialchars($item->social_youtube ?? '') ?>" placeholder="https://youtube.com/...">
                </div>
            </div>

            <!-- Foto principal -->
            <div class="mp-card" style="margin-bottom:1rem">
                <div class="mp-card-h">📷 Foto principal</div>
                <div class="mp-card-b">
                    <?php if (!empty($item->image)): ?>
                    <img src="<?= Uri::root() . htmlspecialchars($item->image) ?>" style="max-height:100px;border-radius:6px;object-fit:cover;margin-bottom:8px;display:block">
                    <?php endif; ?>
                    <input type="hidden" name="image_existing" value="<?= htmlspecialchars($item->image ?? '') ?>">
                    <label for="mp_img_file" style="display:inline-block;background:rgba(180,120,20,.5);border:1px solid rgba(200,150,50,.5);color:#f5c842;font-weight:700;padding:.4rem 1rem;border-radius:6px;cursor:pointer;font-size:.85rem">
                        📷 <?= !empty($item->image) ? 'Cambiar foto' : 'Añadir foto' ?>
                    </label>
                    <input type="file" name="image" id="mp_img_file" accept="image/*" style="display:none"
                           onchange="document.getElementById('mp_img_name').textContent=this.files[0]?this.files[0].name:''">
                    <span id="mp_img_name" style="color:#8feba8;font-size:.8rem;margin-left:8px"></span>
                </div>
            </div>

            <!-- Galería -->
            <div class="mp-card" style="margin-bottom:1rem">
                <div class="mp-card-h">🖼️ Galería de fotos adicionales</div>
                <div class="mp-card-b">
                    <?php if (!empty($gallery)): ?>
                    <div class="d-flex flex-wrap gap-2 mb-2" id="mp-gallery-grid">
                        <?php foreach ($gallery as $mgi => $mgImg): ?>
                        <div style="position:relative" id="mpgitem_<?= $mgi ?>">
                            <img src="<?= Uri::root().htmlspecialchars($mgImg) ?>" style="height:70px;width:70px;object-fit:cover;border-radius:4px">
                            <button type="button" onclick="mpEliminarFoto(<?= $mgi ?>, '<?= addslashes(htmlspecialchars($mgImg)) ?>')"
                                style="position:absolute;top:2px;right:2px;background:#dc3545;border:none;color:#fff;width:20px;height:20px;border-radius:50%;cursor:pointer;font-size:12px;line-height:1;padding:0">✕</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="gallery_existing" id="mp_gallery_existing" value="<?= htmlspecialchars(json_encode($gallery)) ?>">
                    <?php else: ?>
                    <input type="hidden" name="gallery_existing" id="mp_gallery_existing" value="[]">
                    <?php endif; ?>
                    <label for="mp_gallery_file" style="display:inline-block;background:rgba(180,120,20,.5);border:1px solid rgba(200,150,50,.5);color:#f5c842;font-weight:700;padding:.4rem 1rem;border-radius:6px;cursor:pointer;font-size:.85rem">
                        📷 Añadir fotos
                    </label>
                    <input type="file" name="gallery_new[]" id="mp_gallery_file" multiple accept="image/*" style="display:none"
                           onchange="document.getElementById('mp_gallery_names').textContent=this.files.length+' archivo(s) seleccionado(s)'">
                    <span id="mp_gallery_names" style="color:#8feba8;font-size:.8rem;margin-left:8px"></span>
                </div>
            </div>

            <!-- DISCOGRAFÍA -->
            <div class="mp-card" style="margin-bottom:1rem">
                <div class="mp-card-h">💿 Discografía</div>
                <div class="mp-card-b">
                    <div id="disco-list">
                    <?php
                    $mpDiscos = $item->discografia ?? '';
                    if (is_string($mpDiscos)) $mpDiscos = json_decode($mpDiscos, true);
                    if (!is_array($mpDiscos)) $mpDiscos = [];
                    foreach ($mpDiscos as $mpDi => $mpD):
                    ?>
                        <div class="mp-disco-row mb-3 p-2" id="discorow_<?= $mpDi ?>" style="border:1px solid rgba(180,140,60,.2);border-radius:8px">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="mp-label">Portada</label>
                                    <?php if (!empty($mpD['portada'])): ?>
                                    <div style="margin-bottom:6px">
                                        <img src="<?= Uri::root() . htmlspecialchars($mpD['portada']) ?>" style="height:80px;width:80px;object-fit:cover;border-radius:4px;display:block">
                                        <label style="font-size:.75rem;color:#8feba8;margin-top:4px">
                                            <input type="checkbox" name="disco_delete_portada[<?= $mpDi ?>]" value="1"> Eliminar portada
                                        </label>
                                    </div>
                                    <?php endif; ?>
                                    <input type="hidden" name="disco_portada_existing[<?= $mpDi ?>]" value="<?= htmlspecialchars($mpD['portada'] ?? '') ?>">
                                    <label for="disco_portada_<?= $mpDi ?>" style="display:inline-block;background:rgba(180,120,20,.4);border:1px solid rgba(200,150,50,.4);color:#f5c842;font-size:.78rem;padding:3px 10px;border-radius:4px;cursor:pointer">📷 <?= empty($mpD['portada']) ? 'Añadir' : 'Cambiar' ?></label>
                                    <input type="file" id="disco_portada_<?= $mpDi ?>" name="disco_portada[<?= $mpDi ?>]" accept="image/*" style="display:none">
                                </div>
                                <div class="col-md-8">
                                    <div class="row g-1">
                                        <div class="col-12">
                                            <label class="mp-label">Título del disco *</label>
                                            <input type="text" name="disco_titulo[<?= $mpDi ?>]" class="mp-ctrl" value="<?= htmlspecialchars($mpD['titulo'] ?? '') ?>" placeholder="Ej: Entre Sombras y Llamas" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="mp-label">Año</label>
                                            <input type="number" name="disco_anio[<?= $mpDi ?>]" class="mp-ctrl" value="<?= htmlspecialchars($mpD['anio'] ?? '') ?>" placeholder="2024" min="1950" max="2099">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="mp-label">Productora / Sello</label>
                                            <input type="text" name="disco_productora[<?= $mpDi ?>]" class="mp-ctrl" value="<?= htmlspecialchars($mpD['productora'] ?? '') ?>" placeholder="Ej: Sony Music, Independiente...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="document.getElementById('discorow_<?= $mpDi ?>').remove()"
                                style="background:rgba(180,30,30,.4);border:none;color:#faa;border-radius:4px;padding:3px 10px;cursor:pointer;font-size:.78rem;margin-top:6px">
                                ✕ Eliminar este disco
                            </button>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addDisco()" style="background:rgba(30,120,60,.3);border:1px solid rgba(60,160,80,.3);color:#8feba8;border-radius:6px;padding:.4rem 1.2rem;cursor:pointer;font-size:.85rem">
                        ➕ Añadir disco
                    </button>
                </div>
            </div>

            <!-- SEO -->
            <div class="mp-card" style="margin-bottom:1rem">
                <div class="mp-card-h">🔍 SEO — Posicionamiento en buscadores</div>
                <div class="mp-card-b">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="mp-label">Meta descripción <small style="color:#6a5f4a;font-weight:normal">(máx. 160 caracteres — aparece en Google bajo el título)</small></label>
                            <textarea name="metadesc" class="mp-ctrl" rows="2" maxlength="160"
                                oninput="document.getElementById('metadesc_count').textContent=(160-this.value.length)"><?= htmlspecialchars($item->metadesc ?? '') ?></textarea>
                            <small style="color:#6a5f4a">Te quedan <span id="metadesc_count"><?= 160 - mb_strlen($item->metadesc ?? '') ?></span> caracteres</small>
                        </div>
                        <div class="col-12">
                            <label class="mp-label">Palabras clave <small style="color:#6a5f4a;font-weight:normal">(separadas por comas)</small></label>
                            <input type="text" name="metakey" class="mp-ctrl"
                                value="<?= htmlspecialchars($item->metakey ?? '') ?>"
                                placeholder="Ej: folk metal, banda Murcia, música celta, contratar grupo">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 align-items-center">
                <button type="submit" class="mp-btn-save">💾 Guardar cambios de la ficha</button>
            </div>

                    <?= HTMLHelper::_('form.token') ?>
        </form>
    </div>
</div>

<!-- ==================== BUSCAR MÚSICO ==================== -->
<?php
$__busqItemid = $__miItemid;
$__busquedas = [];
try {
    $__dbB = Factory::getContainer()->get('DatabaseDriver');
    $__dbB->setQuery('SELECT * FROM #__artistas_busquedas WHERE artista_id=' . (int)$item->id . ' AND state=1 ORDER BY created DESC');
    $__busquedas = $__dbB->loadObjectList() ?: [];
} catch (\Exception $e) {}

// Genres from DB
try {
    $__dbG = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
    $__dbG->setQuery('SELECT name FROM #__artistas_generos WHERE state=1 ORDER BY ordering ASC, name ASC');
    $__todosEstilos = $__dbG->loadColumn() ?: [];
} catch (\Exception $e) {
    $__todosEstilos = ['Folk Metal','Heavy Metal','Hard Rock','Rock'];
}
$__provincias = ['Álava','Albacete','Alicante','Almería','Asturias','Ávila','Badajoz','Barcelona','Burgos','Cáceres','Cádiz','Cantabria','Castellón','Ciudad Real','Córdoba','La Coruña','Cuenca','Girona','Granada','Guadalajara','Gipuzkoa','Huelva','Huesca','Islas Baleares','Jaén','León','Lleida','La Rioja','Lugo','Madrid','Málaga','Melilla','Murcia','Navarra','Ourense','Palencia','Las Palmas','Pontevedra','Salamanca','Santa Cruz de Tenerife','Segovia','Sevilla','Soria','Tarragona','Teruel','Toledo','Valencia','Valladolid','Vizcaya','Zamora','Zaragoza'];
?>
<div class="mp-card" id="buscar-musico">
    <div class="mp-card-h">🔍 Buscar músico / integrante
        <button type="button" id="btn-busqueda" onclick="toggleBusqueda()"
            style="background:rgba(30,120,60,.4);border:1px solid rgba(60,160,80,.4);color:#8feba8;border-radius:6px;padding:4px 14px;cursor:pointer;font-size:.78rem">
            ➕ Nueva búsqueda
        </button>
    </div>
    <div class="mp-card-b">

        <!-- Form nueva búsqueda -->
        <div id="busqueda-form" style="display:none;background:rgba(0,0,0,.3);border:1px solid rgba(180,140,60,.2);border-radius:8px;padding:1.2rem;margin-bottom:1.2rem">
            <form action="<?= 'index.php?option=com_artistas&task=busqueda.save' . ($__miItemid ? '&Itemid='.$__miItemid : '') ?>"
                  method="post" id="form-busqueda">
                <input type="hidden" name="artista_id" value="<?= $item->id ?>">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="mp-label">Instrumento que buscas *</label>
                        <select name="instrumento" class="mp-ctrl" required>
                            <option value="">-- Seleccionar --</option>
                            <option value="Voz">🎤 Voz / Cantante</option>
                            <option value="Guitarra">🎸 Guitarra</option>
                            <option value="Bajo">🎸 Bajo</option>
                            <option value="Batería">🥁 Batería</option>
                            <option value="Teclado">🎹 Teclado</option>
                            <option value="Otro">Otro instrumento</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="mp-label">Provincia</label>
                        <select name="provincia" class="mp-ctrl">
                            <option value="">-- Cualquiera --</option>
                            <?php foreach ($__provincias as $__p): ?>
                            <option value="<?= htmlspecialchars($__p) ?>"><?= htmlspecialchars($__p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="mp-label">Ciudad</label>
                        <input type="text" name="ciudad" class="mp-ctrl" placeholder="Ej: Madrid, Valencia...">
                    </div>
                    <div class="col-12">
                        <label class="mp-label">Estilos que tocáis</label>
                        <div>
                            <?php foreach ($__todosEstilos as $__est): ?>
                            <label style="display:inline-flex;align-items:center;gap:4px;background:rgba(180,120,20,.2);border:1px solid rgba(180,140,60,.25);color:#d4bb8a;border-radius:4px;padding:2px 8px;margin:2px;font-size:.8rem;cursor:pointer">
                                <input type="checkbox" name="estilos[]" value="<?= htmlspecialchars($__est) ?>" style="accent-color:#b87d1e">
                                <?= htmlspecialchars($__est) ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="mp-label">Descripción / requisitos</label>
                        <textarea name="descripcion" class="mp-ctrl" rows="3" placeholder="Describe qué buscas, qué experiencia requieres, disponibilidad para ensayos..."></textarea>
                    </div>
                </div>
                <?= HTMLHelper::_('form.token') ?>
                <div class="d-flex gap-2 mt-2">
                    <button type="submit" style="background:linear-gradient(135deg,#b87d1e,#8a5a10);border:none;color:#fff;font-weight:700;padding:.5rem 1.5rem;border-radius:6px;cursor:pointer">🔍 Publicar búsqueda</button>
                    <button type="button" onclick="toggleBusqueda()" style="background:transparent;border:1px solid rgba(180,140,60,.3);color:#8a7a60;padding:.5rem 1rem;border-radius:6px;cursor:pointer">Cancelar</button>
                </div>
            </form>
        </div>

        <!-- Búsquedas activas -->
        <?php if (!empty($__busquedas)): ?>
        <div>
            <p style="color:#6a5f4a;font-size:.8rem;margin-bottom:.5rem">Búsquedas activas:</p>
            <?php foreach ($__busquedas as $__b):
                $__bestilo = json_decode($__b->estilos ?? '[]', true) ?: [];
                // Count matches
                $__dbB->setQuery('SELECT COUNT(*) FROM #__artistas_matches WHERE busqueda_id=' . $__b->id);
                $__matchCount = (int)$__dbB->loadResult();
            ?>
            <div style="border:1px solid rgba(180,140,60,.2);border-radius:6px;padding:.8rem;margin-bottom:.5rem;display:flex;justify-content:space-between;align-items:center">
                <div>
                    <strong style="color:#f0e0c0">🎵 <?= htmlspecialchars($__b->instrumento) ?></strong>
                    <?php if ($__b->provincia): ?><span style="color:#888;font-size:.82rem"> · <?= htmlspecialchars($__b->provincia) ?></span><?php endif; ?>
                    <?php if ($__matchCount > 0): ?>
                    <span style="background:rgba(30,120,60,.4);color:#8feba8;border-radius:4px;padding:2px 8px;font-size:.78rem;margin-left:8px">
                        🎸 <?= $__matchCount ?> músico(s) encontrado(s)
                    </span>
                    <?php endif; ?>
                </div>
                <a href="index.php?option=com_artistas&task=busqueda.delete&id=<?= $__b->id ?><?= $__miItemid ? '&Itemid='.$__miItemid : '' ?>"
                   onclick="return confirm('¿Eliminar esta búsqueda?')"
                   style="color:#f07070;font-size:.8rem;text-decoration:none">✕ Eliminar</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color:#6a5f4a;font-size:.85rem">No tienes búsquedas activas. Publica una para que te notifiquemos cuando haya músicos disponibles.</p>
        <?php endif; ?>

        <!-- Link al directorio de músicos -->
        <?php
        // Find musicos menu item directly by link
        $__menuMusicos = Factory::getApplication()->getMenu()->getItems('link', 'index.php?option=com_artistas&view=musicos', true);
        $__mId = $__menuMusicos ? $__menuMusicos->id : 0;
        $__musicosUrl = $__mId
            ? \Joomla\CMS\Router\Route::_('index.php?option=com_artistas&view=musicos&Itemid=' . $__mId)
            : 'index.php?option=com_artistas&view=musicos';
        ?>
        <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(180,140,60,.15)">
            <a href="<?= $__musicosUrl ?>" style="color:#d4bb8a;font-size:.85rem">
                👥 Ver directorio de músicos buscando banda →
            </a>
        </div>
    </div>
</div>

<!-- ==================== BLOQUE 2: NOTICIAS ==================== -->
<div class="mp-card" id="noticias">
    <div class="mp-card-h">📰 Mis noticias y eventos
        <button type="button" id="btn-nueva-noticia" onclick="toggleNoticiaForm()"
            style="background:rgba(30,120,60,.4);border:1px solid rgba(60,160,80,.4);color:#8feba8;border-radius:6px;padding:4px 14px;cursor:pointer;font-size:.78rem">
            ➕ Nueva noticia
        </button>
    </div>
    <div class="mp-card-b">

        <!-- Formulario noticia (inicialmente oculto) -->
        <div id="noticia-form" style="display:none;background:rgba(0,0,0,.3);border:1px solid rgba(180,140,60,.2);border-radius:8px;padding:1.2rem;margin-bottom:1.2rem">
            <form action="<?= 'index.php?option=com_artistas&task=noticia.save' . ($__miItemid ? '&Itemid='.$__miItemid : '') ?>"
                  method="post" enctype="multipart/form-data" id="form-noticia"
                  action="<?= 'index.php?option=com_artistas&task=noticia.save' . ($__miItemid ? '&Itemid='.$__miItemid : '') ?>">
                <input type="hidden" name="artista_id" value="<?= $item->id ?>">
                <input type="hidden" name="noticia_id" id="noticia_id" value="0">

                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <label class="mp-label">Tipo de noticia</label>
                        <select name="tipo" id="noticia_tipo" class="mp-ctrl" onchange="toggleCamposEvento(this.value)">
                            <option value="concierto">🎤 Concierto</option>
                            <option value="album">💿 Nuevo álbum / single</option>
                        </select>
                        <p style="color:#6a5f4a;font-size:.75rem;margin-top:3px">El título se genera automáticamente.</p>
                    </div>
                    <div class="col-md-4" id="campo_fecha">
                        <label class="mp-label">Fecha del evento / lanzamiento</label>
                        <input type="date" name="fecha_evento" id="n_fecha" class="mp-ctrl">
                    </div>
                    <div class="col-md-5" id="campo_lugar">
                        <label class="mp-label">Nombre del evento</label>
                        <input type="text" name="lugar" id="n_nombre_evento" class="mp-ctrl" placeholder="Ej: Rock en el Parque, Festival Metal Sur...">
                    </div>
                    <div class="col-md-3" id="campo_hora">
                        <label class="mp-label">Hora</label>
                        <input type="time" name="hora_evento" id="n_hora" class="mp-ctrl">
                    </div>
                    <div class="col-md-4" id="campo_ciudad" style="display:none">
                        <label class="mp-label">Ciudad</label>
                        <input type="text" name="nombre_evento" id="n_lugar" class="mp-ctrl" placeholder="Ej: Madrid, Barcelona...">
                    </div>
                    <div class="col-md-3" id="campo_provincia" style="display:none">
                        <label class="mp-label">Provincia</label>
                        <select name="provincia" id="n_provincia" class="mp-ctrl">
                            <option value="">-- Provincia --</option>
                            <?php foreach ($provincias as $__prov): ?>
                            <option value="<?= htmlspecialchars($__prov) ?>"><?= htmlspecialchars($__prov) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12" id="campo_disco" style="display:none">
                        <label class="mp-label">Nombre del disco / single</label>
                        <input type="text" name="nombre_disco" id="n_disco" class="mp-ctrl" placeholder="Ej: Entre Sombras y Llamas">
                    </div>
                    <div class="col-12">
                        <label class="mp-label">Descripción</label>
                        <textarea name="introtext" id="n_desc" class="mp-ctrl" rows="4" placeholder="Describe el evento..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="mp-label">Imagen de la noticia</label>
                        <div id="imagen_preview" style="display:none;margin-bottom:.5rem">
                            <img id="imagen_preview_img" src="" style="max-height:100px;border-radius:4px;object-fit:cover">
                            <button type="button" onclick="quitarImagenNoticia()"
                                style="display:block;margin-top:4px;background:rgba(180,30,30,.5);border:1px solid rgba(200,60,60,.4);color:#faa;border-radius:4px;padding:2px 10px;cursor:pointer;font-size:.78rem">
                                ✕ Quitar imagen
                            </button>
                        </div>
                        <input type="hidden" name="imagen_existing" id="imagen_existing" value="">
                        <label for="n_imagen_file" style="display:inline-block;background:rgba(180,120,20,.5);border:1px solid rgba(200,150,50,.5);color:#f5c842;font-weight:700;padding:.4rem 1rem;border-radius:6px;cursor:pointer;font-size:.82rem">📷 Seleccionar imagen</label>
                        <input type="file" name="imagen" id="n_imagen_file" accept="image/*" style="display:none"
                               onchange="previsualizarNuevaImg(this)">
                        <span id="n_img_name" style="color:#8feba8;font-size:.8rem;margin-left:8px"></span>
                    </div>
                </div>

                    <?= HTMLHelper::_('form.token') ?>
                <div class="d-flex gap-2">
                    <button type="submit" style="background:linear-gradient(135deg,#b87d1e,#8a5a10);border:none;color:#fff;font-weight:700;padding:.5rem 1.5rem;border-radius:6px;cursor:pointer">
                        💾 Publicar noticia
                    </button>
                    <button type="button" onclick="toggleNoticiaForm()" style="background:transparent;border:1px solid rgba(180,140,60,.3);color:#8a7a60;padding:.5rem 1rem;border-radius:6px;cursor:pointer">Cancelar</button>
                </div>
            </form>
        </div>

        <!-- Listado noticias -->
        <?php if (!empty($__noticias)): ?>
        <p style="color:#6a5f4a;font-size:.8rem;margin-bottom:.5rem">Noticias publicadas:</p>
        <table class="table table-sm" style="color:#e8e0d5">
            <thead><tr>
                <th>Noticia</th><th>Tipo</th><th>Fecha</th><th class="text-center">Acciones</th>
            </tr></thead>
            <tbody>
            <?php foreach ($__noticias as $__n):
                $__d = $_noticiasData[$__n->id] ?? [];
                $__imgUrl = !empty($__d['img']) ? $__d['img'] : '';
            ?>
            <tr>
                <td style="font-size:.82rem"><?php
                    if ($__n->tipo === 'album' && !empty($__n->nombre_disco)) echo '"'.htmlspecialchars($__n->nombre_disco).'"';
                    elseif ($__n->tipo === 'concierto') {
                        echo '🎤 Concierto';
                        if (!empty($__n->lugar)) echo ' en '.htmlspecialchars($__n->lugar);
                    }
                    if (!empty($__n->fecha_evento)) echo ' · '.date('d/m/Y', strtotime($__n->fecha_evento));
                ?></td>
                <td><span class="badge <?= $__n->tipo==='concierto'?'bg-primary':'bg-warning text-dark' ?>"><?= $__n->tipo==='concierto'?'🎤':'💿' ?></span></td>
                <td><small><?= $__n->fecha_evento ? date('d/m/Y', strtotime($__n->fecha_evento)) : '' ?></small></td>
                <td class="text-center" style="white-space:nowrap">
                    <button type="button" class="btn btn-sm btn-outline-warning"
                        onclick="editarNoticia(<?= $__n->id ?>)">✏️</button>
                    <a href="index.php?option=com_artistas&task=noticia.delete&id=<?= $__n->id ?>&artista_id=<?= $item->id ?><?= $__miItemid ? '&Itemid='.$__miItemid : '' ?>"
                       class="btn btn-sm btn-outline-danger ms-1"
                       onclick="return confirm('¿Eliminar esta noticia?')">✕</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

</div>

<script>
var _noticiasData = <?= json_encode($_noticiasData) ?>;

function toggleBusqueda() {
    const f = document.getElementById('busqueda-form');
    const b = document.getElementById('btn-busqueda');
    const show = f.style.display === 'none';
    f.style.display = show ? 'block' : 'none';
    b.textContent = show ? '✕ Cancelar' : '➕ Nueva búsqueda';
    if (show) f.scrollIntoView({behavior:'smooth'});
}
function toggleNoticiaForm() {
    const f = document.getElementById('noticia-form');
    const b = document.getElementById('btn-nueva-noticia');
    const show = f.style.display === 'none';
    f.style.display = show ? 'block' : 'none';
    b.textContent = show ? '✕ Cancelar' : '➕ Nueva noticia';
    if (show) {
        // Reset form
        document.getElementById('noticia_id').value = '0';
        document.getElementById('noticia_tipo').value = 'concierto';
        ['n_fecha','n_lugar','n_disco','n_desc'].forEach(id => { const e=document.getElementById(id); if(e) e.value=''; });
        const pEl = document.getElementById('n_provincia'); if(pEl) pEl.value='';
        document.getElementById('imagen_preview').style.display = 'none';
        document.getElementById('imagen_existing').value = '';
        toggleCamposEvento('concierto');
        f.scrollIntoView({behavior:'smooth'});
    }
}

function editarNoticia(id) {
    const d = _noticiasData[id];
    if (!d) return;
    document.getElementById('noticia_id').value = id;
    const sel = document.getElementById('noticia_tipo');
    if(sel) { sel.value = d.tipo; toggleCamposEvento(d.tipo); }
    const set = (eid, val) => { const e=document.getElementById(eid); if(e) e.value=val||''; };
    set('n_nombre_evento', d.lugar);      // lugar = nombre del evento
    const fp = (d.fecha||'').split(' ');
    set('n_fecha', fp[0]||'');
    set('n_hora',  fp[1]||'');
    set('n_lugar', d.disco||'');          // nombre_disco = ciudad
    set('n_disco', d.disco||'');          // for albums
    set('n_desc',  d.desc);
    const pEl = document.getElementById('n_provincia'); if(pEl) pEl.value = d.prov||'';
    const preview = document.getElementById('imagen_preview');
    const previewImg = document.getElementById('imagen_preview_img');
    const imgField = document.getElementById('imagen_existing');
    if(d.img) { previewImg.src=d.img; preview.style.display='block'; imgField.value=d.img_rel||''; }
    else { preview.style.display='none'; imgField.value=''; }
    const f = document.getElementById('noticia-form');
    f.style.display = 'block';
    document.getElementById('btn-nueva-noticia').textContent = '✕ Cancelar';
    f.scrollIntoView({behavior:'smooth'});
}

function toggleCamposEvento(tipo) {
    const esC = tipo==='concierto', esA = tipo==='album';
    ['campo_lugar','campo_ciudad','campo_provincia','campo_hora'].forEach(id => {
        const e=document.getElementById(id); if(e) e.style.display=esC?'':'none';
    });
    const cd = document.getElementById('campo_disco'); if(cd) cd.style.display=esA?'':'none';
}

function previsualizarNuevaImg(input) {
    if (!input.files||!input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('imagen_preview_img').src = e.target.result;
        document.getElementById('imagen_preview').style.display = 'block';
        document.getElementById('n_img_name').textContent = file.name;
    };
    reader.readAsDataURL(file);
}

function quitarImagenNoticia() {
    document.getElementById('imagen_preview').style.display='none';
    document.getElementById('imagen_preview_img').src='';
    document.getElementById('imagen_existing').value='';
    document.getElementById('n_img_name').textContent='';
}

function addDisco() {
    const list = document.getElementById('disco-list');
    const idx  = list.querySelectorAll('.mp-disco-row').length;
    const row  = document.createElement('div');
    row.className = 'mp-disco-row mb-3 p-2';
    row.id = 'discorow_new_' + idx;
    row.style.cssText = 'border:1px solid rgba(180,140,60,.2);border-radius:8px';
    row.innerHTML = `
        <div class="row g-2">
            <div class="col-md-4">
                <label class="mp-label">Portada</label>
                <label style="display:inline-block;background:rgba(180,120,20,.4);border:1px solid rgba(200,150,50,.4);color:#f5c842;font-size:.78rem;padding:3px 10px;border-radius:4px;cursor:pointer">
                    📷 Añadir portada
                    <input type="file" name="disco_portada[new_${idx}]" accept="image/*" style="display:none">
                </label>
            </div>
            <div class="col-md-8">
                <div class="row g-1">
                    <div class="col-12">
                        <label class="mp-label">Título del disco *</label>
                        <input type="text" name="disco_titulo[new_${idx}]" class="mp-ctrl" placeholder="Ej: Entre Sombras y Llamas">
                    </div>
                    <div class="col-md-4">
                        <label class="mp-label">Año</label>
                        <input type="number" name="disco_anio[new_${idx}]" class="mp-ctrl" placeholder="2024" min="1950" max="2099">
                    </div>
                    <div class="col-md-8">
                        <label class="mp-label">Productora / Sello</label>
                        <input type="text" name="disco_productora[new_${idx}]" class="mp-ctrl" placeholder="Ej: Independiente">
                    </div>
                </div>
            </div>
        </div>
        <button type="button" onclick="this.closest('.mp-disco-row').remove()"
            style="background:rgba(180,30,30,.4);border:none;color:#faa;border-radius:4px;padding:3px 10px;cursor:pointer;font-size:.78rem;margin-top:6px">
            ✕ Eliminar este disco
        </button>`;
    list.appendChild(row);
}
function makeRemoveBtn(rowClass) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = '✕';
    btn.style.cssText = 'background:rgba(180,30,30,.4);border:none;color:#faa;border-radius:6px;padding:.4rem .7rem;cursor:pointer;width:100%';
    btn.addEventListener('click', function(){ this.closest('.' + rowClass).remove(); });
    return btn;
}
function addIntegrante() {
    const list = document.getElementById('integrantes-list');
    const idx  = list.querySelectorAll('.integrante-row').length;
    const row  = document.createElement('div');
    row.className = 'row g-1 mb-1 integrante-row';

    const c1 = document.createElement('div'); c1.className = 'col-5';
    const i1 = document.createElement('input'); i1.type='text'; i1.name='componentes['+idx+'][nombre]'; i1.className='mp-ctrl'; i1.placeholder='Nombre';
    c1.appendChild(i1);

    const c2 = document.createElement('div'); c2.className = 'col-5';
    const i2 = document.createElement('input'); i2.type='text'; i2.name='componentes['+idx+'][instrumento]'; i2.className='mp-ctrl'; i2.placeholder='Instrumento';
    c2.appendChild(i2);

    const c3 = document.createElement('div'); c3.className = 'col-2';
    c3.appendChild(makeRemoveBtn('integrante-row'));

    row.appendChild(c1); row.appendChild(c2); row.appendChild(c3);
    list.appendChild(row);
}
function addVideo() {
    const list = document.getElementById('videos-list');
    const row  = document.createElement('div');
    row.className = 'row g-1 mb-1 video-row';

    const c1 = document.createElement('div'); c1.className = 'col-10';
    const i1 = document.createElement('input'); i1.type='text'; i1.name='videos[]'; i1.className='mp-ctrl'; i1.placeholder='https://www.youtube.com/watch?v=...';
    c1.appendChild(i1);

    const c2 = document.createElement('div'); c2.className = 'col-2';
    c2.appendChild(makeRemoveBtn('video-row'));

    row.appendChild(c1); row.appendChild(c2);
    list.appendChild(row);
}
function mpEliminarFoto(idx, path) {
    if (!confirm('¿Eliminar esta foto?')) return;
    const item = document.getElementById('mpgitem_' + idx);
    if (item) item.remove();
    const field = document.getElementById('mp_gallery_existing');
    if (field) {
        let gallery = JSON.parse(field.value || '[]');
        gallery = gallery.filter(p => p !== path);
        field.value = JSON.stringify(gallery);
    }
}
</script>
