<?php
namespace Joomla\Component\Artistas\Administrator\View\Generos;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        ToolbarHelper::title('Géneros Musicales', 'music');
        ToolbarHelper::addNew('genero.add');
        ToolbarHelper::publish('generos.publish','JTOOLBAR_PUBLISH',true);
        ToolbarHelper::unpublish('generos.unpublish','JTOOLBAR_UNPUBLISH',true);
        ToolbarHelper::deleteList('','generos.delete');
        parent::display($tpl);
    }
}
