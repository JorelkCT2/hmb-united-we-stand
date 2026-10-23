<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="com_hmb_videos">
<input type="hidden" name="view" value="folders">
<div class="row"><div class="col-md-12">
<table class="table" id="folderList">
    <thead>
        <tr>
            <th class="w-1 text-center"><?= HTMLHelper::_('grid.checkall') ?></th>
            <th><?= Text::_('JGLOBAL_TITLE') ?></th>
            <th class="d-none d-md-table-cell"><?= Text::_('COM_HMB_VIDEOS_FOLDER_PARENT') ?></th>
            <th class="w-5 text-center"><?= Text::_('JSTATUS') ?></th>
            <th class="w-5 text-center"><?= Text::_('JGRID_HEADING_ID') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
        <tr>
            <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $item->id) ?></td>
            <td>
                <a href="index.php?option=com_hmb_videos&view=folder&layout=edit&id=<?= $item->id ?>">
                    <?= $this->escape($item->title) ?>
                </a>
            </td>
            <td class="d-none d-md-table-cell"><?= $this->escape($item->parent_title ?? '—') ?></td>
            <td class="text-center"><?= HTMLHelper::_('jgrid.published', $item->state, $i, 'folders.', true) ?></td>
            <td class="text-center"><?= $item->id ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?= $this->pagination->getListFooter() ?>
</div></div>
<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?= HTMLHelper::_('form.token') ?>
</form>
