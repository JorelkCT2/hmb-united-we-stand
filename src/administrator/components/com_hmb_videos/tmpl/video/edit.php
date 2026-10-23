<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.formvalidator');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
<input type="hidden" name="option" value="com_hmb_videos">
<input type="hidden" name="view" value="video">
<input type="hidden" name="id" value="<?= (int)$this->item->id ?>">
<input type="hidden" name="jform[id]" value="<?= (int)$this->item->id ?>">
<div class="row">
    <div class="col-lg-9">
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title"><?= Text::_('COM_HMB_VIDEOS_VIDEO_DETAILS') ?></h3></div>
            <div class="card-body">
                <?= $this->form->renderField('title') ?>
                <?= $this->form->renderField('alias') ?>
                <?= $this->form->renderField('youtube_id') ?>
                <?php if (!empty($this->item->youtube_id)) : ?>
                <div class="mb-3">
                    <label class="form-label"><?= Text::_('COM_HMB_VIDEOS_VIDEO_PREVIEW') ?></label><br>
                    <img src="https://img.youtube.com/vi/<?= htmlspecialchars($this->item->youtube_id) ?>/hqdefault.jpg"
                         style="max-width:320px;border:2px solid #c00;" alt="Thumbnail" loading="lazy">
                </div>
                <?php endif ?>
                <?= $this->form->renderField('description') ?>
                <?= $this->form->renderField('band') ?>
                <?= $this->form->renderField('year') ?>
                <?= $this->form->renderField('tags') ?>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card mb-3"><div class="card-body">
            <?= $this->form->renderField('folder_id') ?>
            <?= $this->form->renderField('state') ?>
            <?= $this->form->renderField('mobile') ?>
            <?= $this->form->renderField('featured') ?>
            <?= $this->form->renderField('access') ?>
            <?= $this->form->renderField('ordering') ?>
        </div></div>
    </div>
</div>
<input type="hidden" name="task" value="">
<?= HTMLHelper::_('form.token') ?>
</form>
