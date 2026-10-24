<?php
/**
 * HMB Banner Switcher Pro v4 — carrusel nativo con scroll-snap (el deslizar con el dedo lo hace el navegador).
 * Variables del módulo: $slides, $params, $moduleId
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

if (empty($slides)) {
    return;
}

$h    = (int) $params->get('banner_height', 160);
$hMob = (int) $params->get('banner_height_mobile', 96);
$fit  = in_array($params->get('object_fit', 'cover'), ['cover', 'contain'], true) ? $params->get('object_fit', 'cover') : 'cover';
$auto = (int) $params->get('autoplay', 1);
$ms   = max(2000, (int) $params->get('interval', 4500));
$n    = count($slides);
$root = Uri::root();

/** Ruta de imagen de Joomla (con el sufijo #joomlaImage://...) -> URL limpia + medidas reales */
$img = static function (string $ruta) use ($root): array {
    $ruta = trim(strtok($ruta, '#'));

    if ($ruta === '') {
        return ['', 0, 0];
    }

    $url = preg_match('#^https?://#i', $ruta) ? $ruta : $root . ltrim($ruta, '/');
    $abs = JPATH_ROOT . '/' . ltrim($ruta, '/');
    $dim = is_file($abs) ? @getimagesize($abs) : false;

    return [$url, $dim ? (int) $dim[0] : 1920, $dim ? (int) $dim[1] : 365];
};
?>
<section class="hmb-slider" id="<?php echo $moduleId; ?>"
         data-autoplay="<?php echo $auto; ?>" data-interval="<?php echo $ms; ?>"
         style="--hmb-sl-h:<?php echo $h > 0 ? $h . 'px' : 'auto'; ?>;--hmb-sl-hm:<?php echo $hMob > 0 ? $hMob . 'px' : 'auto'; ?>;--hmb-sl-fit:<?php echo $fit; ?>"
         role="region" aria-roledescription="carrusel" aria-label="Destacados de Heavy Metal Brothers">

    <div class="hmb-slider__viewport" tabindex="0" aria-live="off">
        <ul class="hmb-slider__track">
            <?php foreach ($slides as $i => $s):
                [$d, $dw, $dh] = $img((string) $s['desktop']);
                [$m, $mw, $mh] = $img((string) ($s['mobile'] ?: $s['desktop']));
                $d = $d ?: $m;

                if ($d === '') {
                    continue;
                }

                $link  = trim((string) ($s['link'] ?? ''));
                $blank = ($s['target'] ?? '_self') === '_blank';
                $alt   = 'Banner ' . ($i + 1) . ' de ' . $n;
            ?>
            <li class="hmb-slider__slide<?php echo $i === 0 ? ' is-active' : ''; ?>" role="group" aria-roledescription="diapositiva" aria-label="<?php echo ($i + 1) . ' de ' . $n; ?>">
                <?php if ($link !== ''): ?>
                <a class="hmb-slider__link" href="<?php echo htmlspecialchars($link, ENT_QUOTES); ?>"<?php echo $blank ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> draggable="false">
                <?php else: ?>
                <div class="hmb-slider__link">
                <?php endif; ?>
                    <picture>
                        <?php if ($m !== '' && $m !== $d): ?>
                        <source media="(max-width: 767px)" srcset="<?php echo htmlspecialchars($m, ENT_QUOTES); ?>" width="<?php echo $mw; ?>" height="<?php echo $mh; ?>">
                        <?php endif; ?>
                        <img class="hmb-slider__img" src="<?php echo htmlspecialchars($d, ENT_QUOTES); ?>" width="<?php echo $dw; ?>" height="<?php echo $dh; ?>"
                             alt="<?php echo htmlspecialchars($alt, ENT_QUOTES); ?>" draggable="false" decoding="async"
                             <?php echo $i === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'; ?>>
                    </picture>
                <?php echo $link !== '' ? '</a>' : '</div>'; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if ($n > 1): ?>
    <button type="button" class="hmb-slider__arrow hmb-slider__arrow--prev" aria-label="Diapositiva anterior">
        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="15 18 9 12 15 6" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button type="button" class="hmb-slider__arrow hmb-slider__arrow--next" aria-label="Diapositiva siguiente">
        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="9 18 15 12 9 6" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    <div class="hmb-slider__ui">
        <div class="hmb-slider__dots" role="group" aria-label="Elegir diapositiva">
            <?php for ($i = 0; $i < $n; $i++): ?>
            <button type="button" class="hmb-slider__dot<?php echo $i === 0 ? ' is-active' : ''; ?>" data-index="<?php echo $i; ?>"
                    aria-label="Ir a la diapositiva <?php echo $i + 1; ?>"<?php echo $i === 0 ? ' aria-current="true"' : ''; ?>>
                <svg viewBox="0 0 20 20" aria-hidden="true"><path fill="currentColor" d="M10 2C5.8 2 3 4.9 3 8.6c0 2 .9 3.6 2.3 4.6V16c0 .6.4 1 1 1h1.2v-1.8h1V17h1v-1.8h1V17H12c.6 0 1-.4 1-1v-2.8c1.4-1 2.3-2.6 2.3-4.6C16.3 4.9 14.2 2 10 2zM7.6 10.4a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm4.8 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"/></svg>
            </button>
            <?php endfor; ?>
        </div>
        <button type="button" class="hmb-slider__pause" aria-label="Pausar el carrusel" aria-pressed="false">
            <svg class="i-pause" viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="5" width="4" height="14" fill="currentColor"/><rect x="14" y="5" width="4" height="14" fill="currentColor"/></svg>
            <svg class="i-play" viewBox="0 0 24 24" aria-hidden="true"><polygon points="7,5 19,12 7,19" fill="currentColor"/></svg>
        </button>
    </div>
    <div class="hmb-slider__progress" aria-hidden="true"><span></span></div>
    <?php endif; ?>
</section>
