<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?= Route::_('index.php?option=com_hmgallery'); ?>" method="post" name="adminForm" id="adminForm">
<table class="table">
<thead><tr>
  <th class="w-1 text-center"><?= HTMLHelper::_('grid.checkall'); ?></th>
  <th><?= HTMLHelper::_('searchtools.sort', 'Title', 'a.title', $listDirn, $listOrder); ?></th>
  <th>Folder</th>
  <th><?= HTMLHelper::_('searchtools.sort', 'Status', 'a.state', $listDirn, $listOrder); ?></th>
  <th>ID</th>
</tr></thead>
<tbody>
<?php foreach ($this->items as $i => $item): ?>
<tr>
  <td class="text-center"><?= HTMLHelper::_('grid.id', $i, $item->id); ?></td>
  <td><a href="<?= Route::_('index.php?option=com_hmgallery&task=album.edit&id=' . $item->id); ?>"><?= $this->escape($item->title); ?></a></td>
  <td><?= $this->escape($item->folder_path); ?></td>
  <td><?= HTMLHelper::_('jgrid.published', $item->state, $i, 'albums.', true, 'cb'); ?></td>
  <td><?= $item->id; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?= $this->pagination->getListFooter(); ?>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="boxchecked" value="0"/>
<?= HTMLHelper::_('form.token'); ?>
</form>
