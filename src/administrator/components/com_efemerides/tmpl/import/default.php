<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><strong><span class="icon-upload"></span> Importar efemérides desde CSV</strong></div>
                <div class="card-body">
                    <form action="<?php echo Route::_('index.php?option=com_efemerides&task=import.process'); ?>"
                          method="post" enctype="multipart/form-data" id="importForm">

                        <div class="mb-3">
                            <label class="form-label required">Archivo CSV *</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                            <div class="form-text">Tamaño máximo: <?php echo ini_get('upload_max_filesize'); ?></div>
                        </div>

                        <div class="alert alert-info">
                            <strong>Columnas esperadas (con cabecera):</strong>
                            <ul class="mb-0 mt-2">
                                <li><code>titulo</code> — Título descriptivo del evento</li>
                                <li><code>fecha_evento</code> — Fecha en formato <code>YYYY-MM-DD</code> o <code>DD/MM/YYYY</code></li>
                                <li><code>categoria</code> — Nombre de la categoría (Conciertos, Nacimientos, Defunciones, Lanzamientos de discos)</li>
                                <li><code>artista</code> — Nombre del artista o banda</li>
                                <li><code>descripcion</code> — Resumen breve</li>
                                <li><code>detalle</code> — Descripción completa</li>
                                <li><code>nombre_disco</code> — (Solo lanzamientos) Nombre del disco</li>
                                <li><code>lugar_concierto</code> — (Solo conciertos) Lugar del evento</li>
                                <li><code>asistentes</code> — (Solo conciertos) Número de asistentes</li>
                            </ul>
                        </div>

                        <div class="alert alert-secondary">
                            <strong>Ejemplo de fila CSV:</strong><br>
                            <code>Led Zeppelin en el O2 Arena,1975-03-12,Conciertos,Led Zeppelin,Concierto histórico en Londres,Descripción larga...,,,75000</code>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <span class="icon-upload"></span> Importar CSV
                            </button>
                            <a href="index.php?option=com_efemerides&view=cpanel" class="btn btn-secondary">Cancelar</a>
                        </div>

                        <?php echo HTMLHelper::_('form.token'); ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">CSV de muestra</div>
                <div class="card-body">
                    <p>Descarga esta plantilla como punto de partida:</p>
                    <a href="data:text/csv;charset=utf-8,titulo%2Cfecha_evento%2Ccategoria%2Cartista%2Cdescripcion%2Cdetalle%2Cnombre_disco%2Clug ar_concierto%2Casistentes%0ARolling+Stones+en+Madrid%2C1982-05-07%2CConciertos%2CThe+Rolling+Stones%2CConcierto+épico%2CDescripci%C3%B3n+completa%2C%2CEstadio+Bernabéu%2C100000"
                       download="efemerides_plantilla.csv" class="btn btn-outline-primary btn-sm">
                        <span class="icon-download"></span> Descargar plantilla CSV
                    </a>
                    <hr>
                    <h6>Notas importantes:</h6>
                    <ul>
                        <li>El separador puede ser <strong>coma</strong> o <strong>punto y coma</strong></li>
                        <li>Las efemérides de categoría <strong>Defunciones</strong> se añaden automáticamente al memorial</li>
                        <li>Las filas con fechas inválidas se omiten</li>
                        <li>La codificación debe ser <strong>UTF-8</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
