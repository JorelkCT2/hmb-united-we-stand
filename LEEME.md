# HMB — flujo de trabajo (Joomla 6, IONOS)

- `src/` extensiones propias con las MISMAS rutas que en el servidor (`src/components/com_x`, `src/modules/mod_y`, `src/plugins/g/n`, `src/media`, `src/language`). `templates/hmb/` la plantilla.
- `python construir.py` valida (php -l + XML) y genera `dist/*.zip` (overlay, no se versiona).
- `python desplegar.py --solo-mirar` compara con HMBNEW; `python desplegar.py` sube solo lo distinto, con copia previa en `tmp/hmb_backups/` del servidor, y verifica huellas + portada.
- Nunca se entrega `configuration.php` ni `.htaccess`.
- Subir version: editar `<version>` del manifiesto en `src/` antes de desplegar.
- Auditoria: `AUDITORIA_IONOS.md`.

## Reglas que ya nos han roto la web (las comprueba `construir.py`)
- El panel flotante **ACCESO** (`#hmb-float-tab`) se maneja SOLO en `templates/hmb/js/template.js`. Nunca añadir otro `onclick`/`addEventListener` sobre él (en `index.php`, módulos o componentes): abre y cierra a la vez y el botón deja de funcionar.
- `hmb-metal.css` es la última hoja de estilos del template; los retoques visuales van ahí, no en los bloques `<style>` de cada extensión.
- Los formularios de `com_users/profile/edit.php` deben enviarse a `…?layout=edit`, si no la subida de foto cae en la vista de perfil y se ignora.
- La ficha de perfil (`.hmb-pf`) usa la clase `.hmb-hero`, que también existe como portada en `hmb-base.css`. Cualquier cambio de alto o `z-index` en `.hmb-hero` debe probarse con el desplegable de la foto (lápiz): sale del encabezado y debe quedar POR ENCIMA de las filas de datos (`.hmb-pf .hmb-hero { z-index: 20 }` en `hmb-metal.css`).
- Para probar subidas de foto: clic real de ratón en "Subir foto" (no `form.submit()`), y comprobar con `document.elementFromPoint` que el botón es lo que recibe el clic.
