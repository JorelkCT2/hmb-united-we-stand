# HMB — flujo de trabajo (Joomla 6, IONOS)

- `src/` extensiones propias con las MISMAS rutas que en el servidor (`src/components/com_x`, `src/modules/mod_y`, `src/plugins/g/n`, `src/media`, `src/language`). `templates/hmb/` la plantilla.
- `python construir.py` valida (php -l + XML) y genera `dist/*.zip` (overlay, no se versiona).
- `python desplegar.py --solo-mirar` compara con HMBNEW; `python desplegar.py` sube solo lo distinto, con copia previa en `tmp/hmb_backups/` del servidor, y verifica huellas + portada.
- Nunca se entrega `configuration.php` ni `.htaccess`.
- Subir version: editar `<version>` del manifiesto en `src/` antes de desplegar.
- Auditoria: `AUDITORIA_IONOS.md`.
