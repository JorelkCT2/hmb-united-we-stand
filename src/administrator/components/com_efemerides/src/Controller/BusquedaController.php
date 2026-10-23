<?php
namespace Joomla\Component\Efemerides\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

class BusquedaController extends BaseController
{
    public function delete(): void
    {
        Session::checkToken() or jexit('Token inválido');

        $app   = Factory::getApplication();
        $input = $app->getInput();
        $db    = Factory::getContainer()->get('DatabaseDriver');

        $id    = (int)$input->post->getInt('del_id', 0);
        $tabla = $input->post->getString('del_tabla', '');
        $q     = $input->post->getString('q', '');

        $tablas_validas = [
            'musicos'    => '#__efemerides_musicos',
            'efemerides' => '#__efemerides',
            'fallecidos' => '#__efemerides_fallecidos',
            'frases'     => '#__efemerides_frases',
        ];

        if ($id > 0 && isset($tablas_validas[$tabla])) {
            $db->setQuery('DELETE FROM ' . $tablas_validas[$tabla] . ' WHERE id = ' . $id);
            $db->execute();
            $app->enqueueMessage('Registro eliminado correctamente.', 'success');
        } else {
            $app->enqueueMessage('No se pudo eliminar el registro.', 'error');
        }

        $app->redirect('index.php?option=com_efemerides&view=busqueda&q=' . urlencode($q));
    }
}
