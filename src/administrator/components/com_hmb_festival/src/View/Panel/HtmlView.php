<?php
namespace Hmb\Component\Festival\Administrator\View\Panel;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public array $stats = [];

    public array $reports = [];

    public function display($tpl = null)
    {
        $model         = $this->getModel();
        $this->stats   = $model->getStats();
        $this->reports = $model->getReports();

        ToolbarHelper::title('HMB Festival', 'calendar');
        ToolbarHelper::preferences('com_hmb_festival');

        parent::display($tpl);
    }
}
