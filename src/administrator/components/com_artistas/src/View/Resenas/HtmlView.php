<?php
namespace Joomla\Component\Artistas\Administrator\View\Resenas;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        ToolbarHelper::title('Reseñas', 'star');
        parent::display($tpl);
    }
}
