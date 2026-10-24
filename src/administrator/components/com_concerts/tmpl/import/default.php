<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Concerts\Administrator\Helper\SyncHelper;

$tok = HTMLHelper::_('form.token');
$ruta = static fn(string $t): string => Route::_('index.php?option=com_concerts&task=' . $t);
$st = $this->stats;
// Sin colores propios: todo usa las clases de Bootstrap del administrador para que se lea en claro y en oscuro.
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body text-center">
        <div class="display-6 fw-bold"><?= (int) $st['proximos'] ?></div><div class="text-muted small">próximos publicados</div>
        <a class="small" href="<?= Route::_('index.php?option=com_concerts&view=concerts') ?>">Ver lista</a>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card h-100 <?= $st['pendientes'] ? 'border-warning' : '' ?>"><div class="card-body text-center">
        <div class="display-6 fw-bold"><?= (int) $st['pendientes'] ?></div><div class="text-muted small">pendientes de revisar</div>
        <?php if ($st['pendientes']) : ?>
        <form action="<?= $ruta('sources.publicarpendientes') ?>" method="post" class="mt-1" onsubmit="return confirm('¿Publicar todos los pendientes?')"><?= $tok ?>
            <button class="btn btn-sm btn-warning">Publicar todos</button>
            <a class="btn btn-sm btn-outline-secondary" href="<?= Route::_('index.php?option=com_concerts&view=concerts&filter_state=pend') ?>">Revisar</a>
        </form>
        <?php endif; ?>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card h-100 <?= $st['sin_geo'] ? 'border-info' : '' ?>"><div class="card-body text-center">
        <div class="display-6 fw-bold"><?= (int) $st['sin_geo'] ?></div><div class="text-muted small">sin situar en el mapa<?= $st['geo_fallo'] ? ' (' . (int) $st['geo_fallo'] . ' no encontrados)' : '' ?></div>
        <?php if ($st['sin_geo']) : ?>
        <form action="<?= $ruta('sources.geo') ?>" method="post" class="mt-1"><?= $tok ?><button class="btn btn-sm btn-info">📍 Situar ahora</button></form>
        <?php endif; ?>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body text-center">
        <div class="display-6 fw-bold"><?= (int) $st['sin_cartel'] ?></div><div class="text-muted small">próximos sin cartel</div>
        <a class="small" href="<?= Route::_('index.php?option=com_concerts&view=concerts&filter_cartel=0&filter_fecha=proximos') ?>">Verlos</a>
    </div></div></div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <strong>🔄 Fuentes automáticas</strong>
        <form action="<?= $ruta('sources.runall') ?>" method="post" class="d-inline"><?= $tok ?>
            <button class="btn btn-success btn-sm">⟳ Actualizar todas ahora</button>
        </form>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Cada fuente se lee sin gastar IA: calendarios <code>.ics</code>, páginas con datos estructurados (schema.org), la API oficial de Ticketmaster o un CSV publicado.
            Lo nuevo entra como <strong>pendiente de revisar</strong> (salvo que marques «publicar automáticamente»), los duplicados se detectan solos y los eventos sin coordenadas se sitúan en el mapa.
        </p>
        <?php if (!$this->fuentes) : ?>
            <div class="alert alert-info">Todavía no hay fuentes. Añade la primera abajo (por ejemplo Ticketmaster para España).</div>
        <?php else : ?>
        <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>Fuente</th><th>Tipo</th><th>Última lectura</th><th class="text-center">Nuevos</th><th class="text-center">Actual.</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($this->fuentes as $f) : ?>
            <tr class="<?= $f->active ? '' : 'text-muted' ?>">
                <td><strong><?= htmlspecialchars($f->name) ?></strong><br><small class="text-muted text-break"><?= htmlspecialchars($f->url ?: '—') ?></small>
                    <?php if ($f->auto_publish) : ?><span class="badge bg-info">publica solo</span><?php endif; ?></td>
                <td><?= htmlspecialchars(SyncHelper::TIPOS_FUENTE[$f->type] ?? $f->type) ?></td>
                <td><?= $f->last_run ? htmlspecialchars($f->last_run) : 'nunca' ?><br><small class="text-muted"><?= htmlspecialchars((string) $f->last_status) ?></small></td>
                <td class="text-center"><?= (int) $f->last_new ?></td>
                <td class="text-center"><?= (int) $f->last_updated ?></td>
                <td class="text-end text-nowrap">
                    <form action="<?= $ruta('sources.run') ?>" method="post" class="d-inline"><?= $tok ?><input type="hidden" name="id" value="<?= (int) $f->id ?>"><button class="btn btn-sm btn-primary">Leer</button></form>
                    <form action="<?= $ruta('sources.toggle') ?>" method="post" class="d-inline"><?= $tok ?><input type="hidden" name="id" value="<?= (int) $f->id ?>"><button class="btn btn-sm btn-outline-secondary"><?= $f->active ? 'Pausar' : 'Activar' ?></button></form>
                    <form action="<?= $ruta('sources.delete') ?>" method="post" class="d-inline" onsubmit="return confirm('¿Quitar esta fuente?')"><?= $tok ?><input type="hidden" name="id" value="<?= (int) $f->id ?>"><button class="btn btn-sm btn-outline-danger">✕</button></form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>

        <details class="mt-3">
            <summary class="fw-semibold">➕ Añadir una fuente</summary>
            <form action="<?= $ruta('sources.save') ?>" method="post" class="row g-2 mt-2"><?= $tok ?>
                <div class="col-md-4"><label class="form-label">Nombre</label><input class="form-control" name="name" placeholder="Ticketmaster España" required></div>
                <div class="col-md-4"><label class="form-label">Tipo</label>
                    <select class="form-select" name="type" id="srcType">
                        <?php foreach (SyncHelper::TIPOS_FUENTE as $k => $v) : ?><option value="<?= $k ?>"><?= htmlspecialchars($v) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="col-md-4"><label class="form-label">Dirección (no hace falta en Ticketmaster)</label><input class="form-control" name="url" placeholder="https://…"></div>
                <div class="col-md-2"><label class="form-label">País</label><input class="form-control" name="p_pais" value="ES" maxlength="2"></div>
                <div class="col-md-4"><label class="form-label">Géneros (Ticketmaster)</label><input class="form-control" name="p_generos" value="metal,hard rock"></div>
                <div class="col-md-3"><label class="form-label">Palabra clave (opcional)</label><input class="form-control" name="p_keyword"></div>
                <div class="col-md-3"><label class="form-label">Meses hacia delante</label><input class="form-control" type="number" name="p_meses" value="12" min="1" max="24"></div>
                <div class="col-md-6"><label class="form-label">Seguir enlaces que contengan… (listados sin datos propios)</label><input class="form-control" name="p_seguir" placeholder="/evento/|/concierto/"></div>
                <div class="col-md-3"><label class="form-label">Solo eventos de (país)</label><input class="form-control" name="p_solo" maxlength="2" placeholder="ES"></div>
                <div class="col-md-6"><label class="form-label">Palabras de estilo extra (opcional)</label><input class="form-control" name="p_palabras" placeholder="folk metal, gothic, nwobhm"></div>
                <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="p_filtro" value="1" id="pf" checked><label class="form-check-label" for="pf">Solo metal / rock duro (webs generalistas)</label></div></div>
                <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="auto_publish" value="1" id="ap"><label class="form-check-label" for="ap">Publicar automáticamente lo nuevo</label></div></div>
                <div class="col-12"><button class="btn btn-success">Guardar fuente</button>
                    <?php if (!$this->hayClaveTm) : ?><span class="text-warning small ms-2">Para Ticketmaster falta la clave gratuita: <a href="<?= Route::_('index.php?option=com_config&view=component&component=com_concerts') ?>">Opciones</a>.</span><?php endif; ?></div>
            </form>
        </details>

        <div class="mt-3 small">
            <strong>Actualización sin tocar nada:</strong>
            <?php if ($this->cronUrl) : ?>
                programa en tu hosting una tarea (cron) diaria que abra esta dirección:<br>
                <code class="text-break user-select-all"><?= htmlspecialchars($this->cronUrl) ?></code>
            <?php else : ?>
                define una «clave de la tarea programada» en <a href="<?= Route::_('index.php?option=com_config&view=component&component=com_concerts') ?>">Opciones</a> y aquí aparecerá la dirección que hay que programar.
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><strong>🌐 Importar desde una dirección web</strong></div>
            <div class="card-body">
                <form action="<?= $ruta('sources.url') ?>" method="post"><?= $tok ?>
                    <label class="form-label">Página de un evento, listado o calendario (.ics)</label>
                    <input class="form-control mb-2" name="url" placeholder="https://…" required>
                    <label class="form-label">Si es un listado, seguir enlaces que contengan (opcional)</label>
                    <input class="form-control mb-2" name="seguir" placeholder="/evento/">
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="auto_publish" value="1" id="ap2"><label class="form-check-label" for="ap2">Publicar directamente</label></div>
                    <button class="btn btn-primary">Importar</button>
                </form>
                <p class="text-muted small mt-3 mb-0">Funciona con las webs que publican sus eventos en formato estándar (schema.org), que son la mayoría de festivales y salas modernas.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><strong>📂 Importar desde CSV</strong></div>
            <div class="card-body">
                <form action="<?= $ruta('import.upload') ?>" method="post" enctype="multipart/form-data"><?= $tok ?>
                    <input type="file" class="form-control mb-2" name="csv_file" accept=".csv,text/csv" required>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><select name="delimiter" class="form-select">
                            <option value="auto">Delimitador automático</option><option value=",">Coma ( , )</option><option value=";">Punto y coma ( ; )</option><option value="&#9;">Tabulador</option></select></div>
                        <div class="col-6"><select name="publicar" class="form-select"><option value="1">Publicar directamente</option><option value="0">Dejar pendiente de revisar</option></select></div>
                    </div>
                    <button class="btn btn-success">⬆ Importar CSV</button>
                    <a class="btn btn-outline-secondary" href="<?= $ruta('import.template') ?>">📥 Plantilla</a>
                </form>
                <p class="text-muted small mt-3 mb-0">Columnas: <code>title, headliners, support_bands, date_start, date_end, venue, city, province, country_code, ticket_url, website_url, poster_url, description</code>
                    (y opcionalmente <code>latitude, longitude, event_type</code>). Lo que ya existe se detecta y no se duplica.</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong>🧬 Posibles duplicados</strong> <span class="badge bg-secondary"><?= count($this->duplicados) ?></span></div>
    <div class="card-body">
        <?php if (!$this->duplicados) : ?>
            <p class="text-muted mb-0">No hay eventos futuros que parezcan repetidos. 👍</p>
        <?php else : ?>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead><tr><th>Fecha</th><th>Ciudad</th><th>Evento A</th><th>Evento B</th></tr></thead>
            <tbody>
            <?php foreach ($this->duplicados as [$a, $b]) : ?>
            <tr>
                <td class="text-nowrap"><?= htmlspecialchars($a->date_start) ?></td>
                <td><?= htmlspecialchars($a->city) ?></td>
                <?php foreach ([$a, $b] as $x) : ?>
                <td>
                    <a href="<?= Route::_('index.php?option=com_concerts&task=concert.edit&id=' . (int) $x->id) ?>"><?= htmlspecialchars($x->title) ?></a>
                    <small class="text-muted">(#<?= (int) $x->id ?>, <?= htmlspecialchars((string) $x->source) ?><?= (int) $x->state === 0 ? ', sin publicar' : '' ?>)</small>
                    <?php if ((int) $x->state !== 0) : ?>
                    <form action="<?= $ruta('sources.despublicar') ?>" method="post" class="d-inline"><?= $tok ?><input type="hidden" name="id" value="<?= (int) $x->id ?>"><button class="btn btn-sm btn-outline-warning py-0">Quitar</button></form>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
