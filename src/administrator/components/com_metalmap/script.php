<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 *
 * Script de instalación basado en el patrón com_artistas que sí funciona
 * en Joomla 6 / IONOS clickandbuilds.
 *
 * preflight() copia los ficheros MANUALMENTE para evitar el bug del
 * instalador con el atributo folder en <files>.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\InstallerAdapter;

class Com_MetalmapInstallerScript
{
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        // Solo en instalación o actualización
        if ($type !== 'install' && $type !== 'update') {
            return true;
        }

        $source = $adapter->getParent()->getPath('source');

        // ─── BORRAR archivos viejos antes de copiar (evita residuos) ───
        // Si una instalación previa dejó archivos huérfanos que están
        // sobrescribiendo los nuevos, los eliminamos primero.
        $cleanupDirs = [
            JPATH_SITE          . '/components/com_metalmap',
            JPATH_ADMINISTRATOR . '/components/com_metalmap',
            JPATH_SITE          . '/media/com_metalmap',
        ];
        foreach ($cleanupDirs as $dir) {
            if (is_dir($dir) && realpath($dir) !== realpath($source)) {
                try {
                    Folder::delete($dir);
                } catch (\Throwable $e) {
                    // Si no se puede borrar, seguimos — al menos copiamos encima
                }
            }
        }

        // 1) Copiar ficheros del FRONTEND
        $siteSrc  = $source . '/components/com_metalmap';
        $siteDest = JPATH_SITE . '/components/com_metalmap';
        if (is_dir($siteSrc) && realpath($siteSrc) !== realpath($siteDest)) {
            Folder::copy($siteSrc, $siteDest, '', true);
        }

        // 2) Copiar ficheros del BACKEND
        $adminSrc  = $source . '/administrator/components/com_metalmap';
        $adminDest = JPATH_ADMINISTRATOR . '/components/com_metalmap';
        if (is_dir($adminSrc) && realpath($adminSrc) !== realpath($adminDest)) {
            Folder::copy($adminSrc, $adminDest, '', true);
        }

        // 3) Copiar media
        $mediaSrc  = $source . '/media/com_metalmap';
        $mediaDest = JPATH_SITE . '/media/com_metalmap';
        if (is_dir($mediaSrc) && realpath($mediaSrc) !== realpath($mediaDest)) {
            Folder::copy($mediaSrc, $mediaDest, '', true);
        }

        // 4) Copiar el manifest XML al backend (solo si no está ya ahí)
        $manifestSrc  = $source . '/com_metalmap.xml';
        $manifestDest = JPATH_ADMINISTRATOR . '/components/com_metalmap/com_metalmap.xml';
        if (file_exists($manifestSrc) && realpath($manifestSrc) !== realpath($manifestDest)) {
            // Asegurar que el directorio destino existe
            if (!is_dir(dirname($manifestDest))) {
                @mkdir(dirname($manifestDest), 0755, true);
            }
            @File::copy($manifestSrc, $manifestDest);
        }

        return true;
    }

    public function install(InstallerAdapter $adapter): bool
    {
        $this->createTables();
        $this->fixExtensionRecord();
        $this->enableComponent();
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        $this->createTables();
        $this->fixExtensionRecord();
        $this->enableComponent();
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        Factory::getApplication()->enqueueMessage(
            '🤘 com_metalmap instalado. Ve a Componentes → 🎸 Mapa Metal.',
            'success'
        );
        return true;
    }

    /**
     * Crea la tabla de eventos. Idempotente.
     */
    private function createTables(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $sql = "CREATE TABLE IF NOT EXISTS `#__metalmap_events` (
            `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name`             VARCHAR(255) NOT NULL DEFAULT '',
            `alias`            VARCHAR(255) NOT NULL DEFAULT '',
            `event_type`       VARCHAR(20)  NOT NULL DEFAULT 'festival',
            `description`      TEXT NULL,
            `start_date`       DATE NULL DEFAULT NULL,
            `end_date`         DATE NULL DEFAULT NULL,
            `city`             VARCHAR(150) NULL DEFAULT NULL,
            `country`          VARCHAR(100) NULL DEFAULT NULL,
            `country_code`     CHAR(2) NULL DEFAULT NULL,
            `latitude`         DECIMAL(10,7) NOT NULL DEFAULT 0,
            `longitude`        DECIMAL(10,7) NOT NULL DEFAULT 0,
            `website`          VARCHAR(500) NULL DEFAULT NULL,
            `image`            VARCHAR(500) NULL DEFAULT NULL,
            `bands`            TEXT NULL,
            `state`            TINYINT(3) NOT NULL DEFAULT 1,
            `ordering`         INT(11) NOT NULL DEFAULT 0,
            `created`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `created_by`       INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `modified`         DATETIME NULL DEFAULT NULL,
            `modified_by`      INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `checked_out`      INT(11) UNSIGNED NULL DEFAULT NULL,
            `checked_out_time` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_state` (`state`),
            KEY `idx_start_date` (`start_date`),
            KEY `idx_country_code` (`country_code`),
            KEY `idx_event_type` (`event_type`),
            KEY `idx_alias` (`alias`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $db->setQuery($sql)->execute();
        } catch (\Throwable $e) {
            // silencioso
        }
    }

    /**
     * Asegura que el registro de la extensión tiene el namespace correcto
     * y manifest_cache válido. Mismo enfoque que com_artistas.
     */
    private function fixExtensionRecord(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $manifestCache = json_encode([
            'name'         => 'com_metalmap',
            'type'         => 'component',
            'creationDate' => '2026',
            'author'       => 'Heavy Metal Brothers',
            'version'      => '1.3.0',
            'description'  => 'Mapa interactivo de festivales de metal',
            'group'        => '',
            'namespace'    => 'Joomla\\Component\\Metalmap',
        ]);

        try {
            $db->setQuery(
                "UPDATE " . $db->quoteName('#__extensions') .
                " SET " . $db->quoteName('manifest_cache') . " = " . $db->quote($manifestCache) .
                " WHERE " . $db->quoteName('element') . " = " . $db->quote('com_metalmap') .
                " AND " . $db->quoteName('type') . " = " . $db->quote('component')
            )->execute();

            // Si la columna namespace existe, también la rellenamos
            $columns = $db->getTableColumns('#__extensions', false);
            if (isset($columns['namespace'])) {
                $db->setQuery(
                    "UPDATE " . $db->quoteName('#__extensions') .
                    " SET " . $db->quoteName('namespace') . " = " . $db->quote('Joomla\\Component\\Metalmap') .
                    " WHERE " . $db->quoteName('element') . " = " . $db->quote('com_metalmap') .
                    " AND " . $db->quoteName('type') . " = " . $db->quote('component')
                )->execute();
            }
        } catch (\Throwable $e) {
            // silencioso
        }
    }

    private function enableComponent(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        try {
            $db->setQuery(
                "UPDATE " . $db->quoteName('#__extensions') .
                " SET " . $db->quoteName('enabled') . " = 1, " . $db->quoteName('state') . " = 0" .
                " WHERE " . $db->quoteName('element') . " = " . $db->quote('com_metalmap') .
                " AND " . $db->quoteName('type') . " = " . $db->quote('component')
            )->execute();
        } catch (\Throwable $e) {
        }

        // Crear carpeta de imports
        $importDir = JPATH_ROOT . '/images/metalmap';
        if (!is_dir($importDir)) {
            @mkdir($importDir, 0755, true);
        }
    }
}
