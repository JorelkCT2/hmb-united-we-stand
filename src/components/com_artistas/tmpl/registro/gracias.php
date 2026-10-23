<?php defined('_JEXEC') or die; ?>
<div class="text-center py-5" style="max-width:600px;margin:0 auto;">
    <div style="font-size:5rem;">🎉</div>
    <h1 class="h2 fw-bold mt-3">¡Ficha enviada!</h1>
    <p class="lead text-muted mt-3">
        Hemos recibido tu registro. Nuestro equipo revisará la información
        y la publicará en breve. Te avisaremos por email.
    </p>
    <div class="mt-4 d-flex gap-3 justify-content-center">
        <a href="<?= \Joomla\CMS\Router\Route::_('index.php?option=com_artistas&view=artistas') ?>"
           class="btn btn-primary">Ver directorio de artistas</a>
        <a href="<?= \Joomla\CMS\Router\Route::_('index.php?option=com_artistas&view=registro') ?>"
           class="btn btn-outline-secondary">Registrar otro artista</a>
    </div>
</div>
