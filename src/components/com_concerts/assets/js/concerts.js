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
