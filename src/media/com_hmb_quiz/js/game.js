/**
 * HMB Quiz - guess the heavy metal song/band from a short audio clip.
 */
(function () {
	'use strict';

	var root = document.getElementById('hmbQuiz');
	if (!root) { return; }

	// Guard against double initialisation (e.g. the script running twice on a page).
	if (root.dataset.hmbqReady === '1') { return; }
	root.dataset.hmbqReady = '1';

	var cfg = {};
	try { cfg = JSON.parse(root.getAttribute('data-config') || '{}'); } catch (e) { cfg = {}; }

	// ---- Element refs -------------------------------------------------------
	var screens   = root.querySelectorAll('[data-screen]');
	var elProgress= root.querySelector('[data-progress]');
	var elScore   = root.querySelector('[data-score]');
	var elTimer   = root.querySelector('[data-timer]');
	var elStage   = root.querySelector('.hmbq-stage');
	var elQuestion= root.querySelector('[data-question]');
	var elOptions = root.querySelector('[data-options]');
	var elLifeline = root.querySelector('[data-action="lifeline"]');
	var elLlCount  = root.querySelector('[data-ll-count]');
	var elCover   = root.querySelector('[data-cover]');
	var elCoverCanvas = root.querySelector('[data-cover-canvas]');
	var elCoverImg = root.querySelector('[data-cover-img]');
	var elPlayerWrap  = root.querySelector('[data-player-wrap]');
	var elReplay  = root.querySelector('[data-action="replay"]');
	var elError   = root.querySelector('[data-error]');
	var elFinal   = root.querySelector('[data-final-score]');
	var elDetail  = root.querySelector('[data-final-detail]');
	var elName    = document.getElementById('hmbqName');
	var elRankMsg = root.querySelector('[data-rank-msg]');
	var elSaveBox = root.querySelector('[data-save]');
	var elGhost   = root.querySelector('[data-ghost]');
	var elGhostMsg= root.querySelector('[data-ghost-msg]');
	var elBoardLst= root.querySelector('[data-board-list]');
	var elEndBoard= root.querySelector('[data-end-board-list]');
	var gameRounds = 0;   // rounds of the current/last game (for the default board tab)
	var boardRounds = 0;  // round bucket currently shown in the leaderboard

	// ---- State --------------------------------------------------------------
	var player      = null;
	var playerReady = false;
	var ytReady     = false;
	var current     = null;   // current round data
	var clipTimer   = null;
	var advanceTimer= null;   // the post-answer "next round" timer
	var countdownInt= null;   // visible answer countdown
	var locked      = false;  // prevent double answers
	var preparing   = false;  // waiting for the clip to actually start playing
	var lifelines   = 0;      // 50:50 uses left this game
	var fiftyUsed   = false;  // 50:50 already used this round
	var started     = false;  // prevent two parallel games
	var seq         = 0;      // round sequence guard against stale async
	var currentBegin = null;  // force-start the current round (used on video error)
	var coverImg    = null;   // album cover image element
	var coverRAF    = null;   // pixelation animation frame
	var coverStartTs= 0;
	var coverWindow = 0;      // ms over which the cover de-pixelates
	var coverOff    = document.createElement('canvas');   // offscreen for pixelation

	var myRank      = 0;      // latest known leaderboard position (for the share card)

	// ---- Duel (Versus) state ----
	var duelMode    = false;
	var duelMatchId = 0;
	var duelOpp     = '';     // opponent display name
	var duelStateInt = null;  // live opponent-score polling
	var duelOppScore = 0;
	var duelOppDone  = false;
	var duelStartElo = 1000;
	var duelEnded    = false;   // result already shown
	var duelOppLeft  = false;   // opponent abandoned
	var primedRound  = null;    // ronda 1 del duelo precargada durante la cuenta atrás
	var roundStartTs= 0;      // when the current round became answerable

	// ---- Networking ---------------------------------------------------------
	function call(task, data) {
		var body = new URLSearchParams();
		body.append('task', 'game.' + task);
		if (cfg.token) { body.append(cfg.token, '1'); }
		if (data) {
			Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
		}
		return fetch(cfg.base + '&task=game.' + task, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString(),
			credentials: 'same-origin'
		}).then(function (r) { return r.json(); });
	}

	function showError(msg) {
		if (!elError) { return; }
		elError.textContent = msg;
		elError.hidden = false;
	}
	function clearError() { if (elError) { elError.hidden = true; } }

	function showScreen(name) {
		screens.forEach(function (s) {
			s.classList.toggle('is-active', s.getAttribute('data-screen') === name);
		});
	}

	// ---- YouTube player -----------------------------------------------------
	window.onYouTubeIframeAPIReady = (function (prev) {
		return function () {
			if (typeof prev === 'function') { try { prev(); } catch (e) {} }
			ytReady = true;
			// Do NOT build the player here: the play screen is still hidden on
			// page load, and YouTube won't initialise inside a hidden element.
			// The player is built lazily in ensurePlayer(), after Start is pressed.
		};
	})(window.onYouTubeIframeAPIReady);

	// If the API was already loaded before this script ran.
	if (window.YT && window.YT.Player) { ytReady = true; }

	function buildPlayer() {
		if (player || !(window.YT && window.YT.Player)) { return; }
		player = new YT.Player('hmbqPlayer', {
			height: '100%',
			width: '100%',
			playerVars: {
				controls: 0, disablekb: 1, modestbranding: 1,
				rel: 0, fs: 0, iv_load_policy: 3, playsinline: 1
			},
			events: {
				onReady: function () { playerReady = true; },
				onError: function () { onPlayerError(); }
			}
		});
	}

	// A video refused to play (embedding disabled, removed, age/region restricted).
	function onPlayerError() {
		try { player.pauseVideo(); } catch (e) {}

		if (locked) {
			// Error while revealing the video after a correct answer:
			// show the album cover full-size instead of YouTube's error screen.
			if (elPlayerWrap) { elPlayerWrap.classList.add('hmbq-hidevideo'); }
			if (current && current.cover && elCoverImg) {
				elCoverImg.classList.remove('is-corner');
				elCoverImg.style.transition = 'none';
				elCoverImg.style.filter = 'none';
				elCoverImg.hidden = false;
			}
			return;
		}

		if (preparing && current) {
			if (current.cover) {
				// Hay portada: la ronda corre sobre ella (se va nitidando), así el
				// jugador puede adivinar igual aunque el vídeo no esté disponible.
				showError(t('videoErr'));
				setTimeout(clearError, 2000);
				if (currentBegin) { currentBegin(); }
			} else if (!duelMode) {
				// Sin audio NI portada = ronda imposible (p. ej. vídeo con embedding
				// bloqueado). En solitario saltamos a otra canción en vez de dar error.
				nextRound();
			} else if (currentBegin) {
				// En duelo las rondas son fijas y no se pueden re-rolear: corre igual.
				currentBegin();
			}
		}
	}

	function ensurePlayer() {
		return new Promise(function (resolve) {
			var tries = 0;
			(function wait() {
				if (playerReady) { return resolve(true); }
				if (!player && window.YT && window.YT.Player) { buildPlayer(); }
				if (tries++ > 150) { return resolve(false); } // ~15s
				setTimeout(wait, 100);
			})();
		});
	}

	// ---- Album cover pixelation --------------------------------------------
	function drawImageCover(ctx, img, dw, dh) {
		var iw = img.naturalWidth || img.width, ih = img.naturalHeight || img.height;
		if (!iw || !ih) { return; }
		var scale = Math.max(dw / iw, dh / ih);
		var sw = dw / scale, sh = dh / scale;
		var sx = (iw - sw) / 2, sy = (ih - sh) / 2;
		ctx.drawImage(img, sx, sy, sw, sh, 0, 0, dw, dh);
	}

	// progress 0 = fully pixelated, 1 = sharp
	function paintCover(progress) {
		if (!elCoverCanvas || !coverImg) { return; }
		var W = elCoverCanvas.width, H = elCoverCanvas.height;
		if (!W || !H) { return; }
		var ctx = elCoverCanvas.getContext('2d');
		progress = Math.max(0, Math.min(1, progress));
		var eased = Math.pow(progress, 3.5);           // stay blocky most of the time, sharpen at the end
		var minBlocks = 6;                             // 6x6 mosaic at the start
		var blocks = Math.round(minBlocks + (Math.min(W, H) - minBlocks) * eased);
		blocks = Math.max(minBlocks, blocks);

		coverOff.width = blocks;
		coverOff.height = blocks;
		var octx = coverOff.getContext('2d');
		octx.imageSmoothingEnabled = true;
		drawImageCover(octx, coverImg, blocks, blocks);

		ctx.imageSmoothingEnabled = false;
		ctx.clearRect(0, 0, W, H);
		ctx.drawImage(coverOff, 0, 0, blocks, blocks, 0, 0, W, H);
	}

	function coverLoop() {
		if (!coverImg) { return; }
		var p = coverWindow > 0 ? (Date.now() - coverStartTs) / coverWindow : 1;
		paintCover(p);
		if (p < 1 && !locked) {
			coverRAF = requestAnimationFrame(coverLoop);
		} else {
			coverRAF = null;
		}
	}

	function startCover(url) {
		stopCover();
		if (!elCoverImg || !url) { return; }
		var img = elCoverImg;
		img.onload = function () {
			img.hidden = false;
			if (elCover) { elCover.classList.add('is-hidden'); }   // hide the equalizer overlay
			img.classList.remove('is-corner');

			var win = (parseInt(cfg.answer, 10) || 0) * 1000;
			// Start heavily blurred, then sharpen over the answer window.
			img.style.transition = 'none';
			img.style.filter = 'blur(22px)';
			void img.offsetWidth;   // force reflow so the next change animates
			if (win > 0) {
				img.style.transition = 'filter ' + win + 'ms linear';
				img.style.filter = 'blur(0px)';
			} else {
				img.style.transition = 'filter .4s ease';
				img.style.filter = 'blur(10px)';   // no timer: keep it a guess
			}
		};
		img.onerror = function () { stopCover(); };
		img.src = url;
	}

	function revealCover() {
		if (!elCoverImg || elCoverImg.hidden) { return; }
		elCoverImg.style.transition = 'filter .3s ease';
		elCoverImg.style.filter = 'blur(0px)';
	}

	function coverToCorner() {
		if (!elCoverImg || elCoverImg.hidden) { return; }
		elCoverImg.style.transition = 'none';
		elCoverImg.style.filter = 'none';
		elCoverImg.classList.add('is-corner');
	}

	function stopCover() {
		if (coverRAF) { cancelAnimationFrame(coverRAF); coverRAF = null; }
		coverImg = null;
		if (elCoverCanvas) { elCoverCanvas.hidden = true; }
		if (elCoverImg) {
			elCoverImg.hidden = true;
			elCoverImg.classList.remove('is-corner');
			elCoverImg.removeAttribute('src');
			elCoverImg.style.filter = '';
			elCoverImg.style.transition = '';
		}
	}

	// Subtle UI tint by difficulty: 1 green, 2 amber, 3 red.
	function setDifficulty(n) {
		root.classList.remove('hmbq-diff-1', 'hmbq-diff-2', 'hmbq-diff-3');
		n = parseInt(n, 10);
		if (n >= 1 && n <= 3) { root.classList.add('hmbq-diff-' + n); }
	}

	function updateLifelineBtn() {
		if (!elLifeline) { return; }
		if (lifelines > 0) {
			elLifeline.hidden = false;
			elLifeline.disabled = fiftyUsed;
			if (elLlCount) { elLlCount.textContent = '(' + lifelines + ')'; }
		} else {
			elLifeline.hidden = true;
		}
	}

	function useLifeline() {
		if (!current || locked || preparing || fiftyUsed || lifelines <= 0) { return; }
		var rm = (current.fifty || []).map(function (x) { return String(x); });
		if (!rm.length) { return; }
		fiftyUsed = true;
		lifelines -= 1;
		elOptions.querySelectorAll('.hmbq-option').forEach(function (b) {
			if (rm.indexOf(String(b.getAttribute('data-opt'))) !== -1) {
				b.classList.add('hmbq-removed');
				b.style.visibility = 'hidden';
				b.disabled = true;
			}
		});
		updateLifelineBtn();
	}

	// Botón ▶ superpuesto: aparece SOLO si el navegador bloquea el autoplay del
	// clip (típico en la primera canción). Su toque lanza playVideo() SÍNCRONO
	// dentro del gesto del usuario, que es lo único que el navegador acepta
	// siempre con audio.
	function showTapPlay() {
		if (!elStage) { return; }
		var tp = root.querySelector('[data-tapplay]');
		if (!tp) {
			tp = document.createElement('div');
			tp.setAttribute('data-tapplay', '');
			tp.className = 'hmbq-tapplay';
			tp.innerHTML = '<button type="button" class="hmbq-tapplay-btn" aria-label="Reproducir">\u25B6</button><span class="hmbq-tapplay-hint">Toca para o\u00edr la canci\u00f3n</span>';
			tp.querySelector('button').addEventListener('click', function () {
				// SÍNCRONO dentro del click: garantiza el audio.
				try {
					player.unMute();
					if (current) { player.seekTo(current.start, true); }
					player.playVideo();
				} catch (e) {}
				hideTapPlay();
			});
			elStage.appendChild(tp);
		}
		tp.hidden = false;
	}
	function hideTapPlay() {
		var tp = root.querySelector('[data-tapplay]');
		if (tp) { tp.hidden = true; }
	}

	function playClip(onStarted) {
		if (!playerReady || !current) { if (onStarted) { onStarted(); } return; }
		if (clipTimer) { clearTimeout(clipTimer); clipTimer = null; }

		var mySeq    = seq;
		var begun    = false;
		var tapShown = false;
		var clipLen  = Math.max(1, (current.clip || cfg.clip || 5)) * 1000;

		function begin() {
			if (begun || mySeq !== seq) { return; }
			begun = true;
			currentBegin = null;
			hideTapPlay();
			if (clipTimer) { clearTimeout(clipTimer); }
			clipTimer = setTimeout(function () { try { player.pauseVideo(); } catch (e) {} }, clipLen);
			if (onStarted) { onStarted(); }
		}
		currentBegin = begin;

		hideTapPlay();
		try {
			player.unMute();
			player.loadVideoById({ videoId: current.youtubeId, startSeconds: current.start });
			player.playVideo();
		} catch (e) {}

		// Wait until the clip is actually playing (buffered) before the countdown starts,
		// so loading time never eats into the player's answer window.
		var waited = 0;
		(function poll() {
			if (begun || mySeq !== seq) { hideTapPlay(); return; }
			var st = -99;
			try { st = player.getPlayerState(); } catch (e) {}
			if (st === 1) { return begin(); }            // 1 = YT.PlayerState.PLAYING
			waited += 120;
			// Ni reproduciendo NI cargando (3 = buffering) tras ~1,6s => autoplay
			// bloqueado: mostramos el botón ▶ y esperamos el toque del jugador.
			if (!tapShown && waited >= 1600 && st !== 3) {
				tapShown = true;
				showTapPlay();
			}
			// Con el botón visible damos mucho más margen; sin él, el fallback
			// corto de siempre para no colgar la ronda.
			if (waited >= (tapShown ? 20000 : 4500)) { return begin(); }
			setTimeout(poll, 120);
		})();
	}

	// ---- Game flow ----------------------------------------------------------
	function stopCountdown() {
		if (countdownInt) { clearInterval(countdownInt); countdownInt = null; }
	}

	function startCountdown() {
		stopCountdown();
		var limit = parseInt(cfg.answer, 10) || 0;
		if (limit <= 0) { if (elTimer) { elTimer.textContent = ''; } return; }
		var left = limit;
		if (elTimer) { elTimer.textContent = left + 's'; elTimer.classList.remove('hmbq-timer-low'); }
		countdownInt = setInterval(function () {
			left--;
			if (elTimer) {
				elTimer.textContent = (left > 0 ? left : 0) + 's';
				elTimer.classList.toggle('hmbq-timer-low', left <= 5);
			}
			if (left <= 0) {
				stopCountdown();
				if (!locked && current) { answer('', null); }  // time up = wrong
			}
		}, 1000);
	}

	function clearTimers() {
		if (clipTimer) { clearTimeout(clipTimer); clipTimer = null; }
		if (advanceTimer) { clearTimeout(advanceTimer); advanceTimer = null; }
		stopCountdown();
		stopCover();
	}

	function startGame(level, rounds, lang, genres, content) {
		if (started) { return; }   // a game is already running
		started = true;
		cardShown = false;
		clearError();
		clearTimers();

		if (level === null || level === undefined || level === '') {
			level = (cfg.defaultLevel != null) ? cfg.defaultLevel : 0;
		}
		if (rounds === null || rounds === undefined || rounds === '') {
			rounds = (cfg.rounds != null) ? cfg.rounds : 10;
		}
		if (lang === null || lang === undefined) { lang = ''; }

		var payload = { level: level, rounds: rounds, lang: lang };
		if (genres && genres.length) { payload.genres = JSON.stringify(genres); }
		if (content) { payload.content = content; }

		call('start', payload).then(function (res) {
			if (!res || !res.ok) { started = false; return showError(t('error')); }
			lifelines = (typeof res.lifelines === 'number') ? res.lifelines : 0;
			showScreen('play');
			if (document.activeElement && document.activeElement.blur) { document.activeElement.blur(); }
			var scrollUp = function () { try { root.scrollIntoView({ block: 'start' }); } catch (e) {} };
			scrollUp();
			setTimeout(scrollUp, 500);   // counter the YouTube iframe pulling the view down
			if (elScore) { elScore.textContent = '0'; }
			ensurePlayer().then(function (ok) {
				if (!ok) { showError(t('noplayer')); }
				nextRound();
			});
		}).catch(function () { started = false; showError(t('error')); });
	}

	function nextRound(attempt) {
		attempt = attempt || 0;
		locked = false;
		preparing = false;
		if (elOptions) { elOptions.classList.remove('hmbq-preparing'); }
		if (elPlayerWrap) { elPlayerWrap.classList.add('hmbq-hidevideo'); }   // video stays hidden until a correct answer
		clearTimers();
		seq++;
		var mySeq = seq;
		if (elReplay) { elReplay.hidden = true; }
		stopCover();
		if (elCover) { elCover.classList.remove('is-hidden'); }
		if (elStage) { elStage.classList.add('hmbq-fading'); }   // fade out

		setTimeout(function () {
			if (mySeq !== seq) { return; }
			var req;
			if (duelMode && primedRound) {
				// Primera ronda del duelo: ya venía precargada desde la cuenta atrás.
				req = primedRound;
				primedRound = null;
			} else {
				req = call(duelMode ? 'matchround' : 'round', duelMode ? { match: duelMatchId } : undefined);
			}
			req.then(function (res) {
				if (mySeq !== seq) { return; }              // a newer round superseded this one
				if (res && res.finished) { return endGame(); }
				if (!res || !res.ok) {
					// In solo play a single bad song can break the round; retry with
					// another (getRound re-rolls a random song each call).
					if (!duelMode && attempt < 3) {
						if (window.console && console.warn) { console.warn('[hmbq] ronda falló, reintento', res); }
						return nextRound(attempt + 1);
					}
					// En duelo, un transitorio (red/IONOS) no debe matar la partida:
					// reintento corto y espaciado. Nunca sobre 'finished' (ya tratado).
					if (duelMode && attempt < 2) {
						return setTimeout(function () { nextRound(attempt + 1); }, 800);
					}
					return showError(mapError(res));
				}
				current = res;
				renderRound(res);
				if (elStage) { elStage.classList.remove('hmbq-fading'); }   // fade in
				// Reanudar el sondeo del rival ya con la ronda nueva pintada (evita
				// que un matchstate concurrente pise el índice recién avanzado).
				if (duelMode && !duelEnded && !duelStateInt) {
					duelStateInt = setInterval(pollDuelState, 2500);
				}
			}).catch(function (err) {
				if (mySeq !== seq) { return; }
				if (!duelMode && attempt < 3) {
					if (window.console && console.warn) { console.warn('[hmbq] ronda error de red, reintento', err); }
					return nextRound(attempt + 1);
				}
				if (duelMode && attempt < 2) {
					return setTimeout(function () { nextRound(attempt + 1); }, 800);
				}
				showError(t('error'));
			});
		}, 320);
	}

	function renderRound(r) {
		clearError();
		if (elProgress) { elProgress.textContent = r.index + ' / ' + r.total; }
		if (r.total) { gameRounds = r.total; }
		if (elQuestion) {
			elQuestion.textContent = questionText(r.type);
		}
		elOptions.innerHTML = '';
		r.options.forEach(function (opt) {
			var b = document.createElement('button');
			b.type = 'button';
			b.className = 'hmbq-option';
			b.textContent = opt;
			b.setAttribute('data-opt', opt);
			b.addEventListener('click', function () { b.blur(); answer(opt, b); });
			elOptions.appendChild(b);
		});

		setDifficulty(r.difficulty);
		fiftyUsed = false;
		updateLifelineBtn();
		if (elReplay) { elReplay.hidden = false; }

		// Prepare state: lock options + show "Preparando…" until the audio really starts.
		preparing = true;
		if (elOptions) { elOptions.classList.add('hmbq-preparing'); }
		if (elTimer)   { elTimer.textContent = t('preparing'); elTimer.classList.remove('hmbq-timer-low'); }

		var thisSeq = seq;
		playClip(function () {
			if (thisSeq !== seq) { return; }
			preparing = false;
			if (elOptions) { elOptions.classList.remove('hmbq-preparing'); }
			startCountdown();
			roundStartTs = Date.now();
			if (r.cover) { startCover(r.cover); }
		});
	}

	function answer(choice, btn) {
		if (locked || preparing || !current) { return; }
		locked = true;
		// Pausa el sondeo del rival durante respuesta+avance: así ningún matchstate
		// concurrente reescribe en sesión el índice de ronda (causaba repetir canción).
		if (duelMode && duelStateInt) { clearInterval(duelStateInt); duelStateInt = null; }
		if (elLifeline) { elLifeline.disabled = true; }
		stopCountdown();
		if (clipTimer) { clearTimeout(clipTimer); clipTimer = null; }

		var ms = roundStartTs ? (Date.now() - roundStartTs) : 0;
		var ansTask = duelMode ? 'matchanswer' : 'answer';
		var ansData = duelMode
			? { match: duelMatchId, idx: current.roundId, choice: choice, ms: ms }
			: { roundId: current.roundId, choice: choice, ms: ms };
		call(ansTask, ansData).then(function (res) {
			if (!res || !res.ok) {
				locked = false;
				clearTimers();
				try { player.pauseVideo(); } catch (e) {}
				// Desincronía puntual (p. ej. ronda ya contestada): en solitario no
				// clavamos al jugador con un error, pasamos a otra ronda.
				if (!duelMode) { return nextRound(); }
				// En duelo, si el servidor dice que estoy en otra ronda (OUT_OF_SYNC),
				// pido MI ronda real y sigo: la BD manda. Antes se quedaba clavado.
				if (res && res.error === 'OUT_OF_SYNC') { return nextRound(); }
				return showError(t('error'));
			}

			if (duelMode && typeof res.oppScore === 'number') {
				duelOppScore = res.oppScore;
				if (res.oppFinished) { duelOppDone = true; }
				updateDuelHud();
			}

			var buttons = elOptions.querySelectorAll('.hmbq-option');
			buttons.forEach(function (b) {
				b.disabled = true;
				if (b.textContent === res.answer) { b.classList.add('is-correct'); }
			});
			if (!res.correct && btn) { btn.classList.add('is-wrong'); }

			if (elScore) { elScore.textContent = res.score; }

			// Reveal rules: the video only appears on a CORRECT answer.
			var showVideo = res.correct && cfg.reveal;

			if (showVideo) {
				if (elPlayerWrap) { elPlayerWrap.classList.remove('hmbq-hidevideo'); }
				if (elCover) { elCover.classList.add('is-hidden'); }
				if (current && current.cover) { coverToCorner(); }   // clear cover in the corner
				try { player.seekTo(current.start, true); player.playVideo(); } catch (e) {}
			} else if (current && current.cover) {
				revealCover();   // wrong/timeout: just unveil the cover, no video
			}

			if (advanceTimer) { clearTimeout(advanceTimer); }
			advanceTimer = setTimeout(function () {
				try { player.pauseVideo(); } catch (e) {}
				if (res.finished) { endGame(); } else { nextRound(); }
			}, 2200);
		}).catch(function () {
			locked = false;
			clearTimers();
			try { player.pauseVideo(); } catch (e) {}
			showError(t('error'));
		});
	}

	function endGame() {
		clearTimers();
		try { player.pauseVideo(); } catch (e) {}
		started = false;
		clearError();
		if (duelMode) { return duelEnd(); }
		var finalScore = elScore ? elScore.textContent : '0';
		if (elFinal) { elFinal.textContent = finalScore; }
		if (elRankMsg) { elRankMsg.hidden = true; }
		showScreen('end');
		setupShare(finalScore);

		if (cfg.loggedIn) {
			// Logged-in players don't get asked for a name; save automatically.
			if (elSaveBox) { elSaveBox.hidden = true; }
			if (elGhost) { elGhost.hidden = true; }
			saveScore(true);
		} else {
			// Guests: no name box. Show their "ghost" position + register CTA.
			if (elSaveBox) { elSaveBox.hidden = true; }
			submitGuest();
		}

		refreshBoard(gameRounds);
	}

	// Marcos según el puesto. sy = puntuación (entre calaveras); ly = etiqueta "Puntos";
	// ny = nombre (justo ENCIMA de la URL de cada marco). Fracciones de 1080, ajustables.
	var CARD_FRAMES = {
		gold:   { file: 'marco1.jpg',      sy: 0.470, ly: 0.528, ny: 0.865, fill: '#ffe9a8', stroke: '#3a2600', glow: true },
		silver: { file: 'marco2.jpg',      sy: 0.470, ly: 0.528, ny: 0.865, fill: '#f4f4f4', stroke: '#23282d', glow: true },
		bronze: { file: 'marco3.jpg',      sy: 0.470, ly: 0.528, ny: 0.865, fill: '#ffd9a0', stroke: '#3a1e00', glow: true },
		consol: { file: 'consolacion.jpg', sy: 0.430, ly: 0.488, ny: 0.850, fill: '#241606', stroke: '#e7d6a6', glow: false },
		vs:     { file: 'marcovs.jpg',     sy: 0.450, ly: 0.508, ny: 0.860, fill: '#fff1c2', stroke: '#3a1400', glow: true }
	};

	function cardFrameKey(rank, isVs) {
		if (isVs) { return 'vs'; }
		if (rank === 1) { return 'gold'; }
		if (rank === 2) { return 'silver'; }
		if (rank === 3) { return 'bronze'; }
		return 'consol';
	}

	// Build a square result card (1080×1080) for Instagram / image sharing.
	// Loads the rank frame (same-origin → canvas not tainted → toBlob works).
	function buildCardBlob(score, rank, isVs) {
		return new Promise(function (resolve) {
			var S = 1080;
			var c = document.createElement('canvas');
			c.width = S; c.height = S;
			var x = c.getContext('2d');
			var fr = CARD_FRAMES[cardFrameKey(rank || 0, !!isVs)];

			function done() { c.toBlob(function (b) { resolve(b); }, 'image/jpeg', 0.92); }

			function overlay() {
				x.textAlign = 'center';
				x.lineJoin = 'round';

				// Puntuación, centrada entre las dos calaveras.
				x.font = '900 170px "Oswald", Impact, sans-serif';
				if (fr.glow) { x.shadowColor = 'rgba(0,0,0,0.85)'; x.shadowBlur = 28; }
				x.lineWidth = 16; x.strokeStyle = fr.stroke;
				x.strokeText(String(score), S / 2, S * fr.sy);
				x.shadowBlur = 0;
				x.fillStyle = fr.fill;
				x.fillText(String(score), S / 2, S * fr.sy);

				// Etiqueta "Puntos" bajo la cifra.
				x.font = '700 56px "Oswald", Arial, sans-serif';
				x.lineWidth = 8; x.strokeStyle = fr.stroke;
				x.strokeText('Puntos', S / 2, S * fr.ly);
				x.fillStyle = fr.fill;
				x.fillText('Puntos', S / 2, S * fr.ly);

				// Nombre de usuario, justo encima de la dirección web.
				if (cfg.userName) {
					x.font = '600 56px "Oswald", Arial, sans-serif';
					x.lineWidth = 8; x.strokeStyle = fr.stroke;
					x.strokeText(cfg.userName, S / 2, S * fr.ny);
					x.fillStyle = fr.fill;
					x.fillText(cfg.userName, S / 2, S * fr.ny);
				}

				done();
			}

			var img = new Image();

			img.onload = function () {
				x.drawImage(img, 0, 0, S, S);
				if (document.fonts && document.fonts.ready) {
					document.fonts.ready.then(overlay, overlay);
				} else { overlay(); }
			};

			img.onerror = function () {
				// Fallback si el marco no carga: tarjeta de texto simple.
				x.fillStyle = '#080808'; x.fillRect(0, 0, S, S);
				x.fillStyle = '#c1121f'; x.fillRect(0, 0, S, 18); x.fillRect(0, S - 18, S, 18);
				x.textAlign = 'center';
				x.fillStyle = '#ffffff'; x.font = '900 230px "Oswald", Arial, sans-serif';
				x.fillText(String(score), S / 2, S / 2 + 60);
				x.fillStyle = '#ffcf3f'; x.font = '700 42px "Oswald", Arial, sans-serif';
				x.fillText('heavymetalbrothers.com', S / 2, 985);
				done();
			};

			img.src = (cfg.imgBase || '') + fr.file;
		});
	}

	var cardShown = false;

	// Muestra la MISMA tarjeta que va a Instagram como emergente en la web.
	function showCardPopup(score, rank, isVs) {
		if (cardShown) { return; }
		cardShown = true;
		buildCardBlob(score, rank, isVs).then(function (blob) {
			if (!blob) { return; }
			var url = URL.createObjectURL(blob);
			var ov = document.createElement('div');
			ov.className = 'hmbq-cardpop';
			ov.style.cssText = 'position:fixed;inset:0;z-index:99999;display:flex;align-items:center;' +
				'justify-content:center;background:rgba(0,0,0,.82);padding:18px;';
			var box = document.createElement('div');
			box.style.cssText = 'position:relative;max-width:min(92vw,520px);width:100%;';
			var im = document.createElement('img');
			im.src = url; im.alt = 'Metal Quiz';
			im.style.cssText = 'width:100%;height:auto;border-radius:10px;display:block;box-shadow:0 10px 40px rgba(0,0,0,.6);';
			var xb = document.createElement('button');
			xb.textContent = '\u00D7'; xb.setAttribute('aria-label', 'Cerrar');
			xb.style.cssText = 'position:absolute;top:-14px;right:-14px;width:40px;height:40px;border-radius:50%;' +
				'border:0;background:#c1121f;color:#fff;font-size:24px;line-height:40px;cursor:pointer;';
			var sb = document.createElement('button');
			sb.textContent = t('shareLabel');
			sb.style.cssText = 'display:block;width:100%;margin-top:12px;padding:14px;border:0;border-radius:8px;' +
				'background:#c1121f;color:#fff;font:600 18px Oswald,Arial,sans-serif;cursor:pointer;';
			function close() {
				if (ov.parentNode) { ov.parentNode.removeChild(ov); }
				setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
			}
			xb.onclick = close;
			ov.addEventListener('click', function (e) { if (e.target === ov) { close(); } });
			sb.onclick = function () { shareImage(score, isVs); };
			box.appendChild(im); box.appendChild(xb); box.appendChild(sb);
			ov.appendChild(box);
			root.appendChild(ov);
		});
	}

	function shareImage(score, isVs) {
		buildCardBlob(score, myRank, !!isVs).then(function (blob) {
			if (!blob) { return; }
			var url  = cfg.shareUrl || window.location.href.split('#')[0];
			var text = t('shareText').replace('%s', score);
			var file = null;
			try { file = new File([blob], 'metal-quiz.jpg', { type: 'image/jpeg' }); } catch (e) {}

			if (file && navigator.canShare && navigator.canShare({ files: [file] })) {
				navigator.share({ files: [file], text: text, url: url }).catch(function () {});
			} else {
				// Desktop / unsupported: download so they can post it manually.
				var a = document.createElement('a');
				a.href = URL.createObjectURL(blob);
				a.download = 'metal-quiz.jpg';
				document.body.appendChild(a); a.click(); document.body.removeChild(a);
				setTimeout(function () { URL.revokeObjectURL(a.href); }, 4000);
				claimToast(t('imgSaved'));
			}
		});
	}

	function setupShare(score) {
		var box = root.querySelector('[data-share]');
		if (!box) { return; }
		var url  = cfg.shareUrl || window.location.href.split('#')[0];
		var text = t('shareText').replace('%s', score);
		var full = text + ' ' + url;

		var nat = box.querySelector('[data-share-do="native"]');
		if (nat) {
			if (navigator.share) {
				nat.hidden = false;
				nat.onclick = function () {
					navigator.share({ title: 'Metal Quiz', text: text, url: url }).catch(function () {});
				};
			} else {
				nat.hidden = true;
			}
		}
		var wa = box.querySelector('[data-share-do="whatsapp"]');
		if (wa) { wa.href = 'https://wa.me/?text=' + encodeURIComponent(full); }
		var tg = box.querySelector('[data-share-do="telegram"]');
		if (tg) { tg.href = 'https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(text); }
		var x = box.querySelector('[data-share-do="x"]');
		if (x) { x.href = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(url); }
		var ig = box.querySelector('[data-share-do="image"]');
		if (ig) { ig.onclick = function () { shareImage(score); }; }

		var copy = box.querySelector('[data-share-do="copy"]');
		if (copy) {
			copy.textContent = t('shareCopy');
			copy.onclick = function () {
				var done = function () {
					copy.textContent = t('shareCopied');
					setTimeout(function () { copy.textContent = t('shareCopy'); }, 2000);
				};
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(full).then(done).catch(done);
				} else {
					var ta = document.createElement('textarea');
					ta.value = full; ta.style.position = 'fixed'; ta.style.opacity = '0';
					document.body.appendChild(ta); ta.focus(); ta.select();
					try { document.execCommand('copy'); } catch (e) {}
					document.body.removeChild(ta); done();
				}
			};
		}
	}

	function submitGuest() {
		call('score', { name: '' }).then(function (res) {
			if (!res || !res.ok) { return; }
			if (res.claim) {
				try { localStorage.setItem('hmbq_claim', res.claim); } catch (e) {}
			}
			if (elGhost) { elGhost.hidden = false; }
			if (elGhostMsg) {
				myRank = (typeof res.rank === 'number') ? res.rank : myRank;
				elGhostMsg.textContent = t('ghost').replace('%d', res.rank);
			}
			// Nota: al invitado NO le mostramos la tarjeta-premio (saldría sin nombre y
			// antes de registrarse). Sale al guardar (logueado) o al reclamar tras registro.
		}).catch(function () {});
	}

	function tryClaim() {
		var tok = null;
		try { tok = localStorage.getItem('hmbq_claim'); } catch (e) {}
		if (!tok) { return; }
		call('claim', { token: tok }).then(function (res) {
			try { localStorage.removeItem('hmbq_claim'); } catch (e) {}
			if (res && res.ok) {
				// Reconstruir la pantalla final: puntuación reclamada + puesto + compartir.
				myRank = (typeof res.rank === 'number') ? res.rank : myRank;
				var sc = (typeof res.score === 'number') ? String(res.score) : '0';

				if (elScore) { elScore.textContent = sc; }
				if (elFinal) { elFinal.textContent = sc; }
				if (elSaveBox) { elSaveBox.hidden = true; }
				if (elGhost) { elGhost.hidden = true; }
				if (elRankMsg) {
					elRankMsg.hidden = false;
					elRankMsg.textContent = t('rank').replace('%d', res.rank);
				}

				setupShare(sc);
				showScreen('end');
				claimToast(t('claimed').replace('%d', res.rank));
				refreshBoard(res.rounds || gameRounds);
				showCardPopup(res.score, myRank, false);
			}
		}).catch(function () {
			try { localStorage.removeItem('hmbq_claim'); } catch (e) {}
		});
	}

	function claimToast(msg) {
		var d = document.createElement('div');
		d.className = 'hmbq-claim-toast';
		d.textContent = msg;
		root.appendChild(d);
		setTimeout(function () { d.classList.add('on'); }, 30);
		setTimeout(function () { d.classList.remove('on'); }, 6000);
		setTimeout(function () { if (d.parentNode) { d.parentNode.removeChild(d); } }, 6600);
	}

	function saveScore(auto) {
		var name = (elName && elName.value) ? elName.value : '';
		call('score', { name: name }).then(function (res) {
			if (!res || !res.ok) {
				if (res && res.error === 'ALREADY_SAVED') { return; }
				return showError(t('error'));
			}
			if (elSaveBox) { elSaveBox.hidden = true; }
			if (elRankMsg) {
				elRankMsg.hidden = false;
				myRank = (typeof res.rank === 'number') ? res.rank : myRank;
				var msg = t('rank').replace('%d', res.rank);
				if (auto && res.name) {
					msg = t('savedAs').replace('%s', res.name) + ' ' + msg;
				}
				elRankMsg.textContent = msg;
			}
			refreshBoard(gameRounds);
			showCardPopup(res.score, myRank, false);
		}).catch(function () { showError(t('error')); });
	}

	function paintBoard(listEl, board) {
		if (!listEl) { return; }
		listEl.innerHTML = '';
		if (!board || !board.length) {
			var empty = document.createElement('li');
			empty.className = 'hmbq-empty';
			empty.textContent = t('boardEmpty');
			listEl.appendChild(empty);
			return;
		}
		board.forEach(function (row, i) {
			var rank = i + 1;
			var medal = rank === 1 ? '\uD83E\uDD47' : (rank === 2 ? '\uD83E\uDD48' : (rank === 3 ? '\uD83E\uDD49' : ''));
			var li = document.createElement('li');
			li.className = 'hmbq-row' + (medal ? ' hmbq-top' + rank : '');
			li.innerHTML =
				'<span class="hmbq-rank">' + (medal || rank) + '</span>' +
				'<span class="hmbq-name"></span>' +
				'<span class="hmbq-hits">' + parseInt(row.correct || 0, 10) + '</span>' +
				'<span class="hmbq-pts">' + parseInt(row.score, 10) + '</span>' +
				'<span class="hmbq-total">' + parseInt(row.total || 0, 10) + '</span>';
			li.querySelector('.hmbq-name').textContent = row.player_name;
			listEl.appendChild(li);
		});
	}

	function refreshBoard(rounds) {
		var want = rounds || boardRounds || gameRounds || cfg.defaultRounds
			|| (cfg.boardBuckets && cfg.boardBuckets[0]) || 10;
		call('board', { limit: 20, rounds: want }).then(function (res) {
			if (!res || !res.ok) { return; }
			boardRounds = res.rounds || want;
			var buckets = (res.buckets && res.buckets.length) ? res.buckets.slice() : [];
			if (buckets.indexOf(boardRounds) === -1) { buckets.push(boardRounds); }
			buckets.sort(function (a, b) { return a - b; });
			renderBoardTabs(buckets, boardRounds);
			paintBoard(elBoardLst, res.board);
			paintBoard(elEndBoard, res.board);
		});
	}

	function renderBoardTabs(buckets, active) {
		var bars = root.querySelectorAll('[data-board-tabs]');
		Array.prototype.forEach.call(bars, function (bar) {
			bar.innerHTML = '';
			if (buckets.length < 2) { return; }   // only one category: no tabs needed
			buckets.forEach(function (b) {
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'hmbq-board-tab' + (b === active ? ' is-active' : '');
				btn.textContent = b + ' ' + t('roundsTab');
				btn.onclick = function () { refreshBoard(b); };
				bar.appendChild(btn);
			});
		});
	}

	// ---- i18n helpers (strings injected via data attributes) ----------------
	var STR = {
		error:      'Algo ha fallado. Inténtalo de nuevo.',
		videoErr:   'Vídeo no disponible. Pasamos a la siguiente.',
		noplayer:   'No se pudo cargar el reproductor de YouTube.',
		qTitle:     '¿Qué canción es?',
		qBand:      '¿De qué grupo es?',
		qYear:      '¿De qué año es?',
		qGenre:     '¿De qué género es?',
		qAlbum:     '¿De qué álbum es?',
		qOrigin:    '¿De dónde es la banda?',
		qSinger:    '¿Quién es el cantante?',
		qMovie:     '¿De qué película es?',
		qMovieYear: '¿De qué año es la película?',
		qMovieOriginal: '¿Cuál es el título original de la película?',
		qDirector:  '¿Quién dirigió la película?',
		rank:       '¡Puesto #%d en el ranking!',
		savedAs:    'Guardado como %s.',
		boardEmpty: 'Aún no hay puntuaciones.',
		roundsTab:  'preg.',
		ghost:      'Serías el puesto #%d del foso… pero los fantasmas no puntúan. 🤘',
		claimed:    '🤘 ¡Puntuación reclamada! Estás en el puesto #%d del foso.',
		shareText:  '🤘 He sacado %s puntos en el Metal Quiz de HeavyMetalBrothers. ¿Puedes superarme?',
		shareCopy:  'Copiar enlace',
		shareCopied:'¡Copiado! 🤘',
		shareLabel: '📸 Compartir / Guardar imagen',
		imgSaved:   '📸 Imagen guardada — súbela a tu historia de Instagram 🤘',
		noSongs:    'No hay canciones disponibles para este nivel.',
		notEnough:  'No hay suficientes canciones para crear preguntas.',
		preparing:  'Preparando…'
	};
	function t(k) { return STR[k] || ''; }
	function questionText(type) {
		switch (type) {
			case 'band':  return t('qBand');
			case 'year':  return t('qYear');
			case 'genre': return t('qGenre');
			case 'album': return t('qAlbum');
			case 'origin': return t('qOrigin');
			case 'singer': return t('qSinger');
			case 'movie':  return t('qMovie');
			case 'movie_year': return t('qMovieYear');
			case 'movie_original': return t('qMovieOriginal');
			case 'director': return t('qDirector');
			default:      return t('qTitle');
		}
	}
	function mapError(res) {
		if (!res) { return t('error'); }
		switch (res.error) {
			case 'NO_SONGS':   return t('noSongs');
			case 'NOT_ENOUGH': return t('notEnough');
			default:           return t('error');
		}
	}

	// ---- Bindings -----------------------------------------------------------
	function quitGame() {
		// Forfeit an in-progress duel so the opponent isn't left hanging.
		if (duelMode && duelMatchId && !duelEnded) {
			try { sendAbandon(); } catch (e) {}
		}
		seq++;                       // invalidate any pending clip callbacks
		clearTimers();
		stopCover();
		try { player.pauseVideo(); } catch (e) {}
		preparing = false;
		started = false;
		current = null;
		locked = false;
		duelMode = false; duelMatchId = 0; duelEnded = false; primedRound = null;
		if (elOptions) { elOptions.classList.remove('hmbq-preparing'); }
		if (elGhost) { elGhost.hidden = true; }
		showScreen('start');
	}

	function selectedValue(group, fallback) {
		var active = root.querySelector('.hmbq-pill.is-active[data-group="' + group + '"]');
		return active ? active.getAttribute('data-value') : fallback;
	}

	function selectedValues(group) {
		var out = [];
		root.querySelectorAll('.hmbq-pill.is-active[data-group="' + group + '"]').forEach(function (p) {
			out.push(p.getAttribute('data-value'));
		});
		return out;
	}

	function setSelDisabled(sel, disabled) {
		root.querySelectorAll('[data-sel="' + sel + '"]').forEach(function (el) {
			el.classList.toggle('hmbq-disabled', disabled);
		});
	}

	// When the player picks one or more genres, lock difficulty & language (rounds stays).
	function updateGenreMode() {
		var on = selectedValues('genre').length > 0;
		setSelDisabled('level', on);
		setSelDisabled('lang', on);
	}

	root.addEventListener('click', function (e) {
		var pill = e.target.closest('.hmbq-pill');
		if (pill && root.contains(pill)) {
			var grp  = pill.getAttribute('data-group');
			var cont = pill.closest('.hmbq-pills');
			if (cont && cont.classList.contains('hmbq-disabled')) { return; }   // locked group
			if (pill.getAttribute('data-multi') === '1') {
				pill.classList.toggle('is-active');   // multi-select (genres)
			} else {
				root.querySelectorAll('.hmbq-pill[data-group="' + grp + '"]').forEach(function (p) {
					p.classList.remove('is-active');
				});
				pill.classList.add('is-active');
			}
			if (grp === 'genre') { updateGenreMode(); }
			return;
		}

		var el = e.target.closest('[data-action]');
		if (!el) { return; }
		switch (el.getAttribute('data-action')) {
			case 'start':
				var lvl  = selectedValue('level', (cfg.defaultLevel != null) ? cfg.defaultLevel : 0);
				var rnds = selectedValue('rounds', (cfg.rounds != null) ? cfg.rounds : 10);
				var lng  = selectedValue('lang', '');
				var gens = [];
				selectedValues('genre').forEach(function (v) {
					(v || '').split('|').forEach(function (x) {
						x = x.trim();
						if (x && gens.indexOf(x) === -1) { gens.push(x); }
					});
				});
				var cont = selectedValue('content', cfg.contentScope || 'both');
				startGame(lvl, rnds, lng, gens, cont);
				break;
			case 'replay': playClip(); break;
			case 'lifeline': useLifeline(); break;
			case 'quit': quitGame(); break;
			case 'fullscreen': toggleFullscreen(); break;
			case 'versus-open':
				if (cfg.loggedIn) { openVersus(); }
				else { var lo = root.querySelector('[data-login]'); if (lo) { lo.hidden = false; } }
				break;
			case 'login-close': {
				var lc = root.querySelector('[data-login]');
				if (lc) { lc.hidden = true; }
				break;
			}
			case 'versus-close': closeVersus(); break;
			case 'versus-ranking': showVersusRanking(); break;
			case 'overlay-close': closeDuelOverlay(); break;
			case 'guide-open': {
				var g = root.querySelector('[data-guide]');
				if (g) { g.hidden = false; }
				break;
			}
			case 'guide-close': {
				var gc = root.querySelector('[data-guide]');
				if (gc) { gc.hidden = true; }
				break;
			}
			case 'versus-challenge': versusChallenge(el.getAttribute('data-id'), el.getAttribute('data-name')); break;
			case 'duel-accept': duelAccept(); break;
			case 'duel-decline': duelDecline(); break;
			case 'duel-close':
				closeDuelOverlay();
				clearTimers(); started = false; current = null;
				showScreen('start');
				break;
			case 'save': saveScore(); break;
			case 'again':
				clearTimers();
				started = false;
				current = null;
				if (elName) { elName.value = ''; }
				showScreen('start');
				refreshBoard();
				break;
		}
	});

	// ---- Fullscreen ---------------------------------------------------------
	var elFs = root.querySelector('[data-action="fullscreen"]');

	function fsSupported() {
		var enabled = (typeof document.fullscreenEnabled !== 'undefined') ? document.fullscreenEnabled
			: (typeof document.webkitFullscreenEnabled !== 'undefined') ? document.webkitFullscreenEnabled
			: false;
		var hasMethod = !!(root.requestFullscreen || root.webkitRequestFullscreen);
		return hasMethod && !!enabled;   // iOS Safari exposes the method but reports it disabled
	}
	function inFullscreen() {
		return !!(document.fullscreenElement || document.webkitFullscreenElement);
	}
	function toggleFullscreen() {
		try {
			if (inFullscreen()) {
				if (document.exitFullscreen) { document.exitFullscreen(); }
				else if (document.webkitExitFullscreen) { document.webkitExitFullscreen(); }
			} else {
				if (root.requestFullscreen) { root.requestFullscreen(); }
				else if (root.webkitRequestFullscreen) { root.webkitRequestFullscreen(); }
			}
		} catch (e) {}
	}
	function syncFs() {
		var on = inFullscreen();
		root.classList.toggle('hmbq-fs-on', on);
		if (elFs) { elFs.innerHTML = on ? '&#10005;' : '&#9974;'; }
	}
	document.addEventListener('fullscreenchange', syncFs);
	document.addEventListener('webkitfullscreenchange', syncFs);
	if (elFs && !fsSupported()) { elFs.hidden = true; }

	// ---- Modo Versus: presencia + lobby ------------------------------------
	var elVersusBtn   = root.querySelector('[data-action="versus-open"]');
	var elVersusPanel = root.querySelector('[data-versus]');
	var elVersusList  = root.querySelector('[data-versus-list]');
	var elVersusEmpty = root.querySelector('[data-versus-empty]');
	var versusEmptyDefault = elVersusEmpty ? elVersusEmpty.textContent : '';
	var lobbyInt = null;
	var heartInt = null;

	function heartbeat() { call('presence').catch(function () {}); }

	function renderLobby(players) {
		if (!elVersusList) { return; }
		players = players || [];
		elVersusList.innerHTML = '';
		if (elVersusEmpty) {
			elVersusEmpty.hidden = players.length > 0;
			elVersusEmpty.textContent = versusEmptyDefault;
		}
		players.forEach(function (p) {
			var li = document.createElement('li');
			li.className = 'hmbq-versus-item';
			var av = p.avatar
				? '<img class="hmbq-versus-av" src="' + p.avatar + '" alt="">'
				: '<span class="hmbq-versus-av hmbq-versus-av-none">\u2022</span>';
			li.innerHTML = av + '<span class="hmbq-versus-name"></span>'
				+ '<button type="button" class="hmbq-versus-btn" data-action="versus-challenge" data-id="' + p.id + '"></button>';
			li.querySelector('.hmbq-versus-name').textContent = p.name;
			var btn = li.querySelector('.hmbq-versus-btn');
			btn.textContent = '\u2694 Retar';
			btn.setAttribute('data-name', p.name);
			elVersusList.appendChild(li);
		});
	}

	function pollLobby() {
		call('lobby').then(function (res) {
			if (res && res.ok) {
				renderLobby(res.players);
			} else {
				lobbyMessage('No se pudo leer la lista (posible cache del servidor). Si no cambia, reinicia PHP en IONOS.');
			}
		}).catch(function () {
			lobbyMessage('No se pudo leer la lista (posible cache del servidor). Si no cambia, reinicia PHP en IONOS.');
		});
	}

	function lobbyMessage(msg) {
		if (elVersusList) { elVersusList.innerHTML = ''; }
		if (elVersusEmpty) { elVersusEmpty.hidden = false; elVersusEmpty.textContent = msg; }
	}

	function openVersus() {
		if (elVersusPanel) { elVersusPanel.hidden = false; }
		ensureRankingBtn();
		pollLobby();
		if (lobbyInt) { clearInterval(lobbyInt); }
		lobbyInt = setInterval(pollLobby, 4000);
	}

	function ensureRankingBtn() {
		if (!elVersusPanel || elVersusPanel.querySelector('[data-action="versus-ranking"]')) { return; }
		var b = document.createElement('button');
		b.type = 'button';
		b.className = 'hmbq-btn hmbq-btn-ghost hmbq-versus-rank-btn';
		b.setAttribute('data-action', 'versus-ranking');
		b.textContent = '\u{1F3C6} Ranking Versus';
		elVersusPanel.insertBefore(b, elVersusPanel.firstChild);
	}

	function showVersusRanking() {
		call('versusboard', { limit: 20 }).then(function (res) {
			var rows = (res && res.ok && res.board) ? res.board : [];
			var html = '<div class="hmbq-duel-card hmbq-rank-card"><p class="hmbq-duel-vs-big">\u{1F3C6} Ranking Versus</p>';
			if (!rows.length) {
				html += '<p>A\u00fan no hay duelos disputados. \u00a1S\u00e9 el primero en pisar el foso!</p>';
			} else {
				html += '<table class="hmbq-vrank"><thead><tr><th>#</th><th>Guerrero</th><th>ELO</th><th>V</th><th>D</th></tr></thead><tbody>';
				var medals = ['\u{1F947}', '\u{1F948}', '\u{1F949}'];
				rows.forEach(function (r, i) {
					var pos = medals[i] || (i + 1);
					html += '<tr><td>' + pos + '</td><td>' + escapeHtml(r.name) + '</td><td><strong>' + r.elo + '</strong></td><td>' + r.wins + '</td><td>' + r.losses + '</td></tr>';
				});
				html += '</tbody></table>';
			}
			html += '<button type="button" class="hmbq-btn" data-action="overlay-close">Cerrar</button></div>';
			duelOverlay(html);
		}).catch(function () {});
	}

	function closeVersus() {
		if (elVersusPanel) { elVersusPanel.hidden = true; }
		if (lobbyInt) { clearInterval(lobbyInt); lobbyInt = null; }
	}

	// ---- Modo Versus: RETO (Fase 1) ----------------------------------------
	var pendingMatchId   = 0;     // challenge I sent, awaiting a response
	var challengePollInt = null;
	var incomingInt      = null;
	var handledChallenge = 0;     // last incoming matchId already prompted

	function versusStatus(msg) {
		if (elVersusEmpty) { elVersusEmpty.hidden = false; elVersusEmpty.textContent = msg; }
	}
	function stopChallengePoll() {
		if (challengePollInt) { clearInterval(challengePollInt); challengePollInt = null; }
	}

	// Challenger sends a duel invite.
	function versusChallenge(id, name) {
		id = parseInt(id, 10) || 0;
		if (!id) { return; }
		var rounds = 10;
		try { rounds = parseInt(selectedValue('rounds', (cfg.rounds != null) ? cfg.rounds : 10), 10) || 10; } catch (e) {}
		versusStatus('\u2694 Retando a ' + (name || '') + '\u2026');
		call('challenge', { opponent: id, rounds: rounds, genres: JSON.stringify([]) }).then(function (res) {
			if (!res || !res.ok) { versusStatus('No se pudo enviar el reto. Int\u00e9ntalo de nuevo.'); return; }
			pendingMatchId = res.matchId;
			versusStatus('Esperando a que ' + (name || res.opponent || '') + ' acepte el duelo\u2026');
			stopChallengePoll();
			challengePollInt = setInterval(function () { pollChallengeStatus(name); }, 2000);
		}).catch(function () { versusStatus('Error de red al enviar el reto.'); });
	}

	// Challenger polls for the opponent's accept/decline.
	function pollChallengeStatus(name) {
		if (!pendingMatchId) { stopChallengePoll(); return; }
		call('challengestatus', { match: pendingMatchId }).then(function (res) {
			if (!res || !res.ok) { return; }
			if (res.status === 'accepted' || res.status === 'active' || res.status === 'finished') {
				stopChallengePoll();
				versusStatus('\u2694 \u00a1' + (name || 'El rival') + ' acept\u00f3! Arrancando el duelo\u2026');
				enterDuel(pendingMatchId, name);
				pendingMatchId = 0;
			} else if (res.status === 'declined') {
				stopChallengePoll(); pendingMatchId = 0;
				versusStatus((name || 'El rival') + ' rechaz\u00f3 el reto.');
			} else if (res.status === 'expired') {
				stopChallengePoll(); pendingMatchId = 0;
				versusStatus('El reto caduc\u00f3.');
			}
		}).catch(function () {});
	}

	// Opponent polls for an incoming challenge and is shown a styled prompt.
	var incomingMatchId = 0;
	var incomingFrom    = '';

	function pollIncoming() {
		if (duelMode || incomingMatchId) { return; }   // busy
		call('incoming').then(function (res) {
			if (!res || !res.ok || !res.challenge) { return; }
			var c = res.challenge;
			if (c.matchId === handledChallenge) { return; }
			handledChallenge = c.matchId;
			incomingMatchId = c.matchId;
			incomingFrom    = c.from || 'Alguien';
			duelOverlay(
				'<div class="hmbq-duel-card">' +
				'<p class="hmbq-duel-vs-big">\u2694 \u00a1Te retan a duelo!</p>' +
				'<p class="hmbq-duel-from"><strong>' + escapeHtml(incomingFrom) + '</strong> te desaf\u00eda a ' + (c.rounds || 10) + ' rondas.</p>' +
				'<div class="hmbq-duel-actions">' +
				'<button type="button" class="hmbq-btn" data-action="duel-accept">Aceptar \u2694</button>' +
				'<button type="button" class="hmbq-btn hmbq-btn-ghost" data-action="duel-decline">Rechazar</button>' +
				'</div></div>'
			);
		}).catch(function () {});
	}

	function duelAccept() {
		var mid = incomingMatchId, from = incomingFrom;
		incomingMatchId = 0;
		closeDuelOverlay();
		call('respond', { match: mid, accept: 1 }).then(function (r) {
			if (r && r.ok) { enterDuel(mid, from); }
		}).catch(function () {});
	}

	function duelDecline() {
		var mid = incomingMatchId;
		incomingMatchId = 0;
		closeDuelOverlay();
		call('respond', { match: mid, accept: 0 }).catch(function () {});
	}

	// ---- Duelo en directo: HUD, overlays, bucle ----
	function escapeHtml(s) {
		return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
		});
	}

	function ensureDuelHud() {
		var hud = root.querySelector('[data-duel-hud]');
		if (!hud) {
			hud = document.createElement('div');
			hud.setAttribute('data-duel-hud', '');
			hud.className = 'hmbq-duel-hud';
			hud.innerHTML = '<span class="hmbq-duel-vs">\u2694</span> <span class="hmbq-duel-opp-name"></span>: <strong class="hmbq-duel-opp-score">0</strong>';
			root.appendChild(hud);
		}
		return hud;
	}
	function updateDuelHud() {
		if (!duelMode) { return; }
		var hud = ensureDuelHud();
		hud.hidden = false;
		var n = hud.querySelector('.hmbq-duel-opp-name');
		var s = hud.querySelector('.hmbq-duel-opp-score');
		if (n) { n.textContent = duelOpp || 'Rival'; }
		if (s) { s.textContent = duelOppScore + (duelOppDone ? ' \u2713' : ''); }
	}
	function hideDuelHud() {
		var hud = root.querySelector('[data-duel-hud]');
		if (hud) { hud.hidden = true; }
	}
	function duelOverlay(html) {
		var ov = root.querySelector('[data-duel-overlay]');
		if (!ov) {
			ov = document.createElement('div');
			ov.setAttribute('data-duel-overlay', '');
			ov.className = 'hmbq-duel-overlay';
			root.appendChild(ov);
		}
		ov.innerHTML = html;
		ov.hidden = false;
		return ov;
	}
	function closeDuelOverlay() {
		var ov = root.querySelector('[data-duel-overlay]');
		if (ov) { ov.hidden = true; ov.innerHTML = ''; }
	}

	function enterDuel(matchId, oppName) {
		matchId = parseInt(matchId, 10) || 0;
		if (!matchId) { return; }
		// Si había una partida en solitario en curso, la abandonamos para entrar al duelo
		// (antes se cancelaba el duelo y el rival jugaba "contra nadie").
		if (started) {
			seq++;                                  // invalida cualquier ronda en vuelo
			clearTimers();
			try { player.pauseVideo(); } catch (e) {}
			started = false; current = null; locked = false; preparing = false;
		}
		closeVersus();
		call('matchstart', { match: matchId }).then(function (res) {
			if (!res || !res.ok) {
				duelOverlay('<div class="hmbq-duel-card"><p>No se pudo iniciar el duelo' + (res && res.error ? ' (' + res.error + ')' : '') + '.</p><button type="button" class="hmbq-btn" data-action="duel-close">Cerrar</button></div>');
				return;
			}
			duelMode = true; duelMatchId = matchId; duelOpp = oppName || res.opponent || 'Rival';
			duelOppScore = 0; duelOppDone = false; duelEnded = false; duelOppLeft = false;
			lifelines = (typeof res.lifelines === 'number') ? res.lifelines : 0;
			duelStartElo = (typeof res.elo === 'number') ? res.elo : 1000;
			// Aprovechamos la cuenta atrás para: (1) mostrar ya la pantalla de juego
			// detrás del overlay y construir el reproductor (YouTube no se inicializa
			// en un elemento oculto), y (2) precargar la primera ronda. Así, al
			// terminar el 3-2-1 el clip arranca al instante, pegado al gesto.
			showScreen('play');
			ensurePlayer();
			primedRound = call('matchround', { match: matchId }).catch(function () { return null; });
			var n = 3;
			duelOverlay('<div class="hmbq-duel-card"><p class="hmbq-duel-vs-big">\u2694 Duelo contra ' + escapeHtml(duelOpp) + '</p><div class="hmbq-duel-count">' + n + '</div></div>');
			var ci = setInterval(function () {
				n--;
				var c = root.querySelector('.hmbq-duel-count');
				if (n > 0) { if (c) { c.textContent = n; } return; }
				clearInterval(ci);
				closeDuelOverlay();
				beginDuelPlay();
			}, 1000);
		}).catch(function () {
			duelOverlay('<div class="hmbq-duel-card"><p>Error de red al iniciar el duelo.</p><button type="button" class="hmbq-btn" data-action="duel-close">Cerrar</button></div>');
		});
	}

	function beginDuelPlay() {
		started = true;
		clearTimers();
		showScreen('play');
		if (document.activeElement && document.activeElement.blur) { document.activeElement.blur(); }
		try { root.scrollIntoView({ block: 'start' }); } catch (e) {}
		if (elScore) { elScore.textContent = '0'; }
		updateDuelHud();
		if (duelStateInt) { clearInterval(duelStateInt); }
		duelStateInt = setInterval(pollDuelState, 2500);
		ensurePlayer().then(function (ok) {
			if (!ok) { showError(t('noplayer')); }
			nextRound();
		});
	}

	function pollDuelState() {
		if (!duelMode || !duelMatchId) { return; }
		call('matchstate', { match: duelMatchId }).then(function (res) {
			if (!res || !res.ok) { return; }
			duelOppScore = res.oppScore || 0;
			if (res.oppFinished) { duelOppDone = true; }
			updateDuelHud();
			// Opponent left the duel → I win right now.
			if (!duelEnded && res.status === 'finished' && res.abandonedBy && res.abandonedBy !== res.me) {
				duelOppLeft = true;
				resolveDuel();
			}
		}).catch(function () {});
	}

	function duelEnd() {
		// I finished my rounds; wait for the opponent, then resolve.
		if (duelStateInt) { clearInterval(duelStateInt); duelStateInt = null; }
		hideDuelHud();
		duelOverlay('<div class="hmbq-duel-card"><p class="hmbq-duel-vs-big">\u2694 Has terminado</p><p>Esperando a que <strong>' + escapeHtml(duelOpp) + '</strong> acabe\u2026</p><p class="hmbq-duel-count">\u2694</p></div>');
		var tries = 0;
		var wait = setInterval(function () {
			tries++;
			call('matchstate', { match: duelMatchId }).then(function (res) {
				if (res && res.ok && (res.oppFinished || res.status === 'finished' || tries > 45)) {
					clearInterval(wait);
					resolveDuel();
				} else if (tries > 45) {
					clearInterval(wait);
					resolveDuel();
				}
			}).catch(function () { if (tries > 45) { clearInterval(wait); resolveDuel(); } });
		}, 2000);
	}

	// Fetch the final state once and show the verdict.
	function resolveDuel() {
		if (duelEnded) { return; }
		duelEnded = true;
		if (duelStateInt) { clearInterval(duelStateInt); duelStateInt = null; }
		clearTimers();
		try { player.pauseVideo(); } catch (e) {}
		hideDuelHud();
		started = false;
		var fallbackMine = elScore ? (parseInt(elScore.textContent, 10) || 0) : 0;
		call('matchstate', { match: duelMatchId }).then(function (res) {
			var mine    = (res && typeof res.score === 'number') ? res.score : fallbackMine;
			var opp     = (res && typeof res.oppScore === 'number') ? res.oppScore : duelOppScore;
			var newElo  = (res && typeof res.elo === 'number') ? res.elo : duelStartElo;
			var oppLeft = (res && res.abandonedBy && res.abandonedBy !== res.me) || duelOppLeft;
			var meLeft  = (res && res.abandonedBy && res.abandonedBy === res.me);
			showDuelResult(mine, opp, newElo, oppLeft, meLeft);
		}).catch(function () {
			showDuelResult(fallbackMine, duelOppScore, duelStartElo, duelOppLeft, false);
		});
	}

	function showDuelResult(mine, opp, newElo, oppLeft, meLeft) {
		var win = oppLeft || (!meLeft && mine > opp);
		var lose = meLeft || (!oppLeft && mine < opp);
		var tie = !win && !lose;

		var cls = win ? 'win' : (lose ? 'lose' : 'tie');

		// Fiercer flavour text.
		var bigWin  = ['\u{1F918} \u00a1APLASTADO!', '\u2694 \u00a1VICTORIA BRUTAL!', '\u{1F3B8} \u00a1HAS REVENTADO EL FOSO!', '\u{1F525} \u00a1DOMINIO TOTAL!'];
		var bigLose = ['\u2620 CA\u00cdSTE EN COMBATE', '\u{1F480} TE HAN HECHO PEDAZOS', '\u26B0 DERROTA EN EL FOSO', '\u{1F3B8} OTRA VEZ SER\u00c1, GUERRERO'];
		var bigTie  = ['\u2696 EMPATE \u00c9PICO', '\u2694 NADIE DOBLEGA AL OTRO'];

		var verdict;
		if (win)      { verdict = oppLeft ? '\u{1F3C6} \u00a1' + escapeHtml(duelOpp) + ' HUY\u00d3 DEL FOSO! GANAS' : pick(bigWin); }
		else if (lose){ verdict = meLeft ? '\u{1F3F3} Abandonaste el duelo' : pick(bigLose); }
		else          { verdict = pick(bigTie); }

		var diff = newElo - duelStartElo;
		var sign = diff > 0 ? '+' : '';
		var eloHtml = '<p class="hmbq-duel-elo">ELO: <strong>' + newElo + '</strong> <span class="hmbq-duel-elo-' + (diff >= 0 ? 'up' : 'down') + '">(' + sign + diff + ')</span></p>';

		duelOverlay(
			'<div class="hmbq-duel-card hmbq-duel-' + cls + '">' +
			'<p class="hmbq-duel-verdict">' + verdict + '</p>' +
			'<p class="hmbq-duel-tally"><span>T\u00fa</span><strong>' + mine + '</strong></p>' +
			'<p class="hmbq-duel-tally"><span>' + escapeHtml(duelOpp) + '</span><strong>' + opp + '</strong></p>' +
			eloHtml +
			'<button type="button" class="hmbq-btn" data-action="duel-close">Volver al foso</button>' +
			'</div>'
		);
		duelMode = false; duelMatchId = 0; duelStateInt = null; primedRound = null;

		// Premio del Versus: al ganar, mostramos la tarjeta VS (emergente + compartir).
		if (win) {
			cardShown = false;
			showCardPopup(mine, 0, true);
		}
	}

	function pick(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

	// Leaving the page mid-duel = forfeit; the opponent is declared winner.
	function sendAbandon() {
		if (!duelMode || duelEnded || !duelMatchId) { return; }
		try {
			var body = new URLSearchParams();
			body.append('task', 'game.matchabandon');
			if (cfg.token) { body.append(cfg.token, '1'); }
			body.append('match', duelMatchId);
			navigator.sendBeacon(cfg.base + '&task=game.matchabandon', body);
		} catch (e) {}
	}
	window.addEventListener('pagehide', sendAbandon);
	window.addEventListener('beforeunload', sendAbandon);

	if (cfg.loggedIn) {
		if (elVersusBtn) { elVersusBtn.hidden = false; }
		heartbeat();
		heartInt = setInterval(heartbeat, 10000);
		incomingInt = setInterval(pollIncoming, 5000);
		tryClaim();
	} else if (/[?&]hmblogin=1/.test(window.location.search)) {
		// Vuelta desde el registro → abrir el login del juego directamente.
		var hmbLo = root.querySelector('[data-login]');
		if (hmbLo) { hmbLo.hidden = false; }
	}

	updateGenreMode();
	refreshBoard();   // populate the per-category leaderboard on load
})();
