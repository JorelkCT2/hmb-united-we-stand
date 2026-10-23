<?php
namespace Joomla\Component\Efemerides\Administrator\View\Efemerides;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;
    public    $categorias = [];

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('SELECT id, name FROM #__efemerides_categorias WHERE state=1 ORDER BY ordering ASC');
        $this->categorias = $db->loadObjectList();

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_EFEMERIDES_EFEMERIDES'), 'calendar');
        ToolbarHelper::addNew('efemerid.add');
        ToolbarHelper::editList('efemerid.edit');
        ToolbarHelper::publish('efemerides.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('efemerides.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::deleteList('COM_EFEMERIDES_CONFIRM_DELETE', 'efemerides.delete');
    }
}
