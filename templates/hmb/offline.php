<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
$app = Factory::getApplication();
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $app->get('sitename'); ?> - Offline</title>
	<link href="https://fonts.googleapis.com/css2?family=Metal+Mania&family=Oswald:wght@400;600&family=Rajdhani:wght@400&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo Uri::root(true); ?>/templates/hmb/css/template.css">
	<style>
		.hmb-offline-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem; background: radial-gradient(ellipse at center, #1a0000 0%, #050505 100%); }
		.hmb-offline-title { font-family: 'Metal Mania', cursive; font-size: 52px; color: var(--white); margin-bottom: 0.5rem; }
		.hmb-offline-msg { color: var(--text-dim); font-size: 16px; max-width: 400px; margin: 0 auto 2rem; }
		.hmb-offline-form { display: flex; flex-direction: column; gap: 12px; max-width: 340px; margin: 0 auto; }
		.hmb-offline-input { background: rgba(255,255,255,0.05); border: 1px solid #333; color: #eee; padding: 12px 16px; font-family: 'Rajdhani'; font-size: 15px; outline: none; width: 100%; }
		.hmb-offline-input:focus { border-color: #CC2200; }
		.hmb-offline-btn { font-family: 'Oswald'; font-size: 12px; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; background: #CC2200; color: #0A0A0A; border: none; padding: 12px; cursor: pointer; width: 100%; }
		.hmb-offline-btn:hover { background: #FF4500; }
	</style>
</head>
<body class="hmb-body">
<div class="hmb-offline-page">
	<div>
		<div class="hmb-offline-title">⚡ <?php echo $app->get('sitename'); ?></div>
		<p class="hmb-offline-msg">
			<?php echo $app->get('offline_message') ?: 'Sitio en mantenimiento. El metal volverá pronto.'; ?>
		</p>
		<?php if ($app->get('display_offline_message', 1) == 3): ?>
		<form class="hmb-offline-form" action="<?php echo Uri::root(); ?>" method="post">
			<input type="text" class="hmb-offline-input" name="username" placeholder="<?php echo Text::_('JGLOBAL_USERNAME'); ?>">
			<input type="password" class="hmb-offline-input" name="passwd" placeholder="<?php echo Text::_('JGLOBAL_PASSWORD'); ?>">
			<input type="hidden" name="option" value="com_users">
			<input type="hidden" name="task" value="user.login">
			<input type="hidden" name="return" value="<?php echo base64_encode(Uri::root()); ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
			<button type="submit" class="hmb-offline-btn"><?php echo Text::_('JLOGIN'); ?></button>
		</form>
		<?php endif; ?>
	</div>
</div>
</body>
</html>
