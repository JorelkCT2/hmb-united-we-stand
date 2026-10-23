<?php
namespace Joomla\Component\Artistas\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class ArtistasController extends AdminController
{
    public function getModel($name = 'Artista', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    // Aprobar directamente desde el cpanel sin pasar por la vista edit
    public function eliminar(): void
    {
        $app = Factory::getApplication();
        $id  = $app->getInput()->getInt('id');
        if ($id > 0) {
            Factory::getContainer()->get('DatabaseDriver')
                ->setQuery('DELETE FROM #__artistas WHERE id=' . (int)$id)
                ->execute();
            $app->enqueueMessage('Registro eliminado.', 'success');
        }
        $app->redirect('index.php?option=com_artistas&view=cpanel');
    }

    public function aprobar(): void
    {
        $app = Factory::getApplication();
        $id  = $app->getInput()->getInt('id');

        if ($id > 0) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery('UPDATE #__artistas SET state=1 WHERE id=' . $id)->execute();
            $app->enqueueMessage('Artista publicado correctamente.', 'success');
        }

        $app->redirect('index.php?option=com_artistas&view=cpanel');
    }
}
