/**
 * HMB Logo Module JS — Generador de partículas
 */
(function () {
  'use strict';

  var PARTICLE_COLORS = ['#CC2200','#FF4500','#8B0000','#DAA520','#FF8C00'];
  var PARTICLE_COUNT  = 8;

  function randomBetween(a, b) {
    return a + Math.random() * (b - a);
  }

  function createParticle(container, bounds) {
    var el = document.createElement('span');
    el.className = 'hmb-particle';

    var startX = randomBetween(0, bounds.width);
    var startY = randomBetween(bounds.height * 0.5, bounds.height);

    var tx1 = randomBetween(-20, 20) + 'px';
    var ty1 = randomBetween(-15, -30) + 'px';
    var tx2 = randomBetween(-35, 35) + 'px';
    var ty2 = randomBetween(-40, -80) + 'px';

    var color    = PARTICLE_COLORS[Math.floor(Math.random() * PARTICLE_COLORS.length)];
    var size     = randomBetween(2, 4);
    var duration = randomBetween(1.8, 3.5);
    var delay    = randomBetween(0, duration);

    el.style.cssText = [
      'left:'       + startX + 'px',
      'top:'        + startY + 'px',
      'width:'      + size + 'px',
      'height:'     + size + 'px',
      'background:' + color,
      'box-shadow: 0 0 ' + (size * 2) + 'px ' + color,
      '--tx1:'      + tx1,
      '--ty1:'      + ty1,
      '--tx2:'      + tx2,
      '--ty2:'      + ty2,
      'animation-duration:' + duration + 's',
      'animation-delay:-'  + delay + 's',
    ].join(';');

    container.appendChild(el);
    return el;
  }

  function initLogo(wrap) {
    var effect    = wrap.dataset.effect || 'glow';
    var container = wrap.querySelector('.hmb-logo-particles');
    var img       = wrap.querySelector('.hmb-logo-img');

    if (!container || !img || effect === 'none') return;

    // Esperar a que la imagen cargue para obtener dimensiones reales
    function setup() {
      var bounds = { width: img.offsetWidth || 160, height: img.offsetHeight || 80 };

      // Limpiar partículas anteriores
      container.innerHTML = '';

      for (var i = 0; i < PARTICLE_COUNT; i++) {
        createParticle(container, bounds);
      }
    }

    if (img.complete) {
      setup();
    } else {
      img.addEventListener('load', setup);
    }

    // Intensificar partículas en hover
    var link = wrap.querySelector('.hmb-logo-link');
    if (link) {
      link.addEventListener('mouseenter', function () {
        // Añadir partículas extra temporales
        var bounds = { width: img.offsetWidth || 160, height: img.offsetHeight || 80 };
        for (var i = 0; i < 5; i++) {
          var p = createParticle(container, bounds);
          p.style.animationDelay = '0s';
          // Eliminar tras la animación
          var dur = parseFloat(p.style.animationDuration) * 1000 || 2000;
          setTimeout(function(el) { if (el.parentNode) el.parentNode.removeChild(el); }, dur, p);
        }
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hmb-logo-wrap').forEach(initLogo);
  });

})();
