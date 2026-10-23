<?php
namespace Joomla\Component\Artistas\Administrator\View\Noticia;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    public $item;

    public function display($tpl = null): void
    {
        $id  = Factory::getApplication()->getInput()->getInt('id');
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('SELECT n.*, a.name AS artista_name FROM #__artistas_noticias n LEFT JOIN #__artistas a ON a.id=n.artista_id WHERE n.id=' . $id);
        $this->item = $db->loadObject();

        ToolbarHelper::title('✏️ Editar noticia', 'edit');
        parent::display($tpl);
    }
}
