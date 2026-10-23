<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.formvalidator');
?>
<form action="<?= Route::_('index.php?option=com_hmgallery&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">
  <div class="row">
    <div class="col-lg-9">
      <div class="card"><div class="card-body">
        <?= $this->form->renderField('title'); ?>
        <?= $this->form->renderField('alias'); ?>
        <?= $this->form->renderField('folder_path'); ?>
        <?= $this->form->renderField('cover_image'); ?>
        <?= $this->form->renderField('description'); ?>
      </div></div>
    </div>
    <div class="col-lg-3">
      <div class="card"><div class="card-body">
        <?= $this->form->renderField('state'); ?>
        <?= $this->form->renderField('parent_id'); ?>
      </div></div>
    </div>
  </div>
  <input type="hidden" name="task" value=""/>
  <?= HTMLHelper::_('form.token'); ?>
</form>
