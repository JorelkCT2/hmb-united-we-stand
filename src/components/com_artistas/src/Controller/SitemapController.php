<?php
namespace Joomla\Component\Artistas\Site\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

class SitemapController extends BaseController
{
    public function generate(): void
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('SELECT id, name, alias, modified FROM #__artistas WHERE state=1 ORDER BY modified DESC');
        $artistas = $db->loadObjectList();

        $fichaItemid = RouteHelper::getItemid('artista');
        $listItemid  = RouteHelper::getItemid('artistas');
        $base        = Uri::root();

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        echo '  <url><loc>' . $base . 'index.php?option=com_artistas&amp;view=artistas' . ($listItemid ? '&amp;Itemid='.$listItemid : '') . '</loc><changefreq>daily</changefreq><priority>0.9</priority></url>' . "\n";
        foreach ($artistas as $a) {
            $url = $base . 'index.php?option=com_artistas&amp;view=artista&amp;id=' . $a->id . ($fichaItemid ? '&amp;Itemid='.$fichaItemid : '');
            $mod = date('Y-m-d', strtotime($a->modified ?? 'now'));
            echo '  <url><loc>' . htmlspecialchars($url) . '</loc><lastmod>' . $mod . '</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>' . "\n";
        }
        echo '</urlset>';
        $app->close();
    }
}
