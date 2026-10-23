<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>
<div class="row g-4">
<div class="col-lg-7">
<div class="card">
    <div class="card-header fw-bold">📂 Importar conciertos desde CSV</div>
    <div class="card-body">
        <form action="<?= Route::_('index.php?option=com_concerts&task=import.upload') ?>"
              method="post" enctype="multipart/form-data" id="importForm">

            <div id="dropZone"
                 style="border:2px dashed #6c757d;border-radius:10px;background:#f8f9fa;
                        padding:2.5rem;text-align:center;cursor:pointer;transition:.2s"
                 onclick="document.getElementById('csv_file').click()">
                <div style="font-size:3rem;line-height:1">📄</div>
                <p class="fw-bold mb-1 mt-2">Arrastra aquí tu CSV o haz clic para seleccionar</p>
                <p id="fileName" class="text-muted small mb-0">Formato aceptado: .csv</p>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" class="d-none"
                       onchange="showFile(this)" />
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Delimitador</label>
                    <select name="delimiter" class="form-select">
                        <option value="auto">Detección automática (recomendado)</option>
                        <option value=",">Coma ( , )</option>
                        <option value=";">Punto y coma ( ; )</option>
                        <option value="	">Tabulador</option>
                    </select>
                </div>
                <div class="col-md-7 d-flex align-items-end">
                    <div class="alert alert-info mb-0 py-2 small w-100">
                        💡 La primera fila debe contener los nombres de columna exactos.
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" id="btnImport" class="btn btn-success" disabled>
                    ⬆️ Importar CSV
                </button>
                <a href="<?= Route::_('index.php?option=com_concerts&task=import.template') ?>"
                   class="btn btn-outline-secondary">
                    📥 Descargar plantilla CSV
                </a>
            </div>
            <?= HTMLHelper::_('form.token') ?>
        </form>
    </div>
</div>
</div>

<div class="col-lg-5">
<div class="card">
    <div class="card-header fw-bold">📋 Columnas del CSV</div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped mb-0">
            <thead><tr><th>Columna</th><th>Req.</th><th>Descripción</th></tr></thead>
            <tbody>
            <?php
            $cols = [
                ['title',         '✅', 'Título del evento'],
                ['headliners',    '✅', 'Bandas principales separadas por |'],
                ['support_bands', '—',  'Teloneras separadas por coma'],
                ['genre_ids',     '—',  'IDs de géneros separados por coma'],
                ['date_start',    '✅', 'Fecha inicio YYYY-MM-DD'],
                ['date_end',      '—',  'Fecha fin (solo festivales)'],
                ['concert_time',  '—',  'Hora inicio HH:MM'],
                ['doors_time',    '—',  'Apertura de puertas HH:MM'],
                ['venue',         '—',  'Recinto / sala'],
                ['city',          '—',  'Ciudad'],
                ['province',      '—',  'Provincia'],
                ['country_code',  '—',  'ES, DE, FR… (defecto ES)'],
                ['address',       '—',  'Dirección completa'],
                ['prices',        '—',  'Ej: Abono:235€|Día1:99€'],
                ['ticket_url',    '—',  'URL compra entradas'],
                ['website_url',   '—',  'Web oficial del evento'],
                ['poster_url',    '—',  'URL cartel (PNG o JPG)'],
                ['description',   '—',  'Descripción / notas'],
            ];
            foreach ($cols as [$col,$req,$desc]):
            ?>
            <tr>
                <td><code class="text-nowrap"><?= $col ?></code></td>
                <td><?= $req ?></td>
                <td class="text-muted small"><?= $desc ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="p-2 small text-muted border-top">
            <strong>Precios:</strong> formato <code>Etiqueta:Precio|Etiqueta2:Precio2</code><br>
            Ej: <code>Abono:235€|Viernes:99€|Sábado:99€</code><br>
            <strong>genre_ids:</strong> consulta los IDs en la tabla <code>mujg_artistas_generos</code>
        </div>
    </div>
</div>
</div>
</div>

<script>
function showFile(input) {
    if (!input.files.length) return;
    document.getElementById('fileName').textContent = '✅ ' + input.files[0].name;
    document.getElementById('btnImport').disabled = false;
    document.getElementById('dropZone').style.cssText += ';background:#e8f5e9;border-color:#4caf50';
}
var dz = document.getElementById('dropZone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.background='#e3f2fd'; });
dz.addEventListener('dragleave', () => { dz.style.background='#f8f9fa'; });
dz.addEventListener('drop', e => {
    e.preventDefault();
    var fi = document.getElementById('csv_file');
    fi.files = e.dataTransfer.files;
    showFile(fi);
});
</script>
