<?php
defined('_JEXEC') or die;

use Joomla\CMS\Installer\InstallerAdapter;

class Com_HmgalleryInstallerScript
{
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        $source = $adapter->getParent()->getPath('source');

        // Copy site files
        $this->copyDirForce($source . '/components/com_hmgallery', JPATH_SITE . '/components/com_hmgallery');

        // Copy admin files
        $this->copyDirForce($source . '/administrator/components/com_hmgallery', JPATH_ADMINISTRATOR . '/components/com_hmgallery');

        // Copy media files
        $this->copyDirForce($source . '/media/com_hmgallery', JPATH_ROOT . '/media/com_hmgallery');

        // Reset OPcache
        if (function_exists('opcache_reset')) @opcache_reset();

        return true;
    }

    public function install(InstallerAdapter $adapter): bool  { return true; }
    public function update(InstallerAdapter $adapter): bool   {
        if (function_exists('opcache_reset')) @opcache_reset();
        return true;
    }
    public function uninstall(InstallerAdapter $adapter): bool { return true; }

    private function copyDirForce(string $src, string $dst): void
    {
        if (!is_dir($src)) return;
        if (!is_dir($dst)) @mkdir($dst, 0755, true);
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($it as $item) {
            $target = $dst . DIRECTORY_SEPARATOR . $it->getSubPathName();
            if ($item->isDir()) { if (!is_dir($target)) @mkdir($target, 0755, true); }
            else @copy($item->getPathname(), $target);
        }
    }
}
