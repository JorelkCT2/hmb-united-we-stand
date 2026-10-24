<?php
namespace Joomla\Component\Hmbnews\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Hmbnews\Administrator\Helper\FeedHelper;

class AjaxController extends BaseController
{
    private function db()  { return Factory::getDbo(); }
    private function app() { return Factory::getApplication(); }

    private function jsonOut(array $data): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->app()->close();
    }

    private function csrf(): bool
    {
        try {
            return Session::checkToken('post') || Session::checkToken('get') || Session::checkToken('request');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function ensureTables(): void
    {
        try {
            $db = $this->db();
            $db->setQuery('CREATE TABLE IF NOT EXISTS `#__hmb_sources`(`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(100) NOT NULL DEFAULT \'\',`url` varchar(255) NOT NULL DEFAULT \'\',`active` tinyint(1) NOT NULL DEFAULT 1,`ordering` int(11) NOT NULL DEFAULT 0,PRIMARY KEY(`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci')->execute();
            $db->setQuery('CREATE TABLE IF NOT EXISTS `#__hmb_imported`(`id` int(11) NOT NULL AUTO_INCREMENT,`article_id` int(11) NOT NULL DEFAULT 0,`source_site` varchar(100) NOT NULL DEFAULT \'\',`source_url` varchar(512) NOT NULL DEFAULT \'\',`imported_at` datetime NOT NULL,`news_date` date NULL DEFAULT NULL,PRIMARY KEY(`id`),KEY`idx_article_id`(`article_id`))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci')->execute();
            try {
                $cols = $db->setQuery('SHOW COLUMNS FROM `#__hmb_imported`')->loadColumn();
                if (!in_array('news_date', $cols, true)) {
                    $db->setQuery('ALTER TABLE `#__hmb_imported` ADD COLUMN `news_date` date NULL DEFAULT NULL AFTER `imported_at`')->execute();
                }
            } catch (\Throwable $e) {}
            try {
                $scols = $db->setQuery('SHOW COLUMNS FROM `#__hmb_sources`')->loadColumn();
                if (!in_array('feed_url', $scols, true)) {
                    $db->setQuery('ALTER TABLE `#__hmb_sources` ADD COLUMN `feed_url` varchar(500) NOT NULL DEFAULT \'\' AFTER `url`')->execute();
                }
            } catch (\Throwable $e) {}
            $n = (int)$db->setQuery('SELECT COUNT(*) FROM `#__hmb_sources`')->loadResult();
            if ($n === 0) {
                $db->setQuery("INSERT IGNORE INTO `#__hmb_sources`(`name`,`url`,`active`,`ordering`)VALUES('Loudwire','loudwire.com',1,1),('Metal Injection','metalinjection.net',1,2),('Blabbermouth','blabbermouth.net',0,3),('Kerrang','kerrang.com',0,4),('Revolver','revolvermag.com',0,5),('Metal Hammer','metalhammer.com',0,6),('Guitar World','guitarworld.com',0,7)")->execute();
            }
        } catch (\Throwable $e) {}
    }

    public function getSources(): void
    {
        try {
            $this->ensureTables();
            $db = $this->db();
            $q  = $db->getQuery(true)
                ->select(['id', 'name', 'url', 'active', 'ordering'])
                ->from('#__hmb_sources')
                ->order('ordering ASC');
            $rows = $db->setQuery($q)->loadAssocList() ?? [];
            $this->jsonOut(['success' => true, 'sources' => $rows]);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'getSources: ' . $e->getMessage()]);
        }
    }

    public function getArticles(): void
    {
        try {
            $this->ensureTables();
            $db = $this->db();
            $sortBy = Factory::getApplication()->input->getString('sort', 'imported');
            $orderCol = ($sortBy === 'news') ? 'i.news_date DESC, i.imported_at DESC' : 'i.imported_at DESC';
            $q  = $db->getQuery(true)
                ->select(['i.id AS import_id', 'i.article_id', 'i.source_site', 'i.source_url', 'i.imported_at', 'i.news_date', 'c.title', 'c.alias', 'c.state', 'c.images', 'c.metakey'])
                ->from('#__hmb_imported AS i')
                ->leftJoin('#__content AS c ON c.id = i.article_id')
                ->order($orderCol);
            $rows = $db->setQuery($q)->loadAssocList() ?? [];
            foreach ($rows as &$r) {
                $imgs = json_decode($r['images'] ?? '{}', true);
                $r['thumb'] = $imgs['image_intro'] ?? '';
                $r['hashtags'] = FeedHelper::hashtags((string) ($r['metakey'] ?? ''));
                unset($r['images'], $r['metakey']);
            }
            $this->jsonOut(['success' => true, 'articles' => $rows]);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'getArticles: ' . $e->getMessage()]);
        }
    }

    public function addSource(): void
    {
        try {
            if (!$this->csrf()) { $this->jsonOut(['success' => false, 'message' => 'Token invalido']); return; }
            $inp = $this->app()->input;
            $name = trim($inp->post->getString('name', ''));
            $url  = trim($inp->post->getString('url', ''));
            if (!$name || !$url) { $this->jsonOut(['success' => false, 'message' => 'Nombre y URL requeridos']); return; }
            $db   = $this->db();
            $max  = (int)$db->setQuery($db->getQuery(true)->select('MAX(ordering)')->from('#__hmb_sources'))->loadResult();
            $row  = new \stdClass();
            $row->name = $name;
            $row->url = $url;
            $row->active = 1;
            $row->ordering = $max + 1;
            $db->insertObject('#__hmb_sources', $row);
            $row->id = (int)$db->insertid();
            $this->jsonOut(['success' => true, 'source' => (array)$row]);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'addSource: ' . $e->getMessage()]);
        }
    }

    public function deleteSource(): void
    {
        try {
            if (!$this->csrf()) { $this->jsonOut(['success' => false, 'message' => 'Token invalido']); return; }
            $id = (int)$this->app()->input->post->getInt('id', 0);
            if (!$id) { $this->jsonOut(['success' => false, 'message' => 'ID invalido']); return; }
            $db = $this->db();
            $db->setQuery($db->getQuery(true)->delete('#__hmb_sources')->where('id = ' . $id))->execute();
            $this->jsonOut(['success' => true]);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'deleteSource: ' . $e->getMessage()]);
        }
    }

    public function toggleSource(): void
    {
        try {
            if (!$this->csrf()) { $this->jsonOut(['success' => false, 'message' => 'Token invalido']); return; }
            $inp = $this->app()->input;
            $id  = (int)$inp->post->getInt('id', 0);
            $active = $inp->post->getInt('active', 0) ? 1 : 0;
            if (!$id) { $this->jsonOut(['success' => false, 'message' => 'ID invalido']); return; }
            $db = $this->db();
            $db->setQuery($db->getQuery(true)->update('#__hmb_sources')->set('active = ' . $active)->where('id = ' . $id))->execute();
            $this->jsonOut(['success' => true, 'active' => $active]);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'toggleSource: ' . $e->getMessage()]);
        }
    }

    public function updateSource(): void
    {
        try {
            if (!$this->csrf()) { $this->jsonOut(['success' => false, 'message' => 'Token invalido']); return; }
            $inp  = $this->app()->input;
            $id   = (int)$inp->post->getInt('id', 0);
            $name = trim($inp->post->getString('name', ''));
            $url  = trim($inp->post->getString('url', ''));
            if (!$id || !$name || !$url) { $this->jsonOut(['success' => false, 'message' => 'Datos incompletos']); return; }
            $db = $this->db();
            $db->setQuery($db->getQuery(true)->update('#__hmb_sources')->set('name = ' . $db->quote($name))->set('url = ' . $db->quote($url))->set('feed_url = ' . $db->quote(''))->where('id = ' . $id))->execute();
            $this->jsonOut(['success' => true]);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'updateSource: ' . $e->getMessage()]);
        }
    }

    public function deleteArticle(): void
    {
        try {
            if (!$this->csrf()) { $this->jsonOut(['success' => false, 'message' => 'Token invalido']); return; }
            $inp = $this->app()->input;
            $aid = (int)$inp->post->getInt('article_id', 0);
            $iid = (int)$inp->post->getInt('import_id', 0);
            if (!$aid || !$iid) { $this->jsonOut(['success' => false, 'message' => 'IDs invalidos']); return; }
            $db = $this->db();
            $db->setQuery($db->getQuery(true)->delete('#__content')->where('id = ' . $aid))->execute();
            $db->setQuery($db->getQuery(true)->delete('#__hmb_imported')->where('id = ' . $iid))->execute();
            $this->jsonOut(['success' => true]);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'deleteArticle: ' . $e->getMessage()]);
        }
    }

    public function clearHistory(): void
    {
        try {
            if (!$this->csrf()) { $this->jsonOut(['success' => false, 'message' => 'Token invalido']); return; }
            $db = $this->db();

            // Obtener IDs de articulos importados
            $ids = $db->setQuery($db->getQuery(true)->select('article_id')->from('#__hmb_imported'))->loadColumn() ?? [];
            $count = 0;
            if (!empty($ids)) {
                $idsStr = implode(',', array_map('intval', $ids));
                // Borrar articulos de #__content
                $db->setQuery('DELETE FROM `#__content` WHERE id IN (' . $idsStr . ')')->execute();
                // Borrar assets huerfanos
                $db->setQuery('DELETE FROM `#__assets` WHERE name LIKE \'com_content.article.%\' AND name NOT IN (SELECT CONCAT(\'com_content.article.\', id) FROM `#__content`)')->execute();
                // Borrar workflow associations huerfanas
                try { $db->setQuery('DELETE FROM `#__workflow_associations` WHERE item_id IN (' . $idsStr . ') AND extension LIKE \'com_content%\'')->execute(); } catch (\Throwable $e) {}
                $count = count($ids);
            }
            // Borrar tabla de imported
            $db->setQuery('TRUNCATE TABLE `#__hmb_imported`')->execute();

            $this->jsonOut(['success' => true, 'deleted' => $count, 'message' => $count . ' articulo(s) borrado(s) y historial limpiado.']);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'clearHistory: ' . $e->getMessage()]);
        }
    }

    // ── Ajustes ──────────────────────────────────────────────────────────

    private function ajustes(): array
    {
        // Se leen directamente de BD para saltarse la cache de ComponentHelper
        $raw = $this->db()->setQuery("SELECT params FROM `#__extensions` WHERE element='com_hmbnews' AND type='component'")->loadResult();
        $p   = json_decode($raw ?? '{}', true);

        if (!is_array($p)) {
            $p = [];
        }

        return (isset($p['params']) && is_array($p['params'])) ? $p['params'] : $p;
    }

    // ── 1) BUSCAR (gratis: solo RSS, sin IA) ─────────────────────────────

    public function buscar(): void
    {
        try {
            if (!$this->csrf()) { $this->jsonOut(['success' => false, 'message' => 'Token invalido']); return; }
            @set_time_limit(120);
            $this->ensureTables();

            $p    = $this->ajustes();
            $inp  = $this->app()->input->post;
            $dias = max(1, min(60, $inp->getInt('dias', 0) ?: (int) ($p['dias_max'] ?? 7)));
            $solo = $inp->getInt('solo_prioritarias', 0) === 1;
            $q    = FeedHelper::normalizar(trim($inp->getString('q', '')));

            $excluir  = FeedHelper::lista((string) (($p['excluir'] ?? '') !== '' ? $p['excluir'] : FeedHelper::basuraPorDefecto()));
            $priori   = FeedHelper::lista((string) ($p['prioritarias'] ?? ''));
            $generalistas = FeedHelper::lista((string) (($p['generalistas'] ?? '') !== '' ? $p['generalistas'] : 'loudwire.com, guitarworld.com, kerrang.com, rockandblog.net, loudersound.com, metalhammer.com'));

            $db     = $this->db();
            $bandas = array_map([FeedHelper::class, 'normalizar'], $db->setQuery('SELECT name FROM `#__artistas` WHERE state = 1')->loadColumn() ?: []);
            $srcs   = $db->setQuery('SELECT id, name, url, feed_url FROM `#__hmb_sources` WHERE active = 1 ORDER BY ordering ASC')->loadAssocList() ?: [];

            if (!$srcs) {
                $this->jsonOut(['success' => false, 'message' => 'No hay fuentes activas. Activa al menos una.']);
                return;
            }

            // URLs ya importadas y titulos recientes (para no repetir)
            $hechas = [];
            foreach ($db->setQuery('SELECT source_url FROM `#__hmb_imported`')->loadColumn() ?: [] as $u) {
                $hechas[preg_replace('#[?\#].*$#', '', rtrim((string) $u, '/'))] = true;
            }

            $limite = time() - $dias * 86400;
            $todas  = [];
            $informe = [];

            foreach ($srcs as $s) {
                $feed = trim((string) $s['feed_url']);
                $xml  = '';

                if ($feed !== '') {
                    $r = FeedHelper::http($feed, 20, '', 2500000);
                    $xml = $r['code'] === 200 ? $r['body'] : '';
                }

                if ($xml === '') {
                    $feed = FeedHelper::descubrirFeed($s['url']);

                    if ($feed !== '') {
                        $db->setQuery('UPDATE `#__hmb_sources` SET feed_url = ' . $db->quote($feed) . ' WHERE id = ' . (int) $s['id'])->execute();
                        $r   = FeedHelper::http($feed, 20, '', 2500000);
                        $xml = $r['code'] === 200 ? $r['body'] : '';
                    }
                }

                $items = FeedHelper::leerFeed($xml);

                if (!$items) {
                    $informe[] = ['fuente' => $s['name'], 'estado' => $feed === '' ? 'sin RSS accesible' : 'RSS vacio', 'total' => 0, 'validas' => 0];
                    continue;
                }

                $gen    = in_array(FeedHelper::hostDe($s['url']), $generalistas, true) || in_array(FeedHelper::hostDe($feed), $generalistas, true);
                $validas = 0;

                foreach ($items as $it) {
                    if ($it['titulo'] === '' || $it['url'] === '') {
                        continue;
                    }

                    if ($it['fecha'] && $it['fecha'] < $limite) {
                        continue;
                    }

                    if (isset($hechas[preg_replace('#[?\#].*$#', '', rtrim($it['url'], '/'))])) {
                        continue;
                    }

                    if ($q !== '' && strpos(FeedHelper::normalizar($it['titulo'] . ' ' . $it['texto']), $q) === false) {
                        continue;
                    }

                    $sc = FeedHelper::puntuar($it, $gen, $excluir, $priori, $bandas);

                    if ($sc['descartar']) {
                        continue;
                    }

                    if ($solo && strpos($sc['motivo'], 'banda de la casa') === false && strpos($sc['motivo'], 'tema prioritario') === false) {
                        continue;
                    }

                    $validas++;
                    $todas[] = [
                        'fuente'  => $s['name'],
                        'titulo'  => $it['titulo'],
                        'url'     => $it['url'],
                        'fecha'   => $it['fecha'] ? date('Y-m-d', $it['fecha']) : '',
                        'ts'      => $it['fecha'],
                        'resumen' => mb_substr($it['texto'], 0, 260),
                        'texto'   => mb_substr($it['texto'], 0, 6000),
                        'imagen'  => $it['imagen'],
                        'puntos'  => $sc['puntos'],
                        'motivo'  => $sc['motivo'],
                        'palabras' => FeedHelper::palabrasTitulo($it['titulo']),
                    ];
                }

                $informe[] = ['fuente' => $s['name'], 'estado' => 'ok', 'total' => count($items), 'validas' => $validas];
            }

            // Misma noticia en varias fuentes: se queda la mejor puntuada
            usort($todas, static fn($a, $b) => [$b['puntos'], $b['ts']] <=> [$a['puntos'], $a['ts']]);
            $finales = [];

            foreach ($todas as $c) {
                foreach ($finales as $f) {
                    if (FeedHelper::parecidas($c['palabras'], $f['palabras']) >= 0.5 && count($c['palabras']) >= 2) {
                        continue 2;
                    }
                }

                $finales[] = $c;
            }

            $finales = array_slice($finales, 0, 60);

            foreach ($finales as &$f) {
                unset($f['palabras'], $f['ts']);
            }

            $this->jsonOut(['success' => true, 'candidatas' => $finales, 'informe' => $informe]);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'buscar: ' . $e->getMessage()]);
        }
    }

    // ── 2) IMPORTAR UNA (usa la IA solo para redactar) ───────────────────

    public function importarUna(): void
    {
        try {
            if (!$this->csrf()) { $this->jsonOut(['success' => false, 'message' => 'Token invalido']); return; }
            @set_time_limit(150);
            $this->ensureTables();

            $p      = $this->ajustes();
            $inp    = $this->app()->input->post;
            $apiKey = trim((string) ($p['api_key'] ?? ''));
            $modelo = trim((string) ($p['modelo'] ?? '')) ?: 'claude-haiku-4-5';
            $catId  = $inp->getInt('catid', 0) ?: (int) ($p['default_category'] ?? 21);
            $state  = $inp->getInt('publicar', 0) === 1 ? 1 : (int) ($p['article_state'] ?? 0);
            $lang   = (string) ($p['article_language'] ?? 'es-ES') ?: 'es-ES';

            $url    = trim($inp->getString('url', ''));
            $titulo = trim($inp->getString('titulo', ''));
            $fuente = trim($inp->getString('fuente', ''));
            $fecha  = trim($inp->getString('fecha', ''));
            $imgFeed = trim($inp->getString('imagen', ''));

            if (!$apiKey) { $this->jsonOut(['success' => false, 'message' => 'Configura la API Key en Ajustes del componente.']); return; }
            if (!preg_match('#^https?://#i', $url)) { $this->jsonOut(['success' => false, 'message' => 'URL de la noticia no valida.']); return; }

            $db   = $this->db();
            $norm = preg_replace('#[?\#].*$#', '', rtrim($url, '/'));

            if ((int) $db->setQuery('SELECT COUNT(*) FROM `#__hmb_imported` WHERE source_url LIKE ' . $db->quote($norm . '%'))->loadResult() > 0) {
                $this->jsonOut(['success' => false, 'message' => 'Ya estaba importada.']);
                return;
            }

            // Pagina real: texto + imagen verdadera (og:image). Si la web bloquea al servidor (403),
            // se usa el texto completo que ya trajo el RSS.
            $pag      = FeedHelper::leerPagina($url);
            $feedText = trim($inp->getString('texto', '')) ?: trim($inp->getString('resumen', ''));
            $texto    = mb_strlen($pag['texto']) >= 400 ? $pag['texto'] : (mb_strlen($feedText) > mb_strlen($pag['texto']) ? $feedText : $pag['texto']);

            if (mb_strlen($texto) < 250) {
                $this->jsonOut(['success' => false, 'message' => 'No hay texto aprovechable de la noticia original (HTTP ' . $pag['code'] . '). No se ha gastado IA.']);
                return;
            }

            $ia = FeedHelper::redactar($apiKey, $modelo, ['fuente' => $fuente, 'fecha' => $fecha, 'titulo' => $titulo ?: $pag['titulo'], 'texto' => $texto]);

            if (!$ia['ok']) {
                $this->jsonOut(['success' => false, 'message' => $ia['error']]);
                return;
            }

            $d = $ia['data'];

            if (isset($d['relevante']) && $d['relevante'] === false) {
                $this->jsonOut(['success' => false, 'message' => 'La IA la descarta: ' . ($d['motivo'] ?? 'no es de metal') . ' (coste ~' . number_format($ia['coste'], 4) . ' $)', 'coste' => $ia['coste']]);
                return;
            }

            $tit   = trim((string) ($d['titulo'] ?? ''));
            $cuerpo = trim((string) ($d['contenido'] ?? ''));

            if ($tit === '' || str_word_count(strip_tags($cuerpo)) < 80) {
                $this->jsonOut(['success' => false, 'message' => 'La IA devolvio un texto demasiado corto.', 'coste' => $ia['coste']]);
                return;
            }

            // Imagen real descargada a nuestro servidor (la de la pagina primero, luego la del feed)
            $slug   = FeedHelper::slug($tit);
            $imagen = FeedHelper::guardarImagen($pag['imagen'], $url, $slug) ?: FeedHelper::guardarImagen($imgFeed, $url, $slug);

            $alias  = $this->aliasUnico($slug);
            $now    = Factory::getDate()->toSql();

            try {
                $artDate = $fecha !== '' ? Factory::getDate($fecha)->toSql() : $now;
            } catch (\Throwable $e) {
                $artDate = $now;
            }

            $fuenteHtml = '<p class="hmb-fuente">Fuente: <a href="' . htmlspecialchars($url, ENT_QUOTES) . '" target="_blank" rel="nofollow noopener">' . htmlspecialchars($fuente ?: FeedHelper::hostDe($url)) . '</a></p>';

            $imgs = json_encode([
                'image_intro' => $imagen, 'float_intro' => '', 'image_intro_alt' => $tit, 'image_intro_caption' => '',
                'image_fulltext' => $imagen, 'float_fulltext' => '', 'image_fulltext_alt' => $tit, 'image_fulltext_caption' => '',
            ]);

            $mvc   = $this->app()->bootComponent('com_content')->getMVCFactory();
            $model = $mvc->createModel('Article', 'Administrator', ['ignore_request' => true]);

            $ok = $model->save([
                'id' => 0, 'title' => $tit, 'alias' => $alias,
                'introtext' => trim((string) ($d['intro'] ?? '')), 'fulltext' => $cuerpo . $fuenteHtml,
                'state' => $state, 'catid' => $catId, 'created' => $artDate, 'created_by' => (int) ($this->app()->getIdentity()->id ?? 0),
                'language' => $lang, 'access' => 1, 'images' => $imgs, 'urls' => '{}', 'attribs' => '{}', 'metadata' => '{}',
                'metadesc' => mb_substr(trim((string) ($d['metadesc'] ?? '')), 0, 155), 'metakey' => trim((string) ($d['etiquetas'] ?? '')),
                'featured' => 0, 'publish_up' => $artDate, 'publish_down' => null, 'rules' => [], 'associations' => [], 'tags' => [],
            ]);

            if (!$ok) {
                $this->jsonOut(['success' => false, 'message' => 'No se pudo guardar: ' . ($model->getError() ?: 'error desconocido'), 'coste' => $ia['coste']]);
                return;
            }

            $aid = (int) $model->getState($model->getName() . '.id');

            $reg = new \stdClass();
            $reg->article_id  = $aid;
            $reg->source_site = $fuente;
            $reg->source_url  = $url;
            $reg->imported_at = $now;
            $reg->news_date   = substr($artDate, 0, 10);
            $db->insertObject('#__hmb_imported', $reg);

            $this->jsonOut([
                'success' => true, 'article_id' => $aid, 'title' => $tit, 'state' => $state,
                'imagen' => $imagen !== '', 'coste' => $ia['coste'], 'tokens' => $ia['tokens'],
                'hashtags' => FeedHelper::hashtags((string) ($d['etiquetas'] ?? '')),
            ]);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'importarUna: ' . $e->getMessage()]);
        }
    }

    private function aliasUnico(string $slug): string
    {
        $db  = $this->db();
        $ex  = $db->setQuery($db->getQuery(true)->select('alias')->from('#__content')->where('alias LIKE ' . $db->quote($slug . '%')))->loadColumn();

        return in_array($slug, $ex, true) ? $slug . '-' . substr(md5(microtime(true)), 0, 6) : $slug;
    }

    // ── 3) REPARAR IMAGENES de lo ya importado (gratis, sin IA) ──────────

    public function repararImagenes(): void
    {
        try {
            if (!$this->csrf()) { $this->jsonOut(['success' => false, 'message' => 'Token invalido']); return; }
            @set_time_limit(150);

            $db   = $this->db();
            $rows = $db->setQuery(
                "SELECT c.id, c.title, c.images, i.source_url FROM `#__hmb_imported` i JOIN `#__content` c ON c.id = i.article_id
                 WHERE c.images LIKE '%\"image_intro\":\"http%' ORDER BY c.id DESC LIMIT 6"
            )->loadAssocList() ?: [];

            $arreglados = 0;
            $sinImagen  = 0;
            $rotos      = [];

            foreach ($rows as $r) {
                $imgs   = json_decode($r['images'], true) ?: [];
                $nuevo  = '';
                $estado = 0;

                if (preg_match('#^https?://#i', (string) $r['source_url'])) {
                    $pag    = FeedHelper::leerPagina($r['source_url']);
                    $estado = $pag['code'];

                    if ($pag['code'] === 200) {
                        $nuevo = FeedHelper::guardarImagen($pag['imagen'], $r['source_url'], FeedHelper::slug($r['title']));
                    }
                }

                $imgs['image_intro'] = $imgs['image_fulltext'] = $nuevo;
                $db->setQuery('UPDATE `#__content` SET images = ' . $db->quote(json_encode($imgs)) . ' WHERE id = ' . (int) $r['id'])->execute();

                if ($nuevo !== '') {
                    $arreglados++;
                } else {
                    $sinImagen++;

                    if ($estado === 404 || $estado === 410 || $estado === 0) {
                        $rotos[] = (int) $r['id'];
                    }
                }
            }

            $quedan = (int) $db->setQuery(
                "SELECT COUNT(*) FROM `#__hmb_imported` i JOIN `#__content` c ON c.id = i.article_id
                 WHERE c.images LIKE '%\"image_intro\":\"http%'"
            )->loadResult();

            $this->jsonOut(['success' => true, 'arreglados' => $arreglados, 'sin_imagen' => $sinImagen, 'enlace_roto' => $rotos, 'quedan' => $quedan]);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'repararImagenes: ' . $e->getMessage()]);
        }
    }
}
