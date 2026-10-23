<?php
namespace Joomla\Component\Efemerides\Administrator\View\Efemerid;

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
            $isNew ? Text::_('COM_EFEMERIDES_NUEVA_EFEMERIDE') : Text::_('COM_EFEMERIDES_EDITAR_EFEMERIDE'),
            'calendar'
        );
        ToolbarHelper::apply('efemerid.apply');
        ToolbarHelper::save('efemerid.save');
        ToolbarHelper::save2new('efemerid.save2new');
        ToolbarHelper::cancel('efemerid.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
