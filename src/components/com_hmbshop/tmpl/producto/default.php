<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use HeavyMetalBrothers\Component\Hmbshop\Site\Helper\Vista;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Site\View\Producto\HtmlView $this */
$h      = fn($s) => Vista::h($s);
$p      = $this->producto;
$cat    = $this->categoria;
$titulo = $p->titulo_largo !== '' ? $p->titulo_largo : $p->nombre;
$ejes   = array_values(array_filter($p->ejes, fn($e) => (bool) $e->valores));
$ctl    = (int) ShopHelper::params()->get('controlar_stock', 0);
$hay    = !$ctl || (bool) array_filter($p->variantes, fn($v) => (int) $v->published && (int) $v->stock > 0);
$fotos  = $p->fotos;
$arriba = $cat ? [$cat->nombre, Vista::urlCategoria($cat)] : ['Tienda', Vista::urlTienda()];
$id     = (int) $p->id;
?>
<div id="ph-pc-item-box" class="pc-view pc-item-view"><div class="ph-top"><a class="btn btn-secondary" title="<?php echo $h($arriba[0]); ?>" href="<?php echo $h($arriba[1]); ?>"> <?php echo $h($arriba[0]); ?></a></div>
<div class="row"><div id="phImageBox" class="col-12 col-sm-5 col-md-5 ph-item-view-image-box"><div class="pc-photoswipe"> <div class="ph-item-image-full-box ">
<div class="ph-label-box"> </div>
<?php if ($fotos) : $g = Vista::foto($fotos[0]->fichero, 'l'); ?>
<figure> <a href="<?php echo $h($g); ?>" class="phjProductHrefVItemP<?php echo $id; ?> phImageFullHref phImageGalleryHref pc-photoswipe-button" data-href="<?php echo $h($g); ?>" data-img-title="<?php echo $h($p->nombre); ?>" id="pgImg<?php echo $id; ?>" itemprop="contentUrl"> <img src="<?php echo $h($g); ?>" alt="<?php echo $h($p->nombre); ?>" class="img-responsive img-thumbnail ph-image-full phImageFull phImageGallery phjProductImageVItemP<?php echo $id; ?>" data-image="<?php echo $h($g); ?>" data-image-large="<?php echo $h($g); ?>" loading="lazy"/> </a> </figure>
<?php endif; ?>
</div>
<?php if (count($fotos) > 1) : ?>
<div class="row ph-item-image-add-box">
<?php foreach (array_slice($fotos, 1) as $k => $f) : $gl = Vista::foto($f->fichero, 'l'); $gs = Vista::foto($f->fichero, 's'); ?>
<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 ph-item-image-box"> <figure> <a href="<?php echo $h($gl); ?>" class="phjProductHrefVItemP<?php echo $id; ?> phImageFullHref phImageAdditionalHref pc-photoswipe-button" data-href="<?php echo $h($gl); ?>" data-img-title="<?php echo $h($p->nombre); ?>" id="pgImgAdd<?php echo $id; ?>Add<?php echo $k + 1; ?>" itemprop="contentUrl"> <img src="<?php echo $h($gs); ?>" alt="<?php echo $h($p->nombre); ?>" class="img-responsive img-thumbnail ph-image-full phImageAdditional " data-image-large="<?php echo $h($gl); ?>" loading="lazy"/> </a> </figure> </div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div></div>

<div class="col-12 col-sm-7 col-md-7 ph-item-view-data-box"><div class="ph-item-price-panel phItemPricePanel"><h1 class="ph-header" ><?php echo $h($titulo); ?></h1><div id="phItemPriceBoxItem<?php echo $id; ?>"> <div class="ph-item-price-box"> <div class="ph-price-txt ph-price-brutto-txt ph-standard-txt">Precio</div><div class="ph-price-brutto ph-standard"><?php echo $h(ShopHelper::dinero((float) $p->precio)); ?></div><div class="ph-cb"></div> </div> </div> <div class="ph-cb"></div>
<?php if ($p->fabricante !== '') : ?><div class="ph-item-manufacturer-box"><div class="ph-manufacturer-txt">Fabricante:</div><div class="ph-manufacturer"><?php echo $h($p->fabricante); ?></div></div><div class="ph-cb"></div><?php endif; ?>
<div class="ph-item-stock-box" id="phItemStockBoxItem<?php echo $id; ?>"> <div class="ph-item-stock-box"> <div class="ph-stock-txt">Disponibilidad:</div> <div class="ph-stock <?php echo $hay ? 'pc-status-en-stock' : 'pc-status-agotado'; ?>"><?php echo $hay ? 'En stock' : 'Agotado'; ?></div> <div class="ph-cb"></div> </div></div>
<div id="phItemIdBoxItem<?php echo $id; ?>"><div class="ph-item-id-box"></div></div>

<?php if ($hay) : ?>
<form id="phCartAddToCartButton<?php echo $id; ?>" class="phItemCartBoxForm phjAddToCart phjItem phjAddToCartVItemP<?php echo $id; ?> form-inline" action="<?php echo $h(Route::_('index.php?option=com_hmbshop&task=carrito.anadir', false)); ?>" method="post">
<?php if ($ejes) : ?>
<div class="ph-item-attributes-box" id="phItemAttributesBox"><h4 class="ph-available-options-title">Opciones Disponibles</h4>
<?php foreach ($ejes as $e) : $oblig = (int) $e->obligatorio === 1; ?>
<div class="ph-attribute-title"><?php echo $h($e->nombre); ?><?php if ($oblig) : ?> <span class="ph-req">*</span><?php endif; ?></div>
<div id="phItemBoxAttributeVItemP<?php echo $id; ?>A<?php echo (int) $e->id; ?>"><select id="phItemAttributeVItemP<?php echo $id; ?>A<?php echo (int) $e->id; ?>" class="form-select chosen-select ph-item-input-set-attributes phjItem phjProductAttribute" <?php echo $oblig ? 'required="" aria-required="true"' : ''; ?> name="eje[<?php echo (int) $e->id; ?>]" data-required="<?php echo $oblig ? 1 : 0; ?>">
<?php if (!$oblig) : ?><option value="">Seleccionar Opción</option><?php endif; ?>
<?php foreach ($e->valores as $v) : ?><option <?php echo (int) $v->por_defecto ? 'selected="selected"' : ''; ?> value="<?php echo (int) $v->id; ?>"><?php echo $h($v->valor . ' ' . Vista::recargo((float) $v->recargo)); ?></option><?php endforeach; ?>
</select></div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<div class="ph-cb"></div>
<div class="ph-pull-right form-group ph-item-add-to-cart-box">
<input type="hidden" name="producto_id" value="<?php echo $id; ?>">
<?php echo HTMLHelper::_('form.token'); ?>
<div class="form-group ph-form-quantity phProductAddToCartItem<?php echo $id; ?> "> <label>Cant.: </label> <input class="form-control ph-input-quantity" type="text" name="cantidad" value="1" /> </div>
<div class="form-group ph-form-button"><button type="submit" class="btn btn-primary ph-btn phProductAddToCartItem<?php echo $id; ?> "> Añadir al Carrito</button> </div>
<div class="ph-cb"></div></div>
</form>
<?php endif; ?>
<div class="ph-cb"></div><div class="ph-top-space"></div><div class="ph-cb"></div><div class="ph-cb"></div></div></div></div>

<?php if (trim(strip_tags((string) $p->descripcion)) !== '') : ?><div class="ph-desc ph-item-desc"><?php echo $p->descripcion; ?></div><?php endif; ?>

<div class="ph-item-bottom-box"><ul class="nav nav-tabs" id="PcItemTab"><li class="nav-item "><a href="#phreview" data-bs-toggle="tab" class="nav-link active show">Reseñas</a></li></ul><div class="tab-content"><div class="tab-pane fade ph-tab-pane active show" id="phreview"><div class="ph-message">Solo los usuarios registrados pueden dejar una reseña, inicia sesión.</div></div></div></div><div class="ph-cb"></div></div>
