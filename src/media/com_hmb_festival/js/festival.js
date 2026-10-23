/* HMB Festival Mode - v0.1.0 */
(function () {
  'use strict';

  var app = document.getElementById('hmbfest-app');
  if (!app) { return; }

  var cfg = JSON.parse(app.getAttribute('data-cfg'));

  var state = {
    festivals: [],
    checkins: [],
    currentFestival: 0,
    pendingFestival: null,
    chat: { matchId: 0, userId: 0, festivalId: 0, lastId: 0, timer: null }
  };

  /* ---------- utilidades ---------- */

  function api(action, data, cb) {
    var body = new URLSearchParams();
    body.set(cfg.token, '1');
    if (data) {
      Object.keys(data).forEach(function (k) { body.set(k, data[k]); });
    }
    fetch(cfg.endpoint + action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (json) { cb(json.data || json); })
      .catch(function () { toast('Error de conexión. Inténtalo de nuevo.'); });
  }

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function toast(msg) {
    var t = document.getElementById('hmbfest-toast');
    t.textContent = msg;
    t.hidden = false;
    clearTimeout(t._timer);
    t._timer = setTimeout(function () { t.hidden = true; }, 3500);
  }

  function intentLabel(intent) {
    if (intent === 'ligar') { return 'Algo más 🔥'; }
    if (intent === 'ambas') { return 'Abierto a todo'; }
    return 'Compañeros 🍺';
  }

  function fmtDate(iso) {
    if (!iso) { return ''; }
    var p = String(iso).substring(0, 10).split('-');
    if (p.length !== 3) { return iso; }
    return p[2] + '/' + p[1];
  }

  /* ---------- pestañas ---------- */

  var tabs = app.querySelectorAll('.hmbfest-tab');
  var panels = app.querySelectorAll('.hmbfest-panel');

  function showTab(name) {
    tabs.forEach(function (t) { t.classList.toggle('is-active', t.getAttribute('data-tab') === name); });
    panels.forEach(function (p) { p.classList.toggle('is-active', p.getAttribute('data-panel') === name); });
    if (name === 'gente') { renderGenteSelector(); }
    if (name === 'matches') { loadMatches(); }
  }

  tabs.forEach(function (t) {
    t.addEventListener('click', function () { showTab(t.getAttribute('data-tab')); });
  });

  /* ---------- festivales ---------- */

  function loadState() {
    api('state', null, function (d) {
      if (!d.ok) { toast(d.error || 'Error'); return; }
      state.festivals = d.festivals || [];
      state.checkins = d.checkins || [];
      renderFestivals();
      renderGenteSelector();
    });
  }

  function isCheckedIn(festivalId) {
    return state.checkins.some(function (c) { return c.festival_id === festivalId; });
  }

  function renderFestivals() {
    var box = document.getElementById('hmbfest-festivales');
    if (!state.festivals.length) {
      box.innerHTML = '<p class="hmbfest-empty">No hay festivales próximos en el mapa. ' +
        'Revisa la integración con MetalMap en las opciones del componente.</p>';
      return;
    }
    box.innerHTML = state.festivals.map(function (f) {
      var checked = isCheckedIn(f.id);
      return '<article class="hmbfest-card hmbfest-fest' + (checked ? ' is-in' : '') + '">' +
        '<div class="hmbfest-fest-dates">' + esc(fmtDate(f.start)) + ' – ' + esc(fmtDate(f.end)) + '</div>' +
        '<h3>' + esc(f.name) + '</h3>' +
        (f.city ? '<p class="hmbfest-muted">' + esc(f.city) + '</p>' : '') +
        '<p class="hmbfest-count">' + f.count + ' metalero' + (f.count === 1 ? '' : 's') + ' dentro</p>' +
        (checked
          ? '<div class="hmbfest-row"><button class="hmbfest-btn hmbfest-btn-small" data-go="' + f.id + '">Ver gente</button>' +
            '<button class="hmbfest-btn hmbfest-btn-ghost hmbfest-btn-small" data-out="' + f.id + '">Salir</button></div>'
          : '<button class="hmbfest-btn hmbfest-btn-small" data-in="' + f.id + '">Check-in 🤘</button>') +
        '</article>';
    }).join('');

    box.querySelectorAll('[data-in]').forEach(function (b) {
      b.addEventListener('click', function () { openCheckin(parseInt(b.getAttribute('data-in'), 10)); });
    });
    box.querySelectorAll('[data-out]').forEach(function (b) {
      b.addEventListener('click', function () {
        api('checkout', { festival_id: b.getAttribute('data-out') }, function () {
          toast('Check-out hecho. Hasta la próxima.');
          loadState();
        });
      });
    });
    box.querySelectorAll('[data-go]').forEach(function (b) {
      b.addEventListener('click', function () {
        state.currentFestival = parseInt(b.getAttribute('data-go'), 10);
        showTab('gente');
        loadPeople();
      });
    });
  }

  /* ---------- check-in modal ---------- */

  var modal = document.getElementById('hmbfest-checkin-modal');
  var adultRow = document.getElementById('hmbfest-adult-row');

  function openCheckin(festivalId) {
    state.pendingFestival = festivalId;
    var fest = state.festivals.filter(function (f) { return f.id === festivalId; })[0];
    document.getElementById('hmbfest-checkin-title').textContent = 'Check-in: ' + (fest ? fest.name : '');
    document.getElementById('hmbfest-bio').value = '';
    document.getElementById('hmbfest-adult').checked = false;
    modal.hidden = false;
  }

  app.querySelectorAll('input[name="hmbfest-intent"]').forEach(function (r) {
    r.addEventListener('change', function () {
      adultRow.hidden = (r.value === 'colegas');
    });
  });

  document.getElementById('hmbfest-checkin-cancel').addEventListener('click', function () {
    modal.hidden = true;
  });

  document.getElementById('hmbfest-checkin-go').addEventListener('click', function () {
    var intent = app.querySelector('input[name="hmbfest-intent"]:checked').value;
    api('checkin', {
      festival_id: state.pendingFestival,
      intent: intent,
      bio: document.getElementById('hmbfest-bio').value,
      adult: document.getElementById('hmbfest-adult').checked ? 1 : 0
    }, function (d) {
      if (!d.ok) { toast(d.error || 'Error'); return; }
      modal.hidden = true;
      toast('Dentro. Cuernos arriba 🤘');
      state.currentFestival = state.pendingFestival;
      loadState();
      showTab('gente');
      loadPeople();
    });
  });

  /* ---------- gente ---------- */

  function renderGenteSelector() {
    var box = document.getElementById('hmbfest-gente-selector');
    var mine = state.checkins;
    if (!mine.length) {
      box.innerHTML = '<p class="hmbfest-empty">Haz check-in en un festival para ver quién anda por allí.</p>';
      document.getElementById('hmbfest-gente').innerHTML = '';
      return;
    }
    if (!state.currentFestival) { state.currentFestival = mine[0].festival_id; }
    box.innerHTML = '<div class="hmbfest-selector">' + mine.map(function (c) {
      return '<button class="hmbfest-chip' + (c.festival_id === state.currentFestival ? ' is-active' : '') +
        '" data-fest="' + c.festival_id + '">' + esc(c.festival_name) + '</button>';
    }).join('') + '</div>';
    box.querySelectorAll('[data-fest]').forEach(function (b) {
      b.addEventListener('click', function () {
        state.currentFestival = parseInt(b.getAttribute('data-fest'), 10);
        renderGenteSelector();
        loadPeople();
      });
    });
    loadPeople();
  }

  function affinityMeter(score, common) {
    if (score === null || score === undefined) {
      return '<div class="hmbfest-aff hmbfest-aff-na">Afinidad desconocida</div>';
    }
    var label = common && common.length ? 'Ambos: ' + common.map(esc).join(', ') : '';
    return '<div class="hmbfest-aff">' +
      '<div class="hmbfest-aff-bar"><span style="width:' + score + '%"></span></div>' +
      '<div class="hmbfest-aff-num">' + score + '<small>/100</small></div>' +
      (label ? '<div class="hmbfest-aff-common">' + label + '</div>' : '') +
      '</div>';
  }

  function loadPeople() {
    if (!state.currentFestival) { return; }
    api('people', { festival_id: state.currentFestival }, function (d) {
      var box = document.getElementById('hmbfest-gente');
      if (!d.ok) { box.innerHTML = '<p class="hmbfest-empty">' + esc(d.error || 'Error') + '</p>'; return; }
      var people = d.people || [];
      var banner = '';
      if (d.horns_received > 0) {
        banner = '<div class="hmbfest-hornbanner">🤘 ' + d.horns_received +
          (d.horns_received === 1 ? ' persona te ha' : ' personas te han') +
          ' enviado cuernos en este festival. Devuélvelos y habrá match.</div>';
      }
      if (!people.length) {
        box.innerHTML = banner + '<p class="hmbfest-empty">Aún no hay nadie compatible aquí. ' +
          'Comparte el enlace y trae a tu cuadrilla 🤘</p>';
        return;
      }
      box.innerHTML = banner + people.map(function (p) {
        return '<article class="hmbfest-card hmbfest-person">' +
          '<div class="hmbfest-person-head">' +
          '<h3>' + esc(p.name) + '</h3>' +
          '<span class="hmbfest-pill hmbfest-pill-' + esc(p.intent) + '">' + intentLabel(p.intent) + '</span>' +
          '</div>' +
          (p.bio ? '<p class="hmbfest-bio">' + esc(p.bio) + '</p>' : '') +
          affinityMeter(p.affinity, p.common) +
          '<div class="hmbfest-row">' +
          (p.matched
            ? '<span class="hmbfest-matched">MATCH 🤘</span>'
            : (p.horned
              ? '<span class="hmbfest-horned">Cuernos enviados</span>'
              : '<button class="hmbfest-btn hmbfest-btn-small" data-horn="' + p.user_id + '">Dar cuernos 🤘</button>')) +
          '<button class="hmbfest-mini" data-block="' + p.user_id + '" title="Bloquear">⛔</button>' +
          '</div>' +
          '</article>';
      }).join('');

      box.querySelectorAll('[data-horn]').forEach(function (b) {
        b.addEventListener('click', function () {
          api('horn', { user_id: b.getAttribute('data-horn'), festival_id: state.currentFestival }, function (r) {
            if (!r.ok) { toast(r.error || 'Error'); return; }
            if (r.matched) { showMatchSplash(); }
            loadPeople();
          });
        });
      });
      box.querySelectorAll('[data-block]').forEach(function (b) {
        b.addEventListener('click', function () {
          if (!window.confirm('¿Bloquear a este usuario? No os volveréis a ver aquí.')) { return; }
          api('block', { user_id: b.getAttribute('data-block') }, function () {
            toast('Usuario bloqueado.');
            loadPeople();
          });
        });
      });
    });
  }

  /* ---------- match splash ---------- */

  function showMatchSplash() {
    var splash = document.getElementById('hmbfest-matchsplash');
    document.getElementById('hmbfest-matchsplash-text').textContent =
      'Cuernos recíprocos. Ya podéis hablar en la pestaña Matches.';
    splash.hidden = false;
  }

  document.getElementById('hmbfest-matchsplash-close').addEventListener('click', function () {
    document.getElementById('hmbfest-matchsplash').hidden = true;
    showTab('matches');
  });

  /* ---------- matches y chat ---------- */

  var knownMatchIds = null;

  function loadMatches(announce) {
    api('matches', null, function (d) {
      var box = document.getElementById('hmbfest-matches');
      var matches = d.matches || [];
      var unread = matches.reduce(function (n, m) { return n + (m.unread || 0); }, 0);
      var badge = document.getElementById('hmbfest-unread');
      badge.textContent = unread;
      badge.hidden = unread === 0;

      // ¿Match nuevo desde el último latido? Celebración automática.
      if (announce && knownMatchIds) {
        var fresh = matches.some(function (m) { return knownMatchIds.indexOf(m.match_id) === -1; });
        if (fresh) { showMatchSplash(); }
      }
      knownMatchIds = matches.map(function (m) { return m.match_id; });

      var panelActive = app.querySelector('.hmbfest-panel[data-panel="matches"]').classList.contains('is-active');
      if (announce && !panelActive) { return; }

      if (!matches.length) {
        box.innerHTML = '<p class="hmbfest-empty">Todavía sin matches. Reparte cuernos en la pestaña Gente.</p>';
        return;
      }
      box.innerHTML = matches.map(function (m) {
        return '<button class="hmbfest-matchrow" data-match="' + m.match_id + '" data-user="' + m.user_id + '" data-festival="' + m.festival_id + '" data-name="' + esc(m.name) + '">' +
          '<span class="hmbfest-matchrow-name">' + esc(m.name) +
          (m.unread ? ' <span class="hmbfest-badge">' + m.unread + '</span>' : '') + '</span>' +
          '<span class="hmbfest-matchrow-fest">' + esc(m.festival) + '</span>' +
          (m.last ? '<span class="hmbfest-matchrow-last">' + esc(m.last) + '</span>' : '<span class="hmbfest-matchrow-last hmbfest-muted">Rompe el hielo…</span>') +
          '</button>';
      }).join('');

      box.querySelectorAll('[data-match]').forEach(function (b) {
        b.addEventListener('click', function () {
          openChat(
            parseInt(b.getAttribute('data-match'), 10),
            parseInt(b.getAttribute('data-user'), 10),
            b.getAttribute('data-name'),
            parseInt(b.getAttribute('data-festival'), 10)
          );
        });
      });
    });
  }

  var chatModal = document.getElementById('hmbfest-chat-modal');
  var chatLog = document.getElementById('hmbfest-chat-log');

  function openChat(matchId, userId, name, festivalId) {
    state.chat.matchId = matchId;
    state.chat.userId = userId;
    state.chat.festivalId = festivalId || 0;
    state.chat.lastId = 0;
    chatLog.innerHTML = '';
    document.getElementById('hmbfest-chat-name').textContent = name;
    chatModal.hidden = false;
    pollChat();
    state.chat.timer = setInterval(pollChat, 8000);
  }

  function closeChat() {
    chatModal.hidden = true;
    clearInterval(state.chat.timer);
    state.chat.timer = null;
    loadMatches();
  }

  function pollChat() {
    if (!state.chat.matchId) { return; }
    api('messages', { match_id: state.chat.matchId, since: state.chat.lastId }, function (d) {
      if (!d.ok) { return; }
      (d.messages || []).forEach(function (m) {
        state.chat.lastId = Math.max(state.chat.lastId, m.id);
        var div = document.createElement('div');
        div.className = 'hmbfest-msg' + (m.mine ? ' is-mine' : '');
        div.textContent = m.body;
        chatLog.appendChild(div);
      });
      if ((d.messages || []).length) {
        chatLog.scrollTop = chatLog.scrollHeight;
      }
    });
  }

  function sendChat() {
    var input = document.getElementById('hmbfest-chat-text');
    var body = input.value.trim();
    if (!body) { return; }
    api('send', { match_id: state.chat.matchId, body: body }, function (d) {
      if (!d.ok) { toast(d.error || 'Error'); return; }
      input.value = '';
      pollChat();
    });
  }

  document.getElementById('hmbfest-chat-send').addEventListener('click', sendChat);
  document.getElementById('hmbfest-chat-text').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { sendChat(); }
  });
  document.getElementById('hmbfest-chat-close').addEventListener('click', closeChat);

  document.getElementById('hmbfest-chat-block').addEventListener('click', function () {
    if (!window.confirm('¿Bloquear a este usuario? El match desaparecerá de tu lista.')) { return; }
    api('block', { user_id: state.chat.userId }, function () {
      toast('Usuario bloqueado.');
      closeChat();
    });
  });

  document.getElementById('hmbfest-chat-report').addEventListener('click', function () {
    var reason = window.prompt('Cuéntanos qué ha pasado (lo verá el admin):');
    if (reason === null) { return; }
    api('report', { user_id: state.chat.userId, festival_id: state.chat.festivalId, reason: reason }, function () {
      toast('Denuncia enviada. Gracias por cuidar la comunidad.');
    });
  });

  /* ---------- arranque ---------- */

  loadState();
  loadMatches();

  // Latido global: detecta matches y mensajes nuevos sin recargar,
  // y refresca la lista de gente si esa pestaña está a la vista.
  setInterval(function () {
    if (document.hidden) { return; }
    loadMatches(true);
    var genteActive = app.querySelector('.hmbfest-panel[data-panel="gente"]').classList.contains('is-active');
    var chatOpen = !document.getElementById('hmbfest-chat-modal').hidden;
    if (genteActive && state.currentFestival && !chatOpen) {
      loadPeople();
    }
  }, 15000);
})();
