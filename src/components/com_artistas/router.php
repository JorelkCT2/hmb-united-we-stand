<?php
defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\Database\DatabaseInterface;

class ArtistasRouter extends RouterView
{
    public function __construct(SiteApplication $app, DatabaseInterface $db)
    {
        $artistas = new RouterViewConfiguration('artistas');
        $artistas->setKey('');
        $this->registerView($artistas);

        $artista = new RouterViewConfiguration('artista');
        $artista->setKey('id');
        $artista->setParent($artistas);
        $this->registerView($artista);

        parent::__construct($app, null);
        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
    }

    public function getArtistaSuffix($id)
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('SELECT alias FROM #__artistas WHERE id=' . (int)$id);
        $alias = $db->loadResult();
        return $alias ?: $id;
    }

    public function getArtistaId($alias, $urlvars)
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('SELECT id FROM #__artistas WHERE alias=' . $db->quote($alias) . ' AND state=1 LIMIT 1');
        $id = $db->loadResult();
        if ($id) return ['id' => $id];
        // Fallback: try numeric
        if (is_numeric($alias)) return ['id' => (int)$alias];
        return false;
    }
}

function ArtistasRouterFactory(\Psr\Container\ContainerInterface $container)
{
    $app = $container->get(SiteApplication::class);
    $db  = $container->get(DatabaseInterface::class);
    return new ArtistasRouter($app, $db);
}
