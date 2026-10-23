<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\View\Discos;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

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
        ToolbarHelper::title('💿 HMB — Discos', 'music');
        ToolbarHelper::addNew('disco.add', 'Nuevo Disco');
        ToolbarHelper::editList('disco.edit', 'Editar');
        ToolbarHelper::publish('discos.publish', 'Publicar');
        ToolbarHelper::unpublish('discos.unpublish', 'Despublicar');
        ToolbarHelper::deleteList('¿Eliminar los discos seleccionados?', 'discos.delete', 'Eliminar');
    }
}
