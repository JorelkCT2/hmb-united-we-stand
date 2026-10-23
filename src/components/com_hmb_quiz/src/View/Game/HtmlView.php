<?php
namespace Hmb\Component\Quiz\Site\View\Game;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
	protected $params;
	protected $board;
	protected $jsConfig;
	protected $genres = [];

	public function display($tpl = null)
	{
		$app          = Factory::getApplication();
		$this->params = $app->getParams('com_hmb_quiz');

		/** @var \Hmb\Component\Quiz\Site\Model\GameModel $model */
		$model       = $this->getModel();
		$this->board = $model->getLeaderboard(10);

		$p = ComponentHelper::getParams('com_hmb_quiz');

		$identity = $app->getIdentity();
		$loggedIn = ($identity && !$identity->guest) ? 1 : 0;
		$optIn    = $loggedIn ? (int) $identity->getParam('metal_quiz_invitations', 0) : 0;

		$this->jsConfig = [
			'base'        => Uri::root(true) . '/index.php?option=com_hmb_quiz&format=json',
			'token'       => Session::getFormToken(),
			'clip'        => (int) $p->get('clip_seconds', 5),
			'total'       => (int) $p->get('rounds_per_game', 10),
			'rounds'      => (int) $p->get('rounds_per_game', 10),
			'chooseRounds' => (int) $p->get('player_chooses_rounds', 1),
			'chooseLang'  => (int) $p->get('player_chooses_language', 0),
			'chooseGenres' => (int) $p->get('player_chooses_genres', 0),
			'genrePct'    => max(0, min(100, (int) $p->get('genre_score_pct', 50))),
			'answer'      => (int) $p->get('answer_seconds', 20),
			'reveal'      => (int) $p->get('reveal_video', 1),
			'chooseLevel' => (int) $p->get('player_chooses_difficulty', 1),
			'defaultLevel' => (int) $p->get('default_difficulty', 0),
			'loggedIn'    => $loggedIn,
			'userName'    => $loggedIn ? $identity->username : '',
			'imgBase'     => Uri::root(true) . '/media/com_hmb_quiz/images/',
			'versus'      => ($loggedIn && $optIn) ? 1 : 0,
			'userId'      => $loggedIn ? (int) $identity->id : 0,
		];

		// Content selector (Música / Películas / Ambas): only when the admin allows
		// both AND there is actually content of each type to play.
		$scopeCfg = (string) $p->get('content_scope', 'both');
		$counts   = $model->contentCounts();
		$hasSong  = ($counts['song'] ?? 0) > 0;
		$hasMovie = ($counts['movie'] ?? 0) > 0;
		$this->jsConfig['contentScope']  = $scopeCfg;
		$this->jsConfig['chooseContent'] = ($scopeCfg === 'both' && $hasSong && $hasMovie) ? 1 : 0;

		// Per-round-count leaderboards (fair across game lengths).
		$this->jsConfig['boardBuckets']  = $model->roundBuckets();
		$this->jsConfig['defaultRounds'] = (int) $p->get('rounds_per_game', 10);

		$genreChoices = $model->getGenreCards();
		$this->genres = (((int) $p->get('player_chooses_genres', 0)) && \count($genreChoices) >= 2) ? $genreChoices : [];

		$doc = $this->getDocument();
		$root = Uri::root(true);

		// Share URL (clean page URL) + Open Graph preview so pasted links unfurl nicely.
		$shareUrl = Uri::getInstance()->toString(['scheme', 'host', 'port', 'path']);
		$ogImage  = rtrim(Uri::root(), '/') . '/media/com_hmb_quiz/images/og-share.png';
		$this->jsConfig['shareUrl'] = $shareUrl;

		// Canonical = clean path (no ?view=game) so Google treats /metal-quiz as THE url.
		$doc->addHeadLink($shareUrl, 'canonical');

		$doc->setMetaData('og:title', 'Metal Quiz — ¿Cuánto sabes de metal?');
		$doc->setMetaData('og:description', 'Pon a prueba tu oído metalero: adivina canciones por su intro. 🤘');
		$doc->setMetaData('og:type', 'website');
		$doc->setMetaData('og:url', $shareUrl);
		$doc->setMetaData('og:image', $ogImage);
		$doc->setMetaData('twitter:card', 'summary_large_image');
		$doc->setMetaData('twitter:title', 'Metal Quiz — ¿Cuánto sabes de metal?');
		$doc->setMetaData('twitter:image', $ogImage);

		$cssV = @filemtime(JPATH_ROOT . '/media/com_hmb_quiz/css/game.css') ?: 1;
		$jsV  = @filemtime(JPATH_ROOT . '/media/com_hmb_quiz/js/game.js') ?: 1;
		$doc->addStyleSheet($root . '/media/com_hmb_quiz/css/game.css?v=' . $cssV);
		$doc->addScript('https://www.youtube.com/iframe_api');
		$doc->addScript($root . '/media/com_hmb_quiz/js/game.js?v=' . $jsV, [], ['defer' => true]);

		parent::display($tpl);
	}
}
