<?php
namespace Joomla\Component\HmbVideos\Administrator\View\Folders;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null): void
    {
        $model            = $this->getModel();
        $this->items      = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state      = $model->getState();

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_HMB_VIDEOS_FOLDERS_TITLE'), 'folder');
        ToolbarHelper::addNew('folder.add');
        ToolbarHelper::publishList('folders.publish');
        ToolbarHelper::unpublishList('folders.unpublish');
        ToolbarHelper::deleteList('', 'folders.delete');
    }
}
