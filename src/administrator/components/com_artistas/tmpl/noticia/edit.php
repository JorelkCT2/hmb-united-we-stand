<?php defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
$n = $this->item;
$allProvincias = ['Álava','Albacete','Alicante','Almería','Asturias','Ávila','Badajoz','Barcelona','Burgos','Cáceres','Cádiz','Cantabria','Castellón','Ceuta','Ciudad Real','Córdoba','La Coruña','Cuenca','Girona','Granada','Guadalajara','Gipuzkoa','Huelva','Huesca','Islas Baleares','Jaén','León','Lleida','La Rioja','Lugo','Madrid','Málaga','Melilla','Murcia','Navarra','Ourense','Palencia','Las Palmas','Pontevedra','Salamanca','Santa Cruz de Tenerife','Segovia','Sevilla','Soria','Tarragona','Teruel','Toledo','Valencia','Valladolid','Vizcaya','Zamora','Zaragoza'];
?>
<form action="index.php" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm">
<div class="d-flex gap-2 mb-3">
    <button type="submit" name="task" value="noticia.apply" class="btn btn-success">💾 Guardar</button>
    <button type="submit" name="task" value="noticia.save" class="btn btn-primary">💾 Guardar y cerrar</button>
    <a href="index.php?option=com_artistas&view=noticias" class="btn btn-danger">✕ Cancelar</a>
</div>
<input type="hidden" name="option" value="com_artistas">
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><strong>Noticia de <?= htmlspecialchars($n->artista_name ?? '') ?></strong></div>
            <div class="card-body">
                <div class="mb-2 p-2 rounded" style="background:rgba(255,255,255,.05);font-size:.85rem">
                    <strong>Título actual:</strong> <?= htmlspecialchars($n->titulo ?? 'Se generará automáticamente al guardar') ?>
                    <div class="text-muted" style="font-size:.78rem">El título se regenera automáticamente según el tipo, artista, lugar y fecha.</div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tipo</label>
                        <select name="tipo" class="form-select" onchange="toggleTipo(this.value)">
                            <option value="concierto" <?= ($n->tipo ?? '') === 'concierto' ? 'selected' : '' ?>>🎤 Concierto</option>
                            <option value="album" <?= ($n->tipo ?? '') === 'album' ? 'selected' : '' ?>>💿 Álbum / Single</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha evento / lanzamiento</label>
                        <input type="date" name="fecha_evento" class="form-control" value="<?= htmlspecialchars($n->fecha_evento ?? '') ?>">
                    </div>
                </div>
                <div id="campos_concierto" <?= ($n->tipo ?? '') === 'album' ? 'style="display:none"' : '' ?>>
                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-bold">Lugar / venue</label>
                            <input type="text" name="lugar" class="form-control" value="<?= htmlspecialchars($n->lugar ?? '') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Provincia</label>
                            <select name="provincia" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($allProvincias as $p): ?>
                                <option value="<?= htmlspecialchars($p) ?>" <?= ($n->provincia ?? '') === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div id="campo_disco" <?= ($n->tipo ?? '') !== 'album' ? 'style="display:none"' : '' ?>>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del disco / single</label>
                        <input type="text" name="nombre_disco" class="form-control" value="<?= htmlspecialchars($n->nombre_disco ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción / contenido</label>
                    <textarea name="descripcion" class="form-control" rows="8"><?= htmlspecialchars(strip_tags($n->descripcion ?? '')) ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><strong>Publicación</strong></div>
            <div class="card-body">
                <p class="text-muted small">Artículo Joomla ID: <strong><?= $n->article_id ?? '-' ?></strong></p>
                <p class="text-muted small">Artista: <strong><?= htmlspecialchars($n->artista_name ?? '') ?></strong></p>
                <p class="text-muted small">Creado: <?= $n->created ? date('d/m/Y H:i', strtotime($n->created)) : '-' ?></p>
                <?php if ($n->imagen): ?>
                <div class="mb-2" id="img-actual">
                    <label class="form-label fw-bold small">Imagen actual:</label><br>
                    <img src="<?= \Joomla\CMS\Uri\Uri::root() . htmlspecialchars($n->imagen) ?>"
                         style="max-width:100%;border-radius:6px;max-height:150px;object-fit:cover;display:block;margin-bottom:6px">
                    <button type="button" onclick="borrarImagen()"
                            class="btn btn-danger btn-sm">✕ Eliminar imagen</button>
                </div>
                <input type="hidden" name="imagen_keep" id="imagen_keep" value="1">
                <?php else: ?>
                <input type="hidden" name="imagen_keep" id="imagen_keep" value="0">
                <?php endif; ?>
                <div class="mb-2 mt-2">
                    <label class="form-label fw-bold small"><?= $n->imagen ? 'Reemplazar imagen:' : 'Añadir imagen:' ?></label><br>
                    <label for="img_file_back" class="btn btn-warning btn-sm" style="cursor:pointer">📷 Elegir imagen</label>
                    <input type="file" name="imagen" id="img_file_back" accept="image/*" style="display:none"
                        onchange="var s=document.getElementById('img_fn');s.textContent=this.files[0]?this.files[0].name:'';s.style.display='inline'">
                    <span id="img_fn" style="display:none;margin-left:8px;color:#198754;font-size:.85rem"></span>
                    <div class="form-text mt-1">Selecciona la imagen y pulsa <strong>Guardar</strong>.</div>
                </div>
                <script>
                function borrarImagen() {
                    if (!confirm('¿Eliminar la imagen de esta noticia?')) return;
                    document.getElementById('imagen_keep').value = '0';
                    document.getElementById('img-actual').style.display = 'none';
                }
                </script>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="id" value="<?= (int)$n->id ?>">
<?= \Joomla\CMS\HTML\HTMLHelper::_('form.token') ?>
</form>
<script>
function toggleTipo(v) {
    document.getElementById('campos_concierto').style.display = v==='concierto' ? '' : 'none';
    document.getElementById('campo_disco').style.display = v==='album' ? '' : 'none';
}
</script>
