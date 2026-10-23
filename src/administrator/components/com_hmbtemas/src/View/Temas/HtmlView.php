<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\View\Temas;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

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
        ToolbarHelper::title('🎸 HMB — Temas Musicales', 'music');
        ToolbarHelper::addNew('tema.add', 'Nuevo Tema');
        ToolbarHelper::editList('tema.edit', 'Editar');
        ToolbarHelper::publish('temas.publish', 'Publicar');
        ToolbarHelper::unpublish('temas.unpublish', 'Despublicar');
        ToolbarHelper::deleteList('¿Eliminar los temas seleccionados?', 'temas.delete', 'Eliminar');
    }
}
