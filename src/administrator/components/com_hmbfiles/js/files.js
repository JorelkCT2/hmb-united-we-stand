/* HMB Archivos – gestor de images/ y media/ (solo super administradores) */
(function () {
    'use strict';

    var C = window.HMBFILES;
    // Cualquier fallo de JavaScript se muestra en pantalla (sin esto, un error silencioso parece que "no hace nada")
    window.addEventListener('error', function (e) {
        var m = document.getElementById('hf-msg');
        if (m) { m.textContent = 'Error en la página: ' + e.message + ' (' + String(e.filename || '').split('/').pop() + ':' + e.lineno + ')'; m.className = 'hf-msg hf-msg-err'; m.hidden = false; }
    });
    var S = { dir: 'images', carpetas: [], ficheros: [], sel: {}, filtro: '', papelera: false, limite: 0, cola: [] };
    var $ = function (id) { return document.getElementById(id); };

    function esc(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function tam(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
        if (b < 1073741824) return (b / 1048576).toFixed(1) + ' MB';
        return (b / 1073741824).toFixed(2) + ' GB';
    }
    function fecha(t) {
        var d = new Date(t * 1000);
        return d.toLocaleDateString('es-ES') + ' ' + d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    }
    function msg(texto, tipo) {
        var m = $('hf-msg');
        m.textContent = texto;
        m.className = 'hf-msg hf-msg-' + (tipo || 'ok');
        m.hidden = false;
        clearTimeout(msg.t);
        msg.t = setTimeout(function () { m.hidden = true; }, tipo === 'err' ? 9000 : 3500);
    }

    // ── Red ──────────────────────────────────────────────────────────────
    function url(task, params) {
        var u = C.ajax + task + '&' + C.token + '=1';
        Object.keys(params || {}).forEach(function (k) { u += '&' + k + '=' + encodeURIComponent(params[k]); });
        return u;
    }
    function leer(task, params) {
        return fetch(url(task, params), { credentials: 'same-origin' }).then(function (r) { return r.json(); });
    }
    function enviar(task, datos) {
        var fd = new FormData();
        fd.append(C.token, '1');
        Object.keys(datos || {}).forEach(function (k) { fd.append(k, datos[k]); });
        return fetch(C.ajax + task, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
    }

    // ── Modal ────────────────────────────────────────────────────────────
    function modal(titulo, html, alAceptar, textoOk) {
        $('hf-modal-t').textContent = titulo;
        $('hf-modal-c').innerHTML = html;
        $('hf-modal-ok').textContent = textoOk || 'Aceptar';
        $('hf-modal-ok').hidden = !alAceptar;
        $('hf-modal').hidden = false;
        var inp = $('hf-modal-c').querySelector('input[type=text]');
        if (inp) { inp.focus(); inp.select(); }
        function cerrar() { $('hf-modal').hidden = true; $('hf-modal-ok').onclick = $('hf-modal-no').onclick = null; }
        $('hf-modal-no').onclick = cerrar;
        $('hf-modal-ok').onclick = function () { if (alAceptar && alAceptar() !== false) cerrar(); };
        if (inp) inp.onkeydown = function (e) { if (e.key === 'Enter') $('hf-modal-ok').click(); };
    }

    // ── Carga y pintado ──────────────────────────────────────────────────
    function cargar(dir) {
        S.papelera = false;
        S.sel = {};
        leer('lista', { dir: dir || S.dir }).then(function (d) {
            if (!d.success) { msg(d.message || 'Error', 'err'); return; }
            S.dir = d.dir; S.carpetas = d.carpetas; S.ficheros = d.ficheros; S.limite = d.limite;
            pintar();
        }).catch(function (e) { msg('Error de red: ' + e.message, 'err'); });
    }

    function pintarMigas() {
        var partes = S.dir.split('/'), acum = '', h = [];
        partes.forEach(function (p, i) {
            acum += (i ? '/' : '') + p;
            h.push('<a href="#" data-dir="' + esc(acum) + '">' + esc(p) + '</a>');
        });
        $('hf-crumbs').innerHTML = S.papelera ? '<b>Papelera</b> <small>(se vacía sola a los 30 días)</small>' : h.join(' <span>/</span> ');
        document.querySelectorAll('.hf-tab').forEach(function (t) {
            var r = t.dataset.raiz;
            t.classList.toggle('on', S.papelera ? r === '__papelera' : r === S.dir.split('/')[0]);
        });
    }

    function icono(f) {
        if (f.tipo === 'imagen') return '<img class="hf-th" src="' + esc(f.url) + '" loading="lazy" alt="">';
        return '<span class="hf-ic">' + ({ video: '🎬', audio: '🎵', doc: '📄' }[f.tipo] || '📦') + '</span>';
    }

    function pintar() {
        pintarMigas();
        $('hf-b-subir').hidden = $('hf-b-mkdir').hidden = $('hf-filtro').hidden = false;
        var q = S.filtro.toLowerCase();
        var carp = S.carpetas.filter(function (c) { return !q || c.nombre.toLowerCase().indexOf(q) >= 0; });
        var fich = S.ficheros.filter(function (f) { return !q || f.nombre.toLowerCase().indexOf(q) >= 0; });
        var h = '<table class="hf-t"><thead><tr><th class="hf-c1"><input type="checkbox" id="hf-todo" title="Marcar todo"></th><th></th><th>Nombre</th><th>Tamaño</th><th>Modificado</th><th></th></tr></thead><tbody>';

        if (S.dir.indexOf('/') >= 0) {
            h += '<tr><td></td><td><span class="hf-ic">⬆</span></td><td><a href="#" data-dir="' + esc(S.dir.replace(/\/[^/]*$/, '')) + '">.. (carpeta superior)</a></td><td></td><td></td><td></td></tr>';
        }
        carp.forEach(function (c) {
            h += '<tr class="' + (S.sel[c.rel] ? 'sel' : '') + '"><td class="hf-c1"><input type="checkbox" class="hf-ck" data-rel="' + esc(c.rel) + '"' + (S.sel[c.rel] ? ' checked' : '') + '></td>' +
                '<td><span class="hf-ic">📁</span></td><td><a href="#" data-dir="' + esc(c.rel) + '"><b>' + esc(c.nombre) + '</b></a></td>' +
                '<td>' + c.elementos + ' elem.</td><td>' + fecha(c.fecha) + '</td>' +
                '<td class="hf-ac"><button class="hf-i" data-a="ren" data-rel="' + esc(c.rel) + '" data-n="' + esc(c.nombre) + '" title="Renombrar">✎</button></td></tr>';
        });
        fich.forEach(function (f) {
            h += '<tr class="' + (S.sel[f.rel] ? 'sel' : '') + '"><td class="hf-c1"><input type="checkbox" class="hf-ck" data-rel="' + esc(f.rel) + '"' + (S.sel[f.rel] ? ' checked' : '') + '></td>' +
                '<td>' + icono(f) + '</td><td>' + (f.tipo === 'imagen' ? '<a href="' + esc(f.url) + '" target="_blank" rel="noopener">' + esc(f.nombre) + '</a>' : esc(f.nombre)) + '</td>' +
                '<td>' + tam(f.tamano) + '</td><td>' + fecha(f.fecha) + '</td>' +
                '<td class="hf-ac"><button class="hf-i" data-a="url" data-url="' + esc(f.url) + '" title="Copiar dirección web">🔗</button>' +
                '<button class="hf-i" data-a="desc" data-rel="' + esc(f.rel) + '" title="Descargar">⬇</button>' +
                '<button class="hf-i" data-a="ren" data-rel="' + esc(f.rel) + '" data-n="' + esc(f.nombre) + '" title="Renombrar">✎</button></td></tr>';
        });
        if (!carp.length && !fich.length) h += '<tr><td colspan="6" class="hf-vacio">' + (q ? 'Nada coincide con el filtro.' : 'Carpeta vacía. Arrastra archivos aquí o pulsa «Subir archivos».') + '</td></tr>';
        h += '</tbody></table>';
        $('hf-lista').innerHTML = h;
        $('hf-pie').textContent = carp.length + ' carpetas · ' + fich.length + ' archivos · tamaño máximo por archivo: ' + tam(S.limite);
        selUi();
    }

    function selUi() {
        var n = Object.keys(S.sel).length;
        $('hf-sel-acts').hidden = S.papelera || !n;
        $('hf-sel-n').textContent = n + ' marcado' + (n === 1 ? '' : 's');
    }

    // ── Papelera ─────────────────────────────────────────────────────────
    function verPapelera() {
        S.papelera = true; S.sel = {};
        pintarMigas(); selUi();
        $('hf-b-subir').hidden = $('hf-b-mkdir').hidden = $('hf-filtro').hidden = true;
        leer('papelera').then(function (d) {
            if (!d.success) { msg(d.message, 'err'); return; }
            var h = '<table class="hf-t"><thead><tr><th></th><th>Nombre</th><th>Estaba en</th><th>Tamaño</th><th>Borrado</th><th></th></tr></thead><tbody>';
            d.items.forEach(function (i) {
                h += '<tr><td><span class="hf-ic">' + (i.esDir ? '📁' : '📄') + '</span></td><td>' + esc(i.nombre) + '</td><td>' + esc(i.rel.replace(/\/[^/]*$/, '')) + '</td><td>' + tam(i.tamano) + '</td>' +
                    '<td>' + fecha(i.fecha) + ' · ' + esc(i.usuario) + '</td><td class="hf-ac"><button class="hf-btn" data-a="rest" data-id="' + esc(i.id) + '">↩ Restaurar</button></td></tr>';
            });
            if (!d.items.length) h += '<tr><td colspan="6" class="hf-vacio">La papelera está vacía.</td></tr>';
            h += '</tbody></table>' + (d.items.length ? '<p><button class="hf-btn hf-peligro" data-a="vaciar">Vaciar papelera (definitivo)</button></p>' : '');
            $('hf-lista').innerHTML = h;
            $('hf-pie').textContent = d.items.length + ' elementos';
        });
    }

    // ── Subida (cola, de uno en uno) ─────────────────────────────────────
    function subir(files) {
        files = [].slice.call(files);
        if (!files.length) { msg('El navegador no ha entregado ningún archivo.', 'err'); return; }
        if (S.papelera) { msg('Sal de la papelera para subir archivos.', 'err'); return; }
        if (!files.length || S.papelera) return;
        var dir = S.dir, cola = $('hf-cola'), ok = 0, k = 0;
        cola.hidden = false;
        cola.innerHTML = files.map(function (f, i) { return '<div id="hf-q' + i + '" class="hf-q"><span>' + esc(f.name) + ' (' + tam(f.size) + ')</span><b>en cola</b><i><u></u></i></div>'; }).join('');

        function sig() {
            if (k >= files.length) {
                msg(ok + ' de ' + files.length + ' archivos subidos.', ok === files.length ? 'ok' : 'err');
                setTimeout(function () { cola.hidden = true; }, 4000);
                if (S.dir === dir) cargar(dir);
                return;
            }
            var f = files[k], row = $('hf-q' + k), est = row.querySelector('b'), barra = row.querySelector('u'); k++;
            if (f.size > S.limite && S.limite) { est.textContent = 'supera ' + tam(S.limite); row.classList.add('err'); sig(); return; }
            var fd = new FormData(), x = new XMLHttpRequest();
            fd.append(C.token, '1'); fd.append('dir', dir); fd.append('archivo', f);
            x.open('POST', C.ajax + 'subir');
            x.upload.onprogress = function (e) { if (e.lengthComputable) { barra.style.width = Math.round(e.loaded * 100 / e.total) + '%'; est.textContent = Math.round(e.loaded * 100 / e.total) + '%'; } };
            x.onload = function () {
                var d; try { d = JSON.parse(x.responseText); } catch (e) { d = { success: false, message: 'respuesta no válida (¿archivo demasiado grande?)' }; }
                if (d.success) { ok++; est.textContent = 'subido'; row.classList.add('ok'); barra.style.width = '100%'; }
                else { est.textContent = d.message; row.classList.add('err'); }
                sig();
            };
            x.onerror = function () { est.textContent = 'error de red'; row.classList.add('err'); sig(); };
            x.send(fd);
        }
        sig();
    }

    // ── Acciones ─────────────────────────────────────────────────────────
    function nuevaCarpeta() {
        modal('Nueva carpeta en ' + S.dir, '<input type="text" class="hf-inp hf-ancho" placeholder="nombre-de-carpeta">', function () {
            var n = $('hf-modal-c').querySelector('input').value.trim();
            if (!n) return false;
            enviar('carpeta', { dir: S.dir, nombre: n }).then(function (d) { d.success ? (msg('Carpeta creada.'), cargar()) : msg(d.message, 'err'); });
        }, 'Crear');
    }
    function renombrar(rel, actual) {
        modal('Renombrar', '<input type="text" class="hf-inp hf-ancho" value="' + esc(actual) + '"><p class="hf-ayuda">Se quitan acentos y símbolos. No se puede cambiar a un tipo no permitido.</p>', function () {
            var n = $('hf-modal-c').querySelector('input').value.trim();
            if (!n) return false;
            enviar('renombrar', { rel: rel, nombre: n }).then(function (d) { d.success ? (msg('Renombrado.'), cargar()) : msg(d.message, 'err'); });
        }, 'Renombrar');
    }
    function marcadas() { return Object.keys(S.sel); }
    function mover() {
        var rels = marcadas(); if (!rels.length) return;
        leer('arbol').then(function (d) {
            var h = '<div class="hf-arbol">' + d.carpetas.map(function (c) {
                return '<label style="padding-left:' + (c.nivel * 18) + 'px"><input type="radio" name="hf-dest" value="' + esc(c.rel) + '"' + (c.rel === S.dir ? ' checked' : '') + '> 📁 ' + esc(c.rel.split('/').pop()) + '</label>';
            }).join('') + '</div>';
            modal('Mover ' + rels.length + ' elemento(s) a…', h, function () {
                var r = $('hf-modal-c').querySelector('input[name=hf-dest]:checked'); if (!r) return false;
                enviar('mover', { dest: r.value, rels: JSON.stringify(rels) }).then(function (d2) {
                    msg(d2.movidos + ' movido(s).' + (d2.message ? ' ' + d2.message : ''), d2.errores && d2.errores.length ? 'err' : 'ok'); cargar();
                });
            }, 'Mover aquí');
        });
    }
    function borrar() {
        var rels = marcadas(); if (!rels.length) return;
        modal('Enviar a la papelera', '<p>¿Mover ' + rels.length + ' elemento(s) a la papelera? Podrás restaurarlos durante 30 días.</p>', function () {
            enviar('borrar', { rels: JSON.stringify(rels) }).then(function (d) {
                msg(d.borrados + ' enviado(s) a la papelera.' + (d.message ? ' ' + d.message : ''), d.errores && d.errores.length ? 'err' : 'ok'); cargar();
            });
        }, 'A la papelera');
    }
    function copiar(texto) {
        var abs = location.origin + texto;
        if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(abs).then(function () { msg('Dirección copiada: ' + abs); });
        else { var t = document.createElement('textarea'); t.value = abs; document.body.appendChild(t); t.select(); document.execCommand('copy'); document.body.removeChild(t); msg('Dirección copiada: ' + abs); }
    }

    // ── Eventos ──────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        $('hf-crumbs').addEventListener('click', function (e) { var a = e.target.closest('a[data-dir]'); if (a) { e.preventDefault(); cargar(a.dataset.dir); } });
        document.querySelectorAll('.hf-tab').forEach(function (t) {
            t.addEventListener('click', function () { t.dataset.raiz === '__papelera' ? verPapelera() : cargar(t.dataset.raiz); });
        });
        $('hf-lista').addEventListener('click', function (e) {
            var a = e.target.closest('a[data-dir]');
            if (a) { e.preventDefault(); cargar(a.dataset.dir); return; }
            var b = e.target.closest('[data-a]'); if (!b) return;
            var acc = b.dataset.a;
            if (acc === 'ren') renombrar(b.dataset.rel, b.dataset.n);
            else if (acc === 'url') copiar(b.dataset.url);
            else if (acc === 'desc') window.location.href = url('descargar', { rel: b.dataset.rel });
            else if (acc === 'rest') enviar('restaurar', { id: b.dataset.id }).then(function (d) { d.success ? (msg('Restaurado en ' + d.rel), verPapelera()) : msg(d.message, 'err'); });
            else if (acc === 'vaciar') modal('Vaciar papelera', '<p>Se borrará todo lo que hay en la papelera de forma <b>definitiva</b>.</p>', function () {
                enviar('vaciar').then(function (d) { msg(d.eliminados + ' eliminados.'); verPapelera(); });
            }, 'Vaciar');
        });
        $('hf-lista').addEventListener('change', function (e) {
            if (e.target.id === 'hf-todo') {
                document.querySelectorAll('.hf-ck').forEach(function (c) { c.checked = e.target.checked; e.target.checked ? S.sel[c.dataset.rel] = true : delete S.sel[c.dataset.rel]; c.closest('tr').classList.toggle('sel', e.target.checked); });
            } else if (e.target.classList.contains('hf-ck')) {
                e.target.checked ? S.sel[e.target.dataset.rel] = true : delete S.sel[e.target.dataset.rel];
                e.target.closest('tr').classList.toggle('sel', e.target.checked);
            }
            selUi();
        });
        $('hf-filtro').addEventListener('input', function () { S.filtro = this.value; pintar(); });
        $('hf-b-mkdir').addEventListener('click', nuevaCarpeta);
        $('hf-b-mover').addEventListener('click', mover);
        $('hf-b-borrar').addEventListener('click', borrar);
        $('hf-b-subir').addEventListener('click', function () { $('hf-file').click(); });
        $('hf-file').addEventListener('change', function () { subir(this.files); this.value = ''; });

        var drop = $('hf-drop');
        ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('over'); }); });
        ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('over'); }); });
        drop.addEventListener('drop', function (e) { if (e.dataTransfer && e.dataTransfer.files.length) subir(e.dataTransfer.files); });

        cargar('images');
    });
}());
