<?php
namespace Hmb\Component\Quiz\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;

class ScoresModel extends ListModel
{
	public function __construct($config = [])
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [
				'id', 'a.id',
				'player_name', 'a.player_name',
				'score', 'a.score',
				'correct', 'a.correct',
				'rounds', 'a.rounds',
				'created', 'a.created',
			];
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.score', $direction = 'desc')
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->quoteName('#__hmb_quiz_scores', 'a'));

		$search = $this->getState('filter.search');

		if (!empty($search)) {
			$search = '%' . trim($search) . '%';
			$query->where($db->quoteName('a.player_name') . ' LIKE :search')
				->bind(':search', $search);
		}

		$orderCol  = $this->state->get('list.ordering', 'a.score');
		$orderDirn = $this->state->get('list.direction', 'desc');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}
