<?php
/**
 * Heavy Metal Brothers — Editar Perfil  v1.0
 * Sube a: templates/rt_remnant/html/com_users/profile/edit.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$app  = Factory::getApplication();
$user = $app->getIdentity();

// Cargar validación de Joomla
HTMLHelper::_('behavior.formvalidator');

// Solo mostrar el fieldset 'core' (Perfil)
// Los fieldsets que queremos EXCLUIR:
$exclude = ['params', 'registration', 'webauthn', 'passkeys', 'mfa', 'multifactorauth',
            'user_mfa', 'com_users', 'joomlatoken', 'passwordreset', 'webservices'];

$form      = $this->form;
$fieldsets = $form->getFieldsets();

// Filtrar solo 'core' y campos sin fieldset conocido
$allowed = [];
foreach ($fieldsets as $name => $fieldset) {
    $nameLower = strtolower($name);
    $skip = false;
    foreach ($exclude as $ex) {
        if (str_contains($nameLower, strtolower($ex))) { $skip = true; break; }
    }
    if (!$skip) $allowed[$name] = $fieldset;
}
?>
<style>

/* ── Tokens ── */
.hmb-edit{--c-s:var(--dark, #0A0A0A);--c-b:var(--steel, #1A1A1A);--c-r:var(--fire, #CC2200);--c-rh:var(--fire, #CC2200);--c-g:var(--gold-light, #DAA520);--c-gd:var(--gold-dim, #7A6030);--c-sv:var(--grey-8, #888888);--c-t:#d4d4d4;--c-th:var(--cream, #E8E0D5);--fd:'Cinzel Decorative','Cinzel',serif;--fh:'Cinzel',serif;--fb:var(--font-body, 'Rajdhani', sans-serif);--r:3px;--e:cubic-bezier(.4,0,.2,1);--sp:.22s;font-family:var(--fb);color:var(--c-t);max-width:680px;margin:2rem auto;padding:0 1rem}

/* ── Card ── */
.hmb-card{position:relative;background:var(--c-s);border:1px solid var(--c-b);border-radius:var(--r);box-shadow:0 0 0 1px rgba(255,255,255,.03) inset,0 24px 64px rgba(0,0,0,.85),0 0 80px rgba(180,20,20,.06)}
.hmb-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--wine, #3A0000) 0%,var(--c-r) 30%,var(--c-g) 50%,var(--c-r) 70%,var(--wine, #3A0000) 100%);background-size:200% 100%;animation:hmbBar 4s ease infinite;z-index:2;border-radius:var(--r) var(--r) 0 0}
@keyframes hmbBar{0%{background-position:0 50%}50%{background-position:100% 50%}100%{background-position:0 50%}}
.hmb-cn{position:absolute;width:18px;height:18px;border-color:var(--c-gd);border-style:solid;opacity:.4;z-index:3}
.hmb-cn.tl{top:8px;left:8px;border-width:1px 0 0 1px}.hmb-cn.tr{top:8px;right:8px;border-width:1px 1px 0 0}
.hmb-cn.bl{bottom:8px;left:8px;border-width:0 0 1px 1px}.hmb-cn.br{bottom:8px;right:8px;border-width:0 1px 1px 0}

/* ── Header ── */
.hmb-hd{padding:2rem 2.5rem 1.75rem;background:linear-gradient(180deg,rgba(180,20,20,.07) 0%,transparent 100%);border-bottom:1px solid var(--c-b);text-align:center;position:relative;z-index:1}
.hmb-skull{font-size:1.5rem;display:block;margin-bottom:.7rem;animation:hmbBr 4s ease-in-out infinite}
@keyframes hmbBr{0%,100%{filter:drop-shadow(0 0 5px rgba(180,20,20,.3))}50%{filter:drop-shadow(0 0 13px rgba(180,20,20,.7))}}
.hmb-title{font-family:var(--fd);font-size:1.25rem;font-weight:700;color:var(--c-th);margin:0 0 .3rem;letter-spacing:.05em;text-shadow:0 2px 20px rgba(180,20,20,.35)}
.hmb-sub{font-family:var(--fh);font-size:.6rem;color:var(--c-g);letter-spacing:.28em;text-transform:uppercase;margin:0;opacity:.75}

/* ── Body ── */
.hmb-body{padding:2rem 2.5rem 2.25rem;position:relative;z-index:1}
.hmb-sec{font-family:var(--fh);font-size:.6rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--c-sv);margin:0 0 1.25rem;padding-bottom:.6rem;border-bottom:1px solid var(--c-b)}
.hmb-fields{display:flex;flex-direction:column;gap:.9rem}

/* ── Field override — neutraliza estilos de Joomla ── */
.hmb-edit .control-group{margin:0;padding:0;border:none}
.hmb-edit .control-group .control-label{display:block;font-family:var(--fh);font-size:.6rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;color:var(--c-sv);margin-bottom:.3rem;padding:0;float:none;width:auto;text-align:left}
.hmb-edit .control-group .control-label label{color:inherit;font:inherit;letter-spacing:inherit}
.hmb-edit .controls{margin:0;float:none;width:auto}
.hmb-edit .controls input[type=text],
.hmb-edit .controls input[type=email],
.hmb-edit .controls input[type=password],
.hmb-edit .controls input[type=username],
.hmb-edit .controls textarea,
.hmb-edit .controls select{width:100%;box-sizing:border-box;background:rgba(255,255,255,.03);border:1px solid var(--c-b);border-radius:var(--r);color:var(--c-th);font-family:var(--fb);font-size:.875rem;font-weight:300;padding:.62rem .88rem;transition:border-color var(--sp) var(--e),background var(--sp) var(--e),box-shadow var(--sp) var(--e);-webkit-appearance:none;appearance:none}
.hmb-edit .controls input:focus,
.hmb-edit .controls textarea:focus,
.hmb-edit .controls select:focus{outline:none;border-color:var(--c-r);background:rgba(180,20,20,.05);box-shadow:0 0 0 3px rgba(180,20,20,.12)}
.hmb-edit .controls input:-webkit-autofill,
.hmb-edit .controls input:-webkit-autofill:focus{-webkit-text-fill-color:var(--c-th);-webkit-box-shadow:0 0 0 1000px var(--wine-black, #0A0000) inset}
.hmb-edit .controls input::placeholder{color:rgba(136,136,136,.28);font-style:italic}

/* Ocultar description/hint innecesarios de Joomla */
.hmb-edit .controls .form-text,
.hmb-edit .controls small.form-text,
.hmb-edit .controls .help-block{display:none}

/* Strength meter contraseña */
.hmb-edit #password-strength-text,
.hmb-edit .password-strength{font-family:var(--fh);font-size:.58rem;letter-spacing:.1em;text-transform:uppercase;color:var(--c-sv);margin-top:.3rem}

/* ── Buttons ── */
.hmb-actions{padding:1.1rem 2.5rem 1.5rem;border-top:1px solid var(--c-b);background:rgba(0,0,0,.2);display:flex;gap:.9rem;flex-wrap:wrap;align-items:center;position:relative;z-index:1}
.hmb-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1.5rem;border-radius:var(--r);font-family:var(--fh);font-size:.68rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;text-decoration:none;cursor:pointer;border:none;position:relative;overflow:hidden;transition:transform var(--sp) var(--e),box-shadow var(--sp) var(--e),filter var(--sp) var(--e)}
.hmb-btn::after{content:'';position:absolute;top:0;left:-110%;width:60%;height:100%;background:linear-gradient(105deg,transparent 30%,rgba(255,255,255,.08) 50%,transparent 70%);transition:left .5s var(--e)}
.hmb-btn:hover::after{left:150%}
.hmb-btn-p{background:linear-gradient(160deg,var(--fire, #CC2200) 0%,var(--blood, #8B0000) 100%);border:1px solid rgba(180,20,20,.5);color:var(--c-th)}
.hmb-btn-p:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(180,20,20,.4);filter:brightness(1.1);color:var(--c-th);text-decoration:none}
.hmb-btn-s{background:rgba(255,255,255,.04);border:1px solid var(--c-b);color:var(--c-sv)}
.hmb-btn-s:hover{border-color:#404040;color:var(--c-th);transform:translateY(-1px);text-decoration:none}

/* ── Responsive ── */
@media(max-width:580px){
    .hmb-hd,.hmb-body{padding-left:1.5rem;padding-right:1.5rem}
    .hmb-actions{padding:1rem 1.5rem 1.4rem}
    .hmb-title{font-size:1rem}
}
</style>

<div class="hmb-edit">
<div class="hmb-card">

    <span class="hmb-cn tl"></span>
    <span class="hmb-cn tr"></span>
    <span class="hmb-cn bl"></span>
    <span class="hmb-cn br"></span>

    <!-- HEADER -->
    <div class="hmb-hd">
        <span class="hmb-skull">&#9760;</span>
        <h1 class="hmb-title">Editar Perfil</h1>
        <p class="hmb-sub">Actualiza tus datos</p>
    </div>

    <!-- FORM -->
    <form action="<?php echo Route::_('index.php?option=com_users&task=profile.save'); ?>"
        method="post" id="member-profile" class="validate" enctype="multipart/form-data">

        <div class="hmb-body">

            <?php foreach ($allowed as $fsName => $fieldset) :
                // Título del fieldset (solo si no es 'core')
                $fsLabel = !empty($fieldset->label) ? Text::_($fieldset->label) : '';
                $fields  = $form->getFieldset($fsName);
                if (empty($fields)) continue;
            ?>

            <?php if ($fsLabel && strtolower($fsName) !== 'core') : ?>
            <p class="hmb-sec" style="margin-top:1.5rem"><?php echo htmlspecialchars($fsLabel); ?></p>
            <?php else : ?>
            <p class="hmb-sec">Perfil</p>
            <?php endif; ?>

            <div class="hmb-fields">
            <?php
            // Campos a excluir por nombre
            $skipFields = ['sendEmail', 'activation', 'block', 'groups', 'lastResetTime', 'resetCount', 'requireReset'];

            foreach ($fields as $field) :
                // Saltar campos hidden (se incluyen más abajo)
                if ($field->hidden) : ?>
                    <?php echo $field->input; ?>
                <?php continue; endif;
                // Saltar campos no deseados
                if (in_array($field->fieldname, $skipFields)) continue;
            ?>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $field->label; ?>
                    </div>
                    <div class="controls">
                        <?php echo $field->input; ?>
                    </div>
                </div>

            <?php endforeach; ?>
            </div>

            <?php endforeach; ?>

        </div><!-- .hmb-body -->

        <!-- ACTIONS -->
        <div class="hmb-actions">
            <button type="submit" class="hmb-btn hmb-btn-p">
                &#10003; Guardar cambios
            </button>
            <a class="hmb-btn hmb-btn-s"
                href="<?php echo Uri::root(); ?>index.php/registro/perfil-usuario">
                &#9664; Volver al perfil
            </a>
        </div>

        <!-- Tokens y campos ocultos necesarios de Joomla -->
        <input type="hidden" name="option"  value="com_users">
        <input type="hidden" name="task"    value="profile.save">
        <input type="hidden" name="user_id" value="<?php echo (int)$user->id; ?>">
        <?php echo HTMLHelper::_('form.token'); ?>

    </form>

</div>
</div>
