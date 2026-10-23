<?php
namespace HeavyMetalBrothers\Component\Hmb\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmb\Administrator\Helper\AreasHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public array $areas = [];

    public function display($tpl = null): void
    {
        ToolbarHelper::title('Panel HMB', 'fire');

        $this->areas = AreasHelper::areas();

        parent::display($tpl);
    }
}
