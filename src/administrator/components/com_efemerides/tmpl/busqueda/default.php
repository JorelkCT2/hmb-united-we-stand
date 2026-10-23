<?php defined('_JEXEC') or die;
use Joomla\CMS\Session\Session;
$q     = htmlspecialchars($this->query);
$base  = 'index.php?option=com_efemerides';
$token = Session::getFormToken();

// Genera formulario de borrado apuntando siempre a busqueda.delete
$del = function(string $tabla, int $id) use ($token, $base, $q): string {
    return '<form method="post" action="' . $base . '&task=busqueda.delete" style="display:inline"'
         . ' onsubmit="return confirm(\'¿Borrar? No se puede deshacer.\')">'
         . '<input type="hidden" name="del_id"    value="' . $id    . '">'
         . '<input type="hidden" name="del_tabla" value="' . htmlspecialchars($tabla) . '">'
         . '<input type="hidden" name="q"         value="' . $q . '">'
         . '<input type="hidden" name="' . $token . '" value="1">'
         . '<button type="submit" class="bqa-del">&#128465; Borrar</button>'
         . '</form>';
};
?>
<style>
.bqa{max-width:1100px}
.bqa-form{display:flex;gap:8px;margin-bottom:24px}
.bqa-input{flex:1;padding:10px 14px;font-size:1rem;border:1px solid #ccc;border-radius:4px}
.bqa-btn{background:#6c4a9e;color:#fff;border:none;padding:10px 22px;border-radius:4px;font-size:.95rem;cursor:pointer;font-weight:600}
.bqa-btn:hover{background:#5a3a8a}
.bqa-summary{color:#666;font-size:.85rem;margin-bottom:20px;padding:8px 14px;background:#f8f8f8;border-radius:4px;border-left:4px solid #6c4a9e}
.bqa-section{margin-bottom:28px}
.bqa-sh{font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;font-weight:700;color:#fff;padding:5px 12px;border-radius:3px;margin-bottom:10px;display:inline-block}
.bqa-sh-ef{background:#c0392b}.bqa-sh-fa{background:#2c3e7a}.bqa-sh-mu{background:#7a5200}.bqa-sh-fr{background:#1a7a4a}
.bqa-table{width:100%;border-collapse:collapse;font-size:.88rem}
.bqa-table th{background:#f0f0f0;padding:7px 10px;text-align:left;font-weight:600;color:#333;border-bottom:2px solid #ddd}
.bqa-table td{padding:8px 10px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
.bqa-table tr:hover td{background:#fafafa}
.bqa-foto{width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid #ddd}
.bqa-nada{color:#999;padding:30px;text-align:center;font-size:.9rem;background:#fafafa;border-radius:4px}
.bqa-link{color:#6c4a9e;text-decoration:none;font-weight:600}
.bqa-link:hover{color:#c0392b}
.bqa-badge{font-size:.7rem;padding:2px 8px;border-radius:10px;background:#eee;color:#666;margin-left:6px}
.bqa-del{background:#c0392b;color:#fff;border:none;padding:4px 12px;border-radius:3px;font-size:.78rem;cursor:pointer;font-weight:600}
.bqa-del:hover{background:#a93226}
.bqa-actions{display:flex;gap:6px;align-items:center}
</style>

<div class="bqa">
  <h1 class="page-title">&#128269; Búsqueda global</h1>

  <form method="get" action="index.php" class="bqa-form">
    <input type="hidden" name="option" value="com_efemerides">
    <input type="hidden" name="view" value="busqueda">
    <input class="bqa-input" type="text" name="q" value="<?php echo $q; ?>"
           placeholder="Buscar por artista, canción, frase... (ej: Michael Jackson)">
    <button class="bqa-btn" type="submit">&#128269; BUSCAR</button>
  </form>

  <?php if ($this->query !== '' && strlen($this->query) < 2): ?>
    <div class="bqa-nada">Escribe al menos 2 caracteres.</div>
  <?php elseif ($this->query !== '' && $this->total === 0): ?>
    <div class="bqa-nada">Sin resultados para <strong>"<?php echo $q; ?>"</strong></div>
  <?php elseif ($this->query !== ''): ?>

    <div class="bqa-summary">
      <strong><?php echo $this->total; ?></strong> resultado<?php echo $this->total !== 1 ? 's' : ''; ?> para
      "<strong><?php echo $q; ?></strong>"
      &nbsp;·&nbsp; <?php echo count($this->musicos); ?> músicos
      &nbsp;·&nbsp; <?php echo count($this->efemerides); ?> efemérides
      &nbsp;·&nbsp; <?php echo count($this->fallecidos); ?> fallecidos
      &nbsp;·&nbsp; <?php echo count($this->frases); ?> frases
    </div>

    <?php if (!empty($this->musicos)): ?>
    <div class="bqa-section">
      <div class="bqa-sh bqa-sh-mu">&#127928; Músicos (<?php echo count($this->musicos); ?>)</div>
      <table class="bqa-table">
        <tr><th>Foto</th><th>Nombre</th><th>Banda</th><th>Instrumento</th><th>Acciones</th></tr>
        <?php foreach ($this->musicos as $m):
          $foto = trim((string)($m->foto ?? ''));
          $vivo = empty($m->fecha_defuncion) || $m->fecha_defuncion === '0000-00-00';
        ?>
        <tr>
          <td><?php if ($foto): ?><img src="<?php echo htmlspecialchars($foto); ?>" class="bqa-foto" alt=""><?php else: ?><span style="font-size:1.4rem">&#127928;</span><?php endif; ?></td>
          <td><strong><?php echo htmlspecialchars($m->nombre); ?></strong><?php if (!$vivo): ?><span class="bqa-badge">✝ <?php echo date('Y',strtotime($m->fecha_defuncion)); ?></span><?php endif; ?></td>
          <td><?php echo htmlspecialchars((string)($m->banda_nombre ?? '')); ?></td>
          <td><?php echo htmlspecialchars((string)($m->instrumento ?? '')); ?></td>
          <td><div class="bqa-actions">
            <a href="<?php echo $base; ?>&task=musico.edit&id=<?php echo (int)$m->id; ?>" class="bqa-link">&#9998; Editar</a>
            <?php echo $del('musicos', (int)$m->id); ?>
          </div></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($this->efemerides)): ?>
    <div class="bqa-section">
      <div class="bqa-sh bqa-sh-ef">&#128197; Efemérides (<?php echo count($this->efemerides); ?>)</div>
      <table class="bqa-table">
        <tr><th>Fecha</th><th>Título</th><th>Artista</th><th>Categoría</th><th>Acciones</th></tr>
        <?php foreach ($this->efemerides as $e): ?>
        <tr>
          <td style="white-space:nowrap;color:#999"><?php echo $e->fecha_evento ? date('d/m/Y', strtotime($e->fecha_evento)) : '—'; ?></td>
          <td><strong><?php echo htmlspecialchars($e->titulo); ?></strong><?php if (!empty($e->descripcion)): ?><br><small style="color:#999"><?php echo htmlspecialchars(mb_substr($e->descripcion,0,80)); ?>...</small><?php endif; ?></td>
          <td><?php echo htmlspecialchars((string)($e->artista ?? '')); ?></td>
          <td><span class="bqa-badge" style="background:#fde;color:#c00"><?php echo htmlspecialchars((string)($e->categoria ?? '')); ?></span></td>
          <td><div class="bqa-actions">
            <a href="<?php echo $base; ?>&task=efemerid.edit&id=<?php echo (int)$e->id; ?>" class="bqa-link">&#9998; Editar</a>
            <?php echo $del('efemerides', (int)$e->id); ?>
          </div></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($this->fallecidos)): ?>
    <div class="bqa-section">
      <div class="bqa-sh bqa-sh-fa">&#10013; Fallecidos (<?php echo count($this->fallecidos); ?>)</div>
      <table class="bqa-table">
        <tr><th>Foto</th><th>Artista</th><th>Fallecimiento</th><th>Acciones</th></tr>
        <?php foreach ($this->fallecidos as $f): $foto = trim((string)($f->foto ?? '')); ?>
        <tr>
          <td><?php if ($foto): ?><img src="<?php echo htmlspecialchars($foto); ?>" class="bqa-foto" alt=""><?php else: ?>—<?php endif; ?></td>
          <td><strong><?php echo htmlspecialchars($f->artista); ?></strong></td>
          <td><?php echo !empty($f->fecha_fallecimiento) ? date('d/m/Y', strtotime($f->fecha_fallecimiento)) : '—'; ?></td>
          <td><div class="bqa-actions">
            <a href="<?php echo $base; ?>&task=fallecido.edit&id=<?php echo (int)$f->id; ?>" class="bqa-link">&#9998; Editar</a>
            <?php echo $del('fallecidos', (int)$f->id); ?>
          </div></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($this->frases)): ?>
    <div class="bqa-section">
      <div class="bqa-sh bqa-sh-fr">&#8220; Frases (<?php echo count($this->frases); ?>)</div>
      <table class="bqa-table">
        <tr><th>Frase</th><th>Autor</th><th>Acciones</th></tr>
        <?php foreach ($this->frases as $f): ?>
        <tr>
          <td style="font-style:italic;color:#444">"<?php echo htmlspecialchars(mb_substr($f->frase,0,120)); ?><?php echo mb_strlen($f->frase)>120?'...':''; ?>"</td>
          <td><strong><?php echo htmlspecialchars($f->autor); ?></strong></td>
          <td><div class="bqa-actions">
            <a href="<?php echo $base; ?>&task=frase.edit&id=<?php echo (int)$f->id; ?>" class="bqa-link">&#9998; Editar</a>
            <?php echo $del('frases', (int)$f->id); ?>
          </div></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>

  <?php endif; ?>
</div>
