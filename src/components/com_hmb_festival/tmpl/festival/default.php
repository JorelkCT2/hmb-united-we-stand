<?php
defined('_JEXEC') or die;

$cfg = htmlspecialchars(json_encode($this->cfg, JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
?>
<div id="hmbfest-app" data-cfg="<?php echo $cfg; ?>">
    <header class="hmbfest-hero">
        <span class="hmbfest-eyebrow">Heavy Metal Brothers presenta</span>
        <h1 class="hmbfest-title">Festival <em>Mode</em></h1>
        <p class="hmbfest-sub">Haz check-in en tu festival y encuentra a tu gente. Cuernos arriba.</p>
    </header>

    <nav class="hmbfest-tabs" role="tablist">
        <button class="hmbfest-tab is-active" data-tab="festivales" role="tab">Festivales</button>
        <button class="hmbfest-tab" data-tab="gente" role="tab">Gente</button>
        <button class="hmbfest-tab" data-tab="matches" role="tab">Matches <span class="hmbfest-badge" id="hmbfest-unread" hidden>0</span></button>
    </nav>

    <main>
        <section class="hmbfest-panel is-active" data-panel="festivales">
            <div id="hmbfest-festivales" class="hmbfest-grid"></div>
        </section>

        <section class="hmbfest-panel" data-panel="gente">
            <div id="hmbfest-gente-selector"></div>
            <div id="hmbfest-gente" class="hmbfest-grid"></div>
        </section>

        <section class="hmbfest-panel" data-panel="matches">
            <div id="hmbfest-matches"></div>
        </section>
    </main>

    <!-- Modal de check-in -->
    <div class="hmbfest-modal" id="hmbfest-checkin-modal" hidden>
        <div class="hmbfest-modal-box">
            <h2 id="hmbfest-checkin-title">Check-in</h2>
            <p class="hmbfest-label">¿Qué buscas?</p>
            <div class="hmbfest-intents">
                <label><input type="radio" name="hmbfest-intent" value="colegas" checked> <span>Compañeros de festival</span></label>
                <label><input type="radio" name="hmbfest-intent" value="ligar"> <span>Algo más 🔥</span></label>
                <label><input type="radio" name="hmbfest-intent" value="ambas"> <span>Abierto a todo</span></label>
            </div>
            <label class="hmbfest-adult" id="hmbfest-adult-row" hidden>
                <input type="checkbox" id="hmbfest-adult"> Confirmo que soy mayor de 18 años
            </label>
            <p class="hmbfest-label">Preséntate (160 caracteres)</p>
            <input type="text" id="hmbfest-bio" maxlength="160" placeholder="Camiseta de Bolt Thrower, zona acampada norte...">
            <div class="hmbfest-modal-actions">
                <button class="hmbfest-btn hmbfest-btn-ghost" id="hmbfest-checkin-cancel">Cancelar</button>
                <button class="hmbfest-btn" id="hmbfest-checkin-go">Hacer check-in 🤘</button>
            </div>
        </div>
    </div>

    <!-- Modal de chat -->
    <div class="hmbfest-modal" id="hmbfest-chat-modal" hidden>
        <div class="hmbfest-modal-box hmbfest-chat-box">
            <div class="hmbfest-chat-head">
                <strong id="hmbfest-chat-name"></strong>
                <div class="hmbfest-chat-head-actions">
                    <button class="hmbfest-mini" id="hmbfest-chat-report" title="Denunciar">⚠</button>
                    <button class="hmbfest-mini" id="hmbfest-chat-block" title="Bloquear">⛔</button>
                    <button class="hmbfest-mini" id="hmbfest-chat-close" title="Cerrar">✕</button>
                </div>
            </div>
            <div class="hmbfest-chat-log" id="hmbfest-chat-log"></div>
            <div class="hmbfest-chat-input">
                <input type="text" id="hmbfest-chat-text" maxlength="1000" placeholder="Escribe algo con actitud...">
                <button class="hmbfest-btn" id="hmbfest-chat-send">Enviar</button>
            </div>
        </div>
    </div>

    <div class="hmbfest-toast" id="hmbfest-toast" hidden></div>

    <!-- Celebración de match -->
    <div class="hmbfest-matchsplash" id="hmbfest-matchsplash" hidden>
        <div class="hmbfest-matchsplash-inner">
            <span class="hmbfest-horns">🤘</span>
            <h2>¡MATCH!</h2>
            <p id="hmbfest-matchsplash-text"></p>
            <button class="hmbfest-btn" id="hmbfest-matchsplash-close">A darle caña</button>
        </div>
    </div>
</div>
