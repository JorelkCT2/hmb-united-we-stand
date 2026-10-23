<?php
/**
 * Heavy Metal Brothers — Perfil de Usuario  v2.0
 * Sube a: templates/rt_remnant/html/com_users/profile/default.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$app         = Factory::getApplication();
$input       = $app->getInput();
$currentUser = $app->getIdentity();

// Obtener usuario desde la BD (no desde $this->item que puede estar vacío)
$userId = $input->getInt('user_id', 0) ?: $currentUser->id;
$user   = \Joomla\CMS\User\User::getInstance($userId);

if (!$user || !$user->id) {
    echo '<p style="color:#ff7070;padding:2rem;font-family:sans-serif;">Usuario no encontrado.</p>';
    return;
}

$isOwn   = ($currentUser->id == $user->id);
$canEdit = $isOwn || $currentUser->authorise('core.edit', 'com_users');

// Avatar: params del usuario o Gravatar
$userParams = new \Joomla\Registry\Registry($user->params);
$avatarPath = $userParams->get('avatar', '');
$avatarUrl  = !empty($avatarPath)
    ? Uri::root() . ltrim($avatarPath, '/')
    : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=160&d=mp';

// Fecha de registro
$registerDate = '';
if (!empty($user->registerDate) && $user->registerDate !== '0000-00-00 00:00:00') {
    try { $registerDate = (new DateTime($user->registerDate))->format('d/m/Y'); } catch (Exception $e) {}
}

// Procesar subida de avatar
$uploadMsg = null;
if ($isOwn && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['hmb_avatar']['name'])) {
    $file    = $_FILES['hmb_avatar'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!in_array($file['type'], $allowed)) {
        $uploadMsg = ['ok' => false, 'msg' => 'Solo se permiten imagenes JPG, PNG, GIF o WEBP.'];
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $uploadMsg = ['ok' => false, 'msg' => 'La imagen no puede superar 2 MB.'];
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadMsg = ['ok' => false, 'msg' => 'Error al recibir el archivo (codigo ' . $file['error'] . ').'];
    } else {
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $dir  = JPATH_ROOT . '/images/hmb_avatars/';
        $name = 'user_' . $user->id . '.' . $ext;

        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }

        if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
            $relPath = 'images/hmb_avatars/' . $name;
            $userParams->set('avatar', $relPath);
            $db = Factory::getDbo();
            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->qn('#__users'))
                    ->set($db->qn('params') . ' = ' . $db->q((string) $userParams))
                    ->where($db->qn('id') . ' = ' . (int) $user->id)
            )->execute();
            $avatarUrl = Uri::root() . $relPath . '?t=' . time();
            $uploadMsg = ['ok' => true, 'msg' => 'Foto actualizada correctamente.'];
        } else {
            $uploadMsg = ['ok' => false, 'msg' => 'No se pudo guardar la imagen. Verifica permisos en /images/hmb_avatars/'];
        }
    }
}
?>
<style>

.hmb-profile{--c-s:#0f0f0f;--c-b:#252525;--c-r:#b41414;--c-rh:#e02020;--c-g:#c9a84c;--c-gd:#7a6030;--c-sv:#888;--c-m:#484848;--c-t:#d4d4d4;--c-th:#f0ebe0;--fd:'Cinzel Decorative','Cinzel',serif;--fh:'Cinzel',serif;--fb:var(--font-body, 'Rajdhani', sans-serif);--r:3px;--e:cubic-bezier(.4,0,.2,1);--sp:.22s;font-family:var(--fb);color:var(--c-t);max-width:680px;margin:2rem auto;padding:0 1rem}
.hmb-pc{position:relative;background:var(--c-s);border:1px solid var(--c-b);border-radius:var(--r);box-shadow:0 0 0 1px rgba(255,255,255,.03) inset,0 24px 64px rgba(0,0,0,.85),0 0 80px rgba(180,20,20,.06)}
.hmb-pc::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#3a0000 0%,var(--c-r) 30%,var(--c-g) 50%,var(--c-r) 70%,#3a0000 100%);background-size:200% 100%;animation:hmbBar 4s ease infinite;z-index:2;border-radius:var(--r) var(--r) 0 0}
@keyframes hmbBar{0%{background-position:0 50%}50%{background-position:100% 50%}100%{background-position:0 50%}}
.hmb-cn{position:absolute;width:18px;height:18px;border-color:var(--c-gd);border-style:solid;opacity:.4;z-index:3}
.hmb-cn.tl{top:8px;left:8px;border-width:1px 0 0 1px}.hmb-cn.tr{top:8px;right:8px;border-width:1px 1px 0 0}.hmb-cn.bl{bottom:8px;left:8px;border-width:0 0 1px 1px}.hmb-cn.br{bottom:8px;right:8px;border-width:0 1px 1px 0}

/* Header */
.hmb-ph{padding:2.5rem 2.5rem 2rem;background:linear-gradient(180deg,rgba(180,20,20,.07) 0%,transparent 100%);border-bottom:1px solid var(--c-b);display:flex;align-items:center;gap:2rem;position:relative;z-index:1}
.hmb-av-sec{position:relative;flex-shrink:0}
.hmb-av{width:100px;height:100px;border-radius:50%;border:2px solid var(--c-gd);object-fit:cover;display:block;box-shadow:0 0 24px rgba(180,20,20,.3);transition:filter .2s,border-color .2s}
.hmb-av:hover{filter:brightness(1.15);border-color:var(--c-g)}
.hmb-av-btn{position:absolute;bottom:0;right:0;background:var(--c-r);color:#fff;border-radius:50%;width:26px;height:26px;display:flex;align-items:center;justify-content:center;font-size:.75rem;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.5);border:2px solid var(--c-s);transition:background .2s;user-select:none}
.hmb-av-btn:hover{background:var(--c-rh)}
.hmb-av-form{display:none;margin-top:.75rem;min-width:200px}
.hmb-av-form.open{display:block}
.hmb-name{font-family:var(--fd);font-size:1.4rem;font-weight:700;color:var(--c-th);margin:0 0 .35rem;letter-spacing:.04em;text-shadow:0 2px 20px rgba(180,20,20,.3);word-break:break-word}
.hmb-uname{font-family:var(--fh);font-size:.62rem;color:var(--c-g);letter-spacing:.22em;text-transform:uppercase;margin:0;opacity:.8}

/* Alert */
.hmb-alert{margin:1rem 2.5rem 0;padding:.65rem .9rem;border-radius:var(--r);font-size:.82rem;border:1px solid;line-height:1.5;position:relative;z-index:1}
.ok{background:rgba(30,120,60,.1);border-color:rgba(30,120,60,.4);color:#66d98e}
.er{background:rgba(180,20,20,.1);border-color:rgba(180,20,20,.4);color:#ff7070}

/* Body */
.hmb-body{padding:2rem 2.5rem 2.5rem;position:relative;z-index:1}
.hmb-sec{font-family:var(--fh);font-size:.6rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:var(--c-sv);margin:0 0 1.25rem;padding-bottom:.6rem;border-bottom:1px solid var(--c-b)}
.hmb-fields{display:flex;flex-direction:column;gap:.75rem}
.hmb-fr{display:grid;grid-template-columns:140px 1fr;gap:.75rem;align-items:center;padding:.65rem .9rem;background:rgba(255,255,255,.02);border:1px solid var(--c-b);border-radius:var(--r)}
.hmb-fl{font-family:var(--fh);font-size:.58rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--c-sv)}
.hmb-fv{font-size:.875rem;color:var(--c-th);word-break:break-all}
.hmb-fv.em{color:var(--c-g)}.hmb-fv.dt{color:var(--c-sv);font-size:.8rem}

/* File */
.hmb-file-lbl{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .8rem;cursor:pointer;background:rgba(255,255,255,.04);border:1px dashed var(--c-b);border-radius:var(--r);font-size:.75rem;color:var(--c-sv);transition:border-color .2s,color .2s}
.hmb-file-lbl:hover{border-color:var(--c-r);color:var(--c-t)}
.hmb-fn{font-size:.72rem;color:var(--c-sv);margin-top:.3rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px}

/* Actions */
.hmb-actions{padding:1.1rem 2.5rem 1.5rem;border-top:1px solid var(--c-b);background:rgba(0,0,0,.2);display:flex;gap:.9rem;flex-wrap:wrap;align-items:center;position:relative;z-index:1}
.hmb-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.68rem 1.4rem;border-radius:var(--r);font-family:var(--fh);font-size:.65rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;text-decoration:none;cursor:pointer;border:none;transition:transform var(--sp) var(--e),box-shadow var(--sp) var(--e),filter var(--sp) var(--e)}
.hmb-btn-p{background:linear-gradient(160deg,#c01010 0%,#7a0a0a 100%);border:1px solid rgba(180,20,20,.5);color:var(--c-th)}
.hmb-btn-p:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(180,20,20,.4);filter:brightness(1.1);color:var(--c-th);text-decoration:none}
.hmb-btn-s{background:rgba(255,255,255,.04);border:1px solid var(--c-b);color:var(--c-sv)}
.hmb-btn-s:hover{border-color:#404040;color:var(--c-th);transform:translateY(-1px);text-decoration:none}

/* Upload btn */
.hmb-upload-btn{display:flex;align-items:center;justify-content:center;gap:.4rem;width:100%;margin-top:.5rem;padding:.55rem;background:linear-gradient(160deg,#c01010 0%,#7a0a0a 100%);border:1px solid rgba(180,20,20,.5);border-radius:var(--r);color:var(--c-th);font-family:var(--fh);font-size:.62rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;cursor:pointer;transition:filter .2s}
.hmb-upload-btn:hover{filter:brightness(1.1)}

@media(max-width:580px){
    .hmb-ph{flex-direction:column;text-align:center;padding:2rem 1.5rem 1.75rem}
    .hmb-body{padding:1.5rem}
    .hmb-actions{padding:1rem 1.5rem 1.4rem}
    .hmb-fr{grid-template-columns:1fr;gap:.2rem}
    .hmb-name{font-size:1.1rem}
}
</style>

<div class="hmb-profile">
<div class="hmb-pc">

    <span class="hmb-cn tl"></span>
    <span class="hmb-cn tr"></span>
    <span class="hmb-cn bl"></span>
    <span class="hmb-cn br"></span>

    <!-- ── HEADER ── -->
    <div class="hmb-ph">
        <div class="hmb-av-sec">
            <img id="hmb-av-img" class="hmb-av"
                src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES); ?>"
                alt="<?php echo htmlspecialchars($user->name, ENT_QUOTES); ?>"
                width="100" height="100"
                <?php if ($isOwn) echo 'onclick="document.getElementById(\'hmb-av-panel\').classList.toggle(\'open\')" style="cursor:pointer"'; ?>>

            <?php if ($isOwn) : ?>
            <div class="hmb-av-btn" onclick="document.getElementById('hmb-av-panel').classList.toggle('open')" title="Cambiar foto">&#9998;</div>

            <div id="hmb-av-panel" class="hmb-av-form">
                <form method="post" enctype="multipart/form-data"
                    action="<?php echo htmlspecialchars(Uri::current(), ENT_QUOTES); ?>">
                    <input type="hidden" name="user_id" value="<?php echo (int)$user->id; ?>">
                    <input type="hidden" name="<?php echo Session::getFormToken(); ?>" value="1">
                    <label class="hmb-file-lbl" for="hmb_av_inp">&#128247; Elegir imagen</label>
                    <input type="file" id="hmb_av_inp" name="hmb_avatar"
                        style="display:none" accept="image/jpeg,image/png,image/gif,image/webp"
                        onchange="document.getElementById('hmb-fn').textContent=this.files[0]?.name||''">
                    <div id="hmb-fn" class="hmb-fn"></div>
                    <button type="submit" class="hmb-upload-btn">&#8593; Subir foto</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <div>
            <h1 class="hmb-name"><?php echo htmlspecialchars($user->name, ENT_QUOTES); ?></h1>
            <p class="hmb-uname">@<?php echo htmlspecialchars($user->username, ENT_QUOTES); ?></p>
        </div>
    </div>

    <!-- ── ALERT ── -->
    <?php if ($uploadMsg) : ?>
    <div class="hmb-alert <?php echo $uploadMsg['ok'] ? 'ok' : 'er'; ?>">
        <?php echo htmlspecialchars($uploadMsg['msg'], ENT_QUOTES); ?>
    </div>
    <?php endif; ?>

    <!-- ── BODY ── -->
    <div class="hmb-body">
        <p class="hmb-sec">Perfil</p>
        <div class="hmb-fields">

            <div class="hmb-fr">
                <span class="hmb-fl">Nombre</span>
                <span class="hmb-fv"><?php echo htmlspecialchars($user->name, ENT_QUOTES); ?></span>
            </div>

            <div class="hmb-fr">
                <span class="hmb-fl">Usuario</span>
                <span class="hmb-fv">@<?php echo htmlspecialchars($user->username, ENT_QUOTES); ?></span>
            </div>

            <div class="hmb-fr">
                <span class="hmb-fl">Email</span>
                <span class="hmb-fv em"><?php echo htmlspecialchars($user->email, ENT_QUOTES); ?></span>
            </div>

            <?php if ($registerDate) : ?>
            <div class="hmb-fr">
                <span class="hmb-fl">Miembro desde</span>
                <span class="hmb-fv dt"><?php echo $registerDate; ?></span>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- ── ACTIONS ── -->
    <div class="hmb-actions">
        <?php if ($canEdit) : ?>
        <a class="hmb-btn hmb-btn-p"
            href="<?php echo Route::_('index.php?option=com_users&task=profile.edit&user_id=' . (int)$user->id); ?>">
            &#9998; Editar perfil
        </a>
        <?php endif; ?>
        <a class="hmb-btn hmb-btn-s" href="<?php echo Uri::root(); ?>">
            &#9664; Inicio
        </a>
    </div>

</div>
</div>
