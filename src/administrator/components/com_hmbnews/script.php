<?php
defined('_JEXEC') or die;
class Com_HmbnewsInstallerScript {
    public function preflight($t,$p){return true;}
    public function postflight($t,$p){
        try {
            $d = \Joomla\CMS\Factory::getDbo();
            $d->setQuery('CREATE TABLE IF NOT EXISTS `#__hmb_sources`(`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(100) NOT NULL DEFAULT \'\',`url` varchar(255) NOT NULL DEFAULT \'\',`active` tinyint(1) NOT NULL DEFAULT 1,`ordering` int(11) NOT NULL DEFAULT 0,PRIMARY KEY(`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci')->execute();
            $d->setQuery('CREATE TABLE IF NOT EXISTS `#__hmb_imported`(`id` int(11) NOT NULL AUTO_INCREMENT,`article_id` int(11) NOT NULL DEFAULT 0,`source_site` varchar(100) NOT NULL DEFAULT \'\',`source_url` varchar(512) NOT NULL DEFAULT \'\',`imported_at` datetime NOT NULL,PRIMARY KEY(`id`),KEY`idx_article_id`(`article_id`))ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci')->execute();
            $n = (int)$d->setQuery('SELECT COUNT(*) FROM `#__hmb_sources`')->loadResult();
            if ($n === 0) {
                $d->setQuery("INSERT IGNORE INTO `#__hmb_sources`(`name`,`url`,`active`,`ordering`)VALUES('Loudwire','loudwire.com',1,1),('Metal Injection','metalinjection.net',1,2),('Blabbermouth','blabbermouth.net',0,3),('Kerrang','kerrang.com',0,4),('Revolver','revolvermag.com',0,5),('Metal Hammer','metalhammer.com',0,6),('Guitar World','guitarworld.com',0,7)")->execute();
            }
        } catch(\Throwable $e) {}
        return true;
    }
    public function uninstall($p){return true;}
    public function install($p){return true;}
    public function update($p){return true;}
}