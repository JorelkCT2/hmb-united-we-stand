<?php defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

if (!$this->banda):?>
<div style="padding:40px;text-align:center;color:#888;background:#0d0d0d;border:1px solid #1e1e1e">
  <h2 style="color:#cc0000">Banda no encontrada</h2>
  <a href="<?php echo Uri::current(); ?>?view=bandas" style="color:#cc0000">← Volver al listado</a>
</div>
<?php return; endif;

$b        = $this->banda;
$siteRoot = rtrim(Uri::root(),'/');
$foto     = trim((string)($b->foto ?? ''));
if ($foto && strpos($foto,'http')!==0) $foto = $siteRoot.'/'.ltrim($foto,'/');
$anoForm  = !empty($b->fecha_formacion)  && $b->fecha_formacion !=='0000-00-00' ? date('Y',strtotime($b->fecha_formacion))  : null;
$anoDisol = !empty($b->fecha_disolucion) && $b->fecha_disolucion!=='0000-00-00' ? date('Y',strtotime($b->fecha_disolucion)) : null;
$catInfo  = static function(string $n): string {
    $n=mb_strtolower(trim($n));
    if(strpos($n,'conci')!==false) return '#d4641a';
    if(strpos($n,'naci')!==false)  return '#4caf50';
    if(strpos($n,'defun')!==false||strpos($n,'fallec')!==false) return '#cc4444';
    if(strpos($n,'lanz')!==false||strpos($n,'disco')!==false) return '#5ba3d9';
    return '#888';
};
?>
<div class="hmb-banda">

  <!-- HERO -->
  <div class="hmb-banda-hero">
    <?php if ($foto): ?>
    <div class="hmb-banda-logo-wrap">
      <img src="<?php echo htmlspecialchars($foto); ?>"
           alt="<?php echo htmlspecialchars($b->nombre); ?>"
           class="hmb-banda-logo" onerror="this.style.display='none'">
    </div>
    <?php endif; ?>
    <div class="hmb-banda-hero-data">
      <div class="hmb-banda-tipo">BANDA</div>
      <h1 class="hmb-banda-nombre"><?php echo htmlspecialchars($b->nombre); ?></h1>
      <div class="hmb-banda-meta">
        <?php if ($b->pais): ?><span class="hmb-banda-meta-item">&#127758; <?php echo htmlspecialchars($b->pais); ?></span><?php endif; ?>
        <?php if ($b->genero): ?><span class="hmb-banda-meta-item hmb-banda-genero"><?php echo htmlspecialchars($b->genero); ?></span><?php endif; ?>
        <?php if ($anoForm): ?>
        <span class="hmb-banda-meta-item">
          <?php echo $anoForm; ?><?php echo $anoDisol ? ' – '.$anoDisol : ' – act.'; ?>
          <?php if ($anoDisol): ?><span style="color:#cc4444;font-size:.65rem;margin-left:4px">DISUELTA</span>
          <?php else: ?><span style="color:#4caf50;font-size:.65rem;margin-left:4px">EN ACTIVO</span><?php endif; ?>
        </span>
        <?php endif; ?>
      </div>
      <?php if (!empty($b->wikipedia_url)): ?>
      <a href="<?php echo htmlspecialchars($b->wikipedia_url); ?>" target="_blank" class="hmb-banda-wiki">W Wikipedia</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="hmb-banda-body">
    <div class="hmb-banda-left">

      <?php if (!empty($b->descripcion)): ?>
      <div class="hmb-ficha-card">
        <h3 class="hmb-ficha-card-h">Historia</h3>
        <div class="hmb-ficha-bio"><?php echo $b->descripcion; ?></div>
      </div>
      <?php endif; ?>

      <!-- EFEMÉRIDES -->
      <?php if (!empty($this->efems)): ?>
      <div class="hmb-ficha-card">
        <h3 class="hmb-ficha-card-h">&#128197; Efemérides (<?php echo count($this->efems); ?>)</h3>
        <div class="hmb-ficha-efems">
          <?php foreach ($this->efems as $ef):
            $fv  = new DateTime($ef->fecha_evento);
            $col = $catInfo((string)($ef->cat_nombre??''));
          ?>
          <div class="hmb-ficha-efem" style="border-left-color:<?php echo $col; ?>">
            <span class="hmb-ficha-efem-anio" style="color:<?php echo $col; ?>"><?php echo $fv->format('Y'); ?></span>
            <span class="hmb-ficha-efem-fecha"><?php echo $fv->format('d/m'); ?></span>
            <div>
              <span class="hmb-ficha-efem-tit"><?php echo htmlspecialchars($ef->titulo); ?></span>
              <?php if (!empty($ef->cat_nombre)): ?>
              <span class="hmb-ficha-efem-badge" style="color:<?php echo $col; ?>"><?php echo htmlspecialchars($ef->cat_nombre); ?></span>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- MIEMBROS -->
    <?php if (!empty($this->miembros)): ?>
    <div class="hmb-banda-right">
      <div class="hmb-ficha-card">
        <?php
        $nPrinc = count(array_filter($this->miembros, fn($m) => !empty($m->es_principal)));
        $nColab = count($this->miembros) - $nPrinc;
        ?>
        <h3 class="hmb-ficha-card-h">
          Miembros (<?php echo count($this->miembros); ?>)
          <?php if ($nColab > 0): ?>
          <small style="font-weight:400;color:#666;font-size:.7rem"> · <?php echo $nColab; ?> colaborador<?php echo $nColab>1?'es':''; ?></small>
          <?php endif; ?>
        </h3>
        <div style="padding:12px;display:flex;flex-direction:column;gap:6px">
          <?php foreach ($this->miembros as $mm):
            $esPrincipal = !empty($mm->es_principal);
            $mfoto=trim((string)($mm->foto??''));
            if($mfoto&&strpos($mfoto,'http')!==0) $mfoto=$siteRoot.'/'.ltrim($mfoto,'/');
            $mnac=!empty($mm->fecha_nacimiento)&&$mm->fecha_nacimiento!=='0000-00-00'?date('Y',strtotime($mm->fecha_nacimiento)):null;
            $mdef=!empty($mm->fecha_defuncion) &&$mm->fecha_defuncion !=='0000-00-00'?date('Y',strtotime($mm->fecha_defuncion)):null;
          ?>
          <a href="<?php echo Uri::current(); ?>?view=musico&id=<?php echo $mm->id; ?>"
             class="hmb-banda-miembro">
            <?php if($mfoto): ?>
            <img src="<?php echo htmlspecialchars($mfoto); ?>" alt="" class="hmb-banda-mfoto"
                 onerror="this.style.display='none'">
            <?php else: ?><div class="hmb-banda-mph">&#127928;</div><?php endif; ?>
            <div>
              <span class="hmb-banda-mnombre"><?php echo htmlspecialchars($mm->nombre); ?></span>
              <?php if (!$esPrincipal): ?>
              <span class="hmb-banda-colaborador" title="Colaborador / Miembro de otra banda principal">colaborador</span>
              <?php endif; ?>
              <?php if($mnac): ?>
              <span class="hmb-banda-manios"><?php echo $mnac; echo $mdef?' – '.$mdef:''; ?></span>
              <?php endif; ?>
            </div>
            <?php if($mdef): ?><span style="color:#cc4444;font-size:.7rem;margin-left:auto">&#10013;</span><?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="hmb-ficha-footer">
    <a href="<?php echo Uri::current(); ?>?view=bandas" class="hmb-lnk">&#8592; Todas las bandas</a>
    &nbsp;·&nbsp;
    <a href="<?php echo Uri::current(); ?>?view=musicos" class="hmb-lnk">Músicos</a>
    &nbsp;·&nbsp;
    <a href="<?php echo Uri::current(); ?>?view=efemerides" class="hmb-lnk">Efemérides</a>
  </div>
</div>

<style>
.hmb-banda{max-width:1000px;margin:0 auto;font-family:inherit}
.hmb-banda-hero{background:#0a0a0a;border:1px solid #1e1e1e;border-top:4px solid #d4af37;display:flex;align-items:center;gap:28px;padding:28px;flex-wrap:wrap}
.hmb-banda-logo-wrap{flex-shrink:0;background:#0d0d0d;padding:12px;border:1px solid #1e1e1e}
.hmb-banda-logo{max-height:130px;max-width:200px;object-fit:contain;filter:brightness(.95)}
.hmb-banda-tipo{font-size:.6rem;color:#d4af37;letter-spacing:.2em;font-weight:700;text-transform:uppercase;margin-bottom:4px}
.hmb-banda-nombre{font-size:2rem;font-weight:900;color:#fff;margin:0 0 10px;line-height:1.1}
.hmb-banda-meta{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:12px}
.hmb-banda-meta-item{font-size:.8rem;color:#aaa}
.hmb-banda-genero{background:#1a1a1a;border:1px solid #2a2a2a;padding:2px 9px;color:#d4af37;font-size:.72rem;font-weight:700}
.hmb-banda-wiki{display:inline-block;background:#1a1a1a;border:1px solid #2a2a2a;color:#aaa;padding:5px 14px;font-size:.76rem;font-weight:700;text-decoration:none}
.hmb-banda-wiki:hover{border-color:#d4af37;color:#fff}
.hmb-banda-body{display:grid;grid-template-columns:1fr 260px;gap:16px;margin-top:16px;align-items:start}
.hmb-banda-right{}
.hmb-banda-miembro{display:flex;align-items:center;gap:10px;padding:8px;text-decoration:none;border-bottom:1px solid #0f0f0f;transition:background .15s}
.hmb-banda-miembro:hover{background:#1a0a0a}
.hmb-banda-miembro:last-child{border-bottom:none}
.hmb-banda-colaborador{font-size:.58rem;background:#1a1a2a;color:#5ba3d9;border:1px solid #2a2a4a;padding:1px 6px;border-radius:2px;letter-spacing:.04em;white-space:nowrap}
.hmb-banda-mfoto{width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #2a2a2a;flex-shrink:0}
.hmb-banda-mph{width:44px;height:44px;border-radius:50%;background:#1a1a1a;border:2px solid #2a2a2a;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0}
.hmb-banda-mnombre{display:block;font-size:.82rem;color:#e0e0e0;font-weight:700}
.hmb-banda-manios{display:block;font-size:.68rem;color:#666}
/* reutilizar clases de musico */
.hmb-ficha-card{background:#111;border:1px solid #1e1e1e;margin-bottom:14px}
.hmb-ficha-card-h{font-size:.75rem;font-weight:700;color:#cc0000;text-transform:uppercase;letter-spacing:.1em;margin:0;padding:10px 16px;border-bottom:1px solid #1e1e1e;background:#0a0a0a}
.hmb-ficha-bio{padding:16px;font-size:.85rem;color:#aaa;line-height:1.8}
.hmb-ficha-efems{padding:10px 14px;display:flex;flex-direction:column;gap:6px}
.hmb-ficha-efem{display:flex;align-items:baseline;gap:10px;padding:7px 0;border-bottom:1px solid #0f0f0f;border-left:3px solid #cc0000;padding-left:10px}
.hmb-ficha-efem:last-child{border-bottom:none}
.hmb-ficha-efem-anio{font-size:1rem;font-weight:900;min-width:40px}
.hmb-ficha-efem-fecha{font-size:.7rem;color:#555;min-width:34px}
.hmb-ficha-efem-tit{font-size:.82rem;color:#ddd;font-weight:600}
.hmb-ficha-efem-badge{font-size:.6rem;padding:1px 6px;border-radius:2px;margin-left:6px}
.hmb-ficha-footer{text-align:center;padding:14px;border-top:1px solid #1a1a1a;margin-top:4px;font-size:.76rem}
.hmb-lnk{color:#cc0000;text-decoration:none;font-weight:700;letter-spacing:.05em;text-transform:uppercase;font-size:.72rem}
.hmb-lnk:hover{color:#ff2222}
@media(max-width:700px){.hmb-banda-body{grid-template-columns:1fr}.hmb-banda-right{order:-1}}
</style>
