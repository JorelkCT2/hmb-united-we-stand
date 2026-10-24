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
$showDestTitle  = $params->get('showDestacadosTitle', 1);
$destTitle      = trim((string) $params->get('destacadosTitle', ''));
$destItems      = [];
for ($i = 1; $i <= 4; $i++) {
    $l  = trim((string) $params->get("destacado{$i}_label", ''));
    $u  = trim((string) $params->get("destacado{$i}_url", ''));
    $ic = trim((string) $params->get("destacado{$i}_icon", ''));
    $im = trim((string) $params->get("destacado{$i}_img", ''));
    $tp = trim((string) $params->get("destacado{$i}_type", 'icon'));
    // Validez según el tipo: imagen exige img; icono exige texto. La URL siempre.
    $ok = ($u !== '');
    if ($tp === 'image')          { $ok = $ok && ($im !== ''); }
    elseif ($tp === 'imagetext')  { $ok = $ok && ($im !== '') && ($l !== ''); }
    else                          { $ok = $ok && ($l !== ''); }
    if ($ok) {
        $destItems[] = ['label' => $l, 'url' => $u, 'icon' => $ic, 'img' => $im, 'type' => $tp];
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
$wa->registerAndUseStyle('hmb.base', 'templates/hmb/css/hmb-base.css', ['version' => (string) (@filemtime(__DIR__ . '/css/hmb-base.css') ?: 1)]);
$wa->registerAndUseStyle('hmb.modules', 'templates/hmb/css/hmb-modules.css', ['version' => (string) (@filemtime(__DIR__ . '/css/hmb-modules.css') ?: 1)]);
$wa->registerAndUseStyle('hmb.layout', 'templates/hmb/css/hmb-layout.css', ['version' => (string) (@filemtime(__DIR__ . '/css/hmb-layout.css') ?: 1)]);
$wa->registerAndUseScript('hmb.template', 'templates/hmb/js/template.js', ['version' => '3.0.0'], ['defer' => true]);

// Phoca Cart — cargar CSS solo en páginas de la tienda
if (in_array($app->input->get('option', ''), ['com_phocacart', 'com_hmbshop'], true)) {
    $wa->registerAndUseStyle('hmb.phocacart', 'templates/hmb/css/hmb-phocacart.css', ['version' => (string) (@filemtime(__DIR__ . '/css/hmb-phocacart.css') ?: 1)]);
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
	<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NR9JTB73');</script>
<!-- End Google Tag Manager -->
<meta charset="utf-8">
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
	<link href="https://fonts.googleapis.com/css2?family=Metal+Mania&family=Cinzel+Decorative:wght@400;700;900&family=Cinzel:wght@400;600;900&family=Oswald:wght@300;400;600;700&family=Rajdhani:wght@300;400;600;700&display=swap" rel="stylesheet">
	<style>:root { <?php echo $cssVars; ?> }</style>
	<link rel="stylesheet" href="<?php echo Uri::root(true); ?>/templates/hmb/css/hmb-components.css?v=<?php echo (int) @filemtime(__DIR__ . '/css/hmb-components.css'); ?>">
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
	/* Tarjetas tipo imagen / banner (recomendado 400×100) */
	.hmb-dest-card.is-banner{padding:0;min-height:0;background:#0a0000;}
	.hmb-dest-banner{display:block;width:100%;height:auto;}
	/* Tarjetas imagen + texto (imagen de fondo con el texto encima, sin icono) */
	.hmb-dest-card.is-imgtext{background:#0a0000 center/cover no-repeat;min-height:150px;justify-content:flex-end;padding:1.2rem;}
	.hmb-dest-card.is-imgtext::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.25) 0%,rgba(0,0,0,.45) 45%,rgba(0,0,0,.85) 100%);z-index:1;transition:background .3s;}
	.hmb-dest-card.is-imgtext:hover::after{background:linear-gradient(180deg,rgba(0,0,0,.15) 0%,rgba(0,0,0,.4) 45%,rgba(0,0,0,.8) 100%);}
	.hmb-dest-card.is-imgtext .hmb-dest-label{color:#fff;font-size:15px;text-shadow:0 2px 10px rgba(0,0,0,.9);}
	@media(max-width:768px){
		.hmb-destacados{padding:1.6rem 0 1.3rem;}
		.hmb-destacados-grid{grid-template-columns:repeat(2,1fr);gap:8px;padding:0 .75rem;}
		.hmb-dest-card{padding:1.3rem .6rem;}
		.hmb-dest-icon{font-size:2rem;}
		.hmb-dest-label{font-size:12px;letter-spacing:1px;}
	}
</style>
<section class="hmb-destacados" aria-label="Accesos destacados">
	<?php if ($showDestTitle && $destTitle !== ''): ?>
	<h2 class="hmb-destacados-title"><?php echo htmlspecialchars($destTitle, ENT_QUOTES); ?></h2>
	<?php endif; ?>
	<div class="hmb-destacados-grid">
		<?php foreach ($destItems as $d):
			$u    = $d['url'];
			$href = preg_match('~^https?://~i', $u)
				? $u
				: (str_starts_with($u, '/') ? $u : $destRoot . '/' . ltrim($u, '/'));
			$cardImg = '';
			if (($d['type'] === 'image' || $d['type'] === 'imagetext') && $d['img'] !== '') {
				$cardImg = preg_match('~^https?://~i', $d['img'])
					? $d['img']
					: (str_starts_with($d['img'], '/') ? $d['img'] : $destRoot . '/' . ltrim($d['img'], '/'));
			}
			$isBanner    = ($d['type'] === 'image'     && $cardImg !== '');
			$isImageText = ($d['type'] === 'imagetext' && $cardImg !== '');
		?>
		<?php if ($isBanner): ?>
		<a class="hmb-dest-card is-banner" href="<?php echo htmlspecialchars($href, ENT_QUOTES); ?>">
			<img class="hmb-dest-banner" src="<?php echo htmlspecialchars($cardImg, ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($d['label'], ENT_QUOTES); ?>" loading="lazy">
		</a>
		<?php elseif ($isImageText): ?>
		<a class="hmb-dest-card is-imgtext" href="<?php echo htmlspecialchars($href, ENT_QUOTES); ?>" style="background-image:url('<?php echo htmlspecialchars($cardImg, ENT_QUOTES); ?>')">
			<span class="hmb-dest-label"><?php echo htmlspecialchars($d['label'], ENT_QUOTES); ?></span>
		</a>
		<?php else: ?>
		<a class="hmb-dest-card" href="<?php echo htmlspecialchars($href, ENT_QUOTES); ?>">
			<?php if ($d['icon'] !== ''): ?>
			<span class="hmb-dest-icon"><?php echo $d['icon']; ?></span>
			<?php endif; ?>
			<span class="hmb-dest-label"><?php echo htmlspecialchars($d['label'], ENT_QUOTES); ?></span>
		</a>
		<?php endif; ?>
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
/* HMB — Red de seguridad: reescribe enlaces nativos de usuarios a las rutas /registro */
(function(){
  var root = '<?php echo rtrim(\Joomla\CMS\Uri\Uri::root(), '/'); ?>';
  var map = [
    [/component\/users\/registration/i, '/index.php/registro?layout=register'],
    [/component\/users\/login/i,        '/index.php/registro'],
    [/component\/users\/remind/i,       '/index.php/registro/recordar-usuario'],
    [/component\/users\/reset/i,        '/index.php/registro/contrasena-olvidada'],
    [/component\/users\/profile/i,      '/index.php/registro/perfil-usuario']
  ];
  document.querySelectorAll('a[href*="component/users/"]').forEach(function(a){
    var h = a.getAttribute('href') || '';
    for (var i=0;i<map.length;i++){
      if (map[i][0].test(h)){ a.setAttribute('href', root + map[i][1]); break; }
    }
  });
})();
</script>
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

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NR9JTB73"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
</body>
</html>
