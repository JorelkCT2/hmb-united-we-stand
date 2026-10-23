<?php
namespace Joomla\Component\Artistas\Site\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

class MusicoController extends BaseController
{
    public function save(): void
    {
        $app    = Factory::getApplication();
        $user   = $app->getIdentity();
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $input  = $app->getInput();
        $filter = InputFilter::getInstance();

        if ($user->guest) { $app->redirect('index.php?option=com_users&view=login'); return; }

        $uid = (int)$user->id;
        $now = date('Y-m-d H:i:s');

        // Read fields
        $nombre       = $filter->clean($input->getString('nombre'), 'string');
        // Combine instrument dropdowns + otro
        $instArr  = array_filter(array_map('trim', $input->get('inst_select', [], 'array')));
        $instOtro = trim($filter->clean($input->getString('inst_otro'), 'string'));
        if ($instOtro) $instArr[] = $instOtro;
        $instrumentos = implode(', ', array_unique($instArr));
        $provincia    = $filter->clean($input->getString('provincia'), 'string');
        $ciudad       = $filter->clean($input->getString('ciudad'), 'string');
        $bio          = $filter->clean($input->getString('bio'), 'string');
        $email        = $filter->clean($input->getString('email'), 'email');
        $whatsapp     = $filter->clean($input->getString('whatsapp'), 'string');
        $estilosArr   = $input->get('estilos', [], 'array');
        $estilos      = json_encode(array_filter(array_map('trim', $estilosArr)));
        $not_email    = $input->getInt('notificar_email', 0);
        $not_wa       = $input->getInt('notificar_whatsapp', 0);

        // Foto
        $foto = $filter->clean($input->getString('foto_existing'), 'string');
        $fotoFile = $input->files->get('foto');
        if (!empty($fotoFile) && $fotoFile['error'] === UPLOAD_ERR_OK) {
            $dir = JPATH_ROOT . '/images/artistas/musicos/';
            if (!Folder::exists($dir)) Folder::create($dir);
            $ext = strtolower(pathinfo($fotoFile['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $fname = 'musico_' . $uid . '_' . time() . '.' . $ext;
                if (File::upload($fotoFile['tmp_name'], $dir . $fname)) {
                    $foto = 'images/artistas/musicos/' . $fname;
                }
            }
        }

        // Check existing
        $db->setQuery('SELECT id FROM #__artistas_musicos WHERE user_id=' . $uid . ' LIMIT 1');
        $existing = (int)$db->loadResult();

        if ($existing) {
            $db->setQuery("UPDATE #__artistas_musicos SET
                nombre=" . $db->quote($nombre) . ", instrumentos=" . $db->quote($instrumentos) . ",
                provincia=" . $db->quote($provincia) . ", ciudad=" . $db->quote($ciudad) . ",
                estilos=" . $db->quote($estilos) . ", bio=" . $db->quote($bio) . ",
                email=" . $db->quote($email) . ", whatsapp=" . $db->quote($whatsapp) . ",
                foto=" . $db->quote($foto) . ", notificar_email=$not_email,
                notificar_whatsapp=$not_wa, modified=" . $db->quote($now) . "
                WHERE user_id=$uid")->execute();
            $app->enqueueMessage('Perfil actualizado.', 'success');
        } else {
            $db->setQuery("INSERT INTO #__artistas_musicos (user_id,nombre,instrumentos,provincia,ciudad,estilos,bio,email,whatsapp,foto,notificar_email,notificar_whatsapp,state,created,modified)
                VALUES ($uid," . $db->quote($nombre) . "," . $db->quote($instrumentos) . "," . $db->quote($provincia) . "," . $db->quote($ciudad) . "," . $db->quote($estilos) . "," . $db->quote($bio) . "," . $db->quote($email) . "," . $db->quote($whatsapp) . "," . $db->quote($foto) . ",$not_email,$not_wa,1," . $db->quote($now) . "," . $db->quote($now) . ")")->execute();
            $musicoId = (int)$db->insertid();
            $app->enqueueMessage('Perfil de músico creado. Ya apareces en el directorio.', 'success');
            // Check for immediate matches
            $this->checkMatches($musicoId, $db, $app);
        }

        $miItemid = RouteHelper::getItemid('musico');
        $app->redirect('index.php?option=com_artistas&view=musico' . ($miItemid ? '&Itemid='.$miItemid : ''));
    }

    private function checkMatches(int $musicoId, $db, $app): void
    {
        // Get musician data
        $db->setQuery('SELECT * FROM #__artistas_musicos WHERE id=' . $musicoId);
        $musico = $db->loadObject();
        if (!$musico) return;

        // Find matching band searches
        $db->setQuery('SELECT b.*, a.name AS artista_name, a.email AS artista_email FROM #__artistas_busquedas b LEFT JOIN #__artistas a ON a.id=b.artista_id WHERE b.state=1');
        $busquedas = $db->loadObjectList() ?: [];

        foreach ($busquedas as $b) {
            $match = false;
            // Check instrumento
            if ($b->instrumento && $musico->instrumentos) {
                $instBusq = strtolower($b->instrumento);
                $instMusico = strtolower($musico->instrumentos);
                if (strpos($instMusico, $instBusq) !== false) $match = true;
            }
            // Check provincia
            if ($match && $b->provincia && $musico->provincia && $b->provincia !== $musico->provincia) $match = false;

            if ($match) {
                try {
                    $db->setQuery("INSERT IGNORE INTO #__artistas_matches (musico_id,busqueda_id,created) VALUES ($musicoId," . $b->id . ",'" . date('Y-m-d H:i:s') . "')")->execute();
                    // Notify band by email
                    $this->notifyBand($musico, $b, $app);
                } catch (\Exception $e) {}
            }
        }
    }

    private function notifyBand(object $musico, object $busqueda, $app): void
    {
        if (empty($busqueda->artista_email)) return;
        try {
            $mailer = Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer();
            $mailer->addRecipient($busqueda->artista_email);
            $mailer->setSubject('🎸 ¡Coincidencia! Un músico encaja con tu búsqueda en Heavy Metal Brothers');
            $body  = "¡Buenas noticias, " . $busqueda->artista_name . "!\n\n";
            $body .= "Un músico que busca banda coincide con tu búsqueda de " . $busqueda->instrumento . ".\n\n";
            $body .= "Músico: " . $musico->nombre . "\n";
            $body .= "Instrumento: " . $musico->instrumentos . "\n";
            $body .= "Provincia: " . $musico->provincia . "\n";
            if ($musico->email) $body .= "Email: " . $musico->email . "\n";
            if ($musico->whatsapp) $body .= "WhatsApp: " . $musico->whatsapp . "\n\n";
            $body .= "Entra en heavymetalbrothers.com para ver más detalles.\n";
            $mailer->setBody($body);
            $mailer->Send();
        } catch (\Exception $e) {}
    }
}
