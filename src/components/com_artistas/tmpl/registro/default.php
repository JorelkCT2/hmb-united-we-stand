<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

// Géneros desde BD
try {
    $__db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
    $__db->setQuery('SELECT name FROM #__artistas_generos WHERE state=1 ORDER BY ordering ASC, name ASC');
    $generosDB = $__db->loadColumn() ?: [];
} catch (\Exception $e) {
    $generosDB = ['Folk Metal','Música Celta','Rock','Pop','Flamenco','Jazz','Clásica','Variado','Otro'];
}

$allProvincias = ['Álava','Albacete','Alicante','Almería','Asturias','Ávila','Badajoz','Barcelona','Burgos','Cáceres','Cádiz','Cantabria','Castellón','Ceuta','Ciudad Real','Córdoba','La Coruña','Cuenca','Girona','Granada','Guadalajara','Gipuzkoa','Huelva','Huesca','Islas Baleares','Jaén','León','Lleida','La Rioja','Lugo','Madrid','Málaga','Melilla','Murcia','Navarra','Ourense','Palencia','Las Palmas','Pontevedra','Salamanca','Santa Cruz de Tenerife','Segovia','Sevilla','Soria','Tarragona','Teruel','Toledo','Valencia','Valladolid','Vizcaya','Zamora','Zaragoza'];
?>

<style>
.reg { color:#e8e0d5; max-width:680px; margin:0 auto; }
.reg h1 { font-size:2rem; font-weight:800; color:#fff; text-shadow:0 2px 12px rgba(0,0,0,.8); margin-bottom:.3rem; }
.reg .subtitulo { color:#c9b99a; margin-bottom:2rem; font-size:1rem; }

.reg-bloque { background:rgba(10,8,6,.82); border:1px solid rgba(180,140,80,.3); border-radius:10px; margin-bottom:1.5rem; overflow:hidden; }
.reg-bloque-titulo { background:rgba(180,120,30,.65); padding:.7rem 1.2rem; font-weight:700; font-size:.85rem; color:#fff; text-transform:uppercase; letter-spacing:.5px; }
.reg-bloque-body { padding:1.4rem; }

.reg label { display:block; color:#d4bb8a; font-size:.82rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px; }
.reg .ayuda { color:#6a5f4a; font-size:.8rem; margin-top:3px; margin-bottom:.8rem; }
.reg .obligatorio { color:#f07070; }

.reg input[type=text],
.reg input[type=email],
.reg input[type=tel],
.reg select,
.reg textarea {
    width:100%;
    background:rgba(255,255,255,.07) !important;
    border:1px solid rgba(180,140,60,.4) !important;
    color:#f0e8d8 !important;
    border-radius:6px;
    padding:.5rem .7rem;
    font-size:.95rem;
    margin-bottom:0;
    transition:border-color .2s;
}
.reg input:focus, .reg select:focus, .reg textarea:focus {
    border-color:rgba(200,160,60,.85) !important;
    box-shadow:0 0 0 3px rgba(180,130,30,.2) !important;
    outline:none;
    color:#fff !important;
}
.reg input::placeholder, .reg textarea::placeholder { color:#5a4f3a !important; }
.reg select option { background:#1a1208; color:#f0e0c0; }

.reg-campo { margin-bottom:1.2rem; }
.reg-campo:last-child { margin-bottom:0; }

.reg-login-box { background:rgba(10,8,6,.85); border:1px solid rgba(180,140,60,.4); border-radius:10px; padding:2rem; text-align:center; margin-bottom:1.5rem; }
.reg-login-btn { display:inline-block; background:linear-gradient(135deg,#b87d1e,#8a5a10); color:#fff; font-weight:700; padding:.8rem 2.5rem; border-radius:8px; text-decoration:none; font-size:1rem; margin:.5rem 0; }
.reg-ya-tienes { background:rgba(180,130,20,.15); border:1px solid rgba(180,140,60,.4); border-radius:10px; padding:1.5rem; text-align:center; margin-bottom:1.5rem; }

.reg-btn-enviar { width:100%; background:linear-gradient(135deg,#b87d1e,#8a5a10); border:1px solid rgba(200,150,60,.5); color:#fff; font-weight:700; font-size:1.1rem; padding:.9rem; border-radius:8px; cursor:pointer; letter-spacing:.5px; transition:all .2s; }
.reg-btn-enviar:hover { background:linear-gradient(135deg,#d4941e,#a06a15); transform:translateY(-1px); }
.reg-nota { color:#6a5f4a; font-size:.8rem; text-align:center; margin-top:.8rem; }
</style>

<div class="reg">
    <div class="text-center mb-4">
        <h1>🎸 Registra tu banda</h1>
        <p class="subtitulo">Rellena el formulario y te publicaremos en el directorio. Es gratis.<br>Los campos con <span class="obligatorio">*</span> son obligatorios.</p>
    </div>

    <?php if ($this->guest ?? false): ?>
    <!-- No logueado -->
    <div class="reg-login-box">
        <div style="font-size:2.5rem;margin-bottom:.5rem">🔒</div>
        <h3 style="color:#d4bb8a;margin-bottom:.5rem">Primero inicia sesión</h3>
        <p style="color:#8a7a60;margin-bottom:1rem">Necesitas una cuenta para registrar tu banda y poder editar tu ficha más adelante.</p>
        <a href="https://heavymetalbrothers.com/index.php/registro" class="reg-login-btn">🔑 Iniciar sesión</a>
        <p style="color:#6a5f4a;font-size:.82rem;margin-top:.8rem">
            ¿No tienes cuenta? <a href="index.php?option=com_users&view=registration" style="color:#d4bb8a">Créala aquí gratis</a>
        </p>
    </div>

    <?php elseif ($this->yaTieneFicha ?? false): ?>
    <!-- Ya tiene ficha -->
    <div class="reg-ya-tienes">
        <div style="font-size:2rem;margin-bottom:.5rem">✅</div>
        <h3 style="color:#d4bb8a;margin-bottom:.5rem">Ya tienes una ficha registrada</h3>
        <p style="color:#c9b890;margin-bottom:.3rem"><strong style="color:#fff"><?= htmlspecialchars($this->fichaExistente->name) ?></strong></p>
        <p style="color:#8a7a60;margin-bottom:1rem">Estado: <?= $this->fichaExistente->state == 1 ? '<span style="color:#8feba8">✓ Publicada y visible</span>' : '<span style="color:#f5c842">⏳ Pendiente de revisión</span>' ?></p>
        <?php
            $miPerfilItemid = \Joomla\Component\Artistas\Site\Helper\RouteHelper::getItemid('miperfil');
            $miPerfilUrl = 'index.php?option=com_artistas&view=miperfil' . ($miPerfilItemid ? '&Itemid='.$miPerfilItemid : '');
        ?>
        <a href="<?= $miPerfilUrl ?>" class="reg-login-btn">✏️ Editar mi ficha</a>
    </div>

    <?php else: ?>
    <!-- Formulario de registro -->
    <?php
    $__registroItemid = \Joomla\Component\Artistas\Site\Helper\RouteHelper::getItemid('registro');
    $__formAction = 'index.php?option=com_artistas&task=registro.submit' . ($__registroItemid ? '&Itemid=' . $__registroItemid : '');
    ?>
    <form action="<?= $__formAction ?>"
          method="post" enctype="multipart/form-data" id="regForm">

        <!-- BLOQUE 1: Lo básico -->
        <div class="reg-bloque">
            <div class="reg-bloque-titulo">1 · Datos básicos</div>
            <div class="reg-bloque-body">

                <div class="reg-campo">
                    <label>Nombre del grupo o artista <span class="obligatorio">*</span></label>
                    <input type="text" name="name" placeholder="Ej: Triskel Celtic Band" required maxlength="255">
                    <p class="ayuda">El nombre con el que os conoce el público.</p>
                </div>

                <div class="reg-campo">
                    <label>Estilo musical <span class="obligatorio">*</span></label>
                    <select name="genre" required>
                        <option value="">— Elige el que mejor os define —</option>
                        <?php foreach ($generosDB as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="ayuda">Si vuestro estilo no aparece, elige el más cercano.</p>
                </div>

                <div class="reg-campo">
                    <label>Descripción breve <span class="obligatorio">*</span></label>
                    <textarea name="short_desc" rows="3" placeholder="Ej: Banda de folk metal con raíces celtas, mezcla de gaitas, guitarra eléctrica y voces épicas. Llevamos 10 años en escena." required id="short_desc_txt" maxlength="500" oninput="contarDesc(this)"></textarea>
                    <p class="ayuda">Describe tu banda en 2-3 frases. Es lo primero que verá el público en el listado. Te quedan <span id="desc_count" style="color:#d4bb8a;font-weight:700">500</span> caracteres.</p>
                </div>

                <div class="reg-campo">
                    <label>Tipo de repertorio <span class="obligatorio">*</span></label>
                    <select name="tipo_banda" required>
                        <option value="solista">🎤 Solista</option>
                        <option value="propias" selected>🎵 Canciones propias</option>
                        <option value="versiones">🎸 Versiones de otros artistas</option>
                        <option value="ambas">🎶 Ambas (propias y versiones)</option>
                    </select>
                    <p class="ayuda">¿Tocáis canciones propias, versiones o una mezcla de ambas?</p>
                </div>

                <div class="reg-campo">
                    <label>Provincia donde estáis <span class="obligatorio">*</span></label>
                    <select name="provincia_origen" required>
                        <option value="">— Selecciona tu provincia —</option>
                        <?php foreach ($allProvincias as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="ayuda">La provincia desde la que operáis habitualmente.</p>
                </div>

            </div>
        </div>

        <!-- BLOQUE 2: Contacto -->
        <div class="reg-bloque">
            <div class="reg-bloque-titulo">2 · Cómo contactar con vosotros</div>
            <div class="reg-bloque-body">

                <div class="reg-campo">
                    <label>Email de contacto <span class="obligatorio">*</span></label>
                    <input type="email" name="email" placeholder="Ej: info@mibanda.com" required>
                    <p class="ayuda">Los interesados en contratarte usarán este email. No se muestra públicamente.</p>
                </div>

                <div class="reg-campo">
                    <label>Teléfono</label>
                    <input type="tel" name="phone" placeholder="Ej: 612 345 678">
                    <p class="ayuda">Opcional. Para los que prefieran llamar directamente.</p>
                </div>

                <div class="reg-campo">
                    <label>Web propia</label>
                    <input type="text" name="website" placeholder="Ej: www.mibanda.com o https://mibanda.com">
                    <p class="ayuda">Opcional. Si tenéis página web, redes sociales principales, Bandcamp, etc.</p>
                </div>

            </div>
        </div>

        <!-- BLOQUE 3: Foto -->
        <div class="reg-bloque">
            <div class="reg-bloque-titulo">3 · Foto del grupo</div>
            <div class="reg-bloque-body">
                <div class="reg-campo">
                    <label>Foto principal</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                    <p class="ayuda">Opcional pero muy recomendable. Una foto del grupo o logo. Formatos: JPG, PNG o WebP. Tamaño máximo recomendado: 2 MB. Una foto horizontal (panorámica) queda mejor.</p>
                </div>
            </div>
        </div>

        <!-- Aviso y botón -->
        <div style="background:rgba(255,255,255,.04);border:1px solid rgba(180,140,60,.2);border-radius:8px;padding:1rem 1.2rem;margin-bottom:1.2rem">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="acepto" required style="width:16px;height:16px;accent-color:#b87d1e;margin-right:8px">
                <label class="form-check-label" for="acepto" style="color:#c9b890;font-size:.88rem">
                    Acepto que mis datos se revisen y publiquen en este directorio. <span class="obligatorio">*</span>
                </label>
            </div>
        </div>

        <!-- SEO -->
        <div class="reg-bloque">
            <div class="reg-bloque-titulo">4 · Posicionamiento en buscadores (opcional)</div>
            <div class="reg-bloque-body">
                <div class="reg-campo">
                    <label class="reg-label">Descripción para Google</label>
                    <textarea name="metadesc" class="mp-ctrl" rows="2" maxlength="160"
                        placeholder="Ej: Banda de folk metal de Murcia con influencias celtas. Disponibles para festivales, bodas y eventos corporativos."></textarea>
                    <p class="ayuda">Máx. 160 caracteres. Es el texto que aparece bajo tu nombre en Google.</p>
                </div>
                <div class="reg-campo">
                    <label class="reg-label">Palabras clave</label>
                    <input type="text" name="metakey" class="mp-ctrl"
                        placeholder="Ej: banda folk metal Murcia, música celta, contratar grupo">
                    <p class="ayuda">Separadas por comas. Términos por los que quieres que os encuentren.</p>
                </div>
            </div>
        </div>

        <button type="submit" class="reg-btn-enviar">🎸 Enviar para revisión</button>
        <p class="reg-nota">Revisaremos tu ficha en 24-48h y te avisaremos por email cuando esté publicada.<br>Una vez aprobada podrás completar todos los detalles desde tu perfil.</p>

            </form>
    </div><!-- /form-banda-section -->
    <?php endif; ?>
</div>

<div id="form-musico-section" style="display:none">
    <div class="reg-bloque">
        <div class="reg-bloque-titulo">🎵 Crear perfil de músico</div>
        <div class="reg-bloque-body">
            <p style="color:#c9b99a;margin-bottom:1rem">Crea tu perfil como músico y las bandas te encontrarán.</p>
            <?php
            $__musicoItemid = \Joomla\Component\Artistas\Site\Helper\RouteHelper::getItemid('musico');
            ?>
            <a href="<?= 'index.php?option=com_artistas&view=musico' . ($__musicoItemid ? '&Itemid='.$__musicoItemid : '') ?>"
               class="reg-btn-enviar" style="display:inline-block;text-align:center;text-decoration:none;max-width:300px">
                🎵 Crear mi perfil de músico
            </a>
        </div>
    </div>
</div>

<script>
function switchTipo(val) {
    document.getElementById('form-banda-section').style.display = val==='banda' ? 'block' : 'none';
    document.getElementById('form-musico-section').style.display = val==='musico' ? 'block' : 'none';
    document.getElementById('opt-banda').style.borderColor = val==='banda' ? 'rgba(200,160,60,.8)' : 'rgba(180,140,60,.4)';
    document.getElementById('opt-musico').style.borderColor = val==='musico' ? 'rgba(100,150,255,.8)' : 'rgba(80,120,200,.3)';
}
document.getElementById('regForm') && document.getElementById('regForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
function contarDesc(el) {
    const max = 500;
    const left = max - el.value.length;
    const c = document.getElementById('desc_count');
    if (!c) return;
    c.textContent = left;
    c.style.color = left <= 50 ? '#f07070' : left <= 100 ? '#f5c842' : '#d4bb8a';
}
</script>
