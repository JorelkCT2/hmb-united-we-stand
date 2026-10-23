<?php
namespace Hmb\Component\Quiz\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class PlayerTable extends Table
{
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__hmb_quiz_players', 'user_id', $db);
	}
}
