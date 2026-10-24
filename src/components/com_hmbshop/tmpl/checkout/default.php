<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use HeavyMetalBrothers\Component\Hmbshop\Site\Helper\Vista;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Site\View\Checkout\HtmlView $this */
$h       = fn($s) => Vista::h($s);
$d       = $this->detalle;
$tok     = HTMLHelper::_('form.token');
$u       = $this->usuario;
$logueado = !$u->guest;
$cl      = $this->cliente;
$volver  = base64_encode(Vista::urlCheckout());
$accion  = fn($t) => $h(Route::_('index.php?option=com_hmbshop&task=carrito.' . $t, false));
$estado  = fn($ok) => $ok ? 'finished' : 'pending';
?>
<div class="pc-view pc-checkout-view phTaxBoxNoTax"><h1 class="ph-header hmb-animate">Finalizar Compra</h1>

<?php if (!$d['lineas']) : ?>
<div class="ph-message">El carrito está vacío</div>
<?php endif; ?>
<?php if ($d['lineas']) : ?>
<div class="phCheckoutCartBox"><div class="ph-checkout-cart-box">
<div class="row ph-checkout-cart-row-header"><div class="col-2 col-sm-2 col-md-2 ph-checkout-cart-image">Imagen</div><div class="col-6 col-sm-6 col-md-6 ph-checkout-cart-product">Producto</div><div class="col-2 col-sm-2 col-md-2 ph-checkout-cart-quantity">Cantidad</div><div class="col-2 col-sm-2 col-md-2 ph-checkout-cart-brutto">Precio</div></div>
<div class="row ph-checkout-cart-row-hr"><div class="col-12 col-sm-12 col-md-12"><div class="ph-hr"></div></div></div>
<?php foreach ($d['lineas'] as $l) : $foto = Vista::foto($l->foto, 's'); ?>
<div class="row ph-vertical-align ph-checkout-cart-row-item"><div class="col-2 col-sm-2 col-md-2 ph-vertical-align-row ph-checkout-cart-image ph-row-image"><?php if ($foto !== '') : ?><img src="<?php echo $h($foto); ?>" alt="<?php echo $h($l->nombre); ?>" class="img-responsive ph-img-cart-checkout"><?php endif; ?></div>
<div class="col-6 col-sm-6 col-md-6 ph-vertical-align-row ph-checkout-cart-title"><a href="<?php echo $h(Route::_('index.php?option=com_hmbshop&view=producto&id=' . (int) $l->producto_id . ':' . $l->producto_alias . '&catid=' . (int) $l->categoria_id, false)); ?>"><?php echo $h($l->nombre); ?></a></div>
<div class="col-2 col-sm-2 col-md-2 ph-vertical-align-row ph-checkout-cart-quantity"><form action="<?php echo $accion('cambiar'); ?>" class="form-inline phItemCartUpdateBoxForm" method="post"><div class="input-group">
<input type="text" class="form-control ph-input-quantity ph-input-xs" name="quantity" value="<?php echo (int) $l->cantidad; ?>">
<?php echo $tok; ?>
<button class="btn btn-success btn-sm ph-btn" type="submit" name="action" value="update" style="display: none !important;"></button>
<button class="btn btn-danger btn-sm ph-btn" type="submit" name="action" value="delete" formaction="<?php echo $accion('quitar'); ?>" style="display: none !important;"></button>
<input type="hidden" name="variante_id" value="<?php echo (int) $l->variante_id; ?>">
</div></form></div>
<div class="col-2 col-sm-2 col-md-2 ph-vertical-align-row ph-checkout-cart-brutto"><?php echo $h(ShopHelper::dinero($l->subtotal)); ?></div></div>
<?php if ($l->atributos) : ?>
<div class="row ph-checkout-cart-row-attributes"><div class="col-2 col-sm-2 col-md-2"></div><div class="col-10 col-sm-10 col-md-10"><ul class="ph-checkout-attribute-box"><?php foreach ($l->atributos as [$k, $v]) : ?><li class="ph-checkout-attribute-item"><span class="ph-small ph-cart-small-attribute"><?php echo $h($k . ' ' . $v); ?></span></li><?php endforeach; ?></ul></div></div>
<?php endif; ?>
<div class="row ph-checkout-cart-row-hr"><div class="col-12 col-sm-12 col-md-12"><div class="ph-hr"></div></div></div>
<?php endforeach; ?>
<div class="row ph-cart-subtotal-box ph-checkout-cart-row-subtotal-box ph-checkout-cart-row-subtotal"><div class="col-0 col-sm-6 col-md-6"></div><div class="col-8 col-sm-4 col-md-4 ph-cart-subtotal-netto-txt">Subtotal</div><div class="col-4 col-sm-2 col-md-2 ph-right ph-cart-subtotal-netto"><?php echo $h(ShopHelper::dinero($d['total'])); ?></div></div>
<div class="row ph-cart-total-box ph-checkout-cart-row-total-box ph-checkout-cart-row-subtotal"><div class="col-0 col-sm-6 col-md-6"></div><div class="col-8 col-sm-4 col-md-4 ph-cart-total-txt">Total</div><div class="col-4 col-sm-2 col-md-2 ph-checkout-total-amount ph-cart-total ph-right"><?php echo $h(ShopHelper::dinero($d['total'])); ?></div></div>
</div></div>
<?php endif; ?>

<?php if (!$logueado) : ?>
<div class="ph-checkout-box-login ph-checkout-box-status-pending"><div class="row ph-checkout-box-row"><div class="col-12 col-sm-12 col-md-12 ph-checkout-box-header"><div class="ph-pull-right ph-checkout-icon-spec-box"> <span class="ph-checkout-icon-spec-pending">1</span> </div><h3>1. Iniciar sesión / Registrarse</h3></div></div>
<div class="row ph-checkout-box-action"><div class="col-12 col-sm-8 col-md-8 ph-right-border"><div class="ph-box-header">Iniciar sesión</div>
<form action="<?php echo $h(Route::_('index.php?option=com_users&task=user.login', false)); ?>" method="post" class="form-inline " role="form"><div class="control-group form-inline">
<div class="form-group"> <label class="sr-only" for="ph-modlgn-username">Usuario</label> <input type="text" name="username" id="ph-modlgn-username" class="form-control" tabindex="0" placeholder="Usuario"> </div>
<div class="form-group"> <label class="sr-only" for="ph-modlgn-passwd">Contraseña</label> <input type="password" name="password" id="ph-modlgn-passwd" class="form-control" tabindex="0" size="18" placeholder="Contraseña"> </div>
<div class="mod-login__remember form-group"> <div class="form-check"> <label class="form-check-label"> <input type="checkbox" name="remember" class="checkbox form-check-input" value="yes"> Recuérdeme </label> </div> </div>
<button type="submit" tabindex="0" name="Submit" class="btn btn-primary ph-btn">Conectar</button> </div>
<ul class="unstyled ph-li-inline"> <li><a href="/registro/recordar-usuario">¿Recordar su usuario?</a></li> <li><a href="/registro/contrasena-olvidada">¿Olvidó su contraseña?</a></li> </ul> <div class="ph-cb"></div>
<input type="hidden" name="return" value="<?php echo $h($volver); ?>"><?php echo $tok; ?></form></div>
<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 ph-left-border"><div class="ph-box-header">Registrarse</div> <a class="btn btn-primary btn-sm ph-btn ph-checkout-btn-login" href="<?php echo $h(Uri::root() . 'index.php/registro?layout=register'); ?>"> Crear una cuenta</a> </div> </div> </div>
<div class="ph-checkout-box-address ph-checkout-box-status-pending"><div class="row ph-checkout-box-row"><div class="col-12 col-sm-12 col-md-12 ph-checkout-box-header-pas"><div class="ph-pull-right ph-checkout-icon-spec-box"> <span class="ph-checkout-icon-spec-pending">2</span> </div><h3>2. Dirección de Facturación y Envío</h3></div></div></div>
<?php if ($d['lineas']) : ?>
<div class="ph-checkout-box-shipping ph-checkout-box-status-pending"><div class="row ph-checkout-box-row"><div class="col-12 col-sm-12 col-md-12 ph-checkout-box-header-pas"><div class="ph-pull-right ph-checkout-icon-spec-box"> <span class="ph-checkout-icon-spec-pending">3</span> </div><h3>3. Opciones de envío</h3></div></div></div>
<div class="ph-checkout-box-payment ph-checkout-box-status-pending"><div class="row ph-checkout-box-row"><div class="col-12 col-sm-12 col-md-12 ph-checkout-box-header-pas"><div class="ph-pull-right ph-checkout-icon-spec-box"> <span class="ph-checkout-icon-spec-pending">4</span> </div><h3>4. Opciones de pago</h3></div></div></div>
<?php endif; ?>

<?php elseif ($d['lineas']) : ?>
<form action="<?php echo $accion('confirmar'); ?>" method="post" class="phCheckoutForm">
<?php echo $tok; ?>
<div class="ph-checkout-box-login ph-checkout-box-status-finished"><div class="row ph-checkout-box-row"><div class="col-12 col-sm-12 col-md-12 ph-checkout-box-header"><div class="ph-pull-right ph-checkout-icon-spec-box"> <span class="ph-checkout-icon-spec-finished">✓</span> </div><h3>1. Iniciar sesión / Registrarse</h3></div></div>
<div class="row ph-checkout-box-action"><div class="col-12"><?php echo $h($u->name); ?> (<?php echo $h($u->email); ?>)</div></div></div>

<div class="ph-checkout-box-address ph-checkout-box-status-pending"><div class="row ph-checkout-box-row"><div class="col-12 col-sm-12 col-md-12 ph-checkout-box-header-pas"><div class="ph-pull-right ph-checkout-icon-spec-box"> <span class="ph-checkout-icon-spec-pending">2</span> </div><h3>2. Dirección de Facturación y Envío</h3></div></div>
<div class="row ph-checkout-box-action">
<div class="col-12 col-sm-6 col-md-6"><label for="hs-nombre">Nombre y apellidos</label><input id="hs-nombre" class="form-control" name="nombre" required value="<?php echo $h($cl['nombre'] ?? ''); ?>"></div>
<div class="col-12 col-sm-6 col-md-6"><label for="hs-email">Correo electrónico</label><input id="hs-email" type="email" class="form-control" name="email" required value="<?php echo $h($cl['email'] ?? ''); ?>"></div>
<div class="col-12 col-sm-6 col-md-6"><label for="hs-tel">Teléfono</label><input id="hs-tel" class="form-control" name="telefono" value="<?php echo $h($cl['telefono'] ?? ''); ?>"></div>
<div class="col-12 col-sm-6 col-md-6"><label for="hs-dir">Dirección</label><input id="hs-dir" class="form-control" name="direccion" value="<?php echo $h($cl['direccion'] ?? ''); ?>"></div>
<div class="col-12 col-sm-6 col-md-6"><label for="hs-ciudad">Ciudad</label><input id="hs-ciudad" class="form-control" name="ciudad" value="<?php echo $h($cl['ciudad'] ?? ''); ?>"></div>
<div class="col-12 col-sm-6 col-md-6"><label for="hs-cp">Código postal</label><input id="hs-cp" class="form-control" name="cp" value="<?php echo $h($cl['cp'] ?? ''); ?>"></div>
</div></div>

<div class="ph-checkout-box-shipping ph-checkout-box-status-pending"><div class="row ph-checkout-box-row"><div class="col-12 col-sm-12 col-md-12 ph-checkout-box-header-pas"><div class="ph-pull-right ph-checkout-icon-spec-box"> <span class="ph-checkout-icon-spec-pending">3</span> </div><h3>3. Opciones de envío</h3></div></div>
<div class="row ph-checkout-box-action ph-checkout-shipping-row">
<?php foreach ($this->envios as $i => $e) : ?>
<div class="col-12 ph-checkout-shipping-item" data-dir="<?php echo (int) $e->pide_direccion; ?>"><label><input type="radio" name="envio_id" value="<?php echo (int) $e->id; ?>" <?php echo $i === 0 ? 'checked' : ''; ?> data-shipping-title="<?php echo $h($e->nombre); ?>"> <span class="ph-shipping-title"><?php echo $h($e->nombre); ?></span> <span class="ph-checkout-shipping-brutto-txt">Envío</span> <span class="ph-checkout-shipping-brutto"><?php echo (float) $e->coste > 0 ? $h(ShopHelper::dinero((float) $e->coste)) : 'Gratis'; ?></span></label><?php if ($e->descripcion) : ?><div class="ph-small"><?php echo $h($e->descripcion); ?></div><?php endif; ?></div>
<?php endforeach; ?>
</div></div>

<div class="ph-checkout-box-payment ph-checkout-box-status-pending"><div class="row ph-checkout-box-row"><div class="col-12 col-sm-12 col-md-12 ph-checkout-box-header-pas"><div class="ph-pull-right ph-checkout-icon-spec-box"> <span class="ph-checkout-icon-spec-pending">4</span> </div><h3>4. Opciones de pago</h3></div></div>
<div class="row ph-checkout-box-action">
<?php $primero = true; foreach ($this->metodos as $clave => [$nombre, $texto]) : ?>
<div class="col-12 ph-checkout-payment-item"><label><input type="radio" name="metodo" value="<?php echo $h($clave); ?>" <?php echo $primero ? 'checked' : ''; ?> data-payment-title="<?php echo $h($nombre); ?>"> <span class="ph-payment-title"><?php echo $h($nombre); ?></span></label><div class="ph-small"><?php echo $h($texto); ?></div></div>
<?php $primero = false; endforeach; ?>
<?php if (!$this->metodos) : ?><div class="col-12 ph-message">Ahora mismo no hay ninguna forma de pago activa.</div><?php endif; ?>
</div></div>

<?php if ($this->metodos) : ?>
<div class="ph-checkout-box-confirm"><div class="row"><div class="col-12 ph-right"><button type="submit" class="btn btn-primary ph-btn ph-checkout-confirm-btn">Confirmar pedido</button></div></div></div>
<?php endif; ?>
</form>
<?php endif; ?>
</div>
