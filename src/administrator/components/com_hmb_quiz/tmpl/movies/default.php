<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Hmb\Component\Quiz\Administrator\View\Movies\HtmlView $this */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$hmbDoc = \Joomla\CMS\Factory::getApplication()->getDocument();
$hmbJs  = JPATH_ROOT . '/media/com_hmb_quiz/js/admin-video.js';
$hmbVer = is_file($hmbJs) ? filemtime($hmbJs) : '1';
$hmbDoc->addScript(\Joomla\CMS\Uri\Uri::root(true) . '/media/com_hmb_quiz/js/admin-video.js?v=' . $hmbVer, [], ['defer' => true]);
$hmbDoc->addScriptOptions('com_hmb_quiz.vid', [
	'base' => \Joomla\CMS\Uri\Uri::root(true) . '/administrator/index.php?option=com_hmb_quiz&task=song.checkVideo&format=json',
]);
?>
<form action="<?php echo Route::_('index.php?option=com_hmb_quiz&view=movies'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">

				<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

				<?php if (!empty($this->items)) : ?>
					<div class="mb-2">
						<button type="button" id="hmbqCheckAll" class="btn btn-sm btn-outline-primary">
							<span class="icon-youtube" aria-hidden="true"></span>
							<?php echo Text::_('COM_HMB_QUIZ_VID_CHECKALL'); ?>
						</button>
					</div>
				<?php endif; ?>

				<?php if (empty($this->items)) : ?>
					<?php if (!empty($this->activeFilters)) : ?>
						<div class="alert alert-warning">
							<span class="icon-search" aria-hidden="true"></span>
							<?php echo Text::_('COM_HMB_QUIZ_NO_RESULTS'); ?>
						</div>
					<?php else : ?>
						<div class="alert alert-info">
							<span class="icon-info-circle" aria-hidden="true"></span>
							<?php echo Text::_('COM_HMB_QUIZ_NO_MOVIES_YET'); ?>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<table class="table table-striped" id="movieList">
						<thead>
							<tr>
								<td class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></td>
								<th class="w-5 text-center"><?php echo Text::_('JSTATUS'); ?></th>
								<th><?php echo HTMLHelper::_('searchtools.sort', 'COM_HMB_QUIZ_MOVIE', 'a.movie', $listDirn, $listOrder); ?></th>
								<th><?php echo HTMLHelper::_('searchtools.sort', 'COM_HMB_QUIZ_VIDEO_TITLE', 'a.title', $listDirn, $listOrder); ?></th>
								<th><?php echo HTMLHelper::_('searchtools.sort', 'COM_HMB_QUIZ_BAND', 'a.band', $listDirn, $listOrder); ?></th>
								<th class="d-none d-md-table-cell"><?php echo HTMLHelper::_('searchtools.sort', 'COM_HMB_QUIZ_DIRECTOR', 'a.director', $listDirn, $listOrder); ?></th>
								<th class="text-center d-none d-md-table-cell"><?php echo HTMLHelper::_('searchtools.sort', 'COM_HMB_QUIZ_MOVIE_YEAR', 'a.movie_year', $listDirn, $listOrder); ?></th>
								<th class="text-center"><?php echo Text::_('COM_HMB_QUIZ_COVER_COL'); ?></th>
								<th class="text-center"><?php echo HTMLHelper::_('searchtools.sort', 'COM_HMB_QUIZ_DIFFICULTY', 'a.difficulty', $listDirn, $listOrder); ?></th>
								<th class="text-center d-none d-md-table-cell"><?php echo HTMLHelper::_('searchtools.sort', 'COM_HMB_QUIZ_CREATED', 'a.created', $listDirn, $listOrder); ?></th>
								<th class="d-none d-lg-table-cell"><?php echo Text::_('COM_HMB_QUIZ_YOUTUBE_ID'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<tr>
								<td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
								<td class="text-center">
									<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'movies.', true, 'cb'); ?>
								</td>
								<td>
									<a href="<?php echo Route::_('index.php?option=com_hmb_quiz&task=song.edit&id=' . (int) $item->id); ?>">
										<strong><?php echo $this->escape($item->movie); ?></strong>
									</a>
								</td>
								<td><?php echo $this->escape($item->title); ?></td>
								<td><?php echo $this->escape($item->band); ?></td>
								<td class="d-none d-md-table-cell"><?php echo $this->escape($item->director); ?></td>
								<td class="text-center d-none d-md-table-cell"><?php echo $this->escape($item->movie_year); ?></td>
								<td class="text-center">
									<?php if (!empty($item->cover_url)) : ?>
										<span class="icon-check text-success" aria-hidden="true" title="<?php echo Text::_('COM_HMB_QUIZ_COVER_YES'); ?>"></span>
										<span class="visually-hidden"><?php echo Text::_('COM_HMB_QUIZ_COVER_YES'); ?></span>
									<?php else : ?>
										<span class="text-muted" title="<?php echo Text::_('COM_HMB_QUIZ_COVER_NO'); ?>">&mdash;</span>
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php
									$lvls = [
										1 => 'COM_HMB_QUIZ_LEVEL_EASY',
										2 => 'COM_HMB_QUIZ_LEVEL_MEDIUM',
										3 => 'COM_HMB_QUIZ_LEVEL_HARD',
									];
									$lv = (int) ($item->difficulty ?? 1);
									echo Text::_($lvls[$lv] ?? $lvls[1]);
									?>
								</td>
								<td class="text-center d-none d-md-table-cell"><?php echo !empty($item->created) ? HTMLHelper::_('date', $item->created, 'Y-m-d') : '<span class="text-muted">—</span>'; ?></td>
								<td class="d-none d-lg-table-cell"><span class="hmbq-vid-dot" data-yt="<?php echo $this->escape($item->youtube_id); ?>" title="" style="margin-right:.4em;color:#888">·</span><code><?php echo $this->escape($item->youtube_id); ?></code></td>
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
