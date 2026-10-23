/**
 * Heavy Metal Brothers — Login Module JS  v1.0
 */
'use strict';

(function () {

    document.addEventListener('DOMContentLoaded', function () {
        const cfg = Joomla.getOptions('mod_hmb_login');
        if (!cfg) return;

        const instances = Array.isArray(cfg) ? cfg : [cfg];
        instances.forEach(initLogin);
    });

    function initLogin(cfg) {
        const root      = document.getElementById(cfg.moduleId);
        if (!root) return;

        const form      = document.getElementById(cfg.moduleId + '-form');
        const submitBtn = document.getElementById(cfg.moduleId + '-submit');

        // ── Password toggle ──────────────────────────────
        // (Handled by the inline onclick on each .hmb-eye-toggle button.
        //  A JS listener here too would double-toggle and cancel out.)

        // ── Loading state on submit ──────────────────────
        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.classList.add('hmb-loading');
            });
        }
    }

})();
