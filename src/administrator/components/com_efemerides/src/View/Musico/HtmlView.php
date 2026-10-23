<?php
namespace Joomla\Component\Efemerides\Administrator\View\Musico;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;

    public function display($tpl = null): void
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $isNew = empty($this->item->id);
        ToolbarHelper::title($isNew ? 'Nuevo Músico' : 'Editar Músico', 'user');
        ToolbarHelper::apply('musico.apply');
        ToolbarHelper::save('musico.save');
        ToolbarHelper::cancel('musico.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        parent::display($tpl);
    }

    public function fotoAbsoluta(string $foto, string $siteRoot): string
    {
        $foto = trim($foto);
        if (!$foto) return '';
        if (strpos($foto, 'http://') === 0 || strpos($foto, 'https://') === 0) return $foto;
        return $siteRoot . '/' . ltrim($foto, '/');
    }
}
