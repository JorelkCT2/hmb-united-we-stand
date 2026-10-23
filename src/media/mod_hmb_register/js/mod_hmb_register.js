/**
 * Heavy Metal Brothers — Register + Login Module JS  v2.2
 */
'use strict';

(function () {

    document.addEventListener('DOMContentLoaded', function () {
        const cfg = Joomla.getOptions('mod_hmb_register');
        if (!cfg) return;
        const instances = Array.isArray(cfg) ? cfg : [cfg];
        instances.forEach(initModule);
    });

    function initModule(cfg) {
        const root = document.getElementById(cfg.moduleId);
        if (!root) return;

        // ── Panel switches ───────────────────────────────
        root.querySelectorAll('.hmb-footer-switch').forEach(function (btn) {
            btn.addEventListener('click', function () {
                switchToPanel(root, btn.dataset.target);
            });
        });

        // ── Password toggles ─────────────────────────────
        root.querySelectorAll('.hmb-eye-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const inp = document.getElementById(btn.dataset.target);
                if (!inp) return;
                inp.type = inp.type === 'password' ? 'text' : 'password';
            });
        });

        // ── Register form ────────────────────────────────
        const regForm  = document.getElementById(cfg.moduleId + '-reg-form');
        const regBtn   = document.getElementById(cfg.moduleId + '-reg-submit');
        const regAlert = document.getElementById(cfg.moduleId + '-reg-alert');

        if (regForm) {
            regForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const pw  = regForm.querySelector('[name="password"]');
                const pw2 = regForm.querySelector('[name="password2"]');
                if (pw && pw2 && pw.value !== pw2.value) {
                    showAlert(regAlert, 'Las contraseñas no coinciden.', 'error');
                    return;
                }

                const em  = regForm.querySelector('[name="email"]');
                const em2 = regForm.querySelector('[name="email2"]');
                if (em && em2 && em.value.trim() !== em2.value.trim()) {
                    showAlert(regAlert, 'Los correos electrónicos no coinciden.', 'error');
                    return;
                }

                setLoading(regBtn, true);

                if (cfg.enableCaptcha && cfg.captchaSiteKey && typeof grecaptcha !== 'undefined') {
                    // reCAPTCHA v3 cargado — obtener token
                    grecaptcha.ready(function () {
                        grecaptcha.execute(cfg.captchaSiteKey, { action: 'register' })
                            .then(function (token) {
                                var tf = document.getElementById(cfg.moduleId + '-recaptcha-token');
                                if (tf) tf.value = token;
                            })
                            .catch(function () { /* sin token, el servidor decide */ })
                            .finally(function () { doSubmit(); });
                    });
                } else {
                    // Sin reCAPTCHA o no cargado — enviar igualmente
                    doSubmit();
                }

                function doSubmit() {
                    // Enviamos los campos tal cual — firstname y surname por separado
                    // El servidor los concatena. No tocamos los valores del formulario.
                    fetch(cfg.ajaxUrl + '&method=register', {
                        method:  'POST',
                        body:    new FormData(regForm),
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    })
                    .then(r => r.json())
                    .then(function (json) {
                        const res = json.data || json;
                        if (res.success) {
                            mostrarBienvenida(res, regAlert, regBtn, cfg);
                        } else {
                            showAlert(regAlert, res.message || 'Error al registrar.', 'error');
                            setLoading(regBtn, false);
                        }
                    })
                    .catch(function () {
                        showAlert(regAlert, 'Error de conexión. Inténtalo de nuevo.', 'error');
                    })
                    .finally(function () {
                        setLoading(regBtn, false);
                    });
                }
            });
        }

        // ── Login form (POST nativo) ──────────────────────
        const loginForm = document.getElementById(cfg.moduleId + '-login-form');
        const loginBtn  = document.getElementById(cfg.moduleId + '-login-submit');
        if (loginForm && loginBtn) {
            loginForm.addEventListener('submit', function () {
                setLoading(loginBtn, true);
            });
        }

        // ── Google OAuth ─────────────────────────────────
        const googleBtn = document.getElementById(cfg.moduleId + '-google-btn');
        if (googleBtn && cfg.enableGoogle && cfg.clientId) {
            const cbName = 'hmbGoogleCallback_' + cfg.moduleId.split('-').pop();
            window[cbName] = function (response) {
                handleGoogle(response.credential, cfg, regAlert, regBtn);
            };
            googleBtn.addEventListener('click', function () {
                if (typeof google === 'undefined') return;
                google.accounts.id.initialize({
                    client_id: cfg.clientId,
                    callback:  window[cbName],
                    context:   'signup',
                });
                google.accounts.id.prompt();
            });
        }
    }

    function mostrarBienvenida(res, alertEl, btn, cfg) {
        const w     = cfg.welcome || {};
        const dur   = w.delay || 4000;
        const dest  = res.redirect || cfg.siteUrl || window.location.origin + '/';
        const title = w.title   || '¡Bienvenido a la Hermandad!';
        const sub   = w.subtitle|| 'Hermano del Metal';
        const msg   = res.message || w.message || 'Tu cuenta ha sido creada.';
        const img   = w.image   || '';
        const root  = document.getElementById(cfg.moduleId);
        const panel = root ? root.querySelector('#' + cfg.moduleId + '-panel-register') : null;

        if (panel) {
            const imgHtml = img
                ? '<img src="'+img+'" alt="" style="max-width:200px;max-height:160px;object-fit:contain;border-radius:4px;margin-bottom:1.25rem;opacity:.9">'
                : '<div style="font-size:2.8rem;margin-bottom:1rem">&#9760;</div>';

            panel.innerHTML =
                '<div style="text-align:center;padding:2.5rem 1.5rem">' +
                    imgHtml +
                    '<h2 style="font-size:1.1rem;color:#fff;margin:0 0 .4rem;letter-spacing:.06em">'+title+'</h2>' +
                    '<p style="font-size:.58rem;color:#c9a84c;letter-spacing:.22em;text-transform:uppercase;margin:0 0 1.1rem">'+sub+'</p>' +
                    '<p style="font-size:.82rem;color:#b0b0b0;line-height:1.6;margin:0 0 1.4rem">'+msg+'</p>' +
                    '<div style="height:3px;background:rgba(255,255,255,.07);border-radius:2px;overflow:hidden;max-width:200px;margin:0 auto">' +
                        '<div id="hmb-wbar-'+cfg.moduleId+'" style="height:100%;background:linear-gradient(90deg,#b41414,#c9a84c);border-radius:2px;width:0"></div>' +
                    '</div>' +
                    '<p style="font-size:.68rem;color:#888;margin-top:.75rem">Preparando tu perfil...</p>' +
                '</div>';

            const bar = document.getElementById('hmb-wbar-' + cfg.moduleId);
            if (bar) {
                bar.getBoundingClientRect();
                bar.style.transition = 'width ' + dur + 'ms linear';
                bar.style.width = '100%';
            }
        } else {
            showAlert(alertEl, msg, 'success');
        }
        setTimeout(function () { window.location.href = dest; }, dur);
    }

    function switchToPanel(root, targetId) {
        root.querySelectorAll('.hmb-panel').forEach(function (p) {
            p.classList.toggle('hmb-panel-active', p.id === targetId);
        });
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function handleGoogle(credential, cfg, alertEl, btn) {
        if (!credential) return;
        setLoading(btn, true);
        fetch(cfg.ajaxUrl + '&method=googleAuth', {
            method:  'POST',
            body:    new URLSearchParams({ credential, client_id: cfg.clientId, redirect: cfg.redirect }),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        })
        .then(r => r.json())
        .then(function (json) {
            const res = json.data || json;
            if (res.success) {
                showAlert(alertEl, res.message || '¡Bienvenido!', 'success');
                setTimeout(function () { window.location.href = res.redirect || cfg.redirect; }, 1000);
            } else {
                showAlert(alertEl, res.message || 'Error de Google.', 'error');
            }
        })
        .catch(function () { showAlert(alertEl, 'Error de conexión con Google.', 'error'); })
        .finally(function () { setLoading(btn, false); });
    }

    function showAlert(el, msg, type) {
        if (!el) return;
        el.className = 'hmb-alert hmb-alert-' + (type || 'error');
        el.innerHTML = msg;
        el.style.display = 'block';
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function setLoading(btn, on) {
        if (!btn) return;
        btn.disabled = on;
        btn.classList.toggle('hmb-loading', on);
    }

})();
