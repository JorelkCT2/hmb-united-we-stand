<?php
defined('_JEXEC') or die;
?>
<div id="hf-app" class="hf-app">
    <div class="hf-bar">
        <div id="hf-crumbs" class="hf-crumbs"></div>
        <div class="hf-acts">
            <input type="search" id="hf-filtro" class="hf-inp" placeholder="Filtrar esta carpeta…">
            <button type="button" class="hf-btn" id="hf-b-mkdir">📁 Nueva carpeta</button>
            <button type="button" class="hf-btn hf-pri" id="hf-b-subir">⬆ Subir archivos</button>
            <input type="file" id="hf-file" multiple hidden>
        </div>
    </div>

    <div class="hf-tabs">
        <button type="button" class="hf-tab" data-raiz="images">🖼 images</button>
        <button type="button" class="hf-tab" data-raiz="media">🎞 media</button>
        <button type="button" class="hf-tab" data-raiz="__papelera">🗑 Papelera</button>
        <span class="hf-sel-acts" id="hf-sel-acts" hidden>
            <span id="hf-sel-n"></span>
            <button type="button" class="hf-btn" id="hf-b-mover">⇢ Mover</button>
            <button type="button" class="hf-btn hf-peligro" id="hf-b-borrar">🗑 A la papelera</button>
        </span>
    </div>

    <div id="hf-msg" class="hf-msg" hidden></div>
    <div id="hf-cola" class="hf-cola" hidden></div>

    <div id="hf-drop" class="hf-drop">
        <div id="hf-lista"><p class="hf-vacio">Cargando…</p></div>
        <p class="hf-pie" id="hf-pie"></p>
    </div>

    <div id="hf-modal" class="hf-modal-bg" hidden>
        <div class="hf-modal">
            <h3 id="hf-modal-t"></h3>
            <div id="hf-modal-c"></div>
            <div class="hf-modal-pie">
                <button type="button" class="hf-btn" id="hf-modal-no">Cancelar</button>
                <button type="button" class="hf-btn hf-pri" id="hf-modal-ok">Aceptar</button>
            </div>
        </div>
    </div>
</div>
<script>
window.HMBFILES = <?php echo json_encode(['ajax' => $this->ajax, 'token' => $this->token, 'config' => $this->config], JSON_UNESCAPED_SLASHES); ?>;
</script>
