<?php
namespace Joomla\Component\Artistas\Administrator\View\Genero;
defined('_JEXEC') or die;
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
        $isNew      = empty($this->item->id);
        ToolbarHelper::title($isNew ? 'Nuevo Género' : 'Editar Género', 'music');
        ToolbarHelper::apply('genero.apply');
        ToolbarHelper::save('genero.save');
        ToolbarHelper::cancel($isNew ? 'generos.cancel' : 'genero.cancel');
        parent::display($tpl);
    }
}
