<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Site\Helper;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper\ShopHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Piezas comunes de las vistas y modulos de la tienda. El HTML y las clases (ph-*, pc-*, hmb-*)
 * son los mismos que daba Phoca Cart con la plantilla HMB, para que el aspecto no cambie.
 */
class Vista
{
    public static function h($s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }

    /** Ruta relativa -> URL absoluta; con $tam (s|m|l) usa miniatura. */
    public static function foto(?string $rel, string $tam = ''): string
    {
        if (!$rel) {
            return '';
        }

        if ($tam !== '') {
            $rel = ShopHelper::miniatura($rel, $tam);
        }

        return Uri::root() . ltrim($rel, '/');
    }

    public static function urlTienda(): string
    {
        return Route::_('index.php?option=com_hmbshop&view=tienda', false);
    }

    public static function urlCategoria(object $c): string
    {
        return Route::_('index.php?option=com_hmbshop&view=categoria&id=' . (int) $c->id . ':' . $c->alias, false);
    }

    public static function urlProducto(object $p, ?int $catid = null): string
    {
        $catid = $catid ?? (int) ($p->categoria_id ?? 0);

        return Route::_('index.php?option=com_hmbshop&view=producto&id=' . (int) $p->id . ':' . $p->alias . '&catid=' . $catid, false);
    }

    public static function urlCheckout(): string
    {
        return Route::_('index.php?option=com_hmbshop&view=checkout', false);
    }

    /** "(+ 3,00 €)" / "(- 3,00 €)" como en las opciones de Phoca. */
    public static function recargo(float $r): string
    {
        return '(' . ($r < 0 ? '- ' : '+ ') . number_format(abs($r), 2, ',', '.') . ' ' . (string) ShopHelper::params()->get('moneda', '€') . ')';
    }

    /** Un producto ¿exige elegir opciones antes de comprar? (algun eje obligatorio con valores) */
    public static function exigeOpciones(int $productoId): bool
    {
        $db = ShopHelper::db();

        return (bool) (int) $db->setQuery('SELECT COUNT(*) FROM #__hmbshop_ejes e WHERE e.producto_id = ' . $productoId . ' AND e.obligatorio = 1 AND e.id IN (SELECT eje_id FROM #__hmbshop_eje_valores)')->loadResult();
    }

    /** Caja del carrito (modulo lateral). */
    public static function miniCarrito(): string
    {
        $d      = ShopHelper::detalleCarrito();
        $url    = self::h(self::urlCheckout());
        $enlace = '<div class="ph-small ph-right ph-u ph-cart-link-checkout"><a href="' . $url . '">Ver carrito - finalizar compra</a></div>';

        if (!$d['lineas']) {
            return '<div class="ph-cart-module-box "><div class="phItemCartBox" id="phItemCartBox"><div>El carrito está vacío</div>' . $enlace . '</div></div>';
        }

        $n    = array_sum(array_map(fn($l) => $l->cantidad, $d['lineas']));
        $html = '<div class="ph-cart-module-box "><div class="phItemCartBox" id="phItemCartBox">'
            . '<div class="ph-small">' . (int) $n . ' Artículo(s)</div><table class="ph-cart-small-box"><tbody>';

        foreach ($d['lineas'] as $l) {
            $attrs = '';

            foreach ($l->atributos as [$k, $v]) {
                $attrs .= '<li><span class="ph-small ph-cart-small-attribute">' . self::h($k . ' ' . $v) . '</span></li>';
            }

            $enlaceProd = Route::_('index.php?option=com_hmbshop&view=producto&id=' . (int) $l->producto_id . ':' . $l->producto_alias . '&catid=' . (int) $l->categoria_id, false);
            $html .= '<tr><td class="ph-cart-small-quantity">' . (int) $l->cantidad . 'x</td>'
                . '<td class="ph-cart-small-title"><a href="' . self::h($enlaceProd) . '">' . self::h($l->nombre) . '</a></td>'
                . '<td class="ph-cart-small-price ph-right">' . self::h(ShopHelper::dinero($l->subtotal)) . '</td></tr>'
                . ($attrs !== '' ? '<tr><td></td><td colspan="2"><ul>' . $attrs . '</ul></td></tr>' : '');
        }

        $html .= '<tr><td colspan="3"><div class="ph-hr"></div></td></tr>'
            . '<tr class="ph-cart-subtotal-box"><td colspan="2" class="ph-cart-subtotal-netto-txt">Subtotal</td><td class="ph-right ph-cart-subtotal-netto">' . self::h(ShopHelper::dinero($d['total'])) . '</td></tr>'
            . '<tr class="ph-cart-total-box"><td colspan="2" class="ph-cart-total-txt">Total</td><td class="ph-right ph-cart-total">' . self::h(ShopHelper::dinero($d['total'])) . '</td></tr>'
            . '</tbody></table>' . $enlace . '</div></div>';

        return $html;
    }
}
