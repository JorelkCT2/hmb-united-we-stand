<?php
namespace Joomla\Component\Artistas\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;

class PresupuestosController extends AdminController
{
    public function delete(): void
    {
        $this->checkToken();
        $app = \Joomla\CMS\Factory::getApplication();
        $db  = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $ids = (array)$app->getInput()->get('cid', [], 'int');
        $ids = array_filter($ids);

        if (empty($ids)) {
            $app->enqueueMessage('Selecciona al menos un presupuesto.', 'warning');
        } else {
            foreach ($ids as $id) {
                $db->setQuery('DELETE FROM #__artistas_presupuestos WHERE id = ' . (int)$id)->execute();
            }
            $app->enqueueMessage(count($ids) . ' solicitud(es) de presupuesto eliminada(s).', 'success');
        }
        $app->redirect(\Joomla\CMS\Router\Route::_('index.php?option=com_artistas&view=presupuestos', false));
    }

    public function cambiarEstado(): void
    {
        $this->checkToken();
        $app   = \Joomla\CMS\Factory::getApplication();
        $db    = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $id    = $app->getInput()->getInt('id');
        $state = $app->getInput()->getInt('state');
        $db->setQuery('UPDATE #__artistas_presupuestos SET state=' . (int)$state . ' WHERE id=' . (int)$id)->execute();
        $app->enqueueMessage('Estado actualizado.', 'success');
        $app->redirect(\Joomla\CMS\Router\Route::_('index.php?option=com_artistas&view=presupuestos', false));
    }
}
