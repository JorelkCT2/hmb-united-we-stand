<?php
/**
 * @package     mod_sorteo
 * @subpackage  tmpl
 * @author      MuJG
 * @license     GNU/GPL v2 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

// Prefijo único por instancia del módulo (evita conflictos si hay varios módulos)
$mid = 'msor' . (int) $module->id;

// Título del módulo
$titulo = htmlspecialchars($params->get('titulo', '¡Gran Sorteo!'), ENT_QUOTES, 'UTF-8');

// Fecha objetivo → timestamp en milisegundos para JavaScript
$fechaSorteo   = trim((string) $params->get('fecha_sorteo', ''));
$targetTimestamp = 0;

if ($fechaSorteo !== '') {
    try {
        $dt = new \DateTime($fechaSorteo);
        $targetTimestamp = $dt->getTimestamp() * 1000; // JS usa milisegundos
    } catch (\Exception $e) {
        $targetTimestamp = 0;
    }
}

// Serializar usuarios para JavaScript (escapado seguro)
$usersJson = json_encode(
    $usuarios,
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT
);

// ¿Ya pasó la fecha? Mostrar la ruleta directamente
$sorteoActivo = ($targetTimestamp > 0 && (time() * 1000) < $targetTimestamp);
?>

<div
    id="<?= $mid ?>"
    class="mod-sorteo-wrap"
    data-mid="<?= $mid ?>"
    data-target-ts="<?= $targetTimestamp ?>"
    data-users="<?= htmlspecialchars($usersJson, ENT_QUOTES, 'UTF-8') ?>"
    data-titulo="<?= $titulo ?>"
>
    <!-- ══════════════════════════════════════════════
         FASE 1 · Cuenta atrás
    ══════════════════════════════════════════════ -->
    <div id="<?= $mid ?>-phase-countdown" class="ms-phase" <?= !$sorteoActivo ? 'style="display:none"' : '' ?>>
        <div class="ms-header">
            <div class="ms-icon-top">💀</div>
            <h2 class="ms-title"><?= $titulo ?></h2>
            <p class="ms-subtitle">⚡ El sorteo comienza en ⚡</p>
        </div>
        <div class="ms-countdown">
            <div class="ms-time-block">
                <div class="ms-number" id="<?= $mid ?>-days">00</div>
                <div class="ms-label">DÍAS</div>
            </div>
            <div class="ms-colon">:</div>
            <div class="ms-time-block">
                <div class="ms-number" id="<?= $mid ?>-hours">00</div>
                <div class="ms-label">HORAS</div>
            </div>
            <div class="ms-colon">:</div>
            <div class="ms-time-block">
                <div class="ms-number" id="<?= $mid ?>-minutes">00</div>
                <div class="ms-label">MIN</div>
            </div>
            <div class="ms-colon">:</div>
            <div class="ms-time-block">
                <div class="ms-number" id="<?= $mid ?>-seconds">00</div>
                <div class="ms-label">SEG</div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         FASE 2 · Ruleta (Máquina tragaperras)
    ══════════════════════════════════════════════ -->
    <div id="<?= $mid ?>-phase-ruleta" class="ms-phase" <?= $sorteoActivo ? 'style="display:none"' : '' ?>>
        <div class="ms-header">
            <div class="ms-icon-top">⚡💀⚡</div>
            <h2 class="ms-title"><?= $titulo ?></h2>
            <p class="ms-subtitle" id="<?= $mid ?>-ruleta-sub">🔥 ¡Que empiece la destrucción! 🔥</p>
        </div>

        <div class="ms-machine-wrap">
            <!-- Marco de la máquina -->
            <div class="ms-machine-frame" id="<?= $mid ?>-frame">
                <!-- Sombra superior (oscurece filas de arriba/abajo) -->
                <div class="ms-reel-fade top"></div>

                <!-- Ventana de la ruleta (3 items visibles × 80 px = 240 px) -->
                <div class="ms-reel-window">
                    <!-- Resaltado de la fila central (ganador) -->
                    <div class="ms-center-highlight"></div>
                    <!-- Tira con todos los usuarios repetidos -->
                    <div class="ms-reel-strip" id="<?= $mid ?>-strip"></div>
                </div>

                <div class="ms-reel-fade bottom"></div>

                <!-- Flechas laterales apuntando al ganador -->
                <div class="ms-arrow left">◄</div>
                <div class="ms-arrow right">►</div>
            </div>

            <!-- Espada lateral (antigua manivela) -->
            <div class="ms-lever" id="<?= $mid ?>-lever" title="¡Invocar destino!">
                <div class="ms-lever-skull">💀</div>
                <div class="ms-lever-pommel"></div>
                <div class="ms-lever-grip"></div>
                <div class="ms-lever-guard"></div>
                <div class="ms-lever-blade"></div>
                <div class="ms-lever-blood"></div>
            </div>
        </div>

        <button
            class="ms-btn ms-btn-spin"
            id="<?= $mid ?>-btn-spin"
            data-action="spin"
        >
            <span>💀</span>
            <span>¡INVOCAR DESTINO!</span>
        </button>
    </div>

    <!-- ══════════════════════════════════════════════
         FASE 3 · ¿Está en la sala?
    ══════════════════════════════════════════════ -->
    <div id="<?= $mid ?>-phase-confirm" class="ms-phase" style="display:none">
        <div class="ms-winner-card">
            <div class="ms-avatar ms-avatar-lg" id="<?= $mid ?>-confirm-avatar">?</div>
            <h2 class="ms-winner-name" id="<?= $mid ?>-confirm-name">—</h2>
            <div class="ms-divider"></div>
            <p class="ms-question">¿Se encuentra en la sala?</p>
            <div class="ms-btn-group">
                <button class="ms-btn ms-btn-yes" data-action="confirmYes">
                    ✅ &nbsp;SÍ, está aquí
                </button>
                <button class="ms-btn ms-btn-no" data-action="confirmNo">
                    ❌ &nbsp;NO está
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         FASE 4 · ¡¡¡Enhorabuena!!!
    ══════════════════════════════════════════════ -->
    <div id="<?= $mid ?>-phase-winner" class="ms-phase" style="display:none">
        <!-- Confeti (generado por JS) -->
        <div class="ms-confetti" id="<?= $mid ?>-confetti" aria-hidden="true"></div>

        <div class="ms-winner-final">
            <div class="ms-trophy" aria-label="Metal">🤘</div>
            <h1 class="ms-congrats">¡¡¡ENHORABUENA!!!</h1>
            <div class="ms-avatar ms-avatar-xl" id="<?= $mid ?>-final-avatar">?</div>
            <h2 class="ms-winner-name-xl" id="<?= $mid ?>-final-name">—</h2>
            <p class="ms-winner-sub">¡Eres el/la ganador/a del sorteo!</p>
            <button class="ms-btn ms-btn-another" data-action="searchAnother">
                ⚡ &nbsp;Invocar otro elegido
            </button>
        </div>
    </div>

</div><!-- /#<?= $mid ?> -->
