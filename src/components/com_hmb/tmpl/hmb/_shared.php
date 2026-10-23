<?php
/**
 * HMB Component — Shared template (login + registro)
 * $activePanel = 'login' | 'register'
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$app         = Factory::getApplication();
$db          = Factory::getDbo();
$token       = $token;
$returnLogin = base64_encode($redirectUrl);
$siteRoot    = Uri::root();
$ajaxUrl     = $siteRoot . 'index.php?option=com_ajax&module=hmb_register&method=register&format=json';
$profileUrl  = $siteRoot . 'index.php/registro/perfil-usuario';
$registroUrl = $siteRoot . 'index.php/registro';

// Base sobre el HOST ACTUAL (no el canónico). Así el retorno es absoluto (no da 404
// como el relativo) y se queda en el mismo host que usa la persona (no pierde sesión).
$reqRoot = rtrim(Uri::getInstance()->toString(['scheme', 'host', 'port']), '/') . '/';

// Destino tras registrarse/loguearse.
$registerDest = $reqRoot . 'index.php/registro/perfil-usuario';
$rdrParam     = (string) $app->getInput()->getString('rdr', '');
if ($rdrParam !== '') {
    $b64 = strtr($rdrParam, '-_', '+/');
    $b64 .= str_repeat('=', (4 - strlen($b64) % 4) % 4);
    $decoded = base64_decode($b64, true);
    if ($decoded) {
        $decoded    = trim($decoded);
        $isExternal = ($decoded === '') || (strpos($decoded, '//') === 0);
        if (!$isExternal) {
            // Quita esquema+host si los trae y reconstruye sobre el host actual.
            $rel = ltrim(preg_replace('#^https?://[^/]+#i', '', $decoded), '/');
            if ($rel !== '') {
                $registerDest = $reqRoot . $rel;
            }
        }
    }
}

// El login (com_users) vuelve al mismo destino que el registro si llega ?rdr.
$returnLogin = base64_encode($registerDest);

// ── Leer params del módulo mod_hmb_register desde BD ──────
$q = $db->getQuery(true)->select('params')->from('#__modules')
    ->where('module = ' . $db->q('mod_hmb_register'))->where('published = 1');
$modCfg = new \Joomla\Registry\Registry($db->setQuery($q)->loadResult() ?: '{}');

$welcomeTitle    = (string) $modCfg->get('welcome_title',    '¡Bienvenido a la Hermandad!');
$welcomeSubtitle = (string) $modCfg->get('welcome_subtitle', 'Hermano del Metal');
$welcomeMessage  = (string) $modCfg->get('welcome_message',  'Tu cuenta ha sido creada. Ya eres parte de la Hermandad del Metal.');
$welcomeImageRaw = (string) $modCfg->get('welcome_image',    '');
$welcomeImage    = $welcomeImageRaw ? $siteRoot . ltrim($welcomeImageRaw, '/') : '';
$welcomeDelay    = max(1000, (int) $modCfg->get('welcome_delay', 4000));

$byeTitle    = (string) $modCfg->get('bye_title',    'Hasta Pronto, Hermano');
$byeSubtitle = (string) $modCfg->get('bye_subtitle', 'Que el Metal te acompañe');
$byeMessage  = (string) $modCfg->get('bye_message',  'Has cerrado sesión. La Hermandad te espera cuando quieras volver.');
$byeImageRaw = (string) $modCfg->get('bye_image',    '');
$byeImage    = $byeImageRaw ? $siteRoot . ltrim($byeImageRaw, '/') : '';
$byeDelay    = max(1000, (int) $modCfg->get('bye_delay', 3500));

// Pantalla de despedida: viene de logout con ?hmbbye=1
$showBye = !$isLoggedIn && (int) $app->getInput()->getInt('hmbbye', 0) === 1;
?>
<style>
.hmb-cw{--r:var(--fire, #CC2200);--rh:var(--fire, #CC2200);--g:var(--gold-light, #DAA520);--gd:var(--gold-dim, #7A6030);--sv:var(--grey-8, #888888);--t:#d4d4d4;--b:var(--steel, #1A1A1A);--s:var(--dark, #0A0A0A);--fd:'Cinzel Decorative','Cinzel',serif;--fh:'Cinzel',serif;--fb:var(--font-body, 'Rajdhani', sans-serif);--ra:3px;--e:cubic-bezier(.4,0,.2,1);--sp:.22s;font-family:var(--fb);color:var(--t);width:100%}
.hmb-ci{position:relative;background:var(--s);border:1px solid var(--b);border-radius:var(--ra);box-shadow:0 0 0 1px rgba(255,255,255,.03) inset,0 24px 64px rgba(0,0,0,.85),0 0 60px rgba(180,20,20,.06)}
.hmb-ci::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--wine, #3A0000),var(--r) 30%,var(--g) 50%,var(--r) 70%,var(--wine, #3A0000));background-size:200% 100%;animation:hmbB 4s ease infinite;z-index:2;border-radius:var(--ra) var(--ra) 0 0}
@keyframes hmbB{0%{background-position:0 50%}50%{background-position:100% 50%}100%{background-position:0 50%}}
.hmb-cn{position:absolute;width:18px;height:18px;border-color:var(--gd);border-style:solid;opacity:.4;z-index:3}
.hmb-cn.a{top:8px;left:8px;border-width:1px 0 0 1px}.hmb-cn.b{top:8px;right:8px;border-width:1px 1px 0 0}
.hmb-cn.c{bottom:8px;left:8px;border-width:0 0 1px 1px}.hmb-cn.d{bottom:8px;right:8px;border-width:0 1px 1px 0}
.hmb-p{display:none}.hmb-p.on{display:block;animation:hmbF .3s var(--e)}
@keyframes hmbF{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
/* Header */
.hmb-hd{padding:2.25rem 3rem 1.75rem;background:linear-gradient(180deg,rgba(180,20,20,.07),transparent);border-bottom:1px solid var(--b);text-align:center;position:relative;z-index:1}
.hmb-sk{font-size:1.5rem;display:block;margin-bottom:.75rem;animation:hmbBr 4s ease-in-out infinite}
@keyframes hmbBr{0%,100%{filter:drop-shadow(0 0 5px rgba(180,20,20,.3))}50%{filter:drop-shadow(0 0 13px rgba(180,20,20,.7))}}
.hmb-tt{font-family:var(--fd);font-size:1.3rem;font-weight:700;color:#fff;margin:0 0 .35rem;letter-spacing:.05em;text-shadow:0 2px 20px rgba(180,20,20,.35)}
.hmb-sb{font-family:var(--fh);font-size:.6rem;color:var(--g);letter-spacing:.28em;text-transform:uppercase;margin:0;opacity:.75}
/* Body */
.hmb-bd{padding:2rem 3rem 2.25rem;position:relative;z-index:1}
/* Alert */
.hmb-al{border-radius:var(--ra);padding:.68rem .9rem;font-size:.82rem;margin-bottom:1.2rem;border:1px solid;line-height:1.5}
.hmb-er{background:rgba(180,20,20,.1);border-color:rgba(180,20,20,.4);color:var(--fire-light, #FF7070)}
.hmb-ok{background:rgba(30,120,60,.1);border-color:rgba(30,120,60,.4);color:var(--ok-light, #66D98E)}
/* Form */
.hmb-fm{display:flex;flex-direction:column;gap:.9rem}
.hmb-rw{display:grid;grid-template-columns:1fr 1fr 1fr;gap:.8rem}
.hmb-r2{grid-template-columns:1fr 1fr}
.hmb-fl{display:flex;flex-direction:column;gap:.3rem}
.hmb-lb{font-family:var(--fh);font-size:.6rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;color:var(--sv);display:flex;align-items:center;gap:.2rem}
.hmb-rq{color:var(--rh)}
.hmb-iw,.hmb-pw{position:relative}
.hmb-in{width:100%;box-sizing:border-box;background:rgba(255,255,255,.04);border:1px solid var(--b);border-radius:var(--ra);color:#fff !important;font-family:var(--fb);font-size:.9rem;font-weight:500 !important;padding:.62rem 2.3rem .62rem .88rem;transition:border-color var(--sp) var(--e),background var(--sp) var(--e),box-shadow var(--sp) var(--e);-webkit-appearance:none;appearance:none}
.hmb-in::placeholder{color:rgba(136,136,136,.28);font-style:italic;font-weight:300}
.hmb-in:focus{outline:none;border-color:var(--r);background:rgba(180,20,20,.05);box-shadow:0 0 0 3px rgba(180,20,20,.12)}
.hmb-in:-webkit-autofill,.hmb-in:-webkit-autofill:focus{-webkit-text-fill-color:#fff !important;-webkit-box-shadow:0 0 0 1000px var(--wine-black, #0A0000) inset}
.hmb-ic{position:absolute;right:.72rem;top:50%;transform:translateY(-50%);color:#484848;pointer-events:none;display:flex;align-items:center}
.hmb-ic svg{width:.95rem;height:.95rem}
.hmb-ey{position:absolute;right:.65rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#484848;padding:.2rem;display:flex;align-items:center;transition:color var(--sp) var(--e)}
.hmb-ey:hover{color:var(--rh)}
.hmb-ey svg{width:.9rem;height:.9rem}
/* Checkbox */
.hmb-cl{display:flex;align-items:center;gap:.6rem;cursor:pointer;user-select:none}
.hmb-cb{position:absolute;opacity:0;width:0;height:0}
.hmb-cbox{width:15px;height:15px;border:1px solid rgba(255,255,255,.2);border-radius:2px;background:rgba(255,255,255,.05);flex-shrink:0;position:relative;transition:border-color var(--sp) var(--e),background var(--sp) var(--e)}
.hmb-cbox::after{content:'';position:absolute;width:5px;height:8px;top:1px;left:4px;border:2px solid var(--rh);border-top:none;border-left:none;transform:rotate(45deg) scale(0);transition:transform .15s var(--e)}
.hmb-cb:checked+.hmb-cbox{background:rgba(180,20,20,.2);border-color:var(--r)}
.hmb-cb:checked+.hmb-cbox::after{transform:rotate(45deg) scale(1)}
.hmb-ctx{font-family:var(--fh);font-size:.65rem;letter-spacing:.08em;text-transform:uppercase;color:#b0b0b0}
/* Button */
.hmb-bt{display:flex;align-items:center;justify-content:center;gap:.6rem;width:100%;margin-top:.2rem;padding:.82rem;background:linear-gradient(160deg,var(--fire, #CC2200),var(--blood, #8B0000));border:1px solid rgba(180,20,20,.5);border-radius:var(--ra);color:#fff;font-family:var(--fh);font-size:.72rem;font-weight:600;letter-spacing:.22em;text-transform:uppercase;cursor:pointer;position:relative;overflow:hidden;transition:transform var(--sp) var(--e),box-shadow var(--sp) var(--e),filter var(--sp) var(--e)}
.hmb-bt::after{content:'';position:absolute;top:0;left:-110%;width:60%;height:100%;background:linear-gradient(105deg,transparent 30%,rgba(255,255,255,.08) 50%,transparent 70%);transition:left .5s var(--e)}
.hmb-bt:hover::after{left:150%}
.hmb-bt:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(180,20,20,.45);filter:brightness(1.12)}
.hmb-bt:active{transform:none;box-shadow:none}
.hmb-bt:disabled{opacity:.4;cursor:not-allowed;transform:none}
.hmb-bt.ld .hmb-bts::after{content:'';display:inline-block;width:.65em;height:.65em;border:2px solid rgba(255,255,255,.25);border-top-color:#fff;border-radius:50%;margin-left:.5em;vertical-align:middle;animation:hmbSp .7s linear infinite}
@keyframes hmbSp{to{transform:rotate(360deg)}}
/* Links */
.hmb-lk{list-style:none;margin:.9rem 0 0;padding:.9rem 0 0;border-top:1px solid var(--b);display:flex;flex-direction:column;gap:.45rem}
.hmb-lk li a{display:flex;align-items:center;gap:.45rem;font-size:.78rem;color:var(--sv);text-decoration:none;transition:color var(--sp) var(--e)}
.hmb-lk li a:hover{color:#fff}
.hmb-bl{font-size:.45rem;color:var(--r);flex-shrink:0}
/* Footer */
.hmb-ft{padding:.95rem 3rem 1.35rem;border-top:1px solid var(--b);text-align:center;font-size:.78rem;color:#484848;background:rgba(0,0,0,.2);position:relative;z-index:1}
.hmb-sw{background:none;border:none;cursor:pointer;padding:0;margin-left:.3rem;color:var(--rh);font-family:var(--fb);font-size:.78rem;font-weight:600;text-decoration:underline;transition:color var(--sp) var(--e)}
.hmb-sw:hover{color:var(--g)}
/* Pantallas de bienvenida / despedida */
.hmb-splash{padding:3.5rem 3rem 3rem;text-align:center;position:relative;z-index:1}
.hmb-splash-skull{font-size:3.5rem;display:block;margin-bottom:1.25rem;animation:hmbBr 2s ease-in-out infinite}
.hmb-splash-title{font-family:var(--fd);font-size:1.3rem;font-weight:700;color:#fff;margin:0 0 .6rem;letter-spacing:.04em;text-shadow:0 2px 20px rgba(180,20,20,.5)}
.hmb-splash-sub{font-family:var(--fh);font-size:.62rem;color:var(--g);letter-spacing:.25em;text-transform:uppercase;margin:0 0 1.5rem;opacity:.85}
.hmb-splash-msg{font-size:.88rem;color:#b0b0b0;line-height:1.65;margin:0 0 1.75rem}
.hmb-splash-bar{height:3px;background:rgba(255,255,255,.07);border-radius:2px;overflow:hidden;margin:0 auto;max-width:240px}
.hmb-splash-fill{height:100%;background:linear-gradient(90deg,var(--r),var(--g));border-radius:2px;width:0;transition:width linear}
.hmb-splash-hint{font-size:.72rem;color:var(--sv);margin-top:.9rem}
@media(max-width:640px){
    .hmb-hd,.hmb-bd,.hmb-splash{padding-left:1.5rem;padding-right:1.5rem}
    .hmb-ft{padding-left:1.5rem;padding-right:1.5rem}
    .hmb-tt,.hmb-splash-title{font-size:1.05rem}
    .hmb-rw,.hmb-r2{grid-template-columns:1fr}
}
</style>

<div class="hmb-cw">
<div class="hmb-ci" id="hmb-ci">
<span class="hmb-cn a"></span><span class="hmb-cn b"></span>
<span class="hmb-cn c"></span><span class="hmb-cn d"></span>

<?php if ($isLoggedIn) : ?>
<!-- ── LOGUEADO ───────────────────────────────────── -->
<div class="hmb-hd">
    <span class="hmb-sk">&#9760;</span>
    <h1 class="hmb-tt">Bienvenido de Nuevo</h1>
    <p class="hmb-sb">Hermano del Metal</p>
</div>
<div class="hmb-bd" style="text-align:center">
    <form method="post" action="<?php echo Route::_('index.php?option=com_users&task=user.logout'); ?>">
        <input type="hidden" name="return" value="<?php echo base64_encode($registroUrl . '?hmbbye=1'); ?>">
        <input type="hidden" name="<?php echo $token; ?>" value="1">
        <button type="submit" class="hmb-bt"><span class="hmb-bts">Salir del Foso</span> &#9658;</button>
    </form>
</div>
<div class="hmb-ft">
    <a style="color:var(--rh);font-weight:600;text-decoration:none" href="<?php echo $profileUrl; ?>">
        &#9670; Ver mi perfil
    </a>
</div>

<?php elseif ($showBye) : ?>
<!-- ── PANTALLA DE DESPEDIDA ─────────────────────── -->
<div class="hmb-splash">
    <?php if (!empty($byeImage)) : ?>
    <img src="<?php echo htmlspecialchars($byeImage, ENT_QUOTES); ?>"
        alt="<?php echo htmlspecialchars($byeTitle, ENT_QUOTES); ?>"
        style="max-width:220px;max-height:180px;object-fit:contain;border-radius:6px;margin-bottom:1.25rem;opacity:.85">
    <?php else : ?>
    <span class="hmb-splash-skull">&#9760;</span>
    <?php endif; ?>
    <h1 class="hmb-splash-title"><?php echo htmlspecialchars($byeTitle, ENT_QUOTES); ?></h1>
    <p class="hmb-splash-sub"><?php echo htmlspecialchars($byeSubtitle, ENT_QUOTES); ?></p>
    <p class="hmb-splash-msg"><?php echo nl2br(htmlspecialchars($byeMessage, ENT_QUOTES)); ?></p>
    <div class="hmb-splash-bar"><div class="hmb-splash-fill" id="hmb-bye-bar"></div></div>
    <p class="hmb-splash-hint">Volviendo al inicio...</p>
</div>

<?php else : ?>

<!-- ── PANEL LOGIN ───────────────────────────────── -->
<div class="hmb-p <?php echo $activePanel==='login'?'on':''; ?>" id="hmb-login">
    <div class="hmb-hd">
        <span class="hmb-sk">&#9760;</span>
        <h1 class="hmb-tt">Entra al Foso</h1>
        <p class="hmb-sb">Accede a tu cuenta</p>
    </div>
    <div class="hmb-bd">
        <form class="hmb-fm" method="post" id="hmb-lf"
            action="<?php echo Route::_('index.php?option=com_users&task=user.login'); ?>">
            <input type="hidden" name="return" value="<?php echo $returnLogin; ?>">
            <input type="hidden" name="<?php echo $token; ?>" value="1">
            <div class="hmb-fl">
                <label class="hmb-lb" for="hmb-lu">Usuario o Email <span class="hmb-rq">*</span></label>
                <div class="hmb-iw">
                    <input type="text" id="hmb-lu" name="username" class="hmb-in"
                        placeholder="Tu nombre de usuario" required autocomplete="username">
                    <span class="hmb-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></span>
                </div>
            </div>
            <div class="hmb-fl">
                <label class="hmb-lb" for="hmb-lpw">Contraseña <span class="hmb-rq">*</span></label>
                <div class="hmb-pw">
                    <input type="password" id="hmb-lpw" name="password" class="hmb-in"
                        placeholder="Tu contraseña" required autocomplete="current-password">
                    <button type="button" class="hmb-ey" data-t="hmb-lpw" aria-label="Ver">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <div class="hmb-fl">
                <label class="hmb-cl">
                    <input type="checkbox" name="remember" class="hmb-cb" value="yes">
                    <span class="hmb-cbox"></span>
                    <span class="hmb-ctx">Recuérdame</span>
                </label>
            </div>
            <button type="submit" class="hmb-bt" id="hmb-lb">
                <span class="hmb-bts">Conectar</span> &#9658;
            </button>
        </form>
        <ul class="hmb-lk">
            <li><a href="<?php echo $siteRoot; ?>index.php?option=com_users&view=reset">
                <span class="hmb-bl">&#9670;</span> ¿Olvidó su contraseña?
            </a></li>
            <li><a href="<?php echo $siteRoot; ?>index.php?option=com_users&view=remind">
                <span class="hmb-bl">&#9670;</span> ¿Olvidó su usuario?
            </a></li>
        </ul>
    </div>
    <div class="hmb-ft">
        ¿Aún no eres miembro?
        <button class="hmb-sw" onclick="hmbSw('register')">Únete a la Hermandad</button>
    </div>
</div>

<!-- ── PANEL REGISTRO ─────────────────────────────── -->
<div class="hmb-p <?php echo $activePanel==='register'?'on':''; ?>" id="hmb-register">
    <div class="hmb-hd">
        <span class="hmb-sk">&#9760;</span>
        <h1 class="hmb-tt">Únete a la Hermandad</h1>
        <p class="hmb-sb">Crea tu cuenta</p>
    </div>
    <div class="hmb-bd" id="hmb-register-body">
        <div id="hmb-ra" style="display:none"></div>
        <form class="hmb-fm" id="hmb-rf" novalidate enctype="multipart/form-data">
            <input type="hidden" name="<?php echo $token; ?>" value="1">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($registerDest, ENT_QUOTES); ?>">
            <div class="hmb-rw">
                <div class="hmb-fl">
                    <label class="hmb-lb" for="hmb-rfn">Nombre</label>
                    <input type="text" id="hmb-rfn" name="firstname" class="hmb-in"
                        placeholder="Tu nombre" autocomplete="given-name">
                </div>
                <div class="hmb-fl">
                    <label class="hmb-lb" for="hmb-rsn">Apellidos</label>
                    <input type="text" id="hmb-rsn" name="surname" class="hmb-in"
                        placeholder="Tus apellidos" autocomplete="family-name">
                </div>
                <div class="hmb-fl">
                    <label class="hmb-lb" for="hmb-run">Usuario</label>
                    <input type="text" id="hmb-run" name="username" class="hmb-in"
                        placeholder="Nombre de usuario" autocomplete="username">
                </div>
            </div>
            <div class="hmb-fl">
                <label class="hmb-lb" for="hmb-rem">Email <span class="hmb-rq">*</span></label>
                <div class="hmb-iw">
                    <input type="email" id="hmb-rem" name="email" class="hmb-in"
                        placeholder="tu@correo.com" required autocomplete="email">
                    <span class="hmb-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg></span>
                </div>
            </div>
            <div class="hmb-fl">
                <label class="hmb-lb" for="hmb-rem2">Confirmar email <span class="hmb-rq">*</span></label>
                <div class="hmb-iw">
                    <input type="email" id="hmb-rem2" name="email2" class="hmb-in"
                        placeholder="Repite tu correo" required autocomplete="email">
                    <span class="hmb-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg></span>
                </div>
            </div>
            <div class="hmb-rw hmb-r2">
                <div class="hmb-fl">
                    <label class="hmb-lb" for="hmb-rp1">Contraseña <span class="hmb-rq">*</span></label>
                    <div class="hmb-pw">
                        <input type="password" id="hmb-rp1" name="password" class="hmb-in"
                            placeholder="Mín. 8 caracteres" required minlength="8" autocomplete="new-password">
                        <button type="button" class="hmb-ey" data-t="hmb-rp1" aria-label="Ver">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="hmb-fl">
                    <label class="hmb-lb" for="hmb-rp2">Confirmar <span class="hmb-rq">*</span></label>
                    <div class="hmb-pw">
                        <input type="password" id="hmb-rp2" name="password2" class="hmb-in"
                            placeholder="Repite la contraseña" required autocomplete="new-password">
                        <button type="button" class="hmb-ey" data-t="hmb-rp2" aria-label="Ver">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Notificaciones + Metal Quiz -->
            <div class="hmb-fl">
                <label class="hmb-cl">
                    <input type="checkbox" name="accept_notifications" class="hmb-cb" value="1" checked>
                    <span class="hmb-cbox"></span>
                    <span class="hmb-ctx">Acepta recibir noticias y novedades del sitio</span>
                </label>
            </div>
            <div class="hmb-fl">
                <label class="hmb-cl">
                    <input type="checkbox" name="metal_quiz_invitations" class="hmb-cb" value="1" checked>
                    <span class="hmb-cbox"></span>
                    <span class="hmb-ctx">&#9760; Acepta recibir invitaciones al Metal Quiz</span>
                </label>
            </div>
            <button type="submit" class="hmb-bt" id="hmb-rb">
                <span class="hmb-bts">Unirse a la Hermandad</span> &#9658;
            </button>
        </form>
    </div>
    <div class="hmb-ft">
        ¿Ya eres miembro?
        <button class="hmb-sw" onclick="hmbSw('login')">Iniciar sesión</button>
    </div>
</div>

<?php endif; ?>
</div>
</div>

<script>
(function(){
    var SITE = '<?php echo addslashes($siteRoot); ?>';

    // ── Pantalla de despedida ───────────────────────
    var byeBar = document.getElementById('hmb-bye-bar');
    if (byeBar) {
        var dur = <?php echo (int) $byeDelay; ?>;
        byeBar.getBoundingClientRect();
        byeBar.style.transition = 'width ' + dur + 'ms linear';
        byeBar.style.width = '100%';
        setTimeout(function(){ window.location.href = SITE; }, dur);
        return;
    }

    // ── Switch de paneles ──────────────────────────
    window.hmbSw = function(p){
        ['login','register'].forEach(function(id){
            var el = document.getElementById('hmb-'+id);
            if(el) el.classList.toggle('on', id===p);
        });
        document.querySelector('.hmb-cw').scrollIntoView({behavior:'smooth',block:'start'});
    };

    // ── Eye toggles ────────────────────────────────
    document.querySelectorAll('.hmb-ey').forEach(function(b){
        b.addEventListener('click',function(){
            var i=document.getElementById(b.dataset.t);
            if(i) i.type=i.type==='password'?'text':'password';
        });
    });

    // ── Login submit (estado de carga) ──────────────
    var lf=document.getElementById('hmb-lf'), lb=document.getElementById('hmb-lb');
    if(lf&&lb) lf.addEventListener('submit',function(){ lb.disabled=true; lb.classList.add('ld'); });

    // ── Register AJAX ──────────────────────────────
    var rf=document.getElementById('hmb-rf'),
        rb=document.getElementById('hmb-rb'),
        ra=document.getElementById('hmb-ra'),
        body=document.getElementById('hmb-register-body');
    if(!rf) return;

    rf.addEventListener('submit',function(e){
        e.preventDefault();

        var pw=rf.querySelector('[name="password"]'), pw2=rf.querySelector('[name="password2"]');
        if(pw&&pw2&&pw.value!==pw2.value){ alertHmb('Las contraseñas no coinciden.','hmb-er'); return; }

        var em=rf.querySelector('[name="email"]'), em2=rf.querySelector('[name="email2"]');
        if(em&&em2&&em.value.trim()!==em2.value.trim()){ alertHmb('Los correos electrónicos no coinciden.','hmb-er'); return; }

        rb.disabled=true; rb.classList.add('ld');

        fetch('<?php echo addslashes($ajaxUrl); ?>',{
            method:'POST', body:new FormData(rf),
            headers:{'X-Requested-With':'XMLHttpRequest'}
        })
        .then(function(r){return r.json();})
        .then(function(j){
            var res=j.data||j;
            if(res.success){
                mostrarBienvenida(res);
            } else {
                alertHmb(res.message||'Error al registrar.','hmb-er');
                rb.disabled=false; rb.classList.remove('ld');
            }
        })
        .catch(function(){
            alertHmb('Error de conexión. Inténtalo de nuevo.','hmb-er');
            rb.disabled=false; rb.classList.remove('ld');
        });
    });

    function mostrarBienvenida(res){
        var dur    = <?php echo (int) $welcomeDelay; ?>;
        var dest   = res.redirect || SITE;
        var title  = <?php echo json_encode($welcomeTitle); ?>;
        var sub    = <?php echo json_encode($welcomeSubtitle); ?>;
        var img    = <?php echo json_encode($welcomeImage); ?>;
        var msg    = res.message || <?php echo json_encode($welcomeMessage); ?>;

        var imgHtml = img
            ? '<img src="'+img+'" alt="" style="max-width:220px;max-height:180px;object-fit:contain;border-radius:6px;margin-bottom:1.25rem;opacity:.9">'
            : '<span style="font-size:3.5rem;display:block;margin-bottom:1.25rem">&#9760;</span>';

        if(body){
            body.innerHTML =
                '<div class="hmb-splash">' +
                    imgHtml +
                    '<h2 class="hmb-splash-title">'+title+'</h2>' +
                    '<p class="hmb-splash-sub">'+sub+'</p>' +
                    '<p class="hmb-splash-msg">'+msg+'</p>' +
                    '<div class="hmb-splash-bar"><div class="hmb-splash-fill" id="hmb-welcome-bar"></div></div>' +
                    '<p class="hmb-splash-hint">Preparando tu perfil...</p>' +
                '</div>';

            var bar = document.getElementById('hmb-welcome-bar');
            if(bar){
                bar.getBoundingClientRect();
                bar.style.transition = 'width ' + dur + 'ms linear';
                bar.style.width = '100%';
            }
        }
        setTimeout(function(){ window.location.href = dest; }, dur);
    }

    function alertHmb(msg,cls){
        if(!ra) return;
        ra.className='hmb-al '+cls; ra.innerHTML=msg; ra.style.display='block';
        ra.scrollIntoView({behavior:'smooth',block:'nearest'});
    }
})();
</script>
