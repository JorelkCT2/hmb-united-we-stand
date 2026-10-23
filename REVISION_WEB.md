# Revisión de la web (23/10/2026)

Método: rastreo de las 877 URLs del sitemap, recorrido en navegador (escritorio y móvil, consola y red), análisis del CSS de plantilla y extensiones.

## A. Errores reales
| # | Qué | Dónde |
|---|---|---|
| A1 | HTTP **500** | `/promo/musicos-buscando-banda?view=musicos` (la página de error muestra "0 - Heavy Metal Brothers") |
| A2 | HTTP **404** enlazados desde el sitemap | `/noticias/noticias-de-nuestros-artistas/-n4`, `…festival-zurbaran-rock-n6`, `…sugoi-eden-blackhearth-n5`, `/noticias/discos/metallica-anuncia-hardwired…`, `/component/tags/tag/1997` |
| A3 | Imágenes rotas (src vacío) | p. ej. 2 en `/noticias/actualidad/dimmu-borgir-han-vuelto` |
| A4 | Banner de portada recorta el logo ("AVY METAL") en escritorio; en móvil el banner Metal Quiz sale cortado ("TAL QU") | portada |
| A5 | 4 controladores de com_artistas con errores de sintaxis | **ya corregido y desplegado** |

## B. HTML / SEO sin criterio común
- **H1**: la portada y `/noticias` no tienen; las noticias tienen **2** (título + subtítulo); 10 páginas con varios.
- **`<meta viewport>` duplicado** en las noticias.
- **Canonical**: falta en 870 de 871 páginas. `www.` y sin `www` responden 200 los dos (contenido duplicado, sin redirección).
- **Sitemap** (`HMBSiteMap.xml`, de junio): 754 de 877 URLs son etiquetas (`/component/tags/tag/…`, contenido fino) y hay 5 URLs que ya dan 404.
- 88 imágenes sin `alt` (16 solo en una noticia). 49 títulos de más de 70 caracteres.

## C. Estilos sin unificar
- **CSS duplicado**: `template.css` = `hmb-base.css` y `modules.css` = `hmb-modules.css` (idénticos); `layout.css` es una versión vieja de `hmb-layout.css`. `templateDetails.xml` lista los viejos; `index.php` carga solo los `hmb-*`. Restos: `index2.php`, `product_image_OLD.php`.
- **Todo inline**: `index.php` pesa 64 KB, con 35 KB de `<style>` + otro bloque + 15 `style=""`; la portada carga 14 hojas inline además de las externas. 45 bloques `<style>` repartidos por PHP de extensiones.
- **597 `!important`** en la plantilla activa (466 `var(--…)` en uso, pero sin que todo pase por variables).
- **Colores**: 48 en la plantilla, **182 hex distintos** en los CSS de extensiones (verdes, azules, naranjas sueltos frente al rojo/negro de marca).
- **Tipografías**: marca = Oswald, Rajdhani, Metal Mania, Cinzel; se cuelan Arial, Raleway, Segoe UI, system-ui, Courier New, Georgia.

## D. Seguridad / operación
- 32+ intentos de acceso fallidos al administrador desde las mismas IP (`208.77.246.9`, …). Conviene limitar `/administrator` por IP o añadir 2FA.
- `debug` está apagado en HMBNEW (bien), pero por eso el 500 de A1 no deja rastro en logs.

## Propuesta por fases
1. **Rápida y segura**: borrar CSS/archivos huérfanos; unificar `<head>` (un solo viewport, canonical, redirección www→apex en `.htaccess`); H1 único por página; `alt` en imágenes; regenerar sitemap sin tags; corregir A1–A4.
2. **Sistema de diseño**: una única tabla de variables (colores, fuentes, espaciados) en `hmb-base.css`; mover el `<style>` de `index.php` a hojas; sustituir colores sueltos de las extensiones por variables.
3. **Contenido**: revisar las páginas con enlaces rotos y los títulos largos.

## Estado tras la fase 1 (23/10/2026)
Hecho y verificado: 500 de músicos (módulo quiz), H1/viewport/canonical únicos, CSS y archivos huérfanos fuera, página de error sin mensajes internos, redirección heavymetalbrothers.com → www (301), sitemap regenerado sin `/component/` (214 URLs, 211 con 200).
Pendiente: 3 URLs 404 de noticias de artistas (`…/noticias-de-nuestros-artistas/-n4`, `-n5`, `-n6`: slug `-nID` que no resuelve; el generador está fuera de com_artistas), 137 imágenes sin `alt`, banner de portada recortado, fase 2 (sistema de diseño).
Copias en el servidor: `tmp/hmb_backups/` (htaccess_20261023, sitemap_20261023.tar.gz, jlsitemap_params_20261023.json, huerfanos_20261023.tar.gz).
