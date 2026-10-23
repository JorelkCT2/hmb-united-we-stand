<?php
namespace Joomla\Component\Hmbnews\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;

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
                ->select(['i.id AS import_id', 'i.article_id', 'i.source_site', 'i.source_url', 'i.imported_at', 'i.news_date', 'c.title', 'c.alias', 'c.state', 'c.images'])
                ->from('#__hmb_imported AS i')
                ->leftJoin('#__content AS c ON c.id = i.article_id')
                ->order($orderCol);
            $rows = $db->setQuery($q)->loadAssocList() ?? [];
            foreach ($rows as &$r) {
                $imgs = json_decode($r['images'] ?? '{}', true);
                $r['thumb'] = $imgs['image_intro'] ?? '';
                unset($r['images']);
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
            $db->setQuery($db->getQuery(true)->update('#__hmb_sources')->set('name = ' . $db->quote($name))->set('url = ' . $db->quote($url))->where('id = ' . $id))->execute();
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

    public function importArticles(): void
    {
        try {
            if (!$this->csrf()) { $this->jsonOut(['success' => false, 'message' => 'Token invalido']); return; }
            @set_time_limit(180);

            $app    = $this->app();
            $inp    = $app->input;

            // Leer params DIRECTAMENTE de BD (evitar cache de ComponentHelper)
            $db0 = $this->db();
            $rawParams = $db0->setQuery("SELECT params FROM `#__extensions` WHERE element='com_hmbnews' AND type='component'")->loadResult();
            $p = json_decode($rawParams ?? '{}', true);
            if (!is_array($p)) $p = [];
            if (isset($p['params']) && is_array($p['params'])) $p = $p['params'];

            $apiKey = trim((string)($p['api_key'] ?? ''));
            $catId  = (int)($inp->post->getInt('catid', 0) ?: ($p['default_category'] ?? 21));
            $num    = min(15, max(1, (int)($inp->post->getInt('num', 0) ?: ($p['articles_per_run'] ?? 2))));
            $type   = $inp->post->getString('content_type', 'news');
            $region = $inp->post->getString('region', 'all');
            $state  = (int)($p['article_state'] ?? 0);
            $lang   = (string)($p['article_language'] ?? 'es-ES') ?: 'es-ES';

            if (!$apiKey) {
                $this->jsonOut(['success' => false, 'message' => 'Configura la API Key en Ajustes del componente.']);
                return;
            }

            $db = $this->db();
            $this->ensureTables();
            $srcs = $db->setQuery($db->getQuery(true)->select(['name', 'url'])->from('#__hmb_sources')->where('active = 1')->order('ordering ASC'))->loadAssocList() ?? [];
            if (empty($srcs)) {
                $this->jsonOut(['success' => false, 'message' => 'No hay fuentes activas. Activa al menos una.']);
                return;
            }
            // Aleatorizar el orden de fuentes en cada ejecucion para forzar variedad
            shuffle($srcs);
            $sourceLines = [];
            foreach ($srcs as $s) {
                $sourceLines[] = '- ' . $s['name'] . ' (' . $s['url'] . ')';
            }
            $sitesFormal = implode("\n", $sourceLines);
            $sites = implode(', ', array_column($srcs, 'url'));

            // Variacion temporal en la query para evitar siempre los mismos resultados
            $today    = date('Y-m-d');

            // Region seleccionada
            $regionMap = [
                'europe'  => 'EUROPA: prioriza noticias sobre bandas europeas, festivales europeos (Wacken, Hellfest, Download, Resurrection Fest, Graspop), giras por Europa y lanzamientos de bandas de Europa',
                'northam' => 'NORTEAMERICA: prioriza noticias sobre bandas de EEUU y Canada, festivales norteamericanos, giras por Norteamerica',
                'southam' => 'SUDAMERICA Y LATINOAMERICA: prioriza noticias sobre bandas latinoamericanas, festivales en Sudamerica (Rock al Parque, Knotfest Chile/Brasil), giras por Latinoamerica, y bandas internacionales visitando la region',
                'all'     => 'INTERNACIONAL: cualquier region del mundo',
            ];
            $regionText = $regionMap[$region] ?? $regionMap['all'];
            $thisWeek = date('W \d\e\l Y');

            // Recuperar titulos y URLs ya importados (ultimas 150) para que Claude los evite
            $alreadyImported = $db->setQuery(
                $db->getQuery(true)
                    ->select(['c.title', 'i.source_url', 'i.source_site'])
                    ->from('#__hmb_imported AS i')
                    ->leftJoin('#__content AS c ON c.id = i.article_id')
                    ->order('i.imported_at DESC')
                    ->setLimit(150)
            )->loadAssocList() ?? [];
            $skipTitles = [];
            $skipUrls = [];
            foreach ($alreadyImported as $imp) {
                if (!empty($imp['title'])) $skipTitles[] = '- ' . $imp['title'];
                if (!empty($imp['source_url'])) $skipUrls[] = $imp['source_url'];
            }
            $skipTitlesStr = !empty($skipTitles) ? implode("\n", array_slice($skipTitles, 0, 80)) : '(ninguno aun)';
            $skipUrlsStr = !empty($skipUrls) ? implode("\n", array_slice($skipUrls, 0, 80)) : '(ninguno aun)';
            $map   = [
                'news'       => 'latest heavy metal news',
                'reviews'    => 'heavy metal album reviews',
                'lists'      => 'best heavy metal lists rankings',
                'videos'     => 'heavy metal music videos',
                'interviews' => 'heavy metal band interviews',
            ];
            $topic = $map[$type] ?? $map['news'];

            $prompt = <<<PROMPT
Eres un redactor de noticias de metal en español para heavymetalbrothers.com.

FECHA ACTUAL: {$today}

ENFOQUE REGIONAL: {$regionText}

FUENTES PERMITIDAS (UNICAS QUE PUEDES USAR):
{$sitesFormal}

REGLAS ESTRICTAS:
1. SOLO busca en las fuentes listadas. NO uses otras (NO Ticketmaster, NO Wikipedia, NO fuentes generalistas).
2. Los {$num} articulos deben ser de fuentes DIFERENTES entre si.
3. Noticias de los ULTIMOS 7 DIAS y que encajen con el enfoque regional indicado.

EVITAR DUPLICADOS - estos ya estan importados, NO repitas ni traigas variantes del mismo evento:

TITULOS YA IMPORTADOS:
{$skipTitlesStr}

URLS YA IMPORTADAS:
{$skipUrlsStr}

REDACCION - MUY IMPORTANTE:
1. Lee la noticia original y comprende los HECHOS.
2. CIERRA mentalmente la fuente y escribe TU PROPIO articulo desde cero en español.
3. PROHIBIDO: traducir frase a frase, copiar la estructura de parrafos del original, usar las mismas metaforas o expresiones.
4. OBLIGATORIO: estructura propia diferente al original, tus propias transiciones, tu propio enfoque periodistico. Puedes reordenar la informacion, empezar por un angulo distinto, añadir contexto de la banda que tu conozcas.
5. VERIFICA antes de incluir cada articulo: si pusieras tu texto y el original lado a lado traducido, NO deberian parecerse en estructura ni redaccion. Solo comparten los hechos noticiosos.
6. Las citas textuales de musicos SI pueden mantenerse como citas (traducidas), marcadas con comillas y maximo 2 por articulo.

PROCESO:
1. Para cada fuente, busca noticias recientes que encajen con la region.
2. Extrae la URL de la imagen principal (og:image).
3. Redacta tu articulo original en español, minimo 200 palabras.

FORMATO JSON (sin markdown, empieza con [ termina con ]):
[
  {
    "titulo": "Titulo original en español (no traduccion literal del original)",
    "fuente": "Nombre de la fuente",
    "url_original": "URL del articulo",
    "imagen_url": "URL completa imagen (https://...)",
    "fecha": "YYYY-MM-DD",
    "intro": "Resumen 2-3 frases",
    "contenido": "Articulo redactado desde cero, minimo 200 palabras",
    "etiquetas": "palabras, clave",
    "metadesc": "Meta SEO menos 155 caracteres"
  }
]

Devuelve {$num} objetos de fuentes DIFERENTES, region correcta, redaccion 100% propia.
PROMPT;

            if (!function_exists('curl_init')) {
                $this->jsonOut(['success' => false, 'message' => 'cURL no disponible en el servidor.']);
                return;
            }

            // Reintentos con backoff exponencial para errores 429/529 (rate limit / overloaded)
            $body = ''; $code = 0; $err = ''; $attempt = 0; $maxAttempts = 4;
            while ($attempt < $maxAttempts) {
                $attempt++;
                $ch = curl_init('https://api.anthropic.com/v1/messages');
                curl_setopt_array($ch, [
                    CURLOPT_POST            => true,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_TIMEOUT         => 150,
                    CURLOPT_CONNECTTIMEOUT  => 30,
                    CURLOPT_HTTPHEADER      => [
                        'Content-Type: application/json',
                        'x-api-key: ' . $apiKey,
                        'anthropic-version: 2023-06-01',
                    ],
                    CURLOPT_POSTFIELDS => json_encode([
                        'model'      => 'claude-haiku-4-5',
                        'max_tokens' => 4000,
                        'tools'      => [['type' => 'web_search_20250305', 'name' => 'web_search']],
                        'messages'   => [['role' => 'user', 'content' => $prompt]],
                    ], JSON_UNESCAPED_UNICODE),
                ]);
                $body = curl_exec($ch);
                $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err  = curl_error($ch);
                curl_close($ch);

                // Si OK o error no reintentable, salir
                if ($code === 200 || ($code !== 429 && $code !== 529 && $code !== 503 && $code !== 0)) {
                    break;
                }
                if ($attempt < $maxAttempts) {
                    sleep((int) pow(2, $attempt)); // 2s, 4s, 8s
                }
            }

            if ($err) { $this->jsonOut(['success' => false, 'message' => 'Error cURL: ' . $err]); return; }
            $data = json_decode($body, true);
            if ($code !== 200) {
                $msg = $data['error']['message'] ?? ('HTTP ' . $code);
                $type = $data['error']['type'] ?? '';
                $debug = 'Status: ' . $code . ' | Type: ' . $type . ' | Body preview: ' . substr($body, 0, 500);
                $this->jsonOut(['success' => false, 'message' => 'API error: ' . $msg, 'debug' => $debug]);
                return;
            }

            $text = '';
            foreach ((array)($data['content'] ?? []) as $b) {
                if (($b['type'] ?? '') === 'text') $text .= $b['text'];
            }
            if (!$text) {
                $this->jsonOut(['success' => false, 'message' => 'API sin texto en respuesta', 'debug' => substr(json_encode($data), 0, 800)]);
                return;
            }
            // Limpiar markdown si Claude devolvio ```json ... ```
            $cleaned = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);
            $cleaned = trim($cleaned);
            $arts = json_decode($cleaned, true);
            if (!is_array($arts)) {
                // Intentar extraer JSON array entre corchetes
                if (preg_match('/(\[[\s\S]*\])/u', $cleaned, $m)) {
                    $arts = json_decode($m[1], true);
                }
            }
            if (!is_array($arts)) {
                $this->jsonOut(['success' => false, 'message' => 'JSON invalido. Texto: ' . substr($text, 0, 600)]);
                return;
            }

            // Si Claude devolvio array vacio, diagnosticar
            if (count($arts) === 0) {
                $blockTypes = [];
                $hasWebSearch = false;
                foreach ((array)($data['content'] ?? []) as $b) {
                    $t = ($b['type'] ?? '?');
                    $blockTypes[] = $t;
                    if ($t === 'server_tool_use' || $t === 'tool_use' || $t === 'web_search_tool_result') {
                        $hasWebSearch = true;
                    }
                }
                $stopReason = $data['stop_reason'] ?? 'unknown';
                $usage = $data['usage'] ?? [];
                $tokens = 'in=' . ($usage['input_tokens'] ?? '?') . ' out=' . ($usage['output_tokens'] ?? '?');
                $msg = 'Array vacio. Web search usado: ' . ($hasWebSearch ? 'SI' : 'NO') . ' | Stop: ' . $stopReason . ' | Bloques: ' . implode(',', $blockTypes) . ' | Tokens: ' . $tokens . ' | Texto: ' . substr($text, 0, 400);
                $this->jsonOut(['success' => false, 'message' => $msg]);
                return;
            }

            $userId = (int)($app->getIdentity()?->id ?? 0);
            $now    = Factory::getDate()->toSql();
            $created = [];
            $skipped = [];

            foreach ($arts as $idx => $art) {
                // Aceptar tanto nombres en español como en inglés
                $title = trim((string)($art['titulo'] ?? $art['title'] ?? $art['titulo_es'] ?? ''));
                if (!$title) {
                    $skipped[] = 'art[' . $idx . '] sin titulo. Campos: ' . implode(',', array_keys((array)$art));
                    continue;
                }

                // Verificacion de redaccion: contenido en español y longitud minima
                $contentText = trim((string)($art['contenido'] ?? $art['content'] ?? $art['body'] ?? ''));
                $wordCount = str_word_count(strip_tags($contentText));
                if ($wordCount < 120) {
                    $skipped[] = 'art[' . $idx . '] contenido muy corto (' . $wordCount . ' palabras): ' . substr($title, 0, 60);
                    continue;
                }
                // Heuristica español: contar palabras funcionales españolas vs inglesas
                $esWords = preg_match_all('/\b(el|la|los|las|de|del|que|con|para|una|uno|su|por|como|mas|pero|este|esta)\b/iu', $contentText);
                $enWords = preg_match_all('/\b(the|and|with|for|that|this|from|has|have|will|their|about)\b/i', $contentText);
                if ($enWords > $esWords) {
                    $skipped[] = 'art[' . $idx . '] contenido parece estar en ingles (no redactado): ' . substr($title, 0, 60);
                    continue;
                }

                // ── ANTI-DUPLICADOS ─────────────────────────────────────────
                $sourceUrl = trim((string)($art['url_original'] ?? $art['url'] ?? $art['source_url'] ?? ''));
                $sourceName = trim((string)($art['fuente'] ?? $art['source'] ?? ''));

                // 0. Validar que la fuente sea una de las activas (URL + nombre)
                $allowedDomains = array_column($srcs, 'url');
                $allowedNames = array_map('mb_strtolower', array_column($srcs, 'name'));
                $isAllowed = false;
                if ($sourceUrl) {
                    foreach ($allowedDomains as $dom) {
                        $domClean = preg_replace('#^https?://(www\.)?#i', '', $dom);
                        $domClean = rtrim($domClean, '/');
                        if (stripos($sourceUrl, $domClean) !== false) { $isAllowed = true; break; }
                    }
                }
                // Si la URL no encaja, probar con el nombre de fuente
                if (!$isAllowed && $sourceName) {
                    $srcNameLow = mb_strtolower($sourceName);
                    foreach ($allowedNames as $allowedName) {
                        if (stripos($srcNameLow, $allowedName) !== false || stripos($allowedName, $srcNameLow) !== false) {
                            $isAllowed = true; break;
                        }
                    }
                }
                if (!$isAllowed) {
                    $skipped[] = 'art[' . $idx . '] fuente NO permitida: ' . substr($sourceName . ' / ' . $sourceUrl, 0, 100);
                    continue;
                }

                // 1. Si la URL original ya fue importada, saltar (normalizar antes)
                $normUrl = preg_replace('#[?#].*$#', '', rtrim($sourceUrl, '/'));
                if ($normUrl) {
                    $exists = (int)$db->setQuery(
                        $db->getQuery(true)
                            ->select('COUNT(*)')
                            ->from('#__hmb_imported')
                            ->where('source_url LIKE ' . $db->quote($normUrl . '%'))
                    )->loadResult();
                    if ($exists > 0) {
                        $skipped[] = 'art[' . $idx . '] URL ya importada: ' . substr($sourceUrl, 0, 80);
                        continue;
                    }
                }

                // 2. Anti-duplicado por similitud de titulo (no solo exacto)
                $normTitle = mb_strtolower($title, 'UTF-8');
                $normTitle = preg_replace('/[^a-z0-9 ]/u', '', $normTitle);
                $titleWords = array_filter(explode(' ', $normTitle), fn($w) => mb_strlen($w) > 3);

                $existingTitles = $db->setQuery(
                    $db->getQuery(true)
                        ->select('title')
                        ->from('#__content')
                        ->where('catid = ' . (int)$catId)
                        ->order('created DESC')
                        ->setLimit(200)
                )->loadColumn() ?? [];

                $isDup = false;
                foreach ($existingTitles as $existing) {
                    $normExisting = preg_replace('/[^a-z0-9 ]/u', '', mb_strtolower($existing, 'UTF-8'));
                    $existingWords = array_filter(explode(' ', $normExisting), fn($w) => mb_strlen($w) > 3);
                    if (empty($titleWords) || empty($existingWords)) continue;
                    $common = array_intersect($titleWords, $existingWords);
                    $similarity = count($common) / max(count($titleWords), count($existingWords));
                    if ($similarity >= 0.6) { // 60% palabras clave coinciden
                        $isDup = true;
                        $skipped[] = 'art[' . $idx . '] similar a existente (' . round($similarity * 100) . '%): ' . substr($title, 0, 60);
                        break;
                    }
                }
                if ($isDup) continue;

                $sl   = strtr(mb_strtolower($title, 'UTF-8'), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n']);
                $sl   = preg_replace('/[^a-z0-9\s\-]/', '', $sl);
                $sl   = trim(preg_replace('/[\s\-]+/', '-', $sl), '-');
                $slug = substr($sl, 0, 100);

                $existing = $db->setQuery($db->getQuery(true)->select('alias')->from('#__content')->where('alias LIKE ' . $db->quote($slug . '%')))->loadColumn();
                $alias    = in_array($slug, $existing, true) ? $slug . '-' . substr(md5(microtime(true)), 0, 6) : $slug;

                try {
                    $artDate = Factory::getDate(trim((string)($art['fecha'] ?? $art['date'] ?? '')))->toSql();
                } catch (\Exception $e) {
                    $artDate = $now;
                }

                $imgUrl = trim((string)($art['imagen_url'] ?? $art['image_url'] ?? $art['image'] ?? $art['imagen'] ?? ''));
                // Validar que la URL parece valida
                if ($imgUrl && !preg_match('#^https?://#i', $imgUrl)) $imgUrl = '';
                $imgs = json_encode([
                    'image_intro'         => $imgUrl,
                    'float_intro'         => '',
                    'image_intro_alt'     => $title,
                    'image_intro_caption' => '',
                    'image_fulltext'      => $imgUrl,
                    'float_fulltext'      => '',
                    'image_fulltext_alt'  => $title,
                    'image_fulltext_caption' => '',
                ]);

                try {
                    // Usar el modelo nativo de com_content para que cree el asset, workflow, etc
                    $mvcFactory = Factory::getApplication()->bootComponent('com_content')->getMVCFactory();
                    $articleModel = $mvcFactory->createModel('Article', 'Administrator', ['ignore_request' => true]);

                    $articleData = [
                        'id'           => 0,
                        'title'        => $title,
                        'alias'        => $alias,
                        'introtext'    => trim((string)($art['intro'] ?? $art['introduccion'] ?? $art['summary'] ?? '')),
                        'fulltext'     => trim((string)($art['contenido'] ?? $art['content'] ?? $art['body'] ?? '')),
                        'state'        => $state,
                        'catid'        => $catId,
                        'created'      => $artDate,
                        'created_by'   => $userId,
                        'language'     => $lang,
                        'access'       => 1,
                        'images'       => $imgs,
                        'urls'         => '{}',
                        'attribs'      => '{}',
                        'metadata'     => '{}',
                        'metadesc'     => trim((string)($art['metadesc'] ?? $art['meta'] ?? $art['description'] ?? '')),
                        'metakey'      => trim((string)($art['etiquetas'] ?? $art['tags'] ?? $art['keywords'] ?? '')),
                        'featured'     => 0,
                        'publish_up'   => $artDate,
                        'publish_down' => null,
                        'rules'        => [],
                        'associations' => [],
                        'tags'         => [],
                    ];

                    if (!$articleModel->save($articleData)) {
                        throw new \Exception($articleModel->getError() ?: 'Error al guardar articulo');
                    }
                    $aid = (int)$articleModel->getState($articleModel->getName() . '.id');

                    if ($aid > 0) {
                        $importObj = new \stdClass();
                        $importObj->article_id  = $aid;
                        $importObj->source_site = trim((string)($art['fuente'] ?? $art['source'] ?? ''));
                        $importObj->source_url  = trim((string)($art['url_original'] ?? $art['url'] ?? $art['source_url'] ?? ''));
                        $importObj->imported_at = $now;
                        $importObj->news_date   = substr($artDate, 0, 10);
                        $db->insertObject('#__hmb_imported', $importObj);
                        $created[] = ['article_id' => $aid, 'title' => $title, 'state' => $state, 'imported_at' => $now];
                    } else {
                        throw new \Exception('No se obtuvo ID del articulo creado');
                    }
                } catch (\Throwable $e) {
                    $skipped[] = 'art[' . $idx . '] DB error: ' . $e->getMessage();
                    continue;
                }
            }

            $n = count($created);
            $totalArts = count($arts);
            if ($n === 0 && $totalArts > 0) {
                // Devolvió items pero todos saltados: enseñar por qué
                $this->jsonOut([
                    'success' => false,
                    'message' => 'Recibidos ' . $totalArts . ' items pero ninguno valido. Razones: ' . implode(' | ', $skipped)
                ]);
                return;
            }
            $this->jsonOut(['success' => true, 'created' => $n, 'articles' => $created, 'message' => $n . ' articulo' . ($n === 1 ? '' : 's') . ' importado' . ($n === 1 ? '' : 's') . '.']);
        } catch (\Throwable $e) {
            $this->jsonOut(['success' => false, 'message' => 'importArticles: ' . $e->getMessage()]);
        }
    }
}
