<?php
/**
 * @package    com_hmb_videos
 * @copyright  Heavy Metal Brothers 2024
 * @license    GNU/GPL v2+
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;

class Com_Hmb_VideosInstallerScript
{
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        // Language files need manual copy because Joomla's <languages> tag
        // in manifest copies to wrong path in some Joomla 6 setups.
        $source = $adapter->getParent()->getPath('source');

        $langSrc = $source . '/language';
        if (is_dir($langSrc)) {
            Folder::copy($langSrc, JPATH_SITE . '/language', '', true);
        }

        $adminLangSrc = $source . '/administrator/language';
        if (is_dir($adminLangSrc)) {
            Folder::copy($adminLangSrc, JPATH_ADMINISTRATOR . '/language', '', true);
        }

        return true;
    }

    public function install(InstallerAdapter $adapter): bool
    {
        $this->createTables();
        $this->fixExtensionRecord();

        // Add mobile column if it doesn't exist (for updates)
        try {
            $db->setQuery("ALTER TABLE `#__hmb_videos` ADD COLUMN IF NOT EXISTS `mobile` TINYINT(1) NOT NULL DEFAULT 0")->execute();
        } catch (\Exception $e) {}
        $this->clearAllCaches();
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        $this->createTables();
        $this->fixExtensionRecord();
        $this->clearAllCaches();
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    // ─── Helpers ────────────────────────────────────────────────────────

    /**
     * Ensure the #__extensions record is correct.
     *
     * Joomla 6 reads manifest_cache.namespace at every boot to register
     * the PSR-4 autoloader paths. If this field is missing or wrong, class
     * loading fails on ANY request (not just the first), causing 404.
     *
     * We also force enabled=1 so the component is never left disabled.
     */
    private function fixExtensionRecord(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $manifestCache = json_encode([
            'name'        => 'com_hmb_videos',
            'type'        => 'component',
            'creationDate'=> '2024',
            'author'      => 'Heavy Metal Brothers',
            'version'     => '1.0.0',
            'description' => 'Heavy Metal Brothers YouTube Video Gallery',
            'namespace'   => 'Joomla\\Component\\HmbVideos',
        ]);

        $db->setQuery(
            "UPDATE #__extensions
             SET manifest_cache = " . $db->quote($manifestCache) . ",
                 enabled = 1,
                 protected = 0
             WHERE element = 'com_hmb_videos'
               AND type = 'component'"
        )->execute();
    }

    private function clearAllCaches(): void
    {
        try {
            foreach ([JPATH_SITE . '/cache', JPATH_ADMINISTRATOR . '/cache'] as $base) {
                if (is_dir($base)) {
                    foreach (glob($base . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
                        Folder::delete($dir);
                    }
                }
            }
        } catch (\Exception $e) {}

        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
    }

    private function createTables(): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__hmb_folders` (
          `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `title`       VARCHAR(255) NOT NULL DEFAULT '',
          `alias`       VARCHAR(255) NOT NULL DEFAULT '',
          `parent_id`   INT(11) UNSIGNED NOT NULL DEFAULT 0,
          `description` TEXT,
          `cover_image` VARCHAR(255) DEFAULT NULL,
          `ordering`    INT(11) NOT NULL DEFAULT 0,
          `state`       TINYINT(1) NOT NULL DEFAULT 1,
          `access`      INT(11) NOT NULL DEFAULT 1,
          `created`     DATETIME NOT NULL,
          `created_by`  INT(11) UNSIGNED NOT NULL DEFAULT 0,
          `modified`    DATETIME NOT NULL,
          `params`      TEXT,
          PRIMARY KEY (`id`),
          KEY `idx_state`  (`state`),
          KEY `idx_parent` (`parent_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__hmb_videos` (
          `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `title`       VARCHAR(255) NOT NULL DEFAULT '',
          `alias`       VARCHAR(255) NOT NULL DEFAULT '',
          `folder_id`   INT(11) UNSIGNED NOT NULL DEFAULT 0,
          `youtube_id`  VARCHAR(50) NOT NULL DEFAULT '',
          `description` TEXT,
          `tags`        VARCHAR(500) DEFAULT NULL,
          `band`        VARCHAR(255) DEFAULT NULL,
          `genre`       VARCHAR(255) DEFAULT NULL,
          `year`        YEAR DEFAULT NULL,
          `ordering`    INT(11) NOT NULL DEFAULT 0,
          `state`       TINYINT(1) NOT NULL DEFAULT 1,
          `featured`    TINYINT(1) NOT NULL DEFAULT 0,
          `access`      INT(11) NOT NULL DEFAULT 1,
          `hits`        INT(11) UNSIGNED NOT NULL DEFAULT 0,
          `mobile`      TINYINT(1) NOT NULL DEFAULT 0,
          `created`     DATETIME NOT NULL,
          `created_by`  INT(11) UNSIGNED NOT NULL DEFAULT 0,
          `modified`    DATETIME NOT NULL,
          `params`      TEXT,
          PRIMARY KEY (`id`),
          KEY `idx_folder`   (`folder_id`),
          KEY `idx_state`    (`state`),
          KEY `idx_featured` (`featured`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")->execute();
    }
}
