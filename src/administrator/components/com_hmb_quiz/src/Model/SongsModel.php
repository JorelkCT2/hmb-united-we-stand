<?php
namespace Hmb\Component\Quiz\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

class SongsModel extends ListModel
{
	public function __construct($config = [])
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [
				'id', 'a.id',
				'title', 'a.title',
				'band', 'a.band',
				'genre', 'a.genre',
				'album', 'a.album',
				'origin', 'a.origin',
				'year', 'a.year',
				'difficulty', 'a.difficulty',
				'state', 'a.state',
				'content_type', 'a.content_type',
				'ordering', 'a.ordering',
				'created', 'a.created',
			];
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.title', $direction = 'asc')
	{
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', ''));
		$this->setState('filter.difficulty', $this->getUserStateFromRequest($this->context . '.filter.difficulty', 'filter_difficulty', ''));
		$this->setState('filter.genre', $this->getUserStateFromRequest($this->context . '.filter.genre', 'filter_genre', ''));
		$this->setState('filter.origin', $this->getUserStateFromRequest($this->context . '.filter.origin', 'filter_origin', ''));
		$this->setState('filter.has_cover', $this->getUserStateFromRequest($this->context . '.filter.has_cover', 'filter_has_cover', '', 'string'));
		$this->setState('filter.content_type', $this->getUserStateFromRequest($this->context . '.filter.content_type', 'filter_content_type', '', 'string'));

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search')
			. ':' . $this->getState('filter.state')
			. ':' . $this->getState('filter.difficulty')
			. ':' . $this->getState('filter.genre')
			. ':' . $this->getState('filter.origin')
			. ':' . $this->getState('filter.has_cover')
			. ':' . $this->getState('filter.content_type');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->quoteName('#__hmb_quiz_songs', 'a'));

		// Free-text search across the main fields.
		$search = $this->getState('filter.search');

		if (!empty($search)) {
			$like = '%' . trim($search) . '%';
			$query->where(
				'(' . $db->quoteName('a.title') . ' LIKE :s1'
				. ' OR ' . $db->quoteName('a.band') . ' LIKE :s2'
				. ' OR ' . $db->quoteName('a.album') . ' LIKE :s3'
				. ' OR ' . $db->quoteName('a.genre') . ' LIKE :s4'
				. ' OR ' . $db->quoteName('a.origin') . ' LIKE :s5'
				. ' OR ' . $db->quoteName('a.singer') . ' LIKE :s7'
				. ' OR ' . $db->quoteName('a.youtube_id') . ' LIKE :s6)'
			)
				->bind(':s1', $like)->bind(':s2', $like)->bind(':s3', $like)
				->bind(':s4', $like)->bind(':s5', $like)->bind(':s6', $like)->bind(':s7', $like);
		}

		// Published state.
		$stateF = $this->getState('filter.state');

		if (is_numeric($stateF)) {
			$st = (int) $stateF;
			$query->where($db->quoteName('a.state') . ' = :st')->bind(':st', $st, ParameterType::INTEGER);
		}

		// Difficulty.
		$diff = $this->getState('filter.difficulty');

		if (is_numeric($diff)) {
			$dv = (int) $diff;
			$query->where($db->quoteName('a.difficulty') . ' = :diff')->bind(':diff', $dv, ParameterType::INTEGER);
		}

		// Genre (exact match from the dropdown).
		$genre = $this->getState('filter.genre');

		if (!empty($genre)) {
			$query->where($db->quoteName('a.genre') . ' = :genre')->bind(':genre', $genre);
		}

		// Origin contains (free text).
		$origin = $this->getState('filter.origin');

		if (!empty($origin)) {
			$ol = '%' . trim($origin) . '%';
			$query->where($db->quoteName('a.origin') . ' LIKE :origin')->bind(':origin', $ol);
		}

		// Has cover / no cover.
		$hasCover = (string) $this->getState('filter.has_cover');

		if ($hasCover === '1') {
			$query->where($db->quoteName('a.cover_url') . ' <> ' . $db->quote(''));
		} elseif ($hasCover === '0') {
			$query->where($db->quoteName('a.cover_url') . ' = ' . $db->quote(''));
		}

		// The songs list never shows movies (they have their own page).
		$query->where('(' . $db->quoteName('a.content_type') . ' = ' . $db->quote('song')
			. ' OR ' . $db->quoteName('a.content_type') . ' = ' . $db->quote('') . ')');

		$orderCol  = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/** Distinct genres present in the songs table, for the filter dropdown. */
	public function getGenreOptions(): array
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('DISTINCT ' . $db->quoteName('genre'))
			->from($db->quoteName('#__hmb_quiz_songs'))
			->where($db->quoteName('genre') . ' <> ' . $db->quote(''))
			->order($db->quoteName('genre') . ' ASC');
		$db->setQuery($query);

		try {
			return array_map('strval', (array) $db->loadColumn());
		} catch (\Throwable $e) {
			return [];
		}
	}
}
