<?php
namespace Joomla\Component\Efemerides\Administrator\View\Frases;
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items; protected $pagination; protected $state;
    public array $autores = [];

    public function display($tpl=null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->autores    = $this->getModel()->getAutores();
        ToolbarHelper::title('Frases Míticas','quote');
        ToolbarHelper::addNew('frase.add','Nueva frase');
        ToolbarHelper::publish('frases.publish','JTOOLBAR_PUBLISH',true);
        ToolbarHelper::unpublish('frases.unpublish','JTOOLBAR_UNPUBLISH',true);
        ToolbarHelper::deleteList('','frases.delete','JTOOLBAR_DELETE');
        parent::display($tpl);
    }
}
