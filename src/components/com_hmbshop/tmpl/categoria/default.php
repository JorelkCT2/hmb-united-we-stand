<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use HeavyMetalBrothers\Component\Hmbshop\Site\Helper\Vista;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Site\View\Categoria\HtmlView $this */
$h      = fn($s) => Vista::h($s);
$c      = $this->categoria;
$titulo = $c->titulo_largo !== '' ? $c->titulo_largo : $c->nombre;
$arriba = $this->padre ? [$this->padre->nombre, Vista::urlCategoria($this->padre)] : ['Categorías', Vista::urlTienda()];
$iconos = ['🤘', '💿', '🎸', '👕', '🔥', '⚡', '💀', '🎵'];
$tok    = HTMLHelper::_('form.token');
?>
<div id="ph-pc-category-box" class="pc-view pc-category-view"><div class="ph-top"><a class="btn btn-secondary" title="<?php echo $h($arriba[0]); ?>" href="<?php echo $h($arriba[1]); ?>"> <?php echo $h($arriba[0]); ?></a></div><h1 class="ph-header" data-image-meta="<?php echo $h($c->imagen); ?>"><?php echo $h($titulo); ?></h1>
<?php if (trim(strip_tags((string) $c->descripcion)) !== '') : ?><div class="ph-desc"><?php echo $c->descripcion; ?></div><?php endif; ?>

<?php if ($this->hijas) : ?>
<div class="hmb-subcats-wrap">
<?php foreach ($this->hijas as $j => $s) : $img = !empty($s->imagen) ? Vista::foto($s->imagen) : ''; ?>
<a href="<?php echo $h(Vista::urlCategoria($s)); ?>" class="hmb-subcat-card" style="--delay:<?php echo $j * 100; ?>ms">
    <div class="hmb-subcat-bg">
        <?php if ($img) : ?>
        <div class="hmb-subcat-img" style="background-image:url('<?php echo $h($img); ?>')"></div>
        <?php else : ?>
        <div class="hmb-subcat-img hmb-subcat-noimg"><?php echo $iconos[$j % count($iconos)]; ?></div>
        <?php endif; ?>
        <div class="hmb-subcat-overlay"></div>
    </div>
    <div class="hmb-subcat-content">
        <h3 class="hmb-subcat-title"><?php echo $h($s->nombre); ?></h3>
        <span class="hmb-subcat-cta">Ver colección →</span>
    </div>
</a>
<?php endforeach; ?>
</div>
<div class="ph-hr"></div>
<?php endif; ?>

<div class="ph-cb"></div>
<div id="phItemsBox">
<?php if ($this->productos) : ?>
<div id="phItems" class="ph-items list"><div class="row ph-row-flex list">
<?php foreach ($this->productos as $p) :
    $url     = Vista::urlProducto($p, (int) $c->id);
    $foto    = Vista::foto($p->foto, 'm');
    $elegir  = Vista::exigeOpciones((int) $p->id);
?>
<div class="row-item col-12 col-sm-12 col-md-12 list"><div class="ph-item-box list"><div class="thumbnail ph-thumbnail ph-thumbnail-c ph-item list "><div class="ph-item-content list"><div class="row jf_ph_cat_list ph-item-content-row list">
<div class="row-item ph-category-item-image col-12 col-sm-1 col-md-2 list"><a href="<?php echo $h($url); ?>"><div class="phIBoxOH list"><div class="phIBox "><?php if ($foto !== '') : ?><img src="<?php echo $h($foto); ?>" alt="<?php echo $h($p->nombre); ?>" class="img-responsive ph-image phIL-not-active" loading="lazy" /><?php endif; ?></div></div></a></div>
<div class="row-item ph-category-item-title col-12 col-sm-5 col-md-5 list"><div class="ph-item-action-box ph-caption list"><h3 class="ph-product-header list"><?php echo $h($p->nombre); ?></h3></div></div>
<div class="col-12 col-sm-5 col-md-5 ph-category-item-body list"><div id="phItemPriceBoxCategory<?php echo (int) $p->id; ?>"> <div class="ph-category-price-box"> <div class="ph-price-txt ph-price-brutto-txt ph-standard-txt">Precio</div><div class="ph-price-brutto ph-standard"><?php echo $h(ShopHelper::dinero((float) $p->precio)); ?></div><div class="ph-cb"></div> </div> </div> <div class="ph-cb"></div> <div class="ph-item-action-box ph-caption ph-category-action-box-buttons list"><div class="ph-category-action-buttons list">
<form class="phItemCartBoxForm phjAddToCart phjCategory" action="<?php echo $h(Route::_('index.php?option=com_hmbshop&task=carrito.anadir', false)); ?>" method="post">
<input type="hidden" name="producto_id" value="<?php echo (int) $p->id; ?>"><input type="hidden" name="cantidad" value="1"><?php echo $tok; ?>
<div class="ph-pull-left ph-button-view-product-box"> <a href="<?php echo $h($url); ?>" class="btn btn-primary btn-sm ph-btn" role="button"> Ver producto</a></div>
<?php if ($elegir) : ?>
<div class="ph-pull-right ph-button-choose-variant-box"><a href="<?php echo $h($url); ?>" class="btn btn-primary btn-sm ph-btn" role="button"> Elegir variante</a></div>
<?php else : ?>
<div class="ph-pull-right ph-button-add-to-cart-box"><button type="submit" class="btn btn-primary btn-sm ph-btn"> Añadir al Carrito</button></div>
<?php endif; ?>
<div class="ph-cb"></div></form></div><div class="ph-cb"></div></div><div class="ph-cb"></div></div></div></div></div></div></div>
<?php endforeach; ?>
</div> <div class="ph-cb"></div></div><div class="ph-cb"></div>
<?php endif; ?>
</div>
</div>
