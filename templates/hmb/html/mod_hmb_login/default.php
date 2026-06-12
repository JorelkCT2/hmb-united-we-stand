<?php
/**
 * Heavy Metal Brothers — Login Module Template
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$return = base64_encode($redirectLogin);
?>

<div id="<?php echo $moduleId; ?>" class="hmb-login-module <?php echo htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_QUOTES); ?>">
<div class="hmb-login-inner">

    <!-- Ornamental corners -->
    <span class="hmb-corner hmb-corner-tl" aria-hidden="true"></span>
    <span class="hmb-corner hmb-corner-tr" aria-hidden="true"></span>
    <span class="hmb-corner hmb-corner-bl" aria-hidden="true"></span>
    <span class="hmb-corner hmb-corner-br" aria-hidden="true"></span>

    <?php if (!$isLoggedIn) : ?>

    <!-- ── Header ── -->
    <header class="hmb-login-header">
        <div class="hmb-crest" aria-hidden="true">
            <span class="hmb-crest-line"></span>
            <span class="hmb-skull">&#9760;</span>
            <span class="hmb-crest-line"></span>
        </div>
        <h2 class="hmb-login-title"><?php echo Text::_('MOD_HMB_LOGIN_TITLE'); ?></h2>
        <p class="hmb-login-subtitle"><?php echo Text::_('MOD_HMB_LOGIN_SUBTITLE'); ?></p>
    </header>

    <?php if (!empty($justLoggedOut)) : ?>
    <div class="hmb-bye" style="margin:0 1.25rem 1rem;padding:.8rem 1rem;border:1px solid #c9a84c;border-radius:4px;background:rgba(201,168,76,.08);color:#c9a84c;text-align:center;letter-spacing:.04em;font-size:.9rem">
        &#9760; Has cerrado sesi&oacute;n. &iexcl;Hasta pronto, hermano del metal! &#9760;
    </div>
    <?php endif; ?>

    <!-- ── Body ── -->
    <div class="hmb-login-body">

        <!-- Alert -->
        <div id="<?php echo $moduleId; ?>-alert"
            class="hmb-alert"
            role="alert"
            aria-live="polite"
            style="display:none;"></div>

        <!-- Login form -->
        <form id="<?php echo $moduleId; ?>-form"
            class="hmb-form"
            action="<?php echo Route::_('index.php?option=com_users&task=user.login'); ?>"
            method="post"
            novalidate>

            <input type="hidden" name="return"           value="<?php echo $return; ?>">
            <input type="hidden" name="<?php echo $token; ?>" value="1">

            <!-- Username -->
            <div class="hmb-field">
                <label class="hmb-label" for="<?php echo $moduleId; ?>-username">
                    <?php echo Text::_('MOD_HMB_LOGIN_FIELD_USERNAME'); ?>
                    <span class="hmb-required" aria-hidden="true">*</span>
                </label>
                <div class="hmb-input-wrap">
                    <input type="text"
                        id="<?php echo $moduleId; ?>-username"
                        name="username"
                        class="hmb-input"
                        placeholder="<?php echo Text::_('MOD_HMB_LOGIN_PLACEHOLDER_USERNAME'); ?>"
                        required
                        autocomplete="username"
                        autofocus>
                    <span class="hmb-input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </span>
                </div>
            </div>

            <!-- Password -->
            <div class="hmb-field">
                <label class="hmb-label" for="<?php echo $moduleId; ?>-password">
                    <?php echo Text::_('MOD_HMB_LOGIN_FIELD_PASSWORD'); ?>
                    <span class="hmb-required" aria-hidden="true">*</span>
                </label>
                <div class="hmb-password-wrap">
                    <input type="password"
                        id="<?php echo $moduleId; ?>-password"
                        name="password"
                        class="hmb-input"
                        placeholder="<?php echo Text::_('MOD_HMB_LOGIN_PLACEHOLDER_PASSWORD'); ?>"
                        required
                        autocomplete="current-password">
                    <button type="button"
                        class="hmb-eye-toggle"
                        data-target="<?php echo $moduleId; ?>-password"
                        onclick="var i=document.getElementById(this.dataset.target);if(i){i.type=i.type==='password'?'text':'password';}"
                        aria-label="<?php echo Text::_('MOD_HMB_LOGIN_TOGGLE_PW'); ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <?php if ($showRemember) : ?>
            <!-- Remember me -->
            <div class="hmb-field hmb-field-check">
                <label class="hmb-check-label">
                    <input type="checkbox"
                        id="<?php echo $moduleId; ?>-remember"
                        name="remember"
                        class="hmb-checkbox"
                        value="yes">
                    <span class="hmb-check-box" aria-hidden="true"></span>
                    <span class="hmb-check-text"><?php echo Text::_('MOD_HMB_LOGIN_REMEMBER'); ?></span>
                </label>
            </div>
            <?php endif; ?>

            <!-- Submit -->
            <button type="submit"
                class="hmb-btn-submit"
                id="<?php echo $moduleId; ?>-submit">
                <span class="hmb-btn-text"><?php echo Text::_('MOD_HMB_LOGIN_BTN_SUBMIT'); ?></span>
                <span class="hmb-btn-icon" aria-hidden="true">&#9658;</span>
            </button>

        </form>

        <!-- Helper links -->
        <?php if ($showForgotPw || $showForgotUser) : ?>
        <ul class="hmb-links" role="list">
            <?php if ($showForgotPw) : ?>
            <li>
                <a href="<?php echo htmlspecialchars($forgotPwUrl, ENT_QUOTES); ?>">
                    <span class="hmb-link-bullet" aria-hidden="true">&#9670;</span>
                    <?php echo Text::_('MOD_HMB_LOGIN_FORGOT_PASSWORD'); ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($showForgotUser) : ?>
            <li>
                <a href="<?php echo htmlspecialchars($forgotUserUrl, ENT_QUOTES); ?>">
                    <span class="hmb-link-bullet" aria-hidden="true">&#9670;</span>
                    <?php echo Text::_('MOD_HMB_LOGIN_FORGOT_USERNAME'); ?>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <?php endif; ?>

    </div><!-- .hmb-login-body -->

    <?php else : ?>

    <!-- ── Logged-in state ── -->
    <header class="hmb-login-header">
        <div class="hmb-crest" aria-hidden="true">
            <span class="hmb-crest-line"></span>
            <span class="hmb-skull">&#9760;</span>
            <span class="hmb-crest-line"></span>
        </div>
        <h2 class="hmb-login-title"><?php echo Text::_('MOD_HMB_LOGIN_WELCOME'); ?></h2>
        <p class="hmb-login-subtitle"><?php echo htmlspecialchars($user->name, ENT_QUOTES); ?></p>
    </header>

    <div class="hmb-login-body">
        <form action="<?php echo Route::_('index.php?option=com_users&task=user.logout'); ?>" method="post">
            <input type="hidden" name="return"           value="<?php echo base64_encode($redirectLogout); ?>">
            <input type="hidden" name="<?php echo $token; ?>" value="1">
            <button type="submit" class="hmb-btn-submit hmb-btn-logout">
                <span class="hmb-btn-text"><?php echo Text::_('MOD_HMB_LOGIN_BTN_LOGOUT'); ?></span>
                <span class="hmb-btn-icon" aria-hidden="true">&#9658;</span>
            </button>
        </form>
    </div>

    <?php endif; ?>

    <!-- ── Footer ── -->
    <footer class="hmb-login-footer">
        <?php if (!$isLoggedIn) : ?>
            <?php echo Text::_('MOD_HMB_LOGIN_NO_ACCOUNT'); ?>
            <a href="<?php echo htmlspecialchars($registerUrl, ENT_QUOTES); ?>">
                <?php echo Text::_('MOD_HMB_LOGIN_REGISTER'); ?>
            </a>
        <?php else : ?>
            <a href="<?php echo Route::_('index.php?option=com_users&view=profile'); ?>">
                <?php echo Text::_('MOD_HMB_LOGIN_MY_PROFILE'); ?>
            </a>
        <?php endif; ?>
    </footer>

</div><!-- .hmb-login-inner -->
</div><!-- .hmb-login-module -->
