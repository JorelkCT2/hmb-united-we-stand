<?php
namespace Hmb\Component\Quiz\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class SongTable extends Table
{
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__hmb_quiz_songs', 'id', $db);

		// The "published" state lives in the `state` column.
		$this->setColumnAlias('published', 'state');
	}

	public function store($updateNulls = true)
	{
		if (empty($this->created)) {
			$this->created = Factory::getDate()->toSql();
		}

		return parent::store($updateNulls);
	}
}
