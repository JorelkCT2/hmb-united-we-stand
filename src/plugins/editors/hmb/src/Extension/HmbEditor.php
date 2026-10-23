<?php
namespace HMB\Plugin\Editors\Hmb\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class HmbEditor extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = false;

    public static function getSubscribedEvents(): array
    {
        return [
            'onEditorDisplay'    => 'onEditorDisplay',
            'onEditorGetContent' => 'onEditorGetContent',
            'onEditorSetContent' => 'onEditorSetContent',
        ];
    }

    /**
     * Renderiza el editor en el formulario de artículo
     */
    public function onEditorDisplay(Event $event): void
    {
        $args = $event->getArguments();

        $name     = $args['name']    ?? ($args[0] ?? '');
        $content  = $args['content'] ?? ($args[1] ?? '');
        $width    = $args['width']   ?? ($args[2] ?? '100%');
        $height   = $args['height']  ?? ($args[3] ?? 400);
        $col      = $args['col']     ?? ($args[4] ?? 50);
        $row      = $args['row']     ?? ($args[5] ?? 20);
        $buttons  = $args['buttons'] ?? ($args[6] ?? true);
        $id       = $args['id']      ?? ($args[7] ?? $name);

        if (empty($id)) $id = $name;

        $editorId   = preg_replace('/[^a-z0-9_\-]/i', '_', $id);
        $fieldName  = $name;
        $safeContent= htmlspecialchars($content ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $rawContent = str_replace(
            ['\\', "'", "\r\n", "\r", "\n"],
            ['\\\\', "\\'", '\\n', '\\n', '\\n'],
            $content ?? ''
        );

        ob_start();
        include dirname(__DIR__, 2) . '/editor.php';
        $html = ob_get_clean();

        $event->addResult($html);
    }

    public function onEditorGetContent(Event $event): void
    {
        $args = $event->getArguments();
        $id   = $args['editor'] ?? ($args[0] ?? '');
        $id   = preg_replace('/[^a-z0-9_\-]/i', '_', $id);
        $event->addResult("document.getElementById('hmb_hidden_{$id}').value;");
    }

    public function onEditorSetContent(Event $event): void
    {
        $args = $event->getArguments();
        $id   = $args['editor'] ?? ($args[0] ?? '');
        $html = $args['html']   ?? ($args[1] ?? '');
        $id   = preg_replace('/[^a-z0-9_\-]/i', '_', $id);
        $esc  = addslashes($html);
        $event->addResult(
            "document.getElementById('hmb_hidden_{$id}').value='{$esc}';" .
            "document.getElementById('hmb-area-{$id}').innerHTML='{$esc}';"
        );
    }
}
