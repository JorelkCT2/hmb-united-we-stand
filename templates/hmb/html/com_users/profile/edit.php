<?php
/**
 * Heavy Metal Brothers — Editar Perfil
 * templates/hmb_template/html/com_users/profile/edit.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');

$app         = Factory::getApplication();
$currentUser = $app->getIdentity();
$userId      = $app->getInput()->getInt('user_id', $currentUser->id);
$user        = \Joomla\CMS\User\User::getInstance($userId);

// Avatar actual
$userParams = new \Joomla\Registry\Registry($user->params);
$avatarPath = $userParams->get('avatar', '');
$avatarUrl  = !empty($avatarPath)
    ? Uri::root() . ltrim($avatarPath, '/')
    : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=120&d=mp';

// Subida de avatar desde esta misma página
$avatarMsg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['hmb_avatar_edit']['name'])) {
    $f = $_FILES['hmb_avatar_edit'];
    if ($f['error'] === UPLOAD_ERR_OK && in_array($f['type'], ['image/jpeg','image/png','image/gif','image/webp']) && $f['size'] <= 2097152) {
        $ext  = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $dir  = JPATH_ROOT . '/images/hmb_avatars/';
        $name = 'user_' . $user->id . '.' . $ext;
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        if (move_uploaded_file($f['tmp_name'], $dir . $name)) {
            $userParams->set('avatar', 'images/hmb_avatars/' . $name);
            $db = Factory::getDbo();
            $db->setQuery($db->getQuery(true)
                ->update($db->qn('#__users'))
                ->set($db->qn('params') . ' = ' . $db->q((string)$userParams))
                ->where($db->qn('id') . ' = ' . (int)$user->id)
            )->execute();
            $avatarUrl = Uri::root() . 'images/hmb_avatars/' . $name . '?t=' . time();
            $avatarMsg = ['ok' => true, 'txt' => 'Foto actualizada.'];
        }
    } else {
        $avatarMsg = ['ok' => false, 'txt' => 'Imagen no válida (máx 2MB, JPG/PNG/GIF/WEBP).'];
    }
}

// Guardar preferencia de Metal Quiz
$quizMsg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hmb_save_quiz'])) {
    $userParams->set('metal_quiz_invitations', !empty($_POST['metal_quiz_invitations']) ? 1 : 0);
    $db = Factory::getDbo();
    $db->setQuery($db->getQuery(true)
        ->update($db->qn('#__users'))
        ->set($db->qn('params') . ' = ' . $db->q((string)$userParams))
        ->where($db->qn('id') . ' = ' . (int)$user->id)
    )->execute();
    $quizMsg = ['ok' => true, 'txt' => 'Preferencia de Metal Quiz actualizada.'];
}

$quizEnabled = (bool) $userParams->get('metal_quiz_invitations', 1);

// Campos a ocultar del formulario de Joomla
$skipFields = [
    'sendEmail','activation','block','groups',
    'lastResetTime','resetCount','requireReset',
    'timezone','language','editor','helpsite',
    'flushExpiredUserTokens','darkMode','a2',
    'params','notificationEmail','task',
];
// Fieldsets a ocultar completamente (todo lo que no sea core/nombre/email/contraseña)
$skipFieldsets = [
    'passkeys','webauthn','mfa','multifactorauth',
    'joomlatoken','params','settings','preferences',
    'basic','accessibility','display',
];

$form = $this->form;
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Cinzel:wght@400;600&family=Raleway:wght@300;400;500;600&display=swap');
.hmb-ed{--r:#b41414;--g:#c9a84c;--gd:#7a6030;--b:#222;--s:#0f0f0f;--t:#d4d4d4;--w:#fff;--sv:#888;--ra:3px;--e:cubic-bezier(.4,0,.2,1);--sp:.2s;font-family:'Raleway',sans-serif;color:var(--t);max-width:580px;margin:2rem auto;padding:0 1rem}
.hmb-card{position:relative;background:var(--s);border:1px solid var(--b);border-radius:var(--ra);overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.8),0 0 60px rgba(180,20,20,.06)}
.hmb-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#3a0000,var(--r) 30%,var(--g) 50%,var(--r) 70%,#3a0000);background-size:200% 100%;animation:hmbB 4s ease infinite;z-index:2}
@keyframes hmbB{0%{background-position:0 50%}50%{background-position:100% 50%}100%{background-position:0 50%}}
.hmb-cn{position:absolute;width:16px;height:16px;border-color:var(--gd);border-style:solid;opacity:.4;z-index:3}
.hmb-cn.a{top:8px;left:8px;border-width:1px 0 0 1px}.hmb-cn.b{top:8px;right:8px;border-width:1px 1px 0 0}
.hmb-cn.c{bottom:8px;left:8px;border-width:0 0 1px 1px}.hmb-cn.d{bottom:8px;right:8px;border-width:0 1px 1px 0}
.hmb-hd{padding:2rem 2rem 1.6rem;background:linear-gradient(180deg,rgba(180,20,20,.07),transparent);border-bottom:1px solid var(--b);text-align:center;position:relative;z-index:1}
.hmb-sk{font-size:1.4rem;display:block;margin-bottom:.6rem;animation:hmbBr 4s ease-in-out infinite}
@keyframes hmbBr{0%,100%{filter:drop-shadow(0 0 5px rgba(180,20,20,.3))}50%{filter:drop-shadow(0 0 12px rgba(180,20,20,.65))}}
.hmb-tt{font-family:'Cinzel Decorative','Cinzel',serif;font-size:1.15rem;font-weight:700;color:var(--w);margin:0 0 .25rem;text-shadow:0 2px 16px rgba(180,20,20,.3)}
.hmb-sb{font-family:'Cinzel',serif;font-size:.58rem;color:var(--g);letter-spacing:.25em;text-transform:uppercase;margin:0;opacity:.75}
/* Avatar section */
.hmb-av-sec{padding:1.5rem 2rem;border-bottom:1px solid var(--b);position:relative;z-index:1;display:flex;align-items:center;gap:1.5rem}
.hmb-av-img{width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid var(--gd);box-shadow:0 0 16px rgba(180,20,20,.2);flex-shrink:0}
.hmb-av-info{flex:1}
.hmb-av-lbl{display:flex;align-items:center;gap:.4rem;padding:.45rem .8rem;background:rgba(255,255,255,.04);border:1px dashed var(--b);border-radius:var(--ra);font-size:.75rem;color:var(--sv);cursor:pointer;transition:border-color var(--sp) var(--e);width:fit-content}
.hmb-av-lbl:hover{border-color:var(--r);color:var(--t)}
.hmb-av-fname{font-size:.7rem;color:var(--sv);margin-top:.3rem}
.hmb-av-upbtn{margin-top:.4rem;display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .9rem;background:linear-gradient(160deg,#c01010,#7a0a0a);border:1px solid rgba(180,20,20,.5);border-radius:var(--ra);color:#fff;font-family:'Cinzel',serif;font-size:.58rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;transition:filter var(--sp) var(--e)}
.hmb-av-upbtn:hover{filter:brightness(1.1)}
/* Alert */
.hmb-alert{margin:0 2rem .5rem;padding:.6rem .85rem;border-radius:var(--ra);font-size:.8rem;border:1px solid;line-height:1.5;position:relative;z-index:1}
.ok{background:rgba(30,120,60,.1);border-color:rgba(30,120,60,.4);color:#66d98e}
.er{background:rgba(180,20,20,.1);border-color:rgba(180,20,20,.4);color:#ff7070}
/* Form body */
.hmb-body{padding:1.5rem 2rem 2rem;position:relative;z-index:1;display:flex;flex-direction:column;gap:.85rem}
.hmb-ed .control-group{margin:0;padding:0;border:none;float:none;width:auto}
.hmb-ed .control-group:after{content:'';display:table;clear:both}
.hmb-ed .control-label{display:block;font-family:'Cinzel',serif;font-size:.58rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--sv);margin-bottom:.3rem;padding:0;float:none;width:auto;text-align:left}
.hmb-ed .control-label label{color:inherit;font:inherit;letter-spacing:inherit;margin:0}
.hmb-ed .controls{float:none;width:auto;margin:0}
.hmb-ed .controls input[type=text],
.hmb-ed .controls input[type=email],
.hmb-ed .controls input[type=password],
.hmb-ed .controls textarea{width:100%;box-sizing:border-box;background:rgba(255,255,255,.04);border:1px solid var(--b);border-radius:var(--ra);color:var(--w) !important;font-family:'Raleway',sans-serif;font-size:.88rem;font-weight:500 !important;padding:.6rem .85rem;transition:border-color var(--sp) var(--e),box-shadow var(--sp) var(--e);-webkit-appearance:none}
.hmb-ed .controls input:focus{outline:none;border-color:var(--r);box-shadow:0 0 0 3px rgba(180,20,20,.12)}
.hmb-ed .controls input:-webkit-autofill,.hmb-ed .controls input:-webkit-autofill:focus{-webkit-text-fill-color:var(--w) !important;-webkit-box-shadow:0 0 0 1000px #120202 inset}
.hmb-ed .controls input::placeholder{color:rgba(136,136,136,.28);font-style:italic}
.hmb-ed .controls .form-text,.hmb-ed .controls small,.hmb-ed .controls .help-block{display:none}
.hmb-ed #password-strength-text{font-family:'Cinzel',serif;font-size:.55rem;letter-spacing:.1em;text-transform:uppercase;color:var(--sv);margin-top:.25rem}
/* Actions */
.hmb-actions{padding:1rem 2rem 1.5rem;border-top:1px solid var(--b);display:flex;gap:.75rem;flex-wrap:wrap;position:relative;z-index:1}
.hmb-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.65rem 1.25rem;border-radius:var(--ra);font-family:'Cinzel',serif;font-size:.62rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;text-decoration:none;cursor:pointer;border:none;transition:transform var(--sp) var(--e),box-shadow var(--sp) var(--e),filter var(--sp) var(--e)}
.hmb-btn-p{background:linear-gradient(160deg,#c01010,#7a0a0a);border:1px solid rgba(180,20,20,.5);color:#fff}
.hmb-btn-p:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(180,20,20,.4);filter:brightness(1.1);color:#fff;text-decoration:none}
.hmb-btn-s{background:rgba(255,255,255,.04);border:1px solid var(--b);color:var(--sv)}
.hmb-btn-s:hover{border-color:#404040;color:var(--w);transform:translateY(-1px);text-decoration:none}
@media(max-width:500px){.hmb-hd,.hmb-body,.hmb-actions,.hmb-av-sec,.hmb-alert,.hmb-quiz-sec{padding-left:1.5rem;padding-right:1.5rem}}

/* Metal Quiz section */
.hmb-quiz-sec{padding:1.25rem 2rem;border-bottom:1px solid var(--b);position:relative;z-index:1;background:linear-gradient(135deg,rgba(180,20,20,.04),rgba(201,168,76,.02))}
.hmb-quiz-check-ed{display:flex;align-items:flex-start;gap:.7rem;cursor:pointer;margin-bottom:.75rem}
.hmb-quiz-check-ed input[type=checkbox]{position:absolute;opacity:0;width:0;height:0}
.hmb-quiz-box-ed{width:17px;height:17px;border:1.5px solid var(--b);border-radius:2px;background:rgba(0,0,0,.4);flex-shrink:0;position:relative;margin-top:2px;transition:border-color var(--sp) var(--e),background var(--sp) var(--e)}
.hmb-quiz-box-ed::after{content:'';position:absolute;width:5px;height:9px;top:1px;left:4px;border:2px solid #e02020;border-top:none;border-left:none;transform:rotate(45deg) scale(0);transition:transform .15s var(--e)}
.hmb-quiz-check-ed input:checked+.hmb-quiz-box-ed{background:rgba(180,20,20,.18);border-color:var(--r)}
.hmb-quiz-check-ed input:checked+.hmb-quiz-box-ed::after{transform:rotate(45deg) scale(1)}
.hmb-quiz-txt-ed{display:flex;flex-direction:column;gap:.15rem;line-height:1.35}
.hmb-quiz-txt-ed strong{font-family:'Cinzel',serif;font-size:.7rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:#fff}
.hmb-quiz-txt-ed small{font-size:.72rem;color:var(--sv);font-weight:300}
.hmb-quiz-savebtn{display:inline-flex;align-items:center;gap:.35rem;padding:.45rem .9rem;background:rgba(180,20,20,.15);border:1px solid rgba(180,20,20,.4);border-radius:var(--ra);color:#ff8080;font-family:'Cinzel',serif;font-size:.58rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;transition:filter var(--sp) var(--e)}
.hmb-quiz-savebtn:hover{filter:brightness(1.15)}
</style>

<div class="hmb-ed">
<div class="hmb-card">
<span class="hmb-cn a"></span><span class="hmb-cn b"></span>
<span class="hmb-cn c"></span><span class="hmb-cn d"></span>

<div class="hmb-hd">
    <span class="hmb-sk">&#9760;</span>
    <h1 class="hmb-tt">Editar Perfil</h1>
    <p class="hmb-sb">Actualiza tus datos</p>
</div>

<!-- FOTO DE PERFIL -->
<?php if ($avatarMsg): ?>
<div class="hmb-alert <?php echo $avatarMsg['ok'] ? 'ok' : 'er'; ?>">
    <?php echo htmlspecialchars($avatarMsg['txt']); ?>
</div>
<?php endif; ?>

<div class="hmb-av-sec">
    <img id="hmb-ed-av" class="hmb-av-img"
        src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES); ?>"
        alt="Avatar" width="72" height="72">
    <div class="hmb-av-info">
        <form method="post" enctype="multipart/form-data"
            action="<?php echo htmlspecialchars(Uri::current(), ENT_QUOTES); ?>">
            <input type="hidden" name="<?php echo Session::getFormToken(); ?>" value="1">
            <input type="hidden" name="user_id" value="<?php echo (int)$userId; ?>">
            <label class="hmb-av-lbl" for="hmb-av-file-ed">&#128247; Cambiar foto</label>
            <input type="file" id="hmb-av-file-ed" name="hmb_avatar_edit"
                style="display:none" accept="image/jpeg,image/png,image/gif,image/webp"
                onchange="
                    document.getElementById('hmb-av-fn-ed').textContent=this.files[0]?.name||'';
                    if(this.files[0]){
                        var r=new FileReader();
                        r.onload=function(e){document.getElementById('hmb-ed-av').src=e.target.result};
                        r.readAsDataURL(this.files[0]);
                    }">
            <div id="hmb-av-fn-ed" class="hmb-av-fname"></div>
            <button type="submit" class="hmb-av-upbtn">&#8593; Subir</button>
        </form>
    </div>
</div>

<!-- METAL QUIZ -->
<?php if ($quizMsg): ?>
<div class="hmb-alert <?php echo $quizMsg['ok'] ? 'ok' : 'er'; ?>">
    <?php echo htmlspecialchars($quizMsg['txt']); ?>
</div>
<?php endif; ?>
<div class="hmb-quiz-sec">
    <form method="post" action="<?php echo htmlspecialchars(Uri::current(), ENT_QUOTES); ?>">
        <input type="hidden" name="<?php echo Session::getFormToken(); ?>" value="1">
        <input type="hidden" name="user_id" value="<?php echo (int)$userId; ?>">
        <input type="hidden" name="hmb_save_quiz" value="1">
        <label class="hmb-quiz-check-ed" for="hmb-quiz-ed">
            <input type="checkbox" id="hmb-quiz-ed" name="metal_quiz_invitations"
                value="1" <?php echo $quizEnabled ? 'checked' : ''; ?>>
            <span class="hmb-quiz-box-ed" aria-hidden="true"></span>
            <span class="hmb-quiz-txt-ed">
                <strong>🎸 Metal Quiz</strong>
                <small>Recibir y enviar invitaciones para jugar contra otros usuarios</small>
            </span>
        </label>
        <button type="submit" class="hmb-quiz-savebtn">&#10003; Guardar preferencia</button>
    </form>
</div>

<!-- DATOS DEL PERFIL -->
<form method="post"
    action="<?php echo Route::_('index.php?option=com_users&task=profile.save'); ?>"
    id="member-profile" class="validate" enctype="multipart/form-data">

    <div class="hmb-body">
    <?php
    foreach ($form->getFieldsets() as $fsName => $fieldset) {
        // Solo mostrar el fieldset 'core' — ignorar todo lo demás
        if (strtolower($fsName) !== 'core') continue;

        $fields = $form->getFieldset($fsName);
        if (empty($fields)) continue;

        foreach ($fields as $field) {
            if ($field->hidden) { echo $field->input; continue; }
            if (in_array($field->fieldname, $skipFields)) continue;
            ?>
            <div class="control-group">
                <div class="control-label"><?php echo $field->label; ?></div>
                <div class="controls"><?php echo $field->input; ?></div>
            </div>
            <?php
        }
    }
    ?>
    </div>

    <div class="hmb-actions">
        <button type="submit" class="hmb-btn hmb-btn-p">&#10003; Guardar</button>
        <a class="hmb-btn hmb-btn-s"
            href="<?php echo Uri::root(); ?>index.php/registro/perfil-usuario">
            &#9664; Volver
        </a>
    </div>

    <input type="hidden" name="option"  value="com_users">
    <input type="hidden" name="task"    value="profile.save">
    <input type="hidden" name="user_id" value="<?php echo (int)$userId; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

</div>
</div>
