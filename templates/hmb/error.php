<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
$app = Factory::getApplication();
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $this->error->getCode(); ?> - <?php echo $app->get('sitename'); ?></title>
	<link href="https://fonts.googleapis.com/css2?family=Metal+Mania&family=Oswald:wght@400;600&family=Rajdhani:wght@400&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo Uri::root(true); ?>/templates/hmb/css/template.css">
	<style>
		.hmb-error-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem; }
		.hmb-error-code { font-family: 'Metal Mania', cursive; font-size: 140px; line-height: 1; color: var(--fire); text-shadow: 0 0 60px rgba(204,34,0,0.4); }
		.hmb-error-title { font-family: 'Oswald', sans-serif; font-size: 28px; color: var(--white); margin: 1rem 0; }
		.hmb-error-msg { color: var(--text-dim); font-size: 16px; max-width: 400px; margin: 0 auto 2rem; }
		.hmb-error-btn { font-family: 'Oswald', sans-serif; font-size: 12px; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; color: #0A0A0A; background: #CC2200; padding: 12px 28px; text-decoration: none; display: inline-block; clip-path: polygon(8px 0%, 100% 0%, calc(100% - 8px) 100%, 0% 100%); }
		.hmb-error-btn:hover { background: #FF4500; }
	</style>
</head>
<body class="hmb-body">
<div class="hmb-error-page">
	<div>
		<div class="hmb-error-code"><?php echo $this->error->getCode(); ?></div>
		<div class="hmb-error-title"><?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></div>
		<p class="hmb-error-msg">
			<?php if ($this->error->getCode() == 404): ?>
				La página que buscas ha sido destruida por el metal. Vuelve al inicio.
			<?php else: ?>
				Algo ha ido mal. El metal nunca muere, pero esta página sí.
			<?php endif; ?>
		</p>
		<a href="<?php echo Uri::root(); ?>" class="hmb-error-btn">Volver al Inicio</a>
	</div>
</div>
</body>
</html>
