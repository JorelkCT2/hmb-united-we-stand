<?php
namespace Joomla\Component\Artistas\Site\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;

class ArtistaModel extends ItemModel
{
    protected $_item = null;

    public function getItem($id = null)
    {
        if ($this->_item === null) {
            $id  = $id ?: Factory::getApplication()->getInput()->getInt('id');
            $db  = $this->getDatabase();
            $db->setQuery('SELECT a.* FROM #__artistas AS a WHERE a.id = ' . (int)$id . ' AND a.state = 1');
            $item = $db->loadObject();
            if ($item) {
                foreach (['formats','events','componentes','provincias_destino','gallery','videos'] as $f) {
                    $item->$f = !empty($item->$f) ? (json_decode($item->$f, true) ?: []) : [];
                }
                // Compatibilidad: si no hay videos array pero sí video_url
                if (empty($item->videos) && !empty($item->video_url)) {
                    $item->videos = [$item->video_url];
                }
                // Evitar que la foto principal aparezca duplicada en la galería
                if (!empty($item->image) && !empty($item->gallery)) {
                    $item->gallery = array_values(array_filter(
                        $item->gallery, fn($g) => $g !== $item->image
                    ));
                }
                $item->resenas = $this->getResenas($id);
            }
            $this->_item = $item;
        }
        return $this->_item;
    }

    protected function getResenas(int $id): array
    {
        $db = $this->getDatabase();
        $db->setQuery('SELECT reviewer_name, rating, review, event_type, created FROM #__artistas_resenas WHERE artista_id=' . $id . ' AND state=1 ORDER BY created DESC');
        return $db->loadObjectList() ?: [];
    }

    public function getRelacionados(string $genre, int $excludeId, int $limit = 3): array
    {
        $db = $this->getDatabase();
        $db->setQuery('SELECT id, name, alias, genre, location, image, rating FROM #__artistas WHERE state=1 AND id!=' . $excludeId . ' AND genre=' . $db->quote($genre) . ' LIMIT ' . $limit);
        return $db->loadObjectList() ?: [];
    }
}
