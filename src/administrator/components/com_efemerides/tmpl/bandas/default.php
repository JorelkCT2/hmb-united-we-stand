<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');
$listOrder=$this->escape($this->state->get('list.ordering','nombre'));
$listDirn=$this->escape($this->state->get('list.direction','ASC'));
$urlSort=static function(string $col) use($listOrder,$listDirn):string{
    $d=($listOrder===$col&&strtoupper($listDirn)==='ASC')?'DESC':'ASC';
    return 'index.php?option=com_efemerides&view=bandas&list%5Bordering%5D='.urlencode($col).'&list%5Bdirection%5D='.$d;
};
$ico=static function(string $col) use($listOrder,$listDirn):string{
    if($listOrder!==$col) return '<span style="color:#444"> ⇅</span>';
    return strtoupper($listDirn)==='ASC'?'<span style="color:#0d6efd"> ▲</span>':'<span style="color:#0d6efd"> ▼</span>';
};
$csvBandas='data:text/csv;charset=utf-8,nombre%2Cwikipedia_url%2Cfoto%2Cfecha_formacion%2Cfecha_disolucion%2Cpais%2Cgenero%2Cdescripcion%0A%22Black%20Sabbath%22%2C%22https%3A//es.wikipedia.org/wiki/Black_Sabbath%22%2C%22https%3A//upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Black_Sabbath_-_1970.jpg/220px-Black_Sabbath_-_1970.jpg%22%2C1968%2C%2C%22Reino%20Unido%22%2C%22Heavy%20Metal%22%2C%22Pioneros%20del%20heavy%20metal%22%0A%22Iron%20Maiden%22%2C%22https%3A//es.wikipedia.org/wiki/Iron_Maiden%22%2C%22https%3A//upload.wikimedia.org/wikipedia/commons/thumb/a/a3/Iron_Maiden_logo.png/220px-Iron_Maiden_logo.png%22%2C1975%2C%2C%22Reino%20Unido%22%2C%22Heavy%20Metal%22%2C%22%22%0A%22Metallica%22%2C%22https%3A//es.wikipedia.org/wiki/Metallica%22%2C%22https%3A//upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Metallica_Logo.svg/220px-Metallica_Logo.svg.png%22%2C1981%2C%2C%22Estados%20Unidos%22%2C%22Thrash%20Metal%22%2C%22%22%0A%22Led%20Zeppelin%22%2C%22https%3A//es.wikipedia.org/wiki/Led_Zeppelin%22%2C%22https%3A//upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Led_Zeppelin_logo.svg/220px-Led_Zeppelin_logo.svg.png%22%2C1968%2C1980%2C%22Reino%20Unido%22%2C%22Hard%20Rock%22%2C%22%22%0A%22AC/DC%22%2C%22https%3A//es.wikipedia.org/wiki/AC/DC%22%2C%22https%3A//upload.wikimedia.org/wikipedia/commons/thumb/3/38/AC_DC_logo.svg/220px-AC_DC_logo.svg.png%22%2C1973%2C%2C%22Australia%22%2C%22Hard%20Rock%22%2C%22%22%0A';
?>
<div class="container-fluid">
  <div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="index.php?option=com_efemerides&task=banda.add" class="btn btn-success"><span class="icon-plus-2"></span> Nueva banda</a>
    <button type="button" class="btn btn-primary" id="btn-import-b">&#8593; Importar CSV</button>
    <a href="<?php echo $csvBandas; ?>" download="bandas_ejemplo.csv" class="btn btn-outline-info">&#8595; CSV de ejemplo</a>
    <a href="index.php?option=com_efemerides&view=cpanel" class="btn btn-secondary">&#8592; Volver</a>
  </div>

  <div id="panel-import-b" style="display:none" class="card bg-dark border-secondary mb-3">
    <div class="card-header border-secondary d-flex justify-content-between">
      <strong class="text-primary">Importar bandas desde CSV</strong>
      <button type="button" class="btn-close btn-close-white btn-sm" id="btn-hide-b"></button>
    </div>
    <div class="card-body">
      <form action="<?php echo Route::_('index.php?option=com_efemerides&task=bandas.importCsv',false); ?>"
            method="post" enctype="multipart/form-data">
        <div class="row g-2 align-items-end">
          <div class="col-md-5"><input type="file" name="csv_bandas" class="form-control" accept=".csv,.txt" required></div>
          <div class="col-md-3"><button type="submit" class="btn btn-success w-100">Importar</button></div>
          <div class="col-md-4"><div class="alert alert-secondary small mb-0 py-2">
            <code>nombre, wikipedia_url, <strong>foto</strong>, fecha_formacion, fecha_disolucion, pais, genero</code><br><small class="text-warning">&#128247; <strong>foto</strong>: URL directa de imagen (Wikipedia, Discogs, etc.) o ruta interna</small>
          </div></div>
        </div>
        <?php echo HTMLHelper::_('form.token'); ?>
      </form>
    </div>
  </div>

  <form action="index.php?option=com_efemerides&view=bandas" method="post" name="adminForm" id="adminForm">
    <div class="row mb-3 g-2">
      <div class="col-md-5">
        <input type="text" name="filter_search" value="<?php echo $this->escape($this->state->get('filter.search','')); ?>"
               class="form-control" placeholder="Buscar nombre, país, género...">
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
        <a href="index.php?option=com_efemerides&view=bandas" class="btn btn-secondary">Limpiar</a>
      </div>
    </div>
    <div class="mb-2 text-muted small">Total: <strong><?php echo $this->pagination->total; ?></strong> bandas</div>
    <table class="table table-dark table-striped table-hover">
      <thead><tr>
        <th width="1%"><input type="checkbox" name="checkall-toggle" onclick="Joomla.checkAll(this)"></th>
        <th width="4%"><a href="<?php echo $urlSort('id'); ?>" class="text-primary text-decoration-none">ID<?php echo $ico('id'); ?></a></th>
        <th><a href="<?php echo $urlSort('nombre'); ?>" class="text-primary text-decoration-none">Nombre<?php echo $ico('nombre'); ?></a></th>
        <th width="12%"><a href="<?php echo $urlSort('pais'); ?>" class="text-primary text-decoration-none">País<?php echo $ico('pais'); ?></a></th>
        <th width="15%">Género</th>
        <th width="10%"><a href="<?php echo $urlSort('fecha_formacion'); ?>" class="text-primary text-decoration-none">Formación<?php echo $ico('fecha_formacion'); ?></a></th>
        <th width="8%">Wiki</th>
        <th width="7%"><a href="<?php echo $urlSort('state'); ?>" class="text-primary text-decoration-none">Estado<?php echo $ico('state'); ?></a></th>
        <th width="8%">Acciones</th>
      </tr></thead>
      <tbody>
      <?php if(empty($this->items)): ?>
        <tr><td colspan="9" class="text-center py-4 text-muted">No hay bandas. <a href="index.php?option=com_efemerides&task=banda.add" class="text-primary">Crear la primera</a></td></tr>
      <?php else: foreach($this->items as $i=>$item): ?>
        <tr>
          <td><?php echo HTMLHelper::_('grid.id',$i,$item->id); ?></td>
          <td><?php echo $item->id; ?></td>
          <td>
            <?php if(!empty($item->foto)): ?>
            <img src="<?php echo htmlspecialchars($item->foto); ?>" style="width:32px;height:32px;object-fit:cover;border-radius:4px;margin-right:8px" alt="">
            <?php endif; ?>
            <a href="index.php?option=com_efemerides&task=banda.edit&id=<?php echo $item->id; ?>" class="text-light fw-bold">
              <?php echo $this->escape($item->nombre); ?>
            </a>
          </td>
          <td class="text-muted"><?php echo $this->escape($item->pais??'—'); ?></td>
          <td class="text-muted small"><?php echo $this->escape($item->genero??'—'); ?></td>
          <td class="text-muted small"><?php echo $item->fecha_formacion&&$item->fecha_formacion!=='0000-00-00'?substr($item->fecha_formacion,0,4):'—'; ?></td>
          <td><?php if(!empty($item->wikipedia_url)): ?><a href="<?php echo htmlspecialchars($item->wikipedia_url); ?>" target="_blank" class="btn btn-xs btn-outline-secondary btn-sm">W</a><?php else: ?>—<?php endif; ?></td>
          <td><?php echo HTMLHelper::_('jgrid.published',$item->state,$i,'bandas.',true); ?></td>
          <td><a href="index.php?option=com_efemerides&task=banda.edit&id=<?php echo $item->id; ?>" class="btn btn-sm btn-primary">Editar</a></td>
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
  var p=document.getElementById('panel-import-b');
  var s=document.getElementById('btn-import-b');
  var h=document.getElementById('btn-hide-b');
  if(s) s.addEventListener('click',function(){p.style.display='';p.scrollIntoView({behavior:'smooth'});});
  if(h) h.addEventListener('click',function(){p.style.display='none';});
});
</script>
