<?php
namespace Joomla\Component\Concerts\Administrator\View\Import;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null): void
    {
        ToolbarHelper::title('Importar Conciertos CSV', 'upload');
        ToolbarHelper::back('Volver', 'index.php?option=com_concerts&view=concerts');
        parent::display($tpl);
    }
}
