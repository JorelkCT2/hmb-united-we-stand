<?php defined('_JEXEC') or die; ?>

<div id="<?php echo $moduleId; ?>"
     class="hmb-logo-wrap effect-<?php echo htmlspecialchars($effect, ENT_QUOTES); ?>"
     data-effect="<?php echo htmlspecialchars($effect, ENT_QUOTES); ?>">

    <a href="<?php echo htmlspecialchars($logoLink, ENT_QUOTES); ?>"
       class="hmb-logo-link"
       aria-label="<?php echo htmlspecialchars($logoAlt, ENT_QUOTES); ?>"
       <?php echo $openNewTab ? 'target="_blank" rel="noopener"' : ''; ?>>

        <?php if (!empty($logoImageUrl)): ?>

            <!-- Partículas de fondo (JS las genera) -->
            <div class="hmb-logo-particles" aria-hidden="true"></div>

            <!-- Anillos decorativos -->
            <div class="hmb-logo-rings" aria-hidden="true">
                <span class="hmb-logo-ring hmb-logo-ring--1"></span>
                <span class="hmb-logo-ring hmb-logo-ring--2"></span>
                <span class="hmb-logo-ring hmb-logo-ring--3"></span>
            </div>

            <!-- Logo PNG -->
            <img src="<?php echo htmlspecialchars($logoImageUrl, ENT_QUOTES); ?>"
                 alt="<?php echo htmlspecialchars($logoAlt, ENT_QUOTES); ?>"
                 class="hmb-logo-img"
                 draggable="false"
                 loading="eager">

            <!-- Reflejo (efecto mirror) -->
            <div class="hmb-logo-mirror" aria-hidden="true">
                <img src="<?php echo htmlspecialchars($logoImageUrl, ENT_QUOTES); ?>"
                     alt=""
                     class="hmb-logo-img-mirror"
                     draggable="false"
                     aria-hidden="true">
            </div>

        <?php else: ?>

            <!-- Fallback texto si no hay imagen -->
            <span class="hmb-logo-text-fallback">
                <?php echo htmlspecialchars($logoAlt, ENT_QUOTES); ?>
            </span>

        <?php endif; ?>

    </a>

</div>
