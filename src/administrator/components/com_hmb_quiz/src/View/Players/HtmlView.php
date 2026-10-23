<?php
namespace Hmb\Component\Quiz\Administrator\View\Players;

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

	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		if (\count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_HMB_QUIZ_PLAYERS'), 'users');

		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.delete', 'com_hmb_quiz')) {
			ToolbarHelper::deleteList(Text::_('JGLOBAL_CONFIRM_DELETE'), 'players.delete');
			ToolbarHelper::custom('players.reset', 'trash', 'trash', Text::_('COM_HMB_QUIZ_LEAGUE_RESET'), false);
		}

		if ($user->authorise('core.admin', 'com_hmb_quiz') || $user->authorise('core.options', 'com_hmb_quiz')) {
			ToolbarHelper::preferences('com_hmb_quiz');
		}
	}
}
