<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

/** @var \Hmb\Component\Quiz\Site\View\Game\HtmlView $this */

$cfg = htmlspecialchars(json_encode($this->jsConfig), ENT_QUOTES, 'UTF-8');

$hmbBase = Uri::getInstance()->toString();

// Menú activo → Itemid, para enrutar el retorno a /metal-quiz (no a la URL fea).
$hmbActive = \Joomla\CMS\Factory::getApplication()->getMenu()->getActive();
$hmbItemid = $hmbActive ? (int) $hmbActive->id : 0;

// Ruta RELATIVA interna del juego. Al ser relativa pasa Uri::isInternal() y conserva el
// host actual (y, por tanto, la sesión). Esto evita que el login/registro te eche a la
// home por el lío www / sin-www: una URL absoluta con el host "equivocado" la descarta
// Joomla y te manda al inicio.
$hmbGameRoute = 'index.php?option=com_hmb_quiz' . ($hmbItemid ? '&Itemid=' . $hmbItemid : '');

// Login nativo (com_users): 'return' en base64 ESTÁNDAR de la ruta interna relativa.
$hmbReturn = base64_encode($hmbGameRoute);

// Registro (com_hmb): volver al juego con 'rdr' en base64url (lo decodifica com_hmb).
$hmbGhostRdr         = rtrim(strtr(base64_encode($hmbGameRoute), '+/', '-_'), '=');
$hmbGhostRegisterUrl = Uri::root() . 'index.php/registro?layout=register&rdr=' . $hmbGhostRdr;
$hmbRegisterUrl      = $hmbGhostRegisterUrl;
?>
<div class="hmb-quiz" id="hmbQuiz" data-config="<?php echo $cfg; ?>">

	<div class="hmbq-flag hmbq-flag-left" aria-hidden="true"></div>
	<div class="hmbq-flag hmbq-flag-right" aria-hidden="true"></div>

	<button type="button" class="hmbq-fs" data-action="fullscreen" title="<?php echo Text::_('COM_HMB_QUIZ_FULLSCREEN'); ?>" aria-label="<?php echo Text::_('COM_HMB_QUIZ_FULLSCREEN'); ?>">&#9974;</button>

	<!-- START SCREEN -->
	<section class="hmbq-screen hmbq-start is-active" data-screen="start">
		<h1 class="hmbq-title"><?php echo Text::_('COM_HMB_QUIZ_GAME_TITLE'); ?></h1>
		<p class="hmbq-sub"><?php echo Text::_('COM_HMB_QUIZ_GAME_INTRO'); ?></p>

		<div class="hmbq-setup">
	<?php if (!empty($this->jsConfig['chooseContent'])) : ?>
		<div class="hmbq-group">
			<p class="hmbq-sel-label" data-sel="content">&#9889; <?php echo Text::_('COM_HMB_QUIZ_CHOOSE_CONTENT'); ?></p>
			<div class="hmbq-pills" data-sel="content">
				<button type="button" class="hmbq-pill is-active" data-group="content" data-value="both"><span class="hmbq-pi">&#128293;</span><span class="hmbq-pt"><?php echo Text::_('COM_HMB_QUIZ_CONTENT_BOTH'); ?></span></button>
				<button type="button" class="hmbq-pill" data-group="content" data-value="song"><span class="hmbq-pi">&#127925;</span><span class="hmbq-pt"><?php echo Text::_('COM_HMB_QUIZ_CONTENT_MUSIC'); ?></span></button>
				<button type="button" class="hmbq-pill" data-group="content" data-value="movie"><span class="hmbq-pi">&#127916;</span><span class="hmbq-pt"><?php echo Text::_('COM_HMB_QUIZ_CONTENT_MOVIES'); ?></span></button>
			</div>
		</div>
	<?php endif; ?>

	<?php if (!empty($this->jsConfig['chooseLevel'])) : ?>
		<div class="hmbq-group">
			<p class="hmbq-sel-label" data-sel="level">&#128128; <?php echo Text::_('COM_HMB_QUIZ_CHOOSE_LEVEL'); ?></p>
			<div class="hmbq-pills" data-sel="level">
				<?php
				$dl = (int) $this->jsConfig['defaultLevel'];
				$lvls = [1 => 'COM_HMB_QUIZ_LEVEL_EASY', 2 => 'COM_HMB_QUIZ_LEVEL_MEDIUM', 3 => 'COM_HMB_QUIZ_LEVEL_HARD', 0 => 'COM_HMB_QUIZ_LEVEL_MIX'];
				$lico = [1 => '&#128128;', 2 => '&#128128;&#128128;', 3 => '&#128128;&#128128;&#128128;', 0 => '&#127922;'];
				foreach ($lvls as $v => $k) : ?>
					<button type="button" class="hmbq-pill<?php echo $v === $dl ? ' is-active' : ''; ?>" data-group="level" data-value="<?php echo $v; ?>"><span class="hmbq-pi"><?php echo $lico[$v]; ?></span><span class="hmbq-pt"><?php echo Text::_($k); ?></span></button>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if (!empty($this->jsConfig['chooseRounds'])) : ?>
		<div class="hmbq-group">
			<p class="hmbq-sel-label">&#127908; <?php echo Text::_('COM_HMB_QUIZ_CHOOSE_ROUNDS'); ?></p>
			<div class="hmbq-pills">
				<?php
				$dr = (int) $this->jsConfig['rounds'];
				$rtag = [10 => 'COM_HMB_QUIZ_ROUNDS_TAG_10', 20 => 'COM_HMB_QUIZ_ROUNDS_TAG_20', 50 => 'COM_HMB_QUIZ_ROUNDS_TAG_50'];
				foreach ([10, 20, 50] as $v) : ?>
					<button type="button" class="hmbq-pill<?php echo $v === $dr ? ' is-active' : ''; ?>" data-group="rounds" data-value="<?php echo $v; ?>"><span class="hmbq-pn"><?php echo $v; ?></span><span class="hmbq-ps"><?php echo Text::_($rtag[$v]); ?></span></button>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if (!empty($this->jsConfig['chooseLang'])) : ?>
		<div class="hmbq-group">
			<p class="hmbq-sel-label" data-sel="lang">&#127758; <?php echo Text::_('COM_HMB_QUIZ_CHOOSE_LANG'); ?></p>
			<div class="hmbq-pills" data-sel="lang">
				<button type="button" class="hmbq-pill" data-group="lang" data-value="es"><span class="hmbq-pi">&#127466;&#127480;</span><span class="hmbq-pt"><?php echo Text::_('COM_HMB_QUIZ_LANG_ES'); ?></span></button>
				<button type="button" class="hmbq-pill" data-group="lang" data-value="en"><span class="hmbq-pi">&#127468;&#127463;</span><span class="hmbq-pt"><?php echo Text::_('COM_HMB_QUIZ_LANG_EN'); ?></span></button>
				<button type="button" class="hmbq-pill is-active" data-group="lang" data-value=""><span class="hmbq-pi">&#127757;</span><span class="hmbq-pt"><?php echo Text::_('COM_HMB_QUIZ_LANG_BOTH'); ?></span></button>
			</div>
		</div>
	<?php endif; ?>

	<?php if (!empty($this->jsConfig['chooseGenres']) && !empty($this->genres)) : ?>
		<div class="hmbq-group hmbq-group-wide">
			<p class="hmbq-sel-label" data-sel="genre">&#129304; <?php echo Text::_('COM_HMB_QUIZ_CHOOSE_GENRES'); ?></p>
			<div class="hmbq-pills hmbq-genres" data-sel="genre">
				<?php
				$gico = [1 => '&#127928;', 2 => '&#9889;', 3 => '&#129304;', 4 => '&#128009;', 5 => '&#128293;'];
				$gi = 0;
				foreach ($this->genres as $group) :
					$gi++;
					$val = $group['value'];
					$lbl = $group['name'] !== '' ? $group['name'] : $group['label'];
				?>
					<button type="button" class="hmbq-pill hmbq-gcard hmbq-g<?php echo $gi; ?>" data-group="genre" data-multi="1" data-value="<?php echo $this->escape($val); ?>" title="<?php echo $this->escape($group['label']); ?>"><span class="hmbq-pi"><?php echo $gico[$gi] ?? '&#128293;'; ?></span><span class="hmbq-pt"><?php echo $this->escape($lbl); ?></span></button>
				<?php endforeach; ?>
			</div>
			<p class="hmbq-genre-note">&#9888; <?php echo sprintf(Text::_('COM_HMB_QUIZ_GENRE_NOTE'), (int) ($this->jsConfig['genrePct'] ?? 50)); ?></p>
		</div>
	<?php endif; ?>
	</div>

	<div class="hmbq-cta">
		<button type="button" class="hmbq-btn hmbq-btn-primary hmbq-start-btn" data-action="start">
			&#129304; <?php echo Text::_('COM_HMB_QUIZ_START'); ?>
		</button>
		<button type="button" class="hmbq-btn hmbq-btn-versus" data-action="versus-open">
			&#9876; <?php echo Text::_('COM_HMB_QUIZ_VERSUS'); ?>
		</button>
		<button type="button" class="hmbq-btn hmbq-btn-ghost hmbq-guide-btn" data-action="guide-open">
			&#128214; &iquest;C&oacute;mo se juega?
		</button>
	</div>

		<div class="hmbq-versus" data-versus hidden>
			<div class="hmbq-versus-head">
				<h2><?php echo Text::_('COM_HMB_QUIZ_VERSUS_PLAYERS'); ?></h2>
				<button type="button" class="hmbq-versus-close" data-action="versus-close" aria-label="X">&times;</button>
			</div>
			<ul class="hmbq-versus-list" data-versus-list></ul>
			<p class="hmbq-versus-empty" data-versus-empty hidden><?php echo Text::_('COM_HMB_QUIZ_VERSUS_EMPTY'); ?></p>
		</div>

		<div class="hmbq-board" data-board>
			<h2><?php echo Text::_('COM_HMB_QUIZ_LEADERBOARD'); ?></h2>
			<div class="hmbq-board-tabs" data-board-tabs></div>
			<div class="hmbq-board-header" aria-hidden="true">
				<span class="hmbq-rank"><?php echo Text::_('COM_HMB_QUIZ_BOARD_POS'); ?></span>
				<span class="hmbq-name"><?php echo Text::_('COM_HMB_QUIZ_BOARD_NAME'); ?></span>
				<span class="hmbq-hits"><?php echo Text::_('COM_HMB_QUIZ_BOARD_HITS'); ?></span>
				<span class="hmbq-pts"><?php echo Text::_('COM_HMB_QUIZ_BOARD_PTS'); ?></span>
				<span class="hmbq-total"><?php echo Text::_('COM_HMB_QUIZ_BOARD_TOTAL'); ?></span>
			</div>
			<ol class="hmbq-board-list" data-board-list>
				<?php foreach ($this->board as $i => $row) :
					$rk = $i + 1;
					$medal = $rk === 1 ? "\u{1F947}" : ($rk === 2 ? "\u{1F948}" : ($rk === 3 ? "\u{1F949}" : ''));
				?>
					<li class="hmbq-row<?php echo $medal !== '' ? ' hmbq-top' . $rk : ''; ?>">
						<span class="hmbq-rank"><?php echo $medal !== '' ? $medal : $rk; ?></span>
						<span class="hmbq-name"><?php echo htmlspecialchars($row['player_name'], ENT_QUOTES, 'UTF-8'); ?></span>
						<span class="hmbq-hits"><?php echo (int) ($row['correct'] ?? 0); ?></span>
						<span class="hmbq-pts"><?php echo (int) $row['score']; ?></span>
						<span class="hmbq-total"><?php echo (int) ($row['total'] ?? 0); ?></span>
					</li>
				<?php endforeach; ?>
				<?php if (empty($this->board)) : ?>
					<li class="hmbq-empty"><?php echo Text::_('COM_HMB_QUIZ_BOARD_EMPTY'); ?></li>
				<?php endif; ?>
			</ol>
		</div>
	</section>

	<!-- PLAY SCREEN -->
	<section class="hmbq-screen hmbq-play" data-screen="play">
		<div class="hmbq-hud">
			<span class="hmbq-progress" data-progress>0 / 0</span>
			<span class="hmbq-timer" data-timer></span>
			<span class="hmbq-scorebox"><?php echo Text::_('COM_HMB_QUIZ_SCORE'); ?>: <strong data-score>0</strong></span>
		</div>

		<div class="hmbq-stage">
			<div class="hmbq-player-wrap" data-player-wrap>
				<div id="hmbqPlayer"></div>
				<canvas class="hmbq-cover-canvas" data-cover-canvas hidden></canvas>
				<img class="hmbq-cover-img" data-cover-img alt="" hidden>
				<div class="hmbq-cover" data-cover>
					<div class="hmbq-equalizer"><span></span><span></span><span></span><span></span><span></span></div>
					<p class="hmbq-listen"><?php echo Text::_('COM_HMB_QUIZ_LISTEN'); ?></p>
				</div>
			</div>

			<p class="hmbq-question" data-question></p>

			<button type="button" class="hmbq-replay" data-action="replay" hidden>
				<?php echo Text::_('COM_HMB_QUIZ_REPLAY'); ?>
			</button>

			<button type="button" class="hmbq-lifeline" data-action="lifeline" hidden>
					<span class="hmbq-ll-ico">&#9889;</span> 50&#8758;50 <span data-ll-count></span>
				</button>

				<div class="hmbq-options" data-options></div>

				<button type="button" class="hmbq-btn hmbq-btn-ghost hmbq-quit" data-action="quit">
					&#10005; <?php echo Text::_('COM_HMB_QUIZ_QUIT'); ?>
				</button>
		</div>
	</section>

	<!-- END SCREEN -->
	<section class="hmbq-screen hmbq-end" data-screen="end">
		<h2 class="hmbq-title"><?php echo Text::_('COM_HMB_QUIZ_GAME_OVER'); ?></h2>
		<p class="hmbq-final">
			<?php echo Text::_('COM_HMB_QUIZ_YOUR_SCORE'); ?>:
			<strong data-final-score>0</strong>
		</p>
		<p class="hmbq-final-detail" data-final-detail></p>

		<div class="hmbq-save" data-save>
			<label for="hmbqName"><?php echo Text::_('COM_HMB_QUIZ_YOUR_NAME'); ?></label>
			<input type="text" id="hmbqName" maxlength="100" autocomplete="off"
				placeholder="<?php echo Text::_('COM_HMB_QUIZ_NAME_PLACEHOLDER'); ?>">
			<button type="button" class="hmbq-btn hmbq-btn-primary" data-action="save">
				<?php echo Text::_('COM_HMB_QUIZ_SAVE_SCORE'); ?>
			</button>
		</div>

		<div class="hmbq-ghost" data-ghost hidden>
			<p class="hmbq-ghost-msg" data-ghost-msg></p>
			<a class="hmbq-btn hmbq-btn-primary hmbq-ghost-cta" href="<?php echo htmlspecialchars($hmbGhostRegisterUrl, ENT_QUOTES, 'UTF-8'); ?>">
				&#128081; <?php echo Text::_('COM_HMB_QUIZ_GHOST_CLAIM'); ?>
			</a>
			<p class="hmbq-ghost-sub"><?php echo Text::_('COM_HMB_QUIZ_GHOST_SUB'); ?></p>
		</div>

		<p class="hmbq-rank-msg" data-rank-msg hidden></p>

		<div class="hmbq-board" data-end-board>
			<h2><?php echo Text::_('COM_HMB_QUIZ_LEADERBOARD'); ?></h2>
			<div class="hmbq-board-tabs" data-board-tabs></div>
			<div class="hmbq-board-header" aria-hidden="true">
				<span class="hmbq-rank"><?php echo Text::_('COM_HMB_QUIZ_BOARD_POS'); ?></span>
				<span class="hmbq-name"><?php echo Text::_('COM_HMB_QUIZ_BOARD_NAME'); ?></span>
				<span class="hmbq-hits"><?php echo Text::_('COM_HMB_QUIZ_BOARD_HITS'); ?></span>
				<span class="hmbq-pts"><?php echo Text::_('COM_HMB_QUIZ_BOARD_PTS'); ?></span>
				<span class="hmbq-total"><?php echo Text::_('COM_HMB_QUIZ_BOARD_TOTAL'); ?></span>
			</div>
			<ol class="hmbq-board-list" data-end-board-list></ol>
		</div>

		<div class="hmbq-share" data-share>
			<p class="hmbq-share-label"><?php echo Text::_('COM_HMB_QUIZ_SHARE_LABEL'); ?></p>
			<div class="hmbq-share-btns">
				<button type="button" class="hmbq-share-btn hmbq-share-native" data-share-do="native" hidden>&#128228; <?php echo Text::_('COM_HMB_QUIZ_SHARE'); ?></button>
				<a class="hmbq-share-btn hmbq-share-wa" data-share-do="whatsapp" target="_blank" rel="noopener">WhatsApp</a>
				<a class="hmbq-share-btn hmbq-share-tg" data-share-do="telegram" target="_blank" rel="noopener">Telegram</a>
				<a class="hmbq-share-btn hmbq-share-x" data-share-do="x" target="_blank" rel="noopener">X</a>
				<button type="button" class="hmbq-share-btn hmbq-share-ig" data-share-do="image">&#128247; Instagram</button>
				<button type="button" class="hmbq-share-btn hmbq-share-copy" data-share-do="copy"><?php echo Text::_('COM_HMB_QUIZ_SHARE_COPY'); ?></button>
			</div>
		</div>

		<button type="button" class="hmbq-btn hmbq-btn-ghost" data-action="again">
			<?php echo Text::_('COM_HMB_QUIZ_PLAY_AGAIN'); ?>
		</button>
	</section>

	<p class="hmbq-error" data-error hidden></p>

	<!-- ¿Cómo se juega? (emergente cerrable) -->
	<div class="hmbq-guide-overlay" data-guide hidden>
		<div class="hmbq-guide-card">
			<button type="button" class="hmbq-guide-x" data-action="guide-close" aria-label="Cerrar">&#10005;</button>
			<h2 class="hmbq-guide-h1">&#127928; C&oacute;mo se juega al Metal Quiz</h2>
			<p class="hmbq-guide-lead">Suena un fragmento de un tema. T&uacute; adivinas. Cuanto m&aacute;s r&aacute;pido aciertes, m&aacute;s puntos. As&iacute; de simple, as&iacute; de bestia. &#129304;</p>

			<h3 class="hmbq-guide-h2">Partida en solitario</h3>
			<ol class="hmbq-guide-steps">
				<li>Elige (si est&aacute; disponible) <strong>dificultad</strong>, <strong>n&uacute;mero de rondas</strong> y <strong>g&eacute;nero</strong>.</li>
				<li>Pulsa <strong>Empezar</strong>. Sonar&aacute; un fragmento de cada tema.</li>
				<li>Toca la respuesta correcta <strong>antes de que se acabe el tiempo</strong>.</li>
				<li>Si aciertas, se revela el videoclip; si fallas, a por la siguiente.</li>
			</ol>

			<h3 class="hmbq-guide-h2">Puntuaci&oacute;n</h3>
			<p>Por cada acierto sumas puntos <strong>+ un bonus por rapidez</strong>: cuanto antes respondas, m&aacute;s puntos. El modo por g&eacute;neros puntua un poco menos (m&aacute;s f&aacute;cil).</p>

			<h3 class="hmbq-guide-h2">&#9889; Comod&iacute;n 50:50</h3>
			<p>Si est&aacute; activado, puedes usarlo para <strong>eliminar dos respuestas incorrectas</strong> en una ronda. &uacute;salo cuando vayas perdido. Tienes un n&uacute;mero limitado por partida.</p>

			<h3 class="hmbq-guide-h2">&#9876; Modo Versus (duelo en directo)</h3>
			<ol class="hmbq-guide-steps">
				<li>Activa <strong>recibir invitaciones Versus</strong> en tu perfil y entra en el lobby con el bot&oacute;n <strong>&#9876; Versus</strong>.</li>
				<li>Ver&aacute;s a los dem&aacute;s usuarios conectados. Pulsa <strong>&#9876; Retar</strong> a uno.</li>
				<li>Si acepta, arranca un duelo: <strong>las mismas canciones para los dos</strong>, a la vez, con el <strong>marcador del rival en vivo</strong>.</li>
				<li>Gana quien <strong>m&aacute;s puntos</strong> consiga. &iexcl;Ojo! Si <strong>abandonas el duelo</strong>, tu rival gana autom&aacute;ticamente.</li>
			</ol>

			<h3 class="hmbq-guide-h2">&#127942; Ranking Versus (ELO)</h3>
			<p>Cada duelo cuenta para tu <strong>ELO</strong>. Empiezas en <strong>1000</strong>: ganar a un rival fuerte te sube mucho, perder contra uno flojo te baja. Consulta la clasificaci&oacute;n con <strong>&#127942; Ranking Versus</strong> en el lobby. &iexcl;A por el n&uacute;mero 1 del foso!</p>

			<button type="button" class="hmbq-btn hmbq-btn-primary" data-action="guide-close">&#129304; &iexcl;A jugar!</button>
		</div>
	</div>

	<!-- Login para Modo Versus (emergente) -->
	<div class="hmbq-guide-overlay hmbq-login-overlay" data-login hidden>
		<div class="hmbq-guide-card hmbq-login-card">
			<button type="button" class="hmbq-guide-x" data-action="login-close" aria-label="Cerrar">&#10005;</button>
			<h2 class="hmbq-guide-h1">&#9876; Modo Versus</h2>
			<p class="hmbq-guide-lead">Para retar a otros hermanos del metal necesitas iniciar sesi&oacute;n.</p>
			<form action="<?php echo Route::_('index.php', false); ?>" method="post" class="hmbq-login-form">
				<label class="hmbq-login-label">Usuario
					<input type="text" name="username" autocomplete="username" required>
				</label>
				<label class="hmbq-login-label">Contrase&ntilde;a
					<input type="password" name="password" autocomplete="current-password" required>
				</label>
				<label class="hmbq-login-remember">
					<input type="checkbox" name="remember" value="yes"> Recu&eacute;rdame
				</label>
				<button type="submit" class="hmbq-btn hmbq-btn-primary">&#129304; Entrar</button>
				<input type="hidden" name="option" value="com_users">
				<input type="hidden" name="task" value="user.login">
				<input type="hidden" name="return" value="<?php echo $hmbReturn; ?>">
				<?php echo HTMLHelper::_('form.token'); ?>
			</form>
			<p class="hmbq-login-alt">&iquest;A&uacute;n no eres del clan? <a href="<?php echo htmlspecialchars($hmbRegisterUrl, ENT_QUOTES, 'UTF-8'); ?>">Reg&iacute;strate</a></p>
		</div>
	</div>
</div>
