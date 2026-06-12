<?php
/**
 * HMB - Heavy Metal Brothers Template
 * @package    Joomla.Site
 * @subpackage Templates.hmb
 * @copyright  (C) 2026 Heavy Metal Brothers
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app   = Factory::getApplication();
$wa    = $this->getWebAssetManager();
$lang  = $this->language;
$dir   = $this->direction;
$params = $app->getTemplate(true)->params;

// Template params
$stickyHeader = $params->get('stickyHeader', 1);
$showTopBar   = $params->get('showTopBar', 1);
$topBarText   = $params->get('topBarText', '⚡ HEAVY METAL BROTHERS ⚡');
$accentColor  = $params->get('accentColor', '#CC2200');
$colorScheme  = $params->get('colorName', 'red');
$fluid        = $params->get('fluidContainer', 0);

// Accesos destacados (franja de portada)
$showDestacados = $params->get('showDestacados', 1);
$destTitle      = trim((string) $params->get('destacadosTitle', 'Explora'));
$destItems      = [];
for ($i = 1; $i <= 4; $i++) {
    $l  = trim((string) $params->get("destacado{$i}_label", ''));
    $u  = trim((string) $params->get("destacado{$i}_url", ''));
    $ic = trim((string) $params->get("destacado{$i}_icon", ''));
    $im = trim((string) $params->get("destacado{$i}_img", ''));
    if ($l !== '' && $u !== '') {
        $destItems[] = ['label' => $l, 'url' => $u, 'icon' => $ic, 'img' => $im];
    }
}

// Color schemes
$colorSchemes = [
    'red'    => ['--fire:#CC2200','--blood:#8B0000','--ember:#FF4500','--gold:#B8860B','--gold-light:#DAA520'],
    'gold'   => ['--fire:#B8860B','--blood:#8B6914','--ember:#DAA520','--gold:#CC2200','--gold-light:#FF6B35'],
    'green'  => ['--fire:#1a7a00','--blood:#0d4d00','--ember:#2db300','--gold:#4CAF50','--gold-light:#8BC34A'],
    'blue'   => ['--fire:#003580','--blood:#001f4d','--ember:#0055cc','--gold:#4090D0','--gold-light:#60B0F0'],
    'purple' => ['--fire:#5c0099','--blood:#3d0066','--ember:#8800cc','--gold:#9C27B0','--gold-light:#CE93D8'],
];
$cssVars = isset($colorSchemes[$colorScheme]) ? implode(';', $colorSchemes[$colorScheme]) : implode(';', $colorSchemes['red']);

// Load template CSS and JS
$wa->registerAndUseStyle('hmb.base', 'templates/hmb/css/hmb-base.css', ['version' => '4.2.0']);
$wa->registerAndUseStyle('hmb.modules', 'templates/hmb/css/hmb-modules.css', ['version' => '4.2.0']);
$wa->registerAndUseStyle('hmb.layout', 'templates/hmb/css/hmb-layout.css', ['version' => '4.2.0']);
$wa->registerAndUseScript('hmb.template', 'templates/hmb/js/template.js', ['version' => '3.0.0'], ['defer' => true]);

// Phoca Cart — cargar CSS solo en páginas de la tienda
if ($app->input->get('option', '') === 'com_phocacart') {
    $wa->registerAndUseStyle('hmb.phocacart', 'templates/hmb/css/hmb-phocacart.css', ['version' => '1.0.0']);
}

// Module position helpers
$hasSidebarLeft  = $this->countModules('sidebar-left', true);
$hasSidebarRight = $this->countModules('sidebar-right', true);
$hasHero         = $this->countModules('hero', true);
$hasBanner       = $this->countModules('banner', true);

// Container class
$containerClass = $fluid ? 'hmb-container-fluid' : 'hmb-container';

// Grid class based on sidebars
$mainClass = 'hmb-main-full';
if ($hasSidebarLeft && $hasSidebarRight) $mainClass = 'hmb-main-both';
elseif ($hasSidebarLeft)  $mainClass = 'hmb-main-left';
elseif ($hasSidebarRight) $mainClass = 'hmb-main-right';

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXP1DXHHLN"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'G-XXP1DXHHLN');
	</script>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Metal+Mania&family=Cinzel+Decorative:wght@700;900&family=Oswald:wght@300;400;600;700&family=Rajdhani:wght@300;400;600;700&display=swap" rel="stylesheet">
	<style>
		:root { <?php echo $cssVars; ?> }
		.hmb-sticky-wrap{position:sticky;top:0;z-index:1000;background:#0a0000;}
		.hmb-fab{display:none;}
		@media(max-width:768px){
			.hmb-fab{display:flex;position:fixed;bottom:20px;right:16px;z-index:8500;background:linear-gradient(135deg,#CC2200,#8B0000);border:none;border-radius:50px;color:#fff;font-family:'Oswald',sans-serif;font-size:11px;letter-spacing:2px;padding:11px 20px;cursor:pointer;gap:8px;align-items:center;box-shadow:0 4px 24px rgba(139,0,0,.7);opacity:0;transform:translateY(12px);transition:opacity .3s,transform .3s;pointer-events:none;}
			.hmb-fab.visible{opacity:1;transform:translateY(0);pointer-events:auto;}
			.hmb-fab-icon{font-size:16px;line-height:1;}
		}
		body.home .hmb-main-wrap{display:none!important;}
		.hmb-header-top{border-bottom:1px solid #111;}
		.hmb-header-top-inner{display:flex;align-items:center;justify-content:space-between;gap:2rem;min-height:90px;padding:.75rem 0;}
		.hmb-header-banner{flex:1;display:flex;align-items:center;justify-content:center;overflow:hidden;padding:0 1rem;}
		.hmb-header-banner img{max-height:80px;width:auto;max-width:100%;display:block;object-fit:contain;}
		.hmb-header-user{display:flex;flex-direction:column;align-items:flex-end;justify-content:center;flex-shrink:0;gap:4px;}
		.hmb-header-user-hello{font-family:'Oswald',sans-serif;font-size:13px;letter-spacing:2px;color:#555;text-transform:uppercase;}
		.hmb-header-user-name{font-family:'Metal Mania',cursive;font-size:24px;color:#CC2200;letter-spacing:1px;line-height:1;text-shadow:0 0 12px rgba(204,34,0,.3);}
		.hmb-header-user-links{display:flex;gap:10px;}
		.hmb-header-user-links a{font-family:'Oswald',sans-serif;font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#444;text-decoration:none;transition:color .2s;}
		.hmb-header-user-links a:hover{color:#CC2200;}
		@media(max-width:768px){.hmb-header-user{display:none!important;}}
		.hmb-header-nav .hmb-nav-list,.hmb-header-nav .hmb-menu-wrap ul,.hmb-header-nav .hmb-menu-wrap .nav{list-style:none!important;display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;align-items:stretch!important;margin:0!important;padding:0!important;width:100%!important;}
		.hmb-header-nav .hmb-nav-list>li,.hmb-header-nav .hmb-menu-wrap ul li{position:relative!important;display:flex!important;align-items:stretch!important;flex-shrink:0!important;}
		.hmb-header-nav .hmb-nav-list>li>a,.hmb-header-nav .hmb-menu-wrap ul li>a{display:flex!important;align-items:center!important;font-family:'Oswald',sans-serif!important;font-size:16px!important;font-weight:600!important;letter-spacing:2px!important;text-transform:uppercase!important;color:var(--text-dim)!important;text-decoration:none!important;padding:0 20px!important;height:52px!important;white-space:nowrap!important;position:relative!important;transition:color .3s,background .3s!important;border-bottom:2px solid transparent!important;}
		.hmb-header-nav .hmb-nav-list>li>a:hover,.hmb-header-nav .hmb-nav-list>li.active>a,.hmb-header-nav .hmb-menu-wrap ul li:hover>a,.hmb-header-nav .hmb-menu-wrap ul li.active>a{color:#fff!important;background:rgba(139,0,0,.15)!important;border-bottom-color:var(--fire)!important;}
		.hmb-nav-dropdown,.hmb-header-nav .hmb-menu-wrap ul ul{display:none!important;position:absolute!important;top:100%!important;left:0!important;min-width:200px!important;background:var(--steel)!important;border:1px solid #222!important;border-top:2px solid var(--fire)!important;flex-direction:column!important;z-index:500!important;padding:.4rem 0!important;}
		.hmb-header-nav .hmb-nav-list>li:hover>.hmb-nav-dropdown,.hmb-header-nav .hmb-menu-wrap ul li:hover>ul{display:flex!important;}
		.hmb-nav-dropdown li,.hmb-header-nav .hmb-menu-wrap ul ul li{display:block!important;width:100%!important;}
		.hmb-nav-dropdown li a,.hmb-header-nav .hmb-menu-wrap ul ul li a{display:block!important;padding:9px 20px!important;font-family:'Oswald',sans-serif!important;font-size:14px!important;letter-spacing:1px!important;text-transform:uppercase!important;color:var(--text-dim)!important;border-bottom:1px solid #1a1a1a!important;transition:color .2s,background .2s,padding-left .2s!important;}
		.hmb-nav-dropdown li a:hover,.hmb-header-nav .hmb-menu-wrap ul ul li a:hover{color:var(--ember)!important;background:rgba(139,0,0,.1)!important;padding-left:26px!important;}
		#hmb-menu{overflow:visible!important;position:static!important;}
		.hmb-header,.hmb-header-nav,.hmb-header-top,.hmb-header-top-inner{overflow:visible!important;}
		[id^="hmb-menu-"]{display:block;width:100%;}
		#hmb-menu{display:flex;justify-content:center;}
		[id^="hmb-menu-"] .hmb-scroll{overflow-x:auto!important;overflow-y:visible!important;-webkit-overflow-scrolling:touch!important;scrollbar-width:none!important;}
		[id^="hmb-menu-"] .hmb-scroll::-webkit-scrollbar{display:none!important;}
		[id^="hmb-menu-"] .hmb-ml{display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;list-style:none!important;margin:0!important;padding:0!important;overflow:visible!important;width:max-content!important;min-width:100%!important;}
		[id^="hmb-menu-"] .hmb-ml>li{flex-shrink:0!important;position:relative!important;display:block!important;}
		[id^="hmb-menu-"] .hmb-ml>li>a{display:flex!important;align-items:center!important;gap:4px!important;padding:0 16px!important;height:46px!important;color:#999!important;font-family:'Oswald',sans-serif!important;font-size:16px!important;font-weight:600!important;letter-spacing:2px!important;text-transform:uppercase!important;text-decoration:none!important;white-space:nowrap!important;border-bottom:2px solid transparent!important;transition:color .2s,border-color .2s!important;}
		[id^="hmb-menu-"] .hmb-ml>li>a:hover,[id^="hmb-menu-"] .hmb-ml>li.active>a{color:#fff!important;border-bottom-color:#CC2200!important;}
		[id^="hmb-menu-"] .hmb-sub{display:none!important;position:absolute!important;top:100%!important;left:0!important;min-width:220px!important;background:#0a0000!important;border:1px solid #2a0000!important;border-top:2px solid #CC2200!important;list-style:none!important;margin:0!important;padding:4px 0!important;z-index:9999!important;box-shadow:0 8px 30px rgba(0,0,0,.9)!important;}
		[id^="hmb-menu-"] .hmb-ml>li:hover>.hmb-sub{display:block!important;}
		[id^="hmb-menu-"] .hmb-sub li{display:block!important;}
		[id^="hmb-menu-"] .hmb-sub li a{display:block!important;padding:11px 20px!important;color:#888!important;font-family:'Oswald',sans-serif!important;font-size:14px!important;letter-spacing:1px!important;text-transform:uppercase!important;text-decoration:none!important;border-bottom:1px solid #1a0000!important;white-space:nowrap!important;}
		[id^="hmb-menu-"] .hmb-sub li a:hover{color:#FF4500!important;background:rgba(139,0,0,.15)!important;}
		@media(max-width:768px){
			[id^="hmb-menu-"] .hmb-ml>li>a{padding:0 10px!important;font-size:11px!important;letter-spacing:1px!important;}
			[id^="hmb-menu-"] .hmb-ml>li:hover>.hmb-sub{display:none!important;}
		}
		ul.tags.list-inline,ul.tags,.tags.list-inline,div.tags,.article-tags,.com-content-article__tags,.tags-list,.article__tags,.com-content-article .tags{display:none!important;}
		.hmb-tags-bottom{display:flex!important;flex-wrap:wrap!important;gap:8px!important;margin-top:2rem!important;padding-top:1.5rem!important;border-top:1px solid #1a0000!important;}
		.hmb-tags-bottom a{display:inline-flex!important;align-items:center!important;padding:4px 12px!important;font-family:'Oswald',sans-serif!important;font-size:11px!important;font-weight:600!important;letter-spacing:1.5px!important;text-transform:uppercase!important;text-decoration:none!important;color:#888!important;background:linear-gradient(145deg,#1a1a1a,#0d0d0d)!important;border:1px solid #2a2a2a!important;border-top-color:#333!important;border-bottom-color:#000!important;border-radius:2px!important;transition:color .2s,border-color .2s,transform .2s!important;}
		.hmb-tags-bottom a:hover{color:#fff!important;border-color:#CC2200!important;transform:translateY(-2px)!important;}
		.hmb-tags-bottom-label{font-family:'Oswald',sans-serif!important;font-size:11px!important;letter-spacing:2px!important;color:#555!important;text-transform:uppercase!important;display:flex!important;align-items:center!important;white-space:nowrap!important;}
		.hmb-row-conciertos{background:var(--steel);padding:2rem 0;border-bottom:2px solid var(--blood);}
		.hmb-row-noticias{background:var(--dark);padding:3rem 0;border-bottom:1px solid #111;}
		.hmb-row-videos{background:var(--darker);padding:3rem 0;border-bottom:1px solid #111;}
		.hmb-row-slider{background:var(--darker);padding:2rem 0;border-bottom:1px solid #111;}
		.hmb-row-slider .hmb-container,.hmb-row-slider .hmb-container-fluid{padding-left:2rem;padding-right:2rem;max-width:1380px;}
		.hmb-row-slider img{width:100%;max-height:420px;object-fit:cover;display:block;border-radius:0;}
		.hmb-row-ef-frases{background:var(--dark);padding:3rem 0;border-bottom:1px solid #111;}
		.hmb-row-ef-frases-inner{display:grid;grid-template-columns:1fr 1fr;gap:2px;}
		.hmb-col-ef{background:var(--steel);padding:2rem;border-top:3px solid var(--blood);}
		.hmb-col-ef-right{display:flex;flex-direction:column;gap:0;}
		.hmb-subcol-frases,.hmb-subcol-temas{flex:1;}
		.hmb-subcol-temas{background:var(--steel);border-top:3px solid var(--blood);padding:2rem;}
		.hmb-row-noticias .hmb-module-title,.hmb-row-videos .hmb-module-title,.hmb-row-conciertos .hmb-module-title{font-family:'Metal Mania',cursive;font-size:26px;color:#efefef;padding:0 0 .75rem;margin-bottom:1.5rem;border-bottom:2px solid var(--blood);letter-spacing:2px;}
		.hmb-row-noticias .hmb-module-content,.hmb-row-videos .hmb-module-content{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5px;}
		.hmb-subcol-temas .hmb-module-title,.hmb-col-ef .hmb-module-title{font-family:'Metal Mania',cursive;font-size:20px;color:#efefef;padding:0 0 .6rem;margin-bottom:1rem;border-bottom:1px solid var(--blood);}
		.hmb-col-ef .hmb-module-content{padding:0;font-size:15px;line-height:1.8;}
		.hmb-subcol-temas iframe,.hmb-subcol-temas audio{width:100%!important;display:block!important;border:none!important;}
		.hmb-menu-close-btn{display:none!important;}
		.hmb-menu-overlay{display:none;}
		.hmb-header-nav .hmb-menu-wrap ul li ul{display:none;}
		.hmb-header-nav .hmb-menu-wrap ul li:hover>ul{display:flex;}
		.hmb-float-login{position:fixed;right:0;top:50%;transform:translateY(-50%);z-index:9000;display:flex;}
		.hmb-float-tab{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;background:linear-gradient(180deg,var(--fire),var(--blood));border:none;border-radius:6px 0 0 6px;padding:14px 8px;cursor:pointer;color:#fff;box-shadow:-4px 0 20px rgba(139,0,0,.4);transition:all .3s;min-height:100px;}
		.hmb-float-icon{font-size:20px;writing-mode:horizontal-tb;}
		.hmb-float-label{font-family:'Oswald',sans-serif;font-size:10px;letter-spacing:3px;font-weight:700;writing-mode:vertical-lr;}
		.hmb-float-panel{position:absolute;right:100%;top:50%;width:320px;background:var(--steel);border:1px solid #222;border-right:none;border-radius:8px 0 0 8px;box-shadow:-8px 0 40px rgba(0,0,0,.8);overflow:hidden;opacity:0;pointer-events:none;transform:translateY(-50%) translateX(20px);transition:opacity .35s,transform .35s;max-height:90vh;overflow-y:auto;}
		.hmb-float-panel.is-open{opacity:1;pointer-events:auto;transform:translateY(-50%) translateX(0);}
		.hmb-float-close{position:absolute;top:8px;left:10px;background:none;border:none;color:var(--text-dim);font-size:14px;cursor:pointer;z-index:10;padding:4px 8px;}
		@media(max-width:1024px){.hmb-row-noticias .hmb-module-content,.hmb-row-videos .hmb-module-content{grid-template-columns:repeat(2,1fr);}}
		@media(max-width:768px){
			.hmb-header-top{display:none!important;}
			.hmb-slider__img{max-height:none!important;height:auto!important;width:100%!important;}
			.hmb-row-ef-frases-inner{grid-template-columns:1fr!important;}
			.hmb-row-noticias .hmb-module-content,.hmb-row-videos .hmb-module-content{grid-template-columns:1fr!important;}
			.hmb-art-banner{width:calc(100% + 4rem)!important;margin-left:-2rem!important;max-width:none!important;}
			.hmb-art-banner img{width:100%!important;height:160px!important;object-fit:cover!important;display:block!important;}
			.hmb-art-presentacion{grid-template-columns:1fr!important;}
			.hmb-art-foto img{width:100%!important;height:200px!important;}
			.hmb-art-foto-izq{border-right:none!important;border-bottom:2px solid var(--blood)!important;}
			.hmb-art-foto-der{border-left:none!important;border-top:2px solid var(--blood)!important;}
			.hmb-menu-toggle{display:flex!important;flex-direction:column!important;gap:5px!important;background:none!important;border:1px solid #8B0000!important;cursor:pointer!important;padding:8px!important;border-radius:3px!important;}
			.hmb-menu-toggle span{display:block!important;width:22px!important;height:2px!important;background:#CC2200!important;}
			.hmb-menu-overlay{display:none!important;position:fixed!important;top:0!important;left:0!important;width:100%!important;height:100%!important;background:rgba(0,0,0,.8)!important;z-index:9998!important;}
			.hmb-menu-overlay.is-open{display:block!important;}
			.hmb-menu-wrap{display:none!important;position:fixed!important;top:0!important;left:0!important;width:80vw!important;max-width:300px!important;height:100vh!important;overflow-y:auto!important;overflow-x:hidden!important;background:#060000!important;border-right:2px solid #CC2200!important;z-index:9999!important;padding-top:56px!important;box-shadow:6px 0 30px rgba(0,0,0,.9)!important;}
			.hmb-menu-wrap.is-open{display:block!important;}
			.hmb-menu-close-btn{display:block!important;position:fixed!important;top:0!important;left:0!important;width:80vw!important;max-width:300px!important;background:#0d0000!important;border:none!important;border-bottom:1px solid #8B0000!important;color:#CC2200!important;font-size:18px!important;cursor:pointer!important;padding:16px 20px!important;text-align:right!important;z-index:10000!important;box-sizing:border-box!important;}
			.hmb-header-nav .hmb-menu-wrap ul,.hmb-header-nav .hmb-nav-list{display:block!important;width:100%!important;}
			.hmb-header-nav .hmb-menu-wrap ul li{display:block!important;width:100%!important;}
			.hmb-header-nav .hmb-menu-wrap ul li:empty{display:none!important;}
			.hmb-header-nav .hmb-menu-wrap ul li>a{display:flex!important;justify-content:space-between!important;align-items:center!important;font-size:14px!important;padding:14px 20px!important;height:auto!important;letter-spacing:2px!important;border-bottom:1px solid #1a0000!important;color:#C8C8C8!important;border-left:3px solid transparent!important;white-space:nowrap!important;}
			.hmb-header-nav .hmb-menu-wrap ul li>a:hover,.hmb-header-nav .hmb-menu-wrap ul li.active>a{color:#FF4500!important;background:rgba(139,0,0,.2)!important;border-left-color:#CC2200!important;}
			.hmb-header-nav .hmb-menu-wrap ul li.has-children>a::after{content:"›"!important;font-size:20px!important;opacity:.7!important;transition:transform .2s!important;flex-shrink:0!important;}
			.hmb-header-nav .hmb-menu-wrap ul li:not(.has-children)>a::after{display:none!important;}
			.hmb-header-nav .hmb-menu-wrap ul li.has-children.is-open>a::after{transform:rotate(90deg)!important;}
			.hmb-header-nav .hmb-menu-wrap ul ul{display:none!important;position:static!important;width:100%!important;background:rgba(15,0,0,.9)!important;border:none!important;box-shadow:none!important;padding:0!important;flex-direction:column!important;}
			.hmb-header-nav .hmb-menu-wrap ul li:hover>ul{display:none!important;}
			.hmb-header-nav .hmb-menu-wrap ul li.is-open>ul{display:block!important;}
			.hmb-header-nav .hmb-menu-wrap ul ul li a{display:block!important;padding:11px 20px 11px 36px!important;font-size:12px!important;letter-spacing:1px!important;color:#888!important;border-bottom:1px solid #0d0000!important;height:auto!important;}
		}
		@media(max-width:480px){
			.hmb-header-top-inner{flex-wrap:wrap;min-height:auto;padding:.5rem 0;}
			.hmb-header-banner{flex-basis:100%;order:3;}
		}
		.hmb-panel-menu{padding:0;}
		.hmb-panel-menu ul,.hmb-panel-menu .nav{list-style:none!important;padding:0!important;margin:0!important;display:block!important;}
		.hmb-panel-menu ul li,.hmb-panel-menu .nav-item{display:block!important;width:100%!important;border-bottom:1px solid #1a0000!important;}
		.hmb-panel-menu ul li a,.hmb-panel-menu .nav-link{display:block!important;padding:14px 20px!important;color:#C8C8C8!important;font-family:'Oswald',sans-serif!important;font-size:13px!important;letter-spacing:2px!important;text-transform:uppercase!important;text-decoration:none!important;border-left:3px solid transparent!important;}
		.hmb-panel-menu ul li a:hover,.hmb-panel-menu ul li.active>a,.hmb-panel-menu .nav-link:hover,.hmb-panel-menu .active>.nav-link{color:#FF4500!important;background:rgba(139,0,0,.15)!important;border-left-color:#CC2200!important;}
		.hmb-panel-menu ul ul,.hmb-panel-menu .dropdown-menu{display:none!important;position:static!important;background:rgba(8,0,0,.8)!important;border:none!important;box-shadow:none!important;padding:0!important;float:none!important;width:100%!important;}
		.hmb-panel-menu ul li.is-open>ul,.hmb-panel-menu .nav-item.is-open>.dropdown-menu{display:block!important;}
		.hmb-panel-menu ul ul li a,.hmb-panel-menu .dropdown-item{padding:11px 20px 11px 36px!important;font-size:12px!important;color:#777!important;letter-spacing:1px!important;}
		.hmb-panel-menu ul ul li a:hover,.hmb-panel-menu .dropdown-item:hover{color:#FF4500!important;background:rgba(139,0,0,.1)!important;}
		.hmb-art-banner{width:100%;overflow:hidden;border-bottom:3px solid var(--blood);display:block;}
		.hmb-art-banner img{width:100%;height:260px;object-fit:cover;object-position:center 30%;display:block;filter:brightness(.8);}
		.hmb-art-presentacion{display:grid;grid-template-columns:160px 1fr 160px;gap:0;background:var(--darker);align-items:start;}
		.hmb-art-foto{position:relative;overflow:hidden;}
		.hmb-art-foto img{width:160px;height:220px;object-fit:cover;object-position:top center;display:block;filter:brightness(.85) contrast(1.1);transition:filter .4s;}
		.hmb-art-foto:hover img{filter:brightness(1) contrast(1.1);}
		.hmb-art-foto-izq{border-right:2px solid var(--blood);}
		.hmb-art-foto-der{border-left:2px solid var(--blood);}
		.hmb-art-foto-label{background:#0d0000;padding:.6rem .5rem;text-align:center;border-top:1px solid #1a0000;}
		.hmb-art-foto-label span{font-family:'Metal Mania',cursive;font-size:16px;color:var(--white);letter-spacing:2px;display:block;line-height:1.2;}
		.hmb-art-foto-label small{font-family:'Oswald',sans-serif;font-size:9px;letter-spacing:2px;text-transform:uppercase;color:var(--gold-light);}
		.hmb-art-texto{padding:2.5rem 2rem;background:var(--darker);border-top:3px solid var(--blood);}
		.hmb-art-texto h2{font-family:'Metal Mania',cursive;font-size:clamp(24px,3.5vw,42px);color:var(--white);margin:0 0 1.5rem;text-align:center;text-shadow:0 0 30px rgba(204,34,0,.4);letter-spacing:2px;line-height:1.1;}
		.hmb-art-texto h2::after{content:'';display:block;width:80px;height:2px;background:linear-gradient(90deg,transparent,var(--fire),transparent);margin:.75rem auto 0;}
		.hmb-art-texto p{font-size:15.5px;line-height:1.85;color:#B8B8B8;margin-bottom:1.4rem;text-align:justify;}
		.hmb-art-texto p:first-of-type::first-letter{font-family:'Metal Mania',cursive;font-size:60px;float:left;line-height:.75;margin:.05em .12em 0 0;color:var(--fire);text-shadow:0 0 20px rgba(204,34,0,.5);}
		.hmb-art-separador{text-align:center;margin:2rem 0;color:var(--blood);font-size:18px;letter-spacing:8px;}
		.hmb-art-cita{border-left:3px solid var(--fire);padding:1rem 1.5rem;margin:2rem 0;background:rgba(139,0,0,.08);}
		.hmb-art-cita p{font-family:'Cinzel',serif;font-style:italic;font-size:16px;color:var(--gold-light)!important;text-align:left!important;margin:0!important;}
		@media(max-width:768px){
			.hmb-art-banner img{height:160px!important;}
			.hmb-art-presentacion{display:grid!important;grid-template-columns:1fr 1fr!important;grid-template-rows:auto auto!important;}
			.hmb-art-texto{grid-column:1 / -1!important;grid-row:1!important;padding:1.2rem 1rem!important;}
			.hmb-art-foto-izq{grid-column:1!important;grid-row:2!important;border-right:1px solid var(--blood)!important;border-top:2px solid var(--blood)!important;}
			.hmb-art-foto-der{grid-column:2!important;grid-row:2!important;border-left:none!important;border-top:2px solid var(--blood)!important;}
			.hmb-art-foto img{width:100%!important;height:auto!important;object-fit:contain!important;object-position:top center!important;display:block!important;}
			.hmb-art-texto h2{font-size:20px!important;margin-bottom:1rem!important;}
			.hmb-art-texto p{font-size:14px!important;line-height:1.7!important;}
			.hmb-art-texto p:first-of-type::first-letter{font-size:40px!important;}
		}

		
		/* ══ PHOCA CART 6 ══════════════════════════════════════════════ */

		/* Grid fix */
		.ph-row-flex.grid .row-item .ph-item-box .ph-thumbnail-c .ph-item-content{flex-direction:column!important;}
		.row-item{width:50%!important;padding:.6rem!important;box-sizing:border-box!important;}
		@media(max-width:900px){.row-item{width:50%!important;}}
		@media(max-width:600px){.row-item{width:100%!important;}}

		/* Tarjeta producto */
		.thumbnail.ph-thumbnail{background:#1a1a1a!important;border:1px solid #222!important;border-radius:0!important;transition:border-color .3s,transform .3s,box-shadow .3s!important;}
		.thumbnail.ph-thumbnail:hover{border-color:#8B0000!important;transform:translateY(-4px)!important;box-shadow:0 12px 30px rgba(0,0,0,.6)!important;}
		.ph-image{transition:transform .4s,filter .4s!important;}
		.thumbnail.ph-thumbnail:hover .ph-image{transform:scale(1.04)!important;filter:brightness(1.05)!important;}
		.ph-product-header{font-family:'Oswald',sans-serif!important;font-size:1rem!important;font-weight:600!important;letter-spacing:1.5px!important;text-transform:uppercase!important;color:#EFEFEF!important;margin:.75rem 0 .4rem!important;}
		.thumbnail.ph-thumbnail:hover .ph-product-header{color:#FF4500!important;}
		.ph-item-desc p{color:#888!important;font-family:'Rajdhani',sans-serif!important;font-size:.88rem!important;}
		.ph-price-txt{font-family:'Oswald',sans-serif!important;font-size:.65rem!important;letter-spacing:1.5px!important;text-transform:uppercase!important;color:#555!important;}
		.ph-price-original{color:#444!important;text-decoration:line-through!important;}
		.ph-price-brutto{font-family:'Metal Mania',cursive!important;font-size:1.3rem!important;color:#CC2200!important;letter-spacing:1px!important;}
		.ph-btn{border-radius:0!important;font-family:'Oswald',sans-serif!important;font-size:.7rem!important;letter-spacing:2px!important;text-transform:uppercase!important;}
		.ph-button-view-product-box .ph-btn,.ph-button-view-product-box a.btn{background:transparent!important;border:1px solid #333!important;color:#777!important;border-radius:0!important;}
		.ph-button-view-product-box .ph-btn:hover,.ph-button-view-product-box a.btn:hover{border-color:#8B0000!important;color:#fff!important;}
		.ph-button-add-to-cart-box .ph-btn,.ph-button-add-to-cart-box button.btn{background:#8B0000!important;border:1px solid #8B0000!important;color:#fff!important;border-radius:0!important;}
		.ph-button-add-to-cart-box .ph-btn:hover,.ph-button-add-to-cart-box button.btn:hover{background:#CC2200!important;border-color:#CC2200!important;}

		/* Producto individual */
		.ph-header{font-family:'Metal Mania',cursive!important;font-size:2rem!important;color:#EFEFEF!important;letter-spacing:2px!important;text-shadow:0 0 20px rgba(204,34,0,.3)!important;border-bottom:2px solid #8B0000!important;padding-bottom:.75rem!important;margin:0 0 1rem!important;}
		.ph-item-view-data-box{padding-left:1.5rem!important;}
		.ph-desc p{font-family:'Rajdhani',sans-serif!important;color:#aaa!important;font-size:1rem!important;line-height:1.6!important;margin:1rem 0!important;}
		.ph-top .btn-secondary{background:transparent!important;border:1px solid #333!important;color:#777!important;border-radius:0!important;font-family:'Oswald',sans-serif!important;font-size:.72rem!important;letter-spacing:2px!important;text-transform:uppercase!important;}
		.ph-top .btn-secondary:hover{border-color:#8B0000!important;color:#fff!important;}
		.pc-status-en-stock{color:#4a9!important;font-family:'Oswald',sans-serif!important;}
		.ph-stock-txt{font-family:'Oswald',sans-serif!important;font-size:.72rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#555!important;}
		.ph-input-quantity{background:#111!important;border:1px solid #333!important;color:#fff!important;border-radius:0!important;width:60px!important;text-align:center!important;}
		.ph-form-button .ph-btn,.ph-form-button button.btn{background:linear-gradient(135deg,#8B0000,#6b0000)!important;border:1px solid #8B0000!important;color:#fff!important;border-radius:0!important;font-family:'Oswald',sans-serif!important;font-size:.8rem!important;letter-spacing:3px!important;text-transform:uppercase!important;padding:.65rem 1.5rem!important;}
		.ph-form-button .ph-btn:hover,.ph-form-button button.btn:hover{background:#CC2200!important;border-color:#CC2200!important;}
		.ph-item-add-to-cart-box{display:flex!important;align-items:center!important;gap:1rem!important;margin-top:1.5rem!important;}
		.img-thumbnail{border:1px solid #222!important;border-radius:0!important;background:#111!important;}
		.nav-tabs{border-bottom:2px solid #8B0000!important;margin-top:2rem!important;}
		.nav-tabs .nav-link{font-family:'Oswald',sans-serif!important;font-size:.75rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#555!important;border:none!important;border-radius:0!important;}
		.nav-tabs .nav-link.active{color:#fff!important;background:#8B0000!important;}
		.ph-message{font-family:'Rajdhani',sans-serif!important;color:#555!important;padding:1rem 0!important;}
		.pc-si{width:14px!important;height:14px!important;fill:currentColor!important;}

		/* Carrusel */
		.hmb-carousel-wrap{position:relative;overflow:hidden;background:#0a0a0a;border:1px solid #222;width:100%;margin:0 auto;}
		.hmb-carousel-slides{display:flex;transition:transform .45s cubic-bezier(.25,.46,.45,.94);}
		.hmb-carousel-slide{min-width:100%;flex-shrink:0;}
		.hmb-carousel-slide img{width:100%;height:auto;max-height:460px;object-fit:contain;display:block;}
		.hmb-carousel-btn{position:absolute;top:50%;transform:translateY(-50%);background:rgba(0,0,0,.7);border:1px solid #333;color:#fff;width:38px;height:38px;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:10;transition:background .2s;padding:0;font-size:1.1rem;}
		.hmb-carousel-btn:hover{background:#8B0000;border-color:#CC2200;}
		.hmb-carousel-prev{left:8px;}.hmb-carousel-next{right:8px;}
		.hmb-carousel-dots{display:flex;justify-content:center;gap:6px;padding:.6rem 0;}
		.hmb-carousel-dot{width:7px;height:7px;border-radius:50%;background:#333;border:none;cursor:pointer;padding:0;transition:background .2s,transform .2s;}
		.hmb-carousel-dot.active{background:#CC2200;transform:scale(1.3);}
		.hmb-thumbs{display:flex;gap:.4rem;flex-wrap:wrap;padding:.4rem 0;}
		.hmb-thumb{width:64px;height:64px;object-fit:cover;cursor:pointer;border:2px solid transparent;opacity:.55;transition:border-color .2s,opacity .2s;}
		.hmb-thumb:hover,.hmb-thumb.active{border-color:#CC2200;opacity:1;}

		/* Módulos sidebar */
		.ph-cart-module-box{background:#111;border:1px solid #1a1a1a;border-top:2px solid #8B0000;}
		.ph-cart-module-box .ph-cart-small-box{padding:.5rem .75rem;}
		.ph-cart-small-title a{color:#EFEFEF!important;text-decoration:none!important;font-family:'Oswald',sans-serif!important;font-size:.78rem!important;letter-spacing:1px!important;text-transform:uppercase!important;}
		.ph-cart-small-title a:hover{color:#CC2200!important;}
		.ph-cart-small-price{font-family:'Metal Mania',cursive!important;color:#CC2200!important;font-size:.9rem!important;}
		.ph-cart-total-txt,.ph-cart-brutto-currency-txt{font-family:'Oswald',sans-serif!important;font-size:.7rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#555!important;}
		.ph-cart-total,.ph-cart-brutton-currency,.ph-b{font-family:'Metal Mania',cursive!important;font-size:1.2rem!important;color:#fff!important;font-weight:normal!important;}
		.ph-cart-link-checkout{padding:.75rem!important;border-top:1px solid #1a1a1a!important;}
		.ph-cart-link-checkout a{font-family:'Oswald',sans-serif!important;font-size:.75rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#fff!important;text-decoration:none!important;display:block!important;background:#8B0000!important;padding:.6rem!important;text-align:center!important;border:1px solid #8B0000!important;transition:background .2s!important;}
		.ph-cart-link-checkout a:hover{background:#CC2200!important;}
		.phProductScrollerModuleBox{background:#111;border:1px solid #1a1a1a;border-top:2px solid #8B0000;padding:.75rem;}
		.phProductScrollerModuleBox .ph-item-box{background:#1a1a1a;border:1px solid #222;margin-bottom:.75rem;transition:border-color .3s;}
		.phProductScrollerModuleBox .ph-item-box:hover{border-color:#8B0000;}
		.phProductScrollerModuleBox .ph-product-header,.phProductScrollerModuleBox h3{font-family:'Oswald',sans-serif!important;font-size:.82rem!important;font-weight:600!important;letter-spacing:1.5px!important;text-transform:uppercase!important;color:#EFEFEF!important;margin:.5rem!important;}
		.phProductScrollerModuleBox .ph-price-brutto{font-family:'Metal Mania',cursive!important;font-size:1rem!important;color:#CC2200!important;}
		.phProductScrollerModuleBox img{width:100%!important;height:auto!important;display:block!important;object-fit:cover!important;}

		/* Popup añadir al carrito */
		#phAddToCartPopup{display:none;position:fixed;inset:0;z-index:9500;background:rgba(0,0,0,.8);align-items:center;justify-content:center;}
		#phAddToCartPopup.hmb-show{display:flex!important;}
		#phAddToCartPopup .modal-dialog{background:#1a1a1a;border:1px solid #222;border-top:3px solid #8B0000;max-width:460px;width:90%;padding:0;}
		#phAddToCartPopup .modal-header{padding:1rem 1.25rem;border-bottom:1px solid #111;display:flex;align-items:center;justify-content:space-between;}
		#phAddToCartPopup .modal-title{font-family:'Oswald',sans-serif;font-size:.9rem;letter-spacing:2px;text-transform:uppercase;color:#EFEFEF;}
		#phAddToCartPopup .modal-body{padding:1.25rem;}
		#phAddToCartPopup .modal-footer{display:none;}
		#phAddToCartPopup .btn-close{background:none;border:none;color:#555;font-size:1.2rem;cursor:pointer;padding:0;}
		#phAddToCartPopup .btn-close:hover{color:#CC2200;}
		#phAddToCartPopup .ph-center{text-align:center;padding:.5rem;}
		#phAddToCartPopup .row{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem;padding:0 .5rem 1rem;}
		#phAddToCartPopup .col-sm-6,.col-6{flex:1;}
		#phAddToCartPopup .btn.btn-primary.ph-btn{background:transparent!important;border:1px solid #333!important;color:#888!important;border-radius:0!important;font-family:'Oswald',sans-serif!important;font-size:.75rem!important;letter-spacing:2px!important;text-transform:uppercase!important;padding:.65rem 1rem!important;display:block!important;text-align:center!important;text-decoration:none!important;width:100%!important;}
		#phAddToCartPopup .btn.btn-primary.ph-btn:hover{border-color:#8B0000!important;color:#fff!important;}
		#phAddToCartPopup .btn.btn-success.ph-btn{background:#8B0000!important;border:1px solid #8B0000!important;color:#fff!important;border-radius:0!important;font-family:'Oswald',sans-serif!important;font-size:.75rem!important;letter-spacing:2px!important;text-transform:uppercase!important;padding:.65rem 1rem!important;display:block!important;text-align:center!important;text-decoration:none!important;width:100%!important;}
		#phAddToCartPopup .btn.btn-success.ph-btn:hover{background:#CC2200!important;border-color:#CC2200!important;}

		/* Checkout */
		.ph-checkout-step-box{background:#111;border:1px solid #1a1a1a;border-left:3px solid #8B0000;margin-bottom:1rem;padding:1.25rem;}
		.ph-checkout-step-title,.ph-checkout-step-box h3{font-family:'Oswald',sans-serif!important;font-size:.9rem!important;letter-spacing:2px!important;text-transform:uppercase!important;color:#EFEFEF!important;margin-bottom:1rem!important;}
		.ph-checkout-step-box label{font-family:'Oswald',sans-serif!important;font-size:.72rem!important;letter-spacing:1.5px!important;text-transform:uppercase!important;color:#555!important;}
		.ph-checkout-step-box input,.ph-checkout-step-box select,.ph-checkout-step-box textarea{background:#0a0a0a!important;border:1px solid #222!important;color:#EFEFEF!important;border-radius:0!important;}
		.ph-checkout-step-box input:focus,.ph-checkout-step-box select:focus{border-color:#8B0000!important;outline:none!important;}
		.ph-checkout-cart-box img{width:60px!important;height:60px!important;object-fit:cover!important;}
		.ph-checkout-cart-box a{color:#EFEFEF!important;font-family:'Oswald',sans-serif!important;font-size:.85rem!important;}
		.ph-checkout-step-box .btn-primary,.ph-checkout-step-box .btn-success{background:#8B0000!important;border:1px solid #8B0000!important;color:#fff!important;border-radius:0!important;font-family:'Oswald',sans-serif!important;font-size:.8rem!important;letter-spacing:2px!important;text-transform:uppercase!important;}
		.ph-checkout-step-box .btn-primary:hover,.ph-checkout-step-box .btn-success:hover{background:#CC2200!important;border-color:#CC2200!important;}

		/* Ocultar passkey */
		[id*="asskey"],[class*="asskey"],[id*="Passkey"],[class*="Passkey"]{display:none!important;}

		@media(max-width:768px){.ph-item-view-data-box{padding-left:0!important;margin-top:1.5rem!important;}}

		/* ── CARRUSEL PRODUCTO ── */
		.hmb-carousel-wrap{position:relative;overflow:hidden;background:#0a0a0a;border:1px solid #222;width:100%;margin:0 auto;}
		.hmb-carousel-slides{display:flex;transition:transform .45s cubic-bezier(.25,.46,.45,.94);}
		.hmb-carousel-slide{flex-shrink:0;}
		.hmb-carousel-slide img{width:100%;height:auto;max-height:480px;object-fit:contain;display:block;}
		.hmb-carousel-btn{position:absolute;top:50%;transform:translateY(-50%);background:rgba(0,0,0,.7);border:1px solid #333;color:#fff;width:38px;height:38px;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:10;transition:background .2s;padding:0;font-size:1.1rem;}
		.hmb-carousel-btn:hover{background:#8B0000;border-color:#CC2200;}
		.hmb-carousel-prev{left:8px;}.hmb-carousel-next{right:8px;}
		.hmb-carousel-dots{display:flex;justify-content:center;gap:6px;padding:.6rem 0;}
		.hmb-carousel-dot{width:7px;height:7px;border-radius:50%;background:#333;border:none;cursor:pointer;padding:0;transition:background .2s,transform .2s;}
		.hmb-carousel-dot.active{background:#CC2200;transform:scale(1.3);}
		.hmb-thumbs{display:flex;gap:.4rem;flex-wrap:wrap;padding:.4rem 0;}
		.hmb-thumb{width:64px;height:64px;object-fit:cover;cursor:pointer;border:2px solid transparent;opacity:.55;transition:border-color .2s,opacity .2s;}
		.hmb-thumb:hover,.hmb-thumb.active{border-color:#CC2200;opacity:1;}
		.ph-item-image-add-box{display:none!important;}
	</style>
</head><?php
$menu   = $app->getMenu();
$isHome = ($menu->getActive() == $menu->getDefault());
$bodyClasses = 'hmb-body ' . $colorScheme . '-scheme';
if ($isHome) $bodyClasses .= ' home';
?>
<body class="<?php echo $bodyClasses; ?>">
<div class="hmb-noise" aria-hidden="true"></div>
<?php if ($showTopBar): ?>
<div class="hmb-topbar" role="marquee">
	<?php if ($this->countModules('topbar')): ?>
		<jdoc:include type="modules" name="topbar" />
	<?php else: ?>
		<span><?php echo htmlspecialchars($topBarText); ?></span>
	<?php endif; ?>
</div>
<?php endif; ?>
<div class="hmb-sticky-wrap">
<header class="hmb-header" role="banner">
	<div class="hmb-header-top">
		<div class="<?php echo $containerClass; ?> hmb-header-top-inner">
			<div class="hmb-logo">
				<?php if ($this->countModules('header')): ?>
					<jdoc:include type="modules" name="header" />
				<?php else: ?>
					<a href="<?php echo Uri::root(); ?>" class="hmb-logo-link">
						<span class="hmb-logo-text"><?php echo $app->get('sitename'); ?></span>
					</a>
				<?php endif; ?>
			</div>
			<div class="hmb-header-banner">
				<?php if ($this->countModules('banner')): ?>
					<jdoc:include type="modules" name="banner" />
				<?php endif; ?>
			</div>
			<?php $headerUser = Factory::getApplication()->getIdentity(); ?>
			<div class="hmb-header-user">
				<?php if ($headerUser->id): ?>
					<span class="hmb-header-user-hello">Bienvenido de nuevo</span>
					<span class="hmb-header-user-name"><?php echo htmlspecialchars($headerUser->username, ENT_QUOTES, 'UTF-8'); ?></span>
					<div class="hmb-header-user-links">
						<a href="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>index.php/registro/perfil-usuario">&#9670; Mi perfil</a>
						<a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_users&task=user.logout&' . \Joomla\CMS\Session\Session::getFormToken() . '=1'); ?>">&#9632; Salir</a>
					</div>
				<?php else: ?>
					<span class="hmb-header-user-hello">Área de miembros</span>
					<div class="hmb-header-user-links">
						<a href="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>index.php/registro">&#9658; Acceder</a>
						<a href="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>index.php/registro?layout=register">&#9670; Registrarse</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>
<div class="hmb-header-nav">
	<div id="hmb-menu" style="width:100%;overflow:visible;position:relative;">
		<jdoc:include type="modules" name="menu" style="none" />
	</div>
</div>
</div>
<button class="hmb-fab" id="hmb-fab" aria-label="Abrir menú">
	<span class="hmb-fab-icon">☰</span> MENÚ
</button>
<?php if ($isHome && $showDestacados && !empty($destItems)):
	$destRoot = rtrim(Uri::root(), '/');
	$destCols = min(count($destItems), 4);
?>
<style>
	.hmb-destacados{background:var(--darker,#080000);padding:2.4rem 0 2rem;border-bottom:2px solid var(--blood,#8B0000);}
	.hmb-destacados-title{font-family:'Metal Mania',cursive;text-align:center;font-size:clamp(22px,4vw,34px);color:#fff;letter-spacing:3px;margin:0 0 1.6rem;text-shadow:0 0 24px rgba(204,34,0,.45);}
	.hmb-destacados-grid{display:grid;grid-template-columns:repeat(<?php echo $destCols; ?>,1fr);gap:14px;max-width:1100px;margin:0 auto;padding:0 1.5rem;}
	.hmb-dest-card{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.7rem;text-align:center;padding:1.9rem 1rem;background:linear-gradient(160deg,#160000,#0a0000);border:1px solid #2a0000;text-decoration:none;overflow:hidden;transition:transform .3s,border-color .3s,box-shadow .3s;}
	.hmb-dest-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--fire,#CC2200),var(--blood,#8B0000),transparent);transform:scaleX(0);transform-origin:left;transition:transform .35s ease;}
	.hmb-dest-card:hover{transform:translateY(-5px);border-color:var(--fire,#CC2200);box-shadow:0 12px 30px rgba(139,0,0,.4);text-decoration:none;}
	.hmb-dest-card:hover::before{transform:scaleX(1);}
	.hmb-dest-icon{font-size:2.5rem;line-height:1;filter:drop-shadow(0 0 10px rgba(204,34,0,.5));transition:transform .3s;}
	.hmb-dest-card:hover .hmb-dest-icon{transform:scale(1.15);}
	.hmb-dest-label{font-family:'Oswald',sans-serif;font-size:14px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:#cccccc;transition:color .25s;position:relative;z-index:2;}
	.hmb-dest-card:hover .hmb-dest-label{color:#fff;}
	.hmb-dest-icon{position:relative;z-index:2;}
	/* Tarjetas con imagen de fondo */
	.hmb-dest-card.has-img{background:#0a0000 center/cover no-repeat;min-height:160px;justify-content:flex-end;}
	.hmb-dest-card.has-img::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.30) 0%,rgba(0,0,0,.45) 45%,rgba(0,0,0,.82) 100%);z-index:1;transition:background .3s;}
	.hmb-dest-card.has-img:hover::after{background:linear-gradient(180deg,rgba(0,0,0,.20) 0%,rgba(0,0,0,.40) 45%,rgba(0,0,0,.80) 100%);}
	.hmb-dest-card.has-img .hmb-dest-label{color:#fff;font-size:15px;text-shadow:0 2px 10px rgba(0,0,0,.9);}
	.hmb-dest-card.has-img .hmb-dest-icon{filter:drop-shadow(0 2px 8px rgba(0,0,0,.9));}
	@media(max-width:768px){
		.hmb-destacados{padding:1.6rem 0 1.3rem;}
		.hmb-destacados-grid{grid-template-columns:repeat(2,1fr);gap:8px;padding:0 .75rem;}
		.hmb-dest-card{padding:1.3rem .6rem;}
		.hmb-dest-icon{font-size:2rem;}
		.hmb-dest-label{font-size:12px;letter-spacing:1px;}
	}
</style>
<section class="hmb-destacados" aria-label="Accesos destacados">
	<?php if ($destTitle !== ''): ?>
	<h2 class="hmb-destacados-title"><?php echo htmlspecialchars($destTitle, ENT_QUOTES); ?></h2>
	<?php endif; ?>
	<div class="hmb-destacados-grid">
		<?php foreach ($destItems as $d):
			$u    = $d['url'];
			$href = preg_match('~^https?://~i', $u)
				? $u
				: (str_starts_with($u, '/') ? $u : $destRoot . '/' . ltrim($u, '/'));
			$hasImg = ($d['img'] !== '');
			$imgUrl = '';
			if ($hasImg) {
				$imgUrl = preg_match('~^https?://~i', $d['img'])
					? $d['img']
					: (str_starts_with($d['img'], '/') ? $d['img'] : $destRoot . '/' . ltrim($d['img'], '/'));
			}
		?>
		<a class="hmb-dest-card<?php echo $hasImg ? ' has-img' : ''; ?>" href="<?php echo htmlspecialchars($href, ENT_QUOTES); ?>"<?php echo $hasImg ? ' style="background-image:url(\'' . htmlspecialchars($imgUrl, ENT_QUOTES) . '\')"' : ''; ?>>
			<?php if ($d['icon'] !== ''): ?>
			<span class="hmb-dest-icon"><?php echo $d['icon']; ?></span>
			<?php endif; ?>
			<span class="hmb-dest-label"><?php echo htmlspecialchars($d['label'], ENT_QUOTES); ?></span>
		</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>
<?php if ($this->countModules('conciertos')): ?>
<section class="hmb-row-conciertos">
	<div class="<?php echo $containerClass; ?>">
		<jdoc:include type="modules" name="conciertos" style="hmb_module" />
	</div>
</section>
<?php endif; ?>
<?php if ($this->countModules('noticias')): ?>
<section class="hmb-row-noticias">
	<div class="<?php echo $containerClass; ?>">
		<jdoc:include type="modules" name="noticias" style="hmb_module" />
	</div>
</section>
<?php endif; ?>
<?php if ($this->countModules('videos')): ?>
<section class="hmb-row-videos">
	<div class="<?php echo $containerClass; ?>">
		<jdoc:include type="modules" name="videos" style="hmb_module" />
	</div>
</section>
<?php endif; ?>
<?php if ($this->countModules('slider')): ?>
<section class="hmb-row-slider">
	<div class="<?php echo $containerClass; ?>">
		<jdoc:include type="modules" name="slider" />
	</div>
</section>
<?php endif; ?>
<?php if ($this->countModules('efemerides') || $this->countModules('frases') || $this->countModules('temas')): ?>
<section class="hmb-row-ef-frases">
	<div class="<?php echo $containerClass; ?> hmb-row-ef-frases-inner">
		<?php if ($this->countModules('efemerides')): ?>
		<div class="hmb-col-ef hmb-col-ef-left">
			<jdoc:include type="modules" name="efemerides" style="hmb_module" />
		</div>
		<?php endif; ?>
		<?php if ($this->countModules('frases') || $this->countModules('temas')): ?>
		<div class="hmb-col-ef hmb-col-ef-right">
			<?php if ($this->countModules('frases')): ?>
			<div class="hmb-subcol-frases">
				<jdoc:include type="modules" name="frases" style="hmb_module" />
			</div>
			<?php endif; ?>
			<?php if ($this->countModules('temas')): ?>
			<div class="hmb-subcol-temas">
				<jdoc:include type="modules" name="temas" style="hmb_module" />
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>
<?php if (!$isHome): ?>
<main class="hmb-main-wrap" id="content" role="main">
	<div class="<?php echo $containerClass; ?>">
		<?php if ($this->countModules('breadcrumbs')): ?>
		<div class="hmb-breadcrumbs">
			<jdoc:include type="modules" name="breadcrumbs" />
		</div>
		<?php endif; ?>
		<div class="hmb-layout <?php echo $mainClass; ?>">
			<?php if ($hasSidebarLeft): ?>
			<aside class="hmb-sidebar hmb-sidebar-left" role="complementary">
				<jdoc:include type="modules" name="sidebar-left" style="hmb_module" />
			</aside>
			<?php endif; ?>
			<div class="hmb-component">
				<div class="hmb-component-inner">
					<jdoc:include type="component" />
				</div>
			</div>
			<?php if ($hasSidebarRight): ?>
			<aside class="hmb-sidebar hmb-sidebar-right" role="complementary">
				<jdoc:include type="modules" name="sidebar-right" style="hmb_module" />
			</aside>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php endif; ?>
<footer class="hmb-footer" role="contentinfo">
	<div class="<?php echo $containerClass; ?>">
		<?php if ($this->countModules('footer')): ?>
			<jdoc:include type="modules" name="footer" style="hmb_module" />
		<?php else: ?>
			<div class="hmb-footer-default">
				<div class="hmb-footer-logo"><?php echo $app->get('sitename'); ?></div>
				<p class="hmb-footer-copy">&copy; <?php echo date('Y'); ?> <?php echo $app->get('sitename'); ?></p>
			</div>
		<?php endif; ?>
	</div>
</footer>
<?php $floatUser = Factory::getApplication()->getIdentity(); ?>
<div class="hmb-float-login" id="hmb-float-login">
	<button class="hmb-float-tab" id="hmb-float-tab" aria-expanded="false">
		<span class="hmb-float-icon">&#9760;</span>
		<span class="hmb-float-label"><?php echo $floatUser->id ? 'ZONA' : 'ACCESO'; ?></span>
	</button>
	<div class="hmb-float-panel" id="hmb-float-panel">
		<button class="hmb-float-close" id="hmb-float-close">&#10005;</button>
		<jdoc:include type="modules" name="login" style="none" />
	</div>
</div>
<jdoc:include type="modules" name="debug" style="none" />
<script>
(function(){
  var ftab=document.getElementById('hmb-float-tab');
  var fpan=document.getElementById('hmb-float-panel');
  if(ftab&&fpan){
    ftab.onclick=function(){fpan.classList.toggle('is-open');};
    var fc=document.getElementById('hmb-float-close');
    if(fc)fc.onclick=function(){fpan.classList.remove('is-open');};
    document.addEventListener('click',function(e){
      var w=document.getElementById('hmb-float-login');
      if(w&&!w.contains(e.target))fpan.classList.remove('is-open');
    });
  }
  (function(){
    var articles=document.querySelectorAll('.com-content-article,.com-content-article__body,.article-body,.item-page');
    articles.forEach(function(art){
      var root=art.classList.contains('com-content-article__body')?(art.parentElement||art):art;
      var tagBlock=root.querySelector('ul.tags.list-inline')||root.querySelector('ul.tags')||root.querySelector('.com-content-article__tags')||root.querySelector('div.tags')||root.querySelector('.article-tags')||root.querySelector('.tags-list');
      if(!tagBlock)return;
      var links=tagBlock.querySelectorAll('a.btn,a.tag-link,a');
      if(!links.length)return;
      if(root.querySelector('.hmb-tags-bottom'))return;
      var wrap=document.createElement('div');
      wrap.className='hmb-tags-bottom';
      var label=document.createElement('span');
      label.className='hmb-tags-bottom-label';
      label.innerHTML='&#9670; Tags:';
      wrap.appendChild(label);
      links.forEach(function(a){var clone=a.cloneNode(true);clone.className='';wrap.appendChild(clone);});
      var body=root.querySelector('.com-content-article__body')||root;
      body.appendChild(wrap);
    });
  })();
  var header=document.querySelector('.hmb-header');
  if(header)window.addEventListener('scroll',function(){header.style.boxShadow=window.scrollY>10?'0 4px 30px rgba(139,0,0,.25)':'none';},{passive:true});
  var fab=document.getElementById('hmb-fab');
  if(fab){
    window.addEventListener('scroll',function(){if(window.innerWidth>768)return;if(window.scrollY>80)fab.classList.add('visible');else fab.classList.remove('visible');},{passive:true});
    fab.addEventListener('click',function(){
      var overlay=document.querySelector('.hmb-menu-overlay');
      var wrap=document.querySelector('.hmb-menu-wrap');
      if(overlay&&wrap){overlay.classList.add('is-open');wrap.classList.add('is-open');document.body.style.overflow='hidden';}
      else window.scrollTo({top:0,behavior:'smooth'});
    });
  }
})();
</script>
<script>
(function(){
  if(!document.getElementById('ph-pc-item-box'))return;
  var imgs=[];
  var mainImg=document.querySelector('.phImageFull');
  if(mainImg)imgs.push({src:mainImg.dataset.image||mainImg.src,alt:mainImg.alt});
  document.querySelectorAll('.phImageAdditional').forEach(function(img){
    imgs.push({src:img.dataset.imageLarge||img.src,alt:img.alt});
  });
  if(imgs.length<1)return;
  var box=document.querySelector('#phImageBox');
  if(!box)return;
  var wrap=document.createElement('div');
  wrap.className='hmb-carousel-wrap';
  var slides=document.createElement('div');
  slides.className='hmb-carousel-slides';
  imgs.forEach(function(img,i){
    var slide=document.createElement('div');
    slide.className='hmb-carousel-slide';
    var im=document.createElement('img');
    im.src=img.src;im.alt=img.alt;im.loading=i===0?'eager':'lazy';
    slide.appendChild(im);slides.appendChild(slide);
  });
  wrap.appendChild(slides);
  var prev,next;
  if(imgs.length>1){
    prev=document.createElement('button');prev.className='hmb-carousel-btn hmb-carousel-prev';prev.innerHTML='&#8592;';prev.type='button';wrap.appendChild(prev);
    next=document.createElement('button');next.className='hmb-carousel-btn hmb-carousel-next';next.innerHTML='&#8594;';next.type='button';wrap.appendChild(next);
  }
  var dotsWrap=document.createElement('div');dotsWrap.className='hmb-carousel-dots';
  var dots=[];
  if(imgs.length>1){imgs.forEach(function(_,i){var dot=document.createElement('button');dot.className='hmb-carousel-dot'+(i===0?' active':'');dot.type='button';dots.push(dot);dotsWrap.appendChild(dot);});}
  var thumbsWrap=document.createElement('div');thumbsWrap.className='hmb-thumbs';
  var thumbEls=[];
  imgs.forEach(function(img,i){var th=document.createElement('img');th.src=img.src;th.alt=img.alt;th.className='hmb-thumb'+(i===0?' active':'');th.loading='lazy';thumbEls.push(th);thumbsWrap.appendChild(th);});
  box.innerHTML='';box.appendChild(wrap);
  if(imgs.length>1)box.appendChild(dotsWrap);
  box.appendChild(thumbsWrap);
  var current=0;
  function fixWidths(){
    var w=wrap.offsetWidth;
    if(!w) return;
    slides.querySelectorAll('.hmb-carousel-slide').forEach(function(s){s.style.minWidth=w+'px';s.style.width=w+'px';});
  }
  function goTo(n){
    fixWidths();
    current=(n+imgs.length)%imgs.length;
    slides.style.transform='translateX(-'+(current*wrap.offsetWidth)+'px)';
    dots.forEach(function(d,i){d.classList.toggle('active',i===current);});
    thumbEls.forEach(function(t,i){t.classList.toggle('active',i===current);});
  }
  fixWidths();
  window.addEventListener('resize',function(){fixWidths();goTo(current);});
  if(prev)prev.addEventListener('click',function(){goTo(current-1);});
  if(next)next.addEventListener('click',function(){goTo(current+1);});
  dots.forEach(function(d,i){d.addEventListener('click',function(){goTo(i);});});
  thumbEls.forEach(function(t,i){t.addEventListener('click',function(){goTo(i);});});
  var startX=0;
  wrap.addEventListener('touchstart',function(e){startX=e.touches[0].clientX;},{passive:true});
  wrap.addEventListener('touchend',function(e){var diff=startX-e.changedTouches[0].clientX;if(Math.abs(diff)>40)goTo(diff>0?current+1:current-1);},{passive:true});
})();
</script>



<script>
(function(){
  var observer = new MutationObserver(function(mutations){
    mutations.forEach(function(m){
      m.addedNodes.forEach(function(node){
        if(!node.id) return;
        if(node.id === 'phAddToCartPopup'){
          setupPopup(node);
        }
      });
    });
  });
  observer.observe(document.body, {childList:true, subtree:false});

  function setupPopup(popup){
    document.body.appendChild(popup);
    popup.classList.add('hmb-show');

    // Click outside to close
    popup.addEventListener('click', function(e){
      if(e.target === popup) close();
    });

    // All close/continue buttons
    popup.querySelectorAll('.btn-close, [data-bs-dismiss], .btn-primary.ph-btn').forEach(function(btn){
      btn.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        close();
      });
    });

    // Proceed to checkout
    popup.querySelectorAll('.btn-success.ph-btn').forEach(function(btn){
      btn.addEventListener('click', function(e){
        var href = btn.getAttribute('href');
        if(href){ close(); window.location.href = href; }
      });
    });

    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape') close();
    });

    function close(){ popup.classList.remove('hmb-show'); }
  }
})();
</script>

<script>
(function(){
  if(!document.getElementById('ph-pc-item-box')) return;
  var imgs=[];
  var mainImg=document.querySelector('.phImageFull');
  if(mainImg) imgs.push({src:mainImg.dataset.image||mainImg.src,alt:mainImg.alt});
  document.querySelectorAll('.phImageAdditional').forEach(function(img){
    imgs.push({src:img.dataset.imageLarge||img.src,alt:img.alt});
  });
  if(imgs.length<1) return;
  var box=document.querySelector('#phImageBox');
  if(!box) return;
  var wrap=document.createElement('div');
  wrap.className='hmb-carousel-wrap';
  var slides=document.createElement('div');
  slides.className='hmb-carousel-slides';
  imgs.forEach(function(img,i){
    var slide=document.createElement('div');
    slide.className='hmb-carousel-slide';
    var im=document.createElement('img');
    im.src=img.src;im.alt=img.alt;im.loading=i===0?'eager':'lazy';
    slide.appendChild(im);slides.appendChild(slide);
  });
  wrap.appendChild(slides);
  var prev,next;
  if(imgs.length>1){
    prev=document.createElement('button');prev.className='hmb-carousel-btn hmb-carousel-prev';prev.innerHTML='&#8592;';prev.type='button';wrap.appendChild(prev);
    next=document.createElement('button');next.className='hmb-carousel-btn hmb-carousel-next';next.innerHTML='&#8594;';next.type='button';wrap.appendChild(next);
  }
  var dotsWrap=document.createElement('div');dotsWrap.className='hmb-carousel-dots';
  var dots=[];
  if(imgs.length>1){imgs.forEach(function(_,i){var dot=document.createElement('button');dot.className='hmb-carousel-dot'+(i===0?' active':'');dot.type='button';dots.push(dot);dotsWrap.appendChild(dot);});}
  var thumbsWrap=document.createElement('div');thumbsWrap.className='hmb-thumbs';
  var thumbEls=[];
  imgs.forEach(function(img,i){var th=document.createElement('img');th.src=img.src;th.alt=img.alt;th.className='hmb-thumb'+(i===0?' active':'');th.loading='lazy';thumbEls.push(th);thumbsWrap.appendChild(th);});
  box.innerHTML='';box.appendChild(wrap);
  if(imgs.length>1)box.appendChild(dotsWrap);
  box.appendChild(thumbsWrap);
  var current=0;
  function fixWidths(){
    var w=wrap.offsetWidth;
    if(!w) return;
    slides.querySelectorAll('.hmb-carousel-slide').forEach(function(s){s.style.width=w+'px';s.style.minWidth=w+'px';});
  }
  function goTo(n){
    fixWidths();
    current=(n+imgs.length)%imgs.length;
    slides.style.transform='translateX(-'+(current*wrap.offsetWidth)+'px)';
    dots.forEach(function(d,i){d.classList.toggle('active',i===current);});
    thumbEls.forEach(function(t,i){t.classList.toggle('active',i===current);});
  }
  fixWidths();
  window.addEventListener('resize',function(){fixWidths();goTo(current);});
  if(prev)prev.addEventListener('click',function(){goTo(current-1);});
  if(next)next.addEventListener('click',function(){goTo(current+1);});
  dots.forEach(function(d,i){d.addEventListener('click',function(){goTo(i);});});
  thumbEls.forEach(function(t,i){t.addEventListener('click',function(){goTo(i);});});
  var startX=0;
  wrap.addEventListener('touchstart',function(e){startX=e.touches[0].clientX;},{passive:true});
  wrap.addEventListener('touchend',function(e){var diff=startX-e.changedTouches[0].clientX;if(Math.abs(diff)>40)goTo(diff>0?current+1:current-1);},{passive:true});
})();
</script>

<jdoc:include type="scripts" />
</body>
</html>
