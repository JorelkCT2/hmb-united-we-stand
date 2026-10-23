<?php defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

$yearFilt = (int)Factory::getApplication()->getInput()->getInt('year', 0);
$cur      = Uri::current();
$siteRoot = rtrim(Uri::root(), '/');
?>

<div class="hmb-memorial">

  <!-- HERO -->
  <div class="hmb-mem-hero">
    <div class="hmb-mem-hero-inner">
      <div class="hmb-mem-hero-cruz">&#10013;</div>
      <div>
        <h1 class="hmb-mem-hero-h1">Memorial <span>Rock &amp; Metal</span></h1>
        <p class="hmb-mem-hero-sub">IN MEMORIAM &middot; RECORDAMOS A LOS QUE YA NO EST&Aacute;N</p>
      </div>
    </div>
  </div>

  <!-- NAV AÑOS -->
  <?php if (!empty($this->aniosDisponibles)): ?>
  <nav class="hmb-mem-nav">
    <a href="<?php echo htmlspecialchars($cur); ?>"
       class="hmb-mem-nav-btn<?php echo !$yearFilt ? ' hmb-mem-nav-on' : ''; ?>">Todos</a>
    <?php foreach ($this->aniosDisponibles as $anio): ?>
    <a href="<?php echo htmlspecialchars($cur); ?>?year=<?php echo (int)$anio; ?>"
       class="hmb-mem-nav-btn<?php echo $yearFilt === (int)$anio ? ' hmb-mem-nav-on' : ''; ?>">
      <?php echo (int)$anio; ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <?php endif; ?>

  <!-- CONTENIDO -->
  <?php if (empty($this->fallecidosPorAnio)): ?>
  <div class="hmb-mem-vacio">
    <span>&#10013;</span>
    <p>No hay artistas en el memorial todav&iacute;a.</p>
  </div>
  <?php else: ?>

  <div class="hmb-mem-columnas">
    <?php foreach ($this->fallecidosPorAnio as $anio => $artistas): ?>
    <div class="hmb-mem-col">

      <!-- Cabecera del año -->
      <div class="hmb-mem-col-header">
        <span class="hmb-mem-col-anio"><?php echo htmlspecialchars((string)$anio); ?></span>
        <span class="hmb-mem-col-count"><?php echo count($artistas); ?></span>
      </div>

      <ul class="hmb-mem-lista" role="list">
        <?php foreach ($artistas as $f):
          $foto = trim((string)($f->foto ?? ''));
          if ($foto && strpos($foto,'http') !== 0) $foto = $siteRoot.'/'.ltrim($foto,'/');
          $fechaFmt = !empty($f->fecha_fallecimiento) ? date('d/m/Y', strtotime((string)$f->fecha_fallecimiento)) : '';
          $banda    = trim((string)($f->banda_nombre ?? ''));
          $hasFicha = !empty($f->musico_id);
          $fichaUrl = $hasFicha ? (Uri::current().'?view=musico&id='.(int)$f->musico_id) : '';
        ?>
        <?php if ($hasFicha): ?>
        <li class="hmb-mem-artista hmb-mem-artista--link" role="listitem">
          <a href="<?php echo htmlspecialchars($fichaUrl); ?>" class="hmb-mem-artista-lnk">
        <?php else: ?>
        <li class="hmb-mem-artista" role="listitem">
          <div class="hmb-mem-artista-lnk">
        <?php endif; ?>

            <!-- Foto -->
            <div class="hmb-mem-foto">
              <?php if ($foto): ?>
              <img src="<?php echo htmlspecialchars($foto); ?>"
                   alt="<?php echo htmlspecialchars((string)$f->artista); ?>"
                   loading="lazy"
                   onerror="this.parentElement.innerHTML='<span class=hmb-mem-foto-ph>&#10013;</span>'">
              <?php else: ?>
              <span class="hmb-mem-foto-ph">&#10013;</span>
              <?php endif; ?>
            </div>

            <!-- Datos -->
            <div class="hmb-mem-datos">
              <span class="hmb-mem-nombre"><?php echo htmlspecialchars((string)$f->artista); ?></span>
              <?php if ($banda): ?>
              <span class="hmb-mem-banda">&#127928; <?php echo htmlspecialchars($banda); ?></span>
              <?php endif; ?>
              <?php if ($fechaFmt): ?>
              <span class="hmb-mem-fecha">&#10013; <?php echo $fechaFmt; ?></span>
              <?php endif; ?>
            </div>

            <!-- Icono ficha -->
            <?php if ($hasFicha): ?>
            <span class="hmb-mem-arrow" aria-hidden="true">&#8594;</span>
            <?php endif; ?>

        <?php if ($hasFicha): ?>
          </a>
        </li>
        <?php else: ?>
          </div>
        </li>
        <?php endif; ?>

        <?php endforeach; ?>
      </ul>

    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>

  <div class="hmb-mem-footer">
    <a href="<?php echo htmlspecialchars($cur); ?>?view=efemerides" class="hmb-mem-back">
      &larr; Efemérides del día
    </a>
  </div>

</div>

<style>
/* ── Wrapper ─────────────────────────────────────────── */
.hmb-memorial{max-width:1100px;margin:0 auto;font-family:inherit}

/* ── Hero ────────────────────────────────────────────── */
.hmb-mem-hero{background:#0a0a0a;border:1px solid #1e1e1e;border-top:4px solid #cc0000;padding:22px 28px;margin-bottom:0}
.hmb-mem-hero-inner{display:flex;align-items:center;gap:16px}
.hmb-mem-hero-cruz{font-size:2.2rem;color:#cc0000;opacity:.7}
.hmb-mem-hero-h1{font-size:1.4rem;font-weight:900;text-transform:uppercase;letter-spacing:.06em;margin:0;color:#fff}
.hmb-mem-hero-h1 span{color:#cc0000}
.hmb-mem-hero-sub{font-size:.62rem;color:#555;letter-spacing:.2em;margin:4px 0 0;font-weight:600}

/* ── Nav años ────────────────────────────────────────── */
.hmb-mem-nav{display:flex;flex-wrap:wrap;gap:5px;padding:10px 0;background:#0d0d0d;border:1px solid #1a1a1a;border-top:none;padding:10px 14px;margin-bottom:16px}
.hmb-mem-nav-btn{padding:4px 12px;background:#111;border:1px solid #1e1e1e;color:#666;text-decoration:none;font-size:.72rem;font-weight:700;letter-spacing:.05em;transition:all .15s}
.hmb-mem-nav-btn:hover{border-color:#cc0000;color:#cc0000}
.hmb-mem-nav-on{background:#1a0000 !important;border-color:#cc0000 !important;color:#cc0000 !important}

/* ── Grid de columnas por año ────────────────────────── */
.hmb-mem-columnas{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;margin-bottom:16px}
.hmb-mem-col{background:#0d0d0d;border:1px solid #1a1a1a}

/* Cabecera columna */
.hmb-mem-col-header{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #1a1a1a;background:#0a0a0a;border-left:3px solid #cc0000}
.hmb-mem-col-anio{font-size:1.2rem;font-weight:900;color:#cc0000;letter-spacing:.05em}
.hmb-mem-col-count{font-size:.65rem;background:#1a0000;color:#cc0000;padding:2px 7px;border-radius:2px;font-weight:700}

/* Lista de artistas */
.hmb-mem-lista{list-style:none;margin:0;padding:0}

/* Cada artista */
.hmb-mem-artista{border-bottom:1px solid #111}
.hmb-mem-artista:last-child{border-bottom:none}
.hmb-mem-artista--link{transition:background .15s}
.hmb-mem-artista--link:hover{background:#150000}

/* Enlace o div interno */
.hmb-mem-artista-lnk{display:flex;align-items:center;gap:10px;padding:10px 12px;text-decoration:none;width:100%;box-sizing:border-box}

/* Foto circular */
.hmb-mem-foto{width:46px;height:46px;border-radius:50%;overflow:hidden;flex-shrink:0;background:#111;border:2px solid #2a2a2a;display:flex;align-items:center;justify-content:center}
.hmb-mem-foto img{width:100%;height:100%;object-fit:cover;object-position:top}
.hmb-mem-foto-ph{font-size:1rem;color:#444}

/* Datos del artista */
.hmb-mem-datos{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px}
.hmb-mem-nombre{font-size:.92rem;font-weight:800;color:#e8e8e8;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hmb-mem-artista--link:hover .hmb-mem-nombre{color:#fff}
.hmb-mem-banda{font-size:.72rem;color:#d4af37;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hmb-mem-fecha{font-size:.65rem;color:#cc4444;font-weight:600}

/* Flecha de ficha */
.hmb-mem-arrow{color:#2a2a2a;font-size:.9rem;flex-shrink:0;transition:color .15s}
.hmb-mem-artista--link:hover .hmb-mem-arrow{color:#cc0000}

/* Vacío */
.hmb-mem-vacio{padding:50px;text-align:center;background:#0d0d0d;border:1px solid #1a1a1a;color:#555}
.hmb-mem-vacio span{font-size:2.5rem;display:block;margin-bottom:10px;opacity:.3}

/* Footer */
.hmb-mem-footer{text-align:center;padding:12px;border-top:1px solid #1a1a1a;margin-top:4px}
.hmb-mem-back{color:#cc0000;text-decoration:none;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em}
.hmb-mem-back:hover{color:#ff2222}

@media(max-width:600px){.hmb-mem-columnas{grid-template-columns:1fr}}
</style>
