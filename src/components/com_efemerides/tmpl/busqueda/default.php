<?php defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
$base = rtrim($this->componentBase, '/');
$q    = htmlspecialchars($this->query);
?>
<style>
.bq{max-width:960px;margin:0 auto;padding:20px 16px}
.bq-form{display:flex;gap:8px;margin-bottom:28px}
.bq-input{flex:1;background:var(--dark, #0A0A0A);border:1px solid var(--grey-3, #333333);color:#eee;padding:12px 16px;font-size:1rem;border-radius:3px 0 0 3px;outline:none}
.bq-input:focus{border-color:var(--fire, #CC2200)}
.bq-btn{background:var(--fire, #CC2200);color:#fff;border:none;padding:12px 22px;cursor:pointer;font-size:.95rem;font-weight:700;letter-spacing:.04em;border-radius:0 3px 3px 0}
.bq-btn:hover{background:#aa0000}
.bq-summary{color:var(--grey-6, #666666);font-size:.82rem;margin-bottom:24px;padding-bottom:12px;border-bottom:1px solid var(--steel, #1A1A1A)}
.bq-summary strong{color:var(--fire, #CC2200)}
.bq-section{margin-bottom:32px}
.bq-sh{font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;color:var(--grey-5, #555555);border-left:3px solid;padding-left:10px;margin-bottom:12px;font-weight:700}
.bq-sh-ef{border-color:var(--fire, #CC2200)}
.bq-sh-fa{border-color:#5555cc}
.bq-sh-mu{border-color:#cc6600}
.bq-sh-fr{border-color:#009966}
.bq-item{background:var(--dark, #0A0A0A);border:1px solid var(--steel, #1A1A1A);border-radius:3px;padding:11px 14px;margin-bottom:7px;display:flex;gap:12px;align-items:center;transition:border-color .2s}
.bq-item:hover{border-color:var(--grey-2, #2A2A2A)}
.bq-foto{width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;background:var(--mid, #111111);border:1px solid var(--steel2, #222222)}
.bq-ph{width:40px;height:40px;border-radius:50%;background:var(--mid, #111111);border:1px solid var(--steel, #1A1A1A);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.bq-body{flex:1;min-width:0}
.bq-title{font-size:.92rem;font-weight:600;margin-bottom:2px}
.bq-title a{color:var(--grey-d, #DDDDDD);text-decoration:none}
.bq-title a:hover{color:var(--fire, #CC2200)}
.bq-meta{color:var(--grey-5, #555555);font-size:.75rem;margin-bottom:3px}
.bq-desc{color:var(--text-dim, #777777);font-size:.78rem;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.bq-frase{color:var(--grey-9, #999999);font-size:.85rem;font-style:italic;line-height:1.5}
.bq-nada{color:var(--grey-4, #444444);text-align:center;padding:40px;font-size:.9rem}
</style>

<div class="bq">
  <h1 style="color:#fff;font-family:'Metal Mania',serif;font-size:1.8rem;margin-bottom:20px;text-transform:uppercase;letter-spacing:.05em">
    &#128269; Búsqueda global
  </h1>

  <form method="get" action="<?php echo htmlspecialchars(Uri::current()); ?>" class="bq-form">
    <input type="hidden" name="view" value="busqueda">
    <input class="bq-input" type="text" name="q" value="<?php echo $q; ?>"
           placeholder="Buscar artista, canción, frase, fecha...">
    <button class="bq-btn" type="submit">BUSCAR</button>
  </form>

  <?php if ($this->query !== '' && strlen($this->query) < 2): ?>
    <div class="bq-nada">Escribe al menos 2 caracteres.</div>
  <?php elseif ($this->query !== '' && $this->total === 0): ?>
    <div class="bq-nada">Sin resultados para <strong style="color:#eee"><?php echo $q; ?></strong></div>
  <?php elseif ($this->query !== ''): ?>
    <div class="bq-summary">
      <?php echo $this->total; ?> resultado<?php echo $this->total !== 1 ? 's' : ''; ?> para
      <strong>"<?php echo $q; ?>"</strong>
      — <?php echo count($this->efemerides); ?> efemérides
      · <?php echo count($this->musicos); ?> músicos
      · <?php echo count($this->fallecidos); ?> fallecidos
      · <?php echo count($this->frases); ?> frases
    </div>

    <?php if (!empty($this->musicos)): ?>
    <div class="bq-section">
      <div class="bq-sh bq-sh-mu">&#127928; Músicos</div>
      <?php foreach ($this->musicos as $m):
        $foto = trim((string)($m->foto ?? ''));
        $vivo = empty($m->fecha_defuncion) || $m->fecha_defuncion === '0000-00-00';
      ?>
      <div class="bq-item">
        <?php if ($foto): ?>
        <img src="<?php echo htmlspecialchars($foto); ?>" class="bq-foto" alt="">
        <?php else: ?><div class="bq-ph">&#127928;</div><?php endif; ?>
        <div class="bq-body">
          <div class="bq-title">
            <a href="<?php echo htmlspecialchars($base . '?view=musico&id=' . (int)$m->id); ?>">
              <?php echo htmlspecialchars($m->nombre); ?>
            </a>
            <?php if (!$vivo): ?>
            <span style="color:#555;font-size:.7rem;font-weight:400"> ✝ <?php echo date('Y', strtotime($m->fecha_defuncion)); ?></span>
            <?php endif; ?>
          </div>
          <div class="bq-meta">
            <?php echo htmlspecialchars((string)($m->banda_nombre ?? '')); ?>
            <?php if (!empty($m->instrumento)): ?> · <?php echo htmlspecialchars($m->instrumento); ?><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($this->efemerides)): ?>
    <div class="bq-section">
      <div class="bq-sh bq-sh-ef">&#128197; Efemérides</div>
      <?php foreach ($this->efemerides as $e): ?>
      <div class="bq-item">
        <div class="bq-ph">&#128197;</div>
        <div class="bq-body">
          <div class="bq-title" style="color:#ddd"><?php echo htmlspecialchars($e->titulo); ?></div>
          <div class="bq-meta">
            <?php echo $e->fecha_evento ? date('d/m/Y', strtotime($e->fecha_evento)) : ''; ?>
            <?php if (!empty($e->artista)): ?> · <?php echo htmlspecialchars($e->artista); ?><?php endif; ?>
            <?php if (!empty($e->categoria)): ?> · <span style="color:#cc4444"><?php echo htmlspecialchars($e->categoria); ?></span><?php endif; ?>
          </div>
          <?php if (!empty($e->descripcion)): ?>
          <div class="bq-desc"><?php echo htmlspecialchars($e->descripcion); ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($this->fallecidos)): ?>
    <div class="bq-section">
      <div class="bq-sh bq-sh-fa">&#10013; Fallecidos</div>
      <?php foreach ($this->fallecidos as $f):
        $foto = trim((string)($f->foto ?? ''));
        $mid  = (int)($f->musico_id ?? 0);
      ?>
      <div class="bq-item">
        <?php if ($foto): ?>
        <img src="<?php echo htmlspecialchars($foto); ?>" class="bq-foto" alt="">
        <?php else: ?><div class="bq-ph">&#10013;</div><?php endif; ?>
        <div class="bq-body">
          <div class="bq-title">
            <?php if ($mid): ?>
            <a href="<?php echo htmlspecialchars($base . '?view=musico&id=' . $mid); ?>"><?php echo htmlspecialchars($f->artista); ?></a>
            <?php else: echo htmlspecialchars($f->artista); endif; ?>
          </div>
          <div class="bq-meta">
            ✝ <?php echo !empty($f->fecha_fallecimiento) ? date('d/m/Y', strtotime($f->fecha_fallecimiento)) : 'Sin fecha'; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($this->frases)): ?>
    <div class="bq-section">
      <div class="bq-sh bq-sh-fr">&#8220; Frases míticas</div>
      <?php foreach ($this->frases as $f):
        $mid  = (int)($f->musico_id ?? 0);
        $foto = trim((string)($f->musico_foto ?? ''));
      ?>
      <div class="bq-item" style="align-items:flex-start">
        <?php if ($foto): ?>
        <img src="<?php echo htmlspecialchars($foto); ?>" class="bq-foto" alt="">
        <?php else: ?><div class="bq-ph">&#8220;</div><?php endif; ?>
        <div class="bq-body">
          <div class="bq-frase">"<?php echo htmlspecialchars($f->frase); ?>"</div>
          <div class="bq-meta" style="margin-top:5px">
            — <?php if ($mid): ?>
            <a href="<?php echo htmlspecialchars($base . '?view=musico&id=' . $mid); ?>" style="color:#888"><?php echo htmlspecialchars($f->autor); ?></a>
            <?php else: echo htmlspecialchars($f->autor); endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  <?php endif; ?>
</div>
