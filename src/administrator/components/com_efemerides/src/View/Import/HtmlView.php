<?php
namespace Joomla\Component\Efemerides\Administrator\View\Import;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null): void
    {
        ToolbarHelper::title(Text::_('COM_EFEMERIDES_IMPORTAR_CSV'), 'upload');
        parent::display($tpl);
    }
}
