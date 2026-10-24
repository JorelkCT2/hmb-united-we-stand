<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$f    = $this->form;
$item = $this->item;

$pricesData = [];
if (!empty($item->prices)) $pricesData = json_decode($item->prices, true) ?? [];
while (count($pricesData) < 5) $pricesData[] = ['label'=>'','amount'=>'','sold_out'=>0];

$headlinersList = [];
if (!empty($item->headliners)) $headlinersList = json_decode($item->headliners, true) ?? [];

$spanishProvinces = [
    ''=>'-- Selecciona --','Álava'=>'Álava','Albacete'=>'Albacete','Alicante'=>'Alicante',
    'Almería'=>'Almería','Asturias'=>'Asturias','Ávila'=>'Ávila','Badajoz'=>'Badajoz',
    'Barcelona'=>'Barcelona','Burgos'=>'Burgos','Cáceres'=>'Cáceres','Cádiz'=>'Cádiz',
    'Cantabria'=>'Cantabria','Castellón'=>'Castellón','Ciudad Real'=>'Ciudad Real',
    'Córdoba'=>'Córdoba','La Coruña'=>'La Coruña','Cuenca'=>'Cuenca','Gerona'=>'Gerona',
    'Granada'=>'Granada','Guadalajara'=>'Guadalajara','Guipúzcoa'=>'Guipúzcoa',
    'Huelva'=>'Huelva','Huesca'=>'Huesca','Jaén'=>'Jaén','León'=>'León','Lérida'=>'Lérida',
    'Lugo'=>'Lugo','Madrid'=>'Madrid','Málaga'=>'Málaga','Murcia'=>'Murcia',
    'Navarra'=>'Navarra','Orense'=>'Orense','Palencia'=>'Palencia',
    'Las Palmas'=>'Las Palmas','Pontevedra'=>'Pontevedra','La Rioja'=>'La Rioja',
    'Salamanca'=>'Salamanca','S.C. Tenerife'=>'S.C. Tenerife','Segovia'=>'Segovia',
    'Sevilla'=>'Sevilla','Soria'=>'Soria','Tarragona'=>'Tarragona','Teruel'=>'Teruel',
    'Toledo'=>'Toledo','Valencia'=>'Valencia','Valladolid'=>'Valladolid',
    'Vizcaya'=>'Vizcaya','Zamora'=>'Zamora','Zaragoza'=>'Zaragoza',
    'Ceuta'=>'Ceuta','Melilla'=>'Melilla','Islas Baleares'=>'Islas Baleares',
];

$currentProvince = $item->province ?? '';
$currentCountry  = $item->country_code ?? 'ES';
?>
<form action="<?= Route::_('index.php?option=com_concerts&id='.(int)($item->id??0)) ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">
<div class="row">
<div class="col-lg-9">

<!-- TÍTULO (protagonista) -->
<div class="card mb-3 border-warning">
    <div class="card-header fw-bold bg-warning bg-opacity-10">🎪 Título del Evento <span class="text-danger">*</span></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12"><?= $f->renderField('title') ?></div>
            <div class="col-12"><?= $f->renderField('alias') ?></div>
        </div>
    </div>
</div>

<!-- ARTISTAS -->
<div class="card mb-3">
    <div class="card-header fw-bold">🎸 Artistas</div>
    <div class="card-body">
        <!-- Cabezas de cartel con + -->
        <div class="mb-3">
            <label class="form-label fw-semibold">⭐ Cabezas de cartel</label>
            <small class="text-muted d-block mb-2">El primero se usará como artista principal</small>
            <div id="headliners-container">
                <?php foreach ($headlinersList as $hl): ?>
                <div class="input-group mb-1 headliner-row">
                    <input type="text" class="form-control headliner-input"
                           value="<?= htmlspecialchars($hl) ?>" placeholder="Nombre del artista o banda" />
                    <button type="button" class="btn btn-outline-danger" onclick="removeHL(this)">✕</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-outline-success btn-sm mt-1" onclick="addHL()">+ Añadir artista</button>
            <input type="hidden" name="jform[headliners]" id="jform_headliners"
                   value="<?= htmlspecialchars($item->headliners ?? '[]') ?>" />
        </div>
        <div class="row g-3">
            <div class="col-md-4"><?= $f->renderField('event_type') ?></div>
            <div class="col-12"><?= $f->renderField('support_bands') ?></div>
            <div class="col-12"><?= $f->renderField('lineup') ?></div>
            <div class="col-12"><?= $f->renderField('genre_ids') ?><small class="text-muted">Ctrl+clic para varios</small></div>
        </div>
    </div>
</div>

<!-- FECHAS -->
<div class="card mb-3">
    <div class="card-header fw-bold">📅 Fechas y Horario</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><?= $f->renderField('date_start') ?></div>
            <div class="col-md-3"><?= $f->renderField('date_end') ?></div>
            <div class="col-md-3"><?= $f->renderField('doors_time') ?></div>
            <div class="col-md-3"><?= $f->renderField('concert_time') ?></div>
        </div>
    </div>
</div>

<!-- LUGAR -->
<div class="card mb-3">
    <div class="card-header fw-bold">📍 Lugar</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><?= $f->renderField('venue') ?></div>
            <div class="col-md-6"><?= $f->renderField('city') ?></div>
            <div class="col-md-4"><?= $f->renderField('country_code') ?></div>
            <div class="col-md-4">
                <label class="form-label">Provincia</label>
                <div id="prov-sel-wrap" style="display:<?= $currentCountry==='ES'?'block':'none' ?>">
                    <select class="form-select" onchange="document.getElementById('jform_province').value=this.value">
                        <?php foreach ($spanishProvinces as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= $currentProvince===$v&&$currentCountry==='ES'?'selected':'' ?>><?= htmlspecialchars($l) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="prov-txt-wrap" style="display:<?= $currentCountry!=='ES'?'block':'none' ?>">
                    <input type="text" class="form-control" value="<?= htmlspecialchars($currentCountry!=='ES'?$currentProvince:'') ?>"
                           placeholder="Región / Provincia"
                           oninput="document.getElementById('jform_province').value=this.value" />
                </div>
                <input type="hidden" name="jform[province]" id="jform_province" value="<?= htmlspecialchars($currentProvince) ?>" />
            </div>
            <div class="col-12"><?= $f->renderField('address') ?></div>
            <div class="col-md-6"><?= $f->renderField('latitude') ?></div>
            <div class="col-md-6"><?= $f->renderField('longitude') ?></div>
            <div class="col-12 small text-muted">Si dejas latitud y longitud vacías, la ciudad se sitúa sola en el mapa (OpenStreetMap) al importar o desde «Importar y actualizar».</div>
        </div>
    </div>
</div>

<!-- PRECIOS -->
<div class="card mb-3">
    <div class="card-header fw-bold">🎟️ Precios (hasta 5 modalidades)</div>
    <div class="card-body">
        <div class="row g-2 mb-1 fw-semibold small"><div class="col-5">Descripción</div><div class="col-3">Precio</div><div class="col-2 text-center">Agotadas</div></div>
        <?php for ($i=0;$i<5;$i++): $p=$pricesData[$i]??['label'=>'','amount'=>'','sold_out'=>0]; ?>
        <div class="row g-2 mb-2 align-items-center">
            <div class="col-5"><input type="text" class="form-control form-control-sm cc-price-label" data-idx="<?= $i ?>" value="<?= htmlspecialchars($p['label']) ?>" placeholder="Ej: Abono, Día 1…" /></div>
            <div class="col-3"><input type="text" class="form-control form-control-sm cc-price-amount" data-idx="<?= $i ?>" value="<?= htmlspecialchars($p['amount']) ?>" placeholder="35€" /></div>
            <div class="col-2 text-center"><input type="checkbox" class="form-check-input cc-price-soldout" data-idx="<?= $i ?>" <?= !empty($p['sold_out'])?'checked':'' ?> /></div>
        </div>
        <?php endfor; ?>
        <input type="hidden" name="jform[prices]" id="jform_prices"
               value="<?= htmlspecialchars(json_encode(array_values(array_filter($pricesData,fn($p)=>!empty($p['label'])||!empty($p['amount']))),JSON_UNESCAPED_UNICODE)) ?>" />
    </div>
</div>

<!-- ENTRADAS Y WEB -->
<div class="card mb-3">
    <div class="card-header fw-bold">🌐 Entradas y Web</div>
    <div class="card-body"><div class="row g-3">
        <div class="col-12"><?= $f->renderField('ticket_url') ?></div>
        <div class="col-12"><?= $f->renderField('website_url') ?></div>
    </div></div>
</div>

<!-- CARTEL -->
<div class="card mb-3">
    <div class="card-header fw-bold">🖼️ Cartel (PNG o JPG)</div>
    <div class="card-body">
        <?php if (!empty($item->poster_url)): ?>
        <div class="mb-3 text-center">
            <img src="<?= htmlspecialchars($item->poster_url) ?>" style="max-height:200px;border-radius:8px;border:1px solid #dee2e6" />
        </div>
        <?php endif; ?>
        <?= $f->renderField('poster_url') ?>
        <?= $f->renderField('description') ?>
    </div>
</div>

</div><!-- col-lg-9 -->
<div class="col-lg-3">
    <div class="card mb-3"><div class="card-header fw-bold">Estado</div><div class="card-body"><?= $f->renderField('state') ?></div></div>
    <?php if (!empty($item->id)): ?>
    <div class="card mb-3"><div class="card-body small text-muted"><div>ID: <strong><?= (int)$item->id ?></strong></div><div>Creado: <?= $item->created ?></div></div></div>
    <?php endif; ?>
    <div class="card mb-3"><div class="card-body d-grid gap-2">
        <button type="button" class="btn btn-success" onclick="collectAll();Joomla.submitbutton('concert.apply')">💾 Guardar</button>
        <button type="button" class="btn btn-primary" onclick="collectAll();Joomla.submitbutton('concert.save')">💾 Guardar y cerrar</button>
        <a href="<?= Route::_('index.php?option=com_concerts') ?>" class="btn btn-secondary">✕ Cancelar</a>
    </div></div>
</div>
</div>

<input type="hidden" name="task" value="" />
<?= HTMLHelper::_('form.token') ?>
</form>

<script>
function collectAll(){
    collectHL();
    collectPrices();
}
function collectPrices(){
    var rows=[];
    for(var i=0;i<5;i++){
        var l=document.querySelector('.cc-price-label[data-idx="'+i+'"]');
        var a=document.querySelector('.cc-price-amount[data-idx="'+i+'"]');
        var s=document.querySelector('.cc-price-soldout[data-idx="'+i+'"]');
        if(!l) continue;
        var label=l.value.trim(), amount=a.value.trim();
        if(label||amount) rows.push({label:label,amount:amount,sold_out:s.checked?1:0});
    }
    var hid=document.getElementById('jform_prices');
    if(hid) hid.value=JSON.stringify(rows);
}
function addHL(){
    var c=document.getElementById('headliners-container');
    var d=document.createElement('div'); d.className='input-group mb-1 headliner-row';
    d.innerHTML='<input type="text" class="form-control headliner-input" placeholder="Nombre del artista o banda"/><button type="button" class="btn btn-outline-danger" onclick="removeHL(this)">✕</button>';
    c.appendChild(d);
}
function removeHL(btn){btn.closest('.headliner-row').remove();}
function collectHL(){
    var arr=[];
    document.querySelectorAll('.headliner-input').forEach(function(i){var v=i.value.trim();if(v)arr.push(v);});
    document.getElementById('jform_headliners').value=JSON.stringify(arr);
}

document.addEventListener('DOMContentLoaded',function(){
    // Province toggle
    var cc=document.getElementById('jform_country_code');
    if(cc) cc.addEventListener('change',function(){
        var es=this.value==='ES';
        document.getElementById('prov-sel-wrap').style.display=es?'block':'none';
        document.getElementById('prov-txt-wrap').style.display=es?'none':'block';
    });
    // Collect dynamic fields on ANY form submit (toolbar buttons or sidebar)
    var form=document.getElementById('adminForm');
    if(form) form.addEventListener('submit',function(){collectAll();});
});
</script>
