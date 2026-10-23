<?php defined('_JEXEC') or die; ?>

<style>
#hmb-wrap-<?php echo $editorId; ?>{
  --hbg:#111;--hbd:#2a2a2a;--hr:#CC2200;--hr2:#8B0000;
  --ht:#E0E0E0;--hm:#555;--hg:#DAA520;
  font-family:'Segoe UI',Arial,sans-serif;
  background:var(--hbg);border:2px solid var(--hbd);border-radius:4px;overflow:hidden;
}
#hmb-wrap-<?php echo $editorId; ?> *{box-sizing:border-box;}
.hbar-<?php echo $editorId; ?>{display:flex;flex-wrap:wrap;gap:4px;align-items:center;background:#0d0d0d;padding:8px;border-bottom:1px solid var(--hbd);}
.hb-<?php echo $editorId; ?>{background:#1a1a1a;border:1px solid #333;color:var(--ht);border-radius:3px;padding:5px 10px;font-size:12px;cursor:pointer;font-family:inherit;transition:all .15s;white-space:nowrap;display:inline-flex;align-items:center;gap:4px;}
.hb-<?php echo $editorId; ?>:hover{background:#252525;border-color:var(--hr);color:#fff;}
.hsep-<?php echo $editorId; ?>{width:1px;background:#2a2a2a;height:24px;margin:0 2px;flex-shrink:0;}
.hyt-<?php echo $editorId; ?>{background:#1a1200;border-color:var(--hg)!important;color:var(--hg)!important;}
.hyt-<?php echo $editorId; ?>:hover{background:#2a1e00!important;}
.hcar-<?php echo $editorId; ?>{background:#001a10;border-color:#006633!important;color:#00cc66!important;}
.hcar-<?php echo $editorId; ?>:hover{background:#002a18!important;}
.hmode-<?php echo $editorId; ?>{margin-left:auto;background:#0a0a0a;border:1px solid #333;border-radius:3px;overflow:hidden;display:flex;}
.hmode-<?php echo $editorId; ?> button{background:transparent;border:none;color:var(--hm);padding:5px 11px;font-size:11px;cursor:pointer;font-family:inherit;letter-spacing:1px;transition:all .15s;}
.hmode-<?php echo $editorId; ?> button.on{background:var(--hr2);color:#fff;}
.hvis-<?php echo $editorId; ?>{min-height:<?php echo max(200,(int)($height??400)); ?>px;max-height:700px;overflow-y:auto;padding:20px 24px;outline:none;color:var(--ht);font-size:15px;line-height:1.8;caret-color:#fff;word-break:break-word;}
.hvis-<?php echo $editorId; ?>[contenteditable=true]:empty::before{content:'Escribe aquí o pega desde Word...';color:#333;pointer-events:none;}
.hcod-<?php echo $editorId; ?>{display:none;width:100%;min-height:<?php echo max(200,(int)($height??400)); ?>px;max-height:700px;background:#060606;color:#a8d8a8;font-family:'Fira Code','Consolas',monospace;font-size:13px;line-height:1.7;padding:16px;border:none;outline:none;resize:none;}
.hvis-<?php echo $editorId; ?> .hsc{display:inline-block;border-radius:3px;padding:2px 8px;font-size:12px;font-family:monospace;cursor:default;user-select:none;vertical-align:middle;margin:1px 3px;}
.hsc-yt{background:#001a00;border:1px solid #005500;color:#00cc00;}
.hsc-car{background:#001a0a;border:1px solid #006633;color:#00cc66;}
.hfot-<?php echo $editorId; ?>{display:flex;justify-content:space-between;padding:5px 12px;background:#0a0a0a;border-top:1px solid #1a1a1a;font-size:11px;color:#444;letter-spacing:1px;}
.htags-<?php echo $editorId; ?>{border-top:2px solid #1a0000;background:#0a0000;padding:10px 14px;}
.htags-lbl-<?php echo $editorId; ?>{font-size:10px;color:var(--hm);letter-spacing:2px;text-transform:uppercase;margin-bottom:6px;}
.htags-in-<?php echo $editorId; ?>{width:100%;background:#0d0d0d;border:1px solid #333;border-radius:3px;color:var(--ht);padding:7px 10px;font-size:13px;font-family:inherit;outline:none;margin-bottom:6px;}
.htags-in-<?php echo $editorId; ?>:focus{border-color:var(--hr);}
.htags-hint-<?php echo $editorId; ?>{font-size:11px;color:#3a3a3a;margin-bottom:6px;}
.hchips-<?php echo $editorId; ?>{display:flex;flex-wrap:wrap;gap:5px;min-height:24px;}
.hchip-<?php echo $editorId; ?>{display:inline-flex;align-items:center;gap:4px;background:#1a0000;border:1px solid var(--hr2);color:var(--hr);border-radius:20px;font-size:11px;padding:3px 10px;}
.hchip-x-<?php echo $editorId; ?>{cursor:pointer;font-size:14px;opacity:.7;line-height:1;}

/* ── Overlay compartido ── */
.hov-<?php echo $editorId; ?>{display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:99999;align-items:center;justify-content:center;}
.hov-<?php echo $editorId; ?>.open{display:flex;}
.hmod-<?php echo $editorId; ?>{background:#111;border:1px solid var(--hr2);border-top:3px solid var(--hr);border-radius:6px;padding:28px;width:95%;max-width:820px;max-height:88vh;overflow-y:auto;}
.hmod-<?php echo $editorId; ?> h3{font-size:17px;color:#fff;margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid #2a0000;}
.hmod-in-<?php echo $editorId; ?>{width:100%;background:#0d0d0d;border:1px solid #333;border-radius:3px;color:var(--ht);padding:10px 14px;font-size:14px;font-family:inherit;outline:none;margin-bottom:14px;}
.hmod-in-<?php echo $editorId; ?>:focus{border-color:var(--hr);}
.hmod-btns-<?php echo $editorId; ?>{display:flex;gap:8px;justify-content:flex-end;}

/* ── Filas de fotos del carrusel ── */
.hcar-rows-<?php echo $editorId; ?>{display:flex;flex-direction:column;gap:10px;margin-bottom:14px;}
.hcar-row-<?php echo $editorId; ?>{display:grid;grid-template-columns:2fr 1.2fr 1.2fr auto;gap:8px;align-items:center;background:#0d0d0d;border:1px solid #222;border-radius:4px;padding:10px;}
.hcar-row-<?php echo $editorId; ?> input{background:#111;border:1px solid #2a2a2a;border-radius:3px;color:var(--ht);padding:8px 11px;font-size:13px;font-family:inherit;outline:none;width:100%;}
.hcar-row-<?php echo $editorId; ?> input:focus{border-color:#006633;}
.hcar-row-<?php echo $editorId; ?> input::placeholder{color:#333;}
.hcar-row-<?php echo $editorId; ?> input.hcar-src-<?php echo $editorId; ?>{color:#888;font-size:11px;}
.hcar-row-<?php echo $editorId; ?> input.hcar-src-<?php echo $editorId; ?>:focus{border-color:#555;}
.hcar-prev-<?php echo $editorId; ?>{width:60px;height:46px;flex-shrink:0;border:1px solid #222;border-radius:3px;overflow:hidden;background:#0a0a0a;display:flex;align-items:center;justify-content:center;}
.hcar-prev-<?php echo $editorId; ?> img{width:100%;height:100%;object-fit:cover;display:block;}
.hcar-prev-<?php echo $editorId; ?> .hcar-prev-empty{font-size:18px;color:#2a2a2a;}
.hcar-prev-<?php echo $editorId; ?> .hcar-prev-err{font-size:10px;color:#cc0000;text-align:center;padding:2px;}
.hcar-row-<?php echo $editorId; ?>{display:grid;grid-template-columns:60px 2fr 1.2fr 1.2fr auto;gap:8px;align-items:center;background:#0d0d0d;border:1px solid #222;border-radius:4px;padding:8px;}
.hcar-del-<?php echo $editorId; ?>{background:#1a0000;border:1px solid #330000;color:#660000;border-radius:3px;width:28px;height:28px;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s,color .15s;}
.hcar-del-<?php echo $editorId; ?>:hover{background:#3a0000;color:#cc0000;}
.hcar-add-<?php echo $editorId; ?>{background:#001a0a;border:1px solid #006633;color:#00cc66;border-radius:3px;padding:7px 14px;font-size:12px;cursor:pointer;font-family:inherit;transition:background .15s;margin-bottom:14px;}
.hcar-add-<?php echo $editorId; ?>:hover{background:#002a18;}
.hcar-hint-<?php echo $editorId; ?>{font-size:11px;color:#2a2a2a;margin-bottom:12px;line-height:1.6;}
</style>

<textarea id="hmb_hidden_<?php echo $editorId; ?>"
  name="<?php echo $fieldName; ?>"
  style="display:none;"
  cols="<?php echo (int)($col??50); ?>"
  rows="<?php echo (int)($row??20); ?>"><?php echo $safeContent; ?></textarea>

<div id="hmb-wrap-<?php echo $editorId; ?>">
  <div class="hbar-<?php echo $editorId; ?>">
    <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.f('bold')"><b>B</b></button>
    <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.f('italic')"><i>I</i></button>
    <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.f('underline')"><u>U</u></button>
    <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.f('strikeThrough')"><s>S</s></button>
    <div class="hsep-<?php echo $editorId; ?>"></div>
    <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.bl('h2')">H2</button>
    <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.bl('h3')">H3</button>
    <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.bl('p')">¶</button>
    <div class="hsep-<?php echo $editorId; ?>"></div>
    <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.f('insertUnorderedList')">☰</button>
    <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.f('insertOrderedList')">1.</button>
    <div class="hsep-<?php echo $editorId; ?>"></div>
    <button type="button" class="hb-<?php echo $editorId; ?>" style="background:#0d0d1a;border-color:#4455aa!important;color:#8899ff!important;" onclick="hmb<?php echo $editorId; ?>.lnkOpen()" title="Insertar enlace (Ctrl+K)">&#128279; Enlace</button>
    <div class="hsep-<?php echo $editorId; ?>"></div>
    <button type="button" class="hb-<?php echo $editorId; ?> hyt-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.ytOpen()">▶ YouTube</button>
    <button type="button" class="hb-<?php echo $editorId; ?> hcar-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.carOpen()">🖼 Carrusel fotos</button>
    <button type="button" class="hb-<?php echo $editorId; ?>" style="background:#000d1a;border-color:#3366cc!important;color:#6699ff!important;" onclick="hmb<?php echo $editorId; ?>.modInsert()">&#9635; Módulo</button>
    <div class="hsep-<?php echo $editorId; ?>"></div>
    <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.clean()">✕ Word</button>
    <div class="hmode-<?php echo $editorId; ?>">
      <button type="button" id="hbv-<?php echo $editorId; ?>" class="on" onclick="hmb<?php echo $editorId; ?>.mode('visual')">✏ Visual</button>
      <button type="button" id="hbc-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.mode('code')">&lt;/&gt; HTML</button>
    </div>
  </div>

  <div class="hvis-<?php echo $editorId; ?>"
    id="hvis-<?php echo $editorId; ?>"
    contenteditable="true"
    onpaste="hmb<?php echo $editorId; ?>.paste(event)"
    oninput="hmb<?php echo $editorId; ?>.sync()"
    onkeydown="hmb<?php echo $editorId; ?>.kd(event)"></div>

  <textarea class="hcod-<?php echo $editorId; ?>"
    id="hcod-<?php echo $editorId; ?>"
    oninput="hmb<?php echo $editorId; ?>.syncCode()"
    spellcheck="false"></textarea>

  <div class="hfot-<?php echo $editorId; ?>">
    <span id="hwc-<?php echo $editorId; ?>">0 palabras</span>
    <span id="hcc-<?php echo $editorId; ?>">0 caracteres</span>
  </div>

  <div class="htags-<?php echo $editorId; ?>">
    <div class="htags-lbl-<?php echo $editorId; ?>">🏷 Tags del artículo</div>
    <input type="text"
      class="htags-in-<?php echo $editorId; ?>"
      id="htin-<?php echo $editorId; ?>"
      placeholder="#HeavyMetal, #Conciertos, #Cartagena..."
      onkeydown="if(event.key==='Enter'||event.key===','){event.preventDefault();hmb<?php echo $editorId; ?>.tagAdd(this.value,true);}"
      onblur="hmb<?php echo $editorId; ?>.tagAdd(this.value,true);">
    <div class="htags-hint-<?php echo $editorId; ?>">Escribe con # y pulsa Enter o coma para confirmar.</div>
    <div class="hchips-<?php echo $editorId; ?>" id="hchips-<?php echo $editorId; ?>"></div>
  </div>
</div>

<!-- Modal Enlace -->
<div class="hov-<?php echo $editorId; ?>" id="hov-lnk-<?php echo $editorId; ?>">
  <div class="hmod-<?php echo $editorId; ?>" style="max-width:520px;">
    <h3>&#128279; Insertar Enlace</h3>

    <label style="font-size:11px;color:#555;letter-spacing:1px;display:block;margin-bottom:5px;">URL <span style="color:#CC2200;">*</span></label>
    <input type="text" class="hmod-in-<?php echo $editorId; ?>"
      id="hlnk-url-<?php echo $editorId; ?>"
      placeholder="https://www.heavymetalbrothers.com"
      onkeydown="if(event.key==='Enter') document.getElementById('hlnk-txt-<?php echo $editorId; ?>').focus();">

    <label style="font-size:11px;color:#555;letter-spacing:1px;display:block;margin-bottom:5px;">Texto del enlace <span style="color:#555;">(si está vacío usa el texto seleccionado)</span></label>
    <input type="text" class="hmod-in-<?php echo $editorId; ?>"
      id="hlnk-txt-<?php echo $editorId; ?>"
      placeholder="Texto visible del enlace"
      onkeydown="if(event.key==='Enter') hmb<?php echo $editorId; ?>.lnkInsert();">

    <label style="font-size:11px;color:#555;letter-spacing:1px;display:block;margin-bottom:8px;">Abrir en</label>
    <div style="display:flex;gap:8px;margin-bottom:18px;">
      <label style="display:flex;align-items:center;gap:6px;font-family:inherit;font-size:13px;color:var(--ht);cursor:pointer;">
        <input type="radio" name="hlnk-target-<?php echo $editorId; ?>" value="_self" checked
          style="accent-color:#CC2200;"> Misma pestaña
      </label>
      <label style="display:flex;align-items:center;gap:6px;font-family:inherit;font-size:13px;color:var(--ht);cursor:pointer;">
        <input type="radio" name="hlnk-target-<?php echo $editorId; ?>" value="_blank"
          style="accent-color:#CC2200;"> Nueva pestaña
      </label>
    </div>

    <div class="hmod-btns-<?php echo $editorId; ?>">
      <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.lnkClose()">Cancelar</button>
      <button type="button" class="hb-<?php echo $editorId; ?>"
        style="background:#0d0d1a;border-color:#4455aa!important;color:#8899ff!important;"
        onclick="hmb<?php echo $editorId; ?>.lnkInsert()">&#128279; Insertar enlace</button>
    </div>
  </div>
</div>

<!-- Modal YouTube -->
<div class="hov-<?php echo $editorId; ?>" id="hov-yt-<?php echo $editorId; ?>">
  <div class="hmod-<?php echo $editorId; ?>">
    <h3>▶ Insertar Vídeo de YouTube</h3>
    <label style="font-size:11px;color:#555;letter-spacing:1px;display:block;margin-bottom:5px;">URL O ID DEL VÍDEO</label>
    <input type="text" class="hmod-in-<?php echo $editorId; ?>"
      id="hyin-<?php echo $editorId; ?>"
      placeholder="https://www.youtube.com/watch?v=..."
      onkeydown="if(event.key==='Enter') hmb<?php echo $editorId; ?>.ytInsert();">
    <div style="font-size:11px;color:#444;margin-bottom:14px;">Pega la URL en cualquier formato.</div>
    <div class="hmod-btns-<?php echo $editorId; ?>">
      <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.ytClose()">Cancelar</button>
      <button type="button" class="hb-<?php echo $editorId; ?> hyt-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.ytInsert()">▶ Insertar</button>
    </div>
  </div>
</div>

<!-- Modal Carrusel Fotos -->
<div class="hov-<?php echo $editorId; ?>" id="hov-car-<?php echo $editorId; ?>">
  <div class="hmod-<?php echo $editorId; ?>">
    <h3>🖼 Insertar Carrusel de Fotos</h3>
    <div class="hcar-hint-<?php echo $editorId; ?>">
      Escribe la ruta o URL de cada foto y un título opcional.<br>
      Las rutas de Joomla: <code style="color:#006633;font-size:11px;">images/mifoto.jpg</code>
    </div>
    <div style="display:grid;grid-template-columns:60px 2fr 1.2fr 1.2fr auto;gap:8px;margin-bottom:6px;padding:0 2px;">
      <span style="font-size:11px;color:#444;letter-spacing:1px;text-transform:uppercase;">Vista</span>
      <span style="font-size:11px;color:#444;letter-spacing:1px;text-transform:uppercase;">Ruta / URL de la foto</span>
      <span style="font-size:11px;color:#444;letter-spacing:1px;text-transform:uppercase;">Título (opcional)</span>
      <span style="font-size:11px;color:#444;letter-spacing:1px;text-transform:uppercase;">Fuente (auto)</span>
      <span></span>
    </div>
    <div class="hcar-rows-<?php echo $editorId; ?>" id="hcar-rows-<?php echo $editorId; ?>"></div>
    <button type="button" class="hcar-add-<?php echo $editorId; ?>" id="hcar-add-<?php echo $editorId; ?>">＋ Añadir foto</button>
    <div class="hmod-btns-<?php echo $editorId; ?>">
      <button type="button" class="hb-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.carClose()">Cancelar</button>
      <button type="button" class="hb-<?php echo $editorId; ?> hcar-<?php echo $editorId; ?>" onclick="hmb<?php echo $editorId; ?>.carInsert()">🖼 Insertar carrusel</button>
    </div>
  </div>
</div>

<script>
window['hmb<?php echo $editorId; ?>'] = (function(){
  var EID  = '<?php echo $editorId; ?>';
  var area = document.getElementById('hvis-'+EID);
  var code = document.getElementById('hcod-'+EID);
  var hid  = document.getElementById('hmb_hidden_'+EID);
  var mode = 'visual';
  var _r   = null;
  var _tags= [];

  var init = <?php echo $jsContent; ?>;
  if(init && init.trim()) area.innerHTML = init;

  function sR(){ var s=window.getSelection(); if(s&&s.rangeCount) _r=s.getRangeAt(0).cloneRange(); }
  function rR(){ if(!_r) return; var s=window.getSelection(); s.removeAllRanges(); s.addRange(_r); }
  area.addEventListener('mouseup',sR); area.addEventListener('keyup',sR);

  function sync(){
    var cl=area.cloneNode(true);
    cl.querySelectorAll('.hsc[data-sc]').forEach(function(el){ el.parentNode.replaceChild(document.createTextNode(el.dataset.sc),el); });
    hid.value=cl.innerHTML;
    var t=area.innerText||'';
    document.getElementById('hwc-'+EID).textContent=(t.trim()?t.trim().split(/\s+/).length:0)+' palabras';
    document.getElementById('hcc-'+EID).textContent=t.length+' caracteres';
  }

  function syncCode(){ hid.value=code.value; document.getElementById('hwc-'+EID).textContent='— palabras'; document.getElementById('hcc-'+EID).textContent=code.value.length+' caracteres'; }

  function setMode(m){
    if(m==='code'){
      var cl=area.cloneNode(true); cl.querySelectorAll('.hsc[data-sc]').forEach(function(el){el.parentNode.replaceChild(document.createTextNode(el.dataset.sc),el);}); code.value=cl.innerHTML;
      area.style.display='none'; code.style.display='block';
      document.getElementById('hbv-'+EID).classList.remove('on');
      document.getElementById('hbc-'+EID).classList.add('on');
      mode='code'; code.focus();
    } else {
      area.innerHTML=code.value; hid.value=code.value;
      code.style.display='none'; area.style.display='';
      document.getElementById('hbc-'+EID).classList.remove('on');
      document.getElementById('hbv-'+EID).classList.add('on');
      mode='visual'; area.focus(); sync();
    }
  }

  function fmt(cmd){ if(mode!=='visual') return; area.focus(); document.execCommand(cmd,false,null); sync(); }
  function blk(tag){ if(mode!=='visual') return; area.focus(); document.execCommand('formatBlock',false,tag); sync(); }

  function clean(){
    if(mode==='code'){code.value=code.value.replace(/<[^>]+>/g,''); syncCode(); return;}
    area.innerHTML='<p>'+(area.innerText||'').replace(/\n{2,}/g,'</p><p>').replace(/\n/g,' ')+'</p>'; sync();
  }

  function paste(e){
    e.preventDefault(); var html='';
    if(e.clipboardData){ html=e.clipboardData.getData('text/html'); if(!html){var t=e.clipboardData.getData('text/plain');html='<p>'+t.replace(/\n{2,}/g,'</p><p>').replace(/\n/g,'<br>')+'</p>';} }
    html=html.replace(/<style[^>]*>[\s\S]*?<\/style>/gi,'').replace(/<script[^>]*>[\s\S]*?<\/script>/gi,'').replace(/<!--[\s\S]*?-->/g,'').replace(/<o:[^>]*>[\s\S]*?<\/o:[^>]*>/gi,'').replace(/class="[^"]*mso[^"]*"/gi,'').replace(/style="[^"]*(?:mso|font-size|color|margin|padding)[^"]*"/gi,'').replace(/<span>/gi,'').replace(/<\/span>/gi,'').replace(/<font[^>]*>/gi,'').replace(/<\/font>/gi,'').replace(/&nbsp;/g,' ').replace(/<p[^>]*>\s*<\/p>/gi,'');
    var sel=window.getSelection();
    if(sel&&sel.rangeCount){var r=sel.getRangeAt(0); r.deleteContents(); var frag=document.createRange().createContextualFragment(html); r.insertNode(frag); r.collapse(false); sel.removeAllRanges(); sel.addRange(r);}
    else{area.innerHTML+=html;}
    sync();
  }

  function chip(sc,label,cls){
    var sp=document.createElement('span'); sp.className='hsc '+cls; sp.contentEditable='false'; sp.dataset.sc=sc; sp.textContent=label; sp.title=sc;
    var sel=window.getSelection();
    if(sel&&sel.rangeCount){var r=sel.getRangeAt(0); r.deleteContents(); r.insertNode(sp); r.setStartAfter(sp); r.collapse(true); sel.removeAllRanges(); sel.addRange(r);}
    else{area.appendChild(sp);}
    sp.insertAdjacentText('afterend','\u00A0'); sync();
  }

  /* ── Enlace ── */
  function lnkOpen(){
    sR();
    var sel = window.getSelection().toString().trim();
    document.getElementById('hlnk-url-'+EID).value = '';
    document.getElementById('hlnk-txt-'+EID).value = sel;
    /* Reset target a _self */
    var radios = document.querySelectorAll('[name="hlnk-target-'+EID+'"]');
    radios.forEach(function(r){ r.checked = r.value === '_self'; });
    document.getElementById('hov-lnk-'+EID).classList.add('open');
    setTimeout(function(){ document.getElementById('hlnk-url-'+EID).focus(); }, 80);
  }
  function lnkClose(){
    document.getElementById('hov-lnk-'+EID).classList.remove('open');
    area.focus();
  }
  function lnkInsert(){
    var url = document.getElementById('hlnk-url-'+EID).value.trim();
    if(!url){ alert('Introduce una URL.'); return; }
    /* Añadir https:// si no tiene protocolo */
    if(!/^https?:\/\//i.test(url) && !/^mailto:/i.test(url) && !/^tel:/i.test(url)){
      url = 'https://' + url;
    }
    var txt    = document.getElementById('hlnk-txt-'+EID).value.trim();
    var target = document.querySelector('[name="hlnk-target-'+EID+'"]:checked');
    var tVal   = target ? target.value : '_self';
    var tAttr  = tVal === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
    /* Si hay texto seleccionado y no se escribió texto, envuelve la selección */
    lnkClose(); rR(); area.focus();
    if(txt){
      document.execCommand('insertHTML', false, '<a href="'+url+'"'+tAttr+'>'+txt+'</a>');
    } else {
      /* Envolver texto seleccionado */
      var sel2 = window.getSelection();
      if(sel2 && sel2.rangeCount && !sel2.getRangeAt(0).collapsed){
        document.execCommand('createLink', false, url);
        /* Aplicar target si es _blank */
        if(tVal === '_blank'){
          var links = area.querySelectorAll('a[href="'+url+'"]');
          links.forEach(function(a){ a.target='_blank'; a.rel='noopener noreferrer'; });
        }
      } else {
        document.execCommand('insertHTML', false, '<a href="'+url+'"'+tAttr+'>'+url+'</a>');
      }
    }
    sync();
  }

  /* ── YouTube ── */
  function ytOpen(){ sR(); document.getElementById('hyin-'+EID).value=''; document.getElementById('hov-yt-'+EID).classList.add('open'); setTimeout(function(){document.getElementById('hyin-'+EID).focus();},80); }
  function ytClose(){ document.getElementById('hov-yt-'+EID).classList.remove('open'); area.focus(); }
  function ytInsert(){
    var url=document.getElementById('hyin-'+EID).value.trim(); if(!url){alert('Introduce una URL.'); return;}
    var m,vid=null;
    if((m=url.match(/youtu\.be\/([a-zA-Z0-9_\-]{11})/))) vid=m[1];
    else if((m=url.match(/[?&]v=([a-zA-Z0-9_\-]{11})/))) vid=m[1];
    else if((m=url.match(/embed\/([a-zA-Z0-9_\-]{11})/))) vid=m[1];
    else if((m=url.match(/shorts\/([a-zA-Z0-9_\-]{11})/))) vid=m[1];
    else if(/^[a-zA-Z0-9_\-]{11}$/.test(url)) vid=url;
    if(!vid){alert('No se pudo extraer el ID del vídeo.'); return;}
    ytClose(); rR(); area.focus(); chip('{hmb_video youtube='+vid+'}','▶ youtube: '+vid,'hsc-yt');
  }

  /* ── Carrusel Fotos ── */
  var carRowCount = 0;
  var MAX_FOTOS   = 10;

  function carAddRow(url, titulo, fuente){
    var rows = document.getElementById('hcar-rows-'+EID);
    if(rows.children.length >= MAX_FOTOS) return;
    carRowCount++;
    var n = carRowCount;
    var row = document.createElement('div');
    row.className = 'hcar-row-'+EID;
    row.id = 'hcar-row-'+EID+'-'+n;
    row.innerHTML =
      '<div class="hcar-prev-'+EID+'" id="hcar-prev-'+EID+'-'+n+'"><span class="hcar-prev-empty">&#128444;</span></div>' +
      '<input type="text" id="hcar-url-'+EID+'-'+n+'" placeholder="images/foto.jpg o https://..." value="'+(url||'')+'">' +
      '<input type="text" id="hcar-tit-'+EID+'-'+n+'" placeholder="Título opcional" value="'+(titulo||'')+'">' +
      '<input type="text" id="hcar-src-'+EID+'-'+n+'" class="hcar-src-'+EID+'" placeholder="Fuente (auto)" value="'+(fuente||'')+'">' +
      '<button type="button" class="hcar-del-'+EID+'" onclick="hmb'+EID+'.carDelRow(\''+n+'\')">✕</button>';
    rows.appendChild(row);
    /* Auto-extraer dominio al escribir/pegar URL */
    var urlIn = document.getElementById('hcar-url-'+EID+'-'+n);
    var srcIn = document.getElementById('hcar-src-'+EID+'-'+n);
    var prevEl = document.getElementById('hcar-prev-'+EID+'-'+n);
    function updatePreview(){
      var v=urlIn.value.trim(); if(!v){prevEl.innerHTML='<span class="hcar-prev-empty">&#128444;</span>';return;}
      var img=document.createElement('img');
      img.onload=function(){prevEl.innerHTML='';prevEl.appendChild(img);};
      img.onerror=function(){prevEl.innerHTML='<span class="hcar-prev-err">&#10060; Error</span>';};
      img.src=v; img.alt='';
    }
    function autoSrc(){ var v=urlIn.value.trim(); if(!srcIn.dataset.manual&&v.match(/^https?:\/\//i)){try{srcIn.value=new URL(v).origin;}catch(e){}} }
    function onUrlChange(){ autoSrc(); clearTimeout(urlIn._pt); urlIn._pt=setTimeout(updatePreview,600); }
    urlIn.addEventListener('input', onUrlChange);
    urlIn.addEventListener('paste', function(){ setTimeout(onUrlChange, 15); });
    srcIn.addEventListener('input', function(){ this.dataset.manual='1'; });
    /* Si ya hay URL al abrir, autorellenar fuente y preview */
    if(url && !fuente) autoSrc();
    if(url) updatePreview();
    document.getElementById('hcar-url-'+EID+'-'+n).focus();
    document.getElementById('hcar-add-'+EID).style.display = rows.children.length >= MAX_FOTOS ? 'none' : '';
  }

  function carDelRow(n){
    var row = document.getElementById('hcar-row-'+EID+'-'+n);
    if(row) row.parentNode.removeChild(row);
    document.getElementById('hcar-add-'+EID).style.display = '';
  }

  function carOpen(){
    sR();
    /* Limpiar filas anteriores */
    var rows = document.getElementById('hcar-rows-'+EID);
    rows.innerHTML = '';
    carRowCount = 0;
    document.getElementById('hcar-add-'+EID).style.display = '';
    /* Añadir 1 fila vacía por defecto */
    carAddRow('','');
    document.getElementById('hov-car-'+EID).classList.add('open');
  }

  function carClose(){
    document.getElementById('hov-car-'+EID).classList.remove('open');
    area.focus();
  }

  function carInsert(){
    var rows = document.getElementById('hcar-rows-'+EID).children;
    var lines = [];
    for(var i=0; i<rows.length; i++){
      var n   = rows[i].id.replace('hcar-row-'+EID+'-','');
      var url = (document.getElementById('hcar-url-'+EID+'-'+n)||{}).value||'';
      var tit = (document.getElementById('hcar-tit-'+EID+'-'+n)||{}).value||'';
      var src = (document.getElementById('hcar-src-'+EID+'-'+n)||{}).value||'';
      url = url.trim(); tit = tit.trim(); src = src.trim();
      if(url){
        var line = url;
        if(tit||src) line += '|' + tit;
        if(src)      line += '|' + src;
        lines.push(line);
      }
    }
    if(!lines.length){ alert('Añade al menos una foto.'); return; }
    var sc = '{hmb_carrusel}\n' + lines.join('\n') + '\n{/hmb_carrusel}';
    var label = '🖼 Carrusel (' + lines.length + ' foto' + (lines.length>1?'s':'') + ')';
    carClose(); rR(); area.focus();
    chip(sc, label, 'hsc-car');
  }

  function modInsert(){ sR(); rR(); area.focus(); chip('{loadmoduleid 125}','&#9635; Carrusel de fotos','hsc-car'); }

  document.getElementById('hcar-add-'+EID).addEventListener('click', function(){ carAddRow('',''); });
  document.getElementById('hov-car-'+EID).addEventListener('click',function(e){if(e.target===this) carClose();});
  document.getElementById('hov-yt-'+EID).addEventListener('click',function(e){if(e.target===this) ytClose();});
  document.getElementById('hov-lnk-'+EID).addEventListener('click',function(e){if(e.target===this) lnkClose();});
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){
      ytClose();
      carClose();
      lnkClose();
    }
  });

  function kd(e){
    if(e.ctrlKey||e.metaKey){
      if(e.key==='b'){e.preventDefault();document.execCommand('bold',false,null);}
      if(e.key==='i'){e.preventDefault();document.execCommand('italic',false,null);}
      if(e.key==='u'){e.preventDefault();document.execCommand('underline',false,null);}
      if(e.key==='k'){e.preventDefault();lnkOpen();}
    }
    sR();
  }

  /* ── Tags ── */
  function tagAdd(val,commit){
    val.split(/[,\s]+/).forEach(function(p){ p=p.trim().replace(/^#+/,''); if(p.length>1&&!_tags.includes(p)) _tags.push(p); });
    if(commit) document.getElementById('htin-'+EID).value='';
    renderChips(); syncTagsJoomla();
  }
  function tagDel(i){ _tags.splice(i,1); renderChips(); syncTagsJoomla(); }
  function renderChips(){
    var c=document.getElementById('hchips-'+EID); c.innerHTML='';
    _tags.forEach(function(t,i){
      var ch=document.createElement('span'); ch.className='hchip-'+EID;
      ch.innerHTML='#'+t.replace(/&/g,'&amp;')+' <span class="hchip-x-'+EID+'" onclick="hmb'+EID+'.tagDel('+i+')">×</span>';
      c.appendChild(ch);
    });
  }
  function syncTagsJoomla(){
    var sel=document.querySelector('select#jform_tags,select[name="jform[tags][]"]');
    if(!sel) return;
    _tags.forEach(function(name){
      var found=false;
      for(var i=0;i<sel.options.length;i++){ if(sel.options[i].text.trim().toLowerCase()===name.toLowerCase()){sel.options[i].selected=true;found=true;break;} }
      if(!found){ var o=document.createElement('option'); o.value='#new#'+name; o.text=name; o.selected=true; sel.appendChild(o); }
    });
    var evt=new Event('change',{bubbles:true}); sel.dispatchEvent(evt);
    if(typeof jQuery!=='undefined'){try{jQuery(sel).trigger('change').trigger('chosen:updated');}catch(e){}}
  }

  /* ── Joomla editor API ── */
  if(typeof Joomla!=='undefined'&&Joomla.editors){
    Joomla.editors.instances[hid.name]={
      getValue:function(){return hid.value;},
      setValue:function(v){area.innerHTML=v;hid.value=v;},
      getSelection:function(){return window.getSelection().toString();},
      replaceSelection:function(v){rR();area.focus();document.execCommand('insertHTML',false,v);sync();}
    };
  }

  sync();

  return {
    f:fmt, bl:blk, clean:clean, paste:paste, sync:sync, syncCode:syncCode,
    mode:setMode, ytOpen:ytOpen, ytClose:ytClose, ytInsert:ytInsert,
    carOpen:carOpen, carClose:carClose, carInsert:carInsert, carDelRow:carDelRow,
    modInsert:modInsert, lnkOpen:lnkOpen, lnkClose:lnkClose, lnkInsert:lnkInsert,
    kd:kd, tagAdd:tagAdd, tagDel:tagDel
  };
})();
</script>
