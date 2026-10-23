# Auditoría IONOS — HMB (23/10/2026, solo lectura)

Acceso: `ssh -i ~/.ssh/ionos u108199308@access909012048.webspace-data.io` (mismo contrato que Magones/SociWeb). Funciona sin contraseña. PHP CLI del servidor es 4.4.9 (inútil); el PHP bueno es el del web (php.ini en la raíz).

## Sitios en `clickandbuilds/`
| Carpeta | Qué es | Estado |
|---|---|---|
| `HMBNEW` | **Web nueva, Joomla 6.1.2**, BD `dbs15662619`, prefijo `mujg_`, debug off | Producción nueva |
| `HMB` | Web vieja (Gantry5 / rt_remnant / j2store), BD `dbs15529480`, **debug ON** | Heredada, última modif. mayo |
| `licencias`, `SociWeb`, … | Proyecto Magones | No tocar desde aquí |

## Extensiones propias en HMBNEW (versión del manifiesto)
Componentes: com_hmb 1.0.10 · com_hmbnews 1.0.26 · com_hmb_quiz 1.50.0 · com_hmb_festival 0.3.0 · com_hmbtemas 1.3.0 · com_artistas 1.1.0 · com_concerts 1.1.1 · com_efemerides 2.6.0 · com_metalmap 1.3.0 · com_hmgallery 2.0.0 · com_hmb_videos 1.0.0 (+ com_hmb_editor, com_hmbimporter sin manifiesto legible).
Módulos: mod_hmb_latestnews 2.6.0 · mod_hmb_register 2.3.1 · mod_hmb_login 1.0.7 · mod_hmb_quiz 1.10.0 · mod_hmb_quiz_ranking 1.1.0 · mod_efemerides 2.6.6 · mod_concerts_next 1.0.2 · mod_hmb_social 2.0.0 · mod_hmb_flash 1.1.0 · mod_fotos_noticia 2.0.0 · mod_hmb_userbar 1.0.1 · mod_artistas_slider, carrusel, colaboradores, contacto, logo, menu, tagcloud, videos, hmbtemas, sorteo, frases_rockeras (1.0.0).
Plantilla: `hmb` 7.5.37.0.

## Hallazgos
1. **Solo la plantilla está en git.** Componentes y módulos solo existen en el servidor y en ZIPs sueltos por J:\ → sin historial ni forma de reconstruir.
2. **Basura en la raíz de HMBNEW** (legible por web, permisos `----r--`): `check_banda.php`, `check_efemerides.php`, `check_module.php`, `debug_menu.php`; en HMB además `debug_token.php`, `fix_cache.php`, `ajax_efemerides.php`, `ruta.php`. Hay que revisarlos y borrarlos (con copia previa).
3. `HMB` (vieja) tiene `debug = true` en producción.
4. `configuration.php` de HMBNEW es 0444 (bien) y está fuera de git (bien).
5. `tmp/` del servidor contiene ZIPs de instalación y carpetas `install_*` residuales.
6. `J:\...\WEB\Login.txt` guarda un usuario y contraseña en claro: moverlo a la bóveda (Vaultwarden) y rotar la contraseña.
7. Vigilar: `com_hmb_quiz` 1.50.0 es el más activo; `com_hmb_festival` 0.3.0 es el más verde.

## Propuesta de compactación (copiar el patrón de Magones)
- `src/<extensión>/` en el repo con el código tal cual está en el servidor (descarga inicial por scp/tar).
- `construir.py`: genera los ZIP en `dist/` y sube la versión del manifiesto.
- `desplegar.py` (SSH + copia previa + `mv` atómico + comprobación bajando lo servido por HTTPS, con `--solo-mirar`), adaptado de `SociWeb/desplegar.py` a `clickandbuilds/HMBNEW`.
- Joomla local de pruebas (XAMPP + PHP 8.4) con volcado de `dbs15662619` para probar antes de subir.
- Memoria del proyecto HMB (reglas igual que Magones: probar en local, subir directo, nunca pisar `configuration.php`).
