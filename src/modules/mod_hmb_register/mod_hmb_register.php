<?php
/**
 * Heavy Metal Brothers — Register + Login Module  v2.0
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

// ── Register params ────────────────────────────────────────
$showName         = (bool) $params->get('show_name', true);
$showUsername     = (bool) $params->get('show_username', true);
$redirectRegister = (string) $params->get('redirect_after_register', '') ?: Uri::root() . 'index.php/registro/perfil-usuario';

// ── Login params ───────────────────────────────────────────
$showRemember     = (bool) $params->get('show_remember', true);
$showForgotPw     = (bool) $params->get('show_forgot_password', true);
$showForgotUser   = (bool) $params->get('show_forgot_username', true);
$redirectLogin    = (string) $params->get('redirect_after_login', '')  ?: Uri::root();
$redirectLogout   = (string) $params->get('redirect_after_logout', '') ?: Uri::root() . 'index.php/registro?hmbbye=1';

// ── Google ─────────────────────────────────────────────────
$enableGoogle     = (bool) $params->get('enable_google', false);
$googleClientId   = trim((string) $params->get('google_client_id', ''));
$googleBtnText    = (string) $params->get('google_button_text', 'Entrar con Google');

// ── reCAPTCHA ──────────────────────────────────────────────
$enableCaptcha    = (bool) $params->get('enable_recaptcha', false);
$recaptchaSiteKey = trim((string) $params->get('recaptcha_site_key', ''));
$recaptchaSecret  = trim((string) $params->get('recaptcha_secret_key', ''));

// ── State ──────────────────────────────────────────────────
$moduleId      = 'mod-hmb-' . (int) $module->id;
$token         = Session::getFormToken();
$isLoggedIn    = (bool) ($user && $user->get('id'));
$justLoggedOut = !$isLoggedIn && (int) $app->getInput()->getInt('hmbbye', 0) === 1;

// ── Params de pantallas de bienvenida y despedida ──
$welcomeTitle    = (string) $params->get('welcome_title',    '¡Bienvenido a la Hermandad!');
$welcomeSubtitle = (string) $params->get('welcome_subtitle', 'Hermano del Metal');
$welcomeMessage  = (string) $params->get('welcome_message',  'Tu cuenta ha sido creada. Ya eres parte de la Hermandad del Metal.');
$welcomeImageRaw = (string) $params->get('welcome_image',    '');
$welcomeImage    = $welcomeImageRaw ? Uri::root() . ltrim($welcomeImageRaw, '/') : '';
$welcomeDelay    = max(1000, (int) $params->get('welcome_delay', 4000));

$byeTitle    = (string) $params->get('bye_title',    'Hasta Pronto, Hermano');
$byeSubtitle = (string) $params->get('bye_subtitle', 'Que el Metal te acompañe');
$byeMessage  = (string) $params->get('bye_message',  'Has cerrado sesión. La Hermandad te espera cuando quieras volver.');
$byeImageRaw = (string) $params->get('bye_image',    '');
$byeImage    = $byeImageRaw ? Uri::root() . ltrim($byeImageRaw, '/') : '';
$byeDelay    = max(1000, (int) $params->get('bye_delay', 3500));

// ── Assets ─────────────────────────────────────────────────
$mediaUrl = Uri::root(true) . '/media/mod_hmb_register/';
$doc->addStyleSheet($mediaUrl . 'css/mod_hmb_register.css', ['version' => 'auto']);
$doc->addScript(    $mediaUrl . 'js/mod_hmb_register.js',   ['version' => 'auto'], ['defer' => true]);

if ($enableGoogle && !empty($googleClientId)) {
    $doc->addScript('https://accounts.google.com/gsi/client', [], ['defer' => true, 'async' => true]);
}
if ($enableCaptcha && !empty($recaptchaSiteKey)) { /* script loaded inline in template */ }


$doc->addScriptOptions('mod_hmb_register', [
    'moduleId'       => $moduleId,
    'ajaxUrl'        => Uri::root() . 'index.php?option=com_ajax&module=hmb_register&format=json',
    'token'          => $token,
    'redirect'       => $redirectRegister,
    'siteUrl'        => Uri::root(),
    'welcome'        => [
        'title'    => $welcomeTitle,
        'subtitle' => $welcomeSubtitle,
        'message'  => $welcomeMessage,
        'image'    => $welcomeImage,
        'delay'    => $welcomeDelay,
    ],
    'bye'            => [
        'title'    => $byeTitle,
        'subtitle' => $byeSubtitle,
        'message'  => $byeMessage,
        'image'    => $byeImage,
        'delay'    => $byeDelay,
    ],
    'enableGoogle'   => $enableGoogle,
    'clientId'       => $googleClientId,
    'enableCaptcha'  => ($enableCaptcha && !empty($recaptchaSiteKey)),
    'captchaSecret'  => $recaptchaSecret,
    'captchaSiteKey' => $recaptchaSiteKey,
    'loginAction'    => Route::_('index.php?option=com_users&task=user.login'),
    'logoutAction'   => Route::_('index.php?option=com_users&task=user.logout'),
    'redirectLogin'  => $redirectLogin,
    'redirectLogout' => $redirectLogout,
]);

require ModuleHelper::getLayoutPath('mod_hmb_register', $params->get('layout', 'default'));
