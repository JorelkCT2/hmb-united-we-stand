<?php defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('behavior.multiselect');

// Joomla guarda ordenación en list[ordering] y list[direction]
$listOrder = $this->escape($this->state->get('list.ordering', 'id'));
$listDirn  = $this->escape($this->state->get('list.direction', 'DESC'));

// Generar URL para ordenar columna — usa los parámetros que Joomla espera
$urlSort = static function(string $col) use ($listOrder, $listDirn): string {
    $newDir = ($listOrder === $col && strtoupper($listDirn) === 'ASC') ? 'DESC' : 'ASC';
    return 'index.php?option=com_efemerides&view=frases'
         . '&list%5Bordering%5D=' . urlencode($col)
         . '&list%5Bdirection%5D=' . $newDir;
};
$iconSort = static function(string $col) use ($listOrder, $listDirn): string {
    if ($listOrder !== $col) return '<span style="color:#444;font-size:.7rem"> ⇅</span>';
    return strtoupper($listDirn) === 'ASC'
        ? '<span style="color:#ffc107;font-size:.7rem"> ▲</span>'
        : '<span style="color:#ffc107;font-size:.7rem"> ▼</span>';
};

// CSV de ejemplo
$csvEjemplo = "frase,autor,fecha_nacimiento,fecha_defuncion\n"
    . "\"Las guitarras hablan cuando las palabras ya no sirven\",\"Slash\",1965-07-23,\n"
    . "\"No seré una estrella del rock. Seré una leyenda\",\"Freddie Mercury\",1946-09-05,1991-11-24\n"
    . "\"El metal no es un género, es un estilo de vida\",\"James Hetfield\",1963-08-03,\n";
$csvB64 = 'data:text/csv;charset=utf-8,' . rawurlencode($csvEjemplo);
?>
<div class="container-fluid">

  <!-- Botones -->
  <div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="index.php?option=com_efemerides&task=frase.add" class="btn btn-success">
      <span class="icon-plus-2"></span> Nueva frase
    </a>
    <button type="button" class="btn btn-primary" id="btn-show-import">
      <span class="icon-upload"></span> Importar CSV
    </button>
    <a href="<?php echo $csvB64; ?>" download="frases_ejemplo.csv" class="btn btn-outline-info">
      <span class="icon-download"></span> CSV de ejemplo
    </a>
    <a href="index.php?option=com_efemerides&view=cpanel" class="btn btn-secondary">
      <span class="icon-arrow-left"></span> Volver
    </a>
  </div>

  <!-- Panel importar CSV — formulario TOTALMENTE separado del adminForm -->
  <div id="import-panel" style="display:none" class="card bg-dark border-secondary mb-3">
    <div class="card-header border-secondary d-flex justify-content-between align-items-center">
      <strong class="text-warning">Importar frases desde CSV</strong>
      <button type="button" class="btn-close btn-close-white btn-sm" id="btn-hide-import"></button>
    </div>
    <div class="card-body">
      <form id="form-import-csv"
            action="<?php echo Route::_('index.php?option=com_efemerides&task=frases.importCsv',false); ?>"
            method="post" enctype="multipart/form-data">
        <div class="row align-items-end g-3">
          <div class="col-md-5">
            <label class="form-label text-light">Archivo CSV</label>
            <input type="file" name="csv_frases" class="form-control" accept=".csv,.txt" required>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-success w-100">
              <span class="icon-upload"></span> Importar
            </button>
          </div>
          <div class="col-md-4">
            <div class="alert alert-secondary small mb-0 py-2">
              <code>frase, autor, fecha_nacimiento, fecha_defuncion</code><br>
              <small>Fechas opcionales · UTF-8</small>
            </div>
          </div>
        </div>
        <?php echo HTMLHelper::_('form.token'); ?>
      </form>
    </div>
  </div>

  <!-- ADMIN FORM principal -->
  <form action="index.php?option=com_efemerides&view=frases"
        method="post" name="adminForm" id="adminForm">

    <!-- Filtros -->
    <div class="row mb-3 g-2">
      <div class="col-md-4">
        <input type="text" name="filter_search"
               value="<?php echo $this->escape($this->state->get('filter.search','')); ?>"
               class="form-control" placeholder="Buscar frase o autor...">
      </div>
      <div class="col-md-3">
        <select name="filter_autor" class="form-select" onchange="this.form.submit()">
          <option value="">-- Todos los autores --</option>
          <?php foreach ($this->autores as $a): ?>
          <option value="<?php echo $this->escape($a); ?>"
            <?php echo $this->state->get('filter.autor','')===$a?'selected':''; ?>>
            <?php echo $this->escape($a); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="filter_state" class="form-select" onchange="this.form.submit()">
          <option value="">-- Estado --</option>
          <option value="1" <?php echo $this->state->get('filter.state','')==='1'?'selected':''; ?>>Publicado</option>
          <option value="0" <?php echo $this->state->get('filter.state','')==='0'?'selected':''; ?>>No publicado</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="index.php?option=com_efemerides&view=frases" class="btn btn-secondary">Limpiar</a>
      </div>
    </div>

    <!-- Resultados por página con "Ver todos" -->
    <div class="d-flex align-items-center gap-3 mb-2">
      <span class="text-muted small">
        Total: <strong><?php echo $this->pagination->total; ?></strong> frases
      </span>
      <div class="d-flex align-items-center gap-2">
        <label class="form-label mb-0 text-muted small">Mostrar:</label>
        <select name="list[limit]" class="form-select form-select-sm" style="width:auto"
                onchange="this.form.submit()">
          <?php $limit = (int)$this->state->get('list.limit',20); ?>
          <?php foreach ([10,20,50,100] as $lv): ?>
          <option value="<?php echo $lv; ?>" <?php echo $limit===$lv?'selected':''; ?>>
            <?php echo $lv; ?>
          </option>
          <?php endforeach; ?>
          <option value="0" <?php echo $limit===0?'selected':''; ?>>Ver todos</option>
        </select>
      </div>
    </div>

    <table class="table table-dark table-striped table-hover">
      <thead>
        <tr>
          <th width="1%"><input type="checkbox" name="checkall-toggle" onclick="Joomla.checkAll(this)"></th>
          <th width="4%">
            <a href="<?php echo $urlSort('id'); ?>" class="text-warning text-decoration-none">
              ID<?php echo $iconSort('id'); ?>
            </a>
          </th>
          <th>
            <a href="<?php echo $urlSort('frase'); ?>" class="text-warning text-decoration-none">
              Frase<?php echo $iconSort('frase'); ?>
            </a>
          </th>
          <th width="16%">
            <a href="<?php echo $urlSort('autor'); ?>" class="text-warning text-decoration-none">
              Autor<?php echo $iconSort('autor'); ?>
            </a>
          </th>
          <th width="11%">
            <a href="<?php echo $urlSort('fecha_nacimiento'); ?>" class="text-warning text-decoration-none">
              Nacimiento<?php echo $iconSort('fecha_nacimiento'); ?>
            </a>
          </th>
          <th width="11%">
            <a href="<?php echo $urlSort('fecha_defuncion'); ?>" class="text-warning text-decoration-none">
              Defunción<?php echo $iconSort('fecha_defuncion'); ?>
            </a>
          </th>
          <th width="7%">
            <a href="<?php echo $urlSort('state'); ?>" class="text-warning text-decoration-none">
              Estado<?php echo $iconSort('state'); ?>
            </a>
          </th>
          <th width="8%">Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($this->items)): ?>
        <tr>
          <td colspan="8" class="text-center py-4 text-muted">
            No hay frases.
            <a href="index.php?option=com_efemerides&task=frase.add" class="text-warning">Crear la primera</a>
            &nbsp;o&nbsp;
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-show-import2">Importar CSV</button>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($this->items as $i => $item): ?>
        <tr>
          <td><?php echo HTMLHelper::_('grid.id',$i,$item->id); ?></td>
          <td><?php echo $item->id; ?></td>
          <td>
            <a href="index.php?option=com_efemerides&task=frase.edit&id=<?php echo $item->id; ?>"
               class="text-light text-decoration-none">
              <em>"<?php echo $this->escape(mb_substr($item->frase,0,110)); ?><?php echo mb_strlen($item->frase)>110?'…':''; ?>"</em>
            </a>
          </td>
          <td><strong class="text-warning"><?php echo $this->escape($item->autor); ?></strong></td>
          <td class="small text-muted">
            <?php echo ($item->fecha_nacimiento && $item->fecha_nacimiento!=='0000-00-00')
                ? date('d/m/Y',strtotime($item->fecha_nacimiento)) : '—'; ?>
          </td>
          <td class="small">
            <?php echo ($item->fecha_defuncion && $item->fecha_defuncion!=='0000-00-00')
                ? '<span class="text-danger">'.date('d/m/Y',strtotime($item->fecha_defuncion)).'</span>'
                : '<span class="text-success small">Vive</span>'; ?>
          </td>
          <td><?php echo HTMLHelper::_('jgrid.published',$item->state,$i,'frases.',true); ?></td>
          <td>
            <a href="index.php?option=com_efemerides&task=frase.edit&id=<?php echo $item->id; ?>"
               class="btn btn-sm btn-warning">Editar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>

    <?php echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task"         value="">
    <input type="hidden" name="boxchecked"   value="0">
    <input type="hidden" name="list[ordering]"  value="<?php echo $listOrder; ?>">
    <input type="hidden" name="list[direction]" value="<?php echo $listDirn; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var panel = document.getElementById('import-panel');
    var s1 = document.getElementById('btn-show-import');
    var s2 = document.getElementById('btn-show-import2');
    var h  = document.getElementById('btn-hide-import');
    function show(){ panel.style.display=''; panel.scrollIntoView({behavior:'smooth'}); }
    function hide(){ panel.style.display='none'; }
    if (s1) s1.addEventListener('click', show);
    if (s2) s2.addEventListener('click', show);
    if (h)  h.addEventListener('click', hide);
});
</script>
