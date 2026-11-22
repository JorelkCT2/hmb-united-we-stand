<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class PlgSystemHmb_seo extends CMSPlugin
{
    private const SITE = 'https://www.heavymetalbrothers.com';

    /** Textos genéricos que Joomla pone cuando una página no tiene descripción propia. */
    private const GENERIC_DESC = ['desde Cartagena para el Mundo', 'somos dos apasionados del Rock', 'Categorías de las noticias'];

    /** Descripción por vista para los listados (los detalles se construyen con datos de la BD). */
    private const VIEW_DESC = [
        'concerts' => 'Agenda de conciertos y festivales de rock y heavy metal en España: fechas, salas, ciudades, entradas y carteles.',
        'posters'  => 'Carteles de conciertos y festivales de rock y metal: galería de cartelería con fechas y bandas.',
        'map'      => 'Mapa interactivo de conciertos y festivales de metal: encuentra qué bandas tocan cerca de ti.',
        'bandas'   => 'Archivo de bandas de rock y heavy metal: miembros, género, país, efemérides y próximos conciertos.',
        'musicos'  => 'Archivo de músicos del rock y el metal: biografía, instrumento, bandas, nacimiento y fallecimiento.',
        'artistas' => 'Directorio de bandas y artistas de rock y metal emergentes: escúchalos, conoce sus conciertos y contáctalos.',
        'lista'    => 'Efemérides del rock y el heavy metal: discos, conciertos, nacimientos y hechos históricos día a día.',
        'fallecidos' => 'Obituario del rock y el metal: homenaje a los músicos que nos han dejado, con fecha y lugar.',
        'efemerides' => 'Efemérides del rock y el heavy metal: qué pasó un día como hoy en la historia del metal.',
        'gallery'  => 'Galería de fotos de conciertos de rock y heavy metal hechas por Heavy Metal Brothers.',
        'temas'    => 'Nuestros temas: canciones y música propia de Heavy Metal Brothers.',
        'videos'   => 'Vídeos de conciertos, entrevistas y música de Heavy Metal Brothers.',
        'discos'   => 'Discos perfectos del rock y el heavy metal: álbumes imprescindibles seleccionados por Heavy Metal Brothers.',
        'noticias' => 'Noticias de las bandas y artistas emergentes del rock y el metal: lanzamientos, giras y novedades.',
        'com_artistas.musicos' => 'Músicos que buscan banda y bandas que buscan músico: anuncios del rock y el metal para formar o completar tu grupo.',
        'game'     => 'Metal Quiz: pon a prueba lo que sabes de metal con preguntas de bandas, discos, años y miembros.',
    ];

    /**
     * Título y descripción a mano para las páginas con muchas impresiones y poco CTR (Search Console, 2026-11).
     * Clave: ruta exacta. Solo datos comprobables; el resto lo deducen las reglas de arriba.
     */
    private const OVERRIDES = [
        '/metal-quiz' => [
            'Metal Quiz: test de heavy metal online gratis',
            'Test de metal: preguntas sobre bandas, discos, años y miembros del rock y el heavy metal. Juega gratis y mide cuánto sabes.',
        ],
        '/noticias/obituarios/la-muerte-de-dee-dee-ramone-el-adios-tragico-al-alma-salvaje-de-los-ramones' => [
            '¿De qué murió Dee Dee Ramone? Su muerte en 2002',
            'Dee Dee Ramone, bajista y compositor de Ramones, murió el 5 de junio de 2002 en Hollywood. Repasamos su vida, su legado en el punk y las circunstancias de su muerte.',
        ],
        '/noticias/obituarios/muere-vinnie-paul-bateria-de-pantera-y-figura-esencial-del-metal-moderno' => [
            'Muere Vinnie Paul, batería de Pantera y Damageplan',
            'Vinnie Paul, batería de Pantera y Damageplan y hermano de Dimebag Darrell, murió el 22 de junio de 2018 a los 54 años. Su vida, su legado y su huella en el metal.',
        ],
        '/noticias/curiosidades/christopher-lee-el-actor-de-saruman-que-sorprendio-al-mundo-con-sus-discos-de-heavy-metal' => [
            'Christopher Lee: el actor de Saruman que grabó heavy metal',
            'El actor que fue Saruman y Drácula grabó discos de heavy metal sinfónico, como Charlemagne: By the Sword and the Cross. Así fue su faceta metalera.',
        ],
    ];

    /** Sitemap dinámico en /sitemap-hmb.xml (y en /sitemap.xml cuando se retire el fichero estático obsoleto). */
    public function onAfterInitialise()
    {
        $app = Factory::getApplication();

        if (!$app->isClient('site')) {
            return;
        }

        $path = rtrim(Uri::getInstance()->getPath(), '/');

        if (!in_array($path, ['/sitemap.xml', '/sitemap-hmb.xml'], true)) {
            return;
        }

        try {
            $xml = $this->buildSitemap();
        } catch (\Throwable $e) {
            return;
        }

        header('Content-Type: application/xml; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        header('X-Robots-Tag: noindex');
        echo $xml;
        $app->close();
    }

    public function onBeforeRender()
    {
        $app = Factory::getApplication();

        if (!$app->isClient('site')) {
            return;
        }

        $document = $app->getDocument();

        if ($document->getType() !== 'html') {
            return;
        }

        // Leer parámetros configurables (con valores por defecto)
        $siteName  = trim($this->params->get('site_name', 'Heavy Metal Brothers'));
        $homeTitle = trim($this->params->get('home_title', 'Heavy Metal Brothers - Rock y Heavy Metal'));
        $homeDesc  = trim($this->params->get('home_description', ''));
        $separator = $this->params->get('separator', ' | ');
        $fixOgTags = (bool) $this->params->get('fix_og_tags', 1);

        // Detectar portada
        $currentUrl = rtrim(Uri::current(), '/');
        $baseUrl    = rtrim(Uri::base(), '/');
        $isHome     = ($currentUrl === $baseUrl);

        if ($isHome) {
            $document->setTitle($homeTitle ?: $siteName);
            if ($homeDesc && !$document->getDescription()) {
                $document->setDescription($homeDesc);
            }
        } else {
            $own = $this->ownTitle($app);

            if ($own !== '') {
                $document->setTitle($own);
            }

            $currentTitle = trim(preg_replace('/\s+/u', ' ', (string) $document->getTitle()));
            $currentTitle = preg_replace('/\s+\|/u', ' |', $currentTitle);
            if ($currentTitle && strpos($currentTitle, $siteName) === false) {
                $document->setTitle($currentTitle . $separator . $siteName);
            } elseif ($currentTitle) {
                $document->setTitle($currentTitle);
            } else {
                $document->setTitle($siteName);
            }
        }

        // Descripción propia en las páginas que solo tienen el texto genérico
        if (!$isHome) {
            $desc = trim((string) $document->getDescription());
            $generic = ($desc === '');

            foreach (self::GENERIC_DESC as $g) {
                if (!$generic && stripos($desc, $g) !== false) {
                    $generic = true;
                }
            }

            if ($generic) {
                $new = $this->describe($app);

                if ($new !== '') {
                    $document->setDescription($new);
                }
            }
        }

        // Canonical único: dominio con www, solo la ruta y los parámetros que definen el contenido.
        $hasCanonical = false;

        foreach (($document->getHeadData()["links"] ?? []) as $href => $attribs) {
            if (($attribs['relation'] ?? '') === 'canonical') {
                $hasCanonical = true;
                break;
            }
        }

        $uri   = Uri::getInstance();
        $keep  = ['view', 'id', 'artista_id', 'catid', 'start', 'layout'];
        $query = array_intersect_key($uri->getQuery(true), array_flip($keep));
        $url   = self::SITE . $uri->getPath();

        // Fichas de banda/músico: una sola URL canónica (la del archivo), aunque cuelguen de otras rutas de menú
        $in0 = $app->getInput();

        if ($in0->get('option') === 'com_efemerides' && in_array($in0->get('view'), ['banda', 'musico'], true) && $in0->getInt('id', 0)) {
            try {
                $db0 = Factory::getContainer()->get('DatabaseDriver');
                $list = $in0->get('view') === 'banda' ? 'bandas' : 'musicos';
                $db0->setQuery("SELECT id FROM #__menu WHERE published = 1 AND client_id = 0 AND access = 1 AND link LIKE "
                    . $db0->quote('index.php?option=com_efemerides&view=' . $list . '%') . ' ORDER BY id LIMIT 1');
                $mid = (int) $db0->loadResult();

                if ($mid) {
                    $base  = strtok(html_entity_decode(Route::_('index.php?Itemid=' . $mid)), '?');
                    $query = ['view' => $in0->get('view'), 'id' => $in0->getInt('id', 0)];
                    $url   = $this->abs($base);
                }
            } catch (\Throwable $e) {
            }
        }

        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        if (!$hasCanonical) {
            $document->addHeadLink(htmlspecialchars($url), 'canonical');
        }

        // Retoques a mano de las páginas con más impresiones (la clave es la ruta sin barra final)
        $ov = self::OVERRIDES[rtrim($uri->getPath(), '/')] ?? null;

        if ($ov) {
            $document->setTitle($ov[0] . $separator . $siteName);
            $document->setDescription($ov[1]);
        }

        // Páginas sin valor para el buscador: filtros, búsquedas, acciones, cuentas
        $in = $app->getInput();

        if ($in->getInt('hmbmodal', 0) === 1 || $in->get('task') || $in->get('tmpl') || $in->get('search') !== null
            || $in->get('genero') !== null || $in->get('q') !== null || in_array($in->get('option'), ['com_users', 'com_hmb'], true)) {
            $document->setMetaData('robots', 'noindex, follow');
        }

        // Google Discover y fragmentos con imagen grande: sin tocar lo que ya sea noindex
        if (!(string) $document->getMetaData('robots')) {
            $document->setMetaData('robots', 'max-image-preview:large, max-snippet:-1, max-video-preview:-1');
        }

        // Corregir OG tags si está activado
        if ($fixOgTags) {
            $document->setMetaData('og:site_name', $siteName, 'property');
            $document->setMetaData('og:title', $document->getTitle(), 'property');
        }
    }

    /**
     * Un único <h1> por página, alt en las imágenes, JSON-LD enriquecido y fuentes sin bloquear el pintado.
     */
    public function onAfterRender()
    {
        $app = Factory::getApplication();

        if (!$app->isClient('site') || $app->getDocument()->getType() !== 'html') {
            return;
        }

        $body = $app->getBody();
        $n    = 0;

        $body = preg_replace_callback('#<h1(\s[^>]*)?>(.*?)</h1>#si', function ($m) use (&$n) {
            $n++;

            return $n === 1 ? $m[0] : '<h2' . ($m[1] ?? '') . '>' . $m[2] . '</h2>';
        }, $body);

        if ($n === 0) {
            $title = trim(preg_replace('#\s*[|\-–]\s*Heavy Metal Brothers.*$#iu', '', $app->getDocument()->getTitle()));
            $h1    = '<h1 style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap">'
                . htmlspecialchars($title ?: 'Heavy Metal Brothers', ENT_QUOTES, 'UTF-8') . '</h1>';
            $body  = preg_replace_callback('#<body[^>]*>#i', static function ($m) use ($h1) {
                return $m[0] . $h1;
            }, $body, 1);
        }

        // <img> sin alt: se deduce del nombre del fichero ("DIMMU BORGIR - 5.jpg" -> "DIMMU BORGIR");
        // las que no tienen src (marcadores de la lightbox) quedan como decorativas.
        $body = preg_replace_callback('#<img(?![^>]*\balt=)([^>]*)>#i', static function ($m) {
            $alt = '';

            if (preg_match('#\bsrc="([^"]+)"#i', $m[1], $s) && $s[1] !== '') {
                $name = rawurldecode(pathinfo(parse_url($s[1], PHP_URL_PATH) ?: '', PATHINFO_FILENAME));
                $name = trim(preg_replace('#[\s_\-]+(\d+|copia|copy)$#i', '', $name));
                $alt  = trim(preg_replace('#[\s_]+#', ' ', $name));
            }

            return '<img alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '"' . $m[1] . '>';
        }, $body);

        try {
            $body = $this->jsonLd($app, $body);
        } catch (\Throwable $e) {
            // el JSON-LD nunca debe romper la página
        }

        try {
            $body = $this->social($app, $body);
        } catch (\Throwable $e) {
        }

        // Hoja de fuentes de Google: se carga sin bloquear el primer pintado (display=swap ya evita texto invisible)
        $body = preg_replace_callback(
            '#<link href="(https://fonts\.googleapis\.com/css2[^"]+)" rel="stylesheet">#i',
            static function ($m) {
                return '<link rel="preload" as="style" href="' . $m[1] . '" onload="this.onload=null;this.rel=\'stylesheet\'">'
                    . '<noscript><link href="' . $m[1] . '" rel="stylesheet"></noscript>';
            },
            $body
        );

        $app->setBody($body);
    }

    // ------------------------------------------------------------------ helpers

    private function abs(string $u): string
    {
        $u = trim($u);

        if ($u === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $u)) {
            return $u;
        }

        return self::SITE . '/' . ltrim($u, '/');
    }

    private function clean(?string $html, int $max = 160): string
    {
        $t = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $t = trim(preg_replace('/\s+/u', ' ', $t));

        if (mb_strlen($t) > $max) {
            $t = rtrim(mb_substr($t, 0, $max - 1), " ,;:.-") . '…';
        }

        return $t;
    }

    private function fechaEs(string $d): string
    {
        $t = strtotime($d);

        if (!$t) {
            return '';
        }

        $m = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

        return (int) date('j', $t) . ' de ' . $m[(int) date('n', $t) - 1] . ' de ' . date('Y', $t);
    }

    /** Título propio de las fichas que comparten el del listado (banda, músico, concierto). */
    private function ownTitle($app): string
    {
        $in   = $app->getInput();
        $opt  = $in->get('option', '');
        $view = $in->get('view', '');
        $id   = $in->getInt('id', 0);

        if (!$id) {
            return '';
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        try {
            if ($opt === 'com_efemerides' && $view === 'banda') {
                $db->setQuery('SELECT nombre, genero, pais FROM #__efemerides_bandas WHERE id = ' . $id . ' AND state = 1');
                $b = $db->loadObject();

                return $b ? trim($b->nombre . ': banda' . ($b->genero ? ' de ' . $b->genero : ' de rock y metal') . ' – miembros e historia') : '';
            }

            if ($opt === 'com_efemerides' && $view === 'musico') {
                $db->setQuery('SELECT nombre, banda_texto, fecha_nacimiento, fecha_defuncion FROM #__efemerides_musicos WHERE id = ' . $id . ' AND state = 1');
                $m = $db->loadObject();

                if (!$m) {
                    return '';
                }

                $born = ($m->fecha_nacimiento && $m->fecha_nacimiento > '0001') ? substr((string) $m->fecha_nacimiento, 0, 4) : '';
                $died = ($m->fecha_defuncion && $m->fecha_defuncion > '0001') ? substr((string) $m->fecha_defuncion, 0, 4) : '';
                $span = $died ? ($born ? '(' . $born . '–' . $died . ')' : '(† ' . $died . ')') : ($born ? '(n. ' . $born . ')' : '');

                return trim($m->nombre . ' ' . $span . ': biografía' . ($m->banda_texto ? ' y carrera en ' . $m->banda_texto : ' y carrera'));
            }

            if ($opt === 'com_concerts' && $view === 'concert') {
                $db->setQuery('SELECT title, band, city, date_start FROM #__concerts WHERE id = ' . $id . ' AND state = 1');
                $c = $db->loadObject();

                if ($c) {
                    $t = strtotime((string) $c->date_start);

                    return trim(($c->title ?: $c->band) . ' en concierto' . ($c->city ? ' en ' . $c->city : '') . ($t ? ' – ' . date('d/m/Y', $t) : ''));
                }
            }
        } catch (\Throwable $e) {
        }

        return '';
    }

    /** Descripción propia de la página actual según su vista; '' si no hay nada mejor. */
    private function describe($app): string
    {
        $in   = $app->getInput();
        $opt  = $in->get('option', '');
        $view = $in->get('view', '');
        $id   = $in->getInt('id', 0);
        $db   = Factory::getContainer()->get('DatabaseDriver');

        try {
            if ($opt === 'com_content' && $view === 'article' && $id) {
                $db->setQuery('SELECT introtext, `fulltext` FROM #__content WHERE id = ' . $id);
                $a = $db->loadObject();

                $d = $a ? $this->clean($a->introtext ?: $a->fulltext) : '';

                return $d !== '' ? $d : $this->clean($app->getDocument()->getTitle() . '. Noticia de rock y heavy metal en Heavy Metal Brothers.', 170);
            }

            if ($opt === 'com_content' && in_array($view, ['category', 'categories'], true)) {
                $cid = $id ?: $in->getInt('catid', 0);
                $db->setQuery('SELECT title, description FROM #__categories WHERE id = ' . (int) $cid);
                $c = $db->loadObject();
                $d = $c ? $this->clean($c->description, 170) : '';

                if ($d !== '') {
                    return $d;
                }

                return $c ? $this->clean($c->title . ': noticias, crónicas y artículos de rock y heavy metal en Heavy Metal Brothers.', 170)
                    : 'Noticias, entrevistas, crónicas y discos del rock y el heavy metal en Heavy Metal Brothers.';
            }

            if ($opt === 'com_concerts' && $view === 'concert' && $id) {
                $db->setQuery('SELECT * FROM #__concerts WHERE id = ' . $id . ' AND state = 1');
                $c = $db->loadObject();

                if ($c) {
                    $who = $c->title ?: $c->band;
                    $s   = $who . ' en concierto' . ($c->venue ? ' en ' . $c->venue : '') . ($c->city ? ' (' . $c->city . ')' : '');
                    $f   = $this->fechaEs((string) $c->date_start);

                    return $this->clean($s . ($f ? ' el ' . $f : '') . '. Entradas, cartel y toda la información en la agenda de conciertos de Heavy Metal Brothers.', 170);
                }
            }

            if ($opt === 'com_efemerides' && $view === 'banda' && $id) {
                $db->setQuery('SELECT nombre, pais, genero FROM #__efemerides_bandas WHERE id = ' . $id . ' AND state = 1');
                $b = $db->loadObject();

                if ($b) {
                    return $this->clean($b->nombre . ': banda' . ($b->genero ? ' de ' . $b->genero : ' de rock y metal') . ($b->pais ? ' de ' . $b->pais : '')
                        . '. Miembros, efemérides y próximos conciertos en el archivo de bandas de Heavy Metal Brothers.', 170);
                }
            }

            if ($opt === 'com_efemerides' && $view === 'musico' && $id) {
                $db->setQuery('SELECT nombre, instrumento, banda_texto, descripcion FROM #__efemerides_musicos WHERE id = ' . $id . ' AND state = 1');
                $m = $db->loadObject();

                if ($m) {
                    $d = $this->clean($m->descripcion, 150);

                    return $d !== '' ? $d : $this->clean($m->nombre . ($m->instrumento ? ', ' . $m->instrumento : '') . ($m->banda_texto ? ' de ' . $m->banda_texto : '')
                        . '. Biografía, fechas y datos en el archivo de músicos de Heavy Metal Brothers.', 170);
                }
            }
        } catch (\Throwable $e) {
            // sin datos: se usa la descripción por vista
        }

        return self::VIEW_DESC[$opt . '.' . $view] ?? (self::VIEW_DESC[$view] ?? '');
    }

    // ------------------------------------------------------------------ Open Graph / Twitter

    /** Completa las etiquetas sociales que falten (las que ya trae el componente se respetan; og:url se fuerza a la canónica). */
    private function social($app, string $body): string
    {
        if (stripos($body, 'HMB Open Graph') !== false) {
            return $body; // ya lo hace hmb_opengraph
        }

        $has = static function (string $k) use ($body): bool {
            return (bool) preg_match('#<meta[^>]+(?:property|name)="' . preg_quote($k, '#') . '"#i', $body);
        };

        $canon = preg_match('#<link href="([^"]+)" rel="canonical"#i', $body, $m) ? html_entity_decode($m[1]) : '';
        $title = $app->getDocument()->getTitle();
        $desc  = $app->getDocument()->getDescription();
        $img   = '';

        // Imagen: la del JSON-LD (artículo, evento, banda...) y, si no hay, el logo
        if (preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $body, $all)) {
            foreach ($all[1] as $raw) {
                $j = json_decode($raw, true);

                foreach (($j['@graph'] ?? [$j]) as $n) {
                    $i = $n['image'] ?? '';
                    $i = is_array($i) ? ($i[0] ?? '') : $i;

                    if (is_string($i) && $i !== '' && !in_array($n['@type'] ?? '', ['Organization'], true)) {
                        $img = $img ?: $i;
                    }
                }
            }
        }

        $img  = $this->abs($img ?: 'images/calaverasIA3.png');
        $type = (stripos($body, '"NewsArticle"') !== false) ? 'article' : 'website';
        $e    = static function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
        $tags = [];

        if ($canon && !$has('og:url')) { $tags[] = '<meta property="og:url" content="' . $e($canon) . '">'; }
        if (!$has('og:type')) { $tags[] = '<meta property="og:type" content="' . $type . '">'; }
        if (!$has('og:locale')) { $tags[] = '<meta property="og:locale" content="es_ES">'; }
        if ($desc && !$has('og:description')) { $tags[] = '<meta property="og:description" content="' . $e($desc) . '">'; }
        if (!$has('og:image')) { $tags[] = '<meta property="og:image" content="' . $e($img) . '">'; }
        if (!$has('twitter:card')) { $tags[] = '<meta name="twitter:card" content="summary_large_image">'; }
        if (!$has('twitter:title')) { $tags[] = '<meta name="twitter:title" content="' . $e($title) . '">'; }
        if ($desc && !$has('twitter:description')) { $tags[] = '<meta name="twitter:description" content="' . $e($desc) . '">'; }
        if (!$has('twitter:image')) { $tags[] = '<meta name="twitter:image" content="' . $e($img) . '">'; }

        // La ficha del artista emite un og:url sin id: se sustituye por la canónica
        if ($canon && $has('og:url')) {
            $body = preg_replace('#(<meta[^>]+property="og:url"[^>]+content=")[^"]*(")#i', '${1}' . $e($canon) . '${2}', $body, 1);
        }

        return $tags ? str_replace('</head>', implode("
", $tags) . "
</head>", $body) : $body;
    }

    // ------------------------------------------------------------------ JSON-LD

    private function jsonLd($app, string $body): string
    {
        $in     = $app->getInput();
        $opt    = $in->get('option', '');
        $view   = $in->get('view', '');
        $id     = $in->getInt('id', 0);
        $doc    = $app->getDocument();
        $robots = (string) $doc->getMetaData('robots');

        if (stripos($robots, 'noindex') !== false) {
            return $body;
        }

        $canon = '';

        if (preg_match('#<link href="([^"]+)" rel="canonical"#i', $body, $m)) {
            $canon = html_entity_decode($m[1]);
        }

        $orgId  = self::SITE . '/#/schema/Organization/base';
        $webId  = self::SITE . '/#/schema/WebSite/base';
        $isHome = rtrim($canon, '/') === self::SITE;
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $extra  = [];

        $org = [
            '@type' => 'Organization', '@id' => $orgId, 'name' => 'Heavy Metal Brothers', 'url' => self::SITE . '/',
            'logo' => ['@type' => 'ImageObject', 'url' => self::SITE . '/images/calaverasIA3.png', 'width' => 180, 'height' => 180],
            'sameAs' => ['https://www.instagram.com/heavymetalbrothers/'],
        ];
        $web = [
            '@type' => 'WebSite', '@id' => $webId, 'url' => self::SITE . '/', 'name' => 'Heavy Metal Brothers', 'inLanguage' => 'es-ES',
            'publisher' => ['@id' => $orgId],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => self::SITE . '/component/finder/search?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        // Bloque que ya mete Joomla (Organization/WebSite/WebPage/Article): se completa en lugar de duplicarlo
        $patched = false;
        $body = preg_replace_callback('#<script type="application/ld\+json">(.*?)</script>#s', function ($m) use (&$patched, $web, $in, $db, $opt, $view, $id, $canon) {
            $j = json_decode($m[1], true);

            if (!is_array($j) || empty($j['@graph'])) {
                return $m[0];
            }

            $patched = true;

            foreach ($j['@graph'] as &$n) {
                if (($n['@type'] ?? '') === 'WebSite') {
                    $n['potentialAction'] = $web['potentialAction'];
                    $n['inLanguage'] = 'es-ES';
                }

                if (($n['@type'] ?? '') === 'Article' && $opt === 'com_content' && $view === 'article' && $id) {
                    $db->setQuery('SELECT a.title, a.created, a.modified, a.publish_up, a.created_by_alias, a.images, a.introtext, u.name AS autor, c.title AS cat
                        FROM #__content a LEFT JOIN #__users u ON u.id = a.created_by LEFT JOIN #__categories c ON c.id = a.catid WHERE a.id = ' . $id);
                    $a = $db->loadObject();

                    if ($a) {
                        $n['@type'] = 'NewsArticle';
                        $pub = $a->publish_up ?: $a->created;
                        $n['datePublished'] = gmdate('c', strtotime($pub . ' UTC'));
                        $n['dateModified']  = gmdate('c', strtotime(($a->modified && $a->modified > '2000' ? $a->modified : $pub) . ' UTC'));
                        $n['description'] = $this->clean($a->introtext, 200);
                        $n['author'] = ['@type' => 'Person', 'name' => $a->created_by_alias ?: ($a->autor ?: 'Heavy Metal Brothers')];
                        $n['publisher'] = ['@id' => self::SITE . '/#/schema/Organization/base'];
                        $n['mainEntityOfPage'] = ['@type' => 'WebPage', '@id' => $canon];

                        if ($a->cat) {
                            $n['articleSection'] = $a->cat;
                        }

                        $img = json_decode($a->images ?: '{}', true) ?: [];
                        $src = $img['image_fulltext'] ?? ($img['image_intro'] ?? '');

                        if (!$src && preg_match('#<img[^>]+src=["\']([^"\']+)#i', $a->introtext, $mm)) {
                            $src = $mm[1];
                        }

                        if (!$src && !empty($n['thumbnailUrl'])) {
                            $src = $n['thumbnailUrl'];
                        }

                        $src = preg_replace('/#.*$/', '', (string) $src);

                        if ($src) {
                            $n['image'] = [$this->abs($src)];
                            unset($n['thumbnailUrl']);
                        }
                    }
                }
            }

            unset($n);

            return '<script type="application/ld+json">' . json_encode($j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
        }, $body);

        $graph = [];

        if (!$patched) {
            $graph[] = $org;
            $graph[] = $web;
        }

        // Migas de pan
        if (!$isHome) {
            $items = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => self::SITE . '/']];
            $pos   = 2;
            $path  = $app->getPathway()->getPathway();

            foreach ($path as $i => $p) {
                $last = ($i === count($path) - 1);
                $name = trim(strip_tags((string) $p->name));

                if ($name === '') {
                    continue;
                }

                $li = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $name];

                if (!$last && !empty($p->link)) {
                    $li['item'] = $this->abs(Route::_($p->link));
                } elseif ($last) {
                    $li['item'] = $canon;
                }

                $items[] = $li;
            }

            if (count($items) === 1) {
                $t = trim(preg_replace('#\s*\|\s*Heavy Metal Brothers.*$#iu', '', $doc->getTitle()));
                $items[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $t, 'item' => $canon];
            }

            $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
        }

        // Concierto -> Event
        if ($opt === 'com_concerts' && $view === 'concert' && $id) {
            $db->setQuery('SELECT * FROM #__concerts WHERE id = ' . $id . ' AND state = 1');
            $c = $db->loadObject();

            if ($c && $c->date_start) {
                $time = $c->concert_time ? 'T' . substr((string) $c->concert_time, 0, 5) : '';
                $name = $c->title ?: $c->band;
                $ev = [
                    '@type' => 'MusicEvent', 'name' => $name, 'url' => $canon, 'inLanguage' => 'es-ES',
                    'startDate' => substr((string) $c->date_start, 0, 10) . $time,
                    'eventStatus' => 'https://schema.org/EventScheduled',
                    'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                    'location' => [
                        '@type' => 'Place', 'name' => $c->venue ?: ($c->city ?: 'Por confirmar'),
                        'address' => array_filter([
                            '@type' => 'PostalAddress', 'streetAddress' => $c->address ?: null,
                            'addressLocality' => $c->city ?: null, 'addressRegion' => $c->province ?: null,
                            'addressCountry' => $c->country_code ?: 'ES',
                        ]),
                    ],
                    'organizer' => ['@id' => $orgId],
                ];

                // endDate: la de la BD o, si es de un solo día, el mismo día de inicio
                $ev['endDate'] = ($c->date_end && $c->date_end > $c->date_start) ? substr((string) $c->date_end, 0, 10) : substr((string) $c->date_start, 0, 10);

                // image: el cartel o, si no hay, el logo de la web
                $ev['image'] = [$this->abs($c->poster_url ?: 'images/calaverasIA3.png')];

                // description: la del concierto o la generada para la meta description
                $ev['description'] = $c->description ? $this->clean($c->description, 300)
                    : $this->clean((string) $doc->getDescription(), 300);

                if ($c->band) {
                    $ev['performer'] = [['@type' => 'MusicGroup', 'name' => $c->band]];
                }

                // offers: enlace de entradas si lo hay; si no, la propia ficha. Precio solo si el texto de precios trae una cifra.
                $offer = ['@type' => 'Offer', 'url' => $c->ticket_url ?: $canon, 'availability' => 'https://schema.org/InStock', 'priceCurrency' => 'EUR'];

                if (!empty($c->prices) && preg_match('/\d+(?:[.,]\d+)?/', (string) $c->prices, $pm)) {
                    $offer['price'] = str_replace(',', '.', $pm[0]);
                }

                $ev['offers'] = $offer;

                $graph[] = $ev;
            }
        }

        // Banda del archivo -> MusicGroup
        if ($opt === 'com_efemerides' && $view === 'banda' && $id) {
            $db->setQuery('SELECT nombre, pais, genero, foto FROM #__efemerides_bandas WHERE id = ' . $id . ' AND state = 1');
            $b = $db->loadObject();

            if ($b) {
                $db->setQuery('SELECT nombre FROM #__efemerides_musicos WHERE banda_id = ' . $id . ' AND state = 1 ORDER BY nombre LIMIT 30');
                $mem = $db->loadColumn() ?: [];
                $g = ['@type' => 'MusicGroup', 'name' => $b->nombre, 'url' => $canon];

                if ($b->genero) { $g['genre'] = $b->genero; }
                if ($b->pais) { $g['foundingLocation'] = ['@type' => 'Place', 'name' => $b->pais]; }
                if ($b->foto) { $g['image'] = $this->abs($b->foto); }
                if ($mem) { $g['member'] = array_map(static fn($n) => ['@type' => 'Person', 'name' => $n], $mem); }

                $graph[] = $g;
            }
        }

        // Músico del archivo -> Person
        if ($opt === 'com_efemerides' && $view === 'musico' && $id) {
            $db->setQuery('SELECT * FROM #__efemerides_musicos WHERE id = ' . $id . ' AND state = 1');
            $m = $db->loadObject();

            if ($m) {
                $p = ['@type' => 'Person', 'name' => $m->nombre, 'url' => $canon, 'jobTitle' => $m->instrumento ?: 'Músico'];

                if ($m->fecha_nacimiento && $m->fecha_nacimiento > '0001') { $p['birthDate'] = substr((string) $m->fecha_nacimiento, 0, 10); }
                if ($m->fecha_defuncion && $m->fecha_defuncion > '0001') { $p['deathDate'] = substr((string) $m->fecha_defuncion, 0, 10); }
                if ($m->lugar_nacimiento) { $p['birthPlace'] = ['@type' => 'Place', 'name' => $m->lugar_nacimiento]; }
                if ($m->lugar_defuncion) { $p['deathPlace'] = ['@type' => 'Place', 'name' => $m->lugar_defuncion]; }
                if ($m->foto) { $p['image'] = $this->abs($m->foto); }
                if ($m->descripcion) { $p['description'] = $this->clean($m->descripcion, 300); }
                if ($m->banda_texto) { $p['memberOf'] = ['@type' => 'MusicGroup', 'name' => $m->banda_texto]; }
                if ($m->wikipedia_url) { $p['sameAs'] = [$m->wikipedia_url]; }

                $graph[] = $p;
            }
        }

        // Artista emergente: el MusicGroup que emite el componente apunta a una URL sin id; se corrige
        if ($opt === 'com_artistas' && $view === 'artista' && $canon) {
            $body = preg_replace_callback('#<script type="application/ld\+json">(.*?)</script>#s', static function ($m) use ($canon) {
                $j = json_decode($m[1], true);

                if (!is_array($j) || ($j['@type'] ?? '') !== 'MusicGroup') {
                    return $m[0];
                }

                $j['url'] = $canon;
                unset($j['email']);

                return '<script type="application/ld+json">' . json_encode($j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
            }, $body);
        }

        if ($graph) {
            $json = json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $body = str_replace('</head>', '<script type="application/ld+json">' . $json . "</script>\n</head>", $body);
        }

        return $body;
    }

    // ------------------------------------------------------------------ sitemap

    private function buildSitemap(): string
    {
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $urls = [];
        $add  = function (string $loc, ?string $mod = null, string $freq = 'weekly', string $prio = '0.5') use (&$urls) {
            $urls[$loc] = [$mod, $freq, $prio];
        };

        $add(self::SITE . '/', null, 'daily', '1.0');

        // Páginas de menú públicas (no cuentas, registro ni acciones)
        $skip = ['com_users', 'com_hmb', 'com_config', 'com_login', 'com_tags', 'com_finder', 'com_hmbshop'];
        $db->setQuery("SELECT id, link, home, params FROM #__menu WHERE published = 1 AND client_id = 0 AND access = 1 AND type = 'component' AND home = 0 AND (language = '*' OR language = 'es-ES')");
        $menuByComp = [];
        $seenView   = [];

        foreach ($db->loadObjectList() ?: [] as $mi) {
            parse_str((string) parse_url($mi->link, PHP_URL_QUERY), $q);
            $opt = $q['option'] ?? '';

            if (!$opt || in_array($opt, $skip, true) || preg_match('/view=(registro|login|reset|remind|miperfil)/', $mi->link)) {
                continue;
            }

            if (stripos((string) $mi->params, '"robots":"noindex') !== false) {
                continue;
            }

            $loc = html_entity_decode($this->abs(Route::_('index.php?Itemid=' . (int) $mi->id)), ENT_QUOTES | ENT_HTML5);
            $loc = html_entity_decode($loc, ENT_QUOTES | ENT_HTML5);

            if (strpos($loc, 'format=') !== false || preg_match('#inicio-de-sesion|/busqueda|view=busqueda#', $loc)) {
                continue;
            }

            // Vistas cuya canónica no lleva ?view=
            if (in_array($q['view'] ?? '', ['game', 'frases'], true)) {
                $loc = strtok($loc, '?');
            }

            // Dos entradas de menú para la misma vista: solo la de ruta más profunda
            $key = $opt . '|' . ($q['view'] ?? '');

            if (isset($seenView[$key])) {
                if (strlen(strtok($loc, '?')) <= strlen(strtok($seenView[$key], '?'))) {
                    continue;
                }

                unset($urls[$seenView[$key]]);
            }

            $seenView[$key] = $loc;
            $add($loc, null, 'weekly', '0.7');
            $menuByComp[$key] = strtok($loc, '?');
        }

        // Páginas de entrada del Metal Quiz (/metal-quiz/<slug>): salen de TemasHelper, sin entradas de menú
        if (isset($menuByComp['com_hmb_quiz|game']) && class_exists('Hmb\Component\Quiz\Site\Helper\TemasHelper')) {
            foreach (array_keys(\Hmb\Component\Quiz\Site\Helper\TemasHelper::all()) as $slug) {
                $add(rtrim($menuByComp['com_hmb_quiz|game'], '/') . '/' . $slug, null, 'monthly', '0.7');
            }
        }

        // Noticias y artículos
        $db->setQuery("SELECT c.id, c.alias, c.catid, GREATEST(c.created, IFNULL(c.modified, c.created), IFNULL(c.publish_up, c.created)) AS m FROM #__content AS c
            INNER JOIN #__categories AS k ON k.id = c.catid AND k.published = 1 AND k.access = 1
            WHERE c.state = 1 AND c.access = 1 AND (c.publish_up IS NULL OR c.publish_up <= NOW()) AND (c.publish_down IS NULL OR c.publish_down > NOW())
            ORDER BY c.id DESC LIMIT 5000");

        foreach ($db->loadObjectList() ?: [] as $a) {
            $route = \Joomla\Component\Content\Site\Helper\RouteHelper::getArticleRoute($a->id . ':' . $a->alias, $a->catid);
            $loc = $this->abs(Route::_($route));

            if (strpos($loc, '/component/') !== false || preg_match('#-n\d+$#', $loc)) {
                continue; // artículos sin ruta de menú (fichas antiguas de artistas)
            }

            $add($loc, gmdate('c', strtotime($a->m . ' UTC')), 'monthly', '0.8');
        }

        // Bandas, músicos, artistas, conciertos: ruta del menú + ?view=...&id=...
        $detail = function (string $base, string $view, string $sql, string $freq, string $prio) use ($db, $add) {
            if (!$base) {
                return;
            }

            $db->setQuery($sql);

            foreach ($db->loadColumn() ?: [] as $id) {
                $add($base . '?view=' . $view . '&id=' . (int) $id, null, $freq, $prio);
            }
        };

        $detail($menuByComp['com_efemerides|bandas'] ?? '', 'banda', 'SELECT id FROM #__efemerides_bandas WHERE state = 1 ORDER BY id LIMIT 20000', 'monthly', '0.6');
        $detail($menuByComp['com_efemerides|musicos'] ?? '', 'musico', 'SELECT id FROM #__efemerides_musicos WHERE state = 1 ORDER BY id LIMIT 20000', 'monthly', '0.5');
        $detail($menuByComp['com_artistas|artistas'] ?? '', 'artista', 'SELECT id FROM #__artistas WHERE state = 1 ORDER BY id LIMIT 5000', 'monthly', '0.6');
        $detail($menuByComp['com_concerts|concerts'] ?? '', 'concert',
            'SELECT id FROM #__concerts WHERE state = 1 AND COALESCE(date_end, date_start) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY date_start LIMIT 5000', 'weekly', '0.7');

        $x = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $loc => [$mod, $freq, $prio]) {
            $x .= '<url><loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>'
                . ($mod ? '<lastmod>' . $mod . '</lastmod>' : '') . '<changefreq>' . $freq . '</changefreq><priority>' . $prio . "</priority></url>\n";
        }

        return $x . '</urlset>' . "\n";
    }
}
