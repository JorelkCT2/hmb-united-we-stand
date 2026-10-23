<?php
namespace Joomla\Component\Hmgallery\Administrator\View\Albums;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

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

        ToolbarHelper::title('HM Gallery - Albums', 'images');
        ToolbarHelper::addNew('album.add');
        ToolbarHelper::publishList('albums.publish');
        ToolbarHelper::unpublishList('albums.unpublish');
        ToolbarHelper::deleteList('', 'albums.delete');

        parent::display($tpl);
    }
}
