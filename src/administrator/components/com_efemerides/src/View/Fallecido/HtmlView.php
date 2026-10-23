<?php
namespace Joomla\Component\Efemerides\Administrator\View\Fallecido;

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
        $isNew = empty($this->item->id);
        ToolbarHelper::title(
            $isNew ? Text::_('COM_EFEMERIDES_NUEVO_FALLECIDO') : Text::_('COM_EFEMERIDES_EDITAR_FALLECIDO'),
            'star'
        );
        ToolbarHelper::apply('fallecido.apply');
        ToolbarHelper::save('fallecido.save');
        ToolbarHelper::cancel('fallecido.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
