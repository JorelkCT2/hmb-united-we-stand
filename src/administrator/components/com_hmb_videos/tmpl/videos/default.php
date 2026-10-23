<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="com_hmb_videos">
<input type="hidden" name="view" value="videos">
<div class="row"><div class="col-md-12">
    <!-- Folder filter -->
    <div class="mb-2">
        <select name="filter[folder_id]" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
            <option value=""><?= Text::_('COM_HMB_VIDEOS_FILTER_ALL_FOLDERS') ?></option>
            <?php foreach ($this->folders as $f) : ?>
            <option value="<?= $f->id ?>" <?= ($this->state->get('filter.folder_id') == $f->id) ? 'selected' : '' ?>>
                <?= $this->escape($f->title) ?>
            </option>
            <?php endforeach ?>
        </select>
    </div>
    <table class="table" id="videoList">
        <thead>
            <tr>
                <th class="w-1 text-center"><?= HTMLHelper::_('grid.checkall') ?></th>
                <th><?= Text::_('JGLOBAL_TITLE') ?></th>
                <th class="d-none d-md-table-cell"><?= Text::_('COM_HMB_VIDEOS_VIDEO_BAND') ?></th>
                <th class="d-none d-md-table-cell"><?= Text::_('COM_HMB_VIDEOS_VIDEO_FOLDER') ?></th>
                <th class="d-none d-md-table-cell"><?= Text::_('COM_HMB_VIDEOS_VIDEO_THUMB') ?></th>
                <th class="w-5 text-center"><?= Text::_('COM_HMB_VIDEOS_VIDEO_MOBILE_SHORT') ?></th>
                            <th class="w-5 text-center"><?= Text::_('JSTATUS') ?></th>
                <th class="w-5 text-center"><?= Text::_('JFEATURED') ?></th>
                <th class="w-5 text-center"><?= Text::_('JGRID_HEADING_ID') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
            <tr>
                <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $item->id) ?></td>
                <td>
                    <a href="index.php?option=com_hmb_videos&task=video.edit&id=<?= $item->id ?>">
                        <?= $this->escape($item->title) ?>
                    </a>
                    <div class="small text-muted"><?= $this->escape($item->youtube_id) ?></div>
                </td>
                <td class="d-none d-md-table-cell"><?= $this->escape($item->band ?? '') ?></td>
                <td class="d-none d-md-table-cell"><?= $this->escape($item->folder_title ?? '') ?></td>
                <td class="d-none d-md-table-cell">
                    <img src="https://img.youtube.com/vi/<?= $this->escape($item->youtube_id) ?>/default.jpg"
                         width="80" alt="" loading="lazy">
                </td>
                <td class="text-center">
                    <button type="button"
                        onclick="hmbToggleMobile(<?= $item->id ?>, <?= (int)$item->mobile ?>)"
                        title="<?= $item->mobile ? 'Quitar de móvil' : 'Activar en móvil' ?>"
                        style="background:none;border:none;cursor:pointer;font-size:1.3em;padding:0;line-height:1;">
                        <?= $item->mobile ? '📱' : '📵' ?>
                    </button>
                </td>
                <td class="text-center">
                    <?= HTMLHelper::_('jgrid.published', $item->state, $i, 'videos.', true) ?>
                </td>
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

<!-- Formulario dedicado para toggle móvil (fuera del adminForm) -->
<form id="hmbMobileForm" action="index.php" method="post" style="display:none">
    <input type="hidden" name="option" value="com_hmb_videos">
    <input type="hidden" name="task" value="videos.toggleMobile">
    <input type="hidden" id="hmbMobileId" name="id" value="0">
    <input type="hidden" id="hmbMobileVal" name="mobile_value" value="0">
    <?= HTMLHelper::_('form.token') ?>
</form>
<script>
function hmbToggleMobile(id, current) {
    document.getElementById('hmbMobileId').value = id;
    document.getElementById('hmbMobileVal').value = current ? 0 : 1;
    document.getElementById('hmbMobileForm').submit();
}
</script>

