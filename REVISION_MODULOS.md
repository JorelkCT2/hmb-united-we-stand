# Revisión de módulos HMB — incoherencias (solo lectura, nada modificado)

Fecha: 2026-10-24. Alcance: todos los módulos de `src/modules`, los overrides de `com_users`, `com_hmb` (UI de registro) y los datos de producción.

## 1. Seguridad (arreglar primero)

| # | Dónde | Problema |
|---|-------|----------|
| S1 | `mod_hmb_register/helper.php` (avatar) y `templates/hmb/html/com_users/profile/edit.php` | La subida de avatar confía en el MIME y la extensión que manda el cliente → se puede subir un `.php` a `images/hmb_avatars/`. Hoy no hay ninguno (solo 16 imágenes legítimas), pero no hay barrera. |
| S2 | `profile/edit.php` (override) | Guarda avatar y preferencias en POST **sin `Session::checkToken()`** y toma `user_id` de la petición → cualquier usuario logueado puede modificar el avatar/parámetros de otro (IDOR + CSRF). |
| S3 | `mod_hmb_register.php` | Pasa `captchaSecret` (clave **secreta** de reCAPTCHA) a `addScriptOptions` → queda visible en el HTML de la página cuando el módulo se pinta. Hoy no se pinta (ver C1), pero basta con arreglar la posición para filtrarla. |
| S4 | `helper.php` (reCAPTCHA) | Lee los parámetros del primer módulo publicado, se salta la verificación si el token viene vacío y solo rechaza si `success && score<0.3` (un token inválido pasa). |
| S5 | `helper.php` (registro) | Se salta el registro de Joomla: sin eventos `onUserBeforeSave/AfterSave`, sin activación (com_users tiene `useractivation=2`), sin aviso al admin (`mail_to_admin=1`), grupo fijo 2, longitud mínima 8 frente a 9 configurada, sin términos/consentimiento, sin validar el nombre de usuario. Autologin escribiendo en sesión directamente (sin eventos, sin `lastvisitDate`, sin regenerar sesión; por eso 40 de 87 usuarios figuran "nunca visitó"). |
| S6 | `helper.php` (Google) | `client_id` viene del POST (no de la configuración), no comprueba `email_verified`, y deja entrar a usuarios existentes sin mirar `block` ni activación. |
| S7 | `helper.php` | `redirect` tomado de la petición (open redirect); mensajes distintos para "usuario existe"/"email existe" (enumeración de cuentas). |

## 2. Registro y login: tres interfaces para lo mismo

- `mod_hmb_register` (posición `above-a`), `mod_hmb_login` + su override de plantilla, y `com_hmb/tmpl/hmb/_shared.php` (la que ve el usuario en `/registro`) implementan registro/login/logout/"adiós" cada uno por su cuenta contra el mismo endpoint `com_ajax`.
- Reglas distintas en cada una: contraseña mínima 8 (UI) vs 9 (Joomla); consentimientos marcados de serie en `mod_hmb_register` y `_shared.php` (RGPD: debe ser opt-in); `metal_quiz_invitations` por defecto 0 (plugin) / 1 (vista de perfil) / marcado (registro); **59 de 87 usuarios no tienen valor y la UI los muestra como "suscritos"**.
- Tres destinos de logout distintos: `/?hmbbye=1` (módulo login), `/index.php/registro?hmbbye=1` (override), raíz (userbar).
- El override de `mod_hmb_login` ignora los parámetros `redirect_after_login/logout` del módulo (URLs fijas dentro de la plantilla).
- URLs fijas `index.php/registro/perfil-usuario` en register/login/userbar (rompen si cambia el alias o se activa la reescritura).
- Ruta `/component/users/registration` sigue respondiendo 200 aunque el menú nativo está en la papelera.

## 3. Módulos que no se muestran (posición sin renderizar)

El template `hmb` solo pinta: banner, breadcrumbs, conciertos, debug(no), efemerides, footer, frases, header, login, menu, noticias, sidebar-left, sidebar-right, slider, temas, topbar, videos.

- **C1** Módulo 176 `REGISTRO HMB` (`above-a`): publicado pero nunca se pinta.
- **C2** Módulo 189 `userbar` (`userbar`): publicado pero nunca se pinta (verificado: 0 apariciones en portada).
- **C3** Módulo 125 `mod_fotos_noticia` en `debug`: publicado, no se pinta.
- Posiciones con módulos despublicados: `showcase-a` 6, `sidebar-a` 4, `header-right` 4.
- Posiciones declaradas en `templateDetails.xml` (hero, main-top, main-bottom, content-top, content-bottom) que `index.php` no usa, y al revés.

## 4. Overrides de com_users duplicados

`src/plugins/system/hmboverrides/overrides/com_users/*` y `templates/hmb/html/com_users/*` **difieren** entre sí (profile/default ~317 líneas de diff, profile/edit ~394, reset/remind 6). El plugin los copia al instalar pero `hmboverrides.php` no hace nada y su parámetro `auto_reinstall` no se usa. No hay una fuente de verdad. El plugin `hmbquiz` (añade el campo `metal_quiz_invitations`) está **deshabilitado** en producción.

## 5. Calidad general de módulos

- Autores inconsistentes: "Tu Empresa" (mod_concerts_next), "Tu Nombre" (mod_efemerides, mod_frases_rockeras), "Francisco &amp; Copilot" (mod_fotos_noticia), "MuJG" (mod_sorteo).
- i18n (`Text::_`) solo en ~6 módulos; el resto con español fijo.
- Parámetros declarados en el XML y nunca leídos: mod_efemerides (`enlace_memorial`, `url_componente`, caché), mod_hmb_flash (`counter_note`), mod_hmbtemas (`mostrar_player`, `subtitulo`), caché declarada y no aplicada en varios.
- Parámetros leídos y no declarados: `count_per_cat` en mod_hmb_latestnews; `api_key` etc. en el plugin hmbnews.
- `Factory::getDbo()` (obsoleto) en varios módulos; muchos `style=""` en línea.
- Falta `defined('_JEXEC') or die` en las clases `*Component.php` de com_artistas, com_concerts, com_efemerides, com_hmbtemas, com_hmb_videos y com_metalmap (admin y site).

## 6. Orden de arreglo propuesto

1. **Seguridad** (S1–S4, S7): validar imagen con `getimagesize` + extensión permitida + `.htaccess` sin PHP en `images/`; token y usuario de sesión en `profile/edit`; quitar el secret de las opciones JS y verificar reCAPTCHA siempre en servidor.
2. **Unificar el registro**: una sola implementación (la de `com_hmb`) que use la API de usuarios de Joomla (eventos, activación, política de contraseña, consentimientos sin marcar) y que los módulos solo enlacen/ muestren estado.
3. **Datos**: poner `metal_quiz_invitations` explícito para los 59 usuarios sin valor (decisión tuya: 0 o 1) y activar `hmbquiz` o eliminarlo.
4. **Posiciones**: mover o retirar los módulos 176/189/125; decidir si la barra de usuario se pinta.
5. **Limpieza**: una sola fuente de overrides, autores/versión, parámetros muertos, `_JEXEC`, URLs vía `Route::_`.
