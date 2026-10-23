<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class PlgSystemHmb_seo extends CMSPlugin
{
    public function onBeforeRender()
    {
        $app = Factory::getApplication();

        if (!$app->isClient('site')) {
            return;
        }

        $document = $app->getDocument();

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
            $currentTitle = $document->getTitle();
            if ($currentTitle && strpos($currentTitle, $siteName) === false) {
                $document->setTitle($currentTitle . $separator . $siteName);
            } elseif (!$currentTitle) {
                $document->setTitle($siteName);
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

        if (!$hasCanonical) {
            $uri   = Uri::getInstance();
            $keep  = ['view', 'id', 'artista_id', 'catid', 'start', 'layout'];
            $query = array_intersect_key($uri->getQuery(true), array_flip($keep));
            $url   = 'https://www.heavymetalbrothers.com' . $uri->getPath();

            if ($query) {
                $url .= '?' . http_build_query($query);
            }

            $document->addHeadLink(htmlspecialchars($url), 'canonical');
        }

        // Corregir OG tags si está activado
        if ($fixOgTags) {
            $document->setMetaData('og:site_name', $siteName, 'property');
            $document->setMetaData('og:title', $document->getTitle(), 'property');
        }
    }

    /**
     * Un único <h1> por página: si no hay ninguno se añade uno oculto con el
     * título; los <h1> sobrantes (p. ej. dentro del texto del artículo) pasan a <h2>.
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

        $app->setBody($body);
    }
}
