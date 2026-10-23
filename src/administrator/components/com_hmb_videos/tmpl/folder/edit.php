<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.formvalidator');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
<input type="hidden" name="option" value="com_hmb_videos">
<input type="hidden" name="view" value="folder">
<input type="hidden" name="id" value="<?= (int)$this->item->id ?>">
<div class="row">
    <div class="col-lg-9">
        <div class="card mb-3"><div class="card-body">
            <?= $this->form->renderField('title') ?>
            <?= $this->form->renderField('alias') ?>
            <?= $this->form->renderField('parent_id') ?>
            <?= $this->form->renderField('description') ?>
            <?= $this->form->renderField('cover_image') ?>
        </div></div>
    </div>
    <div class="col-lg-3">
        <div class="card mb-3"><div class="card-body">
            <?= $this->form->renderField('state') ?>
            <?= $this->form->renderField('access') ?>
            <?= $this->form->renderField('ordering') ?>
        </div></div>
    </div>
</div>
<input type="hidden" name="jform[id]" value="<?= (int)$this->item->id ?>">
<input type="hidden" name="task" value="">
<?= HTMLHelper::_('form.token') ?>
</form>
