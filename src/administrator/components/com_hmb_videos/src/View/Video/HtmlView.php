<?php
namespace Joomla\Component\HmbVideos\Administrator\View\Video;

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
        ToolbarHelper::title(Text::_((int)$this->item->id ? 'COM_HMB_VIDEOS_VIDEO_EDIT' : 'COM_HMB_VIDEOS_VIDEO_NEW'), 'film');
        ToolbarHelper::apply('video.apply');
        ToolbarHelper::saveGroup([['save', 'video.save'], ['save2new', 'video.save2new'], ['save2copy', 'video.save2copy']]);
        ToolbarHelper::cancel('video.cancel');
    }
}
