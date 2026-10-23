<?php
namespace Joomla\Plugin\System\Hmbshop\Extension;
defined('_JEXEC') or die;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

final class Hmbshop extends CMSPlugin
{
    public function onAfterDispatch(): void
    {
        $app = Factory::getApplication();
        if ($app->isClient('administrator')) return;
        $doc = Factory::getDocument();
        if ($doc->getType() !== 'html') return;
        $doc->addScriptDeclaration($this->js());
    }

    private function js(): string { return '
(function(){
window.addEventListener("load",function(){

// Fix carrusel - anchos en píxeles
var carWrap = document.querySelector(".hmb-carousel-wrap");
var carSlides = document.querySelector(".hmb-carousel-slides");
if(carWrap && carSlides){
  function fixCarousel(){
    var w = carWrap.offsetWidth;
    if(!w) return;
    carSlides.querySelectorAll(".hmb-carousel-slide").forEach(function(s){
      s.style.minWidth = w+"px";
      s.style.width = w+"px";
    });
  }
  fixCarousel();
  window.addEventListener("resize", fixCarousel);
}

var s=document.createElement("style");
document.head.appendChild(s);
s.textContent=[

// Grid productos
".ph-row-flex.grid .row-item .ph-item-box .ph-thumbnail-c .ph-item-content{flex-direction:column!important;}",
".row-item{width:50%!important;padding:.6rem!important;box-sizing:border-box!important;}",
"@media(max-width:600px){.row-item{width:100%!important;}}",
".thumbnail.ph-thumbnail{background:#1a1a1a!important;border:1px solid #222!important;border-radius:0!important;transition:border-color .3s,transform .3s!important;}",
".thumbnail.ph-thumbnail:hover{border-color:#8B0000!important;transform:translateY(-4px)!important;box-shadow:0 8px 24px rgba(0,0,0,.6)!important;}",
".ph-product-header{font-family:Oswald,sans-serif!important;font-size:1.1rem!important;font-weight:600!important;letter-spacing:1.5px!important;text-transform:uppercase!important;color:#EFEFEF!important;margin:.75rem 0 .4rem!important;}",
".thumbnail.ph-thumbnail:hover .ph-product-header{color:#FF4500!important;}",
".ph-price-original{color:#444!important;text-decoration:line-through!important;font-size:.9rem!important;}",
".ph-price-brutto{font-family:\"Metal Mania\",cursive!important;font-size:1.5rem!important;color:#CC2200!important;}",
".ph-price-txt{font-family:Oswald,sans-serif!important;font-size:.72rem!important;text-transform:uppercase!important;color:#555!important;letter-spacing:1px!important;}",
".ph-button-add-to-cart-box .ph-btn,.ph-button-add-to-cart-box button.btn{background:#8B0000!important;border:1px solid #8B0000!important;color:#fff!important;border-radius:0!important;font-size:.78rem!important;letter-spacing:2px!important;}",
".ph-button-add-to-cart-box .ph-btn:hover,.ph-button-add-to-cart-box button.btn:hover{background:#CC2200!important;border-color:#CC2200!important;}",
".ph-button-view-product-box .ph-btn,.ph-button-view-product-box a.btn{background:transparent!important;border:1px solid #333!important;color:#777!important;border-radius:0!important;font-size:.78rem!important;}",
".ph-button-view-product-box .ph-btn:hover,.ph-button-view-product-box a.btn:hover{border-color:#8B0000!important;color:#fff!important;}",

// Producto individual
".ph-header{font-family:\"Metal Mania\",cursive!important;font-size:2.2rem!important;color:#EFEFEF!important;letter-spacing:2px!important;border-bottom:2px solid #8B0000!important;padding-bottom:.75rem!important;margin:0 0 1rem!important;}",
".ph-desc p,.ph-item-desc p{font-family:Rajdhani,sans-serif!important;color:#aaa!important;font-size:1.05rem!important;line-height:1.7!important;}",
".ph-top .btn-secondary{background:transparent!important;border:1px solid #333!important;color:#777!important;border-radius:0!important;font-family:Oswald,sans-serif!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
".ph-input-quantity{background:#111!important;border:1px solid #333!important;color:#fff!important;border-radius:0!important;width:65px!important;text-align:center!important;font-size:1rem!important;}",
".ph-form-button button.btn{background:#8B0000!important;border:1px solid #8B0000!important;color:#fff!important;border-radius:0!important;font-family:Oswald,sans-serif!important;letter-spacing:3px!important;text-transform:uppercase!important;padding:.7rem 1.8rem!important;font-size:.85rem!important;}",
".ph-form-button button.btn:hover{background:#CC2200!important;}",
".nav-tabs{border-bottom:2px solid #8B0000!important;}",
".nav-tabs .nav-link{color:#555!important;border:none!important;border-radius:0!important;font-family:Oswald,sans-serif!important;font-size:.8rem!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
".nav-tabs .nav-link.active{color:#fff!important;background:#8B0000!important;}",
".pc-si{width:16px!important;height:16px!important;fill:currentColor!important;}",
"@media(max-width:768px){.ph-item-view-data-box{padding-left:0!important;margin-top:1.5rem!important;}}",

// Módulo carrito
".ph-cart-small-title a{color:#EFEFEF!important;text-decoration:none!important;font-family:Oswald,sans-serif!important;text-transform:uppercase!important;font-size:.95rem!important;letter-spacing:1px!important;}",
".ph-cart-module-image img{width:64px!important;height:64px!important;object-fit:cover!important;display:block!important;}",
".ph-cart-small-quantity{font-family:Oswald,sans-serif!important;font-size:.85rem!important;color:#555!important;}",
".ph-cart-small-price{font-size:1rem!important;}",
".ph-cart-small-price,.ph-cart-total,.ph-b{font-family:\"Metal Mania\",cursive!important;color:#CC2200!important;}",
".ph-cart-total,.ph-b{color:#fff!important;font-size:1.3rem!important;font-weight:normal!important;}",
".ph-cart-link-checkout a{background:#8B0000!important;color:#fff!important;display:block!important;padding:.7rem!important;text-align:center!important;text-decoration:none!important;font-family:Oswald,sans-serif!important;letter-spacing:2px!important;text-transform:uppercase!important;font-size:.82rem!important;}",
".ph-cart-link-checkout a:hover{background:#CC2200!important;}",

// Módulo productos destacados
".phProductScrollerModuleBox .ph-item-box{background:#1a1a1a;border:1px solid #222;margin-bottom:.75rem;transition:border-color .3s;}",
".phProductScrollerModuleBox .ph-item-box:hover{border-color:#8B0000;}",
".phProductScrollerModuleBox img{width:100%!important;height:auto!important;display:block!important;}",
".phProductScrollerModuleBox .ph-product-header,.phProductScrollerModuleBox h3{font-family:Oswald,sans-serif!important;font-size:.88rem!important;font-weight:600!important;letter-spacing:1.5px!important;text-transform:uppercase!important;color:#EFEFEF!important;}",
".phProductScrollerModuleBox .ph-price-brutto{font-family:\"Metal Mania\",cursive!important;color:#CC2200!important;font-size:1.1rem!important;}",

// Checkout login y dirección
".ph-checkout-box-login,.ph-checkout-box-action,.ph-checkout-box-row,.ph-checkout-box-header,.ph-box-header,.ph-right-border,.ph-left-border,.ph-checkout-box-address,.ph-checkout-box-header-pas,.ph-checkout-box-status-pending{background:#111!important;color:#EFEFEF!important;}",
".ph-checkout-box-login *,.ph-checkout-box-address *{color:#EFEFEF!important;}",
".ph-checkout-box-login h3,.ph-checkout-box-address h3{font-family:Oswald,sans-serif!important;letter-spacing:2px!important;text-transform:uppercase!important;font-size:1rem!important;}",
".ph-checkout-box-login input[type=text],.ph-checkout-box-login input[type=password],.ph-checkout-box-address input,.ph-checkout-box-address select,.ph-checkout-box-address textarea{background:#0a0a0a!important;border:1px solid #333!important;color:#EFEFEF!important;border-radius:0!important;font-size:.95rem!important;}",
".ph-checkout-box-login input:focus,.ph-checkout-box-address input:focus{border-color:#8B0000!important;outline:none!important;box-shadow:none!important;}",
".ph-checkout-box-login .btn-primary,.ph-checkout-box-address .btn-primary,.ph-checkout-box-address .btn-success{background:#8B0000!important;border-color:#8B0000!important;color:#fff!important;border-radius:0!important;font-family:Oswald,sans-serif!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
".ph-checkout-box-login .btn-primary:hover,.ph-checkout-box-address .btn-primary:hover{background:#CC2200!important;}",
".ph-checkout-box-login a{color:#CC2200!important;}",
".ph-checkout-box-address label,.ph-checkout-box-login label{color:#777!important;font-family:Oswald,sans-serif!important;font-size:.78rem!important;letter-spacing:1.5px!important;text-transform:uppercase!important;}",
".ph-checkout-icon-spec-pending{background:#8B0000!important;color:#fff!important;font-family:\"Metal Mania\",cursive!important;font-size:1.1rem!important;}",
".ph-checkout-box-shipping,.ph-checkout-box-payment{background:#111!important;border:1px solid #1a1a1a!important;border-left:3px solid #8B0000!important;margin-bottom:1rem!important;}",
".ph-checkout-box-shipping *,.ph-checkout-box-payment *{color:#EFEFEF!important;background:transparent!important;}",
".ph-checkout-box-shipping h3,.ph-checkout-box-payment h3{font-family:Oswald,sans-serif!important;font-size:1rem!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
".ph-checkout-box-status-finished{border-left-color:#2a5!important;}",
".ph-checkout-icon-spec-finished{background:#2a5!important;color:#fff!important;font-family:Metal Mania,cursive!important;font-size:1.1rem!important;}",
".ph-checkout-shipping-row .ph-box-header{font-family:Oswald,sans-serif!important;font-size:.85rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#555!important;margin-bottom:.75rem!important;}",
".ph-checkout-shipping-item{background:#1a1a1a!important;border:1px solid #222!important;margin-bottom:.5rem!important;padding:.75rem!important;}",
".ph-checkout-shipping-item:hover{border-color:#8B0000!important;}",
".ph-checkout-shipping-item input[type=radio]{accent-color:#CC2200!important;width:18px!important;height:18px!important;margin-right:.5rem!important;}",
".ph-shipping-title{font-family:Oswald,sans-serif!important;font-size:.9rem!important;letter-spacing:1px!important;text-transform:uppercase!important;color:#EFEFEF!important;}",
".ph-shipping-image img{width:40px!important;height:40px!important;object-fit:cover!important;border-radius:0!important;border:1px solid #333!important;margin-right:.5rem!important;vertical-align:middle!important;}",
".ph-checkout-shipping-brutto-txt{font-family:Oswald,sans-serif!important;font-size:.72rem!important;letter-spacing:1.5px!important;text-transform:uppercase!important;color:#555!important;}",
".ph-checkout-shipping-brutto{font-family:Metal Mania,cursive!important;color:#CC2200!important;font-size:1rem!important;}",
".ph-checkout-shipping-save .btn-primary,.ph-checkout-shipping-save .ph-btn{background:#8B0000!important;border-color:#8B0000!important;color:#fff!important;border-radius:0!important;font-family:Oswald,sans-serif!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
".ph-checkout-shipping-save .btn-primary:hover,.ph-checkout-payment-save .btn-primary:hover{background:#CC2200!important;}",
".ph-checkout-shipping-row-item{display:flex!important;align-items:center!important;gap:1rem!important;background:#1a1a1a!important;border:2px solid #CC2200!important;padding:1rem!important;}",
".ph-checkout-shipping-row-item .ph-shipping-image img{width:72px!important;height:72px!important;object-fit:cover!important;display:block!important;border:1px solid #333!important;}",
".ph-checkout-shipping-row-item .ph-shipping-title{font-family:Oswald,sans-serif!important;font-size:1rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#EFEFEF!important;}",
".ph-checkout-shipping-edit .btn-success{background:transparent!important;border:1px solid #333!important;color:#777!important;border-radius:0!important;font-family:Oswald,sans-serif!important;font-size:.72rem!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
".ph-checkout-shipping-edit .btn-success:hover{border-color:#8B0000!important;color:#fff!important;}",
".ph-checkout-payment-row-item{display:flex!important;align-items:center!important;gap:1rem!important;background:#1a1a1a!important;border:2px solid #CC2200!important;padding:1rem!important;}",
".ph-checkout-payment-row-item .ph-payment-image img{width:72px!important;height:72px!important;object-fit:cover!important;display:block!important;border:1px solid #333!important;}",
".ph-checkout-payment-row-item .ph-payment-title{font-family:Oswald,sans-serif!important;font-size:1rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#EFEFEF!important;}",
".ph-checkout-payment-edit .btn-success{background:transparent!important;border:1px solid #333!important;color:#777!important;border-radius:0!important;font-family:Oswald,sans-serif!important;font-size:.72rem!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
".ph-checkout-payment-edit .btn-success:hover{border-color:#8B0000!important;color:#fff!important;}",
".ph-input-apply-coupon{background:#0a0a0a!important;border:1px solid #333!important;color:#EFEFEF!important;border-radius:0!important;margin-top:.3rem!important;}",
".ph-checkout-payment-save .btn-primary{background:#8B0000!important;border-color:#8B0000!important;color:#fff!important;border-radius:0!important;font-family:Oswald,sans-serif!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
".ph-checkout-address-edit .btn-success{background:transparent!important;border:1px solid #333!important;color:#777!important;border-radius:0!important;font-family:Oswald,sans-serif!important;font-size:.72rem!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
".ph-checkout-address-edit .btn-success:hover{border-color:#8B0000!important;color:#fff!important;}",
".ph-category-module-box{background:#111!important;border:1px solid #1a1a1a!important;border-top:2px solid #8B0000!important;padding:.5rem!important;}",
".ph-category-box-link{display:flex!important;align-items:center!important;gap:.75rem!important;padding:.5rem!important;text-decoration:none!important;border-bottom:1px solid #1a1a1a!important;transition:background .2s!important;}",
".ph-category-box-link:hover{background:rgba(139,0,0,.15)!important;}",
".ph-category-box-image img{width:48px!important;height:48px!important;object-fit:cover!important;border:1px solid #222!important;flex-shrink:0!important;}",
".ph-category-box-title{font-family:Oswald,sans-serif!important;font-size:.85rem!important;letter-spacing:1.5px!important;text-transform:uppercase!important;color:#EFEFEF!important;}",
".ph-category-box-link:hover .ph-category-box-title{color:#CC2200!important;}",

// Checkout tabla
".ph-checkout-cart-box{overflow-x:auto!important;}",
".ph-checkout-cart-box .row{display:flex!important;flex-wrap:nowrap!important;align-items:center!important;min-width:580px!important;border-bottom:1px solid #1a1a1a!important;padding:.4rem 0!important;margin:0!important;background:#111!important;}",
// 4 columnas: imagen(72px), producto(flex), cantidad(140px), precio(120px)
".ph-checkout-cart-box .ph-checkout-cart-image{flex:0 0 72px!important;max-width:72px!important;min-width:72px!important;padding:.2rem .3rem!important;}",
".ph-checkout-cart-box .ph-checkout-cart-product,.ph-checkout-cart-box .ph-checkout-cart-title{flex:1!important;padding:.2rem .4rem!important;}",
".ph-checkout-cart-box .ph-checkout-cart-quantity{flex:0 0 140px!important;max-width:140px!important;min-width:140px!important;padding:.2rem .3rem!important;}",
".ph-checkout-cart-box .ph-checkout-cart-brutto{flex:0 0 120px!important;max-width:120px!important;padding:.2rem .4rem!important;font-family:\"Metal Mania\",cursive!important;color:#CC2200!important;font-size:1.1rem!important;text-align:right!important;}",
".ph-checkout-cart-row-header{font-family:Oswald,sans-serif!important;font-size:.68rem!important;letter-spacing:1.5px!important;text-transform:uppercase!important;color:#555!important;}",
".ph-checkout-cart-row-attributes .ph-cart-small-attribute{font-family:Oswald,sans-serif!important;font-size:.78rem!important;color:#777!important;letter-spacing:1px!important;}",
".ph-img-cart-checkout{width:58px!important;height:58px!important;object-fit:cover!important;border:1px solid #222!important;display:block!important;}",
".ph-checkout-cart-title a{color:#EFEFEF!important;text-decoration:none!important;font-family:Oswald,sans-serif!important;font-size:.85rem!important;text-transform:uppercase!important;}",
".ph-checkout-cart-quantity .input-group{display:flex!important;align-items:center!important;flex-wrap:nowrap!important;gap:.25rem!important;}",
".ph-checkout-cart-quantity input[name=quantity]{width:36px!important;min-width:36px!important;text-align:center!important;background:#111!important;border:1px solid #333!important;color:#fff!important;border-radius:0!important;padding:.2rem .1rem!important;font-size:.9rem!important;}",
".hmb-qty-btn{background:transparent!important;border:1px solid #444!important;color:#EFEFEF!important;width:24px!important;min-width:24px!important;height:24px!important;padding:0!important;cursor:pointer!important;font-size:1.1rem!important;line-height:1!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;border-radius:0!important;transition:border-color .15s,color .15s!important;}",
".hmb-qty-btn:hover{border-color:#CC2200!important;color:#CC2200!important;}",
".ph-cart-subtotal-netto-txt,.ph-cart-tax-txt,.ph-cart-total-txt{font-family:Oswald,sans-serif!important;font-size:.78rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#555!important;}",
".ph-cart-total{font-family:\"Metal Mania\",cursive!important;font-size:1.4rem!important;color:#CC2200!important;}",
".ph-checkout-total-amount{color:#EFEFEF!important;font-size:.95rem!important;}",
".ph-hr{border:none!important;border-top:1px solid #1a1a1a!important;}",

// Popup
"#phAddToCartPopup .modal-dialog{background:#1a1a1a!important;border:1px solid #222!important;border-top:3px solid #8B0000!important;}",
"#phAddToCartPopup .modal-content{background:#1a1a1a!important;border:none!important;}",
"#phAddToCartPopup .modal-header{background:#111!important;border-bottom:1px solid #222!important;}",
"#phAddToCartPopup .modal-title{font-family:Oswald,sans-serif!important;font-size:.95rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#EFEFEF!important;}",
"#phAddToCartPopup .btn-close{filter:invert(1)!important;opacity:.6!important;}",
"#phAddToCartPopup .modal-body{padding:1.25rem!important;}",
"#phAddToCartPopup .modal-footer{display:none!important;}",
"#phAddToCartPopup .row{display:flex!important;gap:.5rem!important;margin:0!important;padding:.5rem 0 0!important;}",
"#phAddToCartPopup [class*=col-]{flex:1!important;padding:0!important;}",
"#phAddToCartPopup .btn.btn-primary.ph-btn{background:transparent!important;border:1px solid #333!important;color:#888!important;border-radius:0!important;padding:.65rem 1rem!important;display:block!important;text-align:center!important;text-decoration:none!important;width:100%!important;font-family:Oswald,sans-serif!important;font-size:.78rem!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
"#phAddToCartPopup .btn.btn-primary.ph-btn:hover{border-color:#8B0000!important;color:#fff!important;}",
"#phAddToCartPopup .btn.btn-success.ph-btn{background:#8B0000!important;border:1px solid #8B0000!important;color:#fff!important;border-radius:0!important;padding:.65rem 1rem!important;display:block!important;text-align:center!important;text-decoration:none!important;width:100%!important;font-family:Oswald,sans-serif!important;font-size:.78rem!important;letter-spacing:2px!important;text-transform:uppercase!important;}",
"#phAddToCartPopup .btn.btn-success.ph-btn:hover{background:#CC2200!important;}",

// Ocultar
"[id*=asskey],[class*=asskey],[id*=Passkey],[class*=Passkey]{display:none!important;}",
"body.modal-open{overflow:auto!important;padding-right:0!important;}","html.modal-open{overflow:auto!important;}","html{overflow-y:scroll!important;}",
"a[href*=phoca\\.cz]{display:none!important;}",

// Subcategorías
".ph-subcategories{font-family:Oswald,sans-serif!important;font-size:.78rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#555!important;margin-bottom:.5rem!important;}",
"#ph-pc-category-box>ul{list-style:none!important;padding:0!important;margin:1rem 0!important;display:flex!important;flex-wrap:wrap!important;gap:.75rem!important;}",
"#ph-pc-category-box>ul li a{display:block!important;padding:.7rem 1.4rem!important;background:#1a1a1a!important;border:1px solid #222!important;border-left:3px solid #8B0000!important;font-family:Oswald,sans-serif!important;font-size:.85rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#EFEFEF!important;text-decoration:none!important;}",
"#ph-pc-category-box>ul li a:hover{border-left-color:#CC2200!important;color:#CC2200!important;}",
".ph-thumbnail.list{overflow:hidden!important;}"

].join("");

// Passkey
document.querySelectorAll("button,a").forEach(function(el){
  if(el.textContent.trim().toLowerCase().indexOf("passkey")>-1){
    var p=el.parentElement; if(p) p.style.cssText="display:none!important";
  }
});

// Powered by
var pbLinks=document.querySelectorAll("a[href*=\"phoca.cz\"]");
pbLinks.forEach(function(a){
  var parent=a.parentElement;
  if(parent) parent.style.cssText="display:none!important";
});

// Evitar autoscroll - blur cualquier input que reciba focus fuera del viewport
document.querySelectorAll("#ph-login-form input, #ph-login-form button").forEach(function(el){
  el.addEventListener("focus", function(){
    var rect = el.getBoundingClientRect();
    if(rect.top < 0 || rect.top > window.innerHeight) el.blur();
  });
});

// Botones +/- checkout
function initQtyButtons(){
  document.querySelectorAll(".ph-checkout-cart-quantity").forEach(function(box){
    var input=box.querySelector("input[name=quantity]");
    if(!input||input.dataset.hmb) return;
    input.dataset.hmb="1";
    var updateBtn=box.querySelector("button[value=update]");
    var deleteBtn=box.querySelector("button[value=delete]");
    if(!updateBtn) return;
    var ig=box.querySelector(".input-group");
    if(!ig) return;
    var minus=document.createElement("button");
    minus.type="button"; minus.className="hmb-qty-btn"; minus.textContent="-";
    minus.addEventListener("click",function(){
      var v=parseInt(input.value)||1;
      if(v>1){input.value=v-1; updateBtn.click();}
      else if(deleteBtn){deleteBtn.click();}
    });
    var plus=document.createElement("button");
    plus.type="button"; plus.className="hmb-qty-btn"; plus.textContent="+";
    plus.addEventListener("click",function(){
      input.value=(parseInt(input.value)||1)+1; updateBtn.click();
    });
    ig.insertBefore(minus,input);
    ig.appendChild(plus);
    updateBtn.style.setProperty("display","none","important");
    if(deleteBtn) deleteBtn.style.setProperty("display","none","important");
  });
}
initQtyButtons();

// Popup
new MutationObserver(function(mm){
  mm.forEach(function(m){
    m.addedNodes.forEach(function(n){
      if(n.nodeType!==1||n.id!=="phAddToCartPopup"||n.dataset.hmb) return;
      n.dataset.hmb="1";
      n.querySelectorAll(".btn-success.ph-btn").forEach(function(btn){
        btn.addEventListener("click",function(e){
          e.preventDefault();
          var h=btn.getAttribute("href");
          if(window.bootstrap&&window.bootstrap.Modal){
            var m=window.bootstrap.Modal.getInstance(n);
            if(m) m.hide();
          }
          document.body.style.overflow="";
          document.body.style.paddingRight="";
          document.body.classList.remove("modal-open");
          if(h) setTimeout(function(){window.location.href=h;},200);
        });
      });
      // Restaurar scroll al cerrar por cualquier medio
      n.addEventListener("hidden.bs.modal",function(){
        document.body.style.overflow="";
        document.body.style.paddingRight="";
        document.body.classList.remove("modal-open");
      });
      n.querySelectorAll(".btn-close,[data-bs-dismiss]").forEach(function(btn){
        btn.addEventListener("click",function(){
          document.body.style.overflow="";
          document.body.style.paddingRight="";
          document.body.classList.remove("modal-open");
        });
      });
    });
  });
}).observe(document.body,{childList:true});

// Tarjetas de envío
(function(){
  var items = document.querySelectorAll(".ph-checkout-shipping-item");
  if(!items.length) return;

  items.forEach(function(item){
    if(item.dataset.hmb) return;
    item.dataset.hmb = "1";

    var radio = item.querySelector("input[type=radio]");
    if(!radio) return;

    var titleEl = item.querySelector(".ph-shipping-title");
    var imgEl = item.querySelector(".ph-shipping-image img");
    var priceEl = item.querySelector(".ph-checkout-shipping-brutto");
    var priceTxtEl = item.querySelector(".ph-checkout-shipping-brutto-txt");

    var src = imgEl ? (imgEl.getAttribute("src")||"").split("#")[0] : "";
    var titleText = titleEl ? titleEl.textContent.trim() : (radio.getAttribute("data-shipping-title")||"");
    var priceText = priceEl ? priceEl.textContent.trim() : "";
    var priceTxtText = priceTxtEl ? priceTxtEl.textContent.trim() : "Envío";

    // Rebuild item as card
    item.style.cssText = "padding:0!important;background:transparent!important;border:none!important;margin-bottom:.5rem!important;";
    item.innerHTML = "";

    var card = document.createElement("label");
    card.style.cssText = "display:flex;align-items:center;gap:1rem;background:#1a1a1a;border:2px solid #222;padding:1rem;cursor:pointer;margin:0;width:100%;box-sizing:border-box;transition:border-color .2s,background .2s;";

    radio.style.cssText = "position:absolute;opacity:0;width:0;height:0;";
    card.appendChild(radio);

    if(src){
      var imgWrap = document.createElement("div");
      imgWrap.style.cssText = "flex-shrink:0;width:72px;height:72px;overflow:hidden;border:1px solid #333;background:#111;";
      var img = document.createElement("img");
      img.src = src;
      img.style.cssText = "width:100%;height:100%;object-fit:cover;display:block;";
      imgWrap.appendChild(img);
      card.appendChild(imgWrap);
    }

    var info = document.createElement("div");
    info.style.cssText = "flex:1;";
    var t = document.createElement("div");
    t.style.cssText = "font-family:Oswald,sans-serif;font-size:1rem;letter-spacing:2px;text-transform:uppercase;color:#EFEFEF;margin-bottom:.35rem;";
    t.textContent = titleText;
    info.appendChild(t);
    var pr = document.createElement("div");
    pr.style.cssText = "display:flex;align-items:baseline;gap:.4rem;";
    var pt = document.createElement("span");
    pt.style.cssText = "font-family:Oswald,sans-serif;font-size:.68rem;letter-spacing:1.5px;text-transform:uppercase;color:#555;";
    pt.textContent = priceTxtText + ":";
    var pv = document.createElement("span");
    pv.style.cssText = "font-family:Metal Mania,cursive;font-size:1.2rem;color:#CC2200;";
    pv.textContent = priceText;
    pr.appendChild(pt); pr.appendChild(pv);
    info.appendChild(pr);
    card.appendChild(info);

    var chk = document.createElement("div");
    chk.style.cssText = "width:26px;height:26px;border:2px solid #333;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.9rem;";
    card.appendChild(chk);

    function upd(){
      if(radio.checked){
        card.style.borderColor="#CC2200"; card.style.background="rgba(139,0,0,.12)";
        chk.style.cssText="width:26px;height:26px;border:2px solid #CC2200;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.9rem;background:#CC2200;color:#fff;";
        chk.textContent="✓";
      } else {
        card.style.borderColor="#222"; card.style.background="#1a1a1a";
        chk.style.cssText="width:26px;height:26px;border:2px solid #333;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.9rem;background:transparent;color:transparent;";
        chk.textContent="";
      }
    }
    radio.addEventListener("change", function(){
      document.querySelectorAll(".ph-checkout-shipping-item[data-hmb] input[type=radio]").forEach(function(r){ r.dispatchEvent(new Event("hmb-upd")); });
    });
    radio.addEventListener("hmb-upd", upd);
    upd();
    item.appendChild(card);
  });

  // Select first by default
  var firstR = document.querySelector(".ph-checkout-shipping-item input[type=radio]");
  if(firstR && !document.querySelector(".ph-checkout-shipping-item input[type=radio]:checked")){
    firstR.checked=true; firstR.dispatchEvent(new Event("hmb-upd"));
  }
})();

// Tarjetas de pago
(function(){
  var items = document.querySelectorAll(".ph-checkout-payment-item");
  if(!items.length) return;

  items.forEach(function(item){
    if(item.dataset.hmb) return;
    item.dataset.hmb = "1";

    var radio = item.querySelector("input[type=radio]");
    if(!radio) return;

    var titleEl = item.querySelector(".ph-payment-title");
    var imgEl = item.querySelector(".ph-payment-image img");
    var priceEl = item.querySelector(".ph-checkout-payment-brutto");
    var priceTxtEl = item.querySelector(".ph-checkout-payment-brutto-txt");

    var src = imgEl ? (imgEl.getAttribute("src")||"").split("#")[0] : "";
    var titleText = titleEl ? titleEl.textContent.trim() : (radio.getAttribute("data-payment-title")||"");
    var priceText = priceEl ? priceEl.textContent.trim() : "";
    var priceTxtText = priceTxtEl ? priceTxtEl.textContent.trim() : "Pago";
    var isChecked = radio.checked;

    item.style.cssText = "padding:0!important;background:transparent!important;border:none!important;margin-bottom:.5rem!important;";
    item.innerHTML = "";

    var card = document.createElement("label");
    card.style.cssText = "display:flex;align-items:center;gap:1rem;background:#1a1a1a;border:2px solid " + (isChecked?"#CC2200":"#222") + ";padding:1rem;cursor:pointer;margin:0;width:100%;box-sizing:border-box;transition:border-color .2s,background .2s;";

    radio.style.cssText = "position:absolute;opacity:0;width:0;height:0;";
    if(isChecked) radio.checked = true;
    card.appendChild(radio);

    if(src){
      var imgWrap = document.createElement("div");
      imgWrap.style.cssText = "flex-shrink:0;width:72px;height:72px;overflow:hidden;border:1px solid #333;background:#111;";
      var img = document.createElement("img");
      img.src = src;
      img.style.cssText = "width:100%;height:100%;object-fit:cover;display:block;";
      imgWrap.appendChild(img);
      card.appendChild(imgWrap);
    }

    var info = document.createElement("div");
    info.style.cssText = "flex:1;";
    var t = document.createElement("div");
    t.style.cssText = "font-family:Oswald,sans-serif;font-size:1rem;letter-spacing:2px;text-transform:uppercase;color:#EFEFEF;margin-bottom:.35rem;";
    t.textContent = titleText;
    info.appendChild(t);
    if(priceText){
      var pr = document.createElement("div");
      pr.style.cssText = "display:flex;align-items:baseline;gap:.4rem;";
      var pt = document.createElement("span");
      pt.style.cssText = "font-family:Oswald,sans-serif;font-size:.68rem;letter-spacing:1.5px;text-transform:uppercase;color:#555;";
      pt.textContent = priceTxtText + ":";
      var pv = document.createElement("span");
      pv.style.cssText = "font-family:Metal Mania,cursive;font-size:1.2rem;color:#CC2200;";
      pv.textContent = priceText;
      pr.appendChild(pt); pr.appendChild(pv);
      info.appendChild(pr);
    }
    card.appendChild(info);

    var chk = document.createElement("div");
    if(isChecked){
      chk.style.cssText = "width:26px;height:26px;border:2px solid #CC2200;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.9rem;background:#CC2200;color:#fff;";
      chk.textContent = "✓";
      card.style.background = "rgba(139,0,0,.12)";
    } else {
      chk.style.cssText = "width:26px;height:26px;border:2px solid #333;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.9rem;background:transparent;color:transparent;";
    }
    card.appendChild(chk);

    function upd(){
      if(radio.checked){
        card.style.borderColor="#CC2200"; card.style.background="rgba(139,0,0,.12)";
        chk.style.cssText="width:26px;height:26px;border:2px solid #CC2200;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.9rem;background:#CC2200;color:#fff;";
        chk.textContent="✓";
      } else {
        card.style.borderColor="#222"; card.style.background="#1a1a1a";
        chk.style.cssText="width:26px;height:26px;border:2px solid #333;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.9rem;background:transparent;color:transparent;";
        chk.textContent="";
      }
    }
    radio.addEventListener("change", function(){
      document.querySelectorAll(".ph-checkout-payment-item[data-hmb] input[type=radio]").forEach(function(r){ r.dispatchEvent(new Event("hmb-upd")); });
    });
    radio.addEventListener("hmb-upd", upd);
    item.appendChild(card);
  });
})();

// Vista lista - reconstruir como tarjeta vertical
document.querySelectorAll(".jf_ph_cat_list.list").forEach(function(row){
  if(row.dataset.hmb) return;
  row.dataset.hmb="1";

  var imgEl = row.querySelector(".ph-category-item-image img");
  var titleEl = row.querySelector(".ph-product-header");
  var priceEl = row.querySelector(".ph-price-brutto");
  var priceTxtEl = row.querySelector(".ph-price-txt");
  var buttonsEl = row.querySelector(".ph-category-action-buttons");
  var linkEl = row.querySelector(".ph-category-item-image a");

  var href = linkEl ? linkEl.getAttribute("href") : "#";
  var imgSrc = imgEl ? imgEl.getAttribute("src") : "";
  var imgAlt = imgEl ? imgEl.alt : "";
  var title = titleEl ? titleEl.textContent.trim() : "";
  var price = priceEl ? priceEl.textContent.trim() : "";
  var priceTxt = priceTxtEl ? priceTxtEl.textContent.trim() : "";

  // Rebuild
  row.style.cssText = "display:flex!important;flex-direction:column!important;align-items:stretch!important;padding:0!important;margin:0!important;";
  row.innerHTML = "";

  // Image
  var imgWrap = document.createElement("a");
  imgWrap.href = href;
  imgWrap.style.cssText = "display:block;overflow:hidden;";
  var img = document.createElement("img");
  img.src = imgSrc;
  img.alt = imgAlt;
  img.style.cssText = "width:100%;height:240px;object-fit:cover;display:block;transition:transform .4s;";
  imgWrap.addEventListener("mouseover",function(){img.style.transform="scale(1.04)";});
  imgWrap.addEventListener("mouseout",function(){img.style.transform="scale(1)";});
  imgWrap.appendChild(img);
  row.appendChild(imgWrap);

  // Content
  var body = document.createElement("div");
  body.style.cssText = "padding:.75rem;text-align:center;display:flex;flex-direction:column;gap:.4rem;";

  var t = document.createElement("h3");
  t.style.cssText = "font-family:Oswald,sans-serif;font-size:1rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#EFEFEF;margin:0;";
  t.textContent = title;
  body.appendChild(t);

  if(price){
    var pt = document.createElement("div");
    pt.style.cssText = "font-family:Oswald,sans-serif;font-size:.65rem;letter-spacing:1.5px;text-transform:uppercase;color:#555;";
    pt.textContent = priceTxt;
    var pv = document.createElement("div");
    pv.style.cssText = "font-family:Metal Mania,cursive;font-size:1.4rem;color:#CC2200;";
    pv.textContent = price;
    body.appendChild(pt);
    body.appendChild(pv);
  }

  if(buttonsEl){
    var btns = buttonsEl.cloneNode(true);
    btns.style.cssText = "display:flex;justify-content:center;gap:.5rem;flex-wrap:wrap;margin-top:.4rem;";
    btns.querySelectorAll(".ph-pull-left,.ph-pull-right").forEach(function(el){
      el.style.cssText = "float:none;display:inline-block;";
    });
    body.appendChild(btns);
  }

  row.appendChild(body);
});

}); // end load
})();
'; }
}
