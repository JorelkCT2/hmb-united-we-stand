<?php
namespace Joomla\Component\Concerts\Administrator\View\Concerts;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $origenes = [];

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');
        $this->origenes   = $this->getModel()->getOrigenes();

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title('Agenda y mapa de conciertos', 'calendar');
        ToolbarHelper::link(Route::_('index.php?option=com_concerts&task=concert.add'), 'Nuevo evento', 'plus', 'btn-success');
        ToolbarHelper::link(Route::_('index.php?option=com_concerts&view=import'), 'Importar y actualizar', 'upload', 'btn-info');
        ToolbarHelper::divider();
        ToolbarHelper::publish('concerts.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('concerts.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::deleteList('¿Eliminar los eventos seleccionados?', 'concerts.delete');
        ToolbarHelper::preferences('com_concerts');
    }
}
