<?php
/**
 * Heavy Metal Brothers — Login Module
 * @package  HeavyMetalBrothers.Module.HmbLogin
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$app  = Factory::getApplication();
$user = $app->getIdentity();
$doc  = $app->getDocument();

// ── Params ────────────────────────────────────────────────────
$showRemember      = (bool) $params->get('show_remember', true);
$showForgotPw      = (bool) $params->get('show_forgot_password', true);
$showForgotUser    = (bool) $params->get('show_forgot_username', true);
$forgotPwParam     = trim((string) $params->get('forgot_password_url', ''));
$isJoomlaPwDef     = empty($forgotPwParam)
    || preg_match('#component/users/reset|view=reset#i', $forgotPwParam);
$forgotPwUrl       = $isJoomlaPwDef
    ? Route::_('index.php?option=com_users&view=reset')
    : $forgotPwParam;

$forgotUserParam   = trim((string) $params->get('forgot_username_url', ''));
$isJoomlaUserDef   = empty($forgotUserParam)
    || preg_match('#component/users/remind|view=remind#i', $forgotUserParam);
$forgotUserUrl     = $isJoomlaUserDef
    ? Route::_('index.php?option=com_users&view=remind')
    : $forgotUserParam;
$showRegisterLink  = (bool) $params->get('show_register_link', true);
$registerUrlParam  = trim((string) $params->get('register_url', ''));
// Si el parámetro está vacío o apunta a la URL por defecto de Joomla, usar la nuestra
$isJoomlaDefault   = empty($registerUrlParam)
    || preg_match('#component/users/registration|view=registration|view=login#i', $registerUrlParam);
$registerUrl       = $isJoomlaDefault
    ? Route::_('index.php?option=com_hmb&view=hmb&layout=register')
    : $registerUrlParam;
$redirectLogin     = (string) $params->get('redirect_after_login', '');
$redirectLogout    = (string) $params->get('redirect_after_logout', '');

if (empty($redirectLogin))  $redirectLogin  = Uri::root();
if (empty($redirectLogout)) $redirectLogout = Uri::root() . '?hmbbye=1';

$justLoggedOut = (int) $app->getInput()->getInt('hmbbye', 0) === 1;

$moduleId   = 'mod-hmb-login-' . (int) $module->id;
$token      = Session::getFormToken();
$isLoggedIn = (bool) ($user && $user->get('id'));

// ── Assets ───────────────────────────────────────────────────
$mediaUrl = Uri::root(true) . '/media/mod_hmb_login/';
$doc->addStyleSheet($mediaUrl . 'css/mod_hmb_login.css', ['version' => 'auto']);
$doc->addScript(    $mediaUrl . 'js/mod_hmb_login.js',   ['version' => 'auto'], ['defer' => true]);

$doc->addScriptOptions('mod_hmb_login', [
    'moduleId'       => $moduleId,
    'isLoggedIn'     => $isLoggedIn,
    'redirectLogin'  => $redirectLogin,
    'redirectLogout' => $redirectLogout,
    'token'          => $token,
    'siteUrl'        => Uri::root(),
    'loginUrl'       => Route::_('index.php?option=com_users&task=user.login'),
    'logoutUrl'      => Route::_('index.php?option=com_users&task=user.logout'),
]);

// ── Render template ──────────────────────────────────────────
require ModuleHelper::getLayoutPath('mod_hmb_login', $params->get('layout', 'default'));
