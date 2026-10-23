<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\View\Tema;

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
        ToolbarHelper::title($isNew ? '🎸 Nuevo Tema' : '🎸 Editar: ' . $this->item->title, 'music');
        ToolbarHelper::apply('tema.apply', 'Aplicar');
        ToolbarHelper::save('tema.save', 'Guardar');
        ToolbarHelper::save2new('tema.save2new', 'Guardar y nuevo');
        ToolbarHelper::cancel($isNew ? 'tema.cancel' : 'tema.cancel', $isNew ? 'Cancelar' : 'Cerrar');
    }
}
