<?php
namespace Hmb\Component\Quiz\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;

class PlayerModel extends AdminModel
{
	public function getTable($name = 'Player', $prefix = 'Administrator', $options = [])
	{
		return parent::getTable($name, $prefix, $options);
	}

	public function getForm($data = [], $loadData = true)
	{
		return false;
	}
}
