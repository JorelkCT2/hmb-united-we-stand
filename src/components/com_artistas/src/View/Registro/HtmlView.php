<?php
namespace Joomla\Component\Artistas\Site\View\Registro;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

class HtmlView extends BaseHtmlView
{
    public $user;
    public $guest          = false;
    public $loginUrl       = '';
    public $yaTieneFicha   = false;
    public $fichaExistente = null;

    public function display($tpl = null): void
    {
        // Disable caching so form tokens are always fresh
        Factory::getApplication()->allowCache(false);

        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        $this->user  = $user;
        $this->guest = $user->guest;

        if ($user->guest) {
            // URL de retorno: página de registro con su Itemid
            $registroItemid = RouteHelper::getItemid('registro');
            $returnUrl = \Joomla\CMS\Uri\Uri::root() . 'index.php?option=com_artistas&view=registro'
                . ($registroItemid ? '&Itemid=' . $registroItemid : '');

            // Buscar el menu item de login en TODOS los menús
            $menu      = $app->getMenu('site');
            $loginItem = null;
            foreach ($menu->getItems('type', 'component') as $mi) {
                if (strpos($mi->link, 'com_users') !== false && strpos($mi->link, 'login') !== false) {
                    $loginItem = $mi;
                    break;
                }
            }

            if ($loginItem) {
                $this->loginUrl = \Joomla\CMS\Router\Route::_('index.php?option=com_users&view=login&Itemid=' . $loginItem->id . '&return=' . base64_encode($returnUrl));
            } else {
                // Fallback: usar Route::_ sin Itemid (Joomla lo resolverá)
                $this->loginUrl = \Joomla\CMS\Router\Route::_('index.php?option=com_users&view=login&return=' . base64_encode($returnUrl));
            }
        } else {
            // Comprobar si ya tiene ficha
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery('SELECT id, name, state FROM #__artistas WHERE created_by = ' . (int)$user->id . ' LIMIT 1');
            $ficha = $db->loadObject();
            if ($ficha) {
                $this->yaTieneFicha   = true;
                $this->fichaExistente = $ficha;
            }
        }

        $app->getDocument()->setTitle('Registro de Artista');
        parent::display($tpl);
    }
}
