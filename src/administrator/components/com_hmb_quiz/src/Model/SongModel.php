<?php
namespace Hmb\Component\Quiz\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class SongModel extends AdminModel
{
	public function getTable($name = 'Song', $prefix = 'Administrator', $options = [])
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Id of the previous (-1) or next (+1) song, following the list order.
	 * Returns 0 when there is no neighbour in that direction.
	 */
	public function getNeighbour(int $id, int $dir): int
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__hmb_quiz_songs'))
			->order($db->quoteName('ordering') . ' ASC')
			->order($db->quoteName('id') . ' ASC');
		$db->setQuery($query);

		$ids = array_map('intval', (array) $db->loadColumn());
		$pos = array_search($id, $ids, true);

		if ($pos === false) {
			return 0;
		}

		$target = $pos + ($dir > 0 ? 1 : -1);

		return ($target >= 0 && $target < \count($ids)) ? (int) $ids[$target] : 0;
	}

	public function getForm($data = [], $loadData = true)
	{
		$form = $this->loadForm('com_hmb_quiz.song', 'song', ['control' => 'jform', 'load_data' => $loadData]);

		return empty($form) ? false : $form;
	}

	protected function loadFormData()
	{
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_hmb_quiz.edit.song.data', []);

		if (empty($data)) {
			$data = $this->getItem();
		}

		// Pre-fill from a "save & another from this band" action (new records only).
		$copy = $app->getUserState('com_hmb_quiz.songcopy', null);

		if (!empty($copy) && \is_array($copy)) {
			$isNew = \is_object($data) ? empty($data->id) : empty($data['id']);

			if ($isNew) {
				foreach ($copy as $k => $v) {
					if (\is_object($data)) {
						$data->$k = $v;
					} else {
						$data[$k] = $v;
					}
				}
			}

			$app->setUserState('com_hmb_quiz.songcopy', null);   // one-shot
		}

		// New movie created from the Películas list → default content type.
		$newType = $app->getUserState('com_hmb_quiz.newcontenttype', null);

		if (!empty($newType)) {
			$isNew = \is_object($data) ? empty($data->id) : empty($data['id']);

			if ($isNew) {
				if (\is_object($data)) {
					$data->content_type = $newType;
				} else {
					$data['content_type'] = $newType;
				}
			}

			$app->setUserState('com_hmb_quiz.newcontenttype', null);   // one-shot
		}

		return $data;
	}

	/**
	 * Normalise the YouTube field: accept a full URL or a bare 11-char ID.
	 */
	public function save($data)
	{
		if (!empty($data['youtube_id'])) {
			$data['youtube_id'] = $this->extractYoutubeId((string) $data['youtube_id']);
		}

		$result = parent::save($data);

		if ($result && !empty($data['genre'])) {
			$this->learnGenre(trim((string) $data['genre']));
		}

		return $result;
	}

	/**
	 * Add a newly typed genre to the component's genre list so it's suggested next time.
	 */
	private function learnGenre(string $genre): void
	{
		if ($genre === '') {
			return;
		}

		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = ' . $db->quote('com_hmb_quiz'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'));
		$db->setQuery($query);

		$json     = (string) $db->loadResult();
		$registry = new \Joomla\Registry\Registry($json);
		$raw      = (string) $registry->get('genres_list', '');
		$lines    = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw))));

		foreach ($lines as $line) {
			if (mb_strtolower($line) === mb_strtolower($genre)) {
				return;   // already known
			}
		}

		$lines[] = $genre;
		$registry->set('genres_list', implode("\n", $lines));

		$newParams = $registry->toString();
		$update = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = :p')
			->where($db->quoteName('element') . ' = ' . $db->quote('com_hmb_quiz'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'))
			->bind(':p', $newParams);

		try {
			$db->setQuery($update)->execute();
		} catch (\Throwable $e) {
			// Non-critical: the genre is still saved on the song.
		}
	}

	private function extractYoutubeId(string $input): string
	{
		$input = trim($input);

		// Already a bare ID.
		if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input)) {
			return $input;
		}

		$patterns = [
			'~youtu\.be/([A-Za-z0-9_-]{11})~',
			'~[?&]v=([A-Za-z0-9_-]{11})~',
			'~/embed/([A-Za-z0-9_-]{11})~',
			'~/shorts/([A-Za-z0-9_-]{11})~',
		];

		foreach ($patterns as $p) {
			if (preg_match($p, $input, $m)) {
				return $m[1];
			}
		}

		// Fallback: first 11 valid chars.
		if (preg_match('/([A-Za-z0-9_-]{11})/', $input, $m)) {
			return $m[1];
		}

		return $input;
	}
}
