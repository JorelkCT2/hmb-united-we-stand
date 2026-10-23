<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Administrator\View\Festivals;

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

final class HtmlView extends BaseHtmlView
{
    public $items;
    public $pagination;
    public $state;
    public $filterForm;
    public $activeFilters;

    public function display($tpl = null): void
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');

        // Try/catch defensivo: en algunos hostings (IONOS) los searchtools rompen
        try {
            $this->filterForm    = $this->get('FilterForm');
            $this->activeFilters = $this->get('ActiveFilters');
        } catch (\Throwable $e) {
            $this->filterForm    = null;
            $this->activeFilters = [];
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        try {
            $canDo = ContentHelper::getActions('com_metalmap');
        } catch (\Throwable $e) {
            $canDo = null;
        }

        ToolbarHelper::title(Text::_('COM_METALMAP_FESTIVALS_TITLE'), 'map-marker');

        $toolbar = Toolbar::getInstance('toolbar');

        if (!$canDo || $canDo->get('core.create')) {
            $toolbar->addNew('festival.add');
        }

        if (!$canDo || $canDo->get('core.edit.state')) {
            $toolbar->publish('festivals.publish')->listCheck(true);
            $toolbar->unpublish('festivals.unpublish')->listCheck(true);
            $toolbar->trash('festivals.trash')->listCheck(true);
        }

        if (!$canDo || $canDo->get('core.delete')) {
            $toolbar->delete('festivals.delete')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        $toolbar->linkButton('import')
            ->text('COM_METALMAP_IMPORT_CSV')
            ->icon('icon-upload')
            ->url('index.php?option=com_metalmap&view=importer');

        if (!$canDo || $canDo->get('core.admin')) {
            $toolbar->preferences('com_metalmap');
        }
    }
}
