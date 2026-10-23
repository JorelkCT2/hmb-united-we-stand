<?php
namespace Joomla\Component\Efemerides\Administrator\View\Categoria;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;

    public function display($tpl = null): void
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_EFEMERIDES_CATEGORIA'), 'tags-2');
        ToolbarHelper::apply('categoria.apply');
        ToolbarHelper::save('categoria.save');
        ToolbarHelper::cancel('categoria.cancel');
    }
}
