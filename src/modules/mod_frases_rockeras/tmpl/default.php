<?php
defined('_JEXEC') or die;
$mostrarFecha = (bool)$params->get('mostrar_fecha', 1);
$autorefresh  = (int)$params->get('autorefresh', 0);
$sufijo       = (string)$params->get('moduleclass_sfx', '');
static $css   = false;

$out = [];
foreach ($todasLasFrases as $f) {
    $nac = (!empty($f->fecha_nacimiento) && $f->fecha_nacimiento !== '0000-00-00') ? (int)date('Y',strtotime($f->fecha_nacimiento)) : 0;
    $def = (!empty($f->fecha_defuncion)  && $f->fecha_defuncion  !== '0000-00-00') ? (int)date('Y',strtotime($f->fecha_defuncion))  : 0;
    $anios = '';
    if ($mostrarFecha) {
        if ($nac && $def) $anios = '('.$nac.'-'.$def.')';
        elseif ($nac)     $anios = '('.$nac.'-)';
    }
    $mu = (string)($f->musico_url ?? '');
    $fu = (string)($f->foto_url   ?? '');
    $out[] = ['q'=>(string)$f->frase,'a'=>(string)$f->autor,'y'=>$anios,'u'=>$mu,'p'=>$fu,'i'=>(string)($f->musico_instrumento??'')];
}
shuffle($out);
$uid = 'sl'.substr(md5(uniqid('',true)),0,6);
$ms  = $autorefresh > 0 ? $autorefresh * 1000 : 0;
$jd  = json_encode($out, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE);
?>
<?php if (!$css): $css=true; ?>
<style>
.hsl{position:relative;background:#0a0a0a;border:1px solid #1e1e1e;border-left:4px solid #cc0000;max-width:1200px;margin:0 auto}
.hsl-deco{position:absolute;top:-8px;left:14px;font-size:5rem;color:#cc0000;opacity:.06;line-height:1;pointer-events:none;font-family:Georgia,serif}
.hsl-arr{position:absolute;top:50%;transform:translateY(-50%);width:44px;height:70px;background:#111;border:1px solid #2a2a2a;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:10;color:#666;font-size:1.4rem;outline:none;transition:all .18s}
.hsl-arr:hover{background:#1a0000;border-color:#cc0000;color:#cc0000;box-shadow:0 0 12px rgba(204,0,0,.35)}
.hsl-arr:active{transform:translateY(-50%) scale(.94)}
.hsl-prev{left:0;border-left:none}
.hsl-next{right:0;border-right:none}
.hsl-stage{margin:0 44px;padding:20px 22px 16px;min-height:130px;display:flex;align-items:center;gap:18px;transition:opacity .24s}
.hsl-stage.fade{opacity:0}
.hsl-fa,.hsl-fd{display:flex;width:72px;height:72px;border-radius:50%;overflow:hidden;border:2px solid #cc0000;background:#111;align-items:center;justify-content:center;flex-shrink:0;transition:border-color .2s,box-shadow .2s;text-decoration:none}
.hsl-fa:hover{border-color:#d4af37;box-shadow:0 0 12px rgba(212,175,55,.45)}
.hsl-fi{width:100%;height:100%;object-fit:cover;object-position:top;display:block}
.hsl-ph{font-size:1.8rem;color:#2a2a2a}
.hsl-body{flex:1;min-width:0}
.hsl-frase{font-size:1rem;color:#e0e0e0;line-height:1.75;font-style:italic;margin:0 0 14px;word-break:break-word}
.hsl-arow{display:flex;align-items:center;gap:10px}
.hsl-line{flex:1;height:1px;background:linear-gradient(90deg,#cc0000,transparent)}
.hsl-info{text-align:right}
.hsl-autor{display:block;font-size:.82rem;font-weight:800;color:#d4af37;letter-spacing:.06em;text-transform:uppercase;text-decoration:none}
.hsl-autor.lnk{animation:hglow 2.6s ease-in-out infinite}
.hsl-autor.lnk:hover{color:#fff;text-shadow:0 0 12px rgba(212,175,55,1)}
@keyframes hglow{0%,100%{text-shadow:0 0 4px rgba(212,175,55,.4)}50%{text-shadow:0 0 10px rgba(212,175,55,.9)}}
.hsl-instr{display:block;font-size:.64rem;color:#555;margin-top:2px}
.hsl-anios{display:block;font-size:.66rem;color:#444;margin-top:1px}
.hsl-foot{display:flex;align-items:center;justify-content:center;padding:8px 12px;background:#050505;border-top:1px solid #111;gap:6px}
.hsl-dot{width:6px;height:6px;border-radius:50%;background:#2a2a2a;border:none;padding:0;cursor:pointer;transition:all .2s;outline:none}
.hsl-dot.on{background:#cc0000;transform:scale(1.4)}
.hsl-empty{padding:24px;text-align:center;color:#555;font-style:italic;font-size:.85rem}
@media(max-width:500px){.hsl-arr{width:34px}.hsl-stage{margin:0 34px;padding:14px 12px;gap:10px}.hsl-fa,.hsl-fd{width:54px;height:54px}.hsl-frase{font-size:.9rem}}
</style>
<?php endif; ?>

<?php if (empty($out)): ?>
<div class="hsl <?php echo htmlspecialchars($sufijo); ?>"><div class="hsl-empty">No hay frases.</div></div>
<?php else: ?>
<div id="<?php echo $uid; ?>-wrap" class="hsl <?php echo htmlspecialchars($sufijo); ?>">
  <div class="hsl-deco">&#8220;</div>
  <button class="hsl-arr hsl-prev" id="<?php echo $uid; ?>-p">&#10094;</button>
  <div class="hsl-stage" id="<?php echo $uid; ?>-s">
    <div id="<?php echo $uid; ?>-f"></div>
    <div class="hsl-body">
      <p class="hsl-frase" id="<?php echo $uid; ?>-t"></p>
      <div class="hsl-arow">
        <div class="hsl-line"></div>
        <div class="hsl-info">
          <span id="<?php echo $uid; ?>-a"></span>
          <span class="hsl-instr" id="<?php echo $uid; ?>-i"></span>
          <span class="hsl-anios" id="<?php echo $uid; ?>-n"></span>
        </div>
      </div>
    </div>
  </div>
  <button class="hsl-arr hsl-next" id="<?php echo $uid; ?>-x">&#10095;</button>
  <div class="hsl-foot" id="<?php echo $uid; ?>-d"></div>
</div>
<script>
(function(){
var D=<?php echo $jd; ?>,uid='<?php echo $uid; ?>',ms=<?php echo $ms; ?>;
var st=document.getElementById(uid+'-s'),
    tE=document.getElementById(uid+'-t'),
    aE=document.getElementById(uid+'-a'),
    iE=document.getElementById(uid+'-i'),
    nE=document.getElementById(uid+'-n'),
    fE=document.getElementById(uid+'-f'),
    dE=document.getElementById(uid+'-d'),
    pp=document.getElementById(uid+'-p'),
    nx=document.getElementById(uid+'-x'),
    cur=0,tmr=null,dots=[],max=Math.min(D.length,12);
for(var j=0;j<max;j++){
  (function(k){var b=document.createElement('button');b.className='hsl-dot';b.onclick=function(){go(k);};dE.appendChild(b);dots.push(b);})(j);
}
function ud(i){var di=D.length<=12?i:Math.round(i/(D.length-1)*(max-1));dots.forEach(function(d,k){d.className='hsl-dot'+(k===di?' on':'');});}
function mkF(d){
  var w=d.u?document.createElement('a'):document.createElement('div');
  w.className=d.u?'hsl-fa':'hsl-fd';
  if(d.u){w.href=d.u;w.title=d.a;}
  if(d.p){var img=document.createElement('img');img.src=d.p;img.alt=d.a;img.className='hsl-fi';img.onerror=function(){w.innerHTML='<span class="hsl-ph">\u266a</span>';};w.appendChild(img);}
  else{var ph=document.createElement('span');ph.className='hsl-ph';ph.innerHTML='\u266a';w.appendChild(ph);}
  return w;
}
function mkA(d){
  var el=d.u?document.createElement('a'):document.createElement('span');
  el.className=d.u?'hsl-autor lnk':'hsl-autor';
  if(d.u){el.href=d.u;el.title=d.a;}
  el.textContent=d.a;
  return el;
}
function show(i){var d=D[i];tE.textContent='\u201C'+d.q+'\u201D';fE.innerHTML='';fE.appendChild(mkF(d));aE.innerHTML='';aE.appendChild(mkA(d));iE.textContent=d.i||'';nE.textContent=d.y||'';ud(i);}
function go(i,sk){if(i<0)i=D.length-1;if(i>=D.length)i=0;if(sk){cur=i;show(i);return;}st.classList.add('fade');setTimeout(function(){cur=i;show(i);st.classList.remove('fade');},240);}
function rst(){clearInterval(tmr);if(ms>0)tmr=setInterval(function(){go(cur+1);},ms);}
pp.onclick=function(){go(cur-1);rst();};nx.onclick=function(){go(cur+1);rst();};
st.addEventListener('touchstart',function(e){st._x=e.touches[0].clientX;},{passive:true});
st.addEventListener('touchend',function(e){var dx=e.changedTouches[0].clientX-st._x;if(Math.abs(dx)>40){go(dx<0?cur+1:cur-1);rst();}},{passive:true});
go(0,true);rst();
})();
</script>
<?php endif; ?>
