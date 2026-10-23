<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$app  = Factory::getApplication();
$user = $app->getIdentity();
$doc  = $app->getDocument();

$isLoggedIn  = (bool) ($user && $user->id);
$profileUrl  = trim((string) $params->get('profile_url', '')) ?: Uri::root() . 'index.php/registro/perfil-usuario';
$loginUrl    = trim((string) $params->get('login_url', ''))   ?: Uri::root() . 'index.php/registro';
$logoutUrl   = Route::_('index.php?option=com_users&task=user.logout');
$token       = Session::getFormToken();
$userName    = $isLoggedIn ? $user->name : '';
$moduleId    = 'mod-hmb-userbar-' . (int) $module->id;

// Avatar
$avatarUrl = '';
if ($isLoggedIn) {
    $userParams = new \Joomla\Registry\Registry($user->params);
    $avatarPath = $userParams->get('avatar', '');
    $avatarUrl  = !empty($avatarPath)
        ? Uri::root() . ltrim($avatarPath, '/')
        : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=40&d=mp';
}

$mediaUrl = Uri::root(true) . '/media/mod_hmb_userbar/';
$doc->addStyleSheet($mediaUrl . 'css/mod_hmb_userbar.css', ['version' => 'auto']);

require ModuleHelper::getLayoutPath('mod_hmb_userbar', $params->get('layout', 'default'));
