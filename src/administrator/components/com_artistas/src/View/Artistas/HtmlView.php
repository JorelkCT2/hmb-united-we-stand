<?php
namespace Joomla\Component\Artistas\Administrator\View\Artistas;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $pagination;
    protected $state;

    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('Artistas'), 'music');
        ToolbarHelper::addNew('artista.add');
        ToolbarHelper::publish('artistas.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('artistas.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::deleteList('', 'artistas.delete');
    }
}
