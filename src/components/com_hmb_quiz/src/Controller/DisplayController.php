<?php
namespace Hmb\Component\Quiz\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
	protected $option = 'com_hmb_quiz';

	protected $default_view = 'game';

	public function display($cachable = false, $urlparams = [])
	{
		return parent::display($cachable, $urlparams);
	}
}
