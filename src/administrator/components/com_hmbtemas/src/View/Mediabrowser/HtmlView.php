<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\View\Mediabrowser;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    public string $fieldId   = '';
    public string $type      = 'audio';   // 'audio' o 'image'
    public array  $files     = [];
    public string $siteRoot  = '';
    public string $folder    = '';

    public function display($tpl = null): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $this->fieldId  = $input->getString('field', 'jform_audio_file');
        $this->type     = $input->getString('type', 'audio');  // audio | image
        $this->siteRoot = Uri::root();

        // Extensiones permitidas según tipo
        $extAudio = ['mp3', 'ogg', 'wav', 'm4a'];
        $extImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $allowed  = $this->type === 'image' ? $extImage : $extAudio;

        // Buscar archivos en /images/ recursivamente
        $imagesPath = JPATH_ROOT . '/images';
        $this->files = [];

        if (Folder::exists($imagesPath)) {
            $allFiles = Folder::files($imagesPath, '.', true, true);
            foreach ($allFiles as $fullPath) {
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed, true)) {
                    // Ruta relativa desde la raíz del sitio
                    $relative = ltrim(str_replace(JPATH_ROOT, '', $fullPath), '/\\');
                    $relative = str_replace('\\', '/', $relative);
                    $this->files[] = [
                        'path'  => $relative,
                        'name'  => basename($fullPath),
                        'folder'=> basename(dirname($fullPath)),
                        'ext'   => $ext,
                    ];
                }
            }
        }

        parent::display($tpl);
    }
}
