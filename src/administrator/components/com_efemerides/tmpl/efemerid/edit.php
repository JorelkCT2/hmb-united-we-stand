<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>
<form action="<?php echo Route::_('index.php?option=com_efemerides&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

    <div class="row">
        <!-- Columna principal -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">Datos de la efeméride</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label required">Título *</label>
                        <?php echo $this->form->renderField('titulo'); ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Fecha del evento *</label>
                            <?php echo $this->form->renderField('fecha_evento'); ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoría</label>
                            <?php echo $this->form->renderField('categoria_id'); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Artista / Banda</label>
                        <?php echo $this->form->renderField('artista'); ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción breve</label>
                        <?php echo $this->form->renderField('descripcion'); ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Detalle completo</label>
                        <?php echo $this->form->renderField('detalle'); ?>
                    </div>
                </div>
            </div>

            <!-- Campos específicos por categoría -->
            <div class="card mb-3" id="campos-disco">
                <div class="card-header bg-info text-white"><span class="icon-music"></span> Datos del disco (lanzamientos)</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del disco</label>
                        <?php echo $this->form->renderField('nombre_disco'); ?>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="campos-concierto">
                <div class="card-header bg-warning"><span class="icon-location"></span> Datos del concierto</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Lugar del concierto</label>
                            <?php echo $this->form->renderField('lugar_concierto'); ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Asistentes</label>
                            <?php echo $this->form->renderField('asistentes'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Publicación</div>
                <div class="card-body">
                    <?php echo $this->form->renderField('state'); ?>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="task"   value="">
    <input type="hidden" name="jform[id]" value="<?php echo (int)$this->item->id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var catSelect = document.querySelector('[name="jform[categoria_id]"]');
    if (!catSelect) return;

    function toggleFields() {
        var optText = catSelect.options[catSelect.selectedIndex]
                      ? catSelect.options[catSelect.selectedIndex].text.toLowerCase()
                      : '';
        var disco     = document.getElementById('campos-disco');
        var concierto = document.getElementById('campos-concierto');
        if (disco)     disco.style.display     = (optText.indexOf('lanc') !== -1 || optText.indexOf('disco') !== -1) ? '' : 'none';
        if (concierto) concierto.style.display = (optText.indexOf('conci') !== -1) ? '' : 'none';
    }

    catSelect.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
