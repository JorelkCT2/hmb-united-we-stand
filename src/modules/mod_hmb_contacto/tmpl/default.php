<?php defined('_JEXEC') or die; ?>
<style>
.hmb-ct{--cr:var(--fire, #CC2200);--cr2:var(--blood, #8B0000);--cdk:var(--wine-black, #0A0000);--cdk2:var(--wine-black, #0A0000);--ht:var(--grey-e, #E0E0E0);--hm:var(--grey-5, #555555);font-family:'Oswald',Arial,sans-serif;background:var(--cdk);color:var(--ht);}
.hmb-ct *{box-sizing:border-box;}
.hmb-ct-hero{position:relative;min-height:300px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:var(--cdk2);border-bottom:3px solid var(--cr2);margin:-20px -20px 0;}
.hmb-ct-hero-bg{position:absolute;inset:0;background-size:cover;background-position:center;filter:brightness(.3) saturate(1.3);}
.hmb-ct-hero-ov{position:absolute;inset:0;background:linear-gradient(180deg,rgba(5,0,0,.2),rgba(5,0,0,.85));}
.hmb-ct-hero-cnt{position:relative;z-index:2;text-align:center;padding:3rem 1rem;}
.hmb-ct-hero h1{font-family:'Metal Mania',cursive;font-size:clamp(32px,7vw,80px);color:#fff;letter-spacing:3px;margin:0 0 .4rem;line-height:1;text-shadow:0 0 40px rgba(204,34,0,.7),0 4px 12px #000;}
.hmb-ct-hero h1::after{content:'';display:block;width:80px;height:3px;background:linear-gradient(90deg,transparent,var(--fire, #CC2200),transparent);margin:.5rem auto 0;}
.hmb-ct-hero p{font-size:clamp(11px,2vw,16px);letter-spacing:4px;text-transform:uppercase;color:rgba(200,200,200,.75);margin:0;}
.hmb-ct-wrap{display:grid;grid-template-columns:1fr 300px;max-width:1200px;margin:0 auto;padding:2rem;}
@media(max-width:860px){.hmb-ct-wrap{grid-template-columns:1fr;padding:1rem;}}
.hmb-ct-main{padding-right:2rem;}@media(max-width:860px){.hmb-ct-main{padding-right:0;margin-bottom:2rem;}}
.hmb-ct-sec{margin-bottom:2rem;}
.hmb-ct-sec-title{font-family:'Metal Mania',cursive;font-size:20px;color:#fff;letter-spacing:2px;border-bottom:2px solid var(--cr2);padding-bottom:.4rem;margin-bottom:1rem;}
.hmb-ct-prose{font-size:14px;line-height:1.8;color:rgba(210,210,210,.85);}
.hmb-ct-alert{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:3px;margin-bottom:1.5rem;font-size:14px;}
.hmb-ct-alert.ok{background:rgba(0,100,0,.15);border:1px solid #006600;color:#88dd88;}
.hmb-ct-alert.err{background:rgba(139,0,0,.15);border:1px solid var(--cr2);color:#ff8888;}
.hmb-ct-form{display:flex;flex-direction:column;gap:12px;}
.hmb-ct-row-2{display:grid;grid-template-columns:1fr 1fr;gap:10px;}@media(max-width:550px){.hmb-ct-row-2{grid-template-columns:1fr;}}
.hmb-ct-lbl{display:block;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--hm);margin-bottom:3px;}
.hmb-ct-inp,.hmb-ct-sel,.hmb-ct-ta{width:100%;background:var(--dark, #0A0A0A);border:1px solid var(--wine, #3A0000);border-radius:3px;color:var(--ht);padding:10px 13px;font-size:13px;font-family:'Oswald',sans-serif;outline:none;transition:border-color .2s;-webkit-appearance:none;appearance:none;}
.hmb-ct-inp:focus,.hmb-ct-sel:focus,.hmb-ct-ta:focus{border-color:var(--cr);}
.hmb-ct-sel{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath fill='%23CC2200' d='M5 6 0 0h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:30px;cursor:pointer;}
.hmb-ct-sel option{background:var(--dark, #0A0A0A);color:var(--ht);}
.hmb-ct-ta{min-height:130px;resize:vertical;line-height:1.6;}
.hmb-ct-btn{align-self:flex-start;display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,var(--cr),var(--cr2));border:none;border-radius:3px;color:#fff;font-family:'Oswald',sans-serif;font-size:13px;font-weight:700;letter-spacing:3px;text-transform:uppercase;padding:12px 28px;cursor:pointer;box-shadow:0 4px 20px rgba(139,0,0,.4);transition:all .25s;}
.hmb-ct-btn:hover{background:linear-gradient(135deg,var(--ember, #FF4500),var(--cr));transform:translateY(-2px);}
.hmb-ct-side{border-left:2px solid #150000;padding-left:2rem;}@media(max-width:860px){.hmb-ct-side{border-left:none;border-top:2px solid #150000;padding-left:0;padding-top:2rem;}}
.hmb-ct-card{background:var(--cdk2);border:1px solid #180000;border-top:2px solid var(--cr2);border-radius:2px;padding:1rem;margin-bottom:1rem;}
.hmb-ct-card-ttl{font-family:'Metal Mania',cursive;font-size:15px;color:#fff;letter-spacing:2px;margin-bottom:.8rem;}
.hmb-ct-wa{display:flex;flex-direction:column;gap:6px;}
.hmb-ct-wa-btn{display:flex;align-items:center;justify-content:center;gap:8px;background:rgba(37,211,102,.1);border:1px solid rgba(37,211,102,.35);border-radius:3px;color:#25d366;font-family:'Oswald',sans-serif;font-size:11px;letter-spacing:2px;text-transform:uppercase;padding:10px 12px;text-decoration:none;transition:all .2s;}
.hmb-ct-wa-btn:hover{background:rgba(37,211,102,.22);border-color:#25d366;color:#fff;}
.hmb-ct-email-a{display:flex;align-items:center;gap:8px;color:var(--grey-8, #888888);font-size:12px;text-decoration:none;padding:6px 0;transition:color .2s;word-break:break-all;}
.hmb-ct-email-a:hover{color:var(--cr);}
.hmb-ct-redes{display:flex;flex-direction:column;gap:4px;}
.hmb-ct-red{display:flex;align-items:center;gap:8px;color:var(--text-dim, #777777);font-size:12px;letter-spacing:1px;text-decoration:none;padding:7px 8px;border:1px solid #180000;border-radius:2px;transition:all .2s;}
.hmb-ct-red:hover{color:#fff;border-color:var(--cr2);background:rgba(139,0,0,.1);}
.hmb-ct-photos{display:grid;grid-template-columns:1fr 1fr;gap:3px;}
.hmb-ct-photo{aspect-ratio:1;overflow:hidden;cursor:pointer;border:1px solid #180000;}
.hmb-ct-photo img{width:100%;height:100%;object-fit:cover;display:block;filter:brightness(.8);transition:filter .3s,transform .4s;}
.hmb-ct-photo:hover img{filter:brightness(1.05);transform:scale(1.07);}
.hmb-ct-lb{display:none;position:fixed;inset:0;background:rgba(0,0,0,.93);z-index:99999;align-items:center;justify-content:center;}
.hmb-ct-lb.on{display:flex;}
.hmb-ct-lb img{max-width:92vw;max-height:86vh;object-fit:contain;border:2px solid var(--cr2);}
.hmb-ct-lb-x{position:fixed;top:14px;right:14px;background:var(--cr2);border:1px solid var(--cr);color:#fff;font-size:20px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:2px;z-index:100000;}
</style>

<div class="hmb-ct">

  <?php if ($heroUrl || $titulo): ?>
  <div class="hmb-ct-hero">
    <?php if ($heroUrl): ?><div class="hmb-ct-hero-bg" style="background-image:url('<?php echo htmlspecialchars($heroUrl,ENT_QUOTES);?>')"></div><?php endif; ?>
    <div class="hmb-ct-hero-ov"></div>
    <div class="hmb-ct-hero-cnt">
      <?php if ($titulo): ?><h1><?php echo htmlspecialchars($titulo,ENT_QUOTES);?></h1><?php endif; ?>
      <?php if ($subtit): ?><p><?php echo htmlspecialchars($subtit,ENT_QUOTES);?></p><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="hmb-ct-wrap">
    <div class="hmb-ct-main">

      <?php if ($formSent): ?>
      <div class="hmb-ct-alert <?php echo $formSuccess?'ok':'err';?>">
        <span><?php echo $formSuccess?'✔':'✕';?></span>
        <?php echo htmlspecialchars($formMsg,ENT_QUOTES);?>
      </div>
      <?php endif; ?>

      <?php if ($intro): ?><div class="hmb-ct-sec"><div class="hmb-ct-prose"><?php echo $intro;?></div></div><?php endif; ?>
      <?php if ($contr): ?>
      <div class="hmb-ct-sec">
        <h2 class="hmb-ct-sec-title">&#9670; Contratación</h2>
        <div class="hmb-ct-prose"><?php echo $contr;?></div>
      </div>
      <?php endif; ?>

      <?php if ($formActivo): ?>
      <div class="hmb-ct-sec">
        <h2 class="hmb-ct-sec-title">&#9660; Envíanos un mensaje</h2>
        <form class="hmb-ct-form" method="post" action="<?php echo htmlspecialchars($currentUrl,ENT_QUOTES);?>">
          <div class="hmb-ct-row-2">
            <div><label class="hmb-ct-lbl">Nombre *</label><input class="hmb-ct-inp" type="text" name="nombre" required placeholder="Tu nombre"></div>
            <div><label class="hmb-ct-lbl">Email *</label><input class="hmb-ct-inp" type="email" name="email" required placeholder="tu@email.com"></div>
          </div>
          <div class="hmb-ct-row-2">
            <div><label class="hmb-ct-lbl">Teléfono</label><input class="hmb-ct-inp" type="tel" name="telefono" placeholder="+34 600 000 000"></div>
            <div><label class="hmb-ct-lbl">Motivo *</label>
              <select class="hmb-ct-sel" name="motivo" required>
                <option value="" disabled selected>— Selecciona —</option>
                <?php foreach($motivos as $m):?><option value="<?php echo htmlspecialchars($m,ENT_QUOTES);?>"><?php echo htmlspecialchars($m,ENT_QUOTES);?></option><?php endforeach;?>
              </select>
            </div>
          </div>
          <div><label class="hmb-ct-lbl">Mensaje *</label><textarea class="hmb-ct-ta" name="mensaje" required placeholder="Cuéntanos lo que necesitas..."></textarea></div>
          <input type="hidden" name="<?php echo $token;?>" value="1">
          <input type="hidden" name="hmb_ct_submit" value="1">
          <button class="hmb-ct-btn" type="submit">&#9658; Enviar mensaje</button>
        </form>
      </div>
      <?php endif; ?>

    </div>

    <div class="hmb-ct-side">

      <?php if ($waNum || $waCan): ?>
      <div class="hmb-ct-card">
        <div class="hmb-ct-card-ttl">&#128172; WhatsApp</div>
        <div class="hmb-ct-wa">
          <?php if ($waNum): ?><a class="hmb-ct-wa-btn" href="https://wa.me/<?php echo $waNum;?>?text=<?php echo $waTxt;?>" target="_blank" rel="noopener">&#128241; Escríbenos</a><?php endif;?>
          <?php if ($waCan): ?><a class="hmb-ct-wa-btn" href="<?php echo htmlspecialchars($waCan,ENT_QUOTES);?>" target="_blank" rel="noopener">&#128226; Canal oficial</a><?php endif;?>
        </div>
      </div>
      <?php endif;?>

      <?php if ($emailVis && $emailDst): ?>
      <div class="hmb-ct-card">
        <div class="hmb-ct-card-ttl">&#9993; Email</div>
        <a class="hmb-ct-email-a" href="mailto:<?php echo htmlspecialchars($emailDst,ENT_QUOTES);?>">&#9993; <?php echo htmlspecialchars($emailDst,ENT_QUOTES);?></a>
      </div>
      <?php endif;?>

      <?php if (!empty($redes)): ?>
      <div class="hmb-ct-card">
        <div class="hmb-ct-card-ttl">&#9760; Síguenos</div>
        <div class="hmb-ct-redes">
          <?php foreach($redes as $label=>$url):?>
          <a class="hmb-ct-red" href="<?php echo htmlspecialchars($url,ENT_QUOTES);?>" target="_blank" rel="noopener noreferrer">&#9670; <?php echo htmlspecialchars($label,ENT_QUOTES);?></a>
          <?php endforeach;?>
        </div>
      </div>
      <?php endif;?>

      <?php if (!empty($fotos)): ?>
      <div class="hmb-ct-card">
        <div class="hmb-ct-card-ttl">&#9670; Fotos</div>
        <div class="hmb-ct-photos">
          <?php foreach($fotos as $i=>$f):?>
          <div class="hmb-ct-photo" onclick="hmbCtLb('<?php echo htmlspecialchars($f,ENT_QUOTES);?>')">
            <img src="<?php echo htmlspecialchars($f,ENT_QUOTES);?>" alt="Foto <?php echo $i+1;?>" loading="lazy">
          </div>
          <?php endforeach;?>
        </div>
      </div>
      <?php endif;?>

    </div>
  </div>
</div>

<div class="hmb-ct-lb" id="hmb-ct-lb">
  <div class="hmb-ct-lb-x" onclick="hmbCtLbClose()">&#10005;</div>
  <img src="" alt="" id="hmb-ct-lb-img">
</div>
<script>
(function(){
  var lb=document.getElementById('hmb-ct-lb'),lbi=document.getElementById('hmb-ct-lb-img');
  window.hmbCtLb=function(s){lbi.src=s;lb.classList.add('on');document.body.style.overflow='hidden';};
  window.hmbCtLbClose=function(){lb.classList.remove('on');document.body.style.overflow='';};
  lb.addEventListener('click',function(e){if(e.target===lb)hmbCtLbClose();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')hmbCtLbClose();});
})();
</script>
