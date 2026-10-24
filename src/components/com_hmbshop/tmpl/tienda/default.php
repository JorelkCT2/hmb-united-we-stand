<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Site\Helper\Vista;
use Joomla\CMS\Uri\Uri;

/** @var \HeavyMetalBrothers\Component\Hmbshop\Site\View\Tienda\HtmlView $this */
$h      = fn($s) => Vista::h($s);
$iconos = ['🤘', '💿', '🎸', '👕', '🔥', '⚡', '💀', '🎵'];
?>
<div class="hmb-shop-wrap">
<div class="hmb-container">
  <nav class="hmb-shop-breadcrumb">
    <a href="<?php echo $h(Uri::root()); ?>">Inicio</a>
    <span class="sep">›</span>
    <span class="current">Tienda</span>
  </nav>
  <div class="hmb-shop-header">
    <h1>Tienda</h1>
  </div>
</div>

<?php if (empty($this->categorias)) : ?>
<div class="hmb-container">
  <p style="color:#777;font-family:'Oswald',sans-serif;letter-spacing:2px;text-transform:uppercase;padding:3rem 0;">Sin categorías disponibles</p>
</div>
<?php else : ?>
<div class="hmb-cats-premium-wrap">
  <?php foreach ($this->categorias as $i => $c) :
      $desc = trim(strip_tags((string) $c->descripcion));
      $img  = !empty($c->imagen) ? Vista::foto($c->imagen) : '';
  ?>
  <a href="<?php echo $h(Vista::urlCategoria($c)); ?>" class="hmb-cat-premium" style="--delay:<?php echo $i * 120; ?>ms">
    <div class="hmb-cat-p-bg">
      <?php if ($img) : ?>
        <div class="hmb-cat-p-img" style="background-image:url('<?php echo $h($img); ?>')"></div>
      <?php else : ?>
        <div class="hmb-cat-p-img hmb-cat-p-noimg"><?php echo $iconos[$i % count($iconos)]; ?></div>
      <?php endif; ?>
      <div class="hmb-cat-p-overlay"></div>
    </div>
    <div class="hmb-cat-p-sparks" aria-hidden="true">
      <span class="sp sp1"></span><span class="sp sp2"></span>
      <span class="sp sp3"></span><span class="sp sp4"></span><span class="sp sp5"></span>
    </div>
    <div class="hmb-cat-p-sideline"></div>
    <div class="hmb-cat-p-content">
      <div class="hmb-cat-p-number"><?php printf('%02d', $i + 1); ?></div>
      <div class="hmb-cat-p-text">
        <h2 class="hmb-cat-p-title"><?php echo $h($c->nombre); ?></h2>
        <?php if ($desc !== '') : ?>
          <p class="hmb-cat-p-desc"><?php echo $h($desc); ?></p>
        <?php endif; ?>
        <div class="hmb-cat-p-cta">
          <span class="hmb-cat-p-cta-text">Ver colección</span>
          <span class="hmb-cat-p-cta-arrow">
            <svg viewBox="0 0 28 8" fill="none"><line x1="0" y1="4" x2="22" y2="4" stroke="currentColor" stroke-width="1.5"/><polyline points="18,1 22,4 18,7" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
          </span>
        </div>
      </div>
    </div>
    <div class="hmb-cat-p-reflection"></div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</div>
