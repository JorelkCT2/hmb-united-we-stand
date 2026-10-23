<?php
namespace Joomla\Component\Hmgallery\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;

/**
 * Campo selector de carpetas para HMGallery.
 * Muestra un desplegable con todas las carpetas dentro de images/
 * para que el usuario elija la carpeta del álbum sin escribir a mano.
 */
class HmgFolderField extends ListField
{
    protected $type = 'HmgFolder';

    protected function getOptions(): array
    {
        $options   = parent::getOptions();
        $base      = JPATH_ROOT . '/images';
        $folders   = $this->scanFolders($base, 'images', 0);

        foreach ($folders as [$path, $depth]) {
            $opt          = new \stdClass();
            $opt->value   = $path;
            $opt->text    = str_repeat('— ', $depth) . basename($path);
            $opt->disable = false;
            $opt->class   = '';
            $options[]    = $opt;
        }

        return $options;
    }

    /**
     * Escanea recursivamente las carpetas bajo $dir.
     * Devuelve array de [ruta_relativa, profundidad].
     */
    private function scanFolders(string $dir, string $prefix, int $depth): array
    {
        $result = [];
        if ($depth > 8 || !is_dir($dir)) return $result;

        $items = @scandir($dir);
        if (!$items) return $result;

        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || str_starts_with($item, '.')) continue;
            // Excluir carpetas de miniaturas y sistema
            if (in_array(strtolower($item), ['thumbs', '.thumbs', 'cache', 'tmp'], true)) continue;
            $full = $dir . '/' . $item;
            if (!is_dir($full)) continue;

            $rel      = $prefix . '/' . $item;
            $result[] = [$rel, $depth];

            // Recurse
            $sub    = $this->scanFolders($full, $rel, $depth + 1);
            $result = array_merge($result, $sub);
        }

        return $result;
    }
}
