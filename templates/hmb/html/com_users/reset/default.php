<?php
/**
 * Heavy Metal Brothers — Contraseña Olvidada
 * Sube a: templates/rt_remnant/html/com_users/reset/default.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Cinzel:wght@400;600;900&family=Raleway:wght@300;400;600&display=swap');

.hmb-page{--c-s:#0f0f0f;--c-b:#252525;--c-r:#b41414;--c-rh:#e02020;--c-g:#c9a84c;--c-gd:#7a6030;--c-sv:#888;--c-t:#d4d4d4;--c-th:#f0ebe0;--fd:'Cinzel Decorative','Cinzel',serif;--fh:'Cinzel',serif;--fb:'Raleway',sans-serif;--r:3px;--e:cubic-bezier(.4,0,.2,1);--sp:.22s;font-family:var(--fb);color:var(--c-t);max-width:480px;margin:3rem auto;padding:0 1rem}

.hmb-card{position:relative;background:var(--c-s);border:1px solid var(--c-b);border-radius:var(--r);box-shadow:0 0 0 1px rgba(255,255,255,.03) inset,0 24px 64px rgba(0,0,0,.85),0 0 80px rgba(180,20,20,.06)}
.hmb-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#3a0000 0%,var(--c-r) 30%,var(--c-g) 50%,var(--c-r) 70%,#3a0000 100%);background-size:200% 100%;animation:hmbBar 4s ease infinite;z-index:2;border-radius:var(--r) var(--r) 0 0}
@keyframes hmbBar{0%{background-position:0 50%}50%{background-position:100% 50%}100%{background-position:0 50%}}

.hmb-cn{position:absolute;width:18px;height:18px;border-color:var(--c-gd);border-style:solid;opacity:.4;z-index:3}
.hmb-cn.tl{top:8px;left:8px;border-width:1px 0 0 1px}.hmb-cn.tr{top:8px;right:8px;border-width:1px 1px 0 0}
.hmb-cn.bl{bottom:8px;left:8px;border-width:0 0 1px 1px}.hmb-cn.br{bottom:8px;right:8px;border-width:0 1px 1px 0}

/* Header */
.hmb-hd{padding:2.25rem 2.5rem 1.75rem;background:linear-gradient(180deg,rgba(180,20,20,.07) 0%,transparent 100%);border-bottom:1px solid var(--c-b);text-align:center;position:relative;z-index:1}
.hmb-icon{font-size:2rem;display:block;margin-bottom:.75rem;animation:hmbBr 4s ease-in-out infinite}
@keyframes hmbBr{0%,100%{filter:drop-shadow(0 0 6px rgba(180,20,20,.3))}50%{filter:drop-shadow(0 0 16px rgba(180,20,20,.65))}}
.hmb-title{font-family:var(--fd);font-size:1.2rem;font-weight:700;color:var(--c-th);margin:0 0 .35rem;letter-spacing:.05em;text-shadow:0 2px 20px rgba(180,20,20,.35)}
.hmb-sub{font-family:var(--fh);font-size:.58rem;color:var(--c-g);letter-spacing:.25em;text-transform:uppercase;margin:0 0 .75rem;opacity:.75}
.hmb-desc{font-size:.82rem;color:var(--c-sv);line-height:1.6;margin:0}

/* Body */
.hmb-body{padding:1.75rem 2.5rem 2rem;position:relative;z-index:1}

/* Alert Joomla */
.hmb-page .alert{border-radius:var(--r);padding:.7rem .9rem;font-size:.82rem;margin-bottom:1.1rem;border:1px solid}
.hmb-page .alert-error,.hmb-page .alert-danger{background:rgba(180,20,20,.1);border-color:rgba(180,20,20,.4);color:#ff7070}
.hmb-page .alert-success,.hmb-page .alert-info{background:rgba(30,120,60,.1);border-color:rgba(30,120,60,.4);color:#66d98e}

/* Field */
.hmb-field{display:flex;flex-direction:column;gap:.32rem;margin-bottom:1rem}
.hmb-label{font-family:var(--fh);font-size:.6rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;color:var(--c-sv)}
.hmb-input{width:100%;box-sizing:border-box;background:rgba(255,255,255,.03);border:1px solid var(--c-b);border-radius:var(--r);color:var(--c-th);font-family:var(--fb);font-size:.9rem;font-weight:300;padding:.65rem .9rem;transition:border-color var(--sp) var(--e),background var(--sp) var(--e),box-shadow var(--sp) var(--e);-webkit-appearance:none;appearance:none}
.hmb-input::placeholder{color:rgba(136,136,136,.28);font-style:italic}
.hmb-input:focus{outline:none;border-color:var(--c-r);background:rgba(180,20,20,.05);box-shadow:0 0 0 3px rgba(180,20,20,.12)}
.hmb-input:-webkit-autofill,.hmb-input:-webkit-autofill:focus{-webkit-text-fill-color:var(--c-th);-webkit-box-shadow:0 0 0 1000px #120202 inset}

/* Button */
.hmb-btn-submit{display:flex;align-items:center;justify-content:center;gap:.6rem;width:100%;padding:.82rem;background:linear-gradient(160deg,#c01010 0%,#7a0a0a 100%);border:1px solid rgba(180,20,20,.5);border-radius:var(--r);color:var(--c-th);font-family:var(--fh);font-size:.72rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;cursor:pointer;position:relative;overflow:hidden;white-space:normal;text-align:center;transition:transform var(--sp) var(--e),box-shadow var(--sp) var(--e),filter var(--sp) var(--e)}
.hmb-btn-submit::after{content:'';position:absolute;top:0;left:-110%;width:60%;height:100%;background:linear-gradient(105deg,transparent 30%,rgba(255,255,255,.08) 50%,transparent 70%);transition:left .5s var(--e)}
.hmb-btn-submit:hover::after{left:150%}
.hmb-btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(180,20,20,.45);filter:brightness(1.12)}
.hmb-btn-submit:active{transform:none;box-shadow:none}

/* Footer */
.hmb-footer{padding:.9rem 2.5rem 1.35rem;border-top:1px solid var(--c-b);background:rgba(0,0,0,.2);position:relative;z-index:1}
.hmb-switch-links{display:flex;flex-direction:column;gap:.5rem}
.hmb-switch-link{display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--c-sv);text-decoration:none;transition:color var(--sp) var(--e);padding:.2rem 0}
.hmb-switch-link:hover{color:var(--c-th)}
.hmb-switch-link.primary{color:var(--c-rh);font-weight:600}
.hmb-switch-link.primary:hover{color:var(--c-g)}
.hmb-bullet{font-size:.45rem;color:var(--c-r);flex-shrink:0}

@media(max-width:520px){
    .hmb-hd,.hmb-body{padding-left:1.5rem;padding-right:1.5rem}
    .hmb-footer{padding-left:1.5rem;padding-right:1.5rem}
    .hmb-title{font-size:1rem}
}
</style>

<div class="hmb-page">
<div class="hmb-card">

    <span class="hmb-cn tl"></span>
    <span class="hmb-cn tr"></span>
    <span class="hmb-cn bl"></span>
    <span class="hmb-cn br"></span>

    <!-- HEADER -->
    <div class="hmb-hd">
        <span class="hmb-icon">&#128274;</span>
        <h1 class="hmb-title">Contraseña Olvidada</h1>
        <p class="hmb-sub">Recupera tu acceso</p>
        <p class="hmb-desc">Introduce tu email y te enviaremos un enlace para restablecer tu contraseña.</p>
    </div>

    <!-- BODY -->
    <div class="hmb-body">

        <!-- Mensajes de Joomla -->
        <?php
        $app = \Joomla\CMS\Factory::getApplication();
        if ($app->getMessageQueue()) :
            foreach ($app->getMessageQueue() as $msg) : ?>
            <div class="alert alert-<?php echo htmlspecialchars($msg['type']); ?>">
                <?php echo $msg['message']; ?>
            </div>
        <?php endforeach; endif; ?>

        <form action="<?php echo Route::_('index.php?option=com_users&task=reset.request'); ?>"
            method="post" class="validate">

            <div class="hmb-field">
                <label class="hmb-label" for="jform_email">
                    Correo electrónico <span style="color:#e02020">*</span>
                </label>
                <input type="email" id="jform_email" name="jform[email]"
                    class="hmb-input validate-email required"
                    placeholder="tu@correo.com"
                    required autocomplete="email">
            </div>

            <button type="submit" class="hmb-btn-submit">
                &#9993; Enviar enlace
            </button>

            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>

    <!-- FOOTER: links de navegación -->
    <div class="hmb-footer">
        <div class="hmb-switch-links">
            <a class="hmb-switch-link" href="https://heavymetalbrothers.com/index.php/registro/recordar-usuario">
                <span class="hmb-bullet">&#9670;</span>
                ¿No recuerdas tu usuario? Recupéralo aquí
            </a>
            <a class="hmb-switch-link" href="https://heavymetalbrothers.com/index.php/registro">
                <span class="hmb-bullet">&#9670;</span>
                ¿Aún no tienes cuenta? Únete a la Hermandad
            </a>
            <a class="hmb-switch-link primary" href="https://heavymetalbrothers.com/index.php/registro">
                <span class="hmb-bullet">&#9670;</span>
                &#9664; Volver al inicio de sesión
            </a>
        </div>
    </div>

</div>
</div>
