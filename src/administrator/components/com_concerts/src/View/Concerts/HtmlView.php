<?php
namespace Joomla\Component\Concerts\Administrator\View\Concerts;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Router\Route;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title('Agenda de Conciertos', 'calendar');
        ToolbarHelper::link(Route::_('index.php?option=com_concerts&task=concert.add'), 'Nuevo concierto', 'plus', 'btn-success');
        ToolbarHelper::link(Route::_('index.php?option=com_concerts&view=import'), 'Importar CSV', 'upload', 'btn-info');
        ToolbarHelper::divider();
        ToolbarHelper::deleteList('¿Eliminar los conciertos seleccionados?', 'concerts.delete');
        ToolbarHelper::preferences('com_concerts');
    }
}
