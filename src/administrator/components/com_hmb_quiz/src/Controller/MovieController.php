<?php
namespace Hmb\Component\Quiz\Administrator\Controller;

\defined('_JEXEC') or die;

/**
 * Movie form controller. Reuses the Song edit form/model, but flags new records
 * so they default to content_type = movie. Edit/save go through the Song view.
 */
class MovieController extends SongController
{
	protected $view_item = 'song';

	protected $view_list = 'movies';

	protected $context = 'song';

	public function add()
	{
		$this->app->setUserState('com_hmb_quiz.newcontenttype', 'movie');

		return parent::add();
	}
}
