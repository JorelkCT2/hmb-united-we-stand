<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Hmb\Component\Quiz\Administrator\View\Players\HtmlView $this */

$search = $this->escape($this->state->get('filter.search'));
?>
<form action="<?php echo Route::_('index.php?option=com_hmb_quiz&view=players'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">

				<div class="js-stools clearfix mb-3">
					<div class="d-flex gap-2 flex-wrap align-items-center">
						<input type="text" name="filter_search" class="form-control" style="max-width:280px"
							value="<?php echo $search; ?>"
							placeholder="<?php echo Text::_('COM_HMB_QUIZ_SEARCH_PLAYER'); ?>">
						<button type="submit" class="btn btn-primary"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
					</div>
				</div>

				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span>
						<?php echo Text::_('COM_HMB_QUIZ_NO_PLAYERS'); ?>
					</div>
				<?php else : ?>
					<table class="table table-striped" id="playerList">
						<thead>
							<tr>
								<td class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></td>
								<th class="w-1 text-center">#</th>
								<th><?php echo Text::_('COM_HMB_QUIZ_PLAYER'); ?></th>
								<th class="text-center"><?php echo Text::_('COM_HMB_QUIZ_LEAGUE_POINTS'); ?></th>
								<th class="text-center"><?php echo Text::_('COM_HMB_QUIZ_GAMES'); ?></th>
								<th class="text-center"><?php echo Text::_('COM_HMB_QUIZ_ACCURACY'); ?></th>
								<th class="text-center d-none d-md-table-cell"><?php echo Text::_('COM_HMB_QUIZ_AVG_SPEED'); ?></th>
								<th class="text-center d-none d-md-table-cell"><?php echo Text::_('COM_HMB_QUIZ_BEST'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<?php
							$acc = (int) $item->total_rounds > 0
								? round((int) $item->total_correct / (int) $item->total_rounds * 100)
								: 0;
							$spd = (int) $item->total_timed > 0
								? round((int) $item->total_ms / (int) $item->total_timed / 1000, 1) . 's'
								: '-';
							?>
							<tr>
								<td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->user_id); ?></td>
								<td class="text-center"><?php echo $i + 1 + $this->pagination->limitstart; ?></td>
								<td><?php echo $this->escape($item->player_name); ?></td>
								<td class="text-center"><strong><?php echo (int) $item->total_league; ?></strong></td>
								<td class="text-center"><?php echo (int) $item->games_played; ?></td>
								<td class="text-center"><?php echo $acc; ?>% <small class="text-muted">(<?php echo (int) $item->total_correct; ?>/<?php echo (int) $item->total_rounds; ?>)</small></td>
								<td class="text-center d-none d-md-table-cell"><?php echo $spd; ?></td>
								<td class="text-center d-none d-md-table-cell"><?php echo (int) $item->best_score; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
<script>
(function () {
	if (window.Joomla && Joomla.submitbutton) {
		var orig = Joomla.submitbutton;
		Joomla.submitbutton = function (task) {
			if (task === 'players.reset' && !window.confirm(<?php echo json_encode(Text::_('COM_HMB_QUIZ_LEAGUE_RESET_CONFIRM')); ?>)) {
				return false;
			}
			return orig.call(Joomla, task);
		};
	}
})();
</script>
