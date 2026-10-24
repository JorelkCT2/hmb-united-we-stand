<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Producto\HtmlView $this */
$h   = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$p   = $this->item;
$id  = (int) ($p->id ?? 0);
$tok = HTMLHelper::_('form.token');
$act = fn($t) => Route::_('index.php?option=com_hmbshop&task=tienda.' . $t);
$ctl = (int) ShopHelper::params()->get('controlar_stock', 0);
?>
<div class="container-fluid p-3" style="max-width:1000px">
    <form method="post" enctype="multipart/form-data" action="<?php echo $act('guardarProducto'); ?>">
        <?php echo $tok; ?>
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Datos del producto</h5>
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label">Nombre</label><input name="nombre" class="form-control" required value="<?php echo $h($p->nombre ?? ''); ?>"></div>
                <div class="col-md-4"><label class="form-label">SKU</label><input name="sku" class="form-control" value="<?php echo $h($p->sku ?? ''); ?>"></div>
                <div class="col-md-3"><label class="form-label">Precio (€, IVA incluido)</label><input name="precio" class="form-control" value="<?php echo $h(isset($p->precio) ? number_format((float) $p->precio, 2, '.', '') : ''); ?>"></div>
                <div class="col-md-3"><label class="form-label">IVA (%)</label><input name="iva_pct" class="form-control" value="<?php echo $h($p->iva_pct ?? ''); ?>" placeholder="<?php echo $h(ShopHelper::params()->get('iva_defecto', 21)); ?>"></div>
                <div class="col-md-3"><label class="form-label">Orden</label><input type="number" name="ordering" class="form-control" value="<?php echo (int) ($p->ordering ?? 0); ?>"></div>
                <div class="col-md-3 d-flex align-items-end gap-3">
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="published" value="1" id="pub" <?php echo !$p || $p->published ? 'checked' : ''; ?>><label class="form-check-label" for="pub">Publicado</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="destacado" value="1" id="dest" <?php echo !empty($p->destacado) ? 'checked' : ''; ?>><label class="form-check-label" for="dest">Destacado</label></div>
                </div>
                <div class="col-12"><label class="form-label">Categorías (la primera es la principal)</label>
                    <select name="categorias[]" class="form-select" multiple size="6">
                        <?php foreach ($this->categorias as $c) : ?>
                            <option value="<?php echo (int) $c->id; ?>" <?php echo $p && in_array((int) $c->id, $p->categorias, true) ? 'selected' : ''; ?>><?php echo str_repeat('— ', (int) $c->nivel) . $h($c->nombre); ?></option>
                        <?php endforeach; ?>
                    </select><div class="form-text">Ctrl+clic para elegir varias.</div></div>
                <div class="col-12"><label class="form-label">Descripción (HTML permitido)</label><textarea name="descripcion" rows="5" class="form-control"><?php echo $h($p->descripcion ?? ''); ?></textarea></div>
                <div class="col-12"><label class="form-label">Añadir foto (JPG, PNG o WebP, máx. 8 MB)</label><input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control"></div>
            </div>
        </div></div>

        <?php if ($p && $p->variantes) : ?>
        <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Variantes y stock</h5>
            <p class="text-muted small"><?php echo $ctl ? 'El stock se descuenta en cada pedido.' : 'El control de stock está desactivado (Ajustes): todas las variantes publicadas se pueden comprar.'; ?> El recargo viene de los valores de los ejes.</p>
            <table class="table table-sm align-middle">
                <thead><tr><th>Variante</th><th class="text-end">Precio</th><th style="width:130px">Stock</th><th class="text-center">Visible</th></tr></thead>
                <tbody>
                <?php foreach ($p->variantes as $v) : ?>
                    <tr>
                        <td><?php echo $v->etiqueta !== '' ? $h($v->etiqueta) : '<em>Única</em>'; ?></td>
                        <td class="text-end"><?php echo $h(ShopHelper::dinero((float) $p->precio + (float) $v->recargo)); ?></td>
                        <td><input type="number" min="0" class="form-control form-control-sm" name="stock[<?php echo (int) $v->id; ?>]" value="<?php echo (int) $v->stock; ?>"></td>
                        <td class="text-center"><input type="checkbox" class="form-check-input" name="vpub[<?php echo (int) $v->id; ?>]" value="1" <?php echo $v->published ? 'checked' : ''; ?>></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div>
        <?php endif; ?>

        <button class="btn btn-success btn-lg">Guardar producto</button>
        <a class="btn btn-outline-secondary btn-lg" href="<?php echo Route::_('index.php?option=com_hmbshop&view=productos'); ?>">Volver</a>
    </form>

    <?php if ($p) : ?>
    <hr class="my-4">

    <div class="card mb-3"><div class="card-body">
        <h5 class="card-title">Fotos</h5>
        <div class="d-flex flex-wrap gap-3">
            <?php foreach ($p->fotos as $f) : ?>
                <div class="text-center" style="width:130px">
                    <img src="<?php echo $h(Uri::root() . $f->fichero); ?>" alt="" style="width:130px;height:130px;object-fit:cover;border-radius:6px;<?php echo $f->principal ? 'outline:3px solid #cc2200' : ''; ?>">
                    <div class="d-flex gap-1 justify-content-center mt-1">
                        <?php if (!$f->principal) : ?>
                        <form method="post" action="<?php echo $act('fotoPrincipal'); ?>"><?php echo $tok; ?><input type="hidden" name="producto_id" value="<?php echo $id; ?>"><input type="hidden" name="foto_id" value="<?php echo (int) $f->id; ?>"><button class="btn btn-sm btn-outline-primary" title="Hacer principal">★</button></form>
                        <?php else : ?><span class="badge bg-danger align-self-center">Principal</span><?php endif; ?>
                        <form method="post" action="<?php echo $act('borrarFoto'); ?>" onsubmit="return confirm('¿Eliminar la foto?');"><?php echo $tok; ?><input type="hidden" name="producto_id" value="<?php echo $id; ?>"><input type="hidden" name="foto_id" value="<?php echo (int) $f->id; ?>"><button class="btn btn-sm btn-outline-danger" title="Eliminar">✕</button></form>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$p->fotos) : ?><span class="text-muted">Sin fotos. Súbelas con el campo de arriba.</span><?php endif; ?>
        </div>
    </div></div>

    <div class="card mb-3"><div class="card-body">
        <h5 class="card-title">Ejes de variación (talla, color…)</h5>
        <?php foreach ($p->ejes as $e) : ?>
            <div class="border rounded p-2 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <strong><?php echo $h($e->nombre); ?></strong>
                    <form method="post" action="<?php echo $act('borrarEje'); ?>" onsubmit="return confirm('¿Eliminar el eje y sus valores? Se regenerarán las variantes.');"><?php echo $tok; ?><input type="hidden" name="producto_id" value="<?php echo $id; ?>"><input type="hidden" name="eje_id" value="<?php echo (int) $e->id; ?>"><button class="btn btn-sm btn-outline-danger">Eliminar eje</button></form>
                </div>
                <div class="d-flex flex-wrap gap-2 my-2">
                    <?php foreach ($e->valores as $v) : ?>
                        <form method="post" action="<?php echo $act('borrarValor'); ?>" class="d-inline"><?php echo $tok; ?><input type="hidden" name="producto_id" value="<?php echo $id; ?>"><input type="hidden" name="valor_id" value="<?php echo (int) $v->id; ?>">
                            <button class="btn btn-sm btn-outline-secondary" title="Quitar valor"><?php echo $h($v->valor); ?><?php echo (float) $v->recargo ? ' (' . ((float) $v->recargo > 0 ? '+' : '') . $h(ShopHelper::dinero((float) $v->recargo)) . ')' : ''; ?> ✕</button></form>
                    <?php endforeach; ?>
                </div>
                <form method="post" action="<?php echo $act('anadirValores'); ?>" class="d-flex gap-2"><?php echo $tok; ?><input type="hidden" name="producto_id" value="<?php echo $id; ?>"><input type="hidden" name="eje_id" value="<?php echo (int) $e->id; ?>">
                    <input name="valores" class="form-control form-control-sm" placeholder="Añadir valores: 4XL:3, 5XL:3 (el número es el recargo en €)"><button class="btn btn-sm btn-secondary">Añadir</button></form>
            </div>
        <?php endforeach; ?>
        <form method="post" action="<?php echo $act('anadirEje'); ?>" class="row g-2 mt-2"><?php echo $tok; ?><input type="hidden" name="producto_id" value="<?php echo $id; ?>"><input type="hidden" name="eje_obligatorio" value="1">
            <div class="col-md-3"><input name="eje_nombre" class="form-control" placeholder="Eje: Talla"></div>
            <div class="col-md-7"><input name="eje_valores" class="form-control" placeholder="Valores separados por comas: S, M, L, XL, 3XL:3"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">+ Añadir eje</button></div>
        </form>
    </div></div>

    <form method="post" action="<?php echo $act('borrarProducto'); ?>&id=<?php echo $id; ?>" onsubmit="return confirm('¿Eliminar el producto, sus fotos y variantes? Los pedidos ya hechos se conservan.');">
        <?php echo $tok; ?><button class="btn btn-outline-danger">Eliminar producto</button>
    </form>
    <?php endif; ?>
</div>
