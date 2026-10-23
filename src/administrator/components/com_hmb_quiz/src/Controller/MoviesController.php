<?php
namespace Hmb\Component\Quiz\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Independent list controller for movies. Operates on the same Song record/table,
 * so publish/unpublish/delete reuse the Song model by id.
 */
class MoviesController extends AdminController
{
	protected $option = 'com_hmb_quiz';

	protected $view_list = 'movies';

	public function getModel($name = 'Song', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}
