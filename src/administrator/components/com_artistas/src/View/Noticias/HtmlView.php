<?php
namespace Joomla\Component\Artistas\Administrator\View\Noticias;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $pagination;
    public $state;

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        ToolbarHelper::title('📰 Noticias de grupos', 'list');
        ToolbarHelper::deleteList('¿Eliminar las noticias seleccionadas?', 'noticias.delete');
        ToolbarHelper::custom('noticias.publish', 'publish', '', 'Publicar', true);
        ToolbarHelper::custom('noticias.unpublish', 'unpublish', '', 'Despublicar', true);

        parent::display($tpl);
    }
}
