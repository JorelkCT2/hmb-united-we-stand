<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var array $cfg, array $board, bool $enabled, bool $showBoard, bool $compact, string $moduleclass */

if (!$enabled) :
	?>
	<div class="hmb-quiz-disabled alert alert-warning">
		<?php echo Text::_('MOD_HMB_QUIZ_NEEDS_COMPONENT'); ?>
	</div>
	<?php
	return;
endif;

$cfgJson = htmlspecialchars(json_encode($cfg), ENT_QUOTES, 'UTF-8');
$classes = 'hmb-quiz' . ($compact ? ' hmbq-compact' : '') . ($moduleclass ? ' ' . $moduleclass : '');
?>
<?php if ($compact) : ?>
<style>
.hmbq-compact { max-width: 420px; padding: 1rem; }
.hmbq-compact .hmbq-title { font-size: 1.3rem; }
.hmbq-compact .hmbq-options { grid-template-columns: 1fr; }
.hmbq-compact .hmbq-sub { font-size: .9rem; }
</style>
<?php endif; ?>
<div class="<?php echo $classes; ?>" id="hmbQuiz" data-config="<?php echo $cfgJson; ?>">

	<button type="button" class="hmbq-fs" data-action="fullscreen" title="<?php echo Text::_('COM_HMB_QUIZ_FULLSCREEN'); ?>" aria-label="<?php echo Text::_('COM_HMB_QUIZ_FULLSCREEN'); ?>">&#9974;</button>

	<!-- START -->
	<section class="hmbq-screen hmbq-start is-active" data-screen="start">
		<h2 class="hmbq-title"><?php echo Text::_('COM_HMB_QUIZ_GAME_TITLE'); ?></h2>
		<p class="hmbq-sub"><?php echo Text::_('COM_HMB_QUIZ_GAME_INTRO'); ?></p>

		<?php if (!empty($cfg['chooseLevel'])) : ?>
			<p class="hmbq-sel-label" data-sel="level"><?php echo Text::_('COM_HMB_QUIZ_CHOOSE_LEVEL'); ?></p>
			<div class="hmbq-pills" data-sel="level">
				<?php
				$dl = (int) $cfg['defaultLevel'];
				$lvls = [1 => 'COM_HMB_QUIZ_LEVEL_EASY', 2 => 'COM_HMB_QUIZ_LEVEL_MEDIUM', 3 => 'COM_HMB_QUIZ_LEVEL_HARD', 0 => 'COM_HMB_QUIZ_LEVEL_MIX'];
				foreach ($lvls as $v => $k) : ?>
					<button type="button" class="hmbq-pill<?php echo $v === $dl ? ' is-active' : ''; ?>" data-group="level" data-value="<?php echo $v; ?>"><?php echo Text::_($k); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($cfg['chooseRounds'])) : ?>
			<p class="hmbq-sel-label"><?php echo Text::_('COM_HMB_QUIZ_CHOOSE_ROUNDS'); ?></p>
			<div class="hmbq-pills">
				<?php $dr = (int) $cfg['rounds']; foreach ([10, 20, 50] as $v) : ?>
					<button type="button" class="hmbq-pill<?php echo $v === $dr ? ' is-active' : ''; ?>" data-group="rounds" data-value="<?php echo $v; ?>"><?php echo $v; ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($cfg['chooseLang'])) : ?>
			<p class="hmbq-sel-label" data-sel="lang"><?php echo Text::_('COM_HMB_QUIZ_CHOOSE_LANG'); ?></p>
			<div class="hmbq-pills" data-sel="lang">
				<button type="button" class="hmbq-pill" data-group="lang" data-value="es"><?php echo Text::_('COM_HMB_QUIZ_LANG_ES'); ?></button>
				<button type="button" class="hmbq-pill" data-group="lang" data-value="en"><?php echo Text::_('COM_HMB_QUIZ_LANG_EN'); ?></button>
				<button type="button" class="hmbq-pill is-active" data-group="lang" data-value=""><?php echo Text::_('COM_HMB_QUIZ_LANG_BOTH'); ?></button>
			</div>
		<?php endif; ?>

		<?php if (!empty($cfg['chooseGenres']) && !empty($genres)) : ?>
			<p class="hmbq-sel-label" data-sel="genre"><?php echo Text::_('COM_HMB_QUIZ_CHOOSE_GENRES'); ?></p>
			<div class="hmbq-pills" data-sel="genre">
				<?php foreach ($genres as $group) : $val = implode('|', $group); $lbl = implode(' / ', $group); ?>
					<button type="button" class="hmbq-pill" data-group="genre" data-multi="1" data-value="<?php echo htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?></button>
				<?php endforeach; ?>
			</div>
			<p class="hmbq-genre-note"><?php echo sprintf(Text::_('COM_HMB_QUIZ_GENRE_NOTE'), (int) ($cfg['genrePct'] ?? 50)); ?></p>
		<?php endif; ?>

		<button type="button" class="hmbq-btn hmbq-btn-primary hmbq-start-btn" data-action="start">
			<?php echo Text::_('COM_HMB_QUIZ_START'); ?>
		</button>

		<button type="button" class="hmbq-btn hmbq-btn-versus" data-action="versus-open" hidden>
			&#9876; <?php echo Text::_('COM_HMB_QUIZ_VERSUS'); ?>
		</button>

		<div class="hmbq-versus" data-versus hidden>
			<div class="hmbq-versus-head">
				<h2><?php echo Text::_('COM_HMB_QUIZ_VERSUS_PLAYERS'); ?></h2>
				<button type="button" class="hmbq-versus-close" data-action="versus-close" aria-label="X">&times;</button>
			</div>
			<ul class="hmbq-versus-list" data-versus-list></ul>
			<p class="hmbq-versus-empty" data-versus-empty hidden><?php echo Text::_('COM_HMB_QUIZ_VERSUS_EMPTY'); ?></p>
		</div>

		<?php if ($showBoard) : ?>
			<div class="hmbq-board" data-board>
				<h3><?php echo Text::_('COM_HMB_QUIZ_LEADERBOARD'); ?></h3>
				<ol class="hmbq-board-list" data-board-list>
					<?php foreach ($board as $i => $row) : ?>
						<li>
							<span class="hmbq-rank"><?php echo $i + 1; ?></span>
							<span class="hmbq-name"><?php echo htmlspecialchars($row['player_name'], ENT_QUOTES, 'UTF-8'); ?></span>
							<span class="hmbq-pts"><?php echo (int) $row['score']; ?></span>
						</li>
					<?php endforeach; ?>
					<?php if (empty($board)) : ?>
						<li class="hmbq-empty"><?php echo Text::_('COM_HMB_QUIZ_BOARD_EMPTY'); ?></li>
					<?php endif; ?>
				</ol>
			</div>
		<?php endif; ?>
	</section>

	<!-- PLAY -->
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
		</div>
	</section>

	<!-- END -->
	<section class="hmbq-screen hmbq-end" data-screen="end">
		<h3 class="hmbq-title"><?php echo Text::_('COM_HMB_QUIZ_GAME_OVER'); ?></h3>
		<p class="hmbq-final"><?php echo Text::_('COM_HMB_QUIZ_YOUR_SCORE'); ?>: <strong data-final-score>0</strong></p>
		<p class="hmbq-final-detail" data-final-detail></p>

		<div class="hmbq-save" data-save>
			<label for="hmbqName"><?php echo Text::_('COM_HMB_QUIZ_YOUR_NAME'); ?></label>
			<input type="text" id="hmbqName" maxlength="100" autocomplete="off"
				placeholder="<?php echo Text::_('COM_HMB_QUIZ_NAME_PLACEHOLDER'); ?>">
			<button type="button" class="hmbq-btn hmbq-btn-primary" data-action="save">
				<?php echo Text::_('COM_HMB_QUIZ_SAVE_SCORE'); ?>
			</button>
		</div>

		<p class="hmbq-rank-msg" data-rank-msg hidden></p>

		<div class="hmbq-board" data-end-board>
			<h3><?php echo Text::_('COM_HMB_QUIZ_LEADERBOARD'); ?></h3>
			<ol class="hmbq-board-list" data-end-board-list></ol>
		</div>

		<button type="button" class="hmbq-btn hmbq-btn-ghost" data-action="again">
			<?php echo Text::_('COM_HMB_QUIZ_PLAY_AGAIN'); ?>
		</button>
	</section>

	<p class="hmbq-error" data-error hidden></p>
</div>
