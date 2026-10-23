<?php
namespace Joomla\Component\Efemerides\Administrator\View\Frase;
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $form; protected $item;
    public function display($tpl=null): void
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $isNew = empty($this->item->id);
        ToolbarHelper::title($isNew?'Nueva Frase':'Editar Frase','quote');
        ToolbarHelper::apply('frase.apply');
        ToolbarHelper::save('frase.save');
        ToolbarHelper::cancel('frase.cancel',$isNew?'JTOOLBAR_CANCEL':'JTOOLBAR_CLOSE');
        parent::display($tpl);
    }
}
