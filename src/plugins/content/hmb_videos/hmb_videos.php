<?php
/**
 * HMB Videos - Content Plugin
 * Permite insertar vídeos de la galería HMB en artículos de Joomla.
 *
 * Uso en el artículo:
 *   {hmb_video id=5}                  → vídeo por ID de la tabla
 *   {hmb_video youtube=dQw4w9WgXcQ}   → vídeo directo por YouTube ID
 *   {hmb_video id=5 width=600}        → con ancho personalizado
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

class PlgContentHmb_videos extends CMSPlugin
{
    protected $autoloadLanguage = true;

    /**
     * onContentPrepare — procesa el shortcode {hmb_video ...} en el contenido
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0): void
    {
        if (!isset($article->text) || strpos($article->text, '{hmb_video') === false) {
            return;
        }

        $article->text = preg_replace_callback(
            '/\{hmb_video\s+([^}]+)\}/i',
            [$this, 'replaceShortcode'],
            $article->text
        );
    }

    private function replaceShortcode(array $matches): string
    {
        $atts = $this->parseAtts($matches[1]);

        $youtubeId = '';
        $width     = $atts['width'] ?? $this->params->get('width', '100%');
        $autoplay  = (int)($atts['autoplay'] ?? $this->params->get('autoplay', 0));

        // Get YouTube ID - from direct youtube param or from DB by id
        if (!empty($atts['youtube'])) {
            $youtubeId = trim($atts['youtube']);
        } elseif (!empty($atts['id'])) {
            $youtubeId = $this->getYoutubeIdFromDb((int)$atts['id']);
        }

        if (empty($youtubeId)) {
            return '<!-- HMB Videos: vídeo no encontrado -->';
        }

        $autoplayParam = $autoplay ? '&autoplay=1' : '';
        $src = 'https://www.youtube.com/embed/' . htmlspecialchars($youtubeId, ENT_QUOTES) . '?rel=0&modestbranding=1' . $autoplayParam;

        // Width: if numeric without unit, add px
        if (is_numeric($width)) {
            $width .= 'px';
        }

        return <<<HTML
<div class="hmb-article-video" style="width:{$width};max-width:100%;margin:1.5em auto;">
    <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;background:#000;border-radius:4px;">
        <iframe
            src="{$src}"
            style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
            loading="lazy">
        </iframe>
    </div>
</div>
HTML;
    }

    private function getYoutubeIdFromDb(int $id): string
    {
        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select($db->quoteName('youtube_id'))
                ->from($db->quoteName('#__hmb_videos'))
                ->where($db->quoteName('id') . ' = ' . $id)
                ->where($db->quoteName('state') . ' = 1');
            return (string) ($db->setQuery($query)->loadResult() ?? '');
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Parse shortcode attributes: id=5 width=600 autoplay=1
     */
    private function parseAtts(string $text): array
    {
        $atts = [];
        preg_match_all('/(\w+)=(["\']?)([^"\'>\s]+)\2/', $text, $m, PREG_SET_ORDER);
        foreach ($m as $match) {
            $atts[strtolower($match[1])] = $match[3];
        }
        return $atts;
    }
}
