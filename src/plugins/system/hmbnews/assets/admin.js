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

window.hmbImport=function(){
    if(!C.hasKey){alert('Configura la API Key en los ajustes del componente.');return;}
    var act=S.filter(function(s){return s.active==1;});if(!act.length){alert('Activa al menos una fuente.');return;}
    var btn=document.getElementById('hmb-imp-btn'),lbl=document.getElementById('hmb-imp-lbl');
    btn.disabled=true;btn.classList.add('running');lbl.textContent='⏳ IMPORTANDO…';
    document.getElementById('hmb-log').innerHTML='';
    log('Iniciando importación…','info');log('Fuentes: '+act.map(function(s){return s.name;}).join(', '),'info');log('Esto puede tardar 30-60 segundos…','info');
    var n=document.getElementById('hmb-num').value,t=document.getElementById('hmb-type').value,c=document.getElementById('hmb-catid').value;
    post('importArticles',{num:n,content_type:t,catid:c}).then(function(d){
        if(d.success){log('✓ '+d.message,'ok');(d.articles||[]).forEach(function(a){log('  ✓ '+(a.title||'').substring(0,65),'ok');});hmbLoadArticles();}
        else log('✗ '+(d.message||'Error'),'err');
    }).catch(function(e){log('✗ Error de red: '+e.message,'err');}).finally(function(){btn.disabled=false;btn.classList.remove('running');lbl.textContent='⚡ IMPORTAR AHORA';});
};

window.hmbLoadArticles=function(){var c=document.getElementById('art-cont');c.innerHTML='<div class="hmb-loading">Cargando…</div>';get('getArticles').then(function(d){if(d.success){A=d.articles;rArt();document.getElementById('hmb-cnt').textContent=A.length;}}).catch(function(e){c.innerHTML='<p class="hmb-err">Error: '+e.message+'</p>';});};
function rArt(){var c=document.getElementById('art-cont');if(!A.length){c.innerHTML='<div class="hmb-empty"><span class="hmb-empty-icon">📭</span><p>Sin artículos importados.<br>Pulsa <b>IMPORTAR AHORA</b>.</p></div>';return;}var rows=A.map(function(a){var st=a.state==1?'<span class="st-pub">Publicado</span>':'<span class="st-un">Borrador</span>';var dt=(a.imported_at||'').substring(0,10);var eu='index.php?option=com_content&task=article.edit&id='+a.article_id;var th=a.thumb?'<img src="'+esc(a.thumb)+'" class="art-thumb" alt=""/>':'<span class="art-nothumb"></span>';var sl=a.source_url?'<a href="'+esc(a.source_url)+'" target="_blank" class="art-srclink" title="Ver original">↗</a>':'';return'<tr><td>'+th+'</td><td><a href="'+eu+'" class="art-title">'+esc(a.title||'Sin título')+'</a><span class="art-src">'+esc(a.source_site||'')+'</span>'+sl+'</td><td style="white-space:nowrap;color:#999;font-size:12px">'+dt+'</td><td>'+st+'</td><td class="art-acts"><a href="'+eu+'" class="hmb-btn hmb-edit">✎ Editar</a><button class="hmb-btn hmb-del" onclick="hmbDA('+a.import_id+','+a.article_id+')">✕</button></td></tr>';}).join('');c.innerHTML='<table class="hmb-table"><thead><tr><th style="width:64px">Imagen</th><th>Título / Fuente</th><th>Importado</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>'+rows+'</tbody></table>';}
window.hmbDA=function(iid,aid){if(!confirm('¿Eliminar este artículo de Joomla? No se puede deshacer.'))return;post('deleteArticle',{import_id:iid,article_id:aid}).then(function(d){if(d.success){A=A.filter(function(a){return a.import_id!=iid;});rArt();document.getElementById('hmb-cnt').textContent=A.length;log('Artículo ID '+aid+' eliminado.','ok');document.getElementById('hmb-log-card').style.display='';}else alert(d.message||'Error.');}).catch(function(e){alert('Error: '+e.message);});};
}());
