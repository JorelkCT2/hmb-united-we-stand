<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

require_once __DIR__ . '/src/Helper/RankingHelper.php';

$helper  = new \Hmb\Module\HmbQuizRanking\Helper\RankingHelper();
$enabled = $helper->componentEnabled();

$limit       = (int) $params->get('limit', 10);
$source      = (string) $params->get('source', 'scores');
$period      = (string) $params->get('period', '');
$showMedals  = (bool) $params->get('show_medals', 1);
$showDetail  = (bool) $params->get('show_detail', 1);
$styledTitle = (bool) $params->get('styled_title', 1);
$titleText   = isset($module->title) ? (string) $module->title : '';
$moduleclass = (string) $params->get('moduleclass_sfx', '');

if (!$enabled) {
	$rows = [];
} elseif ($source === 'league') {
	$rows = $helper->getLeague($limit);
} else {
	$rows = $helper->getTop($limit, $period);
}

require ModuleHelper::getLayoutPath('mod_hmb_quiz_ranking', $params->get('layout', 'default'));