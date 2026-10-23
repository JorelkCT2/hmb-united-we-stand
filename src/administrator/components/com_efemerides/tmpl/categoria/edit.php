<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.formvalidator');
?>
<form action="<?php echo Route::_('index.php?option=com_efemerides&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="card">
        <div class="card-header">Categoría de efeméride</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label required">Nombre *</label>
                <?php echo $this->form->renderField('name'); ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Orden</label>
                <?php echo $this->form->renderField('ordering'); ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Estado</label>
                <?php echo $this->form->renderField('state'); ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="jform[id]" value="<?php echo (int)$this->item->id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
