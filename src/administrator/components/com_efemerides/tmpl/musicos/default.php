<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');
$listOrder=$this->escape($this->state->get('list.ordering','nombre'));
$listDirn=$this->escape($this->state->get('list.direction','ASC'));
$urlSort=static function(string $col) use($listOrder,$listDirn):string{
    $d=($listOrder===$col&&strtoupper($listDirn)==='ASC')?'DESC':'ASC';
    return 'index.php?option=com_efemerides&view=musicos&list%5Bordering%5D='.urlencode($col).'&list%5Bdirection%5D='.$d;
};
$ico=static function(string $col) use($listOrder,$listDirn):string{
    if($listOrder!==$col) return '<span style="color:#444"> ⇅</span>';
    return strtoupper($listDirn)==='ASC'?'<span style="color:#ffc107"> ▲</span>':'<span style="color:#ffc107"> ▼</span>';
};
$csvMusicos='data:text/csv;charset=utf-8,nombre%2Cwikipedia_url%2Cfoto%2Cfecha_nacimiento%2Cfecha_defuncion%2Cbanda%0A%22Ozzy%20Osbourne%22%2C%22https%3A//es.wikipedia.org/wiki/Ozzy_Osbourne%22%2C%22https%3A//upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Ozzy_Osbourne_1991.jpg/220px-Ozzy_Osbourne_1991.jpg%22%2C1948-12-03%2C%2C%22Black%20Sabbath%22%0A%22Bruce%20Dickinson%22%2C%22https%3A//es.wikipedia.org/wiki/Bruce_Dickinson%22%2C%22https%3A//upload.wikimedia.org/wikipedia/commons/thumb/a/a7/Bruce_Dickinson.jpg/220px-Bruce_Dickinson.jpg%22%2C1958-08-07%2C%2C%22Iron%20Maiden%22%0A%22James%20Hetfield%22%2C%22https%3A//es.wikipedia.org/wiki/James_Hetfield%22%2C%22https%3A//upload.wikimedia.org/wikipedia/commons/thumb/d/de/JamesHetfieldPlaying.jpg/220px-JamesHetfieldPlaying.jpg%22%2C1963-08-03%2C%2C%22Metallica%22%0A%22Ronnie%20James%20Dio%22%2C%22https%3A//es.wikipedia.org/wiki/Ronnie_James_Dio%22%2C%22https%3A//upload.wikimedia.org/wikipedia/commons/thumb/4/45/Dio_1978.jpg/220px-Dio_1978.jpg%22%2C1942-07-10%2C2010-05-16%2C%22Black%20Sabbath%22%0A%22Freddie%20Mercury%22%2C%22https%3A//es.wikipedia.org/wiki/Freddie_Mercury%22%2C%22https%3A//upload.wikimedia.org/wikipedia/commons/thumb/0/0b/Freddie_Mercury_performing_in_New_Haven_CT_1977.jpg/220px-Freddie_Mercury_performing_in_New_Haven_CT_1977.jpg%22%2C1946-09-05%2C1991-11-24%2C%22Queen%22%0A';
?>
<div class="container-fluid">
  <div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="index.php?option=com_efemerides&task=musico.add" class="btn btn-success"><span class="icon-plus-2"></span> Nuevo músico</a>
    <button type="button" class="btn btn-warning" id="btn-import-m">&#8593; Importar CSV</button>
    <a href="<?php echo $csvMusicos; ?>" download="musicos_ejemplo.csv" class="btn btn-outline-info">&#8595; CSV de ejemplo</a>
    <a href="index.php?option=com_efemerides&view=bandas" class="btn btn-primary">&#127928; Gestionar bandas</a>
    <a href="index.php?option=com_efemerides&view=cpanel" class="btn btn-secondary">&#8592; Volver</a>
  </div>

  <div id="panel-import-m" style="display:none" class="card bg-dark border-secondary mb-3">
    <div class="card-header border-secondary d-flex justify-content-between">
      <strong class="text-warning">Importar músicos desde CSV</strong>
      <button type="button" class="btn-close btn-close-white btn-sm" id="btn-hide-m"></button>
    </div>
    <div class="card-body">
      <form action="<?php echo Route::_('index.php?option=com_efemerides&task=musicos.importCsv',false); ?>"
            method="post" enctype="multipart/form-data">
        <div class="row g-2 align-items-end">
          <div class="col-md-5"><input type="file" name="csv_musicos" class="form-control" accept=".csv,.txt" required></div>
          <div class="col-md-3"><button type="submit" class="btn btn-success w-100">Importar</button></div>
          <div class="col-md-4"><div class="alert alert-secondary small mb-0 py-2">
            <code>nombre, wikipedia_url, <strong>foto</strong>, fecha_nacimiento, fecha_defuncion, banda</code><br><small class="text-warning">&#128247; <strong>foto</strong>: URL directa (Wikipedia, Wikimedia, etc.) o ruta interna</small><br>
            <small>Si la banda no existe, se crea automáticamente</small>
          </div></div>
        </div>
        <?php echo HTMLHelper::_('form.token'); ?>
      </form>
    </div>
  </div>

  <form action="index.php?option=com_efemerides&view=musicos" method="post" name="adminForm" id="adminForm">
    <div class="row mb-3 g-2">
      <div class="col-md-5">
        <input type="text" name="filter_search" value="<?php echo $this->escape($this->state->get('filter.search','')); ?>"
               class="form-control" placeholder="Buscar nombre o banda...">
      </div>
      <div class="col-md-2">
        <select name="filter_state" class="form-select" onchange="this.form.submit()">
          <option value="">-- Estado --</option>
          <option value="1" <?php echo $this->state->get('filter.state','')==='1'?'selected':''; ?>>Publicado</option>
          <option value="0" <?php echo $this->state->get('filter.state','')==='0'?'selected':''; ?>>No publicado</option>
        </select>
      </div>
      <div class="col-md-2">
        <select name="list[limit]" class="form-select" onchange="this.form.submit()">
          <?php foreach([10,20,50,100] as $l): ?>
          <option value="<?php echo $l; ?>" <?php echo (int)$this->state->get('list.limit',20)===$l?'selected':''; ?>><?php echo $l; ?></option>
          <?php endforeach; ?>
          <option value="0" <?php echo (int)$this->state->get('list.limit',20)===0?'selected':''; ?>>Ver todos</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="index.php?option=com_efemerides&view=musicos" class="btn btn-secondary">Limpiar</a>
      </div>
    </div>
    <div class="mb-2 text-muted small">Total: <strong><?php echo $this->pagination->total; ?></strong> músicos</div>
    <table class="table table-dark table-striped table-hover">
      <thead><tr>
        <th width="1%"><input type="checkbox" name="checkall-toggle" onclick="Joomla.checkAll(this)"></th>
        <th width="4%"><a href="<?php echo $urlSort('id'); ?>" class="text-warning text-decoration-none">ID<?php echo $ico('id'); ?></a></th>
        <th><a href="<?php echo $urlSort('nombre'); ?>" class="text-warning text-decoration-none">Músico<?php echo $ico('nombre'); ?></a></th>
        <th width="18%">Banda principal</th>
        <th width="11%"><a href="<?php echo $urlSort('fecha_nacimiento'); ?>" class="text-warning text-decoration-none">Nacimiento<?php echo $ico('fecha_nacimiento'); ?></a></th>
        <th width="10%">Defunción</th>
        <th width="7%">Wiki</th>
        <th width="7%"><a href="<?php echo $urlSort('state'); ?>" class="text-warning text-decoration-none">Estado<?php echo $ico('state'); ?></a></th>
        <th width="8%">Acciones</th>
      </tr></thead>
      <tbody>
      <?php if(empty($this->items)): ?>
        <tr><td colspan="9" class="text-center py-4 text-muted">No hay músicos. <a href="index.php?option=com_efemerides&task=musico.add" class="text-warning">Crear el primero</a></td></tr>
      <?php else: foreach($this->items as $i=>$item): ?>
        <tr>
          <td><?php echo HTMLHelper::_('grid.id',$i,$item->id); ?></td>
          <td><?php echo $item->id; ?></td>
          <td>
            <?php if(!empty($item->foto)): ?>
            <img src="<?php echo htmlspecialchars($item->foto); ?>" style="width:32px;height:32px;object-fit:cover;border-radius:50%;margin-right:8px" alt="">
            <?php endif; ?>
            <a href="index.php?option=com_efemerides&task=musico.edit&id=<?php echo $item->id; ?>" class="text-warning fw-bold">
              <?php echo $this->escape($item->nombre); ?>
            </a>
          </td>
          <td class="text-muted small">
            <?php $banda=$item->banda_nombre??$item->banda_texto??''; echo $this->escape($banda)?:'—'; ?>
          </td>
          <td class="text-muted small"><?php echo $item->fecha_nacimiento&&$item->fecha_nacimiento!=='0000-00-00'?date('d/m/Y',strtotime($item->fecha_nacimiento)):'—'; ?></td>
          <td class="small">
            <?php if($item->fecha_defuncion&&$item->fecha_defuncion!=='0000-00-00'):?>
            <span class="text-danger"><?php echo date('d/m/Y',strtotime($item->fecha_defuncion)); ?></span>
            <?php else: ?><span class="text-success">Vive</span><?php endif; ?>
          </td>
          <td><?php if(!empty($item->wikipedia_url)): ?><a href="<?php echo htmlspecialchars($item->wikipedia_url); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">W</a><?php else: ?>—<?php endif; ?></td>
          <td><?php echo HTMLHelper::_('jgrid.published',$item->state,$i,'musicos.',true); ?></td>
          <td><a href="index.php?option=com_efemerides&task=musico.edit&id=<?php echo $item->id; ?>" class="btn btn-sm btn-warning">Editar</a></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
    <?php echo $this->pagination->getListFooter(); ?>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="list[ordering]" value="<?php echo $listOrder; ?>">
    <input type="hidden" name="list[direction]" value="<?php echo $listDirn; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
  </form>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
  var p=document.getElementById('panel-import-m');
  var s=document.getElementById('btn-import-m');
  var h=document.getElementById('btn-hide-m');
  if(s) s.addEventListener('click',function(){p.style.display='';p.scrollIntoView({behavior:'smooth'});});
  if(h) h.addEventListener('click',function(){p.style.display='none';});
});
</script>
