<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Administrator\View\Productos\HtmlView $this */
$h   = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$inp = Factory::getApplication()->getInput();
?>
<div class="container-fluid p-3">
    <form method="get" action="<?php echo Route::_('index.php'); ?>" class="row g-2 mb-3">
        <input type="hidden" name="option" value="com_hmbshop">
        <input type="hidden" name="view" value="productos">
        <div class="col-md-4"><input type="search" name="buscar" class="form-control" placeholder="Buscar producto…" value="<?php echo $h($inp->getString('buscar', '')); ?>"></div>
        <div class="col-md-3">
            <select name="categoria" class="form-select">
                <option value="0">Todas las categorías</option>
                <?php foreach ($this->categorias as $c) : ?>
                    <option value="<?php echo (int) $c->id; ?>" <?php echo $inp->getInt('categoria', 0) === (int) $c->id ? 'selected' : ''; ?>><?php echo str_repeat('— ', (int) $c->nivel) . $h($c->nombre); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-secondary">Filtrar</button></div>
        <div class="col text-end"><a class="btn btn-success" href="<?php echo Route::_('index.php?option=com_hmbshop&view=producto'); ?>">+ Nuevo producto</a></div>
    </form>

    <table class="table table-striped align-middle">
        <thead><tr><th style="width:70px"></th><th>Producto</th><th>Categoría</th><th class="text-end">Precio</th><th class="text-center">Variantes</th><th class="text-center">Estado</th></tr></thead>
        <tbody>
        <?php foreach ($this->items as $p) : ?>
            <tr>
                <td><?php if ($p->foto) : ?><img src="<?php echo $h(Uri::root() . $p->foto); ?>" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:4px"><?php endif; ?></td>
                <td><a href="<?php echo Route::_('index.php?option=com_hmbshop&view=producto&id=' . (int) $p->id); ?>"><strong><?php echo $h($p->nombre); ?></strong></a><?php if ($p->destacado) : ?> <span class="badge bg-warning text-dark">Destacado</span><?php endif; ?></td>
                <td><?php echo $h($p->categoria_nombre); ?></td>
                <td class="text-end"><?php echo $h(ShopHelper::dinero((float) $p->precio)); ?></td>
                <td class="text-center"><?php echo (int) $p->n_variantes; ?></td>
                <td class="text-center"><?php echo $p->published ? '<span class="badge bg-success">Publicado</span>' : '<span class="badge bg-secondary">Oculto</span>'; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$this->items) : ?><tr><td colspan="6" class="text-center text-muted p-4">No hay productos.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
