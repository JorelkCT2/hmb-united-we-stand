<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var array $rows, bool $enabled, bool $showMedals, bool $showDetail, string $moduleclass */

if (!$enabled) {
	return;
}

$medals = ['🥇', '🥈', '🥉'];
?>
<div class="hmbq-rank-mod <?php echo htmlspecialchars($moduleclass, ENT_QUOTES, 'UTF-8'); ?>">
	<?php if ($styledTitle && $titleText !== '') : ?>
		<h3 class="hmbq-rank-title"><?php echo htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8'); ?></h3>
	<?php endif; ?>
	<?php if (empty($rows)) : ?>
		<p class="hmbq-rank-empty"><?php echo Text::_('MOD_HMB_QUIZ_RANKING_EMPTY'); ?></p>
	<?php else : ?>
		<ol class="hmbq-rank-list">
			<?php foreach ($rows as $i => $row) : ?>
				<li class="hmbq-rank-row<?php echo $i < 3 ? ' hmbq-top3 hmbq-pos' . ($i + 1) : ''; ?>">
					<span class="hmbq-rank-pos">
						<?php echo ($showMedals && $i < 3) ? $medals[$i] : ($i + 1); ?>
					</span>
					<span class="hmbq-rank-name"><?php echo htmlspecialchars($row['player_name'], ENT_QUOTES, 'UTF-8'); ?></span>
					<?php if (($source ?? 'scores') === 'league') : ?>
						<span class="hmbq-rank-score">
							<?php echo (int) $row['total_league']; ?>
							<?php if ($showDetail) : ?>
								<?php
								$acc = (int) $row['total_rounds'] > 0
									? round((int) $row['total_correct'] / (int) $row['total_rounds'] * 100)
									: 0;
								$spd = (int) $row['total_timed'] > 0
									? round((int) $row['total_ms'] / (int) $row['total_timed'] / 1000, 1)
									: null;
								?>
								<small class="hmbq-rank-detail">
									<?php echo (int) $row['games_played']; ?>p · <?php echo $acc; ?>%<?php echo $spd !== null ? ' · ' . $spd . 's' : ''; ?>
								</small>
							<?php endif; ?>
						</span>
					<?php else : ?>
						<span class="hmbq-rank-score">
							<?php echo (int) $row['score']; ?>
							<?php if ($showDetail) : ?>
								<small class="hmbq-rank-detail"><?php echo (int) $row['correct']; ?>/<?php echo (int) $row['rounds']; ?></small>
							<?php endif; ?>
						</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</div>

<style>
.hmbq-rank-mod { color: #e8e8e8; }
.hmbq-rank-mod .hmbq-rank-title {
	font-family: 'Metal Mania', 'Cinzel', inherit;
	color: #ff1f2d;
	text-align: center;
	letter-spacing: 1px;
	text-shadow: 0 0 12px rgba(255, 31, 45, 0.45);
	margin: 0 0 .75rem;
}
.hmbq-rank-mod .hmbq-rank-list { list-style: none; margin: 0; padding: 0; counter-reset: none; }
.hmbq-rank-mod .hmbq-rank-row {
	display: flex; align-items: center; gap: .6rem;
	padding: .5rem .65rem; border-bottom: 1px solid #1f1f1f;
}
.hmbq-rank-mod .hmbq-rank-row:last-child { border-bottom: 0; }
.hmbq-rank-mod .hmbq-rank-pos {
	width: 1.9rem; text-align: center; font-weight: 700; color: var(--fire, #CC2200); flex: 0 0 auto;
}
.hmbq-rank-mod .hmbq-top3 .hmbq-rank-pos { font-size: 1.15rem; }
.hmbq-rank-mod .hmbq-rank-name { flex: 1 1 auto; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hmbq-rank-mod .hmbq-rank-score { font-weight: 700; color: #ff1f2d; flex: 0 0 auto; }
.hmbq-rank-mod .hmbq-rank-detail { color: #8a8a8a; font-weight: 400; margin-left: .3rem; }
.hmbq-rank-mod .hmbq-pos1 .hmbq-rank-name { color: #ffd24a; }
.hmbq-rank-mod .hmbq-rank-empty { color: #8a8a8a; }
</style>
