<?php defined('_JEXEC') or die; ?>

<div id="<?php echo $moduleId; ?>"
     class="hmb-social hmb-social--<?php echo $layout; ?> hmb-social--<?php echo $size; ?><?php echo $animated ? ' hmb-social--animated' : ''; ?>"
     <?php if ($customSize > 0): ?>style="--icon-size:<?php echo $customSize; ?>px;"<?php endif; ?>>

    <?php if ($title): ?>
    <div class="hmb-social__title">
        <span class="hmb-social__title-line"></span>
        <span class="hmb-social__title-text"><?php echo htmlspecialchars($title, ENT_QUOTES); ?></span>
        <span class="hmb-social__title-line"></span>
    </div>
    <?php endif; ?>

    <div class="hmb-social__grid">
        <?php foreach ($networks as $key => $net):
            if (empty($net['url'])) continue;
            $url = htmlspecialchars($net['url'], ENT_QUOTES);
        ?>
        <a href="<?php echo $url; ?>"
           class="hmb-social__item hmb-social__item--<?php echo $key; ?>"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="<?php echo htmlspecialchars($net['label'], ENT_QUOTES); ?>"
           style="--brand:<?php echo $net['color']; ?>">
            <span class="hmb-social__icon-wrap" aria-hidden="true">
                <svg class="hmb-social__svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="<?php echo $net['icon']; ?>"/>
                </svg>
            </span>
            <?php if ($showLabel): ?>
            <span class="hmb-social__label"><?php echo htmlspecialchars($net['label'], ENT_QUOTES); ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

</div>
