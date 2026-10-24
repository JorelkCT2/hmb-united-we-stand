/**
 * HMB Banner Switcher Pro v4
 * Carrusel con scroll-snap: el deslizar con el dedo / rueda / trackpad lo hace el navegador (funciona igual en móvil y escritorio).
 * Este script solo añade: autoplay con pausa inteligente, flechas, puntos, barra de progreso, teclado y accesibilidad.
 */
(function () {
  'use strict';

  var REDUCE = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function init(el) {
    var vp     = el.querySelector('.hmb-slider__viewport');
    var slides = [].slice.call(el.querySelectorAll('.hmb-slider__slide'));
    var dots   = [].slice.call(el.querySelectorAll('.hmb-slider__dot'));
    var prev   = el.querySelector('.hmb-slider__arrow--prev');
    var next   = el.querySelector('.hmb-slider__arrow--next');
    var pauseB = el.querySelector('.hmb-slider__pause');
    var n      = slides.length;

    if (!vp || n < 2) { el.classList.add('is-single'); return; }

    var interval = parseInt(el.getAttribute('data-interval'), 10) || 4500;
    var wantAuto = el.getAttribute('data-autoplay') !== '0' && !REDUCE;
    var idx = 0, timer = null, userPaused = false, hold = 0, visible = true, ticking = false;

    el.style.setProperty('--hmb-sl-ms', interval + 'ms');

    // ── Marca la diapositiva activa (a11y + puntos) ─────────────────────
    function mark(i) {
      idx = i;
      slides.forEach(function (s, k) {
        var on = k === i;
        s.classList.toggle('is-active', on);
        s.setAttribute('aria-hidden', on ? 'false' : 'true');
        [].forEach.call(s.querySelectorAll('a'), function (a) { a.tabIndex = on ? 0 : -1; });
      });
      dots.forEach(function (d, k) {
        var on = k === i;
        d.classList.toggle('is-active', on);
        if (on) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current');
      });
    }

    function go(i, smooth) {
      i = (i + n) % n;
      mark(i);
      vp.scrollTo({ left: slides[i].offsetLeft, behavior: (smooth === false || REDUCE) ? 'auto' : 'smooth' });
    }

    // ── La posición real la manda el scroll (también cuando se desliza a mano) ──
    function onScroll() {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(function () {
        ticking = false;
        var w = vp.clientWidth || 1;
        var i = Math.round(vp.scrollLeft / w);
        if (i !== idx && i >= 0 && i < n) { mark(i); restart(); }
      });
    }
    vp.addEventListener('scroll', onScroll, { passive: true });

    // ── Autoplay con progreso (CSS) ─────────────────────────────────────
    function canRun() { return wantAuto && !userPaused && hold === 0 && visible && !document.hidden; }

    function clear() { clearTimeout(timer); timer = null; el.classList.remove('is-running'); }

    function restart() {
      clear();
      if (!canRun()) return;
      void el.offsetWidth;              // reinicia la animación de la barra
      el.classList.add('is-running');
      timer = setTimeout(function () { go(idx + 1); }, interval);
    }

    function holdOn()  { hold++; clear(); }
    function holdOff() { hold = Math.max(0, hold - 1); restart(); }

    // Pausas: ratón encima, foco dentro, dedo tocando
    el.addEventListener('mouseenter', function () { if (matchMedia('(hover: hover)').matches) holdOn(); });
    el.addEventListener('mouseleave', function () { if (matchMedia('(hover: hover)').matches) holdOff(); });
    el.addEventListener('focusin',  function () { holdOn(); });
    el.addEventListener('focusout', function () { holdOff(); });
    vp.addEventListener('touchstart', function () { clear(); }, { passive: true });
    vp.addEventListener('touchend',   function () { setTimeout(restart, 800); }, { passive: true });

    document.addEventListener('visibilitychange', function () { if (document.hidden) clear(); else restart(); });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) { visible = es[0].isIntersecting; if (visible) restart(); else clear(); }, { threshold: 0.2 }).observe(el);
    }

    // ── Controles ───────────────────────────────────────────────────────
    function user(fn) { return function (e) { if (e) e.preventDefault(); fn(); restart(); }; }
    if (prev) prev.addEventListener('click', user(function () { go(idx - 1); }));
    if (next) next.addEventListener('click', user(function () { go(idx + 1); }));
    dots.forEach(function (d) {
      d.addEventListener('click', user(function () { go(parseInt(d.getAttribute('data-index'), 10) || 0); }));
    });

    if (pauseB) {
      if (!wantAuto) pauseB.hidden = true;
      pauseB.addEventListener('click', function () {
        userPaused = !userPaused;
        pauseB.setAttribute('aria-pressed', userPaused ? 'true' : 'false');
        pauseB.setAttribute('aria-label', userPaused ? 'Reanudar el carrusel' : 'Pausar el carrusel');
        el.classList.toggle('is-paused', userPaused);
        if (userPaused) clear(); else restart();
      });
    }

    vp.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft')  { e.preventDefault(); go(idx - 1); restart(); }
      else if (e.key === 'ArrowRight') { e.preventDefault(); go(idx + 1); restart(); }
      else if (e.key === 'Home') { e.preventDefault(); go(0); restart(); }
      else if (e.key === 'End')  { e.preventDefault(); go(n - 1); restart(); }
    });

    // Al cambiar el tamaño (girar el móvil) se recoloca sin animación
    var rt;
    window.addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(function () { go(idx, false); }, 150); });

    mark(0);
    restart();
  }

  function boot() { [].forEach.call(document.querySelectorAll('.hmb-slider'), init); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
