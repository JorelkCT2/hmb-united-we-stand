<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Administrator\View\Importer;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

final class HtmlView extends BaseHtmlView
{
    public function display($tpl = null): void
    {
        ToolbarHelper::title(Text::_('COM_METALMAP_IMPORT_TITLE'), 'upload');
        ToolbarHelper::cancel('festivals.cancel', 'JTOOLBAR_CLOSE');
        parent::display($tpl);
    }
}
