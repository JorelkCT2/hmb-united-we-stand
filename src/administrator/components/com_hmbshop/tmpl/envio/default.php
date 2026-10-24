<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Envio\HtmlView $this */
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$e = $this->item;
?>
<div class="container-fluid p-3" style="max-width:700px">
    <form method="post" action="<?php echo Route::_('index.php?option=com_hmbshop&task=tienda.guardarEnvio'); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
        <input type="hidden" name="id" value="<?php echo (int) ($e->id ?? 0); ?>">
        <div class="mb-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control" required value="<?php echo $h($e->nombre ?? ''); ?>" placeholder="Recogida en persona"></div>
        <div class="mb-3"><label class="form-label">Descripción</label><input name="descripcion" class="form-control" value="<?php echo $h($e->descripcion ?? ''); ?>"></div>
        <div class="row mb-3">
            <div class="col-md-4"><label class="form-label">Coste (€)</label><input name="coste" class="form-control" value="<?php echo $h($e->coste ?? '0'); ?>"></div>
            <div class="col-md-4"><label class="form-label">Orden</label><input type="number" name="ordering" class="form-control" value="<?php echo (int) ($e->ordering ?? 0); ?>"></div>
        </div>
        <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="pide_direccion" value="1" id="dir" <?php echo !empty($e->pide_direccion) ? 'checked' : ''; ?>><label class="form-check-label" for="dir">Pedir dirección de entrega</label></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="published" value="1" id="pub" <?php echo !$e || $e->published ? 'checked' : ''; ?>><label class="form-check-label" for="pub">Activa</label></div>
        <button class="btn btn-success">Guardar</button>
        <a class="btn btn-outline-secondary" href="<?php echo Route::_('index.php?option=com_hmbshop&view=envios'); ?>">Volver</a>
    </form>
</div>
