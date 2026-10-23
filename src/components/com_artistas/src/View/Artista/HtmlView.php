<?php
namespace Joomla\Component\Artistas\Site\View\Artista;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

class HtmlView extends BaseHtmlView
{
    protected $item;

    public function display($tpl = null): void
    {
        // Disable caching so form tokens are always fresh
        Factory::getApplication()->allowCache(false);

        $app   = Factory::getApplication();
        $input = $app->getInput();
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $user  = $app->getIdentity();

        // Leer id directamente de la URI para que no lo sobreescriba el menu item
        $uri = \Joomla\CMS\Uri\Uri::getInstance();
        $id  = (int)$uri->getVar('id', 0);
        if ($id === 0) {
            $id = $input->getInt('id', 0); // fallback al input normal
        }

        // Si no hay ID o es 0, intentar buscar el artista del usuario logueado
        if ($id === 0 && !$user->guest) {
            $db->setQuery('SELECT id FROM #__artistas WHERE created_by = ' . (int)$user->id . ' AND state = 1 LIMIT 1');
            $id = (int)$db->loadResult();
        }

        // Si sigue siendo 0, redirigir al listado
        if ($id === 0) {
            $app->enqueueMessage('No se encontró ningún artista.', 'warning');
            $app->redirect('index.php?option=com_artistas&view=artistas' .
                (RouteHelper::getItemid('artistas') ? '&Itemid=' . RouteHelper::getItemid('artistas') : ''));
            return;
        }

        // Pasar el ID corregido al input para que el modelo lo use
        $input->set('id', $id);

        $this->item = $this->get('Item');

        if (!$this->item) {
            $app->enqueueMessage('Artista no encontrado o no está publicado.', 'warning');
            $app->redirect('index.php?option=com_artistas&view=artistas' .
                (RouteHelper::getItemid('artistas') ? '&Itemid=' . RouteHelper::getItemid('artistas') : ''));
            return;
        }

        $doc  = $app->getDocument();
        $item = $this->item;

        // Title
        $seoTitle = $item->name;
        if (!empty($item->genre))            $seoTitle .= ' | ' . $item->genre;
        if (!empty($item->provincia_origen))  $seoTitle .= ' | ' . $item->provincia_origen;
        $seoTitle .= ' | Heavy Metal Brothers';
        $doc->setTitle($seoTitle);

        // Meta description
        $seoDesc = '';
        if (!empty($item->metadesc))       $seoDesc = $item->metadesc;
        elseif (!empty($item->short_desc))  $seoDesc = mb_substr(strip_tags($item->short_desc), 0, 160);
        elseif (!empty($item->description)) $seoDesc = mb_substr(strip_tags($item->description), 0, 160);
        if ($seoDesc) $doc->setDescription($seoDesc);

        // Keywords
        if (!empty($item->metakey)) {
            $doc->setMetaData('keywords', $item->metakey);
        } else {
            $kw = array_filter([$item->name, $item->genre ?? '', $item->provincia_origen ?? '', 'grupos musicales', 'bandas']);
            $doc->setMetaData('keywords', implode(', ', $kw));
        }

        // Open Graph
        $doc->setMetaData('og:title',      $item->name . ' | Heavy Metal Brothers', 'property');
        $doc->setMetaData('og:type',       'music.band', 'property');
        $doc->setMetaData('og:url',        \Joomla\CMS\Uri\Uri::current(), 'property');
        if (!empty($item->image)) $doc->setMetaData('og:image', \Joomla\CMS\Uri\Uri::root() . $item->image, 'property');
        if ($seoDesc)            $doc->setMetaData('og:description', $seoDesc, 'property');

        parent::display($tpl);
    }
}
