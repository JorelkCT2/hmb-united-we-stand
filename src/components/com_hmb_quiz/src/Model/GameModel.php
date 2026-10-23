<?php
namespace Hmb\Component\Quiz\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class GameModel extends BaseDatabaseModel
{
	private const SESSION_KEY = 'com_hmb_quiz.game';
	private const TABLE       = '#__hmb_quiz_songs';

	/** Read and cache component params. */
	private function cfg()
	{
		static $cfg = null;

		if ($cfg !== null) {
			return $cfg;
		}

		$p = ComponentHelper::getParams('com_hmb_quiz');

		$cfg = (object) [
			'clip'     => max(1, (int) $p->get('clip_seconds', 5)),
			'rounds'   => max(1, (int) $p->get('rounds_per_game', 10)),
			'startMin' => (int) $p->get('start_min', 15),
			'startMax' => (int) $p->get('start_max', 60),
			'points'   => (int) $p->get('points_correct', 10),
			'mode'     => (string) $p->get('question_mode', 'mixed'),
			'types'    => $this->questionTypes($p),
			'reveal'   => (int) $p->get('reveal_video', 1),
			'chooses'  => (int) $p->get('player_chooses_difficulty', 1),
			'defLevel' => (int) $p->get('default_difficulty', 0),
			'choosesRounds' => (int) $p->get('player_chooses_rounds', 1),
			'choosesLang'   => (int) $p->get('player_chooses_language', 0),
			'choosesGenres' => (int) $p->get('player_chooses_genres', 0),
			'answer'        => (int) $p->get('answer_seconds', 20),
			'genrePct'      => max(0, min(100, (int) $p->get('genre_score_pct', 50))),
			'genreChoices'  => $this->genreChoices($p),
			'genreGroups'   => $this->genreGroups($p),
			'coverPixelate' => (int) $p->get('cover_pixelate', 1),
			'lifelines'     => max(0, min(3, (int) $p->get('lifelines', 0))),
			'contentScope'  => (string) $p->get('content_scope', 'both'),
		];

		if ($cfg->startMax < $cfg->startMin) {
			$cfg->startMax = $cfg->startMin;
		}

		return $cfg;
	}

	/** The configured options for the player; each is a group of one or more genres. */
	private function genreGroups($p): array
	{
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

		return $groups;
	}

	/** Flat, deduped union of every genre across all options (used for validation). */
	private function genreChoices($p): array
	{
		$flat = [];
		$seen = [];

		foreach ($this->genreGroups($p) as $grp) {
			foreach ($grp as $g) {
				if (isset($seen[mb_strtolower($g)])) {
					continue;
				}

				$seen[mb_strtolower($g)] = true;
				$flat[] = $g;
			}
		}

		return $flat;
	}

	private function session()
	{
		return Factory::getApplication()->getSession();
	}

	private function getState2()
	{
		$state = $this->session()->get(self::SESSION_KEY, null);

		if (!\is_array($state)) {
			$state = $this->resetState();
		}

		return $state;
	}

	private function saveState(array $state)
	{
		$this->session()->set(self::SESSION_KEY, $state);
	}

	private function resetState()
	{
		$state = [
			'score' => 0, 'correct' => 0, 'served' => 0, 'rounds' => [], 'saved' => false,
			'league' => 0, 'ms_correct' => 0, 'correct_timed' => 0,
		];
		$this->saveState($state);

		return $state;
	}

	public function newGame($level = 0, $rounds = 0, $lang = '', $genres = [], $content = ''): array
	{
		$cfg = $this->cfg();

		$level = (int) $level;

		if ($level < 0 || $level > 3) {
			$level = 0;
		}

		// If the player isn't allowed to choose, force the configured default.
		if (!$cfg->chooses) {
			$level = ($cfg->defLevel >= 0 && $cfg->defLevel <= 3) ? $cfg->defLevel : 0;
		}

		$rounds  = (int) $rounds;
		$allowed = [10, 20, 50];

		if (!$cfg->choosesRounds || !\in_array($rounds, $allowed, true)) {
			$rounds = \in_array($cfg->rounds, $allowed, true) ? $cfg->rounds : 10;
		}

		$lang = (string) $lang;

		if (!$cfg->choosesLang || !\in_array($lang, ['es', 'en'], true)) {
			$lang = '';   // no language filter
		}

		// Genre selection: only valid genres from the curated shortlist (needs >= 2 offered).
		$genreList   = [];
		$genreActive = false;
		$choices     = $cfg->genreChoices;

		if ($cfg->choosesGenres && \count($cfg->genreGroups) >= 2 && \is_array($genres)) {
			$lc = array_map('mb_strtolower', $choices);

			foreach ($genres as $g) {
				$g  = trim((string) $g);
				$ix = array_search(mb_strtolower($g), $lc, true);

				if ($g !== '' && $ix !== false) {
					$genreList[] = $choices[$ix];   // canonical spelling
				}
			}

			$genreList = array_values(array_unique($genreList));
		}

		// "Genre mode": picking styles disables difficulty & language and reduces points.
		if (!empty($genreList)) {
			$genreActive = true;
			$level = 0;     // mix
			$lang  = '';    // no language filter
		}

		// Content scope: only honoured when the admin allows both; otherwise forced.
		$content = (string) $content;

		if ($cfg->contentScope === 'both') {
			$content = \in_array($content, ['song', 'movie', 'both'], true) ? $content : 'both';
		} else {
			$content = $cfg->contentScope;   // 'song' or 'movie' forced by config
		}

		$state = $this->resetState();
		$state['level']      = $level;
		$state['total']      = $rounds;
		$state['lang']       = $lang;
		$state['genres']     = $genreList;
		$state['genre_mode'] = $genreActive;
		$state['content']    = $content;
		$state['score_pct']  = $genreActive ? $cfg->genrePct : 100;
		$state['lifelines']  = $cfg->lifelines;
		$this->saveState($state);

		return ['ok' => true, 'totalRounds' => $rounds, 'clip' => $cfg->clip, 'reveal' => $cfg->reveal, 'lifelines' => $cfg->lifelines];
	}

	/** Genres chosen for the current game (empty = all). */
	private function currentGenres(): array
	{
		$state = $this->getState2();

		return (isset($state['genres']) && \is_array($state['genres'])) ? $state['genres'] : [];
	}

	/** The configured player options (each a group of genres) for the start screen. */
	public function getGenres(): array
	{
		return $this->cfg()->genreGroups;
	}

	/**
	 * Genre groups enriched for the start screen: per non-empty group, the
	 * pipe-joined value (what the client submits), the canonical genre list,
	 * an auto label, and the admin-set name + image. Name/image are purely
	 * presentational; scoring still works off the real genre strings.
	 */
	public function getGenreCards(): array
	{
		$p     = ComponentHelper::getParams('com_hmb_quiz');
		$cards = [];

		// Nombres por defecto por si aún no se han guardado en Opciones (el valor
		// por defecto que se ve en el formulario NO se almacena hasta pulsar Guardar).
		// Si guardas otros nombres, esos mandan.
		$defNames = [1 => 'ROCK', 2 => 'THRASH', 3 => 'HEAVY', 4 => 'POWER', 5 => ''];
		$defImgs  = [1 => 'images/genero-rock.jpg', 2 => 'images/genero-thrash.jpg', 3 => 'images/genero-heavy.jpg', 4 => 'images/genero-power.jpg', 5 => ''];

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

			if (empty($clean)) {
				continue;
			}

			$gName = trim((string) $p->get('genre_' . $i . '_name', ''));
			$gImg  = trim((string) $p->get('genre_' . $i . '_img', ''));

			if ($gName === '') {
				$gName = $defNames[$i] ?? '';
			}

			if ($gImg === '') {
				$gImg = $defImgs[$i] ?? '';
			}

			$cards[] = [
				'value'  => implode('|', $clean),
				'genres' => $clean,
				'label'  => implode(' / ', $clean),
				'name'   => $gName,
				'img'    => $gImg,
			];
		}

		return $cards;
	}

	/** Count playable rows per content type (state=1, has youtube id). */
	public function contentCounts(): array
	{
		$db = $this->getDatabase();
		$q  = $db->getQuery(true)
			->select('CASE WHEN ' . $db->quoteName('content_type') . ' = ' . $db->quote('movie') . ' THEN ' . $db->quote('movie') . ' ELSE ' . $db->quote('song') . ' END AS ctype')
			->select('COUNT(*) AS n')
			->from($db->quoteName(self::TABLE))
			->where($db->quoteName('state') . ' = 1')
			->where($db->quoteName('youtube_id') . ' <> ' . $db->quote(''))
			->group('ctype');

		$out = ['song' => 0, 'movie' => 0];

		try {
			$db->setQuery($q);
			foreach (($db->loadObjectList() ?: []) as $r) {
				$out[$r->ctype] = (int) $r->n;
			}
		} catch (\Throwable $e) {
			// schema may predate content_type → treat everything as songs
			try {
				$db->setQuery($db->getQuery(true)->select('COUNT(*)')->from($db->quoteName(self::TABLE))->where($db->quoteName('state') . ' = 1'));
				$out['song'] = (int) $db->loadResult();
			} catch (\Throwable $e2) {
			}
		}

		return $out;
	}

	/** Content scope for the current game: player choice (state) clamped by config. */
	private function currentScope(): string
	{
		$cfgScope = $this->cfg()->contentScope;

		// If the admin forces a single type, that wins.
		if ($cfgScope === 'song' || $cfgScope === 'movie') {
			return $cfgScope;
		}

		$state = $this->getState2();
		$sel   = isset($state['content']) ? (string) $state['content'] : 'both';

		return \in_array($sel, ['song', 'movie', 'both'], true) ? $sel : 'both';
	}

	/** Current language bucket: '' (all), 'es' (Spanish-speaking) or 'en' (the rest). */
	private function currentLang(): string
	{
		$state = $this->getState2();
		$lang  = isset($state['lang']) ? (string) $state['lang'] : '';

		return \in_array($lang, ['es', 'en'], true) ? $lang : '';
	}

	/** The number of rounds for the current game. */
	private function gameRounds(array $state): int
	{
		$total = isset($state['total']) ? (int) $state['total'] : 0;

		return $total > 0 ? $total : $this->cfg()->rounds;
	}

	/** Difficulty level chosen for the current game (0 = mixed/all). */
	private function currentLevel(): int
	{
		$state = $this->getState2();

		return isset($state['level']) ? (int) $state['level'] : 0;
	}

	/** Map a question type to its column in the songs table. */
	private function columnForType(string $type): string
	{
		switch ($type) {
			case 'band':  return 'band';
			case 'genre': return 'genre';
			case 'year':  return 'year';
			case 'album': return 'album';
			case 'origin': return 'origin';
			case 'singer': return 'singer';
			case 'movie':  return 'movie';
			case 'movie_year': return 'movie_year';
			case 'movie_original': return 'original_title';
			case 'director': return 'director';
			case 'title':
			default:      return 'title';
		}
	}

	/** Question types enabled in the options (for mixed mode); defaults to all. */
	private function questionTypes($p): array
	{
		$all = ['title', 'band', 'genre', 'year', 'album', 'origin', 'singer', 'movie', 'movie_year', 'movie_original', 'director'];
		$raw = $p->get('question_types', $all);

		if (\is_string($raw)) {
			$raw = $raw === '' ? [] : explode(',', $raw);
		}

		if (!\is_array($raw)) {
			$raw = $all;
		}

		$out = array_values(array_intersect($all, array_map('trim', $raw)));

		return $out ?: $all;
	}

	private function pickType($cfg): string
	{
		$all = ['title', 'band', 'genre', 'year', 'album', 'origin', 'singer', 'movie', 'movie_year', 'movie_original', 'director'];

		if (\in_array($cfg->mode, $all, true)) {
			return $this->countDistinct($this->columnForType($cfg->mode)) >= 4 ? $cfg->mode : 'title';
		}

		// Mixed mode: only the types enabled in the options, with enough distinct values.
		$enabled = !empty($cfg->types) ? $cfg->types : $all;
		$candidates = [];

		foreach ($all as $type) {
			if (\in_array($type, $enabled, true) && $this->countDistinct($this->columnForType($type)) >= 4) {
				$candidates[] = $type;
			}
		}

		if (empty($candidates)) {
			return 'title';
		}

		// El año debe ser SIEMPRE minoritario (música y películas): damos peso bajo a
		// 'year' y 'movie_year' frente al resto, en lugar de elegir uniforme.
		$weighted = [];

		foreach ($candidates as $t) {
			$w = ($t === 'year' || $t === 'movie_year') ? 1 : 3;

			for ($i = 0; $i < $w; $i++) {
				$weighted[] = $t;
			}
		}

		return $weighted[random_int(0, \count($weighted) - 1)];
	}

	private function baseWhere($db, $query, $valueCol)
	{
		$query->where($db->quoteName('state') . ' = 1')
			->where($db->quoteName($valueCol) . ' <> ' . $db->quote(''));

		$scope = $this->currentScope();

		if ($scope === 'song') {
			$query->where('(' . $db->quoteName('content_type') . ' = ' . $db->quote('song')
				. ' OR ' . $db->quoteName('content_type') . ' = ' . $db->quote('') . ')');
		} elseif ($scope === 'movie') {
			$query->where($db->quoteName('content_type') . ' = ' . $db->quote('movie'));
		}

		$level = $this->currentLevel();

		if ($level >= 1 && $level <= 3) {
			$query->where($db->quoteName('difficulty') . ' = ' . (int) $level);
		}

		$this->langWhere($db, $query);

		$genres = $this->currentGenres();

		if (!empty($genres)) {
			$marks = [];

			foreach ($genres as $i => $g) {
				$ph = ':g' . $i;
				$marks[] = $ph;
				$query->bind($ph, $genres[$i]);
			}

			$query->where($db->quoteName('genre') . ' IN (' . implode(',', $marks) . ')');
		}
	}

	/** Spanish-speaking origin keywords (lowercase substrings of the free-text origin). */
	private function spanishKeywords(): array
	{
		return [
			'espa', 'hispano', 'latino', 'mexic', 'méxic', 'argentin', 'chile', 'colombia',
			'peru', 'perú', 'venezuel', 'uruguay', 'paraguay', 'bolivia', 'ecuador', 'cuba',
			'puerto rico', 'dominican', 'guatemala', 'hondur', 'salvador', 'nicarag',
			'costa rica', 'panam',
		];
	}

	/** SQL boolean matching Spanish-speaking origins. */
	private function spanishSql($db): string
	{
		$ors = [];

		foreach ($this->spanishKeywords() as $kw) {
			$ors[] = 'LOWER(' . $db->quoteName('origin') . ') LIKE ' . $db->quote('%' . $kw . '%');
		}

		return '(' . implode(' OR ', $ors) . ')';
	}

	/** Classify a song's free-text origin as 'es', 'en', or '' (unknown). */
	private function langOf(string $origin): string
	{
		$o = mb_strtolower(trim($origin));

		if ($o === '') {
			return '';
		}

		foreach ($this->spanishKeywords() as $kw) {
			if (mb_strpos($o, $kw) !== false) {
				return 'es';
			}
		}

		return 'en';
	}

	/** Constrain a query to a language bucket ('es' Spanish-speaking / 'en' the rest). */
	private function langApply($db, $query, string $lang): void
	{
		if ($lang !== 'es' && $lang !== 'en') {
			return;
		}

		$spanish = $this->spanishSql($db);

		if ($lang === 'es') {
			$query->where($spanish);
		} else {
			// English / Anglo bucket = has an origin but is not Spanish-speaking.
			$query->where($db->quoteName('origin') . ' <> ' . $db->quote(''));
			$query->where('NOT ' . $spanish);
		}
	}

	/**
	 * Filter by band origin for the whole game, driven by the player's language choice.
	 */
	private function langWhere($db, $query): void
	{
		$this->langApply($db, $query, $this->currentLang());
	}

	private function countDistinct(string $valueCol): int
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('COUNT(DISTINCT ' . $db->quoteName($valueCol) . ')')
			->from($db->quoteName(self::TABLE));
		$this->baseWhere($db, $query, $valueCol);
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	public function getRound(): array
	{
		$cfg   = $this->cfg();
		$state = $this->getState2();

		if ($state['served'] >= $this->gameRounds($state)) {
			return ['ok' => false, 'finished' => true, 'error' => 'GAME_OVER'];
		}

		$type     = $this->pickType($cfg);
		$valueCol = $this->columnForType($type);

		$used = isset($state['used']) && \is_array($state['used']) ? $state['used'] : [];

		$song = $this->pickSong($valueCol, $used);

		// All eligible songs already used this game? Allow repeats from here on.
		if (!$song && !empty($used)) {
			$used = [];
			$song = $this->pickSong($valueCol, $used);
		}

		if (!$song) {
			return ['ok' => false, 'error' => 'NO_SONGS'];
		}

		$correct     = (string) $song->answer;
		$distractors = $this->pickDistractors($valueCol, $correct, 3, $this->langOf((string) ($song->origin ?? '')));

		if (\count($distractors) < 1) {
			return ['ok' => false, 'error' => 'NOT_ENOUGH'];
		}

		$options = array_merge([$correct], $distractors);
		shuffle($options);

		$roundId = bin2hex(random_bytes(8));
		$state['rounds'][$roundId] = ['correct' => $correct, 'answered' => false, 'options' => array_values($options)];

		// Remember this song so it doesn't repeat within the game.
		$used[] = (int) $song->id;
		$state['used'] = $used;
		$this->saveState($state);

		$start = ((int) $song->start_time) > 0
			? (int) $song->start_time
			: random_int($cfg->startMin, $cfg->startMax);

		$clip = ((int) $song->clip_length) > 0 ? (int) $song->clip_length : $cfg->clip;

		$cover = '';

		if ($cfg->coverPixelate) {
			$isMovie = (($song->content_type ?? 'song') === 'movie');
			$img = ($isMovie && !empty($song->poster_url)) ? (string) $song->poster_url : (string) $song->cover_url;

			if (!empty($img)) {
				$cover = $img;
			}
		}

		// Precompute the two wrong options for a 50:50 lifeline (used client-side).
		$fifty = [];

		if ($cfg->lifelines > 0) {
			$wrong = array_values(array_filter($options, static function ($o) use ($correct) {
				return (string) $o !== $correct;
			}));
			shuffle($wrong);
			$fifty = array_slice($wrong, 0, 2);
		}

		return [
			'ok'        => true,
			'roundId'   => $roundId,
			'youtubeId' => (string) $song->youtube,
			'start'     => $start,
			'clip'      => $clip,
			'type'      => $type,
			'options'   => array_values($options),
			'cover'     => $cover,
			'difficulty' => (int) $song->difficulty,
			'fifty'     => array_values($fifty),
			'index'     => $state['served'] + 1,
			'total'     => $this->gameRounds($state),
		];
	}

	/** 50:50 lifeline: remove two wrong options for the current round. */
	public function useLifeline($roundId, array $clientOpts = []): array
	{
		$state = $this->getState2();
		$left  = (int) ($state['lifelines'] ?? 0);

		if ($left <= 0) {
			return ['ok' => false, 'error' => 'NO_LIFELINES'];
		}

		$roundId = (string) $roundId;

		if ($roundId === '' || empty($state['rounds'][$roundId])) {
			return ['ok' => false, 'error' => 'BAD_ROUND'];
		}

		$r = $state['rounds'][$roundId];

		if (!empty($r['answered'])) {
			return ['ok' => false, 'error' => 'ANSWERED'];
		}

		if (!empty($r['fifty'])) {
			return ['ok' => false, 'error' => 'ALREADY'];
		}

		$correct = (string) $r['correct'];

		// Prefer the options stored with the round; fall back to those sent by the client.
		$opts = (isset($r['options']) && \is_array($r['options']) && !empty($r['options']))
			? $r['options']
			: $clientOpts;
		$opts = array_map('strval', (array) $opts);

		$wrong = array_values(array_filter($opts, static function ($o) use ($correct) {
			return $o !== $correct;
		}));

		shuffle($wrong);
		$remove = array_slice($wrong, 0, 2);

		$state['rounds'][$roundId]['fifty'] = true;
		$state['lifelines'] = $left - 1;
		$this->saveState($state);

		return ['ok' => true, 'remove' => array_values($remove), 'left' => $left - 1];
	}

	/** Pick one random eligible song, excluding the given used ids. */
	private function pickSong(string $valueCol, array $used)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select(
				[
					$db->quoteName('id'),
					$db->quoteName('youtube_id', 'youtube'),
					$db->quoteName($valueCol, 'answer'),
					$db->quoteName('start_time'),
					$db->quoteName('clip_length'),
					$db->quoteName('cover_url'),
					$db->quoteName('poster_url'),
					$db->quoteName('content_type'),
					$db->quoteName('origin'),
					$db->quoteName('difficulty'),
				]
			)
			->from($db->quoteName(self::TABLE));
		$this->baseWhere($db, $query, $valueCol);
		$query->where($db->quoteName('youtube_id') . ' <> ' . $db->quote(''));

		$used = array_values(array_filter(array_map('intval', $used)));

		if (!empty($used)) {
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $used) . ')');
		}

		$query->order('RAND()');
		$query->setLimit(1);
		$db->setQuery($query);

		return $db->loadObject();
	}

	private function pickDistractors(string $valueCol, string $correct, int $count, string $lang = ''): array
	{
		// Primero, señuelos del MISMO idioma que el tema (un tema español no debe ofrecer
		// cantantes anglosajones, y al revés).
		$rows = $this->fetchDistractors($valueCol, $correct, $count, $lang);

		// Si no hay suficientes del mismo idioma, se completa con el resto para no romper la ronda.
		if (\count($rows) < $count && ($lang === 'es' || $lang === 'en')) {
			foreach ($this->fetchDistractors($valueCol, $correct, $count, '') as $extra) {
				if (!\in_array($extra, $rows, true)) {
					$rows[] = $extra;
				}

				if (\count($rows) >= $count) {
					break;
				}
			}
		}

		return \array_slice($rows, 0, $count);
	}

	private function fetchDistractors(string $valueCol, string $correct, int $count, string $lang): array
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('DISTINCT ' . $db->quoteName($valueCol, 'answer'))
			->from($db->quoteName(self::TABLE));
		$this->baseWhere($db, $query, $valueCol);
		$this->langApply($db, $query, $lang);
		$query->where($db->quoteName($valueCol) . ' <> :correct')
			->bind(':correct', $correct);
		$query->order('RAND()');
		$query->setLimit($count);
		$db->setQuery($query);

		return $db->loadColumn() ?: [];
	}

	public function submitAnswer($roundId, $choice, $ms = 0): array
	{
		$cfg   = $this->cfg();
		$state = $this->getState2();
		$roundId = (string) $roundId;

		if (!isset($state['rounds'][$roundId]) || $state['rounds'][$roundId]['answered']) {
			return ['ok' => false, 'error' => 'INVALID_ROUND'];
		}

		$correctVal = (string) $state['rounds'][$roundId]['correct'];
		$isCorrect  = ((string) $choice === $correctVal);

		// Clamp the client-reported response time to a sane range.
		$ms = (int) $ms;
		$ms = max(200, min(30000, $ms));

		$state['rounds'][$roundId]['answered'] = true;
		$state['served']++;

		if ($isCorrect) {
			// Speed scoring: full points if instant, dropping proportionally to the
			// answer window (e.g. 10s window + 10 base => -1 point per second).
			$window = (int) $cfg->answer;
			$base   = (int) $cfg->points;

			if ($window > 0) {
				$secs    = $ms / 1000;
				$factor  = max(0, ($window - $secs) / $window);
				$points  = max(1, (int) round($base * $factor));
			} else {
				$points = $base;   // no time limit: flat points
			}

			// Genre mode awards a reduced share of the points.
			$pct = isset($state['score_pct']) ? (int) $state['score_pct'] : 100;

			if ($pct < 100) {
				$points = max(1, (int) round($points * $pct / 100));
			}

			$state['score'] += $points;
			$state['correct']++;

			// League: base 10 + speed bonus, with the same genre-mode reduction.
			$bonus  = max(0, 10 - intdiv($ms, 1000));
			$league = 10 + $bonus;

			if ($pct < 100) {
				$league = max(1, (int) round($league * $pct / 100));
			}

			$state['league']       += $league;
			$state['ms_correct']   += $ms;
			$state['correct_timed']++;
		}

		$this->saveState($state);

		return [
			'ok'       => true,
			'correct'  => $isCorrect,
			'answer'   => $correctVal,
			'score'    => $state['score'],
			'finished' => $state['served'] >= $this->gameRounds($state),
		];
	}

	public function submitScore($playerName): array
	{
		$state = $this->getState2();
		$app   = Factory::getApplication();

		if (!empty($state['saved'])) {
			return ['ok' => false, 'error' => 'ALREADY_SAVED'];
		}

		if ($state['served'] < 1) {
			return ['ok' => false, 'error' => 'NO_GAME'];
		}

		$user   = $app->getIdentity();
		$userId = ($user && !$user->guest) ? (int) $user->id : 0;
		$ip     = mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);

		// ── Guests don't get saved. They see their "ghost" position in the
		//    leaderboard and a signed token to CLAIM the score after registering.
		if ($userId === 0) {
			$score = (int) $state['score'];
			$state['saved'] = true;            // block re-submission of this game
			$this->saveState($state);

			return [
				'ok'    => true,
				'guest' => true,
				'score' => $score,
				'rank'  => $this->rankFor($score, $this->gameRounds($state)),
				'claim' => $this->makeClaimToken($state),
			];
		}

		// Logged-in players use their account username.
		$uname = $this->displayNick($user->username ?? '', $user->name ?? '');
		$name  = $uname !== '' ? mb_substr($uname, 0, 100) : 'Player';

		$db  = $this->getDatabase();
		$row = (object) [
			'player_name' => $name,
			'user_id'     => $userId,
			'score'       => (int) $state['score'],
			'rounds'      => $this->gameRounds($state),
			'correct'     => (int) $state['correct'],
			'created'     => Factory::getDate()->toSql(),
			'ip'          => $ip,
		];

		$db->insertObject('#__hmb_quiz_scores', $row);

		// Registered users also feed the league (aggregate) ranking.
		$this->updateLeague($userId, $user, $state);

		$state['saved'] = true;
		$this->saveState($state);

		return ['ok' => true, 'score' => $row->score, 'rank' => $this->rankFor($row->score, $this->gameRounds($state)), 'name' => $name];
	}

	/**
	 * Build a tamper-proof token that lets a guest claim THIS game's score
	 * after they register. Signed with the site secret; expires in 2h.
	 */
	private function claimSecret(): string
	{
		return (string) Factory::getApplication()->get('secret', 'hmbquiz');
	}

	private function makeClaimToken(array $state): string
	{
		$payload = [
			's'   => (int) $state['score'],
			'r'   => $this->gameRounds($state),
			'c'   => (int) $state['correct'],
			'lg'  => (int) ($state['league'] ?? 0),
			'ms'  => (int) ($state['ms_correct'] ?? 0),
			'td'  => (int) ($state['correct_timed'] ?? 0),
			'exp' => time() + 7200,
		];
		$json = json_encode($payload);
		$sig  = hash_hmac('sha256', $json, $this->claimSecret());

		return base64_encode($json) . '.' . $sig;
	}

	/**
	 * Claim a guest score into the now-registered user's record.
	 * Verifies the HMAC signature and expiry so scores can't be forged.
	 */
	public function claimGuest(string $token): array
	{
		$app  = Factory::getApplication();
		$user = $app->getIdentity();

		if (!$user || $user->guest || !$user->id) {
			return ['ok' => false, 'error' => 'NOT_LOGGED'];
		}

		$parts = explode('.', $token, 2);

		if (\count($parts) !== 2) {
			return ['ok' => false, 'error' => 'BAD_TOKEN'];
		}

		$json = base64_decode($parts[0], true);

		if ($json === false) {
			return ['ok' => false, 'error' => 'BAD_TOKEN'];
		}

		$calc = hash_hmac('sha256', $json, $this->claimSecret());

		if (!hash_equals($calc, (string) $parts[1])) {
			return ['ok' => false, 'error' => 'BAD_TOKEN'];
		}

		$p = json_decode($json, true);

		if (!\is_array($p)) {
			return ['ok' => false, 'error' => 'BAD_TOKEN'];
		}

		if (time() > (int) ($p['exp'] ?? 0)) {
			return ['ok' => false, 'error' => 'EXPIRED'];
		}

		$userId = (int) $user->id;
		$score  = max(0, (int) ($p['s'] ?? 0));
		$rounds = max(0, (int) ($p['r'] ?? 0));
		$correct = max(0, (int) ($p['c'] ?? 0));

		$db    = $this->getDatabase();
		$uname = $this->displayNick($user->username ?? '', $user->name ?? '');
		$name  = $uname !== '' ? mb_substr($uname, 0, 100) : 'Player';

		$db->insertObject('#__hmb_quiz_scores', (object) [
			'player_name' => $name,
			'user_id'     => $userId,
			'score'       => $score,
			'rounds'      => $rounds,
			'correct'     => $correct,
			'created'     => Factory::getDate()->toSql(),
			'ip'          => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
		]);

		// Feed the league aggregate with a synthetic state.
		$this->updateLeague($userId, $user, [
			'correct'       => $correct,
			'served'        => $rounds,
			'score'         => $score,
			'league'        => max(0, (int) ($p['lg'] ?? 0)),
			'ms_correct'    => max(0, (int) ($p['ms'] ?? 0)),
			'correct_timed' => max(0, (int) ($p['td'] ?? 0)),
		]);

		return ['ok' => true, 'rank' => $this->rankFor($score, $rounds), 'score' => $score, 'rounds' => $rounds, 'name' => $name];
	}

	/**
	 * Upsert the per-registered-user league summary.
	 */
	private function updateLeague(int $userId, $user, array $state): void
	{
		$db   = $this->getDatabase();
		$uname = $this->displayNick($user->username ?? '', $user->name ?? '');
		$name = $uname !== '' ? mb_substr($uname, 0, 100) : 'Player';
		$now  = Factory::getDate()->toSql();

		$correct = (int) $state['correct'];
		$rounds  = (int) $state['served'];
		$score   = (int) $state['score'];
		$league  = (int) $state['league'];
		$ms      = (int) $state['ms_correct'];
		$timed   = (int) $state['correct_timed'];

		// Does a row already exist?
		$query = $db->getQuery(true)
			->select('user_id')
			->from($db->quoteName('#__hmb_quiz_players'))
			->where($db->quoteName('user_id') . ' = :uid')
			->bind(':uid', $userId, ParameterType::INTEGER);
		$db->setQuery($query);
		$exists = (int) $db->loadResult();

		if ($exists) {
			$query = $db->getQuery(true)
				->update($db->quoteName('#__hmb_quiz_players'))
				->set($db->quoteName('player_name') . ' = :name')
				->set($db->quoteName('games_played') . ' = ' . $db->quoteName('games_played') . ' + 1')
				->set($db->quoteName('total_correct') . ' = ' . $db->quoteName('total_correct') . ' + ' . $correct)
				->set($db->quoteName('total_rounds') . ' = ' . $db->quoteName('total_rounds') . ' + ' . $rounds)
				->set($db->quoteName('total_score') . ' = ' . $db->quoteName('total_score') . ' + ' . $score)
				->set($db->quoteName('total_league') . ' = ' . $db->quoteName('total_league') . ' + ' . $league)
				->set($db->quoteName('total_ms') . ' = ' . $db->quoteName('total_ms') . ' + ' . $ms)
				->set($db->quoteName('total_timed') . ' = ' . $db->quoteName('total_timed') . ' + ' . $timed)
				->set($db->quoteName('best_score') . ' = GREATEST(' . $db->quoteName('best_score') . ', ' . $score . ')')
				->set($db->quoteName('updated') . ' = :now')
				->where($db->quoteName('user_id') . ' = :uid')
				->bind(':name', $name)
				->bind(':now', $now)
				->bind(':uid', $userId, ParameterType::INTEGER);
			$db->setQuery($query)->execute();
		} else {
			$row = (object) [
				'user_id'      => $userId,
				'player_name'  => $name,
				'games_played' => 1,
				'total_correct' => $correct,
				'total_rounds' => $rounds,
				'total_score'  => $score,
				'total_league' => $league,
				'total_ms'     => $ms,
				'total_timed'  => $timed,
				'best_score'   => $score,
				'updated'      => $now,
			];
			$db->insertObject('#__hmb_quiz_players', $row);
		}
	}

	/**
	 * League ranking of registered players.
	 */
	public function getLeague($limit = 10): array
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select(['player_name', 'games_played', 'total_correct', 'total_rounds', 'total_league', 'total_ms', 'total_timed', 'best_score'])
			->from($db->quoteName('#__hmb_quiz_players'))
			->order($db->quoteName('total_league') . ' DESC')
			->order($db->quoteName('total_correct') . ' DESC');
		$db->setQuery($query, 0, (int) $limit);

		return $db->loadAssocList() ?: [];
	}

	private function rankFor($score, $rounds = 0): int
	{
		$score = (int) $score;
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('COUNT(*) + 1')
			->from($db->quoteName('#__hmb_quiz_scores'))
			->where($db->quoteName('score') . ' > :score')
			->bind(':score', $score, ParameterType::INTEGER);

		if ((int) $rounds > 0) {
			$rounds = (int) $rounds;
			$query->where($db->quoteName('rounds') . ' = :rounds')
				->bind(':rounds', $rounds, ParameterType::INTEGER);
		}

		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	public function getLeaderboard($limit = 10, $rounds = 0): array
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select(['player_name', 'user_id', 'score', 'correct', 'rounds', 'created'])
			->from($db->quoteName('#__hmb_quiz_scores'))
			->order($db->quoteName('score') . ' DESC')
			->order($db->quoteName('created') . ' ASC');

		if ((int) $rounds > 0) {
			$query->where($db->quoteName('rounds') . ' = ' . (int) $rounds);
		}

		// Sin LIMIT en SQL: agrupamos por jugador en PHP y recortamos después.
		$db->setQuery($query);
		$rows = $db->loadAssocList() ?: [];

		// Una entrada por jugador. Como vienen ordenadas por score DESC, la primera
		// vez que vemos a un jugador es su MEJOR partida (la que cuenta para el ranking).
		// 'total' acumula la puntuación de todas sus partidas; 'games' cuenta cuántas.
		$byUser = [];

		foreach ($rows as $r) {
			$uid = (int) ($r['user_id'] ?? 0);
			$key = $uid > 0 ? 'u' . $uid : 'n' . mb_strtolower(trim((string) $r['player_name']));

			if (!isset($byUser[$key])) {
				$byUser[$key] = [
					'player_name' => $r['player_name'],
					'score'       => (int) $r['score'],            // mejor puntuación (rankea)
					'correct'     => (int) ($r['correct'] ?? 0),   // aciertos de su mejor partida
					'total'       => 0,                            // suma de todas sus partidas
					'games'       => 0,
				];
			}

			$byUser[$key]['total'] += (int) $r['score'];
			$byUser[$key]['games'] += 1;
		}

		$out = array_values($byUser);

		usort($out, static function ($a, $b) {
			return $b['score'] <=> $a['score'];
		});

		return array_slice($out, 0, (int) $limit);
	}

	/** Distinct round counts that have at least one score (for the leaderboard tabs). */
	public function roundBuckets(): array
	{
		$db = $this->getDatabase();
		$q  = $db->getQuery(true)
			->select('DISTINCT ' . $db->quoteName('rounds'))
			->from($db->quoteName('#__hmb_quiz_scores'))
			->where($db->quoteName('rounds') . ' > 0')
			->order($db->quoteName('rounds') . ' ASC');

		$db->setQuery($q);
		$out = [];

		foreach (($db->loadColumn() ?: []) as $r) {
			$out[] = (int) $r;
		}

		return $out;
	}

	/* ===========================  MODO VERSUS  =========================== */

	/** Heartbeat: mark the current user as present (online). */
	public function touchPresence(int $userId): array
	{
		if ($userId <= 0) {
			return ['ok' => false, 'error' => 'NOAUTH'];
		}

		$db  = $this->getDatabase();
		$uid = (int) $userId;
		$now = $db->quote(Factory::getDate()->toSql());

		$sql = 'INSERT INTO ' . $db->quoteName('#__metalquiz_presence')
			. ' (' . $db->quoteName('user_id') . ', ' . $db->quoteName('last_seen') . ')'
			. ' VALUES (' . $uid . ', ' . $now . ')'
			. ' ON DUPLICATE KEY UPDATE ' . $db->quoteName('last_seen') . ' = ' . $now;

		$db->setQuery($sql)->execute();

		return ['ok' => true];
	}

	/** List players currently online who accept Versus invitations (excluding self). */
	public function getLobby(int $userId): array
	{
		$db  = $this->getDatabase();
		$cut = Factory::getDate('-30 seconds')->toSql();

		$query = $db->getQuery(true)
			->select([
				$db->quoteName('u.id'),
				$db->quoteName('u.name'),
				$db->quoteName('u.username'),
				$db->quoteName('u.params'),
			])
			->from($db->quoteName('#__users', 'u'))
			->innerJoin(
				$db->quoteName('#__metalquiz_presence', 'p')
				. ' ON ' . $db->quoteName('p.user_id') . ' = ' . $db->quoteName('u.id')
			)
			->where($db->quoteName('u.block') . ' = 0')
			->where($db->quoteName('p.last_seen') . ' >= :cut')
			->where('(JSON_EXTRACT(' . $db->quoteName('u.params') . ", '$.metal_quiz_invitations') IS NULL OR JSON_UNQUOTE(JSON_EXTRACT(" . $db->quoteName('u.params') . ", '$.metal_quiz_invitations')) <> '0')")
			->order($db->quoteName('u.name') . ' ASC');

		if ($userId > 0) {
			$query->where($db->quoteName('u.id') . ' <> :me')
				->bind(':me', $userId, ParameterType::INTEGER);
		}

		$query->bind(':cut', $cut);
		$db->setQuery($query, 0, 50);

		$rows    = $db->loadObjectList() ?: [];
		$base    = Uri::root();
		$players = [];

		foreach ($rows as $r) {
			$avatar = '';

			if (!empty($r->params)) {
				$reg = new Registry($r->params);
				$av  = trim((string) $reg->get('avatar', ''));

				if ($av !== '') {
					$avatar = preg_match('#^https?://#i', $av) ? $av : $base . ltrim($av, '/');
				}
			}

			$players[] = [
				'id'     => (int) $r->id,
				'name'   => $this->displayNick($r->username, $r->name),
				'avatar' => $avatar,
			];
		}

		return $players;
	}

	/* ===========================  VERSUS — DUELO (Fase 1)  =========================== */

	/** A player's display name. */
	/**
	 * Nick visible: usa el 'username' salvo que parezca un email (cuando el
	 * registro usa el correo como usuario); en ese caso usa el nombre real.
	 * Evita mostrar el correo del jugador en rankings y duelos.
	 */
	private function displayNick($username, $name): string
	{
		$u = trim((string) $username);
		$n = trim((string) $name);

		if ($u !== '' && strpos($u, '@') === false) {
			return $u;
		}

		return $n !== '' ? $n : $u;
	}

	private function userName(int $userId): string
	{
		if ($userId <= 0) {
			return '';
		}

		$db = $this->getDatabase();
		$q  = $db->getQuery(true)
			->select([$db->quoteName('name'), $db->quoteName('username')])
			->from($db->quoteName('#__users'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $userId, ParameterType::INTEGER);
		$db->setQuery($q);
		$r = $db->loadObject();

		return $r ? $this->displayNick($r->username, $r->name) : '';
	}

	private function loadMatch(int $matchId)
	{
		if ($matchId <= 0) {
			return null;
		}

		$db = $this->getDatabase();
		$q  = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__metalquiz_matches'))
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $matchId, ParameterType::INTEGER);
		$db->setQuery($q);

		return $db->loadObject() ?: null;
	}

	/** Challenger sends a duel invite to an opponent. */
	public function createChallenge(int $from, int $to, int $rounds, array $genres): array
	{
		if ($from <= 0 || $to <= 0 || $from === $to) {
			return ['ok' => false, 'error' => 'BADREQ'];
		}

		$db  = $this->getDatabase();
		$now = Factory::getDate()->toSql();

		// Expire my older pending challenges so only the newest is live.
		$db->setQuery(
			'UPDATE ' . $db->quoteName('#__metalquiz_matches')
			. ' SET ' . $db->quoteName('status') . ' = ' . $db->quote('expired')
			. ' WHERE ' . $db->quoteName('challenger_id') . ' = ' . (int) $from
			. ' AND ' . $db->quoteName('status') . ' = ' . $db->quote('pending')
		)->execute();

		$allowed = [10, 20, 50];
		$rounds  = \in_array($rounds, $allowed, true) ? $rounds : 10;
		$seed    = random_int(100000, 999999);

		$row = (object) [
			'challenger_id'    => $from,
			'opponent_id'      => $to,
			'status'           => 'pending',
			'rounds'           => $rounds,
			'genres'           => json_encode(array_values($genres)),
			'seed'             => $seed,
			'challenger_score' => 0,
			'opponent_score'   => 0,
			'challenger_done'  => 0,
			'opponent_done'    => 0,
			'winner_id'        => 0,
			'created'          => $now,
			'updated'          => $now,
		];
		$db->insertObject('#__metalquiz_matches', $row, 'id');

		return ['ok' => true, 'matchId' => (int) $row->id, 'opponent' => $this->userName($to)];
	}

	/** Opponent polls for a fresh incoming challenge. */
	public function getIncomingChallenge(int $userId): array
	{
		if ($userId <= 0) {
			return ['ok' => true, 'challenge' => null];
		}

		$db  = $this->getDatabase();
		$cut = Factory::getDate('-60 seconds')->toSql();

		$q = $db->getQuery(true)
			->select([$db->quoteName('m.id'), $db->quoteName('m.challenger_id'), $db->quoteName('m.rounds')])
			->from($db->quoteName('#__metalquiz_matches', 'm'))
			->where($db->quoteName('m.opponent_id') . ' = :me')
			->where($db->quoteName('m.status') . ' = ' . $db->quote('pending'))
			->where($db->quoteName('m.created') . ' >= :cut')
			->order($db->quoteName('m.id') . ' DESC')
			->bind(':me', $userId, ParameterType::INTEGER)
			->bind(':cut', $cut);
		$db->setQuery($q, 0, 1);
		$r = $db->loadObject();

		if (!$r) {
			return ['ok' => true, 'challenge' => null];
		}

		return ['ok' => true, 'challenge' => [
			'matchId' => (int) $r->id,
			'from'    => $this->userName((int) $r->challenger_id),
			'rounds'  => (int) $r->rounds,
		]];
	}

	/** Opponent accepts or declines a pending challenge. */
	public function respondChallenge(int $matchId, int $userId, bool $accept): array
	{
		$db = $this->getDatabase();
		$m  = $this->loadMatch($matchId);

		if (!$m || (int) $m->opponent_id !== $userId || $m->status !== 'pending') {
			return ['ok' => false, 'error' => 'BADREQ'];
		}

		$status = $accept ? 'accepted' : 'declined';
		$now    = Factory::getDate()->toSql();

		$db->setQuery(
			'UPDATE ' . $db->quoteName('#__metalquiz_matches')
			. ' SET ' . $db->quoteName('status') . ' = ' . $db->quote($status)
			. ', ' . $db->quoteName('updated') . ' = ' . $db->quote($now)
			. ' WHERE ' . $db->quoteName('id') . ' = ' . (int) $matchId
		)->execute();

		return ['ok' => true, 'status' => $status, 'matchId' => (int) $matchId];
	}

	/** Either party polls the current status of a match. */
	public function getChallengeStatus(int $matchId, int $userId): array
	{
		$m = $this->loadMatch($matchId);

		if (!$m || ((int) $m->challenger_id !== $userId && (int) $m->opponent_id !== $userId)) {
			return ['ok' => false, 'error' => 'BADREQ'];
		}

		return ['ok' => true, 'status' => (string) $m->status, 'matchId' => (int) $matchId];
	}

	/* ===========================  VERSUS — DUELO EN DIRECTO (Fase 2)  =========================== */

	private const DUEL_KEY = 'com_hmb_quiz.duel';

	private function isChallenger($m, int $userId): bool
	{
		return (int) $m->challenger_id === $userId;
	}

	/**
	 * Build the SAME deterministic round list for both players, from the match seed.
	 * Each round: youtubeId, start, clip, type, options[], correct, difficulty.
	 */
	private function buildMatchRounds($m): array
	{
		$db   = $this->getDatabase();
		$cols = ['title', 'band', 'year', 'album', 'genre', 'origin', 'singer', 'movie', 'movie_year', 'movie_original', 'director'];

		$scope = $this->cfg()->contentScope;

		$genres = [];

		if (!empty($m->genres)) {
			$g = json_decode((string) $m->genres, true);

			if (\is_array($g)) {
				$genres = array_values(array_filter(array_map('strval', $g)));
			}
		}

		$fetch = function (bool $withGenres) use ($db, $genres, $scope) {
			$q = $db->getQuery(true)
				->select([
					$db->quoteName('id'), $db->quoteName('youtube_id'),
					$db->quoteName('title'), $db->quoteName('band'), $db->quoteName('year'),
					$db->quoteName('album'), $db->quoteName('genre'), $db->quoteName('origin'),
					$db->quoteName('singer'), $db->quoteName('start_time'),
					$db->quoteName('clip_length'), $db->quoteName('cover_url'), $db->quoteName('difficulty'),
					$db->quoteName('movie'), $db->quoteName('movie_year'), $db->quoteName('director'),
					$db->quoteName('original_title'), $db->quoteName('poster_url'), $db->quoteName('content_type'),
				])
				->from($db->quoteName(self::TABLE))
				->where($db->quoteName('state') . ' = 1')
				->where($db->quoteName('youtube_id') . ' <> ' . $db->quote(''));

			if ($scope === 'song') {
				$q->where('(' . $db->quoteName('content_type') . ' = ' . $db->quote('song')
					. ' OR ' . $db->quoteName('content_type') . ' = ' . $db->quote('') . ')');
			} elseif ($scope === 'movie') {
				$q->where($db->quoteName('content_type') . ' = ' . $db->quote('movie'));
			}

			if ($withGenres && !empty($genres)) {
				$marks = [];

				foreach ($genres as $i => $gg) {
					$ph = ':dg' . $i;
					$marks[] = $ph;
					$q->bind($ph, $genres[$i]);
				}

				$q->where($db->quoteName('genre') . ' IN (' . implode(',', $marks) . ')');
			}

			$db->setQuery($q);

			return $db->loadObjectList() ?: [];
		};

		$songs = $fetch(true);

		if (\count($songs) < 4) {
			$songs = $fetch(false);   // fall back to the whole library
		}

		if (\count($songs) < 4) {
			return [];
		}

		// Columns with enough distinct values to build 4 options.
		$distinct = [];

		foreach ($cols as $c) {
			$vals = [];

			foreach ($songs as $s) {
				$v = trim((string) $s->$c);

				if ($v !== '') {
					$vals[$v] = true;
				}
			}

			if (\count($vals) >= 4) {
				$distinct[$c] = array_keys($vals);
			}
		}

		if (empty($distinct)) {
			return [];
		}

		$types = array_keys($distinct);
		$cfg   = $this->cfg();

		// Deterministic shuffle seeded by the match seed → identical for both players.
		mt_srand((int) $m->seed);

		$shuffle = static function (array &$a): void {
			for ($i = \count($a) - 1; $i > 0; $i--) {
				$j = mt_rand(0, $i);
				$tmp = $a[$i]; $a[$i] = $a[$j]; $a[$j] = $tmp;
			}
		};

		$pool = $songs;
		$shuffle($pool);

		$total  = (int) $m->rounds;
		$rounds = [];

		foreach ($pool as $song) {
			if (\count($rounds) >= $total) {
				break;
			}

			$okTypes = array_values(array_filter($types, static function ($t) use ($song) {
				return trim((string) $song->$t) !== '';
			}));

			if (empty($okTypes)) {
				continue;
			}

			$wgt = [];

			foreach ($okTypes as $t) {
				$w = ($t === 'year' || $t === 'movie_year') ? 1 : 3;

				for ($i = 0; $i < $w; $i++) {
					$wgt[] = $t;
				}
			}

			$type    = $wgt[mt_rand(0, \count($wgt) - 1)];
			$correct = trim((string) $song->$type);

			$others = array_values(array_filter($distinct[$type], static function ($v) use ($correct) {
				return $v !== $correct;
			}));

			if (\count($others) < 1) {
				continue;
			}

			$shuffle($others);
			$options = array_merge([$correct], \array_slice($others, 0, 3));
			$shuffle($options);

			$start = ((int) $song->start_time) > 0
				? (int) $song->start_time
				: mt_rand((int) $cfg->startMin, (int) $cfg->startMax);

			$clip = ((int) $song->clip_length) > 0 ? (int) $song->clip_length : (int) $cfg->clip;

			$isMovieR = (($song->content_type ?? 'song') === 'movie');
			$imgR = ($isMovieR && !empty($song->poster_url)) ? (string) $song->poster_url : (string) $song->cover_url;
			$cover = ($cfg->coverPixelate && !empty($imgR)) ? $imgR : '';

			$rounds[] = [
				'youtubeId'  => (string) $song->youtube_id,
				'start'      => $start,
				'clip'       => $clip,
				'type'       => $type,
				'options'    => array_values($options),
				'correct'    => $correct,
				'cover'      => $cover,
				'difficulty' => (int) $song->difficulty,
			];
		}

		mt_srand();   // restore randomness

		return $rounds;
	}

	/** Start (or join) a duel: ensure the shared rounds exist and reset MY score. */
	public function matchStart(int $matchId, int $userId): array
	{
		$db = $this->getDatabase();
		$m  = $this->loadMatch($matchId);

		if (!$m || (!$this->isChallenger($m, $userId) && (int) $m->opponent_id !== $userId)) {
			return ['ok' => false, 'error' => 'BADREQ'];
		}

		if (!\in_array($m->status, ['accepted', 'active'], true)) {
			return ['ok' => false, 'error' => 'NOT_READY', 'status' => (string) $m->status];
		}

		$rounds = [];

		if (!empty($m->rounds_data)) {
			$rounds = json_decode((string) $m->rounds_data, true) ?: [];
		}

		if (empty($rounds)) {
			$built = $this->buildMatchRounds($m);

			if (empty($built)) {
				return ['ok' => false, 'error' => 'NO_SONGS'];
			}

			// Escritura ATÓMICA: solo el PRIMER jugador que llega fija las rondas.
			// El WHERE exige que rounds_data siga vacío, así el segundo intento no
			// afecta a ninguna fila y NO sobrescribe. Sin esto, si ambos pulsan
			// "empezar" a la vez, cada uno generaba canciones distintas (aleatorias)
			// y el segundo pisaba al primero -> los duelistas oían temas distintos.
			$db->setQuery(
				'UPDATE ' . $db->quoteName('#__metalquiz_matches')
				. ' SET ' . $db->quoteName('rounds_data') . ' = ' . $db->quote(json_encode($built))
				. ', ' . $db->quoteName('status') . ' = ' . $db->quote('active')
				. ' WHERE ' . $db->quoteName('id') . ' = ' . (int) $matchId
				. ' AND (' . $db->quoteName('rounds_data') . ' IS NULL'
				. ' OR ' . $db->quoteName('rounds_data') . ' = ' . $db->quote('') . ')'
			)->execute();

			// Releer el valor GANADOR (puede ser el del rival, si llegó antes que yo).
			$m      = $this->loadMatch($matchId);
			$rounds = json_decode((string) ($m->rounds_data ?? ''), true) ?: $built;
		}

		// Reset MY side (índice incluido: si no, tras una recarga quedaba
		// puntuación 0 pero ronda avanzada => marcadores incoherentes).
		$col = $this->isChallenger($m, $userId) ? 'challenger' : 'opponent';
		$db->setQuery(
			'UPDATE ' . $db->quoteName('#__metalquiz_matches')
			. ' SET ' . $db->quoteName($col . '_score') . ' = 0'
			. ', ' . $db->quoteName($col . '_idx') . ' = 0'
			. ', ' . $db->quoteName($col . '_done') . ' = 0'
			. ' WHERE ' . $db->quoteName('id') . ' = ' . (int) $matchId
		)->execute();

		$this->session()->set(self::DUEL_KEY, ['matchId' => (int) $matchId, 'index' => 0]);

		$cfg     = $this->cfg();
		$oppId   = $this->isChallenger($m, $userId) ? (int) $m->opponent_id : (int) $m->challenger_id;

		return [
			'ok'        => true,
			'total'     => \count($rounds),
			'clip'      => $cfg->clip,
			'reveal'    => $cfg->reveal,
			'lifelines' => (int) $cfg->lifelines,
			'elo'       => $this->getElo($userId),
			'opponent'  => $this->userName($oppId),
		];
	}

	/** Serve the next shared round (without the correct answer). */
	public function matchRound(int $matchId, int $userId): array
	{
		$m = $this->loadMatch($matchId);

		if (!$m) {
			return ['ok' => false, 'error' => 'BADREQ'];
		}

		$isChal = $this->isChallenger($m, $userId);

		// El usuario debe ser uno de los dos contendientes.
		if (!$isChal && (int) $m->opponent_id !== $userId) {
			return ['ok' => false, 'error' => 'NO_DUEL'];
		}

		$rounds = json_decode((string) $m->rounds_data, true) ?: [];
		// Índice de ronda desde la BD (por jugador), no desde la sesión.
		$idx    = (int) ($isChal ? ($m->challenger_idx ?? 0) : ($m->opponent_idx ?? 0));

		if ($idx >= \count($rounds)) {
			return ['ok' => false, 'finished' => true];
		}

		$r = $rounds[$idx];

		// 50:50: precompute two wrong options (client-side, like the solo game).
		$fifty = [];

		if ((int) $this->cfg()->lifelines > 0) {
			$correct = (string) $r['correct'];
			$wrong   = array_values(array_filter(array_map('strval', $r['options']), static function ($o) use ($correct) {
				return $o !== $correct;
			}));
			shuffle($wrong);
			$fifty = \array_slice($wrong, 0, 2);
		}

		return [
			'ok'         => true,
			'roundId'    => $idx,
			'youtubeId'  => (string) $r['youtubeId'],
			'start'      => (int) $r['start'],
			'clip'       => (int) $r['clip'],
			'type'       => (string) $r['type'],
			'options'    => array_values($r['options']),
			'cover'      => (string) ($r['cover'] ?? ''),
			'difficulty' => (int) $r['difficulty'],
			'fifty'      => array_values($fifty),
			'index'      => $idx + 1,
			'total'      => \count($rounds),
		];
	}

	/** Score MY answer for the current shared round and report both scores. */
	public function matchAnswer(int $matchId, int $userId, int $idx, $choice, int $ms): array
	{
		$db = $this->getDatabase();
		$m  = $this->loadMatch($matchId);

		$isChal = $this->isChallenger($m, $userId);

		if (!$m || (!$isChal && (int) $m->opponent_id !== $userId)) {
			return ['ok' => false, 'error' => 'BADREQ'];
		}

		// Índice actual de ESTE jugador desde la BD (no sesión).
		$curIdx = (int) ($isChal ? ($m->challenger_idx ?? 0) : ($m->opponent_idx ?? 0));

		// La respuesta debe ser para la ronda en la que realmente está. Si no coincide
		// (ronda ya contestada / repetida), se rechaza: así no se puede puntuar dos veces.
		if ($curIdx !== $idx) {
			return ['ok' => false, 'error' => 'OUT_OF_SYNC'];
		}

		$rounds = json_decode((string) $m->rounds_data, true) ?: [];

		if (!isset($rounds[$idx])) {
			return ['ok' => false, 'error' => 'BADROUND'];
		}

		$cfg        = $this->cfg();
		$correctVal = (string) $rounds[$idx]['correct'];
		$isCorrect  = ((string) $choice === $correctVal);

		$ms     = max(200, min(30000, (int) $ms));
		$points = 0;

		if ($isCorrect) {
			$window = (int) $cfg->answer;
			$base   = (int) $cfg->points;

			if ($window > 0) {
				$secs   = $ms / 1000;
				$factor = max(0, ($window - $secs) / $window);
				$points = max(1, (int) round($base * $factor));
			} else {
				$points = $base;
			}
		}

		$col    = $isChal ? 'challenger' : 'opponent';
		$idxCol = $col . '_idx';

		// Avance ATÓMICO: suma puntos y +1 al índice SOLO si sigue en $idx. Si otra
		// petición ya avanzó (carrera) o se reintenta la misma ronda, afecta 0 filas
		// y no se duplica nada.
		$sql = 'UPDATE ' . $db->quoteName('#__metalquiz_matches')
			. ' SET ' . $db->quoteName($idxCol) . ' = ' . $db->quoteName($idxCol) . ' + 1';

		if ($points > 0) {
			$sql .= ', ' . $db->quoteName($col . '_score') . ' = ' . $db->quoteName($col . '_score') . ' + ' . (int) $points;
		}

		$sql .= ' WHERE ' . $db->quoteName('id') . ' = ' . (int) $matchId
			. ' AND ' . $db->quoteName($idxCol) . ' = ' . (int) $idx;

		$db->setQuery($sql)->execute();

		if ((int) $db->getAffectedRows() < 1) {
			// Ya estaba avanzada → respuesta duplicada/desincronizada, sin puntuar de nuevo.
			return ['ok' => false, 'error' => 'OUT_OF_SYNC'];
		}

		$finished = ($idx + 1) >= \count($rounds);

		if ($finished) {
			$db->setQuery(
				'UPDATE ' . $db->quoteName('#__metalquiz_matches')
				. ' SET ' . $db->quoteName($col . '_done') . ' = 1'
				. ' WHERE ' . $db->quoteName('id') . ' = ' . (int) $matchId
			)->execute();
			$this->maybeFinishMatch($matchId);
		}

		$m2 = $this->loadMatch($matchId);
		$mineScore = (int) ($isChal ? $m2->challenger_score : $m2->opponent_score);
		$oppScore  = (int) ($isChal ? $m2->opponent_score : $m2->challenger_score);
		$oppDone   = (int) ($isChal ? $m2->opponent_done : $m2->challenger_done) === 1;

		return [
			'ok'          => true,
			'correct'     => $isCorrect,
			'answer'      => $correctVal,
			'score'       => $mineScore,
			'oppScore'    => $oppScore,
			'finished'    => $finished,
			'oppFinished' => $oppDone,
		];
	}

	/** Live state for polling the opponent during/after the duel. */
	public function matchState(int $matchId, int $userId): array
	{
		$m = $this->loadMatch($matchId);

		if (!$m || (!$this->isChallenger($m, $userId) && (int) $m->opponent_id !== $userId)) {
			return ['ok' => false, 'error' => 'BADREQ'];
		}

		$mine = $this->isChallenger($m, $userId);
		$oppId = $mine ? (int) $m->opponent_id : (int) $m->challenger_id;

		return [
			'ok'          => true,
			'status'      => (string) $m->status,
			'score'       => (int) ($mine ? $m->challenger_score : $m->opponent_score),
			'oppScore'    => (int) ($mine ? $m->opponent_score : $m->challenger_score),
			'done'        => (int) ($mine ? $m->challenger_done : $m->opponent_done) === 1,
			'oppFinished' => (int) ($mine ? $m->opponent_done : $m->challenger_done) === 1,
			'winnerId'    => (int) $m->winner_id,
			'abandonedBy' => (int) ($m->abandoned_by ?? 0),
			'elo'         => $this->getElo($userId),
			'me'          => $userId,
			'opponent'    => $this->userName($oppId),
		];
	}

	/** When both players are done, decide the winner once and apply ELO. */
	private function maybeFinishMatch(int $matchId): void
	{
		$db = $this->getDatabase();
		$m  = $this->loadMatch($matchId);

		if (!$m || (int) $m->challenger_done !== 1 || (int) $m->opponent_done !== 1) {
			return;
		}

		if ($m->status === 'finished') {
			return;
		}

		$winner = 0;

		if ((int) $m->challenger_score > (int) $m->opponent_score) {
			$winner = (int) $m->challenger_id;
		} elseif ((int) $m->opponent_score > (int) $m->challenger_score) {
			$winner = (int) $m->opponent_id;
		}   // tie => 0

		$db->setQuery(
			'UPDATE ' . $db->quoteName('#__metalquiz_matches')
			. ' SET ' . $db->quoteName('status') . ' = ' . $db->quote('finished')
			. ', ' . $db->quoteName('winner_id') . ' = ' . (int) $winner
			. ' WHERE ' . $db->quoteName('id') . ' = ' . (int) $matchId
		)->execute();

		$this->applyElo((int) $m->challenger_id, (int) $m->opponent_id, $winner);
	}

	/** A player left the duel before finishing: the opponent wins. */
	public function matchAbandon(int $matchId, int $userId): array
	{
		$db = $this->getDatabase();
		$m  = $this->loadMatch($matchId);

		if (!$m || (!$this->isChallenger($m, $userId) && (int) $m->opponent_id !== $userId)) {
			return ['ok' => false];
		}

		if ($m->status === 'finished') {
			return ['ok' => true];
		}

		$winner = $this->isChallenger($m, $userId) ? (int) $m->opponent_id : (int) $m->challenger_id;

		$db->setQuery(
			'UPDATE ' . $db->quoteName('#__metalquiz_matches')
			. ' SET ' . $db->quoteName('status') . ' = ' . $db->quote('finished')
			. ', ' . $db->quoteName('winner_id') . ' = ' . (int) $winner
			. ', ' . $db->quoteName('abandoned_by') . ' = ' . (int) $userId
			. ', ' . $db->quoteName('challenger_done') . ' = 1'
			. ', ' . $db->quoteName('opponent_done') . ' = 1'
			. ' WHERE ' . $db->quoteName('id') . ' = ' . (int) $matchId
		)->execute();

		$this->applyElo((int) $m->challenger_id, (int) $m->opponent_id, $winner);

		return ['ok' => true];
	}

	/* -------- ELO -------- */

	private function getElo(int $userId): int
	{
		$db = $this->getDatabase();
		$q  = $db->getQuery(true)
			->select($db->quoteName('elo'))
			->from($db->quoteName('#__metalquiz_elo'))
			->where($db->quoteName('user_id') . ' = :id')
			->bind(':id', $userId, ParameterType::INTEGER);
		$db->setQuery($q);
		$v = $db->loadResult();

		return ($v === null) ? 1000 : (int) $v;
	}

	private function applyElo(int $aId, int $bId, int $winnerId): void
	{
		if ($aId <= 0 || $bId <= 0) {
			return;
		}

		$eloA = $this->getElo($aId);
		$eloB = $this->getElo($bId);

		$expA = 1 / (1 + pow(10, ($eloB - $eloA) / 400));
		$expB = 1 / (1 + pow(10, ($eloA - $eloB) / 400));

		$sA = ($winnerId === $aId) ? 1.0 : (($winnerId === $bId) ? 0.0 : 0.5);
		$sB = 1.0 - $sA;

		$k = 32;
		$this->saveEloResult($aId, (int) round($eloA + $k * ($sA - $expA)), $sA);
		$this->saveEloResult($bId, (int) round($eloB + $k * ($sB - $expB)), $sB);
	}

	private function saveEloResult(int $userId, int $newElo, float $score): void
	{
		$db  = $this->getDatabase();
		$now = $db->quote(Factory::getDate()->toSql());

		$w = $score >= 1 ? 1 : 0;
		$d = ($score > 0 && $score < 1) ? 1 : 0;
		$l = $score <= 0 ? 1 : 0;

		$sql = 'INSERT INTO ' . $db->quoteName('#__metalquiz_elo')
			. ' (' . $db->quoteName('user_id') . ', ' . $db->quoteName('elo') . ', ' . $db->quoteName('wins')
			. ', ' . $db->quoteName('draws') . ', ' . $db->quoteName('losses') . ', ' . $db->quoteName('played')
			. ', ' . $db->quoteName('updated') . ')'
			. ' VALUES (' . (int) $userId . ', ' . (int) $newElo . ', ' . $w . ', ' . $d . ', ' . $l . ', 1, ' . $now . ')'
			. ' ON DUPLICATE KEY UPDATE '
			. $db->quoteName('elo') . ' = ' . (int) $newElo . ', '
			. $db->quoteName('wins') . ' = ' . $db->quoteName('wins') . ' + ' . $w . ', '
			. $db->quoteName('draws') . ' = ' . $db->quoteName('draws') . ' + ' . $d . ', '
			. $db->quoteName('losses') . ' = ' . $db->quoteName('losses') . ' + ' . $l . ', '
			. $db->quoteName('played') . ' = ' . $db->quoteName('played') . ' + 1, '
			. $db->quoteName('updated') . ' = ' . $now;

		$db->setQuery($sql)->execute();
	}

	/** Versus ELO leaderboard. */
	public function getVersusRanking(int $limit = 20): array
	{
		$db = $this->getDatabase();
		$q  = $db->getQuery(true)
			->select([
				$db->quoteName('e.user_id'), $db->quoteName('e.elo'), $db->quoteName('e.wins'),
				$db->quoteName('e.losses'), $db->quoteName('e.draws'), $db->quoteName('e.played'),
				$db->quoteName('u.username'), $db->quoteName('u.name'),
			])
			->from($db->quoteName('#__metalquiz_elo', 'e'))
			->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('e.user_id'))
			->order($db->quoteName('e.elo') . ' DESC');
		$db->setQuery($q, 0, max(3, min(100, $limit)));
		$rows = $db->loadObjectList() ?: [];

		$out = [];

		foreach ($rows as $r) {
			$out[] = [
				'name'   => $this->displayNick($r->username, $r->name),
				'elo'    => (int) $r->elo,
				'wins'   => (int) $r->wins,
				'losses' => (int) $r->losses,
				'draws'  => (int) $r->draws,
				'played' => (int) $r->played,
			];
		}

		return $out;
	}
}
