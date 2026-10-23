<?php
namespace HeavyMetalBrothers\Component\Hmb\Site\View\Hmb;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    public bool   $isLoggedIn;
    public object $currentUser;
    public string $token;
    public string $redirectUrl;
    public string $loginAction;

    public function display($tpl = null): void
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        $this->isLoggedIn   = (bool) ($user && $user->id);
        $this->currentUser  = $user;
        $this->token        = Session::getFormToken();
        $this->redirectUrl  = Uri::current();
        $this->loginAction  = Route::_('index.php?option=com_users&task=user.login');

        parent::display($tpl);
    }
}
