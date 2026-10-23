<?php
namespace Joomla\Component\Artistas\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class ResenaController extends BaseController
{
    public function submit(): void
    {
        
        $app       = Factory::getApplication();
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $input     = $app->getInput();
        $artistaId = $input->getInt('artista_id');

        $data = [
            'artista_id'     => $artistaId,
            'reviewer_name'  => $input->getString('reviewer_name'),
            'reviewer_email' => $input->getString('reviewer_email'),
            'rating'         => max(1, min(5, $input->getInt('rating', 5))),
            'review'         => $input->getString('review'),
            'event_type'     => $input->getString('event_type'),
            'state'          => 0, // pendiente de moderación
            'created'        => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        if (empty($data['reviewer_name']) || empty($data['review'])) {
            $app->enqueueMessage('Por favor rellena nombre y reseña.', 'warning');
            $app->redirect(Route::_('index.php?option=com_artistas&view=artista&id=' . $artistaId . '#resenas'));
            return;
        }

        $table = (object) $data;
        $db->insertObject('#__artistas_resenas', $table);

        $app->enqueueMessage('¡Gracias por tu reseña! Será revisada antes de publicarse.', 'success');
        $app->redirect(Route::_('index.php?option=com_artistas&view=artista&id=' . $artistaId . '#resenas'));
    }
}
