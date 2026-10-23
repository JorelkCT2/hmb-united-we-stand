<?php
namespace Joomla\Component\Artistas\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

class ArtistaModel extends AdminModel
{
    public $typeAlias = 'com_artistas.artista';

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm('com_artistas.artista', 'artista', ['control' => 'jform', 'load_data' => $loadData]);
        return $form ?: false;
    }

    protected function loadFormData()
    {
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_artistas.edit.artista.data', []);
        if (empty($data)) {
            $data = $this->getItem();
            if ($data) {
                foreach (['formats','events','componentes','provincias_destino','gallery','videos'] as $f) {
                    if (!empty($data->$f) && is_string($data->$f)) {
                        $data->$f = json_decode($data->$f, true) ?: [];
                    }
                }
            }
        }
        return $data;
    }

    public function save($data)
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        // Componentes dinámicos desde POST array
        $compRaw = $input->get('componentes', [], 'array');
        $componentes = [];
        foreach ($compRaw as $c) {
            $nombre     = trim($c['nombre'] ?? '');
            $instrumento= trim($c['instrumento'] ?? '');
            if ($nombre || $instrumento) {
                $componentes[] = ['nombre' => $nombre, 'instrumento' => $instrumento];
            }
        }
        $data['componentes'] = json_encode($componentes);

        // Vídeos
        $videosRaw = $input->get('videos', [], 'array');
        $videos = array_values(array_filter(array_map('trim', $videosRaw)));
        $data['videos']    = json_encode($videos);
        $data['video_url'] = $videos[0] ?? '';

        // Provincias destino
        $provRaw = $input->get('provincias_destino', [], 'array');
        $data['provincias_destino'] = json_encode(array_values(array_filter($provRaw)));

        // Formatos y eventos
        if (isset($data['formats']) && is_array($data['formats'])) {
            $data['formats'] = json_encode($data['formats']);
        }
        if (isset($data['events']) && is_array($data['events'])) {
            $data['events'] = json_encode($data['events']);
        }

        // Galería existente - viene en jform['gallery_existing'] como JSON
        $galleryExisting = json_decode($data['gallery_existing'] ?? '[]', true) ?: [];

        // gallery_existing ahora viene en $data['gallery_existing'] como JSON
        // (el botón ✕ quita fotos del array antes de enviar)

        // Nuevas fotos galería
        $uploadDir = JPATH_ROOT . '/images/artistas/';
        if (!Folder::exists($uploadDir)) { Folder::create($uploadDir); }

        $files = $input->files->get('gallery_new');
        if (!empty($files)) {
            $safeName = preg_replace('/[^a-z0-9_-]/', '', strtolower(str_replace(' ', '_', $data['name'] ?? 'artista')));
            foreach ((array)$files as $file) {
                if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
                $fname = $safeName . '_gal_' . uniqid() . '.' . $ext;
                if (File::upload($file['tmp_name'], $uploadDir . $fname)) {
                    $newPath = 'images/artistas/' . $fname;
                    // Evitar duplicados
                    if (!in_array($newPath, $galleryExisting)) {
                        $galleryExisting[] = $newPath;
                    }
                }
            }
        }

        // Eliminar foto principal de la galería si está duplicada
        $mainImage = $data['image'] ?? '';
        if ($mainImage) {
            $galleryExisting = array_values(array_filter($galleryExisting, fn($g) => $g !== $mainImage));
        }

        $data['gallery'] = json_encode(array_values($galleryExisting));

        return parent::save($data);
    }

    public function getTable($name = 'Artista', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
