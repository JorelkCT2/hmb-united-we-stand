/**
 * Metal Quiz — comprobador de vídeos en el admin.
 *
 *  - Listado: botón "Revisar vídeos" que consulta la API oEmbed (vía servidor)
 *    para cada fila y pinta un punto verde/rojo/ámbar. Detecta borrados/privados.
 *  - Ficha de edición: botón "Probar reproducción" que carga el reproductor real
 *    de YouTube y detecta si el vídeo está BLOQUEADO para embeber (lo que oEmbed
 *    no puede ver).
 */
(function () {
	'use strict';

	var L = (window.Joomla && Joomla.getOptions) ? (Joomla.getOptions('com_hmb_quiz.vid') || {}) : {};
	var BASE = L.base || 'index.php?option=com_hmb_quiz&task=song.checkVideo&format=json';
	var T = L.txt || {};
	function t(k, d) { return T[k] || d || k; }

	function extractId(v) {
		v = (v || '').trim();
		var m = v.match(/(?:youtu\.be\/|[?&]v=|\/embed\/|\/shorts\/|\/v\/)([A-Za-z0-9_-]{11})/);
		if (m) { return m[1]; }
		if (/^[A-Za-z0-9_-]{11}$/.test(v)) { return v; }
		return '';
	}

	// ---- 1) LISTADO: comprobación en lote vía oEmbed ----------------------
	function initList() {
		var btn  = document.getElementById('hmbqCheckAll');
		var dots = Array.prototype.slice.call(document.querySelectorAll('.hmbq-vid-dot'));
		if (!btn || !dots.length) { return; }

		var COLORS = { ok: '#1f7a33', bad: '#b21f2d', warn: '#9a6b00' };
		var WORD   = { ok: '✓', bad: '✗', warn: '?' };

		function paint(dot, status, note) {
			dot.textContent = WORD[status] || '?';
			dot.style.color = COLORS[status] || '#888';
			dot.style.fontWeight = '700';
			if (note) { dot.title = note; }
		}

		btn.addEventListener('click', function () {
			btn.disabled = true;
			var total = dots.length, done = 0;

			function step(i) {
				if (i >= dots.length) {
					btn.disabled = false;
					btn.textContent = t('done', 'Revisión terminada');
					return;
				}
				var dot = dots[i];
				var yt  = (dot.getAttribute('data-yt') || '').trim();
				btn.textContent = t('checking', 'Revisando') + ' ' + (i + 1) + '/' + total + '…';
				if (!yt) { paint(dot, 'warn', t('noid', 'Sin ID')); return next(i); }

				fetch(BASE + '&yt=' + encodeURIComponent(yt), { credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.then(function (d) { paint(dot, (d && d.status) || 'warn', d && d.note); })
					.catch(function () { paint(dot, 'warn', t('neterr', 'Sin respuesta')); })
					.then(function () { next(i); });
			}
			function next(i) { done++; setTimeout(function () { step(i + 1); }, 130); }
			step(0);
		});
	}

	// ---- 2) FICHA: tester real de reproducción (IFrame API) ---------------
	var apiLoading = false, apiReady = false, apiQueue = [];
	function loadApi(cb) {
		if (apiReady) { return cb(); }
		apiQueue.push(cb);
		if (apiLoading) { return; }
		apiLoading = true;
		var prev = window.onYouTubeIframeAPIReady;
		window.onYouTubeIframeAPIReady = function () {
			if (typeof prev === 'function') { try { prev(); } catch (e) {} }
			apiReady = true;
			apiQueue.forEach(function (fn) { try { fn(); } catch (e) {} });
			apiQueue = [];
		};
		var s = document.createElement('script');
		s.src = 'https://www.youtube.com/iframe_api';
		document.head.appendChild(s);
	}

	function initEdit() {
		var input = document.getElementById('jform_youtube_id');
		if (!input) { return; }

		// Inyecta botón + zona de resultado justo bajo el campo del id.
		var host = input.closest('.control-group') || input.parentNode;
		if (!host) { return; }

		var bar = document.createElement('div');
		bar.className = 'mt-2';
		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'btn btn-sm btn-outline-warning';
		btn.id = 'hmbYtTest';
		btn.textContent = t('test', '▶ Probar reproducción (embed real)');
		var res = document.createElement('span');
		res.id = 'hmbYtTestResult';
		res.className = 'ms-2';
		bar.appendChild(btn);
		bar.appendChild(res);
		host.appendChild(bar);

		var stage = document.createElement('div');
		// Fuera de vista pero presente y "visible" para el navegador, para que el
		// reproductor cargue de verdad (display:none lo impediría).
		stage.style.cssText = 'position:absolute;left:-9999px;top:0;width:320px;height:180px;opacity:0';
		document.body.appendChild(stage);
		var mount = document.createElement('div');
		stage.appendChild(mount);

		var player = null, settled = false, watchdog = null;

		function show(kind, msg) {
			if (settled) { return; }
			settled = true;
			if (watchdog) { clearTimeout(watchdog); watchdog = null; }
			var color = kind === 'ok' ? '#1f7a33' : (kind === 'bad' ? '#b21f2d' : '#9a6b00');
			res.style.color = color;
			res.style.fontWeight = '700';
			res.textContent = msg;
			try { if (player && player.stopVideo) { player.stopVideo(); } } catch (e) {}
		}

		btn.addEventListener('click', function () {
			var id = extractId(input.value);
			settled = false;
			res.style.color = '#888';
			res.style.fontWeight = '400';
			res.textContent = t('testing', 'Comprobando reproducción…');
			if (!id) { return show('bad', t('badid', '✗ ID no válido')); }

			var startEl = document.getElementById('jform_start_time');
			var start = startEl ? (parseInt(startEl.value, 10) || 0) : 0;

			loadApi(function () {
				mount.innerHTML = '';
				var div = document.createElement('div');
				mount.appendChild(div);
				try {
					player = new YT.Player(div, {
						width: 320, height: 180, videoId: id,
						playerVars: { start: start, autoplay: 1, controls: 0, mute: 1 },
						events: {
							onReady: function (e) { try { e.target.playVideo(); } catch (err) {} },
							onError: function (e) {
								var c = e && e.data;
								if (c === 101 || c === 150) { return show('bad', t('blocked', '✗ No se reproduce embebido (restricción de edad o embedding desactivado). No sirve para el quiz.')); }
								if (c === 100) { return show('bad', t('gone', '✗ Vídeo no encontrado o privado')); }
								if (c === 2)   { return show('bad', t('badid', '✗ ID no válido')); }
								return show('bad', t('err', '✗ No se puede reproducir'));
							},
							onStateChange: function (e) {
								// 1 PLAYING, 3 BUFFERING → se reproduce de verdad.
								if (e.data === 1 || e.data === 3) { show('ok', t('ok', '✓ Se reproduce (embebible)')); }
							}
						}
					});
				} catch (err) {
					return show('warn', t('apifail', '⚠ No se pudo iniciar el reproductor'));
				}
				// Si en 6 s no ha sonado ni dado error, lo damos por dudoso.
				watchdog = setTimeout(function () {
					show('warn', t('slow', '⚠ Sin confirmación (vuelve a probar)'));
				}, 6000);
			});
		});
	}

	function boot() { initList(); initEdit(); }
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
