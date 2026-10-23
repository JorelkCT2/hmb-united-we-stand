<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\InstallerAdapter;

class Com_ArtistasInstallerScript
{
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        $source = $adapter->getParent()->getPath('source');

        // Copiar ficheros SITE
        $siteSrc  = $source . '/components/com_artistas';
        $siteDest = JPATH_SITE . '/components/com_artistas';
        if (is_dir($siteSrc)) {
            Folder::copy($siteSrc, $siteDest, '', true);
        }

        // Copiar ficheros ADMIN
        $adminSrc  = $source . '/administrator/components/com_artistas';
        $adminDest = JPATH_ADMINISTRATOR . '/components/com_artistas';
        if (is_dir($adminSrc)) {
            Folder::copy($adminSrc, $adminDest, '', true);
        }

        // Copiar idioma frontend
        $langSrc  = $source . '/language';
        if (is_dir($langSrc)) {
            Folder::copy($langSrc, JPATH_SITE . '/language', '', true);
        }

        // Copiar idioma backend
        $adminLangSrc = $source . '/administrator/language';
        if (is_dir($adminLangSrc)) {
            Folder::copy($adminLangSrc, JPATH_ADMINISTRATOR . '/language', '', true);
        }

        // Copiar router.php explícitamente (por si Folder::copy lo omite)
        $routerSrc  = $siteSrc  . '/router.php';
        $routerDest = $siteDest . '/router.php';
        if (file_exists($routerSrc) && !file_exists($routerDest)) {
            File::copy($routerSrc, $routerDest);
        } elseif (file_exists($routerSrc)) {
            File::copy($routerSrc, $routerDest);
        }

        return true;
    }

    public function install(InstallerAdapter $adapter): bool
    {
        $this->createTables();
        $this->seedGeneros();
        $this->fixExtensionRecord();
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        $this->createTables();
        $this->migrateColumns();
        $this->seedGeneros();
        $this->fixExtensionRecord();
        return true;
    }

    public function uninstall(InstallerAdapter $a): bool { return true; }

    private function fixExtensionRecord(): void
    {
        // Asegurar que el registro de la extensión tiene el namespace correcto
        $db = Factory::getContainer()->get('DatabaseDriver');
        $namespace = json_encode(['name' => 'Joomla\\Component\\Artistas', 'path' => 'src']);
        $manifestCache = json_encode([
            'name'         => 'com_artistas',
            'type'         => 'component',
            'creationDate' => '2026',
            'author'       => 'Fran Moreno',
            'version'      => '1.0.5',
            'description'  => 'Gestion de artistas',
            'group'        => '',
            'namespace'    => 'Joomla\\Component\\Artistas',
        ]);
        $db->setQuery(
            "UPDATE #__extensions SET manifest_cache = " . $db->quote($manifestCache) .
            " WHERE element = 'com_artistas' AND type = 'component'"
        )->execute();
    }

    private function createTables(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__artistas` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL DEFAULT '',
          `alias` varchar(400) NOT NULL DEFAULT '',
          `short_desc` varchar(500) DEFAULT NULL,
          `description` mediumtext DEFAULT NULL,
          `biografia` mediumtext DEFAULT NULL,
          `influencias` varchar(1000) DEFAULT NULL,
          `componentes` mediumtext DEFAULT NULL,
          `genre` varchar(255) DEFAULT NULL,
          `provincia_origen` varchar(100) DEFAULT NULL,
          `provincias_destino` mediumtext DEFAULT NULL,
          `location` varchar(255) DEFAULT NULL,
          `formats` varchar(500) DEFAULT NULL,
          `events` mediumtext DEFAULT NULL,
          `image` varchar(400) DEFAULT NULL,
          `gallery` mediumtext DEFAULT NULL,
          `videos` mediumtext DEFAULT NULL,
          `video_url` varchar(500) DEFAULT NULL,
          `phone` varchar(50) DEFAULT NULL,
          `email` varchar(255) DEFAULT NULL,
          `website` varchar(500) DEFAULT NULL,
          `social_instagram` varchar(255) DEFAULT NULL,
          `social_facebook` varchar(255) DEFAULT NULL,
          `social_youtube` varchar(255) DEFAULT NULL,
          `social_tiktok` varchar(255) DEFAULT NULL,
          `rating` decimal(3,2) DEFAULT 0.00,
          `review_count` int DEFAULT 0,
          `state` tinyint NOT NULL DEFAULT 0,
          `ordering` int NOT NULL DEFAULT 0,
          `created` datetime DEFAULT NULL,
          `created_by` int DEFAULT 0,
          `modified` datetime DEFAULT NULL,
          `modified_by` int DEFAULT 0,
          `metadesc` varchar(1024) DEFAULT NULL,
          `metakey` varchar(1024) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_state` (`state`),
          KEY `idx_alias` (`alias`(100))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__artistas_resenas` (
          `id` int NOT NULL AUTO_INCREMENT,
          `artista_id` int NOT NULL DEFAULT 0,
          `reviewer_name` varchar(255) DEFAULT NULL,
          `reviewer_email` varchar(255) DEFAULT NULL,
          `rating` tinyint NOT NULL DEFAULT 5,
          `review` mediumtext DEFAULT NULL,
          `event_type` varchar(255) DEFAULT NULL,
          `state` tinyint DEFAULT 0,
          `created` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_artista` (`artista_id`),
          KEY `idx_state` (`state`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__artistas_presupuestos` (
          `id` int NOT NULL AUTO_INCREMENT,
          `artista_id` int NOT NULL DEFAULT 0,
          `artista_name` varchar(255) DEFAULT NULL,
          `client_name` varchar(255) NOT NULL DEFAULT '',
          `client_email` varchar(255) NOT NULL DEFAULT '',
          `client_phone` varchar(50) DEFAULT NULL,
          `event_type` varchar(255) DEFAULT NULL,
          `event_date` date DEFAULT NULL,
          `event_location` varchar(255) DEFAULT NULL,
          `message` mediumtext DEFAULT NULL,
          `state` tinyint DEFAULT 0,
          `created` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_artista` (`artista_id`),
          KEY `idx_state` (`state`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__artistas_noticias` (
          `id`           int NOT NULL AUTO_INCREMENT,
          `artista_id`   int NOT NULL DEFAULT 0,
          `article_id`   int NOT NULL DEFAULT 0,
          `tipo`         varchar(50) NOT NULL DEFAULT 'concierto',
          `titulo`       varchar(500) DEFAULT NULL,
          `fecha_evento` varchar(20) DEFAULT NULL,
          `lugar`        varchar(255) DEFAULT NULL,
          `provincia`    varchar(100) DEFAULT NULL,
          `nombre_disco` varchar(255) DEFAULT NULL,
          `descripcion`  mediumtext DEFAULT NULL,
          `imagen`       varchar(400) DEFAULT NULL,
          `state`        tinyint NOT NULL DEFAULT 1,
          `created`      datetime DEFAULT NULL,
          `created_by`   int DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `idx_artista` (`artista_id`),
          KEY `idx_state` (`state`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        // Añadir columnas nuevas si ya existía la tabla
        $db->setQuery("SHOW COLUMNS FROM `" . $db->getPrefix() . "artistas_noticias`");
        $nCols = array_column($db->loadAssocList() ?: [], "Field");
        $nNew = ["titulo"=>"varchar(500) DEFAULT NULL","fecha_evento"=>"varchar(20) DEFAULT NULL","lugar"=>"varchar(255) DEFAULT NULL","provincia"=>"varchar(100) DEFAULT NULL","nombre_disco"=>"varchar(255) DEFAULT NULL","descripcion"=>"mediumtext DEFAULT NULL","imagen"=>"varchar(400) DEFAULT NULL","state"=>"tinyint NOT NULL DEFAULT 1"];
        foreach ($nNew as $nc => $nd) {
            if (!in_array($nc, $nCols)) {
                $db->setQuery("ALTER TABLE `#__artistas_noticias` ADD COLUMN `{$nc}` {$nd}")->execute();
            }
        }

        // Tabla músicos buscando banda
        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__artistas_musicos` (
          `id`                int NOT NULL AUTO_INCREMENT,
          `user_id`           int NOT NULL DEFAULT 0,
          `nombre`            varchar(255) NOT NULL DEFAULT '',
          `instrumentos`      varchar(500) DEFAULT NULL,
          `provincia`         varchar(100) DEFAULT NULL,
          `ciudad`            varchar(255) DEFAULT NULL,
          `estilos`           mediumtext DEFAULT NULL,
          `bio`               mediumtext DEFAULT NULL,
          `foto`              varchar(400) DEFAULT NULL,
          `email`             varchar(255) DEFAULT NULL,
          `whatsapp`          varchar(30) DEFAULT NULL,
          `notificar_email`   tinyint NOT NULL DEFAULT 1,
          `notificar_whatsapp` tinyint NOT NULL DEFAULT 0,
          `state`             tinyint NOT NULL DEFAULT 1,
          `created`           datetime DEFAULT NULL,
          `modified`          datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_user` (`user_id`),
          KEY `idx_state` (`state`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        // Tabla bandas buscando músico
        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__artistas_busquedas` (
          `id`                int NOT NULL AUTO_INCREMENT,
          `artista_id`        int NOT NULL DEFAULT 0,
          `instrumento`       varchar(255) DEFAULT NULL,
          `provincia`         varchar(100) DEFAULT NULL,
          `ciudad`            varchar(255) DEFAULT NULL,
          `estilos`           mediumtext DEFAULT NULL,
          `descripcion`       mediumtext DEFAULT NULL,
          `state`             tinyint NOT NULL DEFAULT 1,
          `created`           datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_artista` (`artista_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        // Tabla de coincidencias
        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__artistas_matches` (
          `id`                int NOT NULL AUTO_INCREMENT,
          `musico_id`         int NOT NULL DEFAULT 0,
          `busqueda_id`       int NOT NULL DEFAULT 0,
          `notificado`        tinyint NOT NULL DEFAULT 0,
          `created`           datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `idx_match` (`musico_id`,`busqueda_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__artistas_generos` (
          `id` int NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL DEFAULT '',
          `state` tinyint NOT NULL DEFAULT 1,
          `ordering` int NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `idx_state` (`state`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
    }

    private function seedGeneros(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('SELECT COUNT(*) FROM #__artistas_generos');
        if ((int)$db->loadResult() === 0) {
            foreach (['Folk Metal','Música Celta','Rock','Pop','Flamenco','Jazz','Clásica','Blues','Reggae','Hip Hop','Electrónica','Variado','Otro'] as $i => $g) {
                $db->setQuery("INSERT INTO `#__artistas_generos` (name,state,ordering) VALUES (" . $db->quote($g) . ",1," . ($i+1) . ")")->execute();
            }
        }
    }

    private function migrateColumns(): void
    {
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $prefix = $db->getPrefix();
        $table  = $prefix . 'artistas';

        // Alias para URLs SEF
        try {
            $db->setQuery("SHOW COLUMNS FROM `{$table}` LIKE 'alias'");
            if (!$db->loadResult()) {
                $db->setQuery("ALTER TABLE `{$table}` ADD COLUMN `alias` varchar(400) DEFAULT NULL AFTER `name`")->execute();
            }
            $db->setQuery("UPDATE `{$table}` SET alias=LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name,' ','-'),'ñ','n'),'á','a'),'é','e'),'ó','o')) WHERE alias IS NULL OR alias=''")->execute();
        } catch (\Exception $e) {}

        // Ampliar short_desc a mediumtext si sigue siendo varchar
        $db->setQuery("SHOW COLUMNS FROM `{$table}` LIKE 'short_desc'");
        $col = $db->loadAssoc();
        if ($col && strpos(strtolower($col['Type']), 'varchar') !== false) {
            $db->setQuery("ALTER TABLE `{$table}` MODIFY `short_desc` mediumtext DEFAULT NULL")->execute();
        }

        $db->setQuery("SHOW COLUMNS FROM `{$table}`");
        $existing = array_column($db->loadAssocList(), 'Field');
        $cols = [
            'biografia'          => "mediumtext DEFAULT NULL AFTER `description`",
            'influencias'        => "varchar(1000) DEFAULT NULL AFTER `biografia`",
            'componentes'        => "mediumtext DEFAULT NULL AFTER `influencias`",
            'provincia_origen'   => "varchar(100) DEFAULT NULL AFTER `genre`",
            'provincias_destino' => "mediumtext DEFAULT NULL AFTER `provincia_origen`",
            'videos'             => "mediumtext DEFAULT NULL AFTER `gallery`",
            'social_instagram'   => "varchar(255) DEFAULT NULL AFTER `website`",
            'social_facebook'    => "varchar(255) DEFAULT NULL AFTER `social_instagram`",
            'social_youtube'     => "varchar(255) DEFAULT NULL AFTER `social_facebook`",
            'social_tiktok'      => "varchar(255) DEFAULT NULL AFTER `social_youtube`",
            'tipo_banda'         => "varchar(50) NOT NULL DEFAULT 'propias' AFTER `social_tiktok`",
            'discografia'        => "mediumtext DEFAULT NULL",
            'solista'            => "tinyint NOT NULL DEFAULT 0",
            'banda_id'           => "int DEFAULT NULL",
            'banda_texto'        => "varchar(255) DEFAULT NULL",
            'instrumento'        => "varchar(255) DEFAULT NULL",
            'pais_origen'        => "varchar(100) DEFAULT NULL",
            'fecha_nacimiento'   => "date DEFAULT NULL",
            'lugar_nacimiento'   => "varchar(255) DEFAULT NULL",
            'vive'               => "tinyint NOT NULL DEFAULT 1",
            'fecha_defuncion'    => "date DEFAULT NULL",
            'lugar_defuncion'    => "varchar(255) DEFAULT NULL",
            'wikipedia_url'      => "varchar(400) DEFAULT NULL",
        ];
        foreach ($cols as $col => $def) {
            if (!in_array($col, $existing)) {
                $db->setQuery("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}")->execute();
            }
        }
    }
}
