<?php
namespace Joomla\Component\Artistas\Site\View\Artistas;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $genres;

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->genres     = $this->get('Genres');

        $app = Factory::getApplication();
        $app->getDocument()->setTitle('Artistas y Grupos Musicales');

        parent::display($tpl);
    }
}
