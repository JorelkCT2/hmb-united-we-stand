<?php defined('_JEXEC') or die; ?>
<script>
setTimeout(function(){
    var bp = document.querySelector('select[name="jform[banda_id]"]');
    if(!bp) return;
    var wrap = bp.closest('.control-group') || bp.closest('.mb-3') || bp.parentElement;
    var d = document.createElement('div');
    d.style.cssText='padding:10px 16px;margin-bottom:8px;background:rgba(60,80,200,.25);border:2px solid rgba(100,140,255,.6);border-radius:8px';
    d.innerHTML='<label style="cursor:pointer;display:flex;align-items:center;gap:12px;margin:0">'
        +'<input type="checkbox" id="chk_s" name="jform[solista]" value="1"<?= !empty($item->solista)?" checked":"" ?> style="width:24px;height:24px;cursor:pointer">'
        +'<strong style="color:#88aaff;font-size:1.1rem">🎤 SOLISTA</strong>'
        +'<span style="color:#6a7898;font-size:.85rem"> — desactiva el campo Banda principal</span>'
        +'</label>';
    wrap.parentNode.insertBefore(d, wrap);
    function tog(c){bp.disabled=c;if(c)bp.value='';wrap.style.opacity=c?'0.3':'1';}
    var cb=document.getElementById('chk_s');
    tog(cb.checked);
    cb.addEventListener('change',function(){tog(this.checked);});
}, 800);
</script>
<?php
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$item        = $this->item;
$componentes = [];
if (!empty($item->componentes)) {
    $componentes = is_string($item->componentes) ? (json_decode($item->componentes, true) ?: []) : $item->componentes;
}
$gallery = [];
if (!empty($item->gallery)) {
    $gallery = is_string($item->gallery) ? (json_decode($item->gallery, true) ?: []) : $item->gallery;
}
$videos = [];
if (!empty($item->videos)) {
    $videos = is_string($item->videos) ? (json_decode($item->videos, true) ?: []) : $item->videos;
}
if (empty($videos) && !empty($item->video_url)) {
    $videos = [$item->video_url];
}
$provinciasDestino = [];
if (!empty($item->provincias_destino)) {
    $provinciasDestino = is_string($item->provincias_destino) ? (json_decode($item->provincias_destino, true) ?: []) : $item->provincias_destino;
}

$allProvincias = ['Álava','Albacete','Alicante','Almería','Asturias','Ávila','Badajoz','Barcelona','Burgos','Cáceres','Cádiz','Cantabria','Castellón','Ceuta','Ciudad Real','Córdoba','La Coruña','Cuenca','Girona','Granada','Guadalajara','Gipuzkoa','Huelva','Huesca','Islas Baleares','Jaén','León','Lleida','La Rioja','Lugo','Madrid','Málaga','Melilla','Murcia','Navarra','Ourense','Palencia','Las Palmas','Pontevedra','Salamanca','Santa Cruz de Tenerife','Segovia','Sevilla','Soria','Tarragona','Teruel','Toledo','Valencia','Valladolid','Vizcaya','Zamora','Zaragoza'];
?>
<form action="<?= Route::_('index.php?option=com_artistas&layout=edit&id=' . (int)$item->id) ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

    <div class="main-card">
        <?= HTMLHelper::_('uitab.startTabSet', 'artistaTabs', ['active' => 'basic', 'recall' => true, 'breakpoint' => 768]) ?>

        <!-- TAB 1: Datos principales -->
        <?= HTMLHelper::_('uitab.addTab', 'artistaTabs', 'basic', 'Datos principales') ?>
        <div class="row">
            <div class="col-lg-9">
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('name') ?></div>
                    <div class="controls"><?= $this->form->getInput('name') ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('short_desc') ?></div>
                    <div class="controls"><?= $this->form->getInput('short_desc') ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('description') ?></div>
                    <div class="controls"><?= $this->form->getInput('description') ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('biografia') ?></div>
                    <div class="controls"><?= $this->form->getInput('biografia') ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('influencias') ?></div>
                    <div class="controls"><?= $this->form->getInput('influencias') ?></div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('state') ?></div>
                    <div class="controls"><?= $this->form->getInput('state') ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('created_by') ?></div>
                    <div class="controls"><?= $this->form->getInput('created_by') ?>
                    <?php if (!empty($this->item->created_by) && $this->item->created_by > 0):
                        $u = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
                        $u->setQuery('SELECT username, email FROM #__users WHERE id=' . (int)$this->item->created_by);
                        $ud = $u->loadObject();
                    ?>
                    <div class="alert alert-info mt-1 p-2 small">
                        👤 <strong><?= htmlspecialchars($ud->username ?? '') ?></strong>
                        &lt;<?= htmlspecialchars($ud->email ?? '') ?>&gt;
                    </div>
                    <?php endif; ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('genre') ?></div>
                    <div class="controls"><?= $this->form->getInput('genre') ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('provincia_origen') ?></div>
                    <div class="controls"><?= $this->form->getInput('provincia_origen') ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('location') ?></div>
                    <div class="controls"><?= $this->form->getInput('location') ?></div>
                </div>
                <hr>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('formats') ?></div>
                    <div class="controls"><?= $this->form->getInput('formats') ?></div>
                </div>
                <hr>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('events') ?></div>
                    <div class="controls"><?= $this->form->getInput('events') ?></div>
                </div>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <!-- TAB 2: Componentes del grupo -->
        <?= HTMLHelper::_('uitab.addTab', 'artistaTabs', 'componentes', 'Componentes del grupo') ?>
        <div class="mb-3">
            <p class="text-muted">Añade los integrantes del grupo con su nombre e instrumento principal.</p>
            <div id="componentes-lista">
                <?php foreach ($componentes as $i => $comp): ?>
                <div class="componente-row row g-2 mb-2" data-index="<?= $i ?>">
                    <div class="col-md-5">
                        <input type="text" name="componentes[<?= $i ?>][nombre]"
                               value="<?= htmlspecialchars($comp['nombre'] ?? '') ?>"
                               class="form-control" placeholder="Nombre del integrante">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="componentes[<?= $i ?>][instrumento]"
                               value="<?= htmlspecialchars($comp['instrumento'] ?? '') ?>"
                               class="form-control" placeholder="Instrumento / rol">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm w-100" onclick="quitarComponente(this)">✕ Quitar</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-success mt-2" onclick="añadirComponente()">➕ Añadir integrante</button>
        </div>
        <input type="hidden" name="componentes_json" id="componentes_json" value="">
        <?= HTMLHelper::_('uitab.endTab') ?>

        <!-- TAB 3: Provincias destino -->
        <?= HTMLHelper::_('uitab.addTab', 'artistaTabs', 'provincias', 'Desplazamiento') ?>
        <div class="mb-3">
            <p class="fw-semibold mb-2">Provincias a las que os desplazáis:</p>
            <div class="mb-3">
                <div class="form-check form-check-inline mb-2">
                    <input class="form-check-input" type="checkbox" name="provincias_destino[]"
                           value="toda_espana" id="pd_toda"
                           <?= in_array('toda_espana', $provinciasDestino) ? 'checked' : '' ?>
                           onchange="toggleTodasProvincias(this)">
                    <label class="form-check-label fw-bold text-primary" for="pd_toda">🇪🇸 Toda España</label>
                </div>
            </div>
            <div id="provincias-grid" class="row row-cols-2 row-cols-md-4 g-1 <?= in_array('toda_espana', $provinciasDestino) ? 'opacity-50 pe-none' : '' ?>">
                <?php foreach ($allProvincias as $prov): ?>
                <div class="col">
                    <div class="form-check">
                        <input class="form-check-input prov-check" type="checkbox"
                               name="provincias_destino[]"
                               value="<?= htmlspecialchars($prov) ?>"
                               id="pd_<?= preg_replace('/\W/', '', $prov) ?>"
                               <?= in_array($prov, $provinciasDestino) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pd_<?= preg_replace('/\W/', '', $prov) ?>"><?= htmlspecialchars($prov) ?></label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <!-- TAB 4: Multimedia -->
        <?= HTMLHelper::_('uitab.addTab', 'artistaTabs', 'media', 'Multimedia') ?>
        <div class="row">
            <!-- Foto principal -->
            <div class="col-lg-6 mb-4">
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('image') ?></div>
                    <div class="controls"><?= $this->form->getInput('image') ?></div>
                </div>
            </div>

            <!-- Galería de fotos -->
            <div class="col-lg-6 mb-4">
                <label class="form-label fw-semibold">Galería de fotos adicionales</label>
                <?php if (!empty($gallery)): ?>
                <p class="text-muted small mb-1">Pulsa <strong>✕</strong> en una foto para eliminarla directamente:</p>
                <div class="row g-2 mb-2" id="gallery-grid">
                    <?php foreach ($gallery as $gi => $gImg): ?>
                    <div class="col-4 gallery-item" id="gitem_<?= $gi ?>">
                        <div class="position-relative">
                            <img src="<?= \Joomla\CMS\Uri\Uri::root() . htmlspecialchars($gImg) ?>"
                                 class="img-fluid rounded"
                                 style="height:80px;object-fit:cover;width:100%">
                            <button type="button"
                                onclick="eliminarFoto(<?= $gi ?>, '<?= addslashes(htmlspecialchars($gImg)) ?>')"
                                style="position:absolute;top:3px;right:3px;background:#dc3545;border:none;color:#fff;width:24px;height:24px;border-radius:50%;cursor:pointer;font-size:14px;line-height:1;padding:0"
                                title="Eliminar esta foto">✕</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="jform[gallery_existing]" id="gallery_existing_json" value="<?= htmlspecialchars(json_encode($gallery)) ?>">
                <?php else: ?>
                <input type="hidden" name="jform[gallery_existing]" id="gallery_existing_json" value="[]">
                <?php endif; ?>
                <input type="file" name="gallery_new[]" multiple accept="image/*" class="form-control mt-2">
                <div class="form-text">Añadir más fotos (Ctrl+clic para varias).</div>
            </div>

            <!-- Vídeos múltiples -->
            <div class="col-12">
                <label class="form-label fw-semibold">🎬 Vídeos (YouTube / Vimeo)</label>
                <p class="text-muted small">Añade tantos vídeos como necesites.</p>
                <div id="videos-lista">
                    <?php if (empty($videos)): ?>
                    <div class="video-row input-group mb-2">
                        <input type="text" name="videos[]" class="form-control" placeholder="https://www.youtube.com/watch?v=…">
                        <button type="button" class="btn btn-outline-danger" onclick="quitarVideo(this)">✕</button>
                    </div>
                    <?php else: ?>
                    <?php foreach ($videos as $vid): ?>
                    <div class="video-row input-group mb-2">
                        <input type="text" name="videos[]" class="form-control"
                               value="<?= htmlspecialchars($vid) ?>"
                               placeholder="https://www.youtube.com/watch?v=…">
                        <button type="button" class="btn btn-outline-danger" onclick="quitarVideo(this)">✕</button>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="añadirVideo()">➕ Añadir vídeo</button>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <!-- TAB 5: Contacto -->
        <?= HTMLHelper::_('uitab.addTab', 'artistaTabs', 'contact', 'Contacto') ?>
        <div class="row">
            <div class="col-lg-4">
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('phone') ?></div>
                    <div class="controls"><?= $this->form->getInput('phone') ?></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('email') ?></div>
                    <div class="controls"><?= $this->form->getInput('email') ?></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('website') ?></div>
                    <div class="controls"><?= $this->form->getInput('website') ?></div>
                </div>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <!-- TAB 5b: Redes sociales -->
        <?= HTMLHelper::_('uitab.addTab', 'artistaTabs', 'social', 'Redes Sociales') ?>
        <div class="row g-3">
            <?php foreach (['social_instagram'=>'📸 Instagram','social_facebook'=>'📘 Facebook','social_youtube'=>'▶️ YouTube','social_tiktok'=>'🎵 TikTok'] as $field=>$label): ?>
            <div class="col-lg-6">
                <div class="control-group">
                    <div class="control-label"><label><?= $label ?></label></div>
                    <div class="controls"><?= $this->form->getInput($field) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <!-- TAB 6: SEO -->
        <?= HTMLHelper::_('uitab.addTab', 'artistaTabs', 'metadata', 'SEO') ?>
        <div class="control-group">
            <div class="control-label"><?= $this->form->getLabel('metadesc') ?></div>
            <div class="controls"><?= $this->form->getInput('metadesc') ?></div>
        </div>
        <div class="control-group">
            <div class="control-label"><?= $this->form->getLabel('metakey') ?></div>
            <div class="controls"><?= $this->form->getInput('metakey') ?></div>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <!-- TAB MÚSICO -->
        <?= HTMLHelper::_('uitab.addTab', 'artistaTabs', 'musico', '🎸 Datos del músico') ?>
        <div class="row">
            <div class="col-lg-9">

                <!-- SOLISTA TOGGLE -->
                <div class="control-group">
                    <div class="control-label"><label>Solista</label></div>
                    <div class="controls">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="chk-solista"
                                   name="jform[solista]" value="1" role="switch"
                                   <?= !empty($item->solista) ? 'checked' : '' ?>
                                   onchange="toggleSolista(this.checked)">
                            <label class="form-check-label text-light" for="chk-solista">Solista (sin banda)</label>
                        </div>
                    </div>
                </div>

                <!-- BANDA -->
                <div class="control-group" id="banda-fields">
                    <div class="control-label"><?= $this->form->getLabel('banda_id') ?></div>
                    <div class="controls"><?= $this->form->getInput('banda_id') ?></div>
                </div>
                <div class="control-group" id="banda-texto-field">
                    <div class="control-label"><?= $this->form->getLabel('banda_texto') ?></div>
                    <div class="controls"><?= $this->form->getInput('banda_texto') ?></div>
                </div>

                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('instrumento') ?></div>
                    <div class="controls"><?= $this->form->getInput('instrumento') ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('pais_origen') ?></div>
                    <div class="controls"><?= $this->form->getInput('pais_origen') ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('fecha_nacimiento') ?></div>
                    <div class="controls"><?= $this->form->getInput('fecha_nacimiento') ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('lugar_nacimiento') ?></div>
                    <div class="controls"><?= $this->form->getInput('lugar_nacimiento') ?></div>
                </div>

                <!-- DEFUNCIÓN -->
                <div class="control-group">
                    <div class="control-label"><label>Defunción</label></div>
                    <div class="controls">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="chk-vive"
                                   name="jform[vive]" value="1" role="switch"
                                   <?= !empty($item->vive) || $item->vive === null ? 'checked' : '' ?>
                                   onchange="toggleDef(this.checked)">
                            <label class="form-check-label text-light" for="chk-vive">Todavía vive</label>
                        </div>
                    </div>
                </div>
                <div id="def-fields" style="<?= !empty($item->vive) || $item->vive === null ? 'display:none' : '' ?>">
                    <div class="control-group">
                        <div class="control-label"><?= $this->form->getLabel('fecha_defuncion') ?></div>
                        <div class="controls"><?= $this->form->getInput('fecha_defuncion') ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?= $this->form->getLabel('lugar_defuncion') ?></div>
                        <div class="controls"><?= $this->form->getInput('lugar_defuncion') ?></div>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label"><?= $this->form->getLabel('wikipedia_url') ?></div>
                    <div class="controls"><?= $this->form->getInput('wikipedia_url') ?></div>
                </div>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab') ?>

        <?= HTMLHelper::_('uitab.endTabSet') ?>

<script>
function toggleDef(vive) {
    document.getElementById('def-fields').style.display = vive ? 'none' : '';
}
function toggleSolista(solista) {
    var opacity = solista ? '0.3' : '1';
    var pointer = solista ? 'none' : '';
    ['banda-fields','banda-texto-field'].forEach(function(id){
        var el = document.getElementById(id);
        if(el){ el.style.opacity=opacity; el.style.pointerEvents=pointer; }
    });
    var sel = document.querySelector('select[name="jform[banda_id]"]');
    if(sel){ sel.disabled=solista; if(solista) sel.value=''; }
}
document.addEventListener('DOMContentLoaded', function(){
    toggleSolista(document.getElementById('chk-solista').checked);
    toggleDef(document.getElementById('chk-vive').checked);
});
</script>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="jform[id]" value="<?= (int)$item->id ?>">
    <?= HTMLHelper::_('form.token') ?>
</form>

<script>
let compIdx = <?= count($componentes) ?>;

function añadirComponente() {
    const lista = document.getElementById('componentes-lista');
    const row = document.createElement('div');
    row.className = 'componente-row row g-2 mb-2';
    row.dataset.index = compIdx;
    row.innerHTML = `
        <div class="col-md-5"><input type="text" name="componentes[${compIdx}][nombre]" class="form-control" placeholder="Nombre del integrante"></div>
        <div class="col-md-5"><input type="text" name="componentes[${compIdx}][instrumento]" class="form-control" placeholder="Instrumento / rol"></div>
        <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm w-100" onclick="quitarComponente(this)">✕ Quitar</button></div>`;
    lista.appendChild(row);
    compIdx++;
}

function quitarComponente(btn) {
    btn.closest('.componente-row').remove();
}

function añadirVideo() {
    const lista = document.getElementById('videos-lista');
    const row = document.createElement('div');
    row.className = 'video-row input-group mb-2';
    row.innerHTML = '<input type="text" name="videos[]" class="form-control" placeholder="https://www.youtube.com/watch?v=…"><button type="button" class="btn btn-outline-danger" onclick="quitarVideo(this)">✕</button>';
    lista.appendChild(row);
}

function quitarVideo(btn) {
    const lista = document.getElementById('videos-lista');
    if (lista.querySelectorAll('.video-row').length > 1) {
        btn.closest('.video-row').remove();
    } else {
        btn.closest('.video-row').querySelector('input').value = '';
    }
}

function toggleTodasProvincias(cb) {
    const grid = document.getElementById('provincias-grid');
    if (cb.checked) {
        grid.classList.add('opacity-50', 'pe-none');
        grid.querySelectorAll('.prov-check').forEach(c => c.checked = false);
    } else {
        grid.classList.remove('opacity-50', 'pe-none');
    }
}
</script>
<script>
function eliminarFoto(idx, path) {
    if (!confirm('¿Eliminar esta foto de la galería?')) return;
    // Quitar del DOM
    const item = document.getElementById('gitem_' + idx);
    if (item) item.remove();
    // Quitar del JSON que se enviará
    const field = document.getElementById('gallery_existing_json');
    let gallery = JSON.parse(field.value || '[]');
    gallery = gallery.filter(p => p !== path);
    field.value = JSON.stringify(gallery);
}
</script>
