/**
 * Heavy Metal Brothers Gallery — JavaScript
 * Features: Lightbox, Grid/Masonry toggle, Lazy-reveal, Keyboard nav
 * Vanilla JS, no dependencies
 * @version 1.0.0
 */

'use strict';

(function () {

    /* ───────────────────────────────────────────────────────────────────────
       STATE
    ─────────────────────────────────────────────────────────────────────── */
    let photos        = [];
    let currentIndex  = 0;
    let isOpen        = false;
    let currentMode   = 'grid'; // 'grid' | 'masonry'

    /* ───────────────────────────────────────────────────────────────────────
       DOM REFS
    ─────────────────────────────────────────────────────────────────────── */
    const galleryEl   = document.getElementById('hmg-gallery');
    const lightbox    = document.getElementById('hmg-lightbox');
    const lbImg       = document.getElementById('hmg-lightbox-img');
    const lbCaption   = document.getElementById('hmg-lightbox-caption');
    const lbCounter   = document.getElementById('hmg-lightbox-counter');
    const lbLoader    = document.getElementById('hmg-lightbox-loader');
    const lbClose     = document.getElementById('hmg-lightbox-close');
    const lbPrev      = document.getElementById('hmg-lightbox-prev');
    const lbNext      = document.getElementById('hmg-lightbox-next');
    const lbBackdrop  = document.getElementById('hmg-lightbox-backdrop');
    const btnGrid     = document.getElementById('hmg-view-grid');
    const btnMasonry  = document.getElementById('hmg-view-masonry');

    /* ───────────────────────────────────────────────────────────────────────
       INITIALISE
    ─────────────────────────────────────────────────────────────────────── */
    function init() {
        collectPhotos();
        bindLightboxTriggers();
        bindViewToggles();
        bindKeyboard();
        initLazyReveal();

        if (galleryEl) {
            if (currentMode === 'masonry') applyMasonry();
        }
    }

    /* ───────────────────────────────────────────────────────────────────────
       COLLECT PHOTO DATA from DOM
    ─────────────────────────────────────────────────────────────────────── */
    function collectPhotos() {
        if (!galleryEl) return;
        const figures = galleryEl.querySelectorAll('.hmg-photo');
        photos = Array.from(figures).map(fig => ({
            src:    fig.dataset.src    || '',
            title:  fig.dataset.title  || '',
            index:  parseInt(fig.dataset.index, 10) || 0,
            width:  parseInt(fig.dataset.width, 10)  || 0,
            height: parseInt(fig.dataset.height, 10) || 0,
        }));
    }

    /* ───────────────────────────────────────────────────────────────────────
       LIGHTBOX
    ─────────────────────────────────────────────────────────────────────── */
    function bindLightboxTriggers() {
        if (!galleryEl || !lightbox) return;

        galleryEl.addEventListener('click', (e) => {
            const trigger = e.target.closest('.hmg-lightbox-trigger');
            if (!trigger) return;
            e.preventDefault();
            const idx = parseInt(trigger.dataset.index, 10);
            openLightbox(idx);
        });
    }

    function openLightbox(index) {
        currentIndex = index;
        isOpen       = true;
        lightbox.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        loadLightboxImage(index);
        updateLightboxNav();
    }

    function closeLightbox() {
        isOpen = false;
        lightbox.setAttribute('hidden', '');
        document.body.style.overflow = '';
        lbImg.src = '';
    }

    function loadLightboxImage(index) {
        if (!photos[index]) return;
        const photo = photos[index];

        // Show loader
        lbImg.classList.add('hmg-loading');
        lbLoader.classList.add('hmg-visible');

        const tempImg  = new Image();
        tempImg.onload = () => {
            lbImg.src = photo.src;
            lbImg.alt = photo.title;
            lbImg.classList.remove('hmg-loading');
            lbLoader.classList.remove('hmg-visible');
        };
        tempImg.onerror = () => {
            lbLoader.classList.remove('hmg-visible');
            lbImg.classList.remove('hmg-loading');
        };
        tempImg.src = photo.src;

        lbCaption.textContent = photo.title;
        lbCounter.textContent = `${index + 1} / ${photos.length}`;
    }

    function navigateLightbox(direction) {
        currentIndex = (currentIndex + direction + photos.length) % photos.length;
        loadLightboxImage(currentIndex);
        updateLightboxNav();
    }

    function updateLightboxNav() {
        if (!lbPrev || !lbNext) return;
        lbPrev.style.display = photos.length > 1 ? '' : 'none';
        lbNext.style.display = photos.length > 1 ? '' : 'none';
    }

    // Bind lightbox controls
    if (lbClose)   lbClose.addEventListener('click', closeLightbox);
    if (lbBackdrop) lbBackdrop.addEventListener('click', closeLightbox);
    if (lbPrev)    lbPrev.addEventListener('click', () => navigateLightbox(-1));
    if (lbNext)    lbNext.addEventListener('click', () => navigateLightbox(1));

    /* Touch / swipe on lightbox */
    let touchStartX = 0;
    if (lightbox) {
        lightbox.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].clientX;
        }, { passive: true });

        lightbox.addEventListener('touchend', (e) => {
            const dx = e.changedTouches[0].clientX - touchStartX;
            if (Math.abs(dx) > 60) {
                navigateLightbox(dx < 0 ? 1 : -1);
            }
        }, { passive: true });
    }

    /* ───────────────────────────────────────────────────────────────────────
       KEYBOARD NAVIGATION
    ─────────────────────────────────────────────────────────────────────── */
    function bindKeyboard() {
        document.addEventListener('keydown', (e) => {
            if (!isOpen) return;
            switch (e.key) {
                case 'ArrowLeft':  e.preventDefault(); navigateLightbox(-1); break;
                case 'ArrowRight': e.preventDefault(); navigateLightbox(1);  break;
                case 'Escape':     e.preventDefault(); closeLightbox();       break;
                case 'ArrowUp':    e.preventDefault(); navigateLightbox(-1); break;
                case 'ArrowDown':  e.preventDefault(); navigateLightbox(1);  break;
            }
        });
    }

    /* ───────────────────────────────────────────────────────────────────────
       VIEW TOGGLES (Grid / Masonry)
    ─────────────────────────────────────────────────────────────────────── */
    function bindViewToggles() {
        if (!btnGrid || !btnMasonry || !galleryEl) return;

        btnGrid.addEventListener('click', () => {
            setViewMode('grid');
        });

        btnMasonry.addEventListener('click', () => {
            setViewMode('masonry');
        });
    }

    function setViewMode(mode) {
        if (!galleryEl) return;
        currentMode = mode;
        galleryEl.className = galleryEl.className.replace(/hmg-gallery--(grid|masonry)/, '');
        galleryEl.classList.add('hmg-gallery--' + mode);

        if (btnGrid)    btnGrid.classList.toggle('hmg-btn--active', mode === 'grid');
        if (btnMasonry) btnMasonry.classList.toggle('hmg-btn--active', mode === 'masonry');

        if (mode === 'masonry') {
            // Slight delay to let images settle
            setTimeout(applyMasonry, 100);
        } else {
            // Reset grid-row-end styles
            const items = galleryEl.querySelectorAll('.hmg-photo');
            items.forEach(item => { item.style.gridRowEnd = ''; });
        }
    }

    /* ───────────────────────────────────────────────────────────────────────
       MASONRY LAYOUT (CSS Grid approach)
    ─────────────────────────────────────────────────────────────────────── */
    function applyMasonry() {
        if (!galleryEl) return;
        const items   = galleryEl.querySelectorAll('.hmg-photo');
        const rowGap  = parseInt(getComputedStyle(galleryEl).rowGap, 10) || 10;
        const rowSize = 10; // must match grid-auto-rows in CSS

        items.forEach(item => {
            const link = item.querySelector('.hmg-photo__link');
            if (!link) return;
            const img  = item.querySelector('.hmg-photo__thumb');
            if (!img)  return;

            const applySpan = () => {
                const h    = link.getBoundingClientRect().height;
                const span = Math.ceil((h + rowGap) / (rowSize + rowGap));
                item.style.gridRowEnd = `span ${span}`;
            };

            if (img.complete) {
                applySpan();
            } else {
                img.addEventListener('load', applySpan, { once: true });
            }
        });
    }

    window.addEventListener('resize', () => {
        if (currentMode === 'masonry') applyMasonry();
    });

    /* ───────────────────────────────────────────────────────────────────────
       LAZY REVEAL via IntersectionObserver
    ─────────────────────────────────────────────────────────────────────── */
    function initLazyReveal() {
        const revealEls = document.querySelectorAll('.hmg-photo, .hmg-album-card');
        if (!revealEls.length) return;

        if (!('IntersectionObserver' in window)) {
            // Fallback: reveal all immediately
            revealEls.forEach(el => el.classList.add('hmg-revealed'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (!entry.isIntersecting) return;
                // Stagger reveal
                setTimeout(() => {
                    entry.target.classList.add('hmg-revealed');
                }, i * 40);
                observer.unobserve(entry.target);
            });
        }, {
            rootMargin: '0px 0px -50px 0px',
            threshold:  0.05,
        });

        revealEls.forEach(el => observer.observe(el));
    }

    /* ───────────────────────────────────────────────────────────────────────
       BOOT
    ─────────────────────────────────────────────────────────────────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
