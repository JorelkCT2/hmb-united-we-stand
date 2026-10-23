<?php
namespace Joomla\Component\Efemerides\Administrator\View\Categorias;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;

    public function display($tpl = null): void
    {
        $this->items = $this->get('Items');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_EFEMERIDES_CATEGORIAS'), 'tags-2');
        ToolbarHelper::addNew('categoria.add');
        ToolbarHelper::editList('categoria.edit');
        ToolbarHelper::deleteList('', 'categorias.delete');
    }
}
