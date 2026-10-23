<?php
namespace Joomla\Component\HmbVideos\Site\View\Videos;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $folderTree;
    protected $currentFolderId;

    public function display($tpl = null): void
    {
        $model                 = $this->getModel();
        $this->items           = $model->getItems();
        $this->pagination      = $model->getPagination();
        $this->state           = $model->getState();
        $this->folderTree      = $model->getFolderTree();
        $this->currentFolderId = (int) $this->state->get('filter.folder_id', 0);

        $doc = Factory::getApplication()->getDocument();
        $doc->addStyleSheet('/media/com_hmb_videos/css/hmb-videos.css');
        $doc->addScript('/media/com_hmb_videos/js/hmb-videos.js', [], ['defer' => true]);

        $this->document->setTitle(Text::_('COM_HMB_VIDEOS_PAGE_TITLE'));

        parent::display($tpl);
    }
}
