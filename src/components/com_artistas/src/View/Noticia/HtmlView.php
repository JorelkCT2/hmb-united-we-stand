<?php
namespace Joomla\Component\Artistas\Site\View\Noticia;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    public $noticia;
    public $artista;

    public function display($tpl = null): void
    {
        $app  = Factory::getApplication();
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $id   = $app->getInput()->getInt('id');

        $db->setQuery('SELECT n.*, a.name as artista_name, a.image as artista_image, a.genre, a.id as artista_id
            FROM #__artistas_noticias n
            LEFT JOIN #__artistas a ON a.id = n.artista_id
            WHERE n.id = ' . (int)$id);
        $this->noticia = $db->loadObject();

        if (!$this->noticia) {
            throw new \RuntimeException('Noticia no encontrada', 404);
        }

        $app->getDocument()->setTitle(($this->noticia->nombre_evento ?? 'Noticia') . ' · Heavy Metal Brothers');

        parent::display($tpl);
    }
}
