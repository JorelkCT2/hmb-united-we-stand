<?php
namespace Joomla\Component\Artistas\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\InputFilter;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

class MiperfilController extends BaseController
{
    public function save(): void
    {
        $app  = Factory::getApplication();

        $user = $app->getIdentity();

        if ($user->guest) {
            $app->redirect('index.php?option=com_users&view=login');
            return;
        }

        $db     = Factory::getContainer()->get('DatabaseDriver');
        $input  = $app->getInput();
        $filter = InputFilter::getInstance();
        $id     = $input->getInt('id', 0);

        // Verificar que la ficha pertenece al usuario
        if ($id > 0) {
            $db->setQuery('SELECT created_by FROM #__artistas WHERE id = ' . $id);
            $owner = (int)$db->loadResult();
            if ($owner !== (int)$user->id) {
                $app->enqueueMessage('No tienes permiso para editar esta ficha.', 'error');
                $app->redirect(RouteHelper::getUrl('miperfil'));
                return;
            }
        }

        $name  = $filter->clean($input->getString('name'), 'string');
        $email = $filter->clean($input->getString('email'), 'email');
        $genre = $filter->clean($input->getString('genre'), 'string');

        if (empty($name) || empty($email) || empty($genre)) {
            $app->enqueueMessage('Por favor rellena al menos nombre, email y género.', 'warning');
            $app->redirect(RouteHelper::getUrl('miperfil'));
            return;
        }

        // Alias
        $alias = trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9-]/', '-', strtolower($name))), '-');
        $q = 'SELECT COUNT(*) FROM #__artistas WHERE alias = ' . $db->quote($alias) . ($id > 0 ? ' AND id != ' . $id : '');
        $db->setQuery($q);
        if ((int)$db->loadResult() > 0) { $alias .= '-' . $user->id; }

        // Subida de imagen
        $uploadDir  = JPATH_ROOT . '/images/artistas/';
        if (!Folder::exists($uploadDir)) { Folder::create($uploadDir); }
        $safeName   = preg_replace('/[^a-z0-9_-]/', '', strtolower(str_replace(' ', '_', $name)));

        // Foto principal
        $imagePath = $filter->clean($input->getString('image_existing'), 'string');
        $mainFile  = $input->files->get('image');
        if (!empty($mainFile) && $mainFile['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($mainFile['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                $fname = $safeName . '_' . uniqid() . '.' . $ext;
                if (File::upload($mainFile['tmp_name'], $uploadDir . $fname)) {
                    $imagePath = 'images/artistas/' . $fname;
                }
            }
        }

        // Galería
        // gallery_existing viene como JSON (el botón ✕ ya habrá quitado las eliminadas)
        $gallery = json_decode($input->getString('gallery_existing', '[]'), true) ?: [];
        $galFiles = $input->files->get('gallery_new');
        if (!empty($galFiles)) {
            foreach ((array)$galFiles as $gf) {
                if (empty($gf) || $gf['error'] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($gf['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
                $fname = $safeName . '_gal_' . uniqid() . '.' . $ext;
                if (File::upload($gf['tmp_name'], $uploadDir . $fname)) {
                    $newP = 'images/artistas/' . $fname;
                    if (!in_array($newP, $gallery)) $gallery[] = $newP;
                }
            }
        }
        // Evitar que foto principal esté en galería
        if (!empty($imagePath)) {
            $gallery = array_values(array_filter($gallery, fn($g) => $g !== $imagePath));
        }

        // Componentes
        $compRaw = $input->get('componentes', [], 'array');
        $componentes = [];
        foreach ($compRaw as $c) {
            $n = trim($c['nombre'] ?? ''); $i = trim($c['instrumento'] ?? '');
            if ($n || $i) $componentes[] = ['nombre' => $n, 'instrumento' => $i];
        }

        // Vídeos
        $videosRaw = $input->get('videos', [], 'array');
        $videos = array_values(array_filter(array_map('trim', $videosRaw)));

        $now  = (new \DateTime())->format('Y-m-d H:i:s');
        $data = (object)[
            'name'               => $name,
            'alias'              => $alias,
            'short_desc'         => $filter->clean($input->getString('short_desc'), 'string'),
            'description'        => $filter->clean($input->getRaw('description'), 'html'),
            'biografia'          => $filter->clean($input->getRaw('biografia'), 'html'),
            'influencias'        => $filter->clean($input->getString('influencias'), 'string'),
            'componentes'        => json_encode($componentes),
            'genre'              => $genre,
            'provincia_origen'   => $filter->clean($input->getString('provincia_origen'), 'string'),
            'provincias_destino' => json_encode(array_values(array_filter($input->get('provincias_destino', [], 'array')))),
            'location'           => $filter->clean($input->getString('location'), 'string'),
            'formats'            => json_encode(array_filter((array)$input->get('formats', [], 'array'))),
            'events'             => json_encode(array_filter((array)$input->get('events', [], 'array'))),
            'image'              => $imagePath,
            'gallery'            => json_encode($gallery),
            'videos'             => json_encode($videos),
            'video_url'          => $videos[0] ?? '',
            'phone'              => $filter->clean($input->getString('phone'), 'string'),
            'email'              => $email,
            'website'            => $filter->clean($input->getString('website'), 'string'),
            'social_instagram'   => $filter->clean($input->getString('social_instagram'), 'string'),
            'social_facebook'    => $filter->clean($input->getString('social_facebook'), 'string'),
            'social_youtube'     => $filter->clean($input->getString('social_youtube'), 'string'),
            'social_tiktok'      => $filter->clean($input->getString('social_tiktok'), 'string'),
            'metadesc'           => mb_substr($filter->clean($input->getString('metadesc'), 'string'), 0, 160),
            'metakey'            => $filter->clean($input->getString('metakey'), 'string'),
            'discografia'        => (function() use ($input, $filter) {
                $titulos    = $input->get('disco_titulo', [], 'array');
                $anios      = $input->get('disco_anio', [], 'array');
                $productoras = $input->get('disco_productora', [], 'array');
                $existings  = $input->get('disco_portada_existing', [], 'array');
                $deletes    = $input->get('disco_delete_portada', [], 'array');
                $files      = $input->files->get('disco_portada', []);
                $discos = [];
                $uploadDir = JPATH_ROOT . '/images/artistas/discografia/';
                if (!\Joomla\CMS\Filesystem\Folder::exists($uploadDir)) \Joomla\CMS\Filesystem\Folder::create($uploadDir);
                foreach ($titulos as $k => $titulo) {
                    $titulo = trim($filter->clean($titulo, 'string'));
                    if (empty($titulo)) continue;
                    $portada = isset($deletes[$k]) ? '' : ($existings[$k] ?? '');
                    if (!empty($files[$k]) && $files[$k]['error'] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($files[$k]['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                            $fname = 'disco_' . uniqid() . '.' . $ext;
                            if (\Joomla\CMS\Filesystem\File::upload($files[$k]['tmp_name'], $uploadDir . $fname)) {
                                $portada = 'images/artistas/discografia/' . $fname;
                            }
                        }
                    }
                    $discos[] = [
                        'titulo'     => $titulo,
                        'anio'       => $filter->clean($anios[$k] ?? '', 'int'),
                        'productora' => $filter->clean($productoras[$k] ?? '', 'string'),
                        'portada'    => $portada,
                    ];
                }
                return json_encode($discos);
            })(),
            'modified'           => $now,
            'modified_by'        => (int)$user->id,
        ];

        if ($id > 0) {
            $data->id = $id;
            $db->updateObject('#__artistas', $data, 'id');
            $app->enqueueMessage('¡Ficha actualizada correctamente!', 'success');
        } else {
            $data->state      = 0; // Pendiente de aprobación
            $data->created    = $now;
            $data->created_by = (int)$user->id;
            $data->rating     = 0;
            $data->review_count = 0;
            $db->insertObject('#__artistas', $data);
            $app->enqueueMessage('¡Ficha enviada! Será revisada antes de publicarse.', 'success');
        }

        $app->redirect(RouteHelper::getUrl('miperfil'));
    }

    public function delete(): void
    {
        
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        if ($user->guest) {
            $app->redirect('index.php?option=com_users&view=login');
            return;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $app->getInput()->getInt('id');

        $db->setQuery('SELECT created_by FROM #__artistas WHERE id = ' . $id);
        $owner = (int)$db->loadResult();

        if ($owner !== (int)$user->id) {
            $app->enqueueMessage('No tienes permiso para borrar esta ficha.', 'error');
        } else {
            $db->setQuery('DELETE FROM #__artistas WHERE id = ' . $id . ' AND created_by = ' . (int)$user->id)->execute();
            $app->enqueueMessage('Ficha eliminada correctamente.', 'success');
        }

        $app->redirect(RouteHelper::getUrl('miperfil'));
    }
}
