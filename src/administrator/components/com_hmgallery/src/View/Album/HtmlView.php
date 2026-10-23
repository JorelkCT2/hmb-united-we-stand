<?php
namespace Joomla\Component\Hmgallery\Administrator\View\Album;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\GenericDataException;
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

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $isNew = empty($this->item->id);
        ToolbarHelper::title($isNew ? 'New Album' : 'Edit Album', 'images');
        ToolbarHelper::apply('album.apply');
        ToolbarHelper::save('album.save');
        ToolbarHelper::cancel('album.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
