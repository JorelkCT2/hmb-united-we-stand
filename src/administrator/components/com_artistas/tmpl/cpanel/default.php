<?php defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
?>
<div class="container-fluid p-3">
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-white bg-primary h-100 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="fs-1">🎸</span>
                    <div><div class="fs-2 fw-bold lh-1"><?= $this->totalArtistas ?></div><div class="small opacity-75">Artistas totales</div></div>
                </div>
                <div class="card-footer bg-black bg-opacity-10 small"><?= $this->artistasPublicados ?> publicados · <?= $this->totalArtistas - $this->artistasPublicados ?> sin publicar</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-white h-100 shadow-sm <?= $this->artistasPendientes > 0 ? 'bg-danger' : 'bg-success' ?>">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="fs-1">⏳</span>
                    <div><div class="fs-2 fw-bold lh-1"><?= $this->artistasPendientes ?></div><div class="small opacity-75">Registros pendientes</div></div>
                </div>
                <div class="card-footer bg-black bg-opacity-10 small"><?= $this->artistasPendientes > 0 ? '¡Requieren revisión!' : 'Todo al día ✓' ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-white h-100 shadow-sm <?= $this->resenasPendientes > 0 ? 'bg-warning' : 'bg-success' ?>">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="fs-1">⭐</span>
                    <div><div class="fs-2 fw-bold lh-1"><?= $this->resenasPendientes ?></div><div class="small opacity-75">Reseñas pendientes</div></div>
                </div>
                <div class="card-footer bg-black bg-opacity-10 small"><?= $this->resenasAprobadas ?> aprobadas en total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-white h-100 shadow-sm <?= $this->presupuestosNuevos > 0 ? 'bg-danger' : 'bg-secondary' ?>">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="fs-1">💶</span>
                    <div><div class="fs-2 fw-bold lh-1"><?= $this->presupuestosNuevos ?></div><div class="small opacity-75">Solicitudes nuevas</div></div>
                </div>
                <div class="card-footer bg-black bg-opacity-10 small"><?= $this->presupuestosTotal ?> solicitudes en total</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header fw-semibold">⚡ Acciones rápidas</div>
        <div class="card-body d-flex flex-wrap gap-2">
            <a href="<?= Route::_('index.php?option=com_artistas&task=artista.add') ?>" class="btn btn-success">➕ Nuevo artista</a>
            <a href="<?= Route::_('index.php?option=com_artistas&view=artistas') ?>" class="btn btn-outline-primary">🎸 Todos los artistas</a>
            <a href="<?= Route::_('index.php?option=com_artistas&view=resenas') ?>" class="btn btn-outline-warning position-relative">
                ⭐ Moderar reseñas
                <?php if ($this->resenasPendientes > 0): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $this->resenasPendientes ?></span><?php endif; ?>
            </a>
            <a href="<?= Route::_('index.php?option=com_artistas&view=presupuestos') ?>" class="btn btn-outline-danger position-relative">
                💶 Ver presupuestos
                <?php if ($this->presupuestosNuevos > 0): ?><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $this->presupuestosNuevos ?></span><?php endif; ?>
            </a>
        </div>
    </div>

    <?php if (!empty($this->pendientesAprobacion)): ?>
    <div class="card mb-4 shadow-sm border-danger">
        <div class="card-header bg-danger text-white fw-semibold d-flex justify-content-between align-items-center">
            <span>⏳ Artistas pendientes de aprobación (<?= count($this->pendientesAprobacion) ?>)</span>
            <a href="<?= Route::_('index.php?option=com_artistas&view=artistas') ?>" class="btn btn-sm btn-light">Ver todos</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0" style="color:#e8e0d5">
                <thead>
                    <tr style="color:#d4bb8a;border-bottom:1px solid rgba(180,140,60,.3)">
                        <th>Nombre</th><th>Género</th><th>Email</th><th>Registrado</th><th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->pendientesAprobacion as $p): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,.06)">
                    <td><strong style="color:#f0e0c0"><?= htmlspecialchars($p->name) ?></strong></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p->genre) ?></span></td>
                    <td style="font-size:.85rem"><?= htmlspecialchars($p->email) ?></td>
                    <td><small style="color:#888"><?= date('d/m/Y H:i', strtotime($p->created)) ?></small></td>
                    <td class="text-center" style="white-space:nowrap">
                        <a href="<?= Route::_('index.php?option=com_artistas&task=artista.edit&id=' . $p->id) ?>" class="btn btn-sm btn-primary">✏️</a>
                        <a href="<?= Route::_('index.php?option=com_artistas&task=artistas.aprobar&id=' . $p->id . '&' . \Joomla\CMS\Session\Session::getFormToken() . '=1') ?>"
                           class="btn btn-sm btn-success ms-1"
                           onclick="return confirm('¿Aprobar a <?= addslashes(htmlspecialchars($p->name)) ?>?')">✓</a>
                        <a href="<?= Route::_('index.php?option=com_artistas&task=artistas.eliminar&id=' . $p->id . '&' . \Joomla\CMS\Session\Session::getFormToken() . '=1') ?>"
                           class="btn btn-sm btn-danger ms-1"
                           onclick="return confirm('¿Eliminar este registro?')">✕</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-success d-flex align-items-center mb-4">
        <span class="me-2 fs-4">✅</span> No hay artistas pendientes de aprobación. ¡Todo al día!
    </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span>🎸 Últimos artistas publicados</span>
                    <a href="<?= Route::_('index.php?option=com_artistas&view=artistas') ?>" class="btn btn-sm btn-outline-secondary">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr style="color:#d4bb8a"><th>Nombre</th><th>Género</th><th class="text-center">Estado</th></tr></thead>
                        <tbody>
                        <?php if (empty($this->ultimosArtistas)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No hay artistas publicados aún.</td></tr>
                        <?php else: ?>
                            <?php foreach ($this->ultimosArtistas as $a): ?>
                            <tr>
                                <td><a href="<?= Route::_('index.php?option=com_artistas&task=artista.edit&id=' . $a->id) ?>"><?= htmlspecialchars($a->name) ?></a><br><small class="text-muted">📍 <?= htmlspecialchars($a->location) ?></small></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($a->genre) ?></span></td>
                                <td class="text-center"><span class="badge bg-<?= $a->state ? 'success' : 'warning text-dark' ?>"><?= $a->state ? '✓ Publicado' : '⏸ Borrador' ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span>💶 Últimas solicitudes de presupuesto</span>
                    <a href="<?= Route::_('index.php?option=com_artistas&view=presupuestos') ?>" class="btn btn-sm btn-outline-secondary">Ver todas</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr style="color:#d4bb8a"><th>Cliente</th><th>Artista</th><th>Evento</th><th class="text-center">Estado</th></tr></thead>
                        <tbody>
                        <?php if (empty($this->ultimosPresupuestos)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No hay solicitudes aún.</td></tr>
                        <?php else: ?>
                            <?php foreach ($this->ultimosPresupuestos as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p->client_name) ?><br><small class="text-muted"><?= htmlspecialchars($p->client_email) ?></small></td>
                                <td><?= htmlspecialchars($p->artista_name) ?></td>
                                <td><small><?= htmlspecialchars($p->event_type) ?></small></td>
                                <td class="text-center">
                                    <?php $b = [0=>['danger','Nuevo'],1=>['info','En gestión'],2=>['success','Cerrado']][$p->state] ?? ['danger','Nuevo']; ?>
                                    <span class="badge bg-<?= $b[0] ?>"><?= $b[1] ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Noticias recientes -->
    <div class="row mt-4">
        <div class="col-12">
            <div style="background:#1a1a2e;border:1px solid rgba(100,120,200,.3);border-radius:8px;overflow:hidden">
                <div style="background:rgba(60,80,180,.5);padding:.7rem 1.2rem;display:flex;justify-content:space-between;align-items:center">
                    <strong style="color:#fff">📰 Noticias de grupos (<?= $this->totalNoticias ?? 0 ?>)</strong>
                    <a href="<?= Route::_('index.php?option=com_artistas&view=noticias') ?>"
                       style="background:rgba(255,255,255,.15);color:#fff;font-size:.78rem;padding:3px 12px;border-radius:4px;text-decoration:none">
                        Ver todas
                    </a>
                </div>
                <?php if (!empty($this->noticiasRecientes)): ?>
                <table style="width:100%;border-collapse:collapse;color:#ccc;font-size:.85rem">
                    <?php foreach ($this->noticiasRecientes as $nn): ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06)">
                        <td style="padding:.5rem 1rem">
                            <?= $nn->tipo === 'concierto' ? '🎤' : '💿' ?>
                            <strong style="color:#e8e0d5"><?= htmlspecialchars($nn->artista_name) ?></strong>
                            <span style="color:#888;margin-left:.5rem;font-size:.8rem"><?= htmlspecialchars($nn->titulo ?? '') ?></span>
                        </td>
                        <td style="padding:.5rem;color:#666;white-space:nowrap"><?= date('d/m/Y H:i', strtotime($nn->created)) ?></td>
                        <td style="padding:.5rem;white-space:nowrap">
                            <a href="<?= Route::_('index.php?option=com_artistas&view=noticia&id=' . $nn->id) ?>"
                               style="background:rgba(60,80,180,.4);color:#aac;font-size:.75rem;padding:2px 10px;border-radius:4px;text-decoration:none">
                                ✏️ Editar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p style="color:#666;padding:1rem;margin:0">No hay noticias publicadas aún.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Importar CSV -->
    <div class="row mt-4">
        <div class="col-12">
            <div style="background:#1a1a2e;border:1px solid rgba(100,120,200,.3);border-radius:8px;overflow:hidden">
                <div style="background:rgba(60,80,180,.5);padding:.7rem 1.2rem;display:flex;justify-content:space-between;align-items:center">
                    <strong style="color:#fff">📥 Importar artistas desde CSV</strong>
                    <a href="<?= Route::_('index.php?option=com_artistas&task=import.ejemplo') ?>"
                       style="background:rgba(255,255,255,.15);color:#fff;font-size:.78rem;padding:3px 12px;border-radius:4px;text-decoration:none">
                        ⬇️ Descargar CSV de ejemplo
                    </a>
                </div>
                <div style="padding:1rem">
                    <p style="color:#aaa;font-size:.85rem;margin-bottom:.8rem">
                        Importa artistas en bloque desde un archivo CSV (separado por <strong>punto y coma</strong>). 
                        Columnas: <code>name, genre, tipo_banda, provincia_origen, short_desc, description, email, phone, website, social_instagram, social_facebook, social_youtube, metadesc, metakey</code><br>
                        Valores de <code>tipo_banda</code>: <strong>solista</strong> · propias · versiones · ambas
                    </p>
                    <form action="<?= Route::_('index.php?option=com_artistas&task=import.csv') ?>" method="post" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                        <input type="file" name="csv_file" accept=".csv" class="form-control form-control-sm" style="max-width:300px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.2);color:#fff">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('¿Importar artistas desde este CSV? Los artistas se publicarán directamente.')">
                            📥 Importar
                        </button>
                        <?= HTMLHelper::_('form.token') ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
