<?php
namespace Hmb\Component\Quiz\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

class ScoresController extends AdminController
{
	protected $option = 'com_hmb_quiz';

	public function getModel($name = 'Score', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}
