<?php
defined('_JEXEC') or die;
class PlgSystemHmbimporterfixerInstallerScript {
    public function postflight($type,$parent) {
        try {
            $db=\Joomla\CMS\Factory::getDbo();
            $db->setQuery("UPDATE `#__extensions` SET `enabled`=1 WHERE `type`='plugin' AND `element`='hmbimporterfixer' AND `folder`='system'")->execute();
        } catch(\Throwable $e){}
        return true;
    }
    public function preflight($t,$p){return true;}
    public function install($p){return true;}
    public function update($p){return true;}
    public function uninstall($p){return true;}
}