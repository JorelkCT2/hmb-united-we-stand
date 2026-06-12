<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
$wa = $this->getWebAssetManager();
$wa->registerAndUseStyle('hmb.template', 'templates/hmb/css/template.css');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />
	<link href="https://fonts.googleapis.com/css2?family=Metal+Mania&family=Oswald:wght@400;600&family=Rajdhani:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="hmb-body hmb-component-only">
	<jdoc:include type="component" />
	<jdoc:include type="scripts" />
</body>
</html>
