<?php
namespace Hmb\Component\Quiz\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

class GameController extends BaseController
{
	protected $option = 'com_hmb_quiz';

	/**
	 * Output a JSON payload and stop.
	 */
	private function out(array $data): void
	{
		$app = Factory::getApplication();
		$app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
		$app->sendHeaders();
		echo json_encode($data);
		$app->close();
	}

	private function model()
	{
		return $this->getModel('Game', 'Site', ['ignore_request' => true]);
	}

	/**
	 * All endpoints require a valid CSRF token (sent by the game JS).
	 */
	private function guard(): void
	{
		if (!Session::checkToken('request')) {
			$this->out(['ok' => false, 'error' => 'INVALID_TOKEN']);
		}
	}

	public function start(): void
	{
		$this->guard();
		$level  = (int) $this->input->getInt('level', 0);
		$rounds = (int) $this->input->getInt('rounds', 0);
		$lang   = (string) $this->input->getCmd('lang', '');
		$content = (string) $this->input->getCmd('content', '');

		$genres = [];
		$raw    = (string) $this->input->get('genres', '', 'raw');

		if ($raw !== '') {
			$decoded = json_decode($raw, true);

			if (\is_array($decoded)) {
				$genres = $decoded;
			}
		}

		$this->out($this->model()->newGame($level, $rounds, $lang, $genres, $content));
	}

	public function round(): void
	{
		$this->guard();
		$this->out($this->model()->getRound());
	}

	public function answer(): void
	{
		$this->guard();

		$input   = $this->input;
		$roundId = (string) $input->getString('roundId', '');
		$choice  = (string) $input->get('choice', '', 'RAW');
		$ms      = (int) $input->getInt('ms', 0);

		$this->out($this->model()->submitAnswer($roundId, $choice, $ms));
	}

	public function lifeline(): void
	{
		$this->guard();

		$roundId = (string) $this->input->getString('roundId', '');
		$opts    = [];
		$raw     = (string) $this->input->get('options', '', 'raw');

		if ($raw !== '') {
			$decoded = json_decode($raw, true);

			if (\is_array($decoded)) {
				$opts = $decoded;
			}
		}

		$this->out($this->model()->useLifeline($roundId, $opts));
	}

	/* ===========================  MODO VERSUS  =========================== */

	public function presence(): void
	{
		$this->guard();
		$this->out($this->model()->touchPresence($this->currentUserId()));
	}

	public function lobby(): void
	{
		$this->guard();
		$this->out(['ok' => true, 'players' => $this->model()->getLobby($this->currentUserId())]);
	}

	private function currentUserId(): int
	{
		return (int) Factory::getApplication()->getIdentity()->id;
	}

	public function league(): void
	{
		$this->guard();

		$limit = (int) $this->input->getInt('limit', 10);
		$limit = max(3, min(50, $limit));

		$this->out(['ok' => true, 'board' => $this->model()->getLeague($limit)]);
	}

	public function score(): void
	{
		$this->guard();

		$name = (string) $this->input->getString('name', '');

		$this->out($this->model()->submitScore($name));
	}

	public function board(): void
	{
		$this->guard();

		$limit  = (int) $this->input->getInt('limit', 20);
		$limit  = max(3, min(50, $limit));
		$rounds = (int) $this->input->getInt('rounds', 0);

		$model = $this->model();
		$this->out([
			'ok'      => true,
			'rounds'  => $rounds,
			'buckets' => $model->roundBuckets(),
			'board'   => $model->getLeaderboard($limit, $rounds),
		]);
	}

	public function claim(): void
	{
		$this->guard();
		$token = (string) $this->input->get('token', '', 'RAW');
		$this->out($this->model()->claimGuest($token));
	}

	/* ===========================  VERSUS — DUELO (Fase 1)  =========================== */

	public function challenge(): void
	{
		$this->guard();

		$to     = (int) $this->input->getInt('opponent', 0);
		$rounds = (int) $this->input->getInt('rounds', 10);

		$genres = [];
		$raw    = (string) $this->input->get('genres', '', 'raw');

		if ($raw !== '') {
			$decoded = json_decode($raw, true);

			if (\is_array($decoded)) {
				$genres = $decoded;
			}
		}

		$this->out($this->model()->createChallenge($this->currentUserId(), $to, $rounds, $genres));
	}

	public function incoming(): void
	{
		$this->guard();
		$this->out($this->model()->getIncomingChallenge($this->currentUserId()));
	}

	public function respond(): void
	{
		$this->guard();

		$matchId = (int) $this->input->getInt('match', 0);
		$accept  = (int) $this->input->getInt('accept', 0) === 1;

		$this->out($this->model()->respondChallenge($matchId, $this->currentUserId(), $accept));
	}

	public function challengestatus(): void
	{
		$this->guard();

		$matchId = (int) $this->input->getInt('match', 0);

		$this->out($this->model()->getChallengeStatus($matchId, $this->currentUserId()));
	}

	/* ===========================  VERSUS — DUELO EN DIRECTO (Fase 2)  =========================== */

	public function matchstart(): void
	{
		$this->guard();
		$matchId = (int) $this->input->getInt('match', 0);
		$this->out($this->model()->matchStart($matchId, $this->currentUserId()));
	}

	public function matchround(): void
	{
		$this->guard();
		$matchId = (int) $this->input->getInt('match', 0);
		$this->out($this->model()->matchRound($matchId, $this->currentUserId()));
	}

	public function matchanswer(): void
	{
		$this->guard();

		$matchId = (int) $this->input->getInt('match', 0);
		$idx     = (int) $this->input->getInt('idx', -1);
		$choice  = (string) $this->input->get('choice', '', 'RAW');
		$ms      = (int) $this->input->getInt('ms', 0);

		$this->out($this->model()->matchAnswer($matchId, $this->currentUserId(), $idx, $choice, $ms));
	}

	public function matchstate(): void
	{
		$this->guard();
		$matchId = (int) $this->input->getInt('match', 0);
		$this->out($this->model()->matchState($matchId, $this->currentUserId()));
	}

	public function matchabandon(): void
	{
		$this->guard();
		$matchId = (int) $this->input->getInt('match', 0);
		$this->out($this->model()->matchAbandon($matchId, $this->currentUserId()));
	}

	public function versusboard(): void
	{
		$this->guard();
		$limit = (int) $this->input->getInt('limit', 20);
		$this->out(['ok' => true, 'board' => $this->model()->getVersusRanking($limit)]);
	}
}
