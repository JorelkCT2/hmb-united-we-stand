<?php
namespace Joomla\Component\Hmgallery\Site\View\Gallery;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    protected array   $albums    = [];
    protected ?object $album     = null;
    protected array   $breadcrumb = [];

    public function display($tpl = null): void
    {
        $app     = Factory::getApplication();
        $input   = $app->getInput();
        $albumId = (int) $input->getInt('album_id', 0);
        $doc     = $app->getDocument();

        $doc->addStyleSheet(Uri::root() . 'media/com_hmgallery/css/gallery.css', ['version' => '1.0.0']);
        $doc->addScript(Uri::root()    . 'media/com_hmgallery/js/gallery.js',    ['version' => '1.0.0'], ['defer' => true]);

        // Wrap model calls in try/catch — if model is cached/broken the template handles everything
        if ($albumId > 0) {
            try { $this->album      = $this->getModel()->getAlbum($albumId); }     catch (\Throwable) { $this->album = null; }
            try { $this->breadcrumb = $this->getModel()->getBreadcrumb($albumId); } catch (\Throwable) { $this->breadcrumb = []; }
        } else {
            try { $this->albums = $this->getModel()->getAlbums(0); } catch (\Throwable) { $this->albums = []; }
        }

        $doc->setTitle('HMB Galería de Fotos');
        parent::display($tpl);
    }
}
