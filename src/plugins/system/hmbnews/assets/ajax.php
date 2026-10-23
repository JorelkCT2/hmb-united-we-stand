<?php
/**
 * HMB News Importer – AJAX Helper
 * Pure PHP helper class – no Joomla MVC inheritance required.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

class HmbAjaxHelper
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    // ── Internal ────────────────────────────────────────────────────────

    private function out(array $data): void
    {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->app->close();
    }

    private function db()
    {
        return Factory::getDbo();
    }

    private function params()
    {
        return ComponentHelper::getParams('com_hmb');
    }

    private function post(string $key, string $type = 'string', $default = '')
    {
        $input = $this->app->input;
        switch ($type) {
            case 'int':    return (int)    $input->post->getInt($key, (int)    $default);
            case 'string': return trim((string) $input->post->getString($key, (string) $default));
            default:       return $input->post->get($key, $default);
        }
    }

    // ── SOURCES ─────────────────────────────────────────────────────────

    public function getSources(): void
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select(['id','name','url','active','ordering'])
            ->from($db->quoteName('#__hmb_sources'))
            ->order('ordering ASC');
        $this->out(['success' => true, 'sources' => ($db->setQuery($q)->loadAssocList() ?? [])]);
    }

    public function addSource(): void
    {
        $name = $this->post('name');
        $url  = $this->post('url');
        if (!$name || !$url) { $this->out(['success' => false, 'message' => 'Nombre y URL requeridos']); return; }

        $db  = $this->db();
        $max = (int) $db->setQuery(
            $db->getQuery(true)->select('MAX(ordering)')->from($db->quoteName('#__hmb_sources'))
        )->loadResult();

        $row = (object) ['name' => $name, 'url' => $url, 'active' => 1, 'ordering' => $max + 1];
        $db->insertObject('#__hmb_sources', $row);
        $row->id = (int) $db->insertid();
        $this->out(['success' => true, 'source' => (array) $row]);
    }

    public function deleteSource(): void
    {
        $id = $this->post('id', 'int');
        if (!$id) { $this->out(['success' => false, 'message' => 'ID inválido']); return; }
        $db = $this->db();
        $db->setQuery($db->getQuery(true)->delete($db->quoteName('#__hmb_sources'))->where('id=' . $id))->execute();
        $this->out(['success' => true]);
    }

    public function toggleSource(): void
    {
        $id     = $this->post('id', 'int');
        $active = $this->post('active', 'int') ? 1 : 0;
        if (!$id) { $this->out(['success' => false, 'message' => 'ID inválido']); return; }
        $db = $this->db();
        $db->setQuery(
            $db->getQuery(true)->update($db->quoteName('#__hmb_sources'))
                ->set('active=' . $active)->where('id=' . $id)
        )->execute();
        $this->out(['success' => true, 'active' => $active]);
    }

    public function updateSource(): void
    {
        $id   = $this->post('id', 'int');
        $name = $this->post('name');
        $url  = $this->post('url');
        if (!$id || !$name || !$url) { $this->out(['success' => false, 'message' => 'Datos incompletos']); return; }
        $db = $this->db();
        $db->setQuery(
            $db->getQuery(true)->update($db->quoteName('#__hmb_sources'))
                ->set('name=' . $db->quote($name))
                ->set('url='  . $db->quote($url))
                ->where('id=' . $id)
        )->execute();
        $this->out(['success' => true]);
    }

    // ── ARTICLES ────────────────────────────────────────────────────────

    public function getArticles(): void
    {
        $db = $this->db();
        $q  = $db->getQuery(true)
            ->select([
                'i.id AS import_id', 'i.article_id', 'i.source_site',
                'i.source_url', 'i.imported_at',
                'c.title', 'c.alias', 'c.state', 'c.images',
            ])
            ->from($db->quoteName('#__hmb_imported', 'i'))
            ->leftJoin($db->quoteName('#__content', 'c') . ' ON c.id = i.article_id')
            ->order('i.imported_at DESC');

        $rows = $db->setQuery($q)->loadAssocList() ?? [];
        foreach ($rows as &$r) {
            $imgs       = json_decode($r['images'] ?? '{}', true);
            $r['thumb'] = $imgs['image_intro'] ?? '';
            unset($r['images']);
        }
        $this->out(['success' => true, 'articles' => $rows]);
    }

    public function deleteArticle(): void
    {
        $aid = $this->post('article_id', 'int');
        $iid = $this->post('import_id', 'int');
        if (!$aid || !$iid) { $this->out(['success' => false, 'message' => 'IDs inválidos']); return; }
        $db = $this->db();
        $db->setQuery($db->getQuery(true)->delete($db->quoteName('#__content'))->where('id=' . $aid))->execute();
        $db->setQuery($db->getQuery(true)->delete($db->quoteName('#__hmb_imported'))->where('id=' . $iid))->execute();
        $this->out(['success' => true]);
    }

    // ── IMPORT ──────────────────────────────────────────────────────────

    public function importArticles(): void
    {
        @set_time_limit(180);

        $params  = $this->params();
        $apiKey  = trim((string) $params->get('api_key', ''));
        $catId   = (int) ($this->post('catid', 'int') ?: $params->get('default_category', 21));
        $num     = min(15, max(1, (int) ($this->post('num', 'int') ?: $params->get('articles_per_run', 5))));
        $type    = $this->post('content_type') ?: 'news';
        $state   = (int) $params->get('article_state', 0);
        $lang    = (string) $params->get('article_language', 'es-ES') ?: 'es-ES';

        if (!$apiKey) {
            $this->out(['success' => false, 'message' => 'Configura la API Key en Ajustes del componente.']);
            return;
        }

        $db   = $this->db();
        $srcQ = $db->getQuery(true)
            ->select(['name', 'url'])
            ->from($db->quoteName('#__hmb_sources'))
            ->where('active=1')
            ->order('ordering ASC');
        $srcs = $db->setQuery($srcQ)->loadAssocList() ?? [];

        if (empty($srcs)) {
            $this->out(['success' => false, 'message' => 'No hay fuentes activas.']);
            return;
        }

        $sites = implode(', ', array_column($srcs, 'url'));
        $map   = [
            'news'       => 'latest heavy metal and rock music news',
            'reviews'    => 'new heavy metal album reviews',
            'lists'      => 'best heavy metal songs albums rankings',
            'videos'     => 'heavy metal music videos live concerts',
            'interviews' => 'heavy metal band interviews',
        ];
        $topic = $map[$type] ?? $map['news'];

        $prompt =
            "You are a heavy metal journalism assistant for the Spanish site heavymetalbrothers.com.\n\n" .
            "Search the web and find the {$num} most recent articles about \"{$topic}\" published on: {$sites}\n\n" .
            "Return ONLY a valid raw JSON array (absolutely no markdown, no backticks, no text outside the JSON):\n" .
            "[\n  {\n" .
            "    \"titulo\": \"Article title in natural Spanish\",\n" .
            "    \"fuente\": \"Source site name\",\n" .
            "    \"url_original\": \"Full article URL\",\n" .
            "    \"fecha\": \"YYYY-MM-DD\",\n" .
            "    \"intro\": \"2-3 sentence Spanish teaser. Hook the reader.\",\n" .
            "    \"contenido\": \"Full body translated to Spanish, min 200 words. Keep band/album/song names original.\",\n" .
            "    \"etiquetas\": \"comma-separated Spanish keywords\",\n" .
            "    \"metadesc\": \"Spanish SEO meta description under 155 chars\"\n" .
            "  }\n]\n\n" .
            "Rules: translate to natural Spanish except proper nouns. Return ONLY the JSON array.";

        $apiResult = $this->callApi($apiKey, $prompt);
        if (!$apiResult['success']) {
            $this->out($apiResult);
            return;
        }

        $userId  = (int) ($this->app->getIdentity()?->id ?? 0);
        $now     = Factory::getDate()->toSql();
        $created = [];

        foreach ($apiResult['articles'] as $art) {
            $title = trim((string) ($art['titulo'] ?? ''));
            if (!$title) continue;

            $alias   = $this->uniqueAlias($this->slug($title));
            $intro   = trim((string) ($art['intro']     ?? ''));
            $full    = trim((string) ($art['contenido'] ?? ''));
            $meta    = trim((string) ($art['metadesc']  ?? substr($intro, 0, 155)));
            $tags    = trim((string) ($art['etiquetas'] ?? ''));
            $srcUrl  = trim((string) ($art['url_original'] ?? ''));
            $srcSite = trim((string) ($art['fuente']    ?? ''));
            $rawDate = trim((string) ($art['fecha']     ?? ''));

            try { $artDate = $rawDate ? Factory::getDate($rawDate)->toSql() : $now; }
            catch (\Exception $e) { $artDate = $now; }

            $imgs = json_encode([
                'image_intro'=>'','float_intro'=>'','image_intro_alt'=>'','image_intro_caption'=>'',
                'image_fulltext'=>'','float_fulltext'=>'','image_fulltext_alt'=>'','image_fulltext_caption'=>'',
            ]);

            try {
                $db->insertObject('#__content', (object) [
                    'title'       => $title,    'alias'       => $alias,
                    'introtext'   => $intro,    'fulltext'    => $full,
                    'state'       => $state,    'catid'       => $catId,
                    'created'     => $artDate,  'created_by'  => $userId,
                    'modified'    => $now,      'modified_by' => $userId,
                    'language'    => $lang,     'access'      => 1,
                    'images'      => $imgs,     'urls'        => '{}',
                    'attribs'     => '{}',      'metadata'    => '{}',
                    'metadesc'    => $meta,     'metakey'     => $tags,
                    'featured'    => 0,         'version'     => 1,
                    'hits'        => 0,         'publish_up'  => $artDate,
                    'publish_down'=> '',
                ]);
                $aid = (int) $db->insertid();
                if ($aid > 0) {
                    $db->insertObject('#__hmb_imported', (object) [
                        'article_id'  => $aid,
                        'source_site' => $srcSite,
                        'source_url'  => $srcUrl,
                        'imported_at' => $now,
                    ]);
                    $created[] = ['article_id' => $aid, 'title' => $title, 'source_site' => $srcSite, 'state' => $state, 'imported_at' => $now];
                }
            } catch (\Exception $e) { continue; }
        }

        $n = count($created);
        $this->out([
            'success'  => true,
            'created'  => $n,
            'articles' => $created,
            'message'  => $n . ' artículo' . ($n === 1 ? '' : 's') . ' importado' . ($n === 1 ? '' : 's') . '.',
        ]);
    }

    // ── Anthropic API ────────────────────────────────────────────────────

    private function callApi(string $key, string $prompt): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'cURL no disponible en el servidor.'];
        }
        $payload = json_encode([
            'model'      => 'claude-sonnet-4-20250514',
            'max_tokens' => 8000,
            'tools'      => [['type' => 'web_search_20250305', 'name' => 'web_search']],
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 150,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $key,
                'anthropic-version: 2023-06-01',
                'anthropic-beta: web-search-2025-03-05',
            ],
            CURLOPT_POSTFIELDS     => $payload,
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) return ['success' => false, 'message' => 'Error cURL: ' . $err];
        $data = json_decode($body, true);
        if ($code !== 200) return ['success' => false, 'message' => 'Error API: ' . ($data['error']['message'] ?? 'HTTP ' . $code)];

        $text = '';
        foreach ((array) ($data['content'] ?? []) as $b) {
            if (($b['type'] ?? '') === 'text') $text .= $b['text'];
        }
        if (!$text) return ['success' => false, 'message' => 'La API no devolvió texto.'];
        if (!preg_match('/(\[[\s\S]*\])/u', $text, $m)) {
            return ['success' => false, 'message' => 'Sin JSON en la respuesta. Texto: ' . mb_substr($text, 0, 200)];
        }
        $arts = json_decode($m[1], true);
        if (!is_array($arts)) return ['success' => false, 'message' => 'JSON inválido.'];
        return ['success' => true, 'articles' => $arts];
    }

    // ── Utilities ────────────────────────────────────────────────────────

    private function slug(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u','ä'=>'a','ö'=>'o']);
        $s = preg_replace('/[^a-z0-9\s\-]/', '', $s);
        $s = preg_replace('/[\s\-]+/', '-', $s);
        return trim(substr($s, 0, 100), '-');
    }

    private function uniqueAlias(string $base): string
    {
        if (!$base) $base = 'articulo';
        $db       = $this->db();
        $existing = $db->setQuery(
            $db->getQuery(true)->select('alias')->from($db->quoteName('#__content'))
                ->where('alias LIKE ' . $db->quote($base . '%'))
        )->loadColumn();
        return in_array($base, $existing, true) ? $base . '-' . substr(md5(microtime(true)), 0, 6) : $base;
    }
}
