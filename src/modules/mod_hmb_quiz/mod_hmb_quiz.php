<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

require_once __DIR__ . '/src/Helper/HmbQuizHelper.php';

$helper  = new \Hmb\Module\HmbQuiz\Helper\HmbQuizHelper();
$enabled = $helper->componentEnabled();

$cfg          = $enabled ? $helper->getConfig() : [];
$board        = $enabled ? $helper->getLeaderboard((int) $params->get('board_limit', 10)) : [];
$genres       = ($enabled && !empty($cfg['chooseGenres'])) ? $helper->getGenres() : [];
$showBoard    = (bool) $params->get('show_board', 1);
$compact      = (bool) $params->get('compact', 1);
$moduleclass  = (string) $params->get('moduleclass_sfx', '');

// Load the shared assets from the component (single source of truth).
if ($enabled) {
	$app  = Factory::getApplication();
	$app->getLanguage()->load('com_hmb_quiz', JPATH_SITE);

	$doc  = $app->getDocument();
	$root = Uri::root(true);
	$cssV = @filemtime(JPATH_ROOT . '/media/com_hmb_quiz/css/game.css') ?: 1;
	$jsV  = @filemtime(JPATH_ROOT . '/media/com_hmb_quiz/js/game.js') ?: 1;
	$doc->addStyleSheet($root . '/media/com_hmb_quiz/css/game.css?v=' . $cssV);
	$doc->addScript('https://www.youtube.com/iframe_api');
	$doc->addScript($root . '/media/com_hmb_quiz/js/game.js?v=' . $jsV, [], ['defer' => true]);
}

require ModuleHelper::getLayoutPath('mod_hmb_quiz', $params->get('layout', 'default'));
