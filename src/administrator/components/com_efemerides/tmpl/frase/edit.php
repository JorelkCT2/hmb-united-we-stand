<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.formvalidator');
?>
<form action="<?php echo Route::_('index.php?option=com_efemerides&task=frase.save'); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

  <div class="row">
    <div class="col-md-8">

      <div class="card mb-3">
        <div class="card-header" style="color:#cc9900;font-weight:700">&#8220; Frase mítica</div>
        <div class="card-body">

          <div class="mb-3">
            <label class="form-label fw-bold"><?php echo $this->form->getLabel('autor'); ?></label>
            <?php echo $this->form->renderField('autor'); ?>
            <small class="text-muted">Selecciona el músico — los datos se rellenan automáticamente.</small>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold"><?php echo $this->form->getLabel('frase'); ?></label>
            <?php echo $this->form->renderField('frase'); ?>
          </div>

        </div>
      </div>

    </div>
    <div class="col-md-4">

      <div class="card mb-3">
        <div class="card-header" style="font-weight:700">Publicación</div>
        <div class="card-body">
          <label class="form-label fw-bold"><?php echo $this->form->getLabel('state'); ?></label>
          <?php echo $this->form->renderField('state'); ?>
        </div>
      </div>

      <?php
      // Preview si hay frase guardada
      $fraseTexto = $this->item->frase ?? '';
      $autorNombre = $this->item->autor ?? '';
      if ($fraseTexto): ?>
      <div class="card mb-3" style="background:#0d0d0d;border:1px solid #2a2a2a">
        <div class="card-header" style="color:#cc0000;font-size:.8rem;font-weight:700">Preview frontend</div>
        <div class="card-body" style="color:#ccc;font-style:italic;font-size:.9rem;line-height:1.6">
          "<?php echo htmlspecialchars($fraseTexto); ?>"
          <?php if ($autorNombre): ?>
          <div style="color:#cc9900;font-weight:700;margin-top:8px;font-style:normal">
            — <?php echo mb_strtoupper(htmlspecialchars($autorNombre)); ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <?php echo $this->form->renderField('id'); ?>
  <?php echo $this->form->renderField('musico_id'); ?>
  <?php echo $this->form->renderField('fecha_nacimiento'); ?>
  <?php echo $this->form->renderField('fecha_defuncion'); ?>
  <input type="hidden" name="task" value="">
  <?php echo HTMLHelper::_('form.token'); ?>
</form>
