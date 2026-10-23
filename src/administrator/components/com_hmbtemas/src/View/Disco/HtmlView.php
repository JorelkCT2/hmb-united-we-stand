<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\View\Disco;
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
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $isNew = ($this->item->id == 0);
        ToolbarHelper::title($isNew ? '💿 Nuevo Disco' : '💿 Editar: ' . $this->item->titulo, 'music');
        ToolbarHelper::apply('disco.apply', 'Aplicar');
        ToolbarHelper::save('disco.save', 'Guardar');
        ToolbarHelper::save2new('disco.save2new', 'Guardar y nuevo');
        ToolbarHelper::cancel($isNew ? 'disco.cancel' : 'disco.cancel', $isNew ? 'Cancelar' : 'Cerrar');
    }
}
