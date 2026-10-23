<?php
namespace Joomla\Component\HmbVideos\Administrator\View\Folder;

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
        $model      = $this->getModel();
        $this->form = $model->getForm();
        $this->item = $model->getItem();
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_((int)$this->item->id ? 'COM_HMB_VIDEOS_FOLDER_EDIT' : 'COM_HMB_VIDEOS_FOLDER_NEW'), 'folder');
        ToolbarHelper::apply('folder.apply');
        ToolbarHelper::saveGroup([['save', 'folder.save'], ['save2new', 'folder.save2new']]);
        ToolbarHelper::cancel('folder.cancel');
    }
}
