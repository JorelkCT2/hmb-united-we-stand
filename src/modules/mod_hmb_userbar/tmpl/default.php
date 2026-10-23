<?php defined('_JEXEC') or die; ?>

<div id="<?php echo $moduleId; ?>" class="hmb-userbar">
<?php if ($isLoggedIn) : ?>

    <a class="hmb-ub-profile" href="<?php echo htmlspecialchars($profileUrl, ENT_QUOTES); ?>">
        <img class="hmb-ub-avatar" src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES); ?>"
            alt="<?php echo htmlspecialchars($userName, ENT_QUOTES); ?>" width="32" height="32">
        <span class="hmb-ub-welcome">Bienvenido,</span>
        <span class="hmb-ub-name"><?php echo htmlspecialchars(explode(' ', $userName)[0], ENT_QUOTES); ?></span>
    </a>

    <form method="post" action="<?php echo htmlspecialchars($logoutUrl, ENT_QUOTES); ?>" class="hmb-ub-logout-form">
        <input type="hidden" name="return" value="<?php echo base64_encode(\Joomla\CMS\Uri\Uri::root()); ?>">
        <input type="hidden" name="<?php echo $token; ?>" value="1">
        <button type="submit" class="hmb-ub-logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Desconectar
        </button>
    </form>

<?php else : ?>

    <a class="hmb-ub-login" href="<?php echo htmlspecialchars($loginUrl, ENT_QUOTES); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Iniciar sesión
    </a>

<?php endif; ?>
</div>
