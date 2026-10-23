<?php
namespace Hmb\Component\Quiz\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
	protected $option = 'com_hmb_quiz';

	protected $default_view = 'songs';

	public function display($cachable = false, $urlparams = [])
	{
		return parent::display($cachable, $urlparams);
	}
}
