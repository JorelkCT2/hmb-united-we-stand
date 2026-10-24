/* HMB News Importer – Admin JS v1.0.3 */
(function(){
'use strict';
var S=[],A=[],C={};
document.addEventListener('DOMContentLoaded',function(){C=window.HMB||{};hmbLoadSources();hmbLoadArticles();});
function post(t,d){var u=C.ajax+t,b=new FormData();b.append(C.token,'1');if(d)Object.keys(d).forEach(function(k){b.append(k,d[k]);});return fetch(u,{method:'POST',body:b}).then(function(r){if(!r.ok)throw new Error('HTTP '+r.status);return r.json();});}
function get(t){return fetch(C.ajax+t+'&'+C.token+'=1').then(function(r){if(!r.ok)throw new Error('HTTP '+r.status);return r.json();});}
function esc(s){return s?String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'):'';};
function log(m,t){var c=document.getElementById('hmb-log-card'),l=document.getElementById('hmb-log');c.style.display='';var p=document.createElement('p');p.className='log-'+(t||'info');p.textContent=new Date().toLocaleTimeString('es-ES')+'  '+m;l.appendChild(p);l.scrollTop=l.scrollHeight;}

window.hmbLoadSources=function(){get('getSources').then(function(d){if(d.success){S=d.sources;rSrc();}}).catch(function(e){document.getElementById('src-list').innerHTML='<p class="hmb-err">Error: '+e.message+'</p>';});};
function rSrc(){var el=document.getElementById('src-list');if(!S.length){el.innerHTML='<p class="hmb-hint">Sin fuentes. Añade una abajo.</p>';return;}el.innerHTML=S.map(function(s){return'<div class="src-card'+(s.active==1?' on':'')+'"><div class="src-info"><span class="src-name">'+esc(s.name)+'</span><span class="src-url">'+esc(s.url)+'</span></div><div class="src-acts"><button class="hmb-tog '+(s.active==1?'on':'off')+'" onclick="hmbTog('+s.id+','+(s.active==1?0:1)+')">'+(s.active==1?'ON':'OFF')+'</button><button class="hmb-ico" onclick="hmbOE('+s.id+')" title="Editar">✎</button><button class="hmb-ico del" onclick="hmbDS('+s.id+')" title="Eliminar">✕</button></div></div>';}).join('');}
window.hmbTog=function(id,a){post('toggleSource',{id:id,active:a}).then(function(d){if(d.success){var s=S.find(function(x){return x.id==id;});if(s)s.active=a;rSrc();}}).catch(function(e){alert('Error: '+e.message);});};
window.hmbAddSource=function(){var n=document.getElementById('ns-name').value.trim(),u=document.getElementById('ns-url').value.trim();if(!n||!u){alert('Introduce nombre y dominio.');return;}post('addSource',{name:n,url:u}).then(function(d){if(d.success){S.push(d.source);rSrc();document.getElementById('ns-name').value='';document.getElementById('ns-url').value='';}else alert(d.message||'Error.');}).catch(function(e){alert('Error: '+e.message);});};
window.hmbDS=function(id){if(!confirm('¿Eliminar esta fuente?'))return;post('deleteSource',{id:id}).then(function(d){if(d.success){S=S.filter(function(s){return s.id!=id;});rSrc();}}).catch(function(e){alert('Error: '+e.message);});};
window.hmbOE=function(id){var s=S.find(function(x){return x.id==id;});if(!s)return;document.getElementById('edit-id').value=s.id;document.getElementById('edit-name').value=s.name;document.getElementById('edit-url').value=s.url;document.getElementById('hmb-modal-bg').style.display='flex';};
window.hmbCloseModal=function(){document.getElementById('hmb-modal-bg').style.display='none';};
window.hmbSaveEdit=function(){var id=document.getElementById('edit-id').value,n=document.getElementById('edit-name').value.trim(),u=document.getElementById('edit-url').value.trim();if(!n||!u){alert('Completa todos los campos.');return;}post('updateSource',{id:id,name:n,url:u}).then(function(d){if(d.success){var s=S.find(function(x){return x.id==id;});if(s){s.name=n;s.url=u;}rSrc();hmbCloseModal();}else alert(d.message||'Error.');}).catch(function(e){alert('Error: '+e.message);});};

var CAND=[];
function busy(b,on){b.disabled=on;b.classList.toggle('running',on);}
window.hmbBuscar=function(){
    var act=S.filter(function(s){return s.active==1;});if(!act.length){alert('Activa al menos una fuente.');return;}
    var btn=document.getElementById('hmb-imp-btn'),lbl=document.getElementById('hmb-imp-lbl');
    busy(btn,true);lbl.textContent='⏳ BUSCANDO…';
    document.getElementById('hmb-log').innerHTML='';
    log('Leyendo el RSS de '+act.length+' fuente(s)… (sin coste)','info');
    post('buscar',{q:document.getElementById('hmb-q').value,dias:document.getElementById('hmb-dias').value,solo_prioritarias:document.getElementById('hmb-solo').checked?1:0}).then(function(d){
        if(!d.success){log('✗ '+(d.message||'Error'),'err');return;}
        (d.informe||[]).forEach(function(i){log((i.estado==='ok'?'  ':'⚠ ')+i.fuente+': '+(i.estado==='ok'?i.validas+' de '+i.total+' valen':i.estado),i.estado==='ok'?'info':'err');});
        CAND=d.candidatas||[];rCand();
        log('✓ '+CAND.length+' candidatas. Marca las que quieras importar.','ok');
    }).catch(function(e){log('✗ Error de red: '+e.message,'err');}).finally(function(){busy(btn,false);lbl.textContent='🔎 BUSCAR NOTICIAS';});
};
function rCand(){
    var card=document.getElementById('hmb-cand-card'),el=document.getElementById('cand-list');card.style.display='';
    document.getElementById('hmb-cand-cnt').textContent=CAND.length;
    if(!CAND.length){el.innerHTML='<div class="hmb-empty"><span class="hmb-empty-icon">🤷</span><p>Nada nuevo con esos filtros.<br>Prueba con más días o quita la palabra.</p></div>';document.getElementById('hmb-go-btn').disabled=true;return;}
    el.innerHTML=CAND.map(function(c,i){
        var im=c.imagen?'<img src="'+esc(c.imagen)+'" class="cand-img" referrerpolicy="no-referrer" loading="lazy" alt="" onerror="this.style.visibility=&quot;hidden&quot;"/>':'<span class="cand-img cand-noimg"></span>';
        return'<label class="cand"><input type="checkbox" class="cand-chk" data-i="'+i+'" onchange="hmbSelCnt()"/>'+im+'<span class="cand-txt"><b>'+esc(c.titulo)+'</b><span class="cand-meta">'+esc(c.fuente)+' · '+esc(c.fecha||'sin fecha')+' · <span class="cand-pts" title="'+esc(c.motivo)+'">★ '+c.puntos+'</span>'+(c.motivo?' · '+esc(c.motivo):'')+' · <a href="'+esc(c.url)+'" target="_blank" rel="noopener" onclick="event.stopPropagation()">original ↗</a></span><span class="cand-res">'+esc(c.resumen)+'</span></span></label>';
    }).join('');
    hmbSelCnt();
}
window.hmbSelCnt=function(){var n=document.querySelectorAll('.cand-chk:checked').length,b=document.getElementById('hmb-go-btn');b.disabled=!n;document.getElementById('hmb-go-lbl').textContent=n?'✍ REDACTAR E IMPORTAR '+n+' MARCADA'+(n>1?'S':''):'✍ REDACTAR E IMPORTAR LAS MARCADAS';};
window.hmbSelTodo=function(v){document.querySelectorAll('.cand-chk').forEach(function(c){c.checked=v;});hmbSelCnt();};
window.hmbImportarSel=function(){
    if(!C.hasKey){alert('Configura la API Key en los ajustes del componente.');return;}
    var marcadas=[].slice.call(document.querySelectorAll('.cand-chk:checked')).map(function(c){return +c.dataset.i;});
    var sel=marcadas.map(function(i){return CAND[i];});if(!sel.length)return;
    var btn=document.getElementById('hmb-go-btn'),lbl=document.getElementById('hmb-go-lbl');busy(btn,true);
    var ok=0,coste=0,k=0,hechas=[],cat=document.getElementById('hmb-catid').value,pub=document.getElementById('hmb-pub').checked?1:0;
    function sig(){
        if(k>=sel.length){
            log('Hecho: '+ok+' de '+sel.length+' importadas. Coste aprox. '+coste.toFixed(4)+' $.','ok');
            CAND=CAND.filter(function(c){return hechas.indexOf(c)<0;});rCand();
            lbl.textContent='✍ REDACTAR E IMPORTAR LAS MARCADAS';busy(btn,false);hmbSelCnt();hmbLoadArticles();return;}
        var c=sel[k++];lbl.textContent='⏳ '+k+'/'+sel.length+'…';
        log('['+k+'/'+sel.length+'] '+c.titulo.substring(0,70),'info');
        post('importarUna',{url:c.url,titulo:c.titulo,fuente:c.fuente,fecha:c.fecha,imagen:c.imagen,resumen:c.resumen,texto:c.texto,catid:cat,publicar:pub}).then(function(d){
            if(d.coste)coste+=d.coste;
            if(d.success){ok++;hechas.push(c);log('  ✓ '+d.title.substring(0,70)+(d.imagen?'  (con imagen)':'  (sin imagen)'),'ok');if(d.hashtags)log('    '+d.hashtags,'info');}
            else log('  ✗ '+d.message,'err');
        }).catch(function(e){log('  ✗ Error de red: '+e.message,'err');}).finally(sig);
    }
    document.getElementById('hmb-log-card').style.display='';sig();
};
window.hmbRepararImg=function(){
    if(!confirm('Se descargará la imagen real de cada artículo que tenga la imagen rota (gratis, sin IA). ¿Seguir?'))return;
    var tot=0,sin=0,rotos=[];
    document.getElementById('hmb-log-card').style.display='';document.getElementById('hmb-log').innerHTML='';
    function paso(){post('repararImagenes').then(function(d){
        if(!d.success){log('✗ '+d.message,'err');return;}
        tot+=d.arreglados;sin+=d.sin_imagen;rotos=rotos.concat(d.enlace_roto||[]);
        log('Reparadas '+tot+' · sin imagen '+sin+' · quedan '+d.quedan,'info');
        if(d.quedan>0&&(d.arreglados+d.sin_imagen)>0)paso();
        else{log('✓ Terminado. Con enlace original roto (posible noticia inventada): '+rotos.length+(rotos.length?' → IDs '+rotos.join(', '):''),'ok');hmbLoadArticles();}
    }).catch(function(e){log('✗ Error de red: '+e.message,'err');});}
    paso();
};

window.hmbSort=window.hmbSort||'imported';
window.hmbSetSort=function(s){window.hmbSort=s;window.hmbLoadArticles();};
window.hmbLoadArticles=function(){var c=document.getElementById('art-cont');c.innerHTML='<div class="hmb-loading">Cargando…</div>';get('getArticles&sort='+window.hmbSort).then(function(d){if(d.success){A=d.articles;rArt();document.getElementById('hmb-cnt').textContent=A.length;}}).catch(function(e){c.innerHTML='<p class="hmb-err">Error: '+e.message+'</p>';});};
function rArt(){var c=document.getElementById('art-cont');if(!A.length){c.innerHTML='<div class="hmb-empty"><span class="hmb-empty-icon">📭</span><p>Sin artículos importados.<br>Busca noticias y marca las que quieras.</p></div>';return;}var rows=A.map(function(a){var st=a.state==1?'<span class="st-pub">Publicado</span>':'<span class="st-un">Borrador</span>';var dt=(a.imported_at||'').substring(0,10);var nd=(a.news_date||'').substring(0,10)||'—';var eu='index.php?option=com_content&task=article.edit&id='+a.article_id;var th=a.thumb?'<img src="'+esc(/^https?:/i.test(a.thumb)?a.thumb:'../'+a.thumb)+'" class="art-thumb" referrerpolicy="no-referrer" alt=""/>':'<span class="art-nothumb"></span>';var sl=a.source_url?'<a href="'+esc(a.source_url)+'" target="_blank" class="art-srclink" title="Ver original">↗</a>':'';return'<tr><td>'+th+'</td><td><a href="'+eu+'" class="art-title">'+esc(a.title||'Sin título')+'</a><span class="art-src">'+esc(a.source_site||'')+'</span>'+sl+'</td><td style="white-space:nowrap;color:#b58900;font-size:12px" title="Fecha de la noticia">'+nd+'</td><td style="white-space:nowrap;color:#999;font-size:12px" title="Fecha de importacion">'+dt+'</td><td>'+st+'</td><td>'+(a.hashtags?'<button class="hmb-btn" title="'+esc(a.hashtags)+'" onclick="hmbCopiarTags('+a.import_id+')">⧉ #tags</button>':'')+'</td><td class="art-acts"><a href="'+eu+'" class="hmb-btn hmb-edit">✎ Editar</a><button class="hmb-btn hmb-del" onclick="hmbDA('+a.import_id+','+a.article_id+')">✕</button></td></tr>';}).join('');c.innerHTML='<table class="hmb-table"><thead><tr><th style="width:64px">Imagen</th><th>Título / Fuente</th><th style="cursor:pointer;user-select:none" onclick="hmbSetSort(\'news\')" title="Ordenar por fecha de la noticia">Noticia '+(window.hmbSort==='news'?'▼':'')+'</th><th style="cursor:pointer;user-select:none" onclick="hmbSetSort(\'imported\')" title="Ordenar por fecha de importación">Importado '+(window.hmbSort==='imported'?'▼':'')+'</th><th>Estado</th><th>Etiquetas</th><th>Acciones</th></tr></thead><tbody>'+rows+'</tbody></table>';}
window.hmbCopiarTags=function(iid){var a=A.find(function(x){return x.import_id==iid;});if(!a||!a.hashtags)return;
    var ok=function(){log('Copiado: '+a.hashtags,'ok');document.getElementById('hmb-log-card').style.display='';};
    if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(a.hashtags).then(ok);}
    else{var t=document.createElement('textarea');t.value=a.hashtags;document.body.appendChild(t);t.select();document.execCommand('copy');document.body.removeChild(t);ok();}};
window.hmbDA=function(iid,aid){if(!confirm('¿Eliminar este artículo de Joomla? No se puede deshacer.'))return;post('deleteArticle',{import_id:iid,article_id:aid}).then(function(d){if(d.success){A=A.filter(function(a){return a.import_id!=iid;});rArt();document.getElementById('hmb-cnt').textContent=A.length;log('Artículo ID '+aid+' eliminado.','ok');document.getElementById('hmb-log-card').style.display='';}else alert(d.message||'Error.');}).catch(function(e){alert('Error: '+e.message);});};
}());
