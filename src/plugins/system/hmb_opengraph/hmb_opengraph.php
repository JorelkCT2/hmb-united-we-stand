<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class PlgSystemHmb_opengraph extends CMSPlugin
{
    protected $autoloadLanguage = true;

    public function onAfterRender(): void
    {
        $app = Factory::getApplication();

        if (!$app->isClient('site')) return;

        $input  = $app->getInput();
        $option = $input->get('option', '');
        $view   = $input->get('view', '');
        $id     = $input->getInt('id', 0);

        $siteName    = $this->params->get('site_name', 'Heavy Metal Brothers');
        $fallbackImg = trim($this->params->get('fallback_image', ''));
        $twitterCard = $this->params->get('twitter_card', 'summary_large_image');
        $twitterSite = trim($this->params->get('twitter_site', ''));
        $siteUrl     = rtrim(Uri::root(), '/');

        $ogType  = 'website';
        $ogTitle = '';
        $ogDesc  = '';
        $ogImage = '';
        $ogUrl   = Uri::getInstance()->toString();

        // Solo artículos individuales
        if ($option === 'com_content' && $view === 'article' && $id > 0) {
            $ogType = 'article';
            try {
                $db    = Factory::getDbo();
                $query = $db->getQuery(true)
                    ->select(['title', 'introtext', 'fulltext', 'images', 'metadesc'])
                    ->from($db->quoteName('#__content'))
                    ->where($db->quoteName('id') . ' = ' . $id)
                    ->where($db->quoteName('state') . ' = 1');
                $db->setQuery($query);
                $article = $db->loadObject();

                if ($article) {
                    $ogTitle = $article->title;

                    // Descripción
                    if (!empty($article->metadesc)) {
                        $ogDesc = strip_tags($article->metadesc);
                    } elseif (!empty($article->introtext)) {
                        $ogDesc = html_entity_decode(strip_tags($article->introtext), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $ogDesc = trim(preg_replace('/\s+/', ' ', $ogDesc));
                        if (mb_strlen($ogDesc) > 200) {
                            $ogDesc = mb_substr($ogDesc, 0, 200) . '…';
                        }
                    }

                    // Imagen: image_intro > image_fulltext > primera <img> del cuerpo
                    $images = json_decode($article->images ?? '{}', true);
                    $ogImage = $images['image_intro'] ?? '';
                    if (empty($ogImage)) $ogImage = $images['image_fulltext'] ?? '';
                    if (empty($ogImage)) {
                        $html = ($article->introtext ?? '') . ($article->fulltext ?? '');
                        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
                            $ogImage = $m[1];
                        }
                    }
                }
            } catch (\Exception $e) {}
        }

        // Fallback de imagen
        if (empty($ogImage) && !empty($fallbackImg)) {
            $ogImage = $fallbackImg;
        }

        // URL absoluta de imagen
        if (!empty($ogImage) && strpos($ogImage, 'http') !== 0) {
            $ogImage = $siteUrl . '/' . ltrim($ogImage, '/');
        }

        if (empty($ogTitle)) {
            $ogTitle = Factory::getApplication()->getDocument()->getTitle();
        }

        // Construir bloque de metas a inyectar
        $metas = [];
        $metas[] = '<meta property="og:type" content="' . htmlspecialchars($ogType, ENT_QUOTES) . '">';
        $metas[] = '<meta property="og:site_name" content="' . htmlspecialchars($siteName, ENT_QUOTES) . '">';
        $metas[] = '<meta property="og:url" content="' . htmlspecialchars($ogUrl, ENT_QUOTES) . '">';
        $metas[] = '<meta property="og:title" content="' . htmlspecialchars($ogTitle, ENT_QUOTES) . '">';

        if (!empty($ogDesc)) {
            $metas[] = '<meta property="og:description" content="' . htmlspecialchars($ogDesc, ENT_QUOTES) . '">';
        }
        if (!empty($ogImage)) {
            $metas[] = '<meta property="og:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES) . '">';
            $metas[] = '<meta property="og:image:width" content="1200">';
            $metas[] = '<meta property="og:image:alt" content="' . htmlspecialchars($ogTitle, ENT_QUOTES) . '">';
        }

        // Twitter Card
        $metas[] = '<meta name="twitter:card" content="' . htmlspecialchars($twitterCard, ENT_QUOTES) . '">';
        $metas[] = '<meta name="twitter:title" content="' . htmlspecialchars($ogTitle, ENT_QUOTES) . '">';
        if (!empty($ogDesc)) {
            $metas[] = '<meta name="twitter:description" content="' . htmlspecialchars($ogDesc, ENT_QUOTES) . '">';
        }
        if (!empty($ogImage)) {
            $metas[] = '<meta name="twitter:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES) . '">';
        }
        if (!empty($twitterSite)) {
            $metas[] = '<meta name="twitter:site" content="' . htmlspecialchars($twitterSite, ENT_QUOTES) . '">';
        }

        $inject = "\n<!-- HMB Open Graph -->\n" . implode("\n", $metas) . "\n<!-- /HMB Open Graph -->\n";

        // Obtener HTML de salida y reemplazar og:image existente
        $body = $app->getBody();

        // Eliminar cualquier og:image/og:title previo para evitar duplicados
        $body = preg_replace('/<meta\s+property="og:[^"]+"\s+content="[^"]*"\s*\/?>/i', '', $body);
        $body = preg_replace('/<meta\s+name="twitter:[^"]+"\s+content="[^"]*"\s*\/?>/i', '', $body);

        // Inyectar antes de </head>
        $body = str_replace('</head>', $inject . '</head>', $body);

        $app->setBody($body);
    }
}
