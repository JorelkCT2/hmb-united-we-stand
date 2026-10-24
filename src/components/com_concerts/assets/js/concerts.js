'use strict';
const CcLb = {
    init() {
        this.overlay = document.getElementById('ccLightbox');
        if (!this.overlay) return;
        this.img     = this.overlay.querySelector('.cc-lb-img');
        this.cap     = this.overlay.querySelector('.cc-lb-cap');
        this.btn     = this.overlay.querySelector('.cc-lb-close');
        this.overlay.addEventListener('click', e => { if (e.target === this.overlay) this.close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') this.close(); });
        // Reposition button when image loads
        this.img.addEventListener('load', () => this._positionBtn());
        window.addEventListener('resize', () => { if (this.overlay.classList.contains('open')) this._positionBtn(); });
    },
    open(src, cap) {
        if (!this.overlay) return;
        this.cap.textContent = cap || '';
        this.overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        // Reset btn position while loading
        if (this.btn) { this.btn.style.cssText = 'display:none'; }
        this.img.src = src;
    },
    close() {
        if (!this.overlay) return;
        this.overlay.classList.remove('open');
        document.body.style.overflow = '';
        this.img.src = '';
    },
    _positionBtn() {
        if (!this.btn || !this.img) return;
        const r = this.img.getBoundingClientRect();
        this.btn.style.cssText = [
            'display:flex',
            'position:fixed',
            'top:' + (r.top + 8) + 'px',
            'left:' + (r.right - 42) + 'px',
            'width:34px', 'height:34px',
            'border-radius:50%',
            'background:rgba(0,0,0,.8)',
            'border:2px solid rgba(255,255,255,.9)',
            'color:#fff',
            'font-size:1rem',
            'align-items:center',
            'justify-content:center',
            'cursor:pointer',
            'z-index:10002',
            'box-shadow:0 2px 8px rgba(0,0,0,.7)',
            'font-weight:700',
            'line-height:1',
        ].join(';');
    }
};

function ccToggleCal(btn) {
    const menu = btn.nextElementSibling;
    const open = menu.classList.contains('open');
    document.querySelectorAll('.cc-cal-menu.open').forEach(m => m.classList.remove('open'));
    if (!open) menu.classList.add('open');
}

document.addEventListener('DOMContentLoaded', () => {
    CcLb.init();
    document.addEventListener('click', e => {
        if (!e.target.closest('.cc-cal-wrap')) {
            document.querySelectorAll('.cc-cal-menu.open').forEach(m => m.classList.remove('open'));
        }
    });
    document.querySelectorAll('.cc-filters select').forEach(s => {
        s.addEventListener('change', () => s.closest('form').submit());
    });
});

/* ── Agenda v3: filtros en pantalla (búsqueda, provincia, mes) ── */
document.addEventListener('DOMContentLoaded', () => {
    const lista = document.getElementById('cc2-list');
    if (!lista) return;
    const q = document.getElementById('cc2-q'), prov = document.getElementById('cc2-prov');
    const meses = document.getElementById('cc2-months'), cuenta = document.getElementById('cc2-count');
    const ninguno = document.getElementById('cc2-none');
    const evs = [...lista.querySelectorAll('.cc2-ev')];
    let mes = '';
    const norm = s => s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');

    function aplicar() {
        const t = norm(q.value.trim()), p = prov.value;
        let n = 0;
        evs.forEach(e => {
            const ok = (!mes || e.dataset.mes === mes) && (!p || e.dataset.prov === p) && (!t || norm(e.dataset.q).includes(t));
            e.hidden = !ok;
            if (ok) n++;
        });
        lista.querySelectorAll('.cc2-month').forEach(s => { s.hidden = !s.querySelector('.cc2-ev:not([hidden])'); });
        cuenta.textContent = n + ' concierto' + (n === 1 ? '' : 's');
        if (ninguno) ninguno.hidden = n > 0;
    }
    q.addEventListener('input', aplicar);
    prov.addEventListener('change', aplicar);
    meses.addEventListener('click', e => {
        const b = e.target.closest('button[data-mes]');
        if (!b) return;
        mes = b.dataset.mes;
        meses.querySelectorAll('button').forEach(x => x.classList.toggle('on', x === b));
        aplicar();
    });
    const reset = document.getElementById('cc2-reset');
    if (reset) reset.addEventListener('click', e => {
        e.preventDefault(); q.value = ''; prov.value = ''; mes = '';
        meses.querySelectorAll('button').forEach((x, i) => x.classList.toggle('on', i === 0));
        aplicar();
    });
    aplicar();
});
