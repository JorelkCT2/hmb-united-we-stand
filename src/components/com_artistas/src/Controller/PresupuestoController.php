<?php
namespace Joomla\Component\Artistas\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;

class PresupuestoController extends BaseController
{
    public function submit(): void
    {
        
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $input = $app->getInput();

        $artistaId = $input->getInt('artista_id');
        if (!$artistaId) {
            $app->enqueueMessage('Artista no válido.', 'error');
            $app->redirect(Route::_('index.php?option=com_artistas&view=artistas'));
            return;
        }

        // Obtener nombre del artista
        $query = $db->getQuery(true)->select('name')->from('#__artistas')->where('id=' . $artistaId . ' AND state=1');
        $db->setQuery($query);
        $artistaName = $db->loadResult();

        if (!$artistaName) {
            $app->enqueueMessage('Artista no encontrado.', 'error');
            $app->redirect(Route::_('index.php?option=com_artistas&view=artistas'));
            return;
        }

        $data = [
            'artista_id'     => $artistaId,
            'artista_name'   => $artistaName,
            'client_name'    => $input->getString('client_name'),
            'client_email'   => $input->getString('client_email'),
            'client_phone'   => $input->getString('client_phone'),
            'event_type'     => $input->getString('event_type'),
            'event_date'     => $input->getString('event_date') ?: null,
            'event_location' => $input->getString('event_location'),
            'message'        => $input->getString('message'),
            'state'          => 0,
            'created'        => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        if (empty($data['client_name']) || empty($data['client_email'])) {
            $app->enqueueMessage('Por favor rellena nombre y email.', 'warning');
            $app->redirect(Route::_('index.php?option=com_artistas&view=artista&id=' . $artistaId));
            return;
        }

        $table = (object) $data;
        $db->insertObject('#__artistas_presupuestos', $table);

        // Enviar email al ARTISTA y registrar si se envió
        $emailEnviado = false;
        $emailArtista = '';
        try {
            $db->setQuery('SELECT email, name, created_by FROM #__artistas WHERE id=' . $artistaId);
            $artData = $db->loadObject();

            // Si el artista no tiene email en ficha, intentar con el usuario de Joomla
            if ($artData && empty($artData->email) && $artData->created_by) {
                $db->setQuery('SELECT email FROM #__users WHERE id=' . (int)$artData->created_by);
                $artData->email = $db->loadResult() ?? '';
            }

            if ($artData && !empty($artData->email)) {
                $emailArtista = $artData->email;
                $mailerArt = Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer();
                $mailerArt->addRecipient($artData->email);
                $mailerArt->setSubject('[HMB] Nueva solicitud de presupuesto para ' . $artistaName);
                $bodyArt  = "Hola {$artData->name},\n\n";
                $bodyArt .= "Has recibido una nueva solicitud de presupuesto a traves de heavymetalbrothers.com\n\n";
                $bodyArt .= "--- DATOS DEL CLIENTE ---\n";
                $bodyArt .= "Nombre: {$data['client_name']}\n";
                $bodyArt .= "Email: {$data['client_email']}\n";
                $bodyArt .= "Telefono: {$data['client_phone']}\n\n";
                $bodyArt .= "--- DATOS DEL EVENTO ---\n";
                $bodyArt .= "Tipo de evento: {$data['event_type']}\n";
                $bodyArt .= "Fecha: {$data['event_date']}\n";
                $bodyArt .= "Lugar: {$data['event_location']}\n\n";
                $bodyArt .= "--- MENSAJE ---\n{$data['message']}\n\n";
                $bodyArt .= "Puedes responder directamente a: {$data['client_email']}\n";
                $bodyArt .= "---\nHeavy Metal Brothers - heavymetalbrothers.com\n";
                $mailerArt->setBody($bodyArt);
                $mailerArt->setReplyTo($data['client_email'], $data['client_name']);
                $emailEnviado = $mailerArt->Send() === true;
            }
        } catch (\Exception $e) {}

        // Actualizar registro con estado del email
        $lastId = $db->insertid();
        try {
            $db->setQuery("ALTER TABLE #__artistas_presupuestos ADD COLUMN IF NOT EXISTS email_artista varchar(255) DEFAULT NULL")->execute();
            $db->setQuery("ALTER TABLE #__artistas_presupuestos ADD COLUMN IF NOT EXISTS email_enviado tinyint DEFAULT 0")->execute();
            $db->setQuery("UPDATE #__artistas_presupuestos SET email_artista=" . $db->quote($emailArtista) . ", email_enviado=" . ($emailEnviado ? 1 : 0) . " WHERE id=" . (int)$lastId)->execute();
        } catch (\Exception $e) {}

        // Enviar email de notificación al admin
        try {
            $config  = $app->getConfig();
            $mailer  = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
            $mailer->addRecipient($config->get('mailfrom'));
            $mailer->setSubject('Nueva solicitud de presupuesto: ' . $artistaName);
            $body  = "Se ha recibido una solicitud de presupuesto.\n\n";
            $body .= "Artista: {$artistaName}\n";
            $body .= "Cliente: {$data['client_name']} ({$data['client_email']})\n";
            $body .= "Teléfono: {$data['client_phone']}\n";
            $body .= "Tipo evento: {$data['event_type']}\n";
            $body .= "Fecha: {$data['event_date']}\n";
            $body .= "Localización: {$data['event_location']}\n\n";
            $body .= "Mensaje:\n{$data['message']}\n";
            $mailer->setBody($body);
            $mailer->Send();
        } catch (\Exception $e) {
            // Si falla el email, el registro ya está guardado en BD
        }

        $app->enqueueMessage('¡Solicitud enviada correctamente! En breve nos ponemos en contacto contigo.', 'success');
        $app->redirect(Route::_('index.php?option=com_artistas&view=artista&id=' . $artistaId));
    }
}
