<?php
/**
 * Heavy Metal Brothers — Perfil de Usuario
 * templates/rt_remnant/html/com_users/profile/default.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$app         = Factory::getApplication();
$currentUser = $app->getIdentity();
$userId      = $app->getInput()->getInt('user_id', 0) ?: $currentUser->id;
$user        = \Joomla\CMS\User\User::getInstance($userId);

if (!$user || !$user->id) {
    $app->redirect(Uri::root());
    return;
}

$isOwn   = ($currentUser->id == $user->id);
$canEdit = $isOwn || $currentUser->authorise('core.edit', 'com_users');

// Avatar
$userParams = new \Joomla\Registry\Registry($user->params);
$avatarPath = $userParams->get('avatar', '');
$avatarUrl  = !empty($avatarPath)
    ? Uri::root() . ltrim($avatarPath, '/')
    : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=200&d=mp';

// Fecha registro
$since = '';
if (!empty($user->registerDate) && $user->registerDate !== '0000-00-00 00:00:00') {
    try { $since = (new DateTime($user->registerDate))->format('d/m/Y'); } catch (\Exception $e) {}
}

// Procesar subida avatar
$msg = null;
if ($isOwn && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['hmb_avatar']['name'])) {
    $f = $_FILES['hmb_avatar'];
    if ($f['error'] === UPLOAD_ERR_OK && in_array($f['type'], ['image/jpeg','image/png','image/gif','image/webp']) && $f['size'] <= 2097152) {
        $ext  = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $dir  = JPATH_ROOT . '/images/hmb_avatars/';
        $name = 'user_' . $user->id . '.' . $ext;
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        if (move_uploaded_file($f['tmp_name'], $dir . $name)) {
            $userParams->set('avatar', 'images/hmb_avatars/' . $name);
            $db = Factory::getDbo();
            $db->setQuery($db->getQuery(true)->update($db->qn('#__users'))
                ->set($db->qn('params') . ' = ' . $db->q((string)$userParams))
                ->where($db->qn('id') . ' = ' . (int)$user->id))->execute();
            $avatarUrl = Uri::root() . 'images/hmb_avatars/' . $name . '?t=' . time();
            $msg = ['ok' => true, 'txt' => 'Foto actualizada.'];
        } else { $msg = ['ok' => false, 'txt' => 'Error al guardar la imagen.']; }
    } else { $msg = ['ok' => false, 'txt' => 'Imagen no válida (máx. 2MB, JPG/PNG/GIF/WEBP).']; }
}
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Cinzel:wght@400;600&family=Raleway:wght@300;400;500;600&display=swap');
.hmb-pf{--r:#b41414;--g:#c9a84c;--gd:#7a6030;--b:#222;--s:#0f0f0f;--t:#d4d4d4;--w:#fff;--sv:#888;--ra:3px;--e:cubic-bezier(.4,0,.2,1);--sp:.2s;font-family:'Raleway',sans-serif;color:var(--t);max-width:600px;margin:2rem auto;padding:0 1rem}
.hmb-card{position:relative;background:var(--s);border:1px solid var(--b);border-radius:var(--ra);overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.8),0 0 60px rgba(180,20,20,.06)}
.hmb-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#3a0000,var(--r) 30%,var(--g) 50%,var(--r) 70%,#3a0000);background-size:200% 100%;animation:hmbB 4s ease infinite;z-index:2}
@keyframes hmbB{0%{background-position:0 50%}50%{background-position:100% 50%}100%{background-position:0 50%}}
.hmb-cn{position:absolute;width:16px;height:16px;border-color:var(--gd);border-style:solid;opacity:.4;z-index:3}
.hmb-cn.a{top:8px;left:8px;border-width:1px 0 0 1px}.hmb-cn.b{top:8px;right:8px;border-width:1px 1px 0 0}
.hmb-cn.c{bottom:8px;left:8px;border-width:0 0 1px 1px}.hmb-cn.d{bottom:8px;right:8px;border-width:0 1px 1px 0}

/* Hero con avatar */
.hmb-hero{padding:2.5rem 2rem 2rem;display:flex;align-items:center;gap:1.75rem;background:linear-gradient(160deg,rgba(180,20,20,.07),transparent);border-bottom:1px solid var(--b);position:relative;z-index:1}
.hmb-av-wrap{position:relative;flex-shrink:0}
.hmb-av{width:90px;height:90px;border-radius:50%;object-fit:cover;border:2px solid var(--gd);box-shadow:0 0 20px rgba(180,20,20,.25);display:block}
<?php if ($isOwn): ?>
.hmb-av-btn{position:absolute;bottom:0;right:0;background:var(--r);color:#fff;border:2px solid var(--s);border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:.7rem;cursor:pointer;transition:background var(--sp) var(--e)}
.hmb-av-btn:hover{background:#e02020}
.hmb-av-drop{display:none;position:absolute;top:110%;left:50%;transform:translateX(-50%);background:#1a1a1a;border:1px solid var(--b);border-radius:var(--ra);padding:.75rem;min-width:180px;z-index:10;box-shadow:0 8px 24px rgba(0,0,0,.6)}
.hmb-av-drop.open{display:block}
<?php endif; ?>
.hmb-hero-info{flex:1;min-width:0}
.hmb-hero-name{font-family:'Cinzel Decorative','Cinzel',serif;font-size:1.2rem;font-weight:700;color:var(--w);margin:0 0 .3rem;text-shadow:0 2px 16px rgba(180,20,20,.3);word-break:break-word}
.hmb-hero-user{font-family:'Cinzel',serif;font-size:.6rem;color:var(--g);letter-spacing:.22em;text-transform:uppercase;margin:0 0 .6rem;opacity:.8}
.hmb-badge{display:inline-flex;align-items:center;gap:.3rem;background:rgba(180,20,20,.12);border:1px solid rgba(180,20,20,.3);border-radius:20px;padding:.2rem .7rem;font-size:.65rem;font-family:'Cinzel',serif;letter-spacing:.1em;text-transform:uppercase;color:#c77}

/* Info rows */
.hmb-info{padding:1.5rem 2rem;display:flex;flex-direction:column;gap:.6rem;position:relative;z-index:1}
.hmb-row{display:flex;align-items:center;gap:.75rem;padding:.55rem .75rem;background:rgba(255,255,255,.02);border:1px solid var(--b);border-radius:var(--ra)}
.hmb-row-lbl{font-family:'Cinzel',serif;font-size:.57rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--sv);width:100px;flex-shrink:0}
.hmb-row-val{font-size:.875rem;color:var(--w);font-weight:400;word-break:break-all}
.hmb-row-val.em{color:var(--g)}
.hmb-row-val.dt{color:var(--sv);font-size:.8rem}

/* Alert */
.hmb-alert{margin:.5rem 2rem 0;padding:.6rem .85rem;border-radius:var(--ra);font-size:.8rem;border:1px solid;line-height:1.5;position:relative;z-index:1}
.ok{background:rgba(30,120,60,.1);border-color:rgba(30,120,60,.4);color:#66d98e}
.er{background:rgba(180,20,20,.1);border-color:rgba(180,20,20,.4);color:#ff7070}

/* File pick */
.hmb-filelbl{display:flex;align-items:center;gap:.4rem;padding:.4rem .7rem;background:rgba(255,255,255,.04);border:1px dashed var(--b);border-radius:var(--ra);font-size:.75rem;color:var(--sv);cursor:pointer;transition:border-color var(--sp) var(--e)}
.hmb-filelbl:hover{border-color:var(--r);color:var(--t)}
.hmb-fname{font-size:.7rem;color:var(--sv);margin-top:.3rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* Actions */
.hmb-actions{padding:1rem 2rem 1.5rem;border-top:1px solid var(--b);display:flex;gap:.75rem;flex-wrap:wrap;position:relative;z-index:1}
.hmb-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.65rem 1.25rem;border-radius:var(--ra);font-family:'Cinzel',serif;font-size:.62rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;text-decoration:none;cursor:pointer;border:none;transition:transform var(--sp) var(--e),box-shadow var(--sp) var(--e),filter var(--sp) var(--e)}
.hmb-btn-p{background:linear-gradient(160deg,#c01010,#7a0a0a);border:1px solid rgba(180,20,20,.5);color:#fff}
.hmb-btn-p:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(180,20,20,.4);filter:brightness(1.1);color:#fff;text-decoration:none}
.hmb-btn-s{background:rgba(255,255,255,.04);border:1px solid var(--b);color:var(--sv)}
.hmb-btn-s:hover{border-color:#404040;color:var(--w);transform:translateY(-1px);text-decoration:none}

/* Upload btn */
.hmb-upbtn{display:flex;align-items:center;justify-content:center;gap:.35rem;width:100%;margin-top:.5rem;padding:.5rem;background:linear-gradient(160deg,#c01010,#7a0a0a);border:1px solid rgba(180,20,20,.5);border-radius:var(--ra);color:#fff;font-family:'Cinzel',serif;font-size:.6rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;transition:filter var(--sp) var(--e)}
.hmb-upbtn:hover{filter:brightness(1.1)}

@media(max-width:560px){
    .hmb-hero{flex-direction:column;text-align:center;padding:2rem 1.5rem 1.75rem}
    .hmb-info,.hmb-actions{padding-left:1.5rem;padding-right:1.5rem}
    .hmb-row{flex-direction:column;align-items:flex-start;gap:.2rem}
    .hmb-row-lbl{width:auto}
    .hmb-hero-name{font-size:1rem}
}
</style>

<div class="hmb-pf">
<div class="hmb-card">
<span class="hmb-cn a"></span><span class="hmb-cn b"></span>
<span class="hmb-cn c"></span><span class="hmb-cn d"></span>

<?php if ($msg): ?>
<div class="hmb-alert <?php echo $msg['ok']?'ok':'er'; ?>"><?php echo htmlspecialchars($msg['txt']); ?></div>
<?php endif; ?>

<!-- HERO -->
<div class="hmb-hero">
    <div class="hmb-av-wrap">
        <img id="hmb-pf-av" class="hmb-av"
            src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES); ?>"
            alt="<?php echo htmlspecialchars($user->name, ENT_QUOTES); ?>"
            width="90" height="90">

        <?php if ($isOwn): ?>
        <div class="hmb-av-btn" onclick="document.getElementById('hmb-avd').classList.toggle('open')" title="Cambiar foto">&#9998;</div>
        <div id="hmb-avd" class="hmb-av-drop">
            <form method="post" enctype="multipart/form-data"
                action="<?php echo htmlspecialchars(Uri::current(), ENT_QUOTES); ?>">
                <input type="hidden" name="user_id" value="<?php echo (int)$user->id; ?>">
                <input type="hidden" name="<?php echo Session::getFormToken(); ?>" value="1">
                <label class="hmb-filelbl" for="hmb-av-inp">&#128247; Elegir imagen</label>
                <input type="file" id="hmb-av-inp" name="hmb_avatar" style="display:none"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    onchange="document.getElementById('hmb-avfn').textContent=this.files[0]?.name||''">
                <div id="hmb-avfn" class="hmb-fname"></div>
                <button type="submit" class="hmb-upbtn">&#8593; Subir foto</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="hmb-hero-info">
        <h1 class="hmb-hero-name"><?php echo htmlspecialchars($user->name, ENT_QUOTES); ?></h1>
        <p class="hmb-hero-user">@<?php echo htmlspecialchars($user->username, ENT_QUOTES); ?></p>
        <?php if ($since): ?>
        <span class="hmb-badge">&#9760; Hermano desde <?php echo $since; ?></span>
        <?php endif; ?>
    </div>
</div>

<!-- INFO -->
<div class="hmb-info">
    <div class="hmb-row">
        <span class="hmb-row-lbl">Email</span>
        <span class="hmb-row-val em"><?php echo htmlspecialchars($user->email, ENT_QUOTES); ?></span>
    </div>
    <div class="hmb-row">
        <span class="hmb-row-lbl">Usuario</span>
        <span class="hmb-row-val">@<?php echo htmlspecialchars($user->username, ENT_QUOTES); ?></span>
    </div>
</div>

<!-- ACTIONS -->
<div class="hmb-actions">
    <?php if ($canEdit): ?>
    <a class="hmb-btn hmb-btn-p"
        href="<?php echo Route::_('index.php?option=com_users&task=profile.edit&user_id=' . (int)$user->id); ?>">
        &#9998; Editar perfil
    </a>
    <?php endif; ?>
    <a class="hmb-btn hmb-btn-s" href="<?php echo Uri::root(); ?>">&#9664; Inicio</a>
</div>

</div>
</div>

<script>
document.addEventListener('click', function(e) {
    var d = document.getElementById('hmb-avd');
    if (d && !d.contains(e.target) && !e.target.classList.contains('hmb-av-btn')) {
        d.classList.remove('open');
    }
});
</script>
