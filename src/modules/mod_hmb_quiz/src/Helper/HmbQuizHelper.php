<?php
namespace Hmb\Module\HmbQuiz\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

class HmbQuizHelper
{
	/**
	 * Whether the com_hmb_quiz component is installed and enabled.
	 */
	public function componentEnabled(): bool
	{
		return ComponentHelper::isEnabled('com_hmb_quiz');
	}

	/**
	 * Config payload consumed by the shared game.js (same shape as the component view).
	 */
	public function getConfig(): array
	{
		$p = ComponentHelper::getParams('com_hmb_quiz');

		$identity = Factory::getApplication()->getIdentity();
		$loggedIn = ($identity && !$identity->guest) ? 1 : 0;

		return [
			'base'         => Uri::root(true) . '/index.php?option=com_hmb_quiz&format=json',
			'token'        => Session::getFormToken(),
			'clip'         => (int) $p->get('clip_seconds', 5),
			'total'        => (int) $p->get('rounds_per_game', 10),
			'rounds'       => (int) $p->get('rounds_per_game', 10),
			'chooseRounds' => (int) $p->get('player_chooses_rounds', 1),
			'chooseLang'   => (int) $p->get('player_chooses_language', 0),
			'chooseGenres' => (int) $p->get('player_chooses_genres', 0),
			'genrePct'     => max(0, min(100, (int) $p->get('genre_score_pct', 50))),
			'answer'       => (int) $p->get('answer_seconds', 20),
			'reveal'       => (int) $p->get('reveal_video', 1),
			'chooseLevel'  => (int) $p->get('player_chooses_difficulty', 1),
			'defaultLevel' => (int) $p->get('default_difficulty', 0),
			'loggedIn'     => $loggedIn,
			'userName'     => $loggedIn ? $identity->username : '',
			'versus'       => ($loggedIn && (int) $identity->getParam('metal_quiz_invitations', 0)) ? 1 : 0,
			'userId'       => $loggedIn ? (int) $identity->id : 0,
		];
	}

	/**
	 * Ranking del módulo: mejor partida de cada jugador, de mayor a menor puntuación.
	 */
	public function getLeaderboard(int $limit = 10): array
	{
		try {
			$db    = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true)
				->select([$db->quoteName('player_name'), $db->quoteName('user_id'), $db->quoteName('score')])
				->from($db->quoteName('#__hmb_quiz_scores'))
				->order($db->quoteName('score') . ' DESC')
				->order($db->quoteName('created') . ' ASC');

			$rows = $db->setQuery($query)->loadAssocList() ?: [];
		} catch (\Throwable $e) {
			return [];
		}

		$byPlayer = [];

		foreach ($rows as $r) {
			$uid = (int) ($r['user_id'] ?? 0);
			$key = $uid > 0 ? 'u' . $uid : 'n' . mb_strtolower(trim((string) $r['player_name']));

			// Vienen ordenadas por puntuación: la primera vez que aparece un jugador es su mejor partida.
			if (!isset($byPlayer[$key])) {
				$byPlayer[$key] = ['player_name' => (string) $r['player_name'], 'score' => (int) $r['score']];
			}
		}

		return array_slice(array_values($byPlayer), 0, max(1, $limit));
	}

	/**
	 * The configured player options (each a group of one or more genres).
	 */
	public function getGenres(): array
	{
		$p      = ComponentHelper::getParams('com_hmb_quiz');
		$groups = [];

		for ($i = 1; $i <= 5; $i++) {
			$raw  = $p->get('genre_' . $i, []);
			$list = \is_array($raw) ? $raw : ((string) $raw !== '' ? [$raw] : []);

			$clean = [];
			$seen  = [];

			foreach ($list as $g) {
				$g = trim((string) $g);

				if ($g === '' || isset($seen[mb_strtolower($g)])) {
					continue;
				}

				$seen[mb_strtolower($g)] = true;
				$clean[] = $g;
			}

			if (!empty($clean)) {
				$groups[] = $clean;
			}
		}

		return (\count($groups) >= 2) ? $groups : [];
	}
}
