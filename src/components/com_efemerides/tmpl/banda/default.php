<?php defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;

$esc = static fn($s): string => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$modalQ = !empty($this->modal) ? '&hmbmodal=1' : '';

if (!$this->banda): ?>
<div class="hb-vacio">
  <h2>Banda no encontrada</h2>
  <a href="<?php echo $esc(Uri::current()); ?>?view=bandas<?php echo $modalQ; ?>">&larr; Volver al listado</a>
</div>
<style>.hb-vacio{padding:48px 20px;text-align:center;color:var(--grey-8,#888);background:var(--dark,#0a0a0a);border:1px solid var(--steel2,#222)}.hb-vacio h2{font-family:var(--font-title,Oswald,sans-serif);color:var(--fire,#cc2200);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px}.hb-vacio a{color:var(--gold-light,#daa520)}</style>
<?php return; endif;

$b        = $this->banda;
$siteRoot = rtrim(Uri::root(), '/');
$foto     = trim((string) ($b->foto ?? ''));
if ($foto && strpos($foto, 'http') !== 0) $foto = $siteRoot . '/' . ltrim($foto, '/');

$anoForm  = !empty($b->fecha_formacion)  && $b->fecha_formacion  !== '0000-00-00' ? (int) date('Y', strtotime($b->fecha_formacion))  : null;
$anoDisol = !empty($b->fecha_disolucion) && $b->fecha_disolucion !== '0000-00-00' ? (int) date('Y', strtotime($b->fecha_disolucion)) : null;
$anios    = $anoForm ? max(0, ($anoDisol ?: (int) date('Y')) - $anoForm) : null;

$generos = array_slice(array_values(array_filter(array_map('trim', preg_split('/[\/,;]+/', (string) ($b->genero ?? ''))))), 0, 6);

// Bandera por pais (flagcdn, la misma que usa el mapa de conciertos)
$cc = ['estados unidos' => 'us', 'reino unido' => 'gb', 'suecia' => 'se', 'noruega' => 'no', 'alemania' => 'de', 'polonia' => 'pl',
    'finlandia' => 'fi', 'paises bajos' => 'nl', 'países bajos' => 'nl', 'canadá' => 'ca', 'canada' => 'ca', 'australia' => 'au', 'brasil' => 'br',
    'francia' => 'fr', 'italia' => 'it', 'irlanda' => 'ie', 'suiza' => 'ch', 'españa' => 'es', 'dinamarca' => 'dk', 'austria' => 'at',
    'grecia' => 'gr', 'méxico' => 'mx', 'mexico' => 'mx', 'argentina' => 'ar', 'chile' => 'cl', 'japón' => 'jp', 'japon' => 'jp'];
$bandera = $cc[mb_strtolower(trim((string) $b->pais))] ?? '';

$catColor = static function (string $n): string {
    $n = mb_strtolower(trim($n));
    if (strpos($n, 'conci') !== false) return '#d4641a';
    if (strpos($n, 'naci') !== false) return '#4caf50';
    if (strpos($n, 'defun') !== false || strpos($n, 'fallec') !== false) return '#cc4444';
    if (strpos($n, 'lanz') !== false || strpos($n, 'disco') !== false) return '#5ba3d9';
    return '#888';
};
$fechaCorta = static function (?string $d): string {
    if (!$d || $d === '0000-00-00') return '';
    $t = strtotime($d);
    $m = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    return date('j', $t) . ' ' . $m[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
};
$nMiembros = count($this->miembros);
$hayEventos = !empty($this->proximos) || !empty($this->pasados);
?>
<article class="hb<?php echo !empty($this->modal) ? ' hb--modal' : ''; ?>">

  <header class="hb-hero">
    <div class="hb-hero__glow" aria-hidden="true"></div>
    <?php if ($foto): ?>
    <div class="hb-logo"><img src="<?php echo $esc($foto); ?>" alt="<?php echo $esc($b->nombre); ?>" onerror="this.parentNode.style.display='none'"></div>
    <?php endif; ?>
    <div class="hb-hero__data">
      <span class="hb-eyebrow">Banda</span>
      <h1 class="hb-name"><?php echo $esc($b->nombre); ?></h1>
      <div class="hb-chips">
        <?php if ($b->pais): ?><span class="hb-chip hb-chip--pais"><?php if ($bandera): ?><img src="https://flagcdn.com/w40/<?php echo $bandera; ?>.png" alt="" width="20" height="14"><?php endif; ?><?php echo $esc($b->pais); ?></span><?php endif; ?>
        <?php foreach ($generos as $g): ?><span class="hb-chip hb-chip--gen"><?php echo $esc($g); ?></span><?php endforeach; ?>
        <?php if ($anoForm): ?><span class="hb-chip hb-chip--estado <?php echo $anoDisol ? 'is-off' : 'is-on'; ?>"><?php echo $anoDisol ? 'Disuelta' : 'En activo'; ?></span><?php endif; ?>
      </div>
      <div class="hb-links">
        <?php if (!empty($b->wikipedia_url)): ?><a class="hb-btn" href="<?php echo $esc($b->wikipedia_url); ?>" target="_blank" rel="noopener">Wikipedia &nearr;</a><?php endif; ?>
        <?php if (!empty($this->urlMapa) && $hayEventos): ?><a class="hb-btn hb-btn--fire" href="<?php echo $esc($this->urlMapa); ?>" target="_top">&#128205; Ver en el mapa</a><?php endif; ?>
      </div>
    </div>
  </header>

  <div class="hb-stats">
    <div class="hb-stat"><span class="hb-stat__n"><?php echo $anoForm ?: '—'; ?></span><span class="hb-stat__l">Formación</span></div>
    <div class="hb-stat"><span class="hb-stat__n"><?php echo $anios !== null ? $anios : '—'; ?></span><span class="hb-stat__l"><?php echo $anoDisol ? 'Años de carrera' : 'Años en activo'; ?></span></div>
    <div class="hb-stat"><span class="hb-stat__n"><?php echo $anoDisol ?: ($anoForm ? 'Hoy' : '—'); ?></span><span class="hb-stat__l"><?php echo $anoDisol ? 'Disolución' : 'Situación'; ?></span></div>
    <div class="hb-stat"><span class="hb-stat__n"><?php echo $nMiembros ?: '—'; ?></span><span class="hb-stat__l">Músicos en la base</span></div>
  </div>

  <div class="hb-grid">
    <div class="hb-main">

      <?php if (!empty($b->descripcion)): ?>
      <section class="hb-card">
        <h2 class="hb-card__h">Historia</h2>
        <div class="hb-text"><?php echo $b->descripcion; ?></div>
      </section>
      <?php endif; ?>

      <?php if (!empty(trim(strip_tags((string) ($b->discografia ?? ''))))): ?>
      <section class="hb-card">
        <h2 class="hb-card__h">Discografía destacada</h2>
        <div class="hb-disco"><?php echo $b->discografia; ?></div>
      </section>
      <?php endif; ?>

      <?php if ($hayEventos): ?>
      <section class="hb-card">
        <h2 class="hb-card__h">En el mapa de conciertos</h2>
        <div class="hb-evs">
          <?php foreach (array_merge($this->proximos, $this->pasados) as $ev):
              $pasado = in_array($ev, $this->pasados, true);
              $f1 = $fechaCorta($ev->start_date); $f2 = $fechaCorta($ev->end_date);
          ?>
          <div class="hb-ev<?php echo $pasado ? ' is-past' : ''; ?>">
            <span class="hb-ev__tipo"><?php echo $ev->event_type === 'festival' ? 'Festival' : 'Concierto'; ?></span>
            <strong class="hb-ev__n"><?php echo !empty($ev->website) ? '<a href="' . $esc($ev->website) . '" target="_blank" rel="noopener nofollow">' . $esc($ev->name) . '</a>' : $esc($ev->name); ?></strong>
            <span class="hb-ev__m"><?php echo $esc($f1 . ($f2 && $f2 !== $f1 ? ' → ' . $f2 : '')); ?><?php echo $ev->city ? ' · ' . $esc($ev->city) : ''; ?><?php echo $pasado ? ' · celebrado' : ''; ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

    </div>

    <aside class="hb-side">

      <?php if ($nMiembros): ?>
      <section class="hb-card">
        <h2 class="hb-card__h">Miembros <small>(<?php echo $nMiembros; ?>)</small></h2>
        <div class="hb-members">
          <?php foreach ($this->miembros as $mm):
              $mfoto = trim((string) ($mm->foto ?? ''));
              if ($mfoto && strpos($mfoto, 'http') !== 0) $mfoto = $siteRoot . '/' . ltrim($mfoto, '/');
              $mnac = !empty($mm->fecha_nacimiento) && $mm->fecha_nacimiento !== '0000-00-00' ? date('Y', strtotime($mm->fecha_nacimiento)) : null;
              $mdef = !empty($mm->fecha_defuncion) && $mm->fecha_defuncion !== '0000-00-00' ? date('Y', strtotime($mm->fecha_defuncion)) : null;
          ?>
          <a class="hb-m" href="<?php echo $esc(Uri::current()); ?>?view=musico&id=<?php echo (int) $mm->id . $modalQ; ?>">
            <?php if ($mfoto): ?><img class="hb-m__img" src="<?php echo $esc($mfoto); ?>" alt="" loading="lazy" onerror="this.style.visibility='hidden'">
            <?php else: ?><span class="hb-m__img hb-m__img--ph">&#127928;</span><?php endif; ?>
            <span class="hb-m__t">
              <span class="hb-m__n"><?php echo $esc($mm->nombre); ?></span>
              <span class="hb-m__a"><?php echo $mnac ? $mnac . ($mdef ? ' – ' . $mdef : '') : ''; ?><?php if (empty($mm->es_principal)): ?> · colaborador<?php endif; ?></span>
            </span>
            <?php if ($mdef): ?><span class="hb-m__cruz" title="Fallecido">&#10013;</span><?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php if (!empty($this->efems)): ?>
      <section class="hb-card">
        <h2 class="hb-card__h">Efemérides <small>(<?php echo count($this->efems); ?>)</small></h2>
        <div class="hb-efs">
          <?php foreach ($this->efems as $ef):
              $fv  = new DateTime($ef->fecha_evento);
              $cat = (string) ($ef->categoria_nombre ?? $ef->cat_nombre ?? '');
              $col = $catColor($cat);
          ?>
          <div class="hb-ef" style="--c:<?php echo $col; ?>">
            <span class="hb-ef__y"><?php echo $fv->format('Y'); ?></span>
            <span class="hb-ef__t"><?php echo $esc($ef->titulo); ?><em><?php echo $fv->format('d/m'); ?><?php echo $cat !== '' ? ' · ' . $esc($cat) : ''; ?></em></span>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

    </aside>
  </div>

  <?php if (empty($this->modal)): ?>
  <nav class="hb-foot">
    <a href="<?php echo $esc(Uri::current()); ?>?view=bandas">&larr; Todas las bandas</a>
    <a href="<?php echo $esc(Uri::current()); ?>?view=musicos">Músicos</a>
    <a href="<?php echo $esc(Uri::current()); ?>?view=efemerides">Efemérides</a>
  </nav>
  <?php endif; ?>
</article>

<style>
.hb{--c-bg:var(--dark,#0a0a0a);--c-card:var(--mid,#111);--c-line:var(--steel2,#222);--c-fire:var(--fire,#cc2200);--c-gold:var(--gold-light,#daa520);--c-text:var(--grey-c,#ccc);--c-dim:var(--grey-8,#888);
  max-width:1040px;margin:0 auto;color:var(--c-text);font-family:var(--font-body,Rajdhani,sans-serif);font-size:1rem;line-height:1.6}
.hb *{box-sizing:border-box}
.hb a{color:var(--c-gold);text-decoration:none}
.hb a:hover{color:#fff}
/* Hero */
.hb-hero{position:relative;overflow:hidden;display:flex;align-items:center;gap:32px;flex-wrap:wrap;padding:32px;background:linear-gradient(135deg,#150000 0%,var(--c-bg) 55%);border:1px solid var(--c-line);border-top:3px solid var(--c-fire)}
.hb-hero__glow{position:absolute;inset:0;background:radial-gradient(ellipse at 15% 20%,rgba(204,34,0,.22),transparent 55%);pointer-events:none}
.hb-hero>*{position:relative}
.hb-logo{flex:0 0 auto;padding:14px;background:rgba(0,0,0,.55);border:1px solid var(--c-line);box-shadow:0 0 0 1px rgba(218,165,32,.12),0 10px 30px rgba(0,0,0,.6)}
.hb-logo img{display:block;max-width:220px;max-height:140px;object-fit:contain}
.hb-hero__data{flex:1 1 280px;min-width:0}
.hb-eyebrow{display:block;font-family:var(--font-title,Oswald,sans-serif);font-size:.72rem;letter-spacing:.32em;text-transform:uppercase;color:var(--c-gold);margin-bottom:2px}
.hb-name{font-family:var(--font-display,'Metal Mania',cursive);font-weight:400;font-size:clamp(2.2rem,5vw,3.6rem);line-height:1.05;color:#fff;margin:0 0 14px;text-shadow:0 2px 18px rgba(204,34,0,.45)}
.hb-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px}
.hb-chip{display:inline-flex;align-items:center;gap:7px;padding:3px 11px;font-family:var(--font-title,Oswald,sans-serif);font-size:.74rem;letter-spacing:.08em;text-transform:uppercase;background:var(--steel,#1a1a1a);border:1px solid var(--grey-2,#2a2a2a);color:var(--c-text)}
.hb-chip img{display:block;border-radius:2px}
.hb-chip--gen{color:var(--c-gold);border-color:rgba(218,165,32,.28)}
.hb-chip--estado.is-on{color:var(--ok-light,#66d98e);border-color:rgba(76,175,80,.4)}
.hb-chip--estado.is-off{color:var(--fire-light,#ff7070);border-color:rgba(204,34,0,.4)}
.hb-links{display:flex;gap:10px;flex-wrap:wrap}
.hb-btn{display:inline-block;padding:7px 16px;font-family:var(--font-title,Oswald,sans-serif);font-size:.76rem;letter-spacing:.1em;text-transform:uppercase;background:transparent;border:1px solid var(--grey-3,#333);color:var(--c-text)!important;transition:.2s}
.hb-btn:hover{border-color:var(--c-gold);color:#fff!important}
.hb-btn--fire{background:linear-gradient(160deg,var(--fire,#cc2200),var(--blood,#8b0000));border-color:rgba(204,34,0,.6)}
.hb-btn--fire:hover{filter:brightness(1.15);border-color:var(--c-gold)}
/* Cifras */
.hb-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;margin:0;background:var(--c-line);border:1px solid var(--c-line);border-top:0}
.hb-stat{background:var(--c-card);padding:14px 10px;text-align:center}
.hb-stat__n{display:block;font-family:var(--font-title,Oswald,sans-serif);font-size:1.6rem;font-weight:600;color:#fff;line-height:1.1}
.hb-stat__l{display:block;margin-top:2px;font-size:.68rem;letter-spacing:.14em;text-transform:uppercase;color:var(--c-dim)}
/* Rejilla */
.hb-grid{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:18px;margin-top:18px;align-items:start}
.hb-card{background:var(--c-card);border:1px solid var(--c-line);margin-bottom:18px}
.hb-card__h{margin:0;padding:11px 18px;font-family:var(--font-title,Oswald,sans-serif);font-size:.86rem;font-weight:500;letter-spacing:.16em;text-transform:uppercase;color:var(--c-fire);background:var(--c-bg);border-bottom:1px solid var(--c-line);border-left:3px solid var(--c-fire)}
.hb-card__h small{color:var(--c-dim);font-weight:400;letter-spacing:.05em}
.hb-text{padding:18px 20px;color:var(--c-text)}
.hb-text p{margin:0 0 .95em}
.hb-text p:last-child{margin-bottom:0}
.hb-text p:first-child::first-letter{float:left;font-family:var(--font-display,'Metal Mania',cursive);font-size:3.1rem;line-height:.85;padding:4px 8px 0 0;color:var(--c-fire)}
.hb-text strong{color:#fff}
.hb-disco{padding:12px 20px 16px}
.hb-disco ul{list-style:none;margin:0;padding:0}
.hb-disco li{display:flex;align-items:baseline;gap:12px;padding:8px 0;border-bottom:1px solid var(--c-line);color:var(--grey-d,#ddd)}
.hb-disco li:last-child{border-bottom:0}
.hb-disco li::before{content:'\25B6';font-size:.6rem;color:var(--c-fire);flex:0 0 auto;transform:translateY(-1px)}
.hb-disco li em{margin-left:auto;font-style:normal;font-family:var(--font-title,Oswald,sans-serif);color:var(--c-gold);letter-spacing:.06em}
/* Eventos */
.hb-evs{padding:6px 18px 12px}
.hb-ev{display:grid;grid-template-columns:78px 1fr;gap:2px 14px;padding:10px 0;border-bottom:1px solid var(--c-line)}
.hb-ev:last-child{border-bottom:0}
.hb-ev__tipo{grid-row:1/3;align-self:center;text-align:center;padding:4px 0;font-family:var(--font-title,Oswald,sans-serif);font-size:.64rem;letter-spacing:.12em;text-transform:uppercase;color:#fff;background:linear-gradient(160deg,var(--fire,#cc2200),var(--blood,#8b0000))}
.hb-ev.is-past .hb-ev__tipo{background:var(--steel,#1a1a1a);color:var(--c-dim)}
.hb-ev__n{color:#fff;font-weight:600}
.hb-ev__m{font-size:.84rem;color:var(--c-dim)}
/* Miembros */
.hb-members{padding:8px}
.hb-m{display:flex;align-items:center;gap:11px;padding:7px 8px;border-bottom:1px solid var(--c-line);transition:background .15s}
.hb-m:last-child{border-bottom:0}
.hb-m:hover{background:rgba(204,34,0,.1)}
.hb-m__img{flex:0 0 42px;width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid var(--grey-2,#2a2a2a);background:var(--steel,#1a1a1a)}
.hb-m__img--ph{display:flex;align-items:center;justify-content:center;font-size:1rem}
.hb-m__t{display:flex;flex-direction:column;min-width:0}
.hb-m__n{color:var(--grey-e,#e0e0e0);font-weight:600;font-size:.95rem;line-height:1.2}
.hb-m__a{font-size:.74rem;color:var(--c-dim)}
.hb-m__cruz{margin-left:auto;color:var(--fire-light,#ff7070);font-size:.8rem}
/* Efemerides */
.hb-efs{padding:8px 14px 12px}
.hb-ef{display:flex;gap:12px;align-items:baseline;padding:7px 0 7px 11px;border-bottom:1px solid var(--c-line);border-left:3px solid var(--c)}
.hb-ef:last-child{border-bottom:0}
.hb-ef__y{font-family:var(--font-title,Oswald,sans-serif);font-weight:600;color:var(--c);min-width:42px}
.hb-ef__t{font-size:.9rem;color:var(--grey-d,#ddd);line-height:1.35}
.hb-ef__t em{display:block;font-style:normal;font-size:.7rem;color:var(--c-dim);letter-spacing:.04em}
.hb-foot{display:flex;justify-content:center;gap:22px;flex-wrap:wrap;padding:18px;margin-top:6px;border-top:1px solid var(--c-line);font-family:var(--font-title,Oswald,sans-serif);font-size:.76rem;letter-spacing:.12em;text-transform:uppercase}
/* Dentro del modal del mapa */
.hb--modal{max-width:none;padding:18px}
.hb--modal .hb-hero{padding:22px}
@media(max-width:820px){.hb-grid{grid-template-columns:1fr}.hb-side{order:-1}.hb-stats{grid-template-columns:repeat(2,1fr)}.hb-hero{padding:22px}}
</style>
