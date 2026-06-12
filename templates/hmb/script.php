<?php
/**
 * HMB Template — Install/Upgrade Script
 * Limpia overrides de módulos obsoletos.
 *
 * Naming para Joomla 6: tpl_ + element = tpl_hmb_template
 * → clase: Tpl_Hmb_templateInstallerScript
 */
\defined('_JEXEC') or die;

// phpcs:disable PSR1.Classes.ClassDeclaration
class Tpl_Hmb_templateInstallerScript
{
    public function postflight(string $type, object $parent): bool
    {
        $this->deleteStaleOverrides();
        return true;
    }

    public function update(object $parent): bool
    {
        $this->deleteStaleOverrides();
        return true;
    }

    private function deleteStaleOverrides(): void
    {
        $base = JPATH_SITE . '/templates/hmb_template/html';
        $dirs = [
            $base . '/mod_hmb_register',
            $base . '/mod_hmb_login',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            foreach (glob($dir . '/*') ?: [] as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($dir);
        }
    }
}
