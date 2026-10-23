<?php
/**
 * Heavy Metal Brothers — Override Installer Script
 * Copia los archivos de override a la plantilla activa al instalar.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Log\Log;

class PlgSystemHmboverridesInstallerScript
{
    public function postflight(string $type, InstallerAdapter $parent): bool
    {
        if (!in_array($type, ['install', 'update'])) {
            return true;
        }

        return $this->copyOverrides();
    }

    private function copyOverrides(): bool
    {
        try {
            $db = Factory::getDbo();

            // Obtener la plantilla activa del sitio
            $q = $db->getQuery(true)
                ->select($db->qn('template'))
                ->from($db->qn('#__template_styles'))
                ->where($db->qn('client_id') . ' = 0')
                ->where($db->qn('home') . ' = 1');
            $template = $db->setQuery($q)->loadResult();

            if (empty($template)) {
                Log::add('HMB Overrides: No se encontró plantilla activa.', Log::WARNING, 'installer');
                return true;
            }

            $src  = JPATH_PLUGINS . '/system/hmboverrides/overrides';
            $dest = JPATH_THEMES  . '/' . $template . '/html';

            if (!is_dir($src)) {
                Log::add('HMB Overrides: Carpeta de overrides no encontrada.', Log::WARNING, 'installer');
                return true;
            }

            $this->copyDir($src, $dest);

            Log::add(
                'HMB Overrides instalados en templates/' . $template . '/html/',
                Log::INFO,
                'installer'
            );

            return true;

        } catch (\Exception $e) {
            Log::add('HMB Overrides error: ' . $e->getMessage(), Log::ERROR, 'installer');
            return true; // No bloquear la instalación
        }
    }

    private function copyDir(string $src, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            $target = $dest . DIRECTORY_SEPARATOR . $items->getSubPathname();
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }
}
