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
	<!-- Google tag (gtag.js) -->
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
	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Metal+Mania&family=Cinzel+Decorative:wght@700;900&family=Oswald:wght@300;400;600;700&family=Rajdhani:wght@300;400;600;700&display=swap" rel="stylesheet">
	<!-- Dynamic color scheme -->
	<style>
		/* ── Tokens ── */
		:root { <?php echo $cssVars; ?> }
		/* ── Sticky wrapper (header + nav) ── */
		.hmb-sticky-wrap{position:sticky;top:0;z-index:1000;background:#0a0000;}

		/* ── FAB menú móvil ── */
		.hmb-fab{display:none;}
		@media(max-width:768px){
			.hmb-fab{
				display:flex;position:fixed;bottom:20px;right:16px;z-index:8500;
				background:linear-gradient(135deg,#CC2200,#8B0000);
				border:none;border-radius:50px;color:#fff;
				font-family:'Oswald',sans-serif;font-size:11px;letter-spacing:2px;
				padding:11px 20px;cursor:pointer;gap:8px;align-items:center;
				box-shadow:0 4px 24px rgba(139,0,0,.7);
				opacity:0;transform:translateY(12px);
				transition:opacity .3s,transform .3s;pointer-events:none;
			}
			.hmb-fab.visible{opacity:1;transform:translateY(0);pointer-events:auto;}
			.hmb-fab-icon{font-size:16px;line-height:1;}
		}

		/* ── Layout portada ── */
		body.home .hmb-main-wrap{display:none!important;}

		/* ── Header ── */
		.hmb-header-top{border-bottom:1px solid #111;}
		.hmb-header-top-inner{display:flex;align-items:center;justify-content:space-between;gap:2rem;min-height:90px;padding:.75rem 0;}
		.hmb-header-banner{flex:1;display:flex;align-items:center;justify-content:center;overflow:hidden;padding:0 1rem;}
		.hmb-header-banner img{max-height:80px;width:auto;max-width:100%;display:block;object-fit:contain;}
		<!-- CSS saludo usuario -->
		.hmb-header-user{display:flex;flex-direction:column;align-items:flex-end;justify-content:center;flex-shrink:0;gap:4px;}
		.hmb-header-user-hello{font-family:'Oswald',sans-serif;font-size:13px;letter-spacing:2px;color:#555;text-transform:uppercase;}
		.hmb-header-user-name{font-family:'Metal Mania',cursive;font-size:24px;color:#CC2200;letter-spacing:1px;line-height:1;text-shadow:0 0 12px rgba(204,34,0,.3);}
		.hmb-header-user-links{display:flex;gap:10px;}
		.hmb-header-user-links a{font-family:'Oswald',sans-serif;font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#444;text-decoration:none;transition:color .2s;}
		.hmb-header-user-links a:hover{color:#CC2200;}
		@media(max-width:768px){.hmb-header-user{display:none!important;}}

		/* ── Menú horizontal ── */
		.hmb-header-nav .hmb-nav-list,.hmb-header-nav .hmb-menu-wrap ul,.hmb-header-nav .hmb-menu-wrap .nav{list-style:none!important;display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;align-items:stretch!important;margin:0!important;padding:0!important;width:100%!important;}
		.hmb-header-nav .hmb-nav-list>li,.hmb-header-nav .hmb-menu-wrap ul li{position:relative!important;display:flex!important;align-items:stretch!important;flex-shrink:0!important;}
		.hmb-header-nav .hmb-nav-list>li>a,.hmb-header-nav .hmb-menu-wrap ul li>a{display:flex!important;align-items:center!important;font-family:'Oswald',sans-serif!important;font-size:16px!important;font-weight:600!important;letter-spacing:2px!important;text-transform:uppercase!important;color:var(--text-dim)!important;text-decoration:none!important;padding:0 20px!important;height:52px!important;white-space:nowrap!important;position:relative!important;transition:color .3s,background .3s!important;border-bottom:2px solid transparent!important;}
		.hmb-header-nav .hmb-nav-list>li>a:hover,.hmb-header-nav .hmb-nav-list>li.active>a,.hmb-header-nav .hmb-menu-wrap ul li:hover>a,.hmb-header-nav .hmb-menu-wrap ul li.active>a{color:#fff!important;background:rgba(139,0,0,.15)!important;border-bottom-color:var(--fire)!important;}
		.hmb-nav-dropdown,.hmb-header-nav .hmb-menu-wrap ul ul{display:none!important;position:absolute!important;top:100%!important;left:0!important;min-width:200px!important;background:var(--steel)!important;border:1px solid #222!important;border-top:2px solid var(--fire)!important;flex-direction:column!important;z-index:500!important;padding:.4rem 0!important;}
		.hmb-header-nav .hmb-nav-list>li:hover>.hmb-nav-dropdown,.hmb-header-nav .hmb-menu-wrap ul li:hover>ul{display:flex!important;}
		.hmb-nav-dropdown li,.hmb-header-nav .hmb-menu-wrap ul ul li{display:block!important;width:100%!important;}
		.hmb-nav-dropdown li a,.hmb-header-nav .hmb-menu-wrap ul ul li a{display:block!important;padding:9px 20px!important;font-family:'Oswald',sans-serif!important;font-size:14px!important;letter-spacing:1px!important;text-transform:uppercase!important;color:var(--text-dim)!important;border-bottom:1px solid #1a1a1a!important;transition:color .2s,background .2s,padding-left .2s!important;}
		.hmb-nav-dropdown li a:hover,.hmb-header-nav .hmb-menu-wrap ul ul li a:hover{color:var(--ember)!important;background:rgba(139,0,0,.1)!important;padding-left:26px!important;}

		/* ── Módulo HMB Menú ── */
		#hmb-menu{overflow:visible!important;position:static!important;}
		.hmb-header,.hmb-header-nav,.hmb-header-top,.hmb-header-top-inner{overflow:visible!important;}
		[id^="hmb-menu-"]{display:block;width:100%;}
		#hmb-menu{display:flex;justify-content:center;}
		/* Wrapper scroll separado del UL que tiene dropdowns */
		[id^="hmb-menu-"] .hmb-scroll{overflow-x:auto!important;overflow-y:visible!important;-webkit-overflow-scrolling:touch!important;scrollbar-width:none!important;}
		[id^="hmb-menu-"] .hmb-scroll::-webkit-scrollbar{display:none!important;}
		[id^="hmb-menu-"] .hmb-ml{display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;list-style:none!important;margin:0!important;padding:0!important;overflow:visible!important;width:max-content!important;min-width:100%!important;}
		[id^="hmb-menu-"] .hmb-ml>li{flex-shrink:0!important;position:relative!important;display:block!important;}
		[id^="hmb-menu-"] .hmb-ml>li>a{display:flex!important;align-items:center!important;gap:4px!important;padding:0 16px!important;height:46px!important;color:#999!important;font-family:'Oswald',sans-serif!important;font-size:16px!important;font-weight:600!important;letter-spacing:2px!important;text-transform:uppercase!important;text-decoration:none!important;white-space:nowrap!important;border-bottom:2px solid transparent!important;transition:color .2s,border-color .2s!important;}
		[id^="hmb-menu-"] .hmb-ml>li>a:hover,[id^="hmb-menu-"] .hmb-ml>li.active>a{color:#fff!important;border-bottom-color:#CC2200!important;}
		/* Submenú desktop: hover CSS puro */
		[id^="hmb-menu-"] .hmb-sub{display:none!important;position:absolute!important;top:100%!important;left:0!important;min-width:220px!important;background:#0a0000!important;border:1px solid #2a0000!important;border-top:2px solid #CC2200!important;list-style:none!important;margin:0!important;padding:4px 0!important;z-index:9999!important;box-shadow:0 8px 30px rgba(0,0,0,.9)!important;}
		[id^="hmb-menu-"] .hmb-ml>li:hover>.hmb-sub{display:block!important;}
		[id^="hmb-menu-"] .hmb-sub li{display:block!important;}
		[id^="hmb-menu-"] .hmb-sub li a{display:block!important;padding:11px 20px!important;color:#888!important;font-family:'Oswald',sans-serif!important;font-size:14px!important;letter-spacing:1px!important;text-transform:uppercase!important;text-decoration:none!important;border-bottom:1px solid #1a0000!important;white-space:nowrap!important;}
		[id^="hmb-menu-"] .hmb-sub li a:hover{color:#FF4500!important;background:rgba(139,0,0,.15)!important;}
		@media(max-width:768px){
		[id^="hmb-menu-"] .hmb-ml>li>a{padding:0 10px!important;font-size:11px!important;letter-spacing:1px!important;}
		[id^="hmb-menu-"] .hmb-ml>li:hover>.hmb-sub{display:none!important;}
		}
		/* ── Tags de artículos — ocultar posición original ── */
		ul.tags.list-inline,ul.tags,.tags.list-inline,div.tags,
		.article-tags,.com-content-article__tags,.tags-list,
		.article__tags,.com-content-article .tags{display:none!important;}
		.hmb-tags-bottom{
			display:flex!important;flex-wrap:wrap!important;gap:8px!important;
			margin-top:2rem!important;padding-top:1.5rem!important;
			border-top:1px solid #1a0000!important;
		}
		.hmb-tags-bottom a{
			display:inline-flex!important;align-items:center!important;
			padding:4px 12px!important;
			font-family:'Oswald',sans-serif!important;
			font-size:11px!important;font-weight:600!important;
			letter-spacing:1.5px!important;text-transform:uppercase!important;
			text-decoration:none!important;
			color:#888!important;
			background:linear-gradient(145deg,#1a1a1a,#0d0d0d)!important;
			border:1px solid #2a2a2a!important;
			border-top-color:#333!important;border-bottom-color:#000!important;
			border-radius:2px!important;
			transition:color .2s,border-color .2s,transform .2s!important;
		}
		.hmb-tags-bottom a:hover{
			color:#fff!important;border-color:#CC2200!important;
			transform:translateY(-2px)!important;
		}
		.hmb-tags-bottom-label{
			font-family:'Oswald',sans-serif!important;
			font-size:11px!important;letter-spacing:2px!important;
			color:#555!important;text-transform:uppercase!important;
			display:flex!important;align-items:center!important;
			white-space:nowrap!important;
		}
		/* Secciones portada */
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

		/* ── Module titles ── */
		.hmb-row-noticias .hmb-module-title,.hmb-row-videos .hmb-module-title,.hmb-row-conciertos .hmb-module-title{font-family:'Metal Mania',cursive;font-size:26px;color:#efefef;padding:0 0 .75rem;margin-bottom:1.5rem;border-bottom:2px solid var(--blood);letter-spacing:2px;}
		.hmb-row-noticias .hmb-module-content,.hmb-row-videos .hmb-module-content{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5px;}
		.hmb-subcol-temas .hmb-module-title,.hmb-col-ef .hmb-module-title{font-family:'Metal Mania',cursive;font-size:20px;color:#efefef;padding:0 0 .6rem;margin-bottom:1rem;border-bottom:1px solid var(--blood);}
		.hmb-col-ef .hmb-module-content{padding:0;font-size:15px;line-height:1.8;}
		.hmb-subcol-temas iframe,.hmb-subcol-temas audio{width:100%!important;display:block!important;border:none!important;}

		/* ── Ocultar elementos solo-móvil en desktop ── */
		.hmb-menu-close-btn{display:none!important;}
		.hmb-menu-overlay{display:none;}
		/* ── Submenús cerrados por defecto ── */
		.hmb-header-nav .hmb-menu-wrap ul li ul{display:none;}
		.hmb-header-nav .hmb-menu-wrap ul li:hover>ul{display:flex;}
		/* ── Login flotante ── */
		.hmb-float-login{position:fixed;right:0;top:50%;transform:translateY(-50%);z-index:9000;display:flex;}
		.hmb-float-tab{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;background:linear-gradient(180deg,var(--fire),var(--blood));border:none;border-radius:6px 0 0 6px;padding:14px 8px;cursor:pointer;color:#fff;box-shadow:-4px 0 20px rgba(139,0,0,.4);transition:all .3s;min-height:100px;}
		.hmb-float-icon{font-size:20px;writing-mode:horizontal-tb;}
		.hmb-float-label{font-family:'Oswald',sans-serif;font-size:10px;letter-spacing:3px;font-weight:700;writing-mode:vertical-lr;}
		.hmb-float-panel{position:absolute;right:100%;top:50%;width:320px;background:var(--steel);border:1px solid #222;border-right:none;border-radius:8px 0 0 8px;box-shadow:-8px 0 40px rgba(0,0,0,.8);overflow:hidden;opacity:0;pointer-events:none;transform:translateY(-50%) translateX(20px);transition:opacity .35s,transform .35s;max-height:90vh;overflow-y:auto;}
		.hmb-float-panel.is-open{opacity:1;pointer-events:auto;transform:translateY(-50%) translateX(0);}
		.hmb-float-close{position:absolute;top:8px;left:10px;background:none;border:none;color:var(--text-dim);font-size:14px;cursor:pointer;z-index:10;padding:4px 8px;}

		/* ── Responsive ── */
		@media(max-width:1024px){
			.hmb-row-noticias .hmb-module-content,.hmb-row-videos .hmb-module-content{grid-template-columns:repeat(2,1fr);}
		}
		@media(max-width:768px){
			.hmb-header-top{display:none!important;}
			
			/* ── SLIDER desktop image scaled ── */
			.hmb-slider__img{max-height:none!important;height:auto!important;width:100%!important;}
			.hmb-row-ef-frases-inner{grid-template-columns:1fr!important;}
			.hmb-row-noticias .hmb-module-content,.hmb-row-videos .hmb-module-content{grid-template-columns:1fr!important;}
			/* ── BANNER ARTÍCULO ── */
			.hmb-art-banner{width:calc(100% + 4rem)!important;margin-left:-2rem!important;max-width:none!important;}
			.hmb-art-banner img{width:100%!important;height:160px!important;object-fit:cover!important;display:block!important;}
			.hmb-art-presentacion{grid-template-columns:1fr!important;}
			.hmb-art-foto img{width:100%!important;height:200px!important;}
			.hmb-art-foto-izq{border-right:none!important;border-bottom:2px solid var(--blood)!important;}
			.hmb-art-foto-der{border-left:none!important;border-top:2px solid var(--blood)!important;}
			/* ── HAMBURGUESA ── */
			.hmb-menu-toggle{display:flex!important;flex-direction:column!important;gap:5px!important;background:none!important;border:1px solid #8B0000!important;cursor:pointer!important;padding:8px!important;border-radius:3px!important;}
			.hmb-menu-toggle span{display:block!important;width:22px!important;height:2px!important;background:#CC2200!important;}
			/* ── OVERLAY ── */
			.hmb-menu-overlay{display:none!important;position:fixed!important;top:0!important;left:0!important;width:100%!important;height:100%!important;background:rgba(0,0,0,.8)!important;z-index:9998!important;}
			.hmb-menu-overlay.is-open{display:block!important;}
			/* ── PANEL: oculto por defecto, visible con is-open ── */
			.hmb-menu-wrap{display:none!important;position:fixed!important;top:0!important;left:0!important;width:80vw!important;max-width:300px!important;height:100vh!important;overflow-y:auto!important;overflow-x:hidden!important;background:#060000!important;border-right:2px solid #CC2200!important;z-index:9999!important;padding-top:56px!important;box-shadow:6px 0 30px rgba(0,0,0,.9)!important;}
			.hmb-menu-wrap.is-open{display:block!important;}
			/* ── BOTÓN CERRAR ── */
			.hmb-menu-close-btn{display:block!important;position:fixed!important;top:0!important;left:0!important;width:80vw!important;max-width:300px!important;background:#0d0000!important;border:none!important;border-bottom:1px solid #8B0000!important;color:#CC2200!important;font-size:18px!important;cursor:pointer!important;padding:16px 20px!important;text-align:right!important;z-index:10000!important;box-sizing:border-box!important;}
			/* ── ITEMS DEL MENÚ ── */
			.hmb-header-nav .hmb-menu-wrap ul,.hmb-header-nav .hmb-nav-list{display:block!important;width:100%!important;}
			.hmb-header-nav .hmb-menu-wrap ul li{display:block!important;width:100%!important;}
			.hmb-header-nav .hmb-menu-wrap ul li:empty{display:none!important;}
			.hmb-header-nav .hmb-menu-wrap ul li>a{display:flex!important;justify-content:space-between!important;align-items:center!important;font-size:14px!important;padding:14px 20px!important;height:auto!important;letter-spacing:2px!important;border-bottom:1px solid #1a0000!important;color:#C8C8C8!important;border-left:3px solid transparent!important;white-space:nowrap!important;}
			.hmb-header-nav .hmb-menu-wrap ul li>a:hover,.hmb-header-nav .hmb-menu-wrap ul li.active>a{color:#FF4500!important;background:rgba(139,0,0,.2)!important;border-left-color:#CC2200!important;}
			.hmb-header-nav .hmb-menu-wrap ul li.has-children>a::after{content:"›"!important;font-size:20px!important;opacity:.7!important;transition:transform .2s!important;flex-shrink:0!important;}
			.hmb-header-nav .hmb-menu-wrap ul li:not(.has-children)>a::after{display:none!important;}
			.hmb-header-nav .hmb-menu-wrap ul li.has-children.is-open>a::after{transform:rotate(90deg)!important;}
			/* ── SUBMENÚS ── */
			.hmb-header-nav .hmb-menu-wrap ul ul{display:none!important;position:static!important;width:100%!important;background:rgba(15,0,0,.9)!important;border:none!important;box-shadow:none!important;padding:0!important;flex-direction:column!important;}
			.hmb-header-nav .hmb-menu-wrap ul li:hover>ul{display:none!important;}
			.hmb-header-nav .hmb-menu-wrap ul li.is-open>ul{display:block!important;}
			.hmb-header-nav .hmb-menu-wrap ul ul li a{display:block!important;padding:11px 20px 11px 36px!important;font-size:12px!important;letter-spacing:1px!important;color:#888!important;border-bottom:1px solid #0d0000!important;height:auto!important;}
		}
		@media(max-width:480px){
			.hmb-header-top-inner{flex-wrap:wrap!important;min-height:auto!important;padding:.5rem 0!important;}
			.hmb-header-banner{flex-basis:100%!important;order:3!important;}
		}
		@media(max-width:480px){
			.hmb-header-top-inner{flex-wrap:wrap!important;min-height:auto!important;padding:.5rem 0!important;}
			.hmb-header-banner{flex-basis:100%!important;order:3!important;}
		}
		@media(max-width:480px){
			.hmb-header-top-inner{flex-wrap:wrap;min-height:auto;padding:.5rem 0;}
			.hmb-header-banner{flex-basis:100%;order:3;}
		}

		/* ═══ PANEL MENÚ MÓVIL — Joomla standard output ═══ */
		.hmb-panel-menu{padding:0;}
		.hmb-panel-menu ul,.hmb-panel-menu .nav{list-style:none!important;padding:0!important;margin:0!important;display:block!important;}
		.hmb-panel-menu ul li,.hmb-panel-menu .nav-item{display:block!important;width:100%!important;border-bottom:1px solid #1a0000!important;}
		.hmb-panel-menu ul li a,.hmb-panel-menu .nav-link{
			display:block!important;padding:14px 20px!important;
			color:#C8C8C8!important;font-family:'Oswald',sans-serif!important;
			font-size:13px!important;letter-spacing:2px!important;
			text-transform:uppercase!important;text-decoration:none!important;
			border-left:3px solid transparent!important;
		}
		.hmb-panel-menu ul li a:hover,.hmb-panel-menu ul li.active>a,.hmb-panel-menu .nav-link:hover,.hmb-panel-menu .active>.nav-link{
			color:#FF4500!important;background:rgba(139,0,0,.15)!important;border-left-color:#CC2200!important;
		}
		.hmb-panel-menu ul ul,.hmb-panel-menu .dropdown-menu{
			display:none!important;position:static!important;
			background:rgba(8,0,0,.8)!important;border:none!important;box-shadow:none!important;
			padding:0!important;float:none!important;width:100%!important;
		}
		.hmb-panel-menu ul li.is-open>ul,.hmb-panel-menu .nav-item.is-open>.dropdown-menu{display:block!important;}
		.hmb-panel-menu ul ul li a,.hmb-panel-menu .dropdown-item{
			padding:11px 20px 11px 36px!important;font-size:12px!important;
			color:#777!important;letter-spacing:1px!important;
		}
		.hmb-panel-menu ul ul li a:hover,.hmb-panel-menu .dropdown-item:hover{color:#FF4500!important;background:rgba(139,0,0,.1)!important;}
		/* ═══ ARTÍCULO PRESENTACIÓN ═══ */
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
			/* Una sola columna: banner / texto / fotos al final */
			.hmb-art-presentacion{grid-template-columns:1fr!important;grid-template-rows:auto!important;}
			/* Grid: texto arriba ancho completo, fotos abajo lado a lado */
			.hmb-art-presentacion{display:grid!important;grid-template-columns:1fr 1fr!important;grid-template-rows:auto auto!important;}
			.hmb-art-texto{grid-column:1 / -1!important;grid-row:1!important;padding:1.2rem 1rem!important;}
			.hmb-art-foto-izq{grid-column:1!important;grid-row:2!important;border-right:1px solid var(--blood)!important;border-top:2px solid var(--blood)!important;}
			.hmb-art-foto-der{grid-column:2!important;grid-row:2!important;border-left:none!important;border-top:2px solid var(--blood)!important;}
			.hmb-art-foto img{width:100%!important;height:auto!important;object-fit:contain!important;object-position:top center!important;display:block!important;}
			.hmb-art-texto h2{font-size:20px!important;margin-bottom:1rem!important;}
			.hmb-art-texto p{font-size:14px!important;line-height:1.7!important;}
			.hmb-art-texto p:first-of-type::first-letter{font-size:40px!important;}
		}
	</style>
</head>
<?php
$menu   = $app->getMenu();
$isHome = ($menu->getActive() == $menu->getDefault());
$bodyClasses = 'hmb-body ' . $colorScheme . '-scheme';
if ($isHome) $bodyClasses .= ' home';
?>
<body class="<?php echo $bodyClasses; ?>">

<!-- Noise texture overlay -->
<div class="hmb-noise" aria-hidden="true"></div>

<?php // TOP BAR ?>
<?php if ($showTopBar): ?>
<div class="hmb-topbar" role="marquee">
	<?php if ($this->countModules('topbar')): ?>
		<jdoc:include type="modules" name="topbar" />
	<?php else: ?>
		<span><?php echo htmlspecialchars($topBarText); ?></span>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php // HEADER + NAV — sticky wrapper ?>
<div class="hmb-sticky-wrap">

<?php // HEADER ?>
<header class="hmb-header" role="banner">

	<!-- ROW 1: Logo | Banner -->
	<div class="hmb-header-top">
		<div class="<?php echo $containerClass; ?> hmb-header-top-inner">

			<!-- Logo izquierda -->
			<div class="hmb-logo">
				<?php if ($this->countModules('header')): ?>
					<jdoc:include type="modules" name="header" />
				<?php else: ?>
					<a href="<?php echo Uri::root(); ?>" class="hmb-logo-link">
						<span class="hmb-logo-text"><?php echo $app->get('sitename'); ?></span>
					</a>
				<?php endif; ?>
			</div>

			<!-- Banner centro/derecha -->
			<div class="hmb-header-banner">
				<?php if ($this->countModules('banner')): ?>
					<jdoc:include type="modules" name="banner" />
				<?php endif; ?>
			</div>

			<!-- Saludo usuario (derecha) -->
			<?php $headerUser = Factory::getApplication()->getIdentity(); ?>
			<div class="hmb-header-user">
				<?php if ($headerUser->id): ?>
					<span class="hmb-header-user-hello">Bienvenido de nuevo</span>
					<span class="hmb-header-user-name"><?php echo htmlspecialchars($headerUser->username, ENT_QUOTES, 'UTF-8'); ?></span>
					<div class="hmb-header-user-links">
						<a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_users&view=profile'); ?>">&#9670; Mi perfil</a>
						<a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_users&task=user.logout&' . \Joomla\CMS\Session\Session::getFormToken() . '=1'); ?>">&#9632; Salir</a>
					</div>
				<?php else: ?>
					<span class="hmb-header-user-hello">Área de miembros</span>
					<div class="hmb-header-user-links">
						<a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_users&view=login'); ?>">&#9658; Acceder</a>
						<a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_users&view=registration'); ?>">&#9670; Registrarse</a>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>

	</header>

	<!-- MENÚ MÓVIL — simple barra horizontal scrollable -->
	<div class="hmb-header-nav">
		<div id="hmb-menu" style="width:100%;overflow:visible;position:relative;">
			<jdoc:include type="modules" name="menu" style="none" />
		</div>
	</div>

</div><!-- /hmb-sticky-wrap -->

<!-- FAB menú móvil (aparece al hacer scroll) -->
<button class="hmb-fab" id="hmb-fab" aria-label="Abrir menú">
	<span class="hmb-fab-icon">☰</span> MENÚ
</button>

<?php // PRÓXIMOS CONCIERTOS ?>
<?php if ($this->countModules('conciertos')): ?>
<section class="hmb-row-conciertos">
	<div class="<?php echo $containerClass; ?>">
		<jdoc:include type="modules" name="conciertos" style="hmb_module" />
	</div>
</section>
<?php endif; ?>

<?php // NOTICIAS ?>
<?php if ($this->countModules('noticias')): ?>
<section class="hmb-row-noticias">
	<div class="<?php echo $containerClass; ?>">
		<jdoc:include type="modules" name="noticias" style="hmb_module" />
	</div>
</section>
<?php endif; ?>

<?php // VIDEOS ?>
<?php if ($this->countModules('videos')): ?>
<section class="hmb-row-videos">
	<div class="<?php echo $containerClass; ?>">
		<jdoc:include type="modules" name="videos" style="hmb_module" />
	</div>
</section>
<?php endif; ?>

<?php // SLIDER ?>
<?php if ($this->countModules('slider')): ?>
<section class="hmb-row-slider">
	<div class="<?php echo $containerClass; ?>">
		<jdoc:include type="modules" name="slider" />
	</div>
</section>
<?php endif; ?>

<?php // EFEMERIDES + FRASES + TEMAS ?>
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

<?php // MAIN CONTENT — no se renderiza en portada ?>
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

<?php // LOGIN FLOTANTE ?>
<?php $floatUser = Factory::getApplication()->getIdentity(); ?>
<?php if (!$floatUser->id): ?>
<div class="hmb-float-login" id="hmb-float-login">
	<button class="hmb-float-tab" id="hmb-float-tab" aria-expanded="false">
		<span class="hmb-float-icon">&#9760;</span>
		<span class="hmb-float-label">ACCESO</span>
	</button>
	<div class="hmb-float-panel" id="hmb-float-panel">
		<button class="hmb-float-close" id="hmb-float-close">&#10005;</button>
		<?php if ($this->countModules('login')): ?>
			<jdoc:include type="modules" name="login" />
		<?php else: ?>
			<div class="hmb-float-fallback">
				<a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_users&view=login'); ?>" class="hmb-float-btn">INICIAR SESIÓN &#9658;</a>
				<a href="<?php echo \Joomla\CMS\Router\Route::_('index.php?option=com_users&view=registration'); ?>" class="hmb-float-register">&#9670; Registrarse</a>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

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
  /* Mover tags al final del artículo en fila horizontal */
  (function(){
    /* Buscar el contenedor raíz del artículo (padre de __body y __tags) */
    var articles = document.querySelectorAll(
      '.com-content-article, .com-content-article__body, .article-body, .item-page'
    );
    articles.forEach(function(art){
      /* Si es __body, subir al padre para buscar tags hermanos */
      var root = art.classList.contains('com-content-article__body')
                 ? (art.parentElement || art)
                 : art;

      /* Buscar el bloque de tags con múltiples selectores */
      var tagBlock = root.querySelector('ul.tags.list-inline')
                  || root.querySelector('ul.tags')
                  || root.querySelector('.com-content-article__tags')
                  || root.querySelector('div.tags')
                  || root.querySelector('.article-tags')
                  || root.querySelector('.tags-list');
      if(!tagBlock) return;

      /* Buscar los enlaces (a.btn, a.tag-link, o cualquier a) */
      var links = tagBlock.querySelectorAll('a.btn, a.tag-link, a');
      if(!links.length) return;

      /* Evitar duplicados */
      if(root.querySelector('.hmb-tags-bottom')) return;

      /* Crear contenedor horizontal al final del artículo */
      var wrap = document.createElement('div');
      wrap.className = 'hmb-tags-bottom';
      var label = document.createElement('span');
      label.className = 'hmb-tags-bottom-label';
      label.innerHTML = '&#9670; Tags:';
      wrap.appendChild(label);
      links.forEach(function(a){
        var clone = a.cloneNode(true);
        /* Limpiar clases de Bootstrap que puedan dar estilo no deseado */
        clone.className = '';
        wrap.appendChild(clone);
      });
      /* Añadir al body del artículo si existe, si no al root */
      var body = root.querySelector('.com-content-article__body') || root;
      body.appendChild(wrap);
    });
  })();

  var header=document.querySelector('.hmb-header');
  if(header)window.addEventListener('scroll',function(){
    header.style.boxShadow=window.scrollY>10?'0 4px 30px rgba(139,0,0,.25)':'none';
  },{passive:true});

  /* ── FAB menú móvil ── */
  var fab = document.getElementById('hmb-fab');
  if(fab){
    /* Mostrar/ocultar según scroll */
    window.addEventListener('scroll', function(){
      if(window.innerWidth > 768) return;
      if(window.scrollY > 80) fab.classList.add('visible');
      else fab.classList.remove('visible');
    }, {passive:true});

    /* Click: abrir el panel de menú móvil si existe,
       o hacer scroll al top para que el sticky nav quede visible */
    fab.addEventListener('click', function(){
      /* Intentar abrir el panel lateral si el módulo lo tiene */
      var overlay = document.querySelector('.hmb-menu-overlay');
      var wrap    = document.querySelector('.hmb-menu-wrap');
      if(overlay && wrap){
        overlay.classList.add('is-open');
        wrap.classList.add('is-open');
        document.body.style.overflow = 'hidden';
      } else {
        /* Fallback: scroll suave al top */
        window.scrollTo({top:0, behavior:'smooth'});
      }
    });
  }
})();
</script>
<jdoc:include type="scripts" />
</body>
</html>