<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.formvalidator');
?>
<form action="<?= Route::_('index.php?option=com_artistas&layout=edit&id=' . (int)$this->item->id) ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">
<div class="main-card">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">🎵 Datos del género</h5></div>
                <div class="card-body">
                    <div class="control-group mb-3">
                        <div class="control-label"><?= $this->form->getLabel('name') ?></div>
                        <div class="controls"><?= $this->form->getInput('name') ?></div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="control-group mb-3">
                                <div class="control-label"><?= $this->form->getLabel('ordering') ?></div>
                                <div class="controls"><?= $this->form->getInput('ordering') ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="control-group mb-3">
                                <div class="control-label"><?= $this->form->getLabel('state') ?></div>
                                <div class="controls"><?= $this->form->getInput('state') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info small mt-2">
                        💡 El nombre del género se guardará <strong>tal cual</strong> en las fichas de los artistas.
                        Si lo cambias aquí, las fichas existentes conservarán el valor anterior.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="task" value="">
<input type="hidden" name="jform[id]" value="<?= (int)$this->item->id ?>">
<?= HTMLHelper::_('form.token') ?>
</form>
