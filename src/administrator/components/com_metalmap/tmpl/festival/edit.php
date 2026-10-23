<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>
<form action="<?= Route::_('index.php?option=com_metalmap&view=festival&id=' . (int) $this->item->id); ?>"
      method="post" enctype="multipart/form-data" name="adminForm" id="festival-form" class="form-validate">

    <div class="main-card">
        <?= HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_METALMAP_TAB_GENERAL')); ?>
        <div class="row">
            <div class="col-lg-8">
                <?= $this->form->renderField('name'); ?>
                <?= $this->form->renderField('alias'); ?>
                <?= $this->form->renderField('event_type'); ?>
                <?= $this->form->renderField('description'); ?>
                <?= $this->form->renderField('bands'); ?>
            </div>
            <div class="col-lg-4">
                <?= $this->form->renderField('state'); ?>
                <?= $this->form->renderField('start_date'); ?>
                <?= $this->form->renderField('end_date'); ?>
                <?= $this->form->renderField('website'); ?>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab'); ?>

        <?= HTMLHelper::_('uitab.addTab', 'myTab', 'location', Text::_('COM_METALMAP_TAB_LOCATION')); ?>
        <div class="row">
            <div class="col-lg-6">
                <?= $this->form->renderField('city'); ?>
                <?= $this->form->renderField('country'); ?>
                <?= $this->form->renderField('country_code'); ?>
            </div>
            <div class="col-lg-6">
                <?= $this->form->renderField('latitude'); ?>
                <?= $this->form->renderField('longitude'); ?>
                <div class="alert alert-info mt-3">
                    <strong>💡 Consejo:</strong> Las coordenadas se pueden obtener en Google Maps haciendo click derecho sobre el lugar y eligiendo las primeras opciones (lat, lng).
                </div>
            </div>
        </div>
        <?= HTMLHelper::_('uitab.endTab'); ?>

        <?= HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <?= HTMLHelper::_('form.token'); ?>
</form>
