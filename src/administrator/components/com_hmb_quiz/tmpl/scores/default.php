<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Hmb\Component\Quiz\Administrator\View\Scores\HtmlView $this */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_hmb_quiz&view=scores'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span>
						<?php echo Text::_('COM_HMB_QUIZ_NO_SCORES'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="scoreList">
						<caption class="visually-hidden"><?php echo Text::_('COM_HMB_QUIZ_SCORES'); ?></caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" class="w-1 text-center">#</th>
								<th scope="col"><?php echo Text::_('COM_HMB_QUIZ_PLAYER'); ?></th>
								<th scope="col" class="text-center"><?php echo Text::_('COM_HMB_QUIZ_SCORE'); ?></th>
								<th scope="col" class="text-center"><?php echo Text::_('COM_HMB_QUIZ_CORRECT'); ?></th>
								<th scope="col" class="text-center"><?php echo Text::_('COM_HMB_QUIZ_ROUNDS'); ?></th>
								<th scope="col"><?php echo Text::_('JDATE'); ?></th>
								<th scope="col" class="w-5 d-none d-md-table-cell"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="text-center"><?php echo $i + 1 + $this->pagination->limitstart; ?></td>
								<td>
									<?php echo $this->escape($item->player_name); ?>
									<?php if (!empty($item->user_id)) : ?>
										<span class="badge bg-secondary" title="<?php echo Text::_('COM_HMB_QUIZ_REGISTERED'); ?>">
											<span class="icon-user" aria-hidden="true"></span>
										</span>
									<?php endif; ?>
								</td>
								<td class="text-center"><strong><?php echo (int) $item->score; ?></strong></td>
								<td class="text-center"><?php echo (int) $item->correct; ?></td>
								<td class="text-center"><?php echo (int) $item->rounds; ?></td>
								<td><?php echo $item->created ? HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC5')) : '-'; ?></td>
								<td class="d-none d-md-table-cell"><?php echo (int) $item->id; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>">
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
