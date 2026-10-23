<?php
namespace Joomla\Component\Artistas\Site\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

class BusquedaController extends BaseController
{
    // Banda publica búsqueda de músico
    public function save(): void
    {
        $app    = Factory::getApplication();
        $user   = $app->getIdentity();
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $input  = $app->getInput();
        $filter = InputFilter::getInstance();

        if ($user->guest) { $app->redirect('index.php?option=com_users&view=login'); return; }

        $artistaId   = $input->getInt('artista_id');
        $instrumento = $filter->clean($input->getString('instrumento'), 'string');
        $provincia   = $filter->clean($input->getString('provincia'), 'string');
        $ciudad      = $filter->clean($input->getString('ciudad'), 'string');
        $descripcion = $filter->clean($input->getString('descripcion'), 'string');
        $estilos     = json_encode($input->get('estilos', [], 'array'));
        $now         = date('Y-m-d H:i:s');

        // Verify ownership
        $db->setQuery('SELECT id, name, email FROM #__artistas WHERE id=' . $artistaId . ' AND created_by=' . (int)$user->id . ' AND state=1');
        $artista = $db->loadObject();
        if (!$artista) { $app->enqueueMessage('Sin permiso.', 'error'); $app->redirect(RouteHelper::getUrl('miperfil')); return; }

        $db->setQuery("INSERT INTO #__artistas_busquedas (artista_id,instrumento,provincia,ciudad,estilos,descripcion,state,created)
            VALUES ($artistaId," . $db->quote($instrumento) . "," . $db->quote($provincia) . "," . $db->quote($ciudad) . "," . $db->quote($estilos) . "," . $db->quote($descripcion) . ",1," . $db->quote($now) . ")")->execute();
        $busquedaId = (int)$db->insertid();

        $app->enqueueMessage('Búsqueda publicada. Te avisaremos si encontramos coincidencias.', 'success');

        // Check for immediate matches
        $this->checkMatchesForBand($busquedaId, $artista, $instrumento, $provincia, $db, $app);

        $miItemid = RouteHelper::getItemid('miperfil');
        $app->redirect('index.php?option=com_artistas&view=miperfil' . ($miItemid ? '&Itemid='.$miItemid : ''));
    }

    private function checkMatchesForBand(int $busquedaId, object $artista, string $instrumento, string $provincia, $db, $app): void
    {
        $q = 'SELECT * FROM #__artistas_musicos WHERE state=1';
        if ($instrumento) $q .= ' AND instrumentos LIKE ' . $db->quote('%' . $instrumento . '%');
        if ($provincia)   $q .= ' AND provincia = ' . $db->quote($provincia);
        $db->setQuery($q);
        $musicos = $db->loadObjectList() ?: [];

        if (empty($musicos)) return;

        foreach ($musicos as $m) {
            try {
                $db->setQuery("INSERT IGNORE INTO #__artistas_matches (musico_id,busqueda_id,created) VALUES (" . $m->id . ",$busquedaId,'" . date('Y-m-d H:i:s') . "')")->execute();
            } catch (\Exception $e) {}
        }

        // Alert band immediately
        $count = count($musicos);
        $app->enqueueMessage('🎸 ¡Coincidencia inmediata! Hay ' . $count . ' músico(s) que encajan con tu búsqueda.', 'notice');

        // Send email to band
        if (!empty($artista->email)) {
            try {
                $mailer = Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer();
                $mailer->addRecipient($artista->email);
                $mailer->setSubject('🎸 ¡' . $count . ' músico(s) encontrado(s) para tu búsqueda!');
                $body  = "Hola " . $artista->name . ",\n\n";
                $body .= "Hay $count músico(s) en Heavy Metal Brothers que encajan con tu búsqueda de $instrumento.\n\n";
                foreach ($musicos as $m) {
                    $body .= "• " . $m->nombre . " | " . $m->instrumentos . " | " . $m->provincia;
                    if ($m->email)    $body .= " | " . $m->email;
                    if ($m->whatsapp) $body .= " | WA: " . $m->whatsapp;
                    $body .= "\n";
                }
                $body .= "\nEntra en heavymetalbrothers.com para ver los perfiles completos.\n";
                $mailer->setBody($body);
                $mailer->Send();
            } catch (\Exception $e) {}
        }
    }

    public function delete(): void
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity();
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $id   = $app->getInput()->getInt('id');

        $db->setQuery('SELECT b.id FROM #__artistas_busquedas b JOIN #__artistas a ON a.id=b.artista_id WHERE b.id=' . $id . ' AND a.created_by=' . (int)$user->id);
        if ($db->loadResult()) {
            $db->setQuery('DELETE FROM #__artistas_busquedas WHERE id=' . $id)->execute();
            $app->enqueueMessage('Búsqueda eliminada.', 'success');
        }
        $miItemid = RouteHelper::getItemid('miperfil');
        $app->redirect('index.php?option=com_artistas&view=miperfil' . ($miItemid ? '&Itemid='.$miItemid : ''));
    }
}
