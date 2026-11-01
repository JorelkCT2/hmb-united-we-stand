# SEO — heavymetalbrothers.com

Rama `seo`. Auditoría hecha el 2026-11-01 sobre producción (curl + navegador, sin tocar la BD). Dominio canónico: **https://www.heavymetalbrothers.com** (el dominio sin www y http ya redirigen con 301).

## 1. Auditoría (estado inicial)

| Área | Hallazgo | Impacto |
|---|---|---|
| Sitemap | `/sitemap.xml` era un fichero estático de **mayo**, con URLs **sin www** y con `/index.php/...` (todas redirigen) + `HMBSiteMap.xml` (JLSitemap, octubre) distinto. Ninguno enlazado desde robots.txt | Alto |
| robots.txt | Sin línea `Sitemap:`; no bloqueaba fichas modales, filtros ni acciones | Medio |
| Canonical | Ya existía (plugin `hmb_seo`), www y solo parámetros de contenido. OK | — |
| Redirecciones | http→https, sin-www→www, `/index.php/x`→`/x`, `/x/`→`/x`: todo 301 correcto | — |
| 404 | Páginas inexistentes devuelven 404 real (no hay soft-404) | — |
| Títulos | Únicos y con marca, pero las fichas de banda/músico mostraban el del listado ("Archivo de bandas"); concierto = solo el nombre de la banda; dobles espacios | Alto |
| Meta description | **Repetida**: ~25 de las 681 URL indexables compartían 5 textos genéricos; fichas de banda, músico, concierto y categorías sin descripción propia | Alto |
| H1 | Uno por página (el plugin ya normaliza) | — |
| Open Graph / Twitter | **Casi inexistentes**: solo `og:site_name` y `og:title`. El plugin `hmb_opengraph` no inyecta nada en producción (probablemente desactivado). Sin `og:image`, `og:url` ni `twitter:card` → mal aspecto al compartir. En artistas `og:url` apuntaba al listado sin id | Alto |
| JSON-LD | Solo Joomla (Organization/WebSite/WebPage/`Article` pobre: sin fechas, autor ni imagen) en algunas páginas; portada, bandas, músicos, conciertos sin nada; ficha de artista con `url` sin id y e-mail público | Alto |
| Imágenes | Todas con `alt` (el plugin lo deduce) y 63 `loading=lazy` en portada. Faltan `width/height` en 48 de 70 (riesgo de CLS), 0 WebP, 15 imágenes de portada son **hotlinks** (YouTube, Amazon, fbcdn, gstatic) que caducan | Medio |
| Rendimiento | CSS/JS propios **sin gzip y sin caché** (`Cache-Control` ausente) → recargas innecesarias. HTML sí va con gzip. 25 scripts + 15 hojas CSS en portada, HTML de 324 KB, 5 familias de Google Fonts bloqueando el render. **GTM + gtag duplicados** en la plantilla | Alto |
| Idioma | `<html lang="es-es">`; un solo idioma → sin hreflang | — |
| Enlaces rotos | En el sitemap actual: 0 de 681. Pendiente rastreo completo de enlaces salientes | — |
| Duplicados | Dos entradas de menú para la agenda (`/agenda?view=concerts` y `/agenda/agenda-de-conciertos?view=concerts`) con el mismo contenido | Medio |
| Fichas antiguas de artistas | Artículos de la categoría 19 (`/component/content/article/...-hmb-NN?catid=19`) y `.../noticias-de-nuestros-artistas/...-nN` devuelven **404** aunque estén "publicados" | Medio |

## 2. Cambios hechos (en la rama `seo`, desplegados)

Todo vive en `src/plugins/system/hmb_seo/hmb_seo.php` (v2.0.0). Desplegado solo ese plugin (copias previas en `tmp/hmb_backups/` del servidor).

- **Sitemap dinámico** en `/sitemap-hmb.xml` (y en `/sitemap.xml` cuando se retire el estático): portada, páginas de menú públicas, artículos publicados, bandas, músicos, artistas y conciertos vigentes. **678 URL, todas verificadas con 200, sin redirección y con canónica = la propia URL**. Excluye registro/login, búsquedas, noindex, entradas de menú duplicadas y artículos con 404.
- **robots.txt** (subido): `Sitemap:` + `Disallow` de modales, búsquedas, filtros y acciones. Copia previa en `tmp/hmb_backups/seo/`.
- **Títulos propios** en fichas de banda ("Anthrax – banda de Thrash Metal (Estados Unidos)"), músico y concierto ("Tankard en concierto en Zaragoza – 01/11/2026").
- **Meta descriptions únicas**: artículos (intro), categorías, conciertos, bandas, músicos y listados; 0 repetidas en el sitemap.
- **Open Graph + Twitter completos** (`og:url`=canónica, `og:type`, `og:locale`, `og:description`, `og:image`, `twitter:*`) con imagen del contenido o, si no, el logo. Se respeta lo que ya trae cada componente.
- **JSON-LD**: `WebSite`+`SearchAction` (buscador de Joomla) y `Organization` en todas las páginas; `BreadcrumbList` en todas menos portada; `Article`→`NewsArticle` con fechas, autor, sección, imagen y editor; `MusicEvent` (conciertos), `MusicGroup` (bandas, con miembros), `Person` (músicos, con nacimiento/defunción); ficha de artista con URL correcta y sin e-mail. Las frases (`Quotation`) ya las emite la rama `frases`. Verificado con validator.schema.org: **concierto = 0 errores / 0 avisos** (MusicEvent, BreadcrumbList, WebSite); el resto de tipos se comprobó que el JSON es válido y con los campos obligatorios, pero conviene repetir la prueba (ver §4).
- `noindex, follow` en fichas modales, búsquedas, filtros, `task=` y cuentas.
- Hoja de Google Fonts cargada sin bloquear el primer pintado (`preload` + `onload`, con `<noscript>`).
- **Error corregido durante el trabajo**: un primer despliegue añadía enlaces `hreflang` que pisaban la canónica (Joomla indexa los `<link>` por href). Estuvo ~3 minutos en producción; ya revertido (no hay hreflang; al ser un sitio monolingüe no hace falta).

## 3. Pendientes (ordenados por impacto)

1. **Aplicar `.htaccess`** (yo no pude escribir en el servidor con esa operación): añadir el contenido de [`htaccess-seo.conf`](htaccess-seo.conf) al final → gzip de CSS/JS/JSON y caché de 1 mes para CSS/JS/imágenes. Copia hecha en `tmp/hmb_backups/seo/.htaccess.bak` (la original sigue sin tocar). Verificar después con `curl -sI -H "Accept-Encoding: gzip" https://www.heavymetalbrothers.com/templates/hmb/css/hmb-metal.css`.
2. **Retirar el sitemap viejo**: `rm clickandbuilds/HMBNEW/sitemap.xml sitemap.json sitemap_urlset.xsl` (copia en `tmp/hmb_backups/seo/`). Así `/sitemap.xml` pasa a ser el dinámico. Opcional: desactivar la tarea de JLSitemap que genera `HMBSiteMap.xml`.
3. **Plantilla** (rama `template`, no la toqué para no pisarla): quitar uno de los dos códigos de analítica (GTM `GTM-NR9JTB73` y gtag `G-XXP1DXHHLN` están ambos), `width/height` en imágenes, `fetchpriority="high"` + sin lazy en la primera imagen del slider (LCP), reducir a 2–3 familias de fuentes (hay 5) y servirlas en local.
4. **Hotlinks** de imágenes de noticias (YouTube/Amazon/fbcdn/gstatic): descargarlas a `images/` al importar la noticia (cambio en `hmbnews`/BD → proponer antes de ejecutar). Las de fbcdn caducan y dejan la noticia sin imagen en Google.
5. **WebP**: convertir `images/` pesadas (galería 281 imágenes, obituarios 122) — necesita script y copia previa.
6. **Menús**: unificar `/agenda` y `/agenda/agenda-de-conciertos` (301 de uno a otro) y crear menú/redirección para la categoría 19 de fichas de artistas o despublicarlas; arreglar `&amp;amp;` guardado en el enlace de los menús "Noticias de artistas".
7. **Frases** (`/frases-miticas/NNN-slug`, ~56 URL): añadirlas al sitemap cuando la rama `frases` esté fusionada y se conozca su función de slug.
8. **Paginación**: `start` ya forma parte de la canónica; revisar si los listados de noticias paginan y añadir `rel=prev/next` es opcional (Google ya no lo usa).
9. Rastreo completo de enlaces salientes rotos (herramienta externa tipo Screaming Frog / Search Console cuando haya datos).
10. `og:image` por defecto: crear una imagen 1200×630 propia y ponerla en el parámetro `fallback_image` (ahora se usa el logo cuadrado).

## 4. Cómo comprobar

```bash
curl -s https://www.heavymetalbrothers.com/sitemap-hmb.xml | head
curl -s https://www.heavymetalbrothers.com/robots.txt | tail -3
```
- Datos estructurados: https://validator.schema.org (pestaña URL, una página por prueba) y https://search.google.com/test/rich-results (noticia, concierto).
- Compartir: https://developers.facebook.com/tools/debug/ y el validador de tarjetas de X.

## 5. Checklist Search Console / Bing / Analytics (lo hace el propietario)

**Google Search Console** (https://search.google.com/search-console)
1. Añadir propiedad → **Prefijo de URL** `https://www.heavymetalbrothers.com/` (o Dominio `heavymetalbrothers.com` con registro TXT en IONOS DNS: es mejor porque agrupa www/no-www/http).
2. Verificar: método *Etiqueta HTML* → copiar el `<meta name="google-site-verification" content="...">` y pegarlo en Sistema → Configuración global → Metadatos → *Contenido extra del head* (o pedírmelo y lo meto en el plugin); o *Fichero HTML* → subirlo a la raíz de HMBNEW. Si ya usas Analytics/GTM del mismo Google, vale la verificación automática por GTM (`GTM-NR9JTB73`).
3. Sitemaps → añadir `sitemap-hmb.xml` (y `sitemap.xml` tras retirar el viejo). Debe salir "Correcto" con ~678 URL.
4. Inspección de URL → probar portada, una noticia, una banda y un concierto → "Solicitar indexación".
5. Páginas → comprobar en 1–2 semanas las excluidas (noindex esperados: modales, búsquedas, cuentas).
6. Mejoras → *Migas de pan*, *Eventos*, *Artículos*: revisar errores. Experiencia → *Core Web Vitals* y *Móvil*.
7. Configuración → *Cambio de dirección* no hace falta (mismo dominio). Vincular con Analytics.

**Bing Webmaster Tools** (https://www.bing.com/webmasters): "Importar desde Google Search Console" (1 clic) y enviar el mismo sitemap.

**Google Analytics 4** (`G-XXP1DXHHLN`): comprobar en Tiempo real que llega tráfico; dejar **un solo** código (GTM *o* gtag) para no duplicar sesiones; marcar conversiones (registro, envío de contacto, partida de MetalQuiz).

**Otros**: Google Business Profile no aplica; dar de alta el perfil de Instagram/YouTube/Facebook y añadirlos a `sameAs` del `Organization` (ahora solo Instagram).
