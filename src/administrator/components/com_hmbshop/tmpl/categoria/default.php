<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Categoria\HtmlView $this */
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$c = $this->item;
?>
<div class="container-fluid p-3" style="max-width:760px">
    <form method="post" action="<?php echo Route::_('index.php?option=com_hmbshop&task=tienda.guardarCategoria'); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
        <input type="hidden" name="id" value="<?php echo (int) ($c->id ?? 0); ?>">
        <div class="mb-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control" required value="<?php echo $h($c->nombre ?? ''); ?>"></div>
        <div class="mb-3"><label class="form-label">Alias (opcional)</label><input name="alias" class="form-control" value="<?php echo $h($c->alias ?? ''); ?>"></div>
        <div class="mb-3"><label class="form-label">Categoría padre</label>
            <select name="parent_id" class="form-select">
                <option value="0">— Ninguna (nivel principal) —</option>
                <?php foreach ($this->categorias as $o) : if ($c && (int) $o->id === (int) $c->id) { continue; } ?>
                    <option value="<?php echo (int) $o->id; ?>" <?php echo $c && (int) $c->parent_id === (int) $o->id ? 'selected' : ''; ?>><?php echo str_repeat('— ', (int) $o->nivel) . $h($o->nombre); ?></option>
                <?php endforeach; ?>
            </select></div>
        <div class="mb-3"><label class="form-label">Descripción</label><textarea name="descripcion" rows="4" class="form-control"><?php echo $h($c->descripcion ?? ''); ?></textarea></div>
        <div class="row mb-3">
            <div class="col-md-4"><label class="form-label">Orden</label><input type="number" name="ordering" class="form-control" value="<?php echo (int) ($c->ordering ?? 0); ?>"></div>
            <div class="col-md-8 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="published" value="1" id="pub" <?php echo !$c || $c->published ? 'checked' : ''; ?>><label class="form-check-label" for="pub">Publicada</label></div></div>
        </div>
        <button class="btn btn-success">Guardar</button>
        <a class="btn btn-outline-secondary" href="<?php echo Route::_('index.php?option=com_hmbshop&view=categorias'); ?>">Volver</a>
    </form>
</div>
