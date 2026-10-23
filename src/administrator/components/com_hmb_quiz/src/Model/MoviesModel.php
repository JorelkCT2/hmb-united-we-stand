<?php
namespace Hmb\Component\Quiz\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * Movies list. Same underlying table as songs, but restricted to
 * content_type = 'movie' and with its own independent filter/search state
 * (context com_hmb_quiz.movies).
 */
class MoviesModel extends ListModel
{
	public function __construct($config = [])
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [
				'id', 'a.id',
				'title', 'a.title',
				'movie', 'a.movie',
				'movie_year', 'a.movie_year',
				'director', 'a.director',
				'band', 'a.band',
				'genre', 'a.genre',
				'album', 'a.album',
				'difficulty', 'a.difficulty',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'created', 'a.created',
			];
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.movie', $direction = 'asc')
	{
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', ''));
		$this->setState('filter.difficulty', $this->getUserStateFromRequest($this->context . '.filter.difficulty', 'filter_difficulty', ''));
		$this->setState('filter.genre', $this->getUserStateFromRequest($this->context . '.filter.genre', 'filter_genre', ''));
		$this->setState('filter.director', $this->getUserStateFromRequest($this->context . '.filter.director', 'filter_director', ''));
		$this->setState('filter.has_cover', $this->getUserStateFromRequest($this->context . '.filter.has_cover', 'filter_has_cover', '', 'string'));

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search')
			. ':' . $this->getState('filter.state')
			. ':' . $this->getState('filter.difficulty')
			. ':' . $this->getState('filter.genre')
			. ':' . $this->getState('filter.director')
			. ':' . $this->getState('filter.has_cover');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->quoteName('#__hmb_quiz_songs', 'a'))
			->where($db->quoteName('a.content_type') . ' = ' . $db->quote('movie'));

		// Free-text search across movie + song fields.
		$search = $this->getState('filter.search');

		if (!empty($search)) {
			$like = '%' . trim($search) . '%';
			$query->where(
				'(' . $db->quoteName('a.movie') . ' LIKE :s1'
				. ' OR ' . $db->quoteName('a.title') . ' LIKE :s2'
				. ' OR ' . $db->quoteName('a.band') . ' LIKE :s3'
				. ' OR ' . $db->quoteName('a.director') . ' LIKE :s4'
				. ' OR ' . $db->quoteName('a.album') . ' LIKE :s5'
				. ' OR ' . $db->quoteName('a.youtube_id') . ' LIKE :s6)'
			)
				->bind(':s1', $like)->bind(':s2', $like)->bind(':s3', $like)
				->bind(':s4', $like)->bind(':s5', $like)->bind(':s6', $like);
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

		// Director contains (free text).
		$director = $this->getState('filter.director');

		if (!empty($director)) {
			$dl = '%' . trim($director) . '%';
			$query->where($db->quoteName('a.director') . ' LIKE :director')->bind(':director', $dl);
		}

		// Has cover / no cover.
		$hasCover = (string) $this->getState('filter.has_cover');

		if ($hasCover === '1') {
			$query->where($db->quoteName('a.cover_url') . ' <> ' . $db->quote(''));
		} elseif ($hasCover === '0') {
			$query->where($db->quoteName('a.cover_url') . ' = ' . $db->quote(''));
		}

		// Ordering.
		$ordering  = $this->getState('list.ordering', 'a.movie');
		$direction = $this->getState('list.direction', 'asc');

		if (!empty($ordering)) {
			$query->order($db->escape($ordering) . ' ' . $db->escape($direction));
		}

		return $query;
	}
}
