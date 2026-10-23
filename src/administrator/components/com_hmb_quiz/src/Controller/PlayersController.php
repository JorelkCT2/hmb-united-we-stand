<?php
namespace Hmb\Component\Quiz\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

class PlayersController extends AdminController
{
	protected $option = 'com_hmb_quiz';

	public function getModel($name = 'Player', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Wipe the whole league table.
	 */
	public function reset()
	{
		$this->checkToken();

		$app = Factory::getApplication();
		$db  = Factory::getContainer()->get('DatabaseDriver');

		try {
			$db->setQuery('TRUNCATE TABLE ' . $db->quoteName('#__hmb_quiz_players'))->execute();
			$app->enqueueMessage(Text::_('COM_HMB_QUIZ_LEAGUE_RESET_DONE'), 'message');
		} catch (\Throwable $e) {
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_hmb_quiz&view=players', false));
	}
}
