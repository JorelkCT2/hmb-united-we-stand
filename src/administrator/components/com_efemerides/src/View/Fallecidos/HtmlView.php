<?php
namespace Joomla\Component\Efemerides\Administrator\View\Fallecidos;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
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
        ToolbarHelper::title(Text::_('COM_EFEMERIDES_FALLECIDOS'), 'star');
        ToolbarHelper::addNew('fallecido.add');
        ToolbarHelper::editList('fallecido.edit');
        ToolbarHelper::deleteList('COM_EFEMERIDES_CONFIRM_DELETE', 'fallecidos.delete');
    }
}
