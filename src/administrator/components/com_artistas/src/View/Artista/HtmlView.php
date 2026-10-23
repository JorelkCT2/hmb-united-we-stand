<?php
namespace Joomla\Component\Artistas\Administrator\View\Artista;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

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
        ToolbarHelper::title($isNew ? Text::_('Nuevo Artista') : Text::_('Editar Artista'), 'music');
        ToolbarHelper::apply('artista.apply');
        ToolbarHelper::saveGroup(
            [['save', 'artista.save'], ['save2new', 'artista.save2new']],
            'btn-success'
        );
        ToolbarHelper::cancel($isNew ? 'artistas.cancel' : 'artista.cancel');
    }
}
