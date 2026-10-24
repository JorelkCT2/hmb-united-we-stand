<?php
defined('_JEXEC') or die;

use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Factory;

return new class implements InstallerScriptInterface
{
    public function install(InstallerAdapter $adapter): bool { return true; }
    public function update(InstallerAdapter $adapter): bool { return true; }
    public function uninstall(InstallerAdapter $adapter): bool { return true; }
    public function preflight(string $type, InstallerAdapter $adapter): bool { return true; }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        // Carpeta de fotos de la tienda (las migradas de Phoca viven en images/tienda/)
        $dir = JPATH_ROOT . '/images/tienda';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return true;
    }
};
