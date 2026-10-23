<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;

class Com_HmbInstallerScript
{
    public function postflight(string $type, InstallerAdapter $parent): bool
    {
        // Habilitar el componente automáticamente
        try {
            $db = Factory::getDbo();
            $q  = $db->getQuery(true)
                ->update($db->qn('#__extensions'))
                ->set($db->qn('enabled') . ' = 1')
                ->where($db->qn('element') . ' = ' . $db->q('com_hmb'))
                ->where($db->qn('type')    . ' = ' . $db->q('component'));
            $db->setQuery($q)->execute();
        } catch (\Exception $e) {
            // Silencioso — no bloquear instalación
        }
        return true;
    }
}
