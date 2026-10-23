<?php
namespace Joomla\Component\Artistas\Site\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Uri\Uri;

class RegistroController extends BaseController
{
    public function submit(): void
    {
        
        $app    = Factory::getApplication();
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $input  = $app->getInput();
        $filter = InputFilter::getInstance();

        $name      = $filter->clean($input->getString('name'), 'string');
        $email     = $filter->clean($input->getString('email'), 'email');
        $genre     = $filter->clean($input->getString('genre'), 'string');

        if (empty($name) || empty($email) || empty($genre)) {
            $app->enqueueMessage('Por favor rellena al menos: nombre, email y género musical.', 'warning');
            $app->redirect(RouteHelper::getUrl('registro'));
            return;
        }

        $uploadDir = JPATH_ROOT . '/images/artistas/';
        if (!Folder::exists($uploadDir)) { Folder::create($uploadDir); }
        $safeName = preg_replace('/[^a-z0-9_-]/', '', strtolower(str_replace(' ', '_', $name)));

        // Foto principal
        $imagePath = '';
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
        $gallery = [];
        $galFiles = $input->files->get('gallery_new');
        if (!empty($galFiles)) {
            foreach ((array)$galFiles as $gf) {
                if (empty($gf) || $gf['error'] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($gf['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
                $fname = $safeName . '_gal_' . uniqid() . '.' . $ext;
                if (File::upload($gf['tmp_name'], $uploadDir . $fname)) {
                    $gallery[] = 'images/artistas/' . $fname;
                }
            }
        }

        // Componentes
        $compRaw = $input->get('componentes', [], 'array');
        $componentes = [];
        foreach ($compRaw as $c) {
            $n = trim($c['nombre'] ?? '');
            $i = trim($c['instrumento'] ?? '');
            if ($n || $i) $componentes[] = ['nombre' => $n, 'instrumento' => $i];
        }

        // Vídeos
        $videosRaw = $input->get('videos', [], 'array');
        $videos = array_values(array_filter(array_map('trim', $videosRaw)));

        // Provincias destino
        $provRaw = $input->get('provincias_destino', [], 'array');
        $provDestino = array_values(array_filter($provRaw));

        // Alias único
        $alias = trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9-]/', '-', strtolower($name))), '-');
        $db->setQuery('SELECT COUNT(*) FROM #__artistas WHERE alias = ' . $db->quote($alias));
        if ((int)$db->loadResult() > 0) { $alias .= '-' . uniqid(); }

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
            'tipo_banda'         => in_array($input->getString('tipo_banda'), ['propias','versiones','ambas']) ? $input->getString('tipo_banda') : 'propias',
            'provincia_origen'   => $filter->clean($input->getString('provincia_origen'), 'string'),
            'provincias_destino' => json_encode($provDestino),
            'location'           => $filter->clean($input->getString('location'), 'string'),
            'formats'            => json_encode(array_filter(array_map('trim', (array)$input->get('formats', [], 'array')))),
            'events'             => json_encode(array_filter(array_map('trim', (array)$input->get('events', [], 'array')))),
            'image'              => $imagePath,
            'gallery'            => json_encode($gallery),
            'videos'             => json_encode($videos),
            'video_url'          => $videos[0] ?? '',
            'phone'              => $filter->clean($input->getString('phone'), 'string'),
            'email'              => $email,
            'metadesc'           => mb_substr($filter->clean($input->getString('metadesc'), 'string'), 0, 160),
            'metakey'            => $filter->clean($input->getString('metakey'), 'string'),
            'website'            => $filter->clean($input->getString('website'), 'string'),
            'social_instagram'   => $filter->clean($input->getString('social_instagram'), 'string'),
            'social_facebook'    => $filter->clean($input->getString('social_facebook'), 'string'),
            'social_youtube'     => $filter->clean($input->getString('social_youtube'), 'string'),
            'social_tiktok'      => $filter->clean($input->getString('social_tiktok'), 'string'),
            'rating'             => 0,
            'review_count'       => 0,
            'state'              => 0,
            'ordering'           => 0,
            'created'            => $now,
            'created_by'         => (int)Factory::getApplication()->getIdentity()->id,
        ];
        // Evitar que la foto principal aparezca también en la galería
        if (!empty($data->image) && !empty($data->gallery)) {
            $gal = json_decode($data->gallery, true) ?: [];
            $gal = array_values(array_filter($gal, fn($g) => $g !== $data->image));
            $data->gallery = json_encode($gal);
        }
        $db->insertObject('#__artistas', $data);

        // Email al admin con enlace directo
        try {
            $config   = $app->getConfig();
            $mailer   = Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer();
            $mailer->addRecipient($config->get('mailfrom'));
            $mailer->setSubject('⚡ Nueva banda pendiente de aprobación: ' . $name);
            $adminUrl = Uri::root() . 'administrator/index.php?option=com_artistas&view=artistas&filter_state=0';
            $body  = "Se ha registrado una nueva banda que necesita tu aprobación.\n\n";
            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $body .= "Nombre:    {$name}\n";
            $body .= "Género:    {$genre}\n";
            $body .= "Email:     {$email}\n";
            $body .= "Teléfono:  " . ($data->phone ?? '-') . "\n";
            $body .= "Provincia: " . ($data->provincia_origen ?? '-') . "\n";
            $body .= "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $body .= "👉 Ver pendientes y aprobar:\n{$adminUrl}\n";
            $mailer->setBody($body);
            $mailer->Send();
        } catch (\Exception $e) {}

        $app->enqueueMessage('¡Registro enviado correctamente! Lo revisaremos en 24-48h y te avisaremos por email.', 'success');
        // Redirigir al registro con Itemid para que Gantry5 no interfiera con la sesión
        $registroItemid = \Joomla\Component\Artistas\Site\Helper\RouteHelper::getItemid('registro');
        $redirectUrl = 'index.php?option=com_artistas&view=registro' . ($registroItemid ? '&Itemid=' . $registroItemid : '');
        $app->redirect($redirectUrl);
    }
}
