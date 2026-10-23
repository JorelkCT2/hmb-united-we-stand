<?php
namespace HeavyMetalBrothers\Component\Hmb\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

class Dispatcher extends ComponentDispatcher
{
    public function dispatch(): void
    {
        $app         = $this->getApplication();
        $layout      = $this->input->getCmd('layout', 'default');
        $user        = $app->getIdentity();
        $isLoggedIn  = (bool) ($user && $user->id);
        $token       = Session::getFormToken();
        $siteRoot    = Uri::root();
        $redirectUrl = Uri::current();
        $loginAction = Route::_('index.php?option=com_users&task=user.login');
        $activePanel = ($layout === 'register') ? 'register' : 'login';
        $ajaxUrl     = Uri::root() . 'index.php?option=com_ajax&module=hmb_register&method=register&format=json';

        $tmpl = JPATH_SITE . '/components/com_hmb/tmpl/hmb/_shared.php';
        include $tmpl;
    }
}
