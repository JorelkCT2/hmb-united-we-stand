<?php
namespace Hmb\Component\Quiz\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

class SongController extends FormController
{
	protected $option = 'com_hmb_quiz';

	protected $view_list = 'songs';

	/**
	 * AJAX: auto-fill helper for the edit form. Given an album/band, returns the
	 * cover for that album (preferring the same band) plus the band's most recent
	 * origin, singer and genre, so the form can fill empty fields automatically.
	 */
	public function findCover()
	{
		$app   = $this->app ?? \Joomla\CMS\Factory::getApplication();
		$album = trim((string) $app->getInput()->getString('album', ''));
		$band  = trim((string) $app->getInput()->getString('band', ''));
		$db    = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');

		$cover  = '';
		$origin = '';
		$singer = '';
		$genre  = '';
		$year   = '';

		// Album-level fields (cover, year): prefer exact band + album, fall back to album only.
		if ($album !== '') {
			$albumVal = function ($col) use ($db, $album, $band) {
				$run = function ($withBand) use ($db, $col, $album, $band) {
					$q = $db->getQuery(true)
						->select($db->quoteName($col))
						->from($db->quoteName('#__hmb_quiz_songs'))
						->where($db->quoteName('album') . ' = :album')
						->where($db->quoteName($col) . ' <> ' . $db->quote(''))
						->order($db->quoteName('id') . ' DESC');
					$q->bind(':album', $album);

					if ($withBand && $band !== '') {
						$q->where($db->quoteName('band') . ' = :band');
						$q->bind(':band', $band);
					}

					$db->setQuery($q, 0, 1);

					try {
						return (string) $db->loadResult();
					} catch (\Throwable $e) {
						return '';
					}
				};

				$v = $run(true);

				if ($v === '' && $band !== '') {
					$v = $run(false);
				}

				return $v;
			};

			$cover = $albumVal('cover_url');
			$year  = $albumVal('year');
		}

		// Band-level fields: most recent non-empty value for that band.
		if ($band !== '') {
			$bandVal = function ($col) use ($db, $band) {
				$q = $db->getQuery(true)
					->select($db->quoteName($col))
					->from($db->quoteName('#__hmb_quiz_songs'))
					->where($db->quoteName('band') . ' = :band')
					->where($db->quoteName($col) . ' <> ' . $db->quote(''))
					->order($db->quoteName('id') . ' DESC');
				$q->bind(':band', $band);
				$db->setQuery($q, 0, 1);

				try {
					return (string) $db->loadResult();
				} catch (\Throwable $e) {
					return '';
				}
			};

			$origin = $bandVal('origin');
			$singer = $bandVal('singer');
			$genre  = $bandVal('genre');
		}

		$app->getInput()->set('format', 'json');
		echo json_encode([
			'cover'  => $cover,
			'origin' => $origin,
			'singer' => $singer,
			'genre'  => $genre,
			'year'   => $year,
		]);
		$app->close();
	}

	/**
	 * AJAX: auto-fill helper for movies. Given a movie title, returns the
	 * movie year, director and poster (cover) from the most recent existing
	 * entry of the same movie, so multi-band soundtracks reuse shared data.
	 */
	public function findMovie()
	{
		$app   = $this->app ?? \Joomla\CMS\Factory::getApplication();
		$movie = trim((string) $app->getInput()->getString('movie', ''));
		$db    = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');

		$movieYear = '';
		$director  = '';
		$cover     = '';
		$origTitle = '';
		$poster    = '';

		if ($movie !== '') {
			$val = function ($col) use ($db, $movie) {
				$q = $db->getQuery(true)
					->select($db->quoteName($col))
					->from($db->quoteName('#__hmb_quiz_songs'))
					->where($db->quoteName('movie') . ' = :movie')
					->where($db->quoteName($col) . ' <> ' . $db->quote(''))
					->order($db->quoteName('id') . ' DESC');
				$q->bind(':movie', $movie);
				$db->setQuery($q, 0, 1);

				try {
					return (string) $db->loadResult();
				} catch (\Throwable $e) {
					return '';
				}
			};

			$movieYear = $val('movie_year');
			$director  = $val('director');
			$cover     = $val('cover_url');
			$origTitle = $val('original_title');
			$poster    = $val('poster_url');
		}

		$app->getInput()->set('format', 'json');
		echo json_encode([
			'movie_year'     => $movieYear,
			'director'       => $director,
			'cover'          => $cover,
			'original_title' => $origTitle,
			'poster'         => $poster,
		]);
		$app->close();
	}

	/**
	 * AJAX: checks a YouTube id against the official oEmbed endpoint. Detects
	 * deleted / private / mistyped videos (returns 'bad'). It CANNOT detect
	 * "embedding disabled" videos (those still come back 'ok' here) — that is
	 * confirmed client-side by the IFrame tester in the edit form.
	 */
	public function checkVideo()
	{
		$app = $this->app ?? \Joomla\CMS\Factory::getApplication();
		$yt  = trim((string) $app->getInput()->getString('yt', ''));

		$status = 'warn';
		$note   = 'COM_HMB_QUIZ_VID_UNCLEAR';

		if ($yt === '' || !preg_match('/^[A-Za-z0-9_-]{6,20}$/', $yt)) {
			$status = 'bad';
			$note   = 'COM_HMB_QUIZ_VID_BADID';
		} else {
			$url  = 'https://www.youtube.com/oembed?format=json&url='
				. rawurlencode('https://www.youtube.com/watch?v=' . $yt);
			$code = 0;

			if (\function_exists('curl_init')) {
				$ch = curl_init($url);
				curl_setopt_array($ch, [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_NOBODY         => true,
					CURLOPT_TIMEOUT        => 8,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_USERAGENT      => 'HMB-VideoChecker/1.0',
				]);
				curl_exec($ch);
				$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			} else {
				$ctx = stream_context_create(['http' => ['method' => 'HEAD', 'timeout' => 8, 'ignore_errors' => true]]);
				@file_get_contents($url, false, $ctx);
				if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
					$code = (int) $m[1];
				}
			}

			if ($code === 200) {
				$status = 'ok';
				$note   = 'COM_HMB_QUIZ_VID_OK';
			} elseif ($code === 401 || $code === 403) {
				$status = 'bad';
				$note   = 'COM_HMB_QUIZ_VID_PRIVATE';
			} elseif ($code === 404) {
				$status = 'bad';
				$note   = 'COM_HMB_QUIZ_VID_GONE';
			}
		}

		$app->getInput()->set('format', 'json');
		echo json_encode(['status' => $status, 'note' => Text::_($note)]);
		$app->close();
	}

	/** Id of the record just saved (captured in postSaveHook). */
	private $savedId = 0;
	protected function postSaveHook($model, $validData = [])
	{
		$this->savedId = (int) ($validData['id'] ?? 0);

		if (!$this->savedId) {
			$this->savedId = (int) $model->getState($model->getName() . '.id');
		}
	}

	public function savePrev()
	{
		$this->saveAndNavigate(-1);
	}

	public function saveNext()
	{
		$this->saveAndNavigate(1);
	}

	/**
	 * Save the current song and open a new one pre-filled with the shared
	 * fields (same band/genre/album/origin/difficulty/year), minus title and video.
	 */
	public function saveCopyBand()
	{
		$app  = $this->app;
		$data = $this->input->post->get('jform', [], 'array');

		$saved = $this->save();

		if (!$saved) {
			return;   // validation failed: stay on the form
		}

		// Keep everything the user just entered, except the per-song fields.
		$copy = $data;

		foreach (['title', 'youtube_id', 'start_time', 'clip_length', 'ordering'] as $clear) {
			$copy[$clear] = '';
		}

		$copy['id']    = 0;
		$copy['state'] = 1;

		$app->setUserState('com_hmb_quiz.songcopy', $copy);

		$this->setRedirect(Route::_('index.php?option=com_hmb_quiz&task=song.add', false));
	}

	/**
	 * Save the current song (validate + store + check in) and open the neighbour.
	 */
	private function saveAndNavigate(int $dir): void
	{
		// FormController::save() validates, stores, checks the record back in and
		// (for this non-standard task) sets a redirect to the list. We let it run,
		// then override the redirect to the neighbouring record on success.
		$saved = $this->save();

		if (!$saved || !$this->savedId) {
			return;   // validation failed: save() already redirected back to the form
		}

		$neighbour = $this->getModel('Song')->getNeighbour($this->savedId, $dir);

		if ($neighbour) {
			$this->setRedirect(
				Route::_('index.php?option=com_hmb_quiz&task=song.edit&id=' . $neighbour, false)
			);
		} else {
			$this->app->enqueueMessage(Text::_('COM_HMB_QUIZ_NO_MORE_SONGS'), 'info');
			$this->setRedirect(Route::_('index.php?option=com_hmb_quiz&view=songs', false));
		}
	}
}
