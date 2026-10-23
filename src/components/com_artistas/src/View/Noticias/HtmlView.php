<?php
namespace Joomla\Component\Artistas\Site\View\Noticias;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    public $noticias = [];
    public $artistaId = 0;
    public $artistaNombre = '';

    public function display($tpl = null): void
    {
        $app  = Factory::getApplication();
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $this->artistaId = \Joomla\CMS\Uri\Uri::getInstance()->getVar('artista_id', 0);

        $q = 'SELECT n.tipo, n.artista_id, c.title, c.introtext, c.created, c.id as article_id,
                     a.name as artista_name, a.image as artista_image
              FROM #__artistas_noticias n
              LEFT JOIN #__content c ON c.id = n.article_id
              LEFT JOIN #__artistas a ON a.id = n.artista_id
              WHERE c.state = 1';

        if ($this->artistaId) {
            $q .= ' AND n.artista_id = ' . (int)$this->artistaId;
            $db->setQuery('SELECT name FROM #__artistas WHERE id=' . (int)$this->artistaId);
            $this->artistaNombre = $db->loadResult() ?: '';
        }

        $q .= ' ORDER BY c.created DESC';
        $db->setQuery($q);
        $this->noticias = $db->loadObjectList() ?: [];

        $app->getDocument()->setTitle('Noticias de grupos · Heavy Metal Brothers');
        parent::display($tpl);
    }
}
