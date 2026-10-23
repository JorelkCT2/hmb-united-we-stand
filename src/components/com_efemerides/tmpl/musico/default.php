<?php defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

if (!$this->musico):?>
<div style="padding:40px;text-align:center;color:#888;background:#0d0d0d;border:1px solid #1e1e1e">
  <h2 style="color:#cc0000">Músico no encontrado</h2>
  <a href="<?php echo Uri::current(); ?>?view=musicos" style="color:#cc0000">← Volver</a>
</div>
<?php return; endif;

$m        = $this->musico;
$siteRoot = rtrim(Uri::root(),'/');
$anioNac  = !empty($m->fecha_nacimiento)&&$m->fecha_nacimiento!=='0000-00-00' ? date('Y',strtotime($m->fecha_nacimiento)) : null;
$anioDef  = !empty($m->fecha_defuncion) &&$m->fecha_defuncion !=='0000-00-00' ? date('Y',strtotime($m->fecha_defuncion))  : null;
$foto     = trim((string)($m->foto??''));
if ($foto&&strpos($foto,'http')!==0) $foto=$siteRoot.'/'.ltrim($foto,'/');

$flagMap = [
    'estados unidos'=>'🇺🇸','usa'=>'🇺🇸','reino unido'=>'🇬🇧','inglaterra'=>'🇬🇧',
    'gales'=>'🏴󠁧󠁢󠁷󠁬󠁳󠁿','escocia'=>'🏴󠁧󠁢󠁳󠁣󠁴󠁿','irlanda'=>'🇮🇪','irlanda del norte'=>'🇬🇧',
    'alemania'=>'🇩🇪','austria'=>'🇦🇹','españa'=>'🇪🇸','francia'=>'🇫🇷',
    'italia'=>'🇮🇹','suecia'=>'🇸🇪','noruega'=>'🇳🇴','dinamarca'=>'🇩🇰',
    'finlandia'=>'🇫🇮','países bajos'=>'🇳🇱','holanda'=>'🇳🇱','paises bajos'=>'🇳🇱',
    'bélgica'=>'🇧🇪','belgica'=>'🇧🇪','suiza'=>'🇨🇭','portugal'=>'🇵🇹',
    'australia'=>'🇦🇺','canada'=>'🇨🇦','canadá'=>'🇨🇦','brasil'=>'🇧🇷',
    'japón'=>'🇯🇵','japon'=>'🇯🇵','polonia'=>'🇵🇱','rusia'=>'🇷🇺',
    'grecia'=>'🇬🇷','colombia'=>'🇨🇴','argentina'=>'🇦🇷','chile'=>'🇨🇱',
    'mexico'=>'🇲🇽','méxico'=>'🇲🇽','tanzania'=>'🇹🇿','tanzanía'=>'🇹🇿',
    'cuba'=>'🇨🇺','nueva zelanda'=>'🇳🇿','israel'=>'🇮🇱','croacia'=>'🇭🇷',
];
$getFlag = static function(string $p) use ($flagMap): string {
    return $flagMap[mb_strtolower(trim($p))] ?? '🌍';
};
$catColor = static function(string $n): string {
    $n=mb_strtolower(trim($n));
    if(strpos($n,'conci')!==false) return '#d4641a';
    if(strpos($n,'naci')!==false)  return '#4caf50';
    if(strpos($n,'defun')!==false||strpos($n,'fallec')!==false) return '#cc4444';
    if(strpos($n,'lanz')!==false||strpos($n,'disco')!==false) return '#5ba3d9';
    return '#888';
};

$banda        = $this->banda  ?? null;
$banda2       = $this->banda2 ?? null;
$bandaNombre  = $banda  ? $banda->nombre  : ($m->banda_texto  ?? '');
$banda2Nombre = $banda2 ? $banda2->nombre : ($m->banda_texto_2 ?? '');
?>

<div class="mf-wrap">

  <!-- TOPBAR -->
  <div class="mf-topbar">
    <span class="mf-tipo">MÚSICO</span>
    <?php if (!empty($m->pais)): ?>
    <span class="mf-flag"><?php echo $getFlag($m->pais); ?></span>
    <span class="mf-pais"><?php echo htmlspecialchars($m->pais); ?></span>
    <?php endif; ?>
  </div>

  <!-- GRID: [foto | datos | sidebar] -->
  <div class="mf-grid">

    <!-- FOTO -->
    <div class="mf-col-foto">
      <?php if ($foto): ?>
      <img src="<?php echo htmlspecialchars($foto); ?>"
           alt="<?php echo htmlspecialchars($m->nombre); ?>"
           class="mf-foto"
           onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
      <div class="mf-foto-ph" style="display:none">&#127928;</div>
      <?php else: ?>
      <div class="mf-foto-ph">&#127928;</div>
      <?php endif; ?>
    </div>

    <!-- DATOS CENTRALES -->
    <div class="mf-col-datos">

      <h1 class="mf-nombre"><?php echo htmlspecialchars($m->nombre); ?></h1>

      <?php if (!empty($m->instrumento)): ?>
      <div class="mf-instr">
        <span>🎸</span>
        <span class="mf-instr-txt"><?php echo htmlspecialchars($m->instrumento); ?></span>
      </div>
      <?php endif; ?>

      <?php if ($esSolista): ?>
      <div class="mf-banda-row">
        <span class="mf-solista-badge">&#9733; Artista Solista</span>
      </div>
      <?php elseif ($bandaNombre): ?>
      <div class="mf-banda-row">
        <?php if ($banda): ?>
        <a href="<?php echo htmlspecialchars(Uri::current()); ?>?view=banda&id=<?php echo $banda->id; ?>"
           class="mf-banda-lnk">&#127928; <?php echo htmlspecialchars($bandaNombre); ?></a>
        <?php else: ?>
        <span class="mf-banda-txt">&#127928; <?php echo htmlspecialchars($bandaNombre); ?></span>
        <?php endif; ?>
      </div>
      <?php if (!$esSolista && $banda2Nombre): ?>
      <div class="mf-banda-row mf-banda2-row">
        <?php if ($banda2): ?>
        <a href="<?php echo htmlspecialchars(Uri::current()); ?>?view=banda&id=<?php echo $banda2->id; ?>"
           class="mf-banda-lnk mf-banda2-lnk">&#127928; <?php echo htmlspecialchars($banda2Nombre); ?></a>
        <?php else: ?>
        <span class="mf-banda-txt">&#127928; <?php echo htmlspecialchars($banda2Nombre); ?></span>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php endif; ?>

      <div class="mf-timeline">
        <?php if ($anioNac): ?>
        <div class="mf-tl-row mf-nac">
          <span class="mf-tl-dot">&#9679;</span>
          <div>
            <span class="mf-tl-f"><?php echo date('d/m/Y',strtotime($m->fecha_nacimiento)); ?></span>
            <?php if (!empty($m->lugar_nacimiento)): ?>
            <span class="mf-tl-l"><?php echo htmlspecialchars($m->lugar_nacimiento); ?></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($anioDef): ?>
        <?php if ($anioNac): ?><div class="mf-tl-line"></div><?php endif; ?>
        <div class="mf-tl-row mf-def">
          <span class="mf-tl-dot">&#10013;</span>
          <div>
            <span class="mf-tl-f"><?php echo date('d/m/Y',strtotime($m->fecha_defuncion)); ?></span>
            <?php if (!empty($m->lugar_defuncion)): ?>
            <span class="mf-tl-l"><?php echo htmlspecialchars($m->lugar_defuncion); ?></span>
            <?php endif; ?>
            <?php if ($anioNac): ?><span class="mf-tl-edad"><?php echo $anioDef-$anioNac; ?> años</span><?php endif; ?>
          </div>
        </div>
        <?php elseif ($anioNac): ?>
        <span class="mf-vive">EN ACTIVO &middot; <?php echo date('Y')-$anioNac; ?> años</span>
        <?php endif; ?>
      </div>

      <?php if (!empty($m->wikipedia_url)): ?>
      <a href="<?php echo htmlspecialchars($m->wikipedia_url); ?>" target="_blank" class="mf-wiki">W Wikipedia</a>
      <?php endif; ?>

      <!-- Contenido largo debajo -->
      <?php if (!empty($m->descripcion)): ?>
      <div class="mf-card mf-mt">
        <h3 class="mf-card-h">Biografía</h3>
        <div class="mf-bio"><?php echo $m->descripcion; ?></div>
      </div>
      <?php endif; ?>

      <?php if (!empty($this->efems)): ?>
      <div class="mf-card mf-mt">
        <h3 class="mf-card-h">&#128197; Efemérides (<?php echo count($this->efems); ?>)</h3>
        <div class="mf-efems">
          <?php foreach ($this->efems as $ef):
            $fv  = new DateTime($ef->fecha_evento);
            $col = $catColor((string)($ef->cat_nombre??''));
          ?>
          <div class="mf-efem" style="border-left-color:<?php echo $col; ?>">
            <span class="mf-efem-anio" style="color:<?php echo $col; ?>"><?php echo $fv->format('Y'); ?></span>
            <span class="mf-efem-fecha"><?php echo $fv->format('d/m'); ?></span>
            <div>
              <span class="mf-efem-tit"><?php echo htmlspecialchars($ef->titulo); ?></span>
              <?php if(!empty($ef->cat_nombre)): ?>
              <span class="mf-efem-badge" style="color:<?php echo $col; ?>"><?php echo htmlspecialchars($ef->cat_nombre); ?></span>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($this->frases)): ?>
      <div class="mf-card mf-mt">
        <h3 class="mf-card-h">&#8220; Frases míticas</h3>
        <?php foreach ($this->frases as $fr): ?>
        <blockquote class="mf-frase">
          <p>"<?php echo htmlspecialchars($fr->frase); ?>"</p>
        </blockquote>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div><!-- /col-datos -->

    <!-- SIDEBAR DERECHA -->
    <div class="mf-col-side">

      <?php if (!empty($this->compas)): ?>
      <div class="mf-card">
        <h3 class="mf-card-h">Compañeros de banda</h3>
        <div class="mf-compas">
          <?php foreach ($this->compas as $c):
            $cf=trim((string)($c->foto??''));
            if($cf&&strpos($cf,'http')!==0) $cf=$siteRoot.'/'.ltrim($cf,'/');
          ?>
          <a href="<?php echo Uri::current(); ?>?view=musico&id=<?php echo $c->id; ?>" class="mf-compa">
            <?php if($cf): ?>
            <img src="<?php echo htmlspecialchars($cf); ?>" alt="" class="mf-compa-foto"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="mf-compa-ph" style="display:none">&#127928;</div>
            <?php else: ?><div class="mf-compa-ph">&#127928;</div><?php endif; ?>
            <span class="mf-compa-n"><?php echo htmlspecialchars($c->nombre); ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($banda): ?>
      <div class="mf-card mf-mt">
        <h3 class="mf-card-h">Banda</h3>
        <a href="<?php echo htmlspecialchars(Uri::current()); ?>?view=banda&id=<?php echo $banda->id; ?>" class="mf-banda-card">
          <?php $bf=trim((string)($banda->foto??''));
          if($bf&&strpos($bf,'http')!==0) $bf=$siteRoot.'/'.ltrim($bf,'/');?>
          <?php if($bf): ?>
          <img src="<?php echo htmlspecialchars($bf); ?>" alt="" class="mf-banda-logo" onerror="this.style.display='none'">
          <?php endif; ?>
          <div>
            <strong class="mf-banda-cn"><?php echo htmlspecialchars($banda->nombre); ?></strong>
            <?php if(!empty($banda->genero)): ?><span class="mf-banda-cg"><?php echo htmlspecialchars($banda->genero); ?></span><?php endif; ?>
            <?php if(!empty($banda->pais)): ?><span class="mf-banda-cp">&#127758; <?php echo htmlspecialchars($banda->pais); ?></span><?php endif; ?>
          </div>
        </a>
      </div>
      <?php endif; ?>

    </div><!-- /col-side -->

  </div><!-- /mf-grid -->

  <div class="mf-footer">
    <a href="<?php echo Uri::current(); ?>?view=musicos" class="mf-lnk">&#8592; Músicos</a>
    &nbsp;·&nbsp;
    <a href="<?php echo Uri::current(); ?>?view=bandas" class="mf-lnk">Bandas</a>
    &nbsp;·&nbsp;
    <a href="<?php echo Uri::current(); ?>?view=efemerides" class="mf-lnk">Efemérides</a>
  </div>
</div>

<style>
.mf-wrap{max-width:1060px;margin:0 auto;font-family:inherit}

/* Topbar */
.mf-topbar{background:var(--dark, #0A0A0A);border:1px solid var(--steel, #1A1A1A);border-top:4px solid var(--fire, #CC2200);
  padding:10px 18px;display:flex;align-items:center;gap:10px}
.mf-tipo{font-size:.6rem;color:var(--fire, #CC2200);letter-spacing:.2em;font-weight:700;text-transform:uppercase;flex:1}
.mf-flag{font-size:1.3rem;line-height:1}
.mf-pais{font-size:.78rem;color:var(--grey-8, #888888);font-weight:600}

/* GRID: foto fija | datos flex | sidebar fija */
.mf-grid{display:grid;grid-template-columns:180px 1fr 250px;gap:0;
  background:var(--dark, #0A0A0A);border:1px solid var(--steel, #1A1A1A);border-top:none}

/* FOTO */
.mf-col-foto{padding:24px 16px;display:flex;flex-direction:column;align-items:center;
  border-right:1px solid var(--steel, #1A1A1A);background:var(--dark, #0A0A0A)}
.mf-foto{width:140px;height:140px;object-fit:cover;border-radius:50%;border:3px solid var(--fire, #CC2200);display:block}
.mf-foto-ph{width:140px;height:140px;border-radius:50%;background:var(--mid, #111111);border:3px solid var(--grey-3, #333333);
  display:flex;align-items:center;justify-content:center;font-size:3rem}

/* DATOS */
.mf-col-datos{padding:22px 22px 16px;border-right:1px solid var(--steel, #1A1A1A)}
.mf-nombre{font-size:1.7rem;font-weight:900;color:#fff;margin:0 0 12px;line-height:1.1}
.mf-instr{display:inline-flex;align-items:center;gap:7px;background:var(--mid, #111111);border:1px solid var(--steel, #1A1A1A);
  border-left:3px solid var(--gold-light, #DAA520);padding:5px 12px;margin-bottom:10px;font-size:.85rem}
.mf-instr-txt{color:var(--gold-light, #DAA520);font-weight:700}
.mf-banda-row{margin-bottom:10px}
.mf-banda-lnk{color:var(--gold-light, #DAA520);font-weight:700;text-decoration:none;font-size:.88rem}
.mf-banda-lnk:hover{color:#fff}
.mf-banda2-row{margin-top:4px}.mf-banda2-lnk{font-size:.75rem;color:var(--grey-a, #AAAAAA);opacity:.8}
.mf-solista-badge{font-size:.72rem;background:var(--mid, #111111);border:1px solid var(--grey-2, #2A2A2A);color:var(--text-dim, #777777);padding:3px 10px;letter-spacing:.06em;font-weight:600;display:inline-block}
.mf-banda-txt{color:var(--gold-light, #DAA520);font-size:.88rem}
.mf-banda2-row{margin-top:4px}.mf-banda2-lnk{font-size:.75rem;color:var(--grey-a, #AAAAAA);opacity:.8}
.mf-solista-badge{font-size:.72rem;background:var(--mid, #111111);border:1px solid var(--grey-2, #2A2A2A);color:var(--grey-8, #888888);padding:3px 10px;letter-spacing:.06em;font-weight:600}
/* Timeline */
.mf-timeline{margin:10px 0 14px;display:flex;flex-direction:column;gap:4px}
.mf-tl-row{display:flex;align-items:flex-start;gap:8px}
.mf-tl-dot{font-size:.85rem;margin-top:2px;flex-shrink:0}
.mf-nac .mf-tl-dot{color:var(--ok, #4CAF50)}
.mf-def .mf-tl-dot{color:#cc4444}
.mf-tl-f{font-size:.82rem;color:var(--grey-c, #CCCCCC);font-weight:600;display:block}
.mf-tl-l{font-size:.73rem;color:var(--text-dim, #777777);display:block;line-height:1.4}
.mf-tl-edad{font-size:.68rem;color:var(--grey-5, #555555);display:block;font-style:italic}
.mf-tl-line{width:1px;height:10px;background:var(--grey-3, #333333);margin-left:7px}
.mf-vive{font-size:.62rem;background:#0a1f0a;color:var(--ok, #4CAF50);padding:3px 9px;
  border-radius:2px;font-weight:700;letter-spacing:.1em;display:inline-block;margin-top:6px}
.mf-wiki{display:inline-block;background:var(--steel, #1A1A1A);border:1px solid var(--grey-2, #2A2A2A);color:var(--grey-a, #AAAAAA);
  padding:5px 14px;font-size:.74rem;font-weight:700;text-decoration:none}
.mf-wiki:hover{border-color:var(--fire, #CC2200);color:#fff}
.mf-mt{margin-top:14px}
/* Cards */
.mf-card{background:var(--mid, #111111);border:1px solid var(--steel, #1A1A1A)}
.mf-card-h{font-size:.72rem;font-weight:700;color:var(--fire, #CC2200);text-transform:uppercase;
  letter-spacing:.1em;margin:0;padding:9px 14px;border-bottom:1px solid var(--steel, #1A1A1A);background:var(--dark, #0A0A0A)}
.mf-bio{padding:14px;font-size:.84rem;color:var(--grey-a, #AAAAAA);line-height:1.8}
.mf-efems{padding:8px 12px;display:flex;flex-direction:column;gap:4px}
.mf-efem{display:flex;align-items:baseline;gap:8px;padding:6px 0;
  border-bottom:1px solid var(--dark, #0A0A0A);border-left:3px solid;padding-left:9px}
.mf-efem:last-child{border-bottom:none}
.mf-efem-anio{font-size:.95rem;font-weight:900;min-width:38px}
.mf-efem-fecha{font-size:.68rem;color:var(--grey-5, #555555);min-width:32px}
.mf-efem-tit{font-size:.8rem;color:var(--grey-d, #DDDDDD);font-weight:600}
.mf-efem-badge{font-size:.58rem;padding:1px 5px;border-radius:2px;margin-left:5px}
.mf-frase{border-left:3px solid var(--fire, #CC2200);padding:10px 14px;margin:0 0 6px;background:var(--dark, #0A0A0A)}
.mf-frase p{font-style:italic;color:var(--grey-a, #AAAAAA);font-size:.85rem;line-height:1.7;margin:0}

/* SIDEBAR */
.mf-col-side{padding:16px;display:flex;flex-direction:column;gap:14px;background:#080808}
.mf-compas{display:grid;grid-template-columns:repeat(auto-fill,minmax(72px,1fr));gap:8px;padding:10px}
.mf-compa{display:flex;flex-direction:column;align-items:center;gap:4px;
  text-decoration:none;padding:5px;transition:background .2s}
.mf-compa:hover{background:var(--wine-dark, #1A0000)}
.mf-compa-foto{width:54px;height:54px;object-fit:cover;border-radius:50%;border:2px solid var(--grey-2, #2A2A2A);display:block}
.mf-compa-ph{width:54px;height:54px;border-radius:50%;background:var(--steel, #1A1A1A);border:2px solid var(--grey-2, #2A2A2A);
  display:flex;align-items:center;justify-content:center;font-size:1.1rem}
.mf-compa-n{font-size:.62rem;color:var(--grey-a, #AAAAAA);text-align:center;line-height:1.3}
.mf-banda-card{display:flex;align-items:center;gap:10px;padding:12px;
  text-decoration:none;background:var(--dark, #0A0A0A);border:1px solid var(--steel, #1A1A1A);transition:border-color .2s}
.mf-banda-card:hover{border-color:var(--gold-light, #DAA520)}
.mf-banda-logo{max-height:48px;max-width:90px;object-fit:contain}
.mf-banda-cn{display:block;font-size:.85rem;color:#fff;font-weight:700}
.mf-banda-cg{display:block;font-size:.7rem;color:var(--grey-8, #888888);margin-top:2px}
.mf-banda-cp{display:block;font-size:.68rem;color:var(--grey-5, #555555);margin-top:2px}

/* Footer */
.mf-footer{text-align:center;padding:12px;border-top:1px solid var(--steel, #1A1A1A);font-size:.76rem}
.mf-lnk{color:var(--fire, #CC2200);text-decoration:none;font-weight:700;letter-spacing:.05em;text-transform:uppercase;font-size:.7rem}
.mf-lnk:hover{color:#ff2222}

/* Responsive */
@media(max-width:800px){
  .mf-grid{grid-template-columns:160px 1fr;grid-template-rows:auto auto}
  .mf-col-side{grid-column:1/-1;border-top:1px solid var(--steel, #1A1A1A);flex-direction:row;flex-wrap:wrap}
}
@media(max-width:560px){
  .mf-grid{grid-template-columns:1fr}
  .mf-col-foto{border-right:none;border-bottom:1px solid var(--steel, #1A1A1A);padding:20px}
}
</style>
