<?php
namespace Hmb\Component\Quiz\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class PlayersModel extends ListModel
{
	public function __construct($config = [])
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [
				'player_name', 'a.player_name',
				'games_played', 'a.games_played',
				'total_correct', 'a.total_correct',
				'total_league', 'a.total_league',
				'best_score', 'a.best_score',
				'updated', 'a.updated',
			];
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.total_league', $direction = 'desc')
	{
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));

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
			->from($db->quoteName('#__hmb_quiz_players', 'a'));

		$search = $this->getState('filter.search');

		if (!empty($search)) {
			$like = '%' . trim($search) . '%';
			$query->where($db->quoteName('a.player_name') . ' LIKE :s')
				->bind(':s', $like);
		}

		$orderCol  = $this->state->get('list.ordering', 'a.total_league');
		$orderDirn = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}
