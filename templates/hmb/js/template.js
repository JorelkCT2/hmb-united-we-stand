/**
 * HMB Template - Heavy Metal Brothers
 * Main JavaScript
 */

(function () {
  'use strict';

  // ── Mobile menu toggle ──────────────────────────────────
  const toggle = document.querySelector('.hmb-menu-toggle');
  const menuWrap = document.querySelector('.hmb-menu-wrap');

  if (toggle && menuWrap) {
    toggle.addEventListener('click', function () {
      const expanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', String(!expanded));
      menuWrap.classList.toggle('is-open');
      document.body.style.overflow = expanded ? '' : 'hidden';
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
      if (!toggle.contains(e.target) && !menuWrap.contains(e.target)) {
        toggle.setAttribute('aria-expanded', 'false');
        menuWrap.classList.remove('is-open');
        document.body.style.overflow = '';
      }
    });
  }

  // ── Sticky header shadow on scroll ─────────────────────
  const header = document.querySelector('.hmb-header');
  if (header) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 10) {
        header.style.boxShadow = '0 4px 30px rgba(139,0,0,0.2)';
      } else {
        header.style.boxShadow = 'none';
      }
    }, { passive: true });
  }

  // ── Animate elements on scroll ─────────────────────────
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('hmb-animate');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.hmb-module, .hmb-component-inner h1, .hmb-component-inner h2').forEach(function (el) {
      observer.observe(el);
    });
  }

  // ── Active nav item ─────────────────────────────────────
  const currentUrl = window.location.href;
  document.querySelectorAll('.hmb-menu-wrap a').forEach(function (link) {
    if (link.href === currentUrl) {
      link.classList.add('active');
      const parent = link.closest('li');
      if (parent) parent.classList.add('active');
    }
  });

  // ── Dropdown hover for desktop ──────────────────────────
  if (window.innerWidth > 768) {
    document.querySelectorAll('.hmb-menu-wrap li').forEach(function (li) {
      const sub = li.querySelector('ul');
      if (sub) {
        li.addEventListener('mouseenter', function () { sub.style.display = 'flex'; });
        li.addEventListener('mouseleave', function () { sub.style.display = ''; });
      }
    });
  }

})();

// ── Floating login panel ────────────────────────────────────
(function () {
  var tab   = document.getElementById('hmb-float-tab');
  var panel = document.getElementById('hmb-float-panel');
  var close = document.getElementById('hmb-float-close');
  if (!tab || !panel) return;

  function openPanel() {
    panel.classList.add('is-open');
    tab.setAttribute('aria-expanded', 'true');
    tab.style.borderRadius = '6px 0 0 0';
  }

  function closePanel() {
    panel.classList.remove('is-open');
    tab.setAttribute('aria-expanded', 'false');
    tab.style.borderRadius = '6px 0 0 6px';
  }

  tab.addEventListener('click', function () {
    panel.classList.contains('is-open') ? closePanel() : openPanel();
  });

  if (close) {
    close.addEventListener('click', closePanel);
  }

  // Cerrar al hacer click fuera
  document.addEventListener('click', function (e) {
    var container = document.getElementById('hmb-float-login');
    if (container && !container.contains(e.target)) {
      closePanel();
    }
  });

  // Cerrar con Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closePanel();
  });
})();

// ── Mobile touch submenus ───────────────────────────────────
(function () {
  if (window.innerWidth > 768) return;

  function initTouchMenus() {
    var items = document.querySelectorAll(
      '.hmb-header-nav .hmb-nav-list > li.has-children, ' +
      '.hmb-header-nav .hmb-menu-wrap ul > li.has-children'
    );

    items.forEach(function (li) {
      var link = li.querySelector(':scope > a');
      var sub  = li.querySelector(':scope > ul, :scope > .hmb-nav-dropdown');
      if (!link || !sub) return;

      link.addEventListener('click', function (e) {
        // Si tiene submenu, primer tap abre/cierra, segundo tap navega
        if (!li.classList.contains('is-open')) {
          e.preventDefault();
          // Cerrar otros abiertos
          items.forEach(function (other) {
            if (other !== li) other.classList.remove('is-open');
          });
          li.classList.add('is-open');
        }
        // Si ya está abierto, deja navegar normalmente
      });
    });

    // Cerrar al tocar fuera
    document.addEventListener('touchstart', function (e) {
      var nav = document.querySelector('.hmb-header-nav');
      if (nav && !nav.contains(e.target)) {
        items.forEach(function (li) { li.classList.remove('is-open'); });
      }
    }, { passive: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTouchMenus);
  } else {
    initTouchMenus();
  }
})();


// ── Mobile touch submenus ───────────────────────────────────
(function () {

  function initTouchMenus() {
    // Añadir clase has-children a todos los li que tengan submenú
    document.querySelectorAll('.hmb-header-nav .hmb-menu-wrap ul li').forEach(function(li) {
      var sub = li.querySelector(':scope > ul');
      if (sub) li.classList.add('has-children');
    });

    var items = document.querySelectorAll('.hmb-header-nav .hmb-menu-wrap ul li.has-children');
    if (!items.length) return;

    items.forEach(function (li) {
      var link = li.querySelector(':scope > a');
      var sub  = li.querySelector(':scope > ul');
      if (!link || !sub) return;

      // Mostrar el submenu inline en móvil
      sub.style.display = 'none';

      link.addEventListener('click', function (e) {
        var isMobile = window.innerWidth <= 768;
        if (!isMobile) return;

        if (!li.classList.contains('is-open')) {
          e.preventDefault();
          e.stopPropagation();
          // Cerrar todos los demás
          items.forEach(function (other) {
            if (other !== li) {
              other.classList.remove('is-open');
              var otherSub = other.querySelector(':scope > ul');
              if (otherSub) otherSub.style.display = 'none';
            }
          });
          // Abrir este
          li.classList.add('is-open');
          sub.style.cssText = 'display:flex!important;flex-direction:column!important;position:static!important;width:100%!important;background:rgba(10,0,0,.95)!important;border-top:1px solid #8B0000!important;border-bottom:1px solid #8B0000!important;padding:.3rem 0!important;';
        } else {
          // Ya abierto — cerrar
          li.classList.remove('is-open');
          sub.style.display = 'none';
        }
      });
    });

    // Cerrar al tocar fuera
    document.addEventListener('touchstart', function (e) {
      var nav = document.querySelector('.hmb-header-nav');
      if (nav && !nav.contains(e.target)) {
        items.forEach(function (li) {
          li.classList.remove('is-open');
          var sub = li.querySelector(':scope > ul');
          if (sub) sub.style.display = 'none';
        });
      }
    }, { passive: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTouchMenus);
  } else {
    initTouchMenus();
  }
})();