<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$sampleUrl = Uri::root() . 'media/com_metalmap/sample/festivales_europa_ejemplo.csv';
?>

<form action="<?= Route::_('index.php?option=com_metalmap'); ?>"
      method="post" enctype="multipart/form-data" id="import-form">

    <!-- TASK explícito en el body (sin sobreescribir con vacío) -->
    <input type="hidden" name="task" value="importer.upload">
    <input type="hidden" name="option" value="com_metalmap">

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">📥 Importar Festivales desde CSV</h3>
                </div>
                <div class="card-body">
                    <p>Sube un archivo CSV con festivales/conciertos. Se actualizarán los existentes (por alias) si marcas la casilla.</p>

                    <div class="mb-3">
                        <label for="csv_file" class="form-label">
                            <strong>Archivo CSV</strong>
                        </label>
                        <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt" required class="form-control" />
                    </div>

                    <div class="mb-3">
                        <label for="delimiter" class="form-label">Delimitador</label>
                        <select name="delimiter" id="delimiter" class="form-select">
                            <option value=",">, (coma)</option>
                            <option value=";">; (punto y coma)</option>
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="update_existing" id="update_existing" value="1" checked class="form-check-input" />
                        <label class="form-check-label" for="update_existing">
                            Actualizar eventos existentes (por alias)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg">
                        <span class="icon-upload" aria-hidden="true"></span>
                        🤘 Importar ahora
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="card-title mb-0">📋 Formato esperado</h4>
                </div>
                <div class="card-body">
                    <p>La primera línea debe ser la cabecera. El orden de columnas es flexible mientras la cabecera coincida.</p>
                    <pre class="bg-light p-2 small" style="white-space:pre-wrap"><code>name,event_type,start_date,end_date,city,country,country_code,latitude,longitude,website,description,bands</code></pre>

                    <h5 class="mt-3">Columnas obligatorias:</h5>
                    <ul>
                        <li><code>name</code> — nombre del festival</li>
                        <li><code>latitude</code> — entre -90 y 90</li>
                        <li><code>longitude</code> — entre -180 y 180</li>
                    </ul>

                    <h5>Columnas opcionales:</h5>
                    <ul class="small">
                        <li><code>event_type</code> — <em>festival</em> o <em>concert</em></li>
                        <li><code>country_code</code> — ISO alpha-2 (es, de, fi, no, uk...)</li>
                        <li><code>start_date</code> / <code>end_date</code> — formato YYYY-MM-DD</li>
                        <li><code>website</code>, <code>city</code>, <code>country</code>, <code>description</code>, <code>bands</code></li>
                    </ul>

                    <hr>
                    <a href="<?= $sampleUrl; ?>" class="btn btn-outline-primary" download>
                        <span class="icon-download" aria-hidden="true"></span>
                        Descargar CSV de ejemplo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?= HTMLHelper::_('form.token'); ?>
</form>
