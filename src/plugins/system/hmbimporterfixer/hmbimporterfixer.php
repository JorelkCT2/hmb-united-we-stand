<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

class PlgSystemHmbimporterfixer extends CMSPlugin {

    public function onAfterInitialise(): void {
        $app = Factory::getApplication();
        if (!$app->isClient('administrator')) return;
        $user = $app->getIdentity();
        if (!$user || !$user->authorise('core.admin')) return;

        $db     = Factory::getDbo();
        $prefix = $db->getPrefix();

        // ── 1. Tablas ──────────────────────────────────────────────────────
        try {
            $tables = $db->getTableList();
            if (!in_array($prefix.'hmb_sources', $tables, true)) {
                $db->setQuery('CREATE TABLE IF NOT EXISTS `#__hmb_sources`(`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(100) NOT NULL DEFAULT \'\',`url` varchar(255) NOT NULL DEFAULT \'\',`active` tinyint(1) NOT NULL DEFAULT 1,`ordering` int(11) NOT NULL DEFAULT 0,PRIMARY KEY(`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci')->execute();
                $db->setQuery("INSERT IGNORE INTO `#__hmb_sources`(`name`,`url`,`active`,`ordering`)VALUES('Loudwire','loudwire.com',1,1),('Metal Injection','metalinjection.net',1,2),('Blabbermouth','blabbermouth.net',0,3),('Kerrang','kerrang.com',0,4),('Revolver','revolvermag.com',0,5),('Metal Hammer','metalhammer.com',0,6),('Guitar World','guitarworld.com',0,7),('AntiHero Magazine','antiherosaremag.com',0,8)")->execute();
            }
            if (!in_array($prefix.'hmb_imported', $tables, true)) {
                $db->setQuery('CREATE TABLE IF NOT EXISTS `#__hmb_imported`(`id` int(11) NOT NULL AUTO_INCREMENT,`article_id` int(11) NOT NULL DEFAULT 0,`source_site` varchar(100) NOT NULL DEFAULT \'\',`source_url` varchar(512) NOT NULL DEFAULT \'\',`imported_at` datetime NOT NULL,PRIMARY KEY(`id`),KEY`idx_article_id`(`article_id`))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci')->execute();
            }
        } catch (\Throwable $e) {}

        // ── 2. Registrar componente en #__extensions ───────────────────────
        try {
            $extId = (int)$db->setQuery($db->getQuery(true)->select('extension_id')->from('#__extensions')->where('element='.$db->quote('com_hmbimporter'))->where('type='.$db->quote('component')))->loadResult();
            if (!$extId) {
                $obj=(object)['package_id'=>0,'name'=>'COM_HMBIMPORTER','type'=>'component','element'=>'com_hmbimporter','folder'=>'','client_id'=>1,'enabled'=>1,'access'=>1,'protected'=>0,'locked'=>0,
                    'manifest_cache'=>'{"name": "COM_HMBIMPORTER", "type": "component", "creationDate": "2026-05-19", "author": "Heavy Metal Brothers", "copyright": "", "authorEmail": "admin@heavymetalbrothers.com", "authorUrl": "https://heavymetalbrothers.com", "version": "1.0.7", "description": "COM_HMBIMPORTER_DESC", "group": "", "filename": "hmbimporter", "namespace": {"value": "HeavyMetalBrothers\\Component\\Hmbimporter", "path": "src", "dist": ""}}',
                    'params'=>'{}','custom_data'=>'','system_data'=>'','checked_out'=>0,'checked_out_time'=>null,'ordering'=>0,'state'=>0];
                $db->insertObject('#__extensions',$obj);
            }
        } catch (\Throwable $e) {}

        // ── 3. Cargar clases directamente (evita dependencia del autoloader) ─
        $src = JPATH_ADMINISTRATOR . '/components/com_hmbimporter/src';
        if (!is_dir($src)) return;
        $map = [
            'HeavyMetalBrothers\Component\Hmbimporter\Administrator\Extension\HmbimporterComponent' => 'Extension/HmbimporterComponent.php',
            'HeavyMetalBrothers\Component\Hmbimporter\Administrator\Dispatcher\Dispatcher'           => 'Dispatcher/Dispatcher.php',
            'HeavyMetalBrothers\Component\Hmbimporter\Administrator\Controller\DisplayController'   => 'Controller/DisplayController.php',
            'HeavyMetalBrothers\Component\Hmbimporter\Administrator\Controller\AjaxController'      => 'Controller/AjaxController.php',
            'HeavyMetalBrothers\Component\Hmbimporter\Administrator\View\Dashboard\HtmlView'        => 'View/Dashboard/HtmlView.php',
        ];
        foreach ($map as $class => $file) {
            try {
                if (!class_exists($class, false)) {
                    $full = $src . '/' . $file;
                    if (is_file($full)) require_once $full;
                }
            } catch (\Throwable $e) {}
        }

        // ── 4. Registrar namespace en el autoloader de Joomla ──────────────
        try {
            if (class_exists('\JLoader', false)) {
                \JLoader::registerNamespace('HeavyMetalBrothers\\Component\\Hmbimporter\\Administrator', $src, false, false, 'psr4');
            }
        } catch (\Throwable $e) {}
    }
}