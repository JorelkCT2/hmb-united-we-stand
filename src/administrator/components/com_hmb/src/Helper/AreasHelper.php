<?php
namespace HeavyMetalBrothers\Component\Hmb\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Mapa de áreas de administración de HMB.
 *
 * Es la única fuente de verdad del menú "HMB" y del panel: cada entrada apunta a
 * la vista de administración que ya existe en su componente (no se mueven tablas
 * ni URLs). 'tabla' es opcional y solo sirve para mostrar un contador.
 */
class AreasHelper
{
    public static function areas(): array
    {
        return [
            'noticias' => [
                'titulo' => 'Noticias',
                'icono'  => 'icon-newspaper',
                'items'  => [
                    ['Artículos', 'Todas las noticias publicadas en la web.', 'option=com_content&view=articles', '#__content'],
                    ['Importador de noticias', 'Fuentes externas y noticias importadas.', 'option=com_hmbnews', '#__hmb_imported'],
                    ['Noticias de grupos', 'Noticias enviadas por las bandas.', 'option=com_artistas&view=noticias', '#__artistas_noticias'],
                ],
            ],
            'musica' => [
                'titulo' => 'Música',
                'icono'  => 'icon-music',
                'items'  => [
                    ['Artistas', 'Directorio de bandas y artistas.', 'option=com_artistas&view=artistas', '#__artistas'],
                    ['Reseñas', 'Opiniones de los usuarios sobre las bandas.', 'option=com_artistas&view=resenas', '#__artistas_resenas'],
                    ['Presupuestos', 'Solicitudes de presupuesto a las bandas.', 'option=com_artistas&view=presupuestos', '#__artistas_presupuestos'],
                    ['Temas y discos', 'Nuestros temas y discos.', 'option=com_hmbtemas', '#__hmb_temas'],
                    ['Efemérides', 'Efemérides, fallecidos, frases y músicos.', 'option=com_efemerides&view=cpanel', '#__efemerides'],
                ],
            ],
            'agenda' => [
                'titulo' => 'Agenda',
                'icono'  => 'icon-calendar',
                'items'  => [
                    ['Agenda y mapa', 'Conciertos y festivales: alimentan la agenda y el mapa de conciertos.', 'option=com_concerts&view=concerts', '#__concerts'],
                    ['Importar y actualizar', 'Fuentes automáticas, CSV, direcciones web, duplicados y mapa.', 'option=com_concerts&view=import', ''],
                    ['Festival', 'Gestión del festival.', 'option=com_hmb_festival', ''],
                ],
            ],
            'multimedia' => [
                'titulo' => 'Multimedia',
                'icono'  => 'icon-images',
                'items'  => [
                    ['Fotos', 'Álbumes de fotos.', 'option=com_hmgallery', '#__hmgallery_albums'],
                    ['Vídeos', 'Vídeos y carpetas.', 'option=com_hmb_videos&view=videos', '#__hmb_videos'],
                    ['Archivos', 'Gestor de archivos de images/ y media/ (solo super administradores).', 'option=com_hmbfiles', ''],
                ],
            ],
            'comunidad' => [
                'titulo' => 'Comunidad',
                'icono'  => 'icon-users',
                'items'  => [
                    ['Metal Quiz', 'Canciones, películas, puntuaciones y jugadores.', 'option=com_hmb_quiz&view=songs', '#__hmb_quiz_players'],
                    ['Usuarios', 'Usuarios registrados en la web.', 'option=com_users&view=users', '#__users'],
                ],
            ],
            'tienda' => [
                'titulo' => 'Tienda',
                'icono'  => 'icon-cart',
                'items'  => [
                    ['Productos', 'Catálogo con tallas, colores y fotos.', 'option=com_hmbshop&view=productos', '#__hmbshop_productos'],
                    ['Pedidos', 'Pedidos recibidos y su estado.', 'option=com_hmbshop&view=pedidos', '#__hmbshop_pedidos'],
                    ['Categorías', 'Categorías de la tienda.', 'option=com_hmbshop&view=categorias', '#__hmbshop_categorias'],
                    ['Formas de envío', 'Recogida y envíos.', 'option=com_hmbshop&view=envios', '#__hmbshop_envios'],
                    ['Ajustes de la tienda', 'Formas de pago, IVA y stock.', 'option=com_config&view=component&component=com_hmbshop', ''],
                ],
            ],
            'sitio' => [
                'titulo' => 'Sitio',
                'icono'  => 'icon-cog',
                'items'  => [
                    ['Menús', 'Menús y enlaces de la web.', 'option=com_menus&view=menus', ''],
                    ['Módulos', 'Módulos de la portada y las páginas.', 'option=com_modules&view=modules&client_id=0', ''],
                    ['Plantilla', 'Estilos de la plantilla HMB.', 'option=com_templates&view=styles&client_id=0', ''],
                    ['Mapa del sitio', 'Generación del sitemap.', 'option=com_jlsitemap', ''],
                ],
            ],
        ];
    }

    /** Contador de filas de una tabla; null si la tabla no existe o falla la consulta. */
    public static function contar(string $tabla): ?int
    {
        if ($tabla === '') {
            return null;
        }

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $q  = $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName($tabla));

            return (int) $db->setQuery($q)->loadResult();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
