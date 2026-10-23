<?php
namespace Joomla\Plugin\Editors\Hmb\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

/**
 * HMB Simplified Editor Plugin for Joomla 6
 */
final class Hmb extends CMSPlugin
{
    protected $autoloadLanguage = false;

    /**
     * Display the editor
     * NOTE: Joomla 6 calls "onDisplay" on editor plugins (same as JCE pattern)
     */
    public function onDisplay(
        $name,
        $content,
        $width    = '100%',
        $height   = '500',
        $col      = 50,
        $row      = 20,
        $buttons  = true,
        $id       = null,
        $asset    = null,
        $author   = null,
        $params   = []
    ) {
        if (empty($id)) {
            $id = $name;
        }

        // Sanitizar ID para uso en JS/CSS
        $editorId  = preg_replace('/[^A-Za-z0-9\-_]/', '_', $id);
        $fieldName = $name;

        // Decodificar entidades HTML previas (contenido guardado con doble escape)
        $cleanContent = html_entity_decode($content ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Para el textarea oculto: Joomla espera el HTML tal cual
        $safeContent = htmlspecialchars($cleanContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Para JS: json_encode garantiza encoding seguro para cualquier carácter
        $jsContent = json_encode($cleanContent);

        $layoutPath = JPATH_PLUGINS . '/editors/hmb/layouts/editor/hmb.php';

        ob_start();
        include $layoutPath;
        return ob_get_clean();
    }
}
