<?php
namespace Joomla\Component\HmbVideos\Administrator\View\Videos;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $folders = [];

    public function display($tpl = null): void
    {
        $model            = $this->getModel();
        $this->items      = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state      = $model->getState();

        $foldersModel  = $this->getModel('Folders');
        $this->folders = $foldersModel ? $foldersModel->getItems() : [];

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_HMB_VIDEOS_VIDEOS_TITLE'), 'film');
        ToolbarHelper::addNew('video.add');
        ToolbarHelper::publishList('videos.publish');
        ToolbarHelper::unpublishList('videos.unpublish');
        ToolbarHelper::deleteList('', 'videos.delete');
    }
}
