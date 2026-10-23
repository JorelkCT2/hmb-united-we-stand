<?php defined('_JEXEC') or die; ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1><span class="icon-calendar"></span> Efemérides Musicales — Panel de Control</h1>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h2 class="display-4 text-primary"><?php echo $this->totalEfemerides; ?></h2>
                    <p class="card-text">Efemérides publicadas</p>
                    <a href="index.php?option=com_efemerides&view=efemerides" class="btn btn-primary btn-sm">Ver todas</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h2 class="display-4 text-success"><?php echo $this->efemerideHoy; ?></h2>
                    <p class="card-text">Efemérides hoy (<?php echo date('d/m'); ?>)</p>
                    <a href="index.php?option=com_efemerides&view=efemerides" class="btn btn-success btn-sm">Ver</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h2 class="display-4 text-info"><?php echo $this->totalCategorias; ?></h2>
                    <p class="card-text">Categorías activas</p>
                    <a href="index.php?option=com_efemerides&view=categorias" class="btn btn-info btn-sm">Gestionar</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h2 class="display-4 text-danger"><?php echo $this->totalFallecidos; ?></h2>
                    <p class="card-text">Artistas fallecidos</p>
                    <a href="index.php?option=com_efemerides&view=fallecidos" class="btn btn-danger btn-sm">Ver memorial</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><strong>Acciones rápidas</strong></div>
                <div class="card-body d-flex gap-2 flex-wrap">
                    <a href="index.php?option=com_efemerides&view=efemerid&layout=edit" class="btn btn-outline-primary">
                        <span class="icon-plus-2"></span> Nueva efeméride
                    </a>
                    <a href="index.php?option=com_efemerides&view=categorias" class="btn btn-outline-secondary">
                        <span class="icon-tags-2"></span> Gestionar categorías
                    </a>
                    <a href="index.php?option=com_efemerides&view=import" class="btn btn-outline-success">
                        <span class="icon-upload"></span> Importar CSV
                    </a>
                    <a href="index.php?option=com_efemerides&view=fallecidos" class="btn btn-outline-danger">
                        <span class="icon-star"></span> Memorial de artistas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>Formato CSV para importación:</strong><br>
                <code>titulo, fecha_evento, categoria, artista, descripcion, detalle, nombre_disco, lugar_concierto, asistentes</code><br>
                <small>Fecha: YYYY-MM-DD o DD/MM/YYYY · Separador: coma o punto y coma · Categoría: nombre de la categoría existente</small>
            </div>
        </div>
    </div>
</div>
