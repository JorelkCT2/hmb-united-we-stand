<?php
namespace Hmb\Component\Quiz\Site\Service;

\defined('_JEXEC') or die;

use Hmb\Component\Quiz\Site\Helper\TemasHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * /metal-quiz                  (entrada de menú, view=game)
 * /metal-quiz/quiz-iron-maiden (páginas de entrada SEO, view=tema)
 */
class Router extends RouterView
{
	public function __construct(SiteApplication $app, AbstractMenu $menu)
	{
		$game = new RouterViewConfiguration('game');
		$this->registerView($game);

		$play = new RouterViewConfiguration('play');
		$this->registerView($play);

		$tema = new RouterViewConfiguration('tema');
		$tema->setKey('slug')->setParent($game);
		$this->registerView($tema);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	public function getTemaSegment($id, $query)
	{
		return [$id => $id];
	}

	public function getTemaId($segment, $query)
	{
		return TemasHelper::get((string) $segment) ? $segment : false;
	}
}
