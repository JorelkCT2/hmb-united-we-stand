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

        // Corregir OG tags si está activado
        if ($fixOgTags) {
            $document->setMetaData('og:site_name', $siteName, 'property');
            $document->setMetaData('og:title', $document->getTitle(), 'property');
        }
    }
}
