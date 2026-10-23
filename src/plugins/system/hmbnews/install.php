<?php
defined('_JEXEC') or die;
class PlgSystemHmbnewsInstallerScript {
    public function postflight($type, $parent) {
        try {
            \Joomla\CMS\Factory::getDbo()->setQuery(
                "UPDATE `#__extensions` SET `enabled`=1
                 WHERE `type`='plugin' AND `folder`='system' AND `element`='hmbnews'"
            )->execute();
        } catch(\Throwable $e) {}
        return true;
    }
    public function preflight($t,$p){return true;}
    public function install($p){return true;}
    public function update($p){return true;}
    public function uninstall($p){return true;}
}