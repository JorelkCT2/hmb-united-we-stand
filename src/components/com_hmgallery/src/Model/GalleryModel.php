<?php
namespace Joomla\Component\Hmgallery\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

class GalleryModel extends BaseDatabaseModel
{
    private const ALLOWED_EXT  = ['jpg','jpeg','png','gif','webp','avif'];
    private const SKIP_FOLDERS = ['thumbs','.thumbs','cache','tmp','sampledata'];

    private function getBasePath(): string
    {
        return trim(ComponentHelper::getParams('com_hmgallery')->get('base_path', 'images'), '/');
    }

    // ── ALBUMS ────────────────────────────────────────────────────────
    public function getAlbums(?int $parentId = null): array
    {
        // Si hay álbumes en BD → mostrar SOLO esos (el admin controla qué se ve)
        $dbAlbums = $this->getDbAlbums($parentId);
        if (!empty($dbAlbums)) {
            return $dbAlbums;
        }

        // Sin álbumes en BD → auto-descubrir filesystem
        if ($parentId === null || $parentId === 0) {
            return $this->getFsAlbums($this->getBasePath(), 0);
        }

        return [];
    }

    private function getDbAlbums(?int $parentId): array
    {
        try {
            $db = $this->getDatabase();
            $q  = $db->getQuery(true);
            $q->select(['id','title','alias','description','folder_path','cover_image','parent_id','ordering','hits'])
              ->from($db->quoteName('#__hmgallery_albums'))
              ->where($db->quoteName('state') . ' = 1')
              ->order($db->quoteName('ordering') . ' ASC, ' . $db->quoteName('title') . ' ASC');

            if ($parentId !== null) {
                $q->where('('
                    . $db->quoteName('parent_id') . ' = :pid OR ('
                    . $db->quoteName('parent_id') . ' IS NULL AND :pid2 = 0))')
                  ->bind(':pid',  $parentId, ParameterType::INTEGER)
                  ->bind(':pid2', $parentId, ParameterType::INTEGER);
            }

            return $db->setQuery($q)->loadObjectList() ?: [];
        } catch (\Exception) { return []; }
    }

    private function getFsAlbums(string $folderPath, int $parentId): array
    {
        $albums  = [];
        $absPath = JPATH_ROOT . '/' . $folderPath;
        if (!is_dir($absPath)) return $albums;

        foreach (@scandir($absPath) ?: [] as $item) {
            if ($item === '.' || $item === '..' || str_starts_with($item, '.')) continue;
            if (in_array(strtolower($item), self::SKIP_FOLDERS, true)) continue;
            $full = $absPath . '/' . $item;
            if (!is_dir($full)) continue;
            $rel = $folderPath . '/' . $item;
            if (!$this->folderHasImages($full) && !$this->folderHasSubdirs($full)) continue;

            $cover              = $this->findCoverImage($full, $rel);
            $album              = new \stdClass();
            $album->id          = abs(crc32($rel)) % 2147483647 ?: 1;
            $album->title       = ucwords(str_replace(['-','_'], ' ', $item));
            $album->alias       = $item;
            $album->description = '';
            $album->folder_path = $rel;
            $album->cover_image = $cover;
            $album->parent_id   = $parentId;
            $album->ordering    = 0;
            $album->hits        = 0;
            $albums[]           = $album;
        }

        usort($albums, fn($a,$b) => strcmp($a->title, $b->title));
        return $albums;
    }

    // ── SINGLE ALBUM ──────────────────────────────────────────────────
    public function getAlbum(int $id): ?object
    {
        $album = $this->getDbAlbum($id) ?? $this->getFsAlbumById($id);
        if ($album) {
            $album->images   = $this->getImagesFromFolder($album->folder_path);
            $album->children = $this->getFsAlbums($album->folder_path, $id);
            $dbChildren = $this->getDbAlbums($id);
            if (!empty($dbChildren)) {
                $album->children = array_merge($dbChildren, $album->children);
            }
        }
        return $album;
    }

    private function getDbAlbum(int $id): ?object
    {
        try {
            $db = $this->getDatabase();
            $q  = $db->getQuery(true);
            $q->select('*')
              ->from($db->quoteName('#__hmgallery_albums'))
              ->where($db->quoteName('id') . ' = :id')
              ->where($db->quoteName('state') . ' = 1')
              ->bind(':id', $id, ParameterType::INTEGER);
            $album = $db->setQuery($q)->loadObject();
            if ($album) $this->incrementHits($id);
            return $album;
        } catch (\Exception) { return null; }
    }

    private function getFsAlbumById(int $id): ?object
    {
        return $this->searchFsAlbum($id, JPATH_ROOT . '/' . $this->getBasePath(), $this->getBasePath());
    }

    private function searchFsAlbum(int $id, string $abs, string $rel): ?object
    {
        foreach (@scandir($abs) ?: [] as $item) {
            if ($item === '.' || $item === '..' || str_starts_with($item, '.')) continue;
            $full   = $abs . '/' . $item;
            if (!is_dir($full)) continue;
            $relFull = $rel . '/' . $item;
            $virtId  = abs(crc32($relFull)) % 2147483647 ?: 1;
            if ($virtId === $id) {
                $album              = new \stdClass();
                $album->id          = $virtId;
                $album->title       = ucwords(str_replace(['-','_'], ' ', $item));
                $album->alias       = $item;
                $album->description = '';
                $album->folder_path = $relFull;
                $album->cover_image = $this->findCoverImage($full, $relFull);
                $album->parent_id   = 0;
                $album->hits        = 0;
                return $album;
            }
            $found = $this->searchFsAlbum($id, $full, $relFull);
            if ($found) return $found;
        }
        return null;
    }

    // ── IMAGES ────────────────────────────────────────────────────────
    public function getImagesFromFolder(string $folderPath): array
    {
        $images = [];
        $base   = JPATH_ROOT . '/' . ltrim($folderPath, '/');
        if (!is_dir($base)) return $images;
        foreach (@scandir($base) ?: [] as $file) {
            if ($file === '.' || $file === '..') continue;
            $fp = $base . '/' . $file;
            if (!is_file($fp)) continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, self::ALLOWED_EXT, true)) continue;
            $rel  = rtrim($folderPath, '/') . '/' . $file;
            $sz   = @getimagesize($fp);
            $images[] = (object)[
                'filename' => $file,
                'url'      => Uri::root() . ltrim($rel, '/'),
                'title'    => pathinfo($file, PATHINFO_FILENAME),
                'width'    => $sz ? $sz[0] : 0,
                'height'   => $sz ? $sz[1] : 0,
                'modified' => @filemtime($fp) ?: 0,
            ];
        }
        usort($images, fn($a,$b) => strcmp($a->filename, $b->filename));
        return $images;
    }

    // ── BREADCRUMB ────────────────────────────────────────────────────
    public function getBreadcrumb(int $albumId): array
    {
        $trail = []; $id = $albumId;
        while ($id > 0) {
            $album = $this->getDbAlbum($id);
            if (!$album) {
                $fs = $this->getFsAlbumById($id);
                if ($fs) array_unshift($trail, $fs);
                break;
            }
            array_unshift($trail, $album);
            $id = (int)$album->parent_id;
        }
        return $trail;
    }

    // ── HELPERS ───────────────────────────────────────────────────────
    private function folderHasImages(string $path): bool
    {
        foreach (@scandir($path) ?: [] as $f) {
            if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), self::ALLOWED_EXT, true)) return true;
        }
        return false;
    }

    private function folderHasSubdirs(string $path): bool
    {
        foreach (@scandir($path) ?: [] as $f) {
            if ($f !== '.' && $f !== '..' && is_dir($path . '/' . $f)) return true;
        }
        return false;
    }

    private function findCoverImage(string $absPath, string $relPath): ?string
    {
        foreach (@scandir($absPath) ?: [] as $f) {
            if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), self::ALLOWED_EXT, true)) {
                return $relPath . '/' . $f;
            }
        }
        return null;
    }

    private function incrementHits(int $id): void
    {
        try {
            $db = $this->getDatabase();
            $q  = $db->getQuery(true);
            $q->update($db->quoteName('#__hmgallery_albums'))
              ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
              ->where($db->quoteName('id') . ' = :id')
              ->bind(':id', $id, ParameterType::INTEGER);
            $db->setQuery($q)->execute();
        } catch (\Exception) {}
    }
}
