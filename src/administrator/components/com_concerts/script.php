<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;

class Com_ConcertsInstallerScript
{
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        $source = $adapter->getParent()->getPath('source');
        $siteSrc  = $source . '/components/com_concerts';
        $siteDest = JPATH_SITE . '/components/com_concerts';
        if (is_dir($siteSrc)) Folder::copy($siteSrc, $siteDest, '', true);
        $adminSrc  = $source . '/administrator/components/com_concerts';
        $adminDest = JPATH_ADMINISTRATOR . '/components/com_concerts';
        if (is_dir($adminSrc)) Folder::copy($adminSrc, $adminDest, '', true);
        $langSrc = $source . '/language';
        if (is_dir($langSrc)) Folder::copy($langSrc, JPATH_SITE . '/language', '', true);
        $adminLangSrc = $source . '/administrator/language';
        if (is_dir($adminLangSrc)) Folder::copy($adminLangSrc, JPATH_ADMINISTRATOR . '/language', '', true);
        return true;
    }

    public function install(InstallerAdapter $adapter): bool
    {
        $this->createTables();
        $this->migrateColumns();
        $this->fixExtensionRecord();
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        $this->createTables();
        $this->migrateColumns();
        $this->fixExtensionRecord();
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool { return true; }

    private function fixExtensionRecord(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $mc = json_encode(['name'=>'com_concerts','type'=>'component','creationDate'=>'2026',
            'author'=>'Tu Empresa','version'=>'1.1.0','description'=>'Agenda de conciertos',
            'group'=>'','namespace'=>'Joomla\\Component\\Concerts']);
        $db->setQuery('UPDATE #__extensions SET manifest_cache='.$db->quote($mc).
            " WHERE element='com_concerts' AND type='component'")->execute();
    }

    private function createTables(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__concerts` (
          `id`               int(11)      NOT NULL AUTO_INCREMENT,
          `title`            varchar(255) NOT NULL DEFAULT '',
          `alias`            varchar(400) NOT NULL DEFAULT '',
          `band`             varchar(255) NOT NULL DEFAULT '',
          `headliners`       text         DEFAULT NULL,
          `support_bands`    varchar(600) DEFAULT NULL,
          `genre_ids`        varchar(500) DEFAULT NULL,
          `date_start`       date         NOT NULL,
          `date_end`         date         DEFAULT NULL,
          `concert_time`     varchar(8)   DEFAULT NULL,
          `doors_time`       varchar(8)   DEFAULT NULL,
          `venue`            varchar(300) NOT NULL DEFAULT '',
          `city`             varchar(150) NOT NULL DEFAULT '',
          `province`         varchar(100) DEFAULT NULL,
          `country_code`     char(2)      NOT NULL DEFAULT 'ES',
          `country_name`     varchar(100) NOT NULL DEFAULT 'España',
          `address`          varchar(400) DEFAULT NULL,
          `prices`           text         DEFAULT NULL,
          `ticket_url`       varchar(600) DEFAULT NULL,
          `website_url`      varchar(600) DEFAULT NULL,
          `poster_url`       varchar(600) DEFAULT NULL,
          `description`      mediumtext   DEFAULT NULL,
          `state`            tinyint(3)   NOT NULL DEFAULT 1,
          `ordering`         int(11)      NOT NULL DEFAULT 0,
          `created`          datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
          `modified`         datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
          `created_by`       int(11)      NOT NULL DEFAULT 0,
          `checked_out`      int(11)      NOT NULL DEFAULT 0,
          `checked_out_time` datetime     DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_state` (`state`), KEY `idx_date` (`date_start`), KEY `idx_country` (`country_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
    }

    private function migrateColumns(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $prefix = $db->getPrefix();
        $table  = $prefix . 'concerts';
        try {
            $db->setQuery("SHOW COLUMNS FROM `{$table}`");
            $existing = array_column($db->loadAssocList(), 'Field');
        } catch (\Exception) { return; }
        $cols = ['genre_ids'=>"varchar(500) DEFAULT NULL AFTER `support_bands`",
                 'prices'   =>"text DEFAULT NULL AFTER `address`"];
        foreach ($cols as $col => $def) {
            if (!in_array($col, $existing)) {
                try { $db->setQuery("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}")->execute(); }
                catch (\Exception) {}
            }
        }
    }
}
