<?php
/**
 * Heavy Metal Brothers — Register + Login Module Template  v2.3
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$returnLogin  = base64_encode($redirectLogin);
$returnLogout = base64_encode($redirectLogout);
?>

<?php if ($enableCaptcha && !empty($recaptchaSiteKey)) : ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($recaptchaSiteKey, ENT_QUOTES); ?>" async></script>
<?php endif; ?>

<div id="<?php echo $moduleId; ?>" class="hmb-module <?php echo htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_QUOTES); ?>">
<div class="hmb-inner">

    <span class="hmb-corner hmb-corner-tl" aria-hidden="true"></span>
    <span class="hmb-corner hmb-corner-tr" aria-hidden="true"></span>
    <span class="hmb-corner hmb-corner-bl" aria-hidden="true"></span>
    <span class="hmb-corner hmb-corner-br" aria-hidden="true"></span>

    <?php if ($isLoggedIn) : ?>
    <!-- ══ LOGGED IN ══════════════════════════════════════ -->
    <header class="hmb-panel-header">
        <div class="hmb-crest" aria-hidden="true">
            <span class="hmb-crest-line"></span>
            <span class="hmb-skull">&#9760;</span>
            <span class="hmb-crest-line"></span>
        </div>
        <h2 class="hmb-title"><?php echo Text::_('MOD_HMB_LOGIN_WELCOME'); ?></h2>
        <p class="hmb-subtitle"><?php echo htmlspecialchars($user->name, ENT_QUOTES); ?></p>
    </header>
    <div class="hmb-panel-body">
        <form action="<?php echo Route::_('index.php?option=com_users&task=user.logout'); ?>" method="post">
            <input type="hidden" name="return" value="<?php echo $returnLogout; ?>">
            <input type="hidden" name="<?php echo $token; ?>" value="1">
            <button type="submit" class="hmb-btn-submit">
                <span class="hmb-btn-text"><?php echo Text::_('MOD_HMB_LOGIN_BTN_LOGOUT'); ?></span>
                <span class="hmb-btn-icon" aria-hidden="true">&#9658;</span>
            </button>
        </form>
        <div style="text-align:center;margin-top:1rem;">
            <a class="hmb-footer-link" href="<?php echo Uri::root(); ?>index.php/registro/perfil-usuario">
                &#9670; <?php echo Text::_('MOD_HMB_LOGIN_MY_PROFILE'); ?>
            </a>
        </div>
    </div>

    <?php elseif ($justLoggedOut) : ?>
    <!-- ══ PANTALLA DE DESPEDIDA ══════════════════════════ -->
    <div class="hmb-panel-body" style="text-align:center;padding:2.5rem 1.5rem">
        <?php if (!empty($byeImage)) : ?>
        <img src="<?php echo htmlspecialchars($byeImage, ENT_QUOTES); ?>"
            alt="<?php echo htmlspecialchars($byeTitle, ENT_QUOTES); ?>"
            style="max-width:200px;max-height:160px;object-fit:contain;border-radius:4px;margin-bottom:1.25rem;opacity:.85">
        <?php else : ?>
        <div style="font-size:2.8rem;margin-bottom:1rem">&#9760;</div>
        <?php endif; ?>
        <h2 style="font-size:1.1rem;color:#fff;margin:0 0 .4rem;letter-spacing:.06em">
            <?php echo htmlspecialchars($byeTitle, ENT_QUOTES); ?>
        </h2>
        <p style="font-size:.58rem;color:var(--c-gold);letter-spacing:.22em;text-transform:uppercase;margin:0 0 1.1rem">
            <?php echo htmlspecialchars($byeSubtitle, ENT_QUOTES); ?>
        </p>
        <p style="font-size:.82rem;color:#b0b0b0;line-height:1.6;margin:0 0 1.4rem">
            <?php echo nl2br(htmlspecialchars($byeMessage, ENT_QUOTES)); ?>
        </p>
        <div style="height:3px;background:rgba(255,255,255,.07);border-radius:2px;overflow:hidden;max-width:200px;margin:0 auto">
            <div id="<?php echo $moduleId; ?>-bye-bar"
                style="height:100%;background:linear-gradient(90deg,var(--c-red,#b41414),var(--c-gold,#c9a84c));border-radius:2px;width:0"></div>
        </div>
        <p style="font-size:.68rem;color:#888;margin-top:.75rem">Volviendo al inicio...</p>
    </div>
    <script>
    (function(){
        var bar=document.getElementById('<?php echo $moduleId; ?>-bye-bar');
        var dur=<?php echo (int) $byeDelay; ?>;
        if(bar){ requestAnimationFrame(function(){ bar.style.transition='width '+dur+'ms linear'; bar.style.width='100%'; }); }
        setTimeout(function(){ window.location.href='<?php echo addslashes(Uri::root()); ?>'; }, dur);
    })();
    </script>

    <?php else : ?>

    <!-- ── REGISTER PANEL ──────────────────────────────── -->
    <div class="hmb-panel hmb-panel-active" id="<?php echo $moduleId; ?>-panel-register">

        <header class="hmb-panel-header">
            <div class="hmb-crest" aria-hidden="true">
                <span class="hmb-crest-line"></span>
                <span class="hmb-skull">&#9760;</span>
                <span class="hmb-crest-line"></span>
            </div>
            <h2 class="hmb-title"><?php echo Text::_('MOD_HMB_REGISTER_TITLE'); ?></h2>
            <p class="hmb-subtitle"><?php echo Text::_('MOD_HMB_REGISTER_SUBTITLE'); ?></p>
        </header>

        <div class="hmb-panel-body">
            <div id="<?php echo $moduleId; ?>-reg-alert" class="hmb-alert" role="alert" aria-live="polite" style="display:none;"></div>

            <form id="<?php echo $moduleId; ?>-reg-form" class="hmb-form" novalidate
                enctype="multipart/form-data">
                <input type="hidden" name="<?php echo $token; ?>" value="1">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectRegister, ENT_QUOTES); ?>">
                <?php if ($enableCaptcha && !empty($recaptchaSiteKey)) : ?>
                <input type="hidden" name="g-recaptcha-response" id="<?php echo $moduleId; ?>-recaptcha-token" value="">
                <?php endif; ?>

                <!-- Nombre · Apellidos · Usuario -->
                <div class="hmb-row hmb-row-3">
                    <div class="hmb-field">
                        <label class="hmb-label" for="<?php echo $moduleId; ?>-reg-firstname">
                            <?php echo Text::_('MOD_HMB_REGISTER_FIELD_FIRSTNAME'); ?>
                        </label>
                        <input type="text"
                            id="<?php echo $moduleId; ?>-reg-firstname"
                            name="firstname"
                            class="hmb-input"
                            placeholder="<?php echo Text::_('MOD_HMB_REGISTER_PLACEHOLDER_FIRSTNAME'); ?>"
                            autocomplete="given-name">
                    </div>
                    <div class="hmb-field">
                        <label class="hmb-label" for="<?php echo $moduleId; ?>-reg-surname">
                            <?php echo Text::_('MOD_HMB_REGISTER_FIELD_SURNAME'); ?>
                        </label>
                        <input type="text"
                            id="<?php echo $moduleId; ?>-reg-surname"
                            name="surname"
                            class="hmb-input"
                            placeholder="<?php echo Text::_('MOD_HMB_REGISTER_PLACEHOLDER_SURNAME'); ?>"
                            autocomplete="family-name">
                    </div>
                    <?php if ($showUsername) : ?>
                    <div class="hmb-field">
                        <label class="hmb-label" for="<?php echo $moduleId; ?>-reg-username">
                            <?php echo Text::_('MOD_HMB_REGISTER_FIELD_USERNAME'); ?>
                        </label>
                        <input type="text"
                            id="<?php echo $moduleId; ?>-reg-username"
                            name="username"
                            class="hmb-input"
                            placeholder="<?php echo Text::_('MOD_HMB_REGISTER_PLACEHOLDER_USERNAME'); ?>"
                            autocomplete="username">
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Email + Confirmar email -->
                <div class="hmb-field">
                    <label class="hmb-label" for="<?php echo $moduleId; ?>-reg-email">
                        <?php echo Text::_('MOD_HMB_REGISTER_FIELD_EMAIL'); ?>
                        <span class="hmb-required" aria-hidden="true">*</span>
                    </label>
                    <div class="hmb-input-wrap">
                        <input type="email"
                            id="<?php echo $moduleId; ?>-reg-email"
                            name="email"
                            class="hmb-input"
                            placeholder="<?php echo Text::_('MOD_HMB_REGISTER_PLACEHOLDER_EMAIL'); ?>"
                            required autocomplete="email">
                        <span class="hmb-input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
                        </span>
                    </div>
                </div>
                <div class="hmb-field">
                    <label class="hmb-label" for="<?php echo $moduleId; ?>-reg-email2">
                        <?php echo Text::_('MOD_HMB_REGISTER_FIELD_EMAIL2'); ?>
                        <span class="hmb-required" aria-hidden="true">*</span>
                    </label>
                    <div class="hmb-input-wrap">
                        <input type="email"
                            id="<?php echo $moduleId; ?>-reg-email2"
                            name="email2"
                            class="hmb-input"
                            placeholder="<?php echo Text::_('MOD_HMB_REGISTER_PLACEHOLDER_EMAIL2'); ?>"
                            required autocomplete="email">
                        <span class="hmb-input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
                        </span>
                    </div>
                </div>

                <!-- Contraseñas -->
                <div class="hmb-row">
                    <div class="hmb-field">
                        <label class="hmb-label" for="<?php echo $moduleId; ?>-reg-pw">
                            <?php echo Text::_('MOD_HMB_REGISTER_FIELD_PASSWORD'); ?>
                            <span class="hmb-required" aria-hidden="true">*</span>
                        </label>
                        <div class="hmb-password-wrap">
                            <input type="password" id="<?php echo $moduleId; ?>-reg-pw" name="password"
                                class="hmb-input"
                                placeholder="<?php echo Text::_('MOD_HMB_REGISTER_PLACEHOLDER_PASSWORD'); ?>"
                                required minlength="8" autocomplete="new-password">
                            <button type="button" class="hmb-eye-toggle" data-target="<?php echo $moduleId; ?>-reg-pw" aria-label="Ver contraseña">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="hmb-field">
                        <label class="hmb-label" for="<?php echo $moduleId; ?>-reg-pw2">
                            <?php echo Text::_('MOD_HMB_REGISTER_FIELD_PASSWORD2'); ?>
                            <span class="hmb-required" aria-hidden="true">*</span>
                        </label>
                        <div class="hmb-password-wrap">
                            <input type="password" id="<?php echo $moduleId; ?>-reg-pw2" name="password2"
                                class="hmb-input"
                                placeholder="<?php echo Text::_('MOD_HMB_REGISTER_PLACEHOLDER_PASSWORD2'); ?>"
                                required autocomplete="new-password">
                            <button type="button" class="hmb-eye-toggle" data-target="<?php echo $moduleId; ?>-reg-pw2" aria-label="Confirmar contraseña">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Foto de perfil (opcional) -->
                <div class="hmb-field">
                    <label class="hmb-label" for="<?php echo $moduleId; ?>-avatar">
                        <?php echo Text::_('MOD_HMB_REGISTER_FIELD_AVATAR'); ?>
                        <span style="color:var(--c-muted);font-weight:300;text-transform:none;letter-spacing:0"> — opcional</span>
                    </label>
                    <label class="hmb-avatar-pick" for="<?php echo $moduleId; ?>-avatar" id="<?php echo $moduleId; ?>-avatar-lbl">
                        <span class="hmb-avatar-pick-icon">&#128247;</span>
                        <span class="hmb-avatar-pick-txt">Elegir foto de perfil</span>
                    </label>
                    <input type="file" id="<?php echo $moduleId; ?>-avatar" name="avatar"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        style="display:none"
                        onchange="
                            var lbl=document.getElementById('<?php echo $moduleId; ?>-avatar-lbl');
                            var txt=lbl.querySelector('.hmb-avatar-pick-txt');
                            if(this.files[0]){
                                var url=URL.createObjectURL(this.files[0]);
                                lbl.style.backgroundImage='url('+url+')';
                                lbl.style.backgroundSize='cover';
                                lbl.style.backgroundPosition='center';
                                txt.textContent=this.files[0].name;
                            }
                        ">
                </div>

                <!-- Notificaciones + Metal Quiz opt-in -->
                <div class="hmb-field hmb-field-check">
                    <label class="hmb-check-label">
                        <input type="checkbox"
                            id="<?php echo $moduleId; ?>-notifications"
                            name="accept_notifications"
                            class="hmb-checkbox"
                            value="1"
                            checked>
                        <span class="hmb-check-box" aria-hidden="true"></span>
                        <span class="hmb-check-text"><?php echo Text::_('MOD_HMB_REGISTER_ACCEPT_NOTIFICATIONS'); ?></span>
                    </label>
                </div>
                <div class="hmb-field hmb-field-check">
                    <label class="hmb-check-label">
                        <input type="checkbox"
                            id="<?php echo $moduleId; ?>-quiz-optin"
                            name="metal_quiz_invitations"
                            class="hmb-checkbox"
                            value="1"
                            checked>
                        <span class="hmb-check-box" aria-hidden="true"></span>
                        <span class="hmb-check-text">&#9760; <?php echo Text::_('MOD_HMB_REGISTER_QUIZ_OPTIN'); ?></span>
                    </label>
                </div>

                <button type="submit" class="hmb-btn-submit" id="<?php echo $moduleId; ?>-reg-submit">
                    <span class="hmb-btn-text"><?php echo Text::_('MOD_HMB_REGISTER_BTN_SUBMIT'); ?></span>
                    <span class="hmb-btn-icon" aria-hidden="true">&#9658;</span>
                </button>
            </form>

            <?php if ($enableGoogle && !empty($googleClientId)) : ?>
            <div class="hmb-divider" aria-hidden="true"><span><?php echo Text::_('MOD_HMB_REGISTER_OR'); ?></span></div>
            <div id="g_id_onload"
                data-client_id="<?php echo htmlspecialchars($googleClientId, ENT_QUOTES); ?>"
                data-context="signup" data-ux_mode="popup"
                data-callback="hmbGoogleCallback_<?php echo (int)$module->id; ?>"
                data-auto_prompt="false"></div>
            <button type="button" class="hmb-google-btn" id="<?php echo $moduleId; ?>-google-btn">
                <svg class="hmb-google-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span class="hmb-google-text"><?php echo htmlspecialchars($googleBtnText, ENT_QUOTES); ?></span>
            </button>
            <?php endif; ?>
        </div>

        <footer class="hmb-panel-footer">
            <?php echo Text::_('MOD_HMB_REGISTER_ALREADY_MEMBER'); ?>
            <button type="button" class="hmb-footer-switch" data-target="<?php echo $moduleId; ?>-panel-login">
                <?php echo Text::_('MOD_HMB_REGISTER_LOGIN'); ?>
            </button>
        </footer>
    </div><!-- panel-register -->

    <!-- ── LOGIN PANEL ──────────────────────────────────── -->
    <div class="hmb-panel" id="<?php echo $moduleId; ?>-panel-login">

        <header class="hmb-panel-header">
            <div class="hmb-crest" aria-hidden="true">
                <span class="hmb-crest-line"></span>
                <span class="hmb-skull">&#9760;</span>
                <span class="hmb-crest-line"></span>
            </div>
            <h2 class="hmb-title"><?php echo Text::_('MOD_HMB_LOGIN_TITLE'); ?></h2>
            <p class="hmb-subtitle"><?php echo Text::_('MOD_HMB_LOGIN_SUBTITLE'); ?></p>
        </header>

        <div class="hmb-panel-body">
            <form id="<?php echo $moduleId; ?>-login-form"
                class="hmb-form"
                action="<?php echo Route::_('index.php?option=com_users&task=user.login'); ?>"
                method="post" novalidate>

                <input type="hidden" name="return"                value="<?php echo $returnLogin; ?>">
                <input type="hidden" name="<?php echo $token; ?>" value="1">

                <div class="hmb-field">
                    <label class="hmb-label" for="<?php echo $moduleId; ?>-login-user">
                        <?php echo Text::_('MOD_HMB_LOGIN_FIELD_USERNAME'); ?>
                        <span class="hmb-required" aria-hidden="true">*</span>
                    </label>
                    <div class="hmb-input-wrap">
                        <input type="text" id="<?php echo $moduleId; ?>-login-user" name="username"
                            class="hmb-input"
                            placeholder="<?php echo Text::_('MOD_HMB_LOGIN_PLACEHOLDER_USERNAME'); ?>"
                            required autocomplete="username">
                        <span class="hmb-input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        </span>
                    </div>
                </div>

                <div class="hmb-field">
                    <label class="hmb-label" for="<?php echo $moduleId; ?>-login-pw">
                        <?php echo Text::_('MOD_HMB_LOGIN_FIELD_PASSWORD'); ?>
                        <span class="hmb-required" aria-hidden="true">*</span>
                    </label>
                    <div class="hmb-password-wrap">
                        <input type="password" id="<?php echo $moduleId; ?>-login-pw" name="password"
                            class="hmb-input"
                            placeholder="<?php echo Text::_('MOD_HMB_LOGIN_PLACEHOLDER_PASSWORD'); ?>"
                            required autocomplete="current-password">
                        <button type="button" class="hmb-eye-toggle" data-target="<?php echo $moduleId; ?>-login-pw" aria-label="Ver contraseña">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <?php if ($showRemember) : ?>
                <div class="hmb-field hmb-field-check">
                    <label class="hmb-check-label">
                        <input type="checkbox" name="remember" class="hmb-checkbox" value="yes">
                        <span class="hmb-check-box" aria-hidden="true"></span>
                        <span class="hmb-check-text"><?php echo Text::_('MOD_HMB_LOGIN_REMEMBER'); ?></span>
                    </label>
                </div>
                <?php endif; ?>

                <button type="submit" class="hmb-btn-submit" id="<?php echo $moduleId; ?>-login-submit">
                    <span class="hmb-btn-text"><?php echo Text::_('MOD_HMB_LOGIN_BTN_SUBMIT'); ?></span>
                    <span class="hmb-btn-icon" aria-hidden="true">&#9658;</span>
                </button>
            </form>

            <?php if ($showForgotPw || $showForgotUser) : ?>
            <ul class="hmb-links">
                <?php if ($showForgotPw) : ?>
                <li><a href="<?php echo Uri::root(); ?>index.php?option=com_users&amp;view=reset">
                    <span class="hmb-link-bullet">&#9670;</span>
                    <?php echo Text::_('MOD_HMB_LOGIN_FORGOT_PASSWORD'); ?>
                </a></li>
                <?php endif; ?>
                <?php if ($showForgotUser) : ?>
                <li><a href="<?php echo Uri::root(); ?>index.php?option=com_users&amp;view=remind">
                    <span class="hmb-link-bullet">&#9670;</span>
                    <?php echo Text::_('MOD_HMB_LOGIN_FORGOT_USERNAME'); ?>
                </a></li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>
        </div>

        <footer class="hmb-panel-footer">
            <?php echo Text::_('MOD_HMB_LOGIN_NO_ACCOUNT'); ?>
            <button type="button" class="hmb-footer-switch" data-target="<?php echo $moduleId; ?>-panel-register">
                <?php echo Text::_('MOD_HMB_TAB_REGISTER'); ?>
            </button>
        </footer>
    </div><!-- panel-login -->

    <?php endif; ?>

</div><!-- .hmb-inner -->
</div><!-- .hmb-module -->
