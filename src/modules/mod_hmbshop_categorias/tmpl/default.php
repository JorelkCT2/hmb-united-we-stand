<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Site\Helper\Vista;

/** @var array $categorias */
if (!$categorias) {
    return;
}
?>
<div class="ph-category-module-box"><div class="pcModCategoryBox"><ul>
<?php foreach ($categorias as $c) : $img = !empty($c->imagen) ? Vista::foto($c->imagen, 's') : ''; ?>
<li class=""><a class="ph-category-box-link" href="<?php echo Vista::h(Vista::urlCategoria($c)); ?>"><?php if ($img !== '') : ?><div class="ph-category-box-image"><img alt="<?php echo Vista::h($c->nombre); ?>" src="<?php echo Vista::h($img); ?>" /></div><?php endif; ?><div class="ph-category-box-title"><?php echo Vista::h($c->nombre); ?></div></a></li>
<?php endforeach; ?>
</ul></div></div>
