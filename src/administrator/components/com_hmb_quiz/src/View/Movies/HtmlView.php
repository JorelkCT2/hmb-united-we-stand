<?php
namespace Hmb\Component\Quiz\Administrator\View\Movies;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	public $filterForm;
	public $activeFilters;

	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (\count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_HMB_QUIZ_MOVIES'), 'film');

		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.create', 'com_hmb_quiz')) {
			ToolbarHelper::addNew('movie.add');
		}

		if ($user->authorise('core.edit.state', 'com_hmb_quiz')) {
			ToolbarHelper::publish('movies.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('movies.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}

		if ($user->authorise('core.delete', 'com_hmb_quiz')) {
			ToolbarHelper::deleteList(Text::_('JGLOBAL_CONFIRM_DELETE'), 'movies.delete');
		}

		if ($user->authorise('core.admin', 'com_hmb_quiz') || $user->authorise('core.options', 'com_hmb_quiz')) {
			ToolbarHelper::preferences('com_hmb_quiz');
		}
	}
}
