<?php
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

require_once __DIR__ . '/helper.php';

$articles          = ModHmbLatestNewsHelper::getArticles($params);
$layout            = $params->get('layout', 'grid');
$showImage         = (bool) $params->get('show_image', 1);
$imageEffect       = $params->get('image_effect', 'pan_down');
$imageAspect       = $params->get('image_aspect', '16/9');
$showCategoryBadge = (bool) $params->get('show_category_badge', 1);
$showIntrotext     = (bool) $params->get('show_introtext', 1);
$showDate          = $params->get('show_date', 'created');
$showAuthor        = (bool) $params->get('show_author', 0);
$showHits          = (bool) $params->get('show_hits', 0);
$showReadmore      = (bool) $params->get('show_readmore', 1);
$showShare         = (bool) $params->get('show_share', 1);
$shareNets         = [
    'facebook' => (bool) $params->get('share_facebook', 1),
    'x'        => (bool) $params->get('share_x', 1),
    'whatsapp' => (bool) $params->get('share_whatsapp', 1),
    'telegram' => (bool) $params->get('share_telegram', 1),
    'linkedin' => (bool) $params->get('share_linkedin', 0),
];
$columns           = (int)  $params->get('columns', 3);
$darkMode          = (bool) $params->get('dark_mode', 1);
$accentColor       = $params->get('accent_color', '#CC0000');
$imageWidth        = (int)  $params->get('image_width', 400);
$imageHeight       = (int)  $params->get('image_height', 220);
$introLimit        = (int)  $params->get('introtext_limit', 120);
$moduleClassSfx    = $params->get('module_class_sfx', '');

$readmoreText = $params->get('readmore_text', 'Leer más');
if (empty($readmoreText) || strtoupper($readmoreText) === $readmoreText) {
    $readmoreText = 'Leer más';
}

require ModuleHelper::getLayoutPath('mod_hmb_latestnews', '_default');
