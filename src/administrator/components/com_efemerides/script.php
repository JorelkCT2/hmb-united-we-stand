<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\InstallerAdapter;

class Com_EfemeridesInstallerScript
{
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        $source = $adapter->getParent()->getPath('source');

        // Copiar ficheros SITE
        $siteSrc  = $source . '/components/com_efemerides';
        $siteDest = JPATH_SITE . '/components/com_efemerides';
        if (is_dir($siteSrc)) {
            $this->copyDirForce($siteSrc, $siteDest);
        }

        // Copiar ficheros ADMIN
        $adminSrc  = $source . '/administrator/components/com_efemerides';
        $adminDest = JPATH_ADMINISTRATOR . '/components/com_efemerides';
        if (is_dir($adminSrc)) {
            $this->copyDirForce($adminSrc, $adminDest);
        }

        // Idioma frontend
        $langSrc = $source . '/language';
        if (is_dir($langSrc)) {
            Folder::copy($langSrc, JPATH_SITE . '/language', '', true);
        }

        // Idioma backend
        $adminLangSrc = $source . '/administrator/language';
        if (is_dir($adminLangSrc)) {
            Folder::copy($adminLangSrc, JPATH_ADMINISTRATOR . '/language', '', true);
        }

        return true;
    }

    public function install(InstallerAdapter $adapter): bool
    {
        $this->createTables();
        $this->seedCategorias();
        $this->fixExtensionRecord();
        $this->copyModules($adapter);
        $this->clearAllCaches();
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        $this->createTables();
        $this->seedCategorias();
        $this->fixExtensionRecord();
        $this->copyModules($adapter);
        $this->clearAllCaches();
        return true;
    }

    public function uninstall(InstallerAdapter $a): bool { return true; }

    private function copyModules(InstallerAdapter $adapter): void
    {
        $src = $adapter->getParent()->getPath('source') . '/modules';
        if (!is_dir($src)) return;
        $this->copyDirForce($src, JPATH_SITE . '/modules');
        if (function_exists('opcache_reset')) @opcache_reset();
    }

    /**
     * Copia recursiva forzando sobreescritura archivo por archivo.
     * Más fiable que Folder::copy() para updates.
     */
    private function copyDirForce(string $src, string $dst): void
    {
        if (!is_dir($dst)) {
            @mkdir($dst, 0755, true);
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($items as $item) {
            $target = $dst . DIRECTORY_SEPARATOR . $items->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($target)) { @mkdir($target, 0755, true); }
            } else {
                @copy($item->getPathname(), $target);
            }
        }
    }

    private function clearAllCaches(): void
    {
        // Limpiar caché de Joomla
        try {
            $cacheBase = JPATH_SITE . '/cache';
            if (is_dir($cacheBase)) {
                $dirs = glob($cacheBase . '/*', GLOB_ONLYDIR);
                if ($dirs) {
                    foreach ($dirs as $dir) { Folder::delete($dir); }
                }
            }
            $adminCache = JPATH_ADMINISTRATOR . '/cache';
            if (is_dir($adminCache)) {
                $dirs = glob($adminCache . '/*', GLOB_ONLYDIR);
                if ($dirs) {
                    foreach ($dirs as $dir) { Folder::delete($dir); }
                }
            }
        } catch (\Exception $e) { /* silencioso */ }

        // Resetear OPcache si está disponible
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
    }

    private function fixExtensionRecord(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $manifestCache = json_encode([
            'name'        => 'com_efemerides',
            'type'        => 'component',
            'creationDate'=> '2026',
            'author'      => 'Tu Nombre',
            'version'     => '1.0.0',
            'description' => 'Gestor de efemérides musicales',
            'namespace'   => 'Joomla\\Component\\Efemerides',
        ]);
        $db->setQuery(
            "UPDATE #__extensions SET manifest_cache = " . $db->quote($manifestCache) .
            " WHERE element = 'com_efemerides' AND type = 'component'"
        )->execute();
    }

    private function createTables(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__efemerides` (
          `id`              int NOT NULL AUTO_INCREMENT,
          `titulo`          varchar(500) NOT NULL DEFAULT '',
          `fecha_evento`    date NOT NULL,
          `categoria_id`    int NOT NULL DEFAULT 0,
          `artista`         varchar(255) DEFAULT NULL,
          `descripcion`     mediumtext DEFAULT NULL,
          `detalle`         mediumtext DEFAULT NULL,
          `nombre_disco`    varchar(255) DEFAULT NULL,
          `lugar_concierto` varchar(255) DEFAULT NULL,
          `asistentes`      int DEFAULT NULL,
          `state`           tinyint NOT NULL DEFAULT 1,
          `ordering`        int NOT NULL DEFAULT 0,
          `created`         datetime DEFAULT NULL,
          `created_by`      int DEFAULT 0,
          `modified`        datetime DEFAULT NULL,
          `modified_by`     int DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `idx_fecha` (`fecha_evento`),
          KEY `idx_categoria` (`categoria_id`),
          KEY `idx_state` (`state`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__efemerides_categorias` (
          `id`       int NOT NULL AUTO_INCREMENT,
          `name`     varchar(255) NOT NULL DEFAULT '',
          `state`    tinyint NOT NULL DEFAULT 1,
          `ordering` int NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__efemerides_fallecidos` (
          `id`                  int NOT NULL AUTO_INCREMENT,
          `efemeride_id`        int NOT NULL DEFAULT 0,
          `artista`             varchar(255) NOT NULL DEFAULT '',
          `fecha_fallecimiento` date DEFAULT NULL,
          `foto`                varchar(500) DEFAULT NULL,
          `wikipedia_url`       varchar(500) DEFAULT NULL,
          `notas`               mediumtext DEFAULT NULL,
          `state`               tinyint NOT NULL DEFAULT 1,
          `ordering`            int NOT NULL DEFAULT 0,
          `created`             datetime DEFAULT NULL,
          `modified`            datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_efemeride` (`efemeride_id`),
          KEY `idx_state` (`state`),
          KEY `idx_fecha` (`fecha_fallecimiento`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__efemerides_frases` (
          `id`                int NOT NULL AUTO_INCREMENT,
          `frase`             text NOT NULL,
          `autor`             varchar(255) NOT NULL DEFAULT '',
          `fecha_nacimiento`  date DEFAULT NULL,
          `fecha_defuncion`   date DEFAULT NULL,
          `state`             tinyint NOT NULL DEFAULT 1,
          `ordering`          int NOT NULL DEFAULT 0,
          `created`           datetime DEFAULT NULL,
          `modified`          datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_state` (`state`),
          KEY `idx_autor` (`autor`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__efemerides_bandas` (
          `id`               int NOT NULL AUTO_INCREMENT,
          `nombre`           varchar(255) NOT NULL DEFAULT '',
          `wikipedia_url`    varchar(500) DEFAULT NULL,
          `foto`             varchar(500) DEFAULT NULL,
          `fecha_formacion`  date DEFAULT NULL,
          `fecha_disolucion` date DEFAULT NULL,
          `pais`             varchar(100) DEFAULT NULL,
          `genero`           varchar(255) DEFAULT NULL,
          `descripcion`      text DEFAULT NULL,
          `state`            tinyint NOT NULL DEFAULT 1,
          `created`          datetime DEFAULT NULL,
          `modified`         datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uk_nombre` (`nombre`),
          KEY `idx_state` (`state`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();


        // Añadir campos nuevos de músicos si no existen (v1.5)
        $cols = [];
        $db->setQuery("SHOW COLUMNS FROM #__efemerides_musicos")->execute();
        foreach ($db->loadObjectList() as $col) $cols[] = $col->Field;
        $adds = [];
        if (!in_array('lugar_nacimiento',$cols)) $adds[] = "ADD COLUMN `lugar_nacimiento` varchar(255) DEFAULT NULL AFTER `fecha_nacimiento`";
        if (!in_array('lugar_defuncion', $cols)) $adds[] = "ADD COLUMN `lugar_defuncion`  varchar(255) DEFAULT NULL AFTER `fecha_defuncion`";
        if (!in_array('pais',            $cols)) $adds[] = "ADD COLUMN `pais`             varchar(100) DEFAULT NULL AFTER `lugar_defuncion`";
        if (!in_array('instrumento',     $cols)) $adds[] = "ADD COLUMN `instrumento`      varchar(255) DEFAULT NULL AFTER `pais`";
        if ($adds) {
            $db->setQuery("ALTER TABLE #__efemerides_musicos ".implode(', ',$adds))->execute();
        }

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__efemerides_musicos` (
          `id`               int NOT NULL AUTO_INCREMENT,
          `nombre`           varchar(255) NOT NULL DEFAULT '',
          `wikipedia_url`    varchar(500) DEFAULT NULL,
          `foto`             varchar(500) DEFAULT NULL,
          `fecha_nacimiento` date DEFAULT NULL,
          `fecha_defuncion`  date DEFAULT NULL,
          `banda_id`         int DEFAULT NULL,
          `banda_texto`      varchar(255) DEFAULT NULL,
          `descripcion`      text DEFAULT NULL,
          `state`            tinyint NOT NULL DEFAULT 1,
          `created`          datetime DEFAULT NULL,
          `modified`         datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uk_nombre` (`nombre`),
          KEY `idx_state` (`state`),
          KEY `idx_banda` (`banda_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
    }

    private function seedCategorias(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('SELECT COUNT(*) FROM #__efemerides_categorias');
        if ((int)$db->loadResult() === 0) {
            foreach (['Conciertos','Nacimientos','Defunciones','Lanzamientos de discos'] as $i => $cat) {
                $db->setQuery("INSERT INTO `#__efemerides_categorias` (name,state,ordering) VALUES ("
                    . $db->quote($cat) . ",1," . ($i + 1) . ")")->execute();
            }
        }
    }
}
