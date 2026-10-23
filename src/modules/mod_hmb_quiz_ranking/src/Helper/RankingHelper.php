<?php
namespace Hmb\Module\HmbQuizRanking\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

class RankingHelper
{
	public function componentEnabled(): bool
	{
		return ComponentHelper::isEnabled('com_hmb_quiz');
	}

	/**
	 * Top scores from the quiz leaderboard.
	 *
	 * @param int    $limit   How many rows to return.
	 * @param string $period  '' = all time, 'month', 'week', 'today'.
	 */
	public function getTop(int $limit = 10, string $period = ''): array
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->select(['player_name', 'score', 'correct', 'rounds', 'created'])
			->from($db->quoteName('#__hmb_quiz_scores'));

		$since = $this->periodStart($period);

		if ($since !== null) {
			$query->where($db->quoteName('created') . ' >= :since')
				->bind(':since', $since);
		}

		$query->order($db->quoteName('score') . ' DESC')
			->order($db->quoteName('created') . ' ASC');

		$db->setQuery($query, 0, max(1, min(100, $limit)));

		try {
			return $db->loadAssocList() ?: [];
		} catch (\Throwable $e) {
			return [];
		}
	}

	private function periodStart(string $period): ?string
	{
		$now = Factory::getDate();

		switch ($period) {
			case 'today':
				return $now->format('Y-m-d') . ' 00:00:00';
			case 'week':
				return Factory::getDate('-7 days')->toSql();
			case 'month':
				return Factory::getDate('-30 days')->toSql();
			default:
				return null;
		}
	}

	/**
	 * League ranking of registered players (aggregate of speed, games and accuracy).
	 */
	public function getLeague(int $limit = 10): array
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->select(['player_name', 'games_played', 'total_correct', 'total_rounds', 'total_league', 'total_ms', 'total_timed'])
			->from($db->quoteName('#__hmb_quiz_players'))
			->order($db->quoteName('total_league') . ' DESC')
			->order($db->quoteName('total_correct') . ' DESC');

		$db->setQuery($query, 0, max(1, min(100, $limit)));

		try {
			return $db->loadAssocList() ?: [];
		} catch (\Throwable $e) {
			return [];
		}
	}
}
