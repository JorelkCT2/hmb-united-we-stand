<?php
namespace Joomla\Component\Artistas\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;

class ResenasController extends AdminController
{
    public function delete(): void
    {
        $this->checkToken();
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $ids = array_filter((array)$app->getInput()->get('cid', [], 'int'));

        if (empty($ids)) {
            $app->enqueueMessage('Selecciona al menos una reseña.', 'warning');
        } else {
            // Necesitamos recalcular ratings después de borrar
            $db->setQuery('SELECT DISTINCT artista_id FROM #__artistas_resenas WHERE id IN (' . implode(',', $ids) . ')');
            $artistaIds = $db->loadColumn();

            foreach ($ids as $id) {
                $db->setQuery('DELETE FROM #__artistas_resenas WHERE id=' . (int)$id)->execute();
            }

            // Recalcular rating de artistas afectados
            foreach ($artistaIds as $aid) {
                $db->setQuery('SELECT AVG(rating), COUNT(id) FROM #__artistas_resenas WHERE artista_id=' . (int)$aid . ' AND state=1');
                [$avg, $cnt] = $db->loadRow();
                $db->setQuery('UPDATE #__artistas SET rating=' . round((float)$avg, 2) . ', review_count=' . (int)$cnt . ' WHERE id=' . (int)$aid)->execute();
            }
            $app->enqueueMessage(count($ids) . ' reseña(s) eliminada(s).', 'success');
        }
        $app->redirect(\Joomla\CMS\Router\Route::_('index.php?option=com_artistas&view=resenas'));
    }

    public function aprobar(): void
    {
        $this->checkToken();
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $ids = array_filter((array)$app->getInput()->get('cid', [], 'int'));

        foreach ($ids as $id) {
            $db->setQuery('UPDATE #__artistas_resenas SET state=1 WHERE id=' . (int)$id)->execute();
        }

        // Recalcular ratings
        $db->setQuery('SELECT DISTINCT artista_id FROM #__artistas_resenas WHERE id IN (' . implode(',', $ids) . ')');
        foreach ($db->loadColumn() as $aid) {
            $db->setQuery('SELECT AVG(rating), COUNT(id) FROM #__artistas_resenas WHERE artista_id=' . (int)$aid . ' AND state=1');
            [$avg, $cnt] = $db->loadRow();
            $db->setQuery('UPDATE #__artistas SET rating=' . round((float)$avg, 2) . ', review_count=' . (int)$cnt . ' WHERE id=' . (int)$aid)->execute();
        }
        $app->enqueueMessage(count($ids) . ' reseña(s) aprobada(s).', 'success');
        $app->redirect(\Joomla\CMS\Router\Route::_('index.php?option=com_artistas&view=resenas'));
    }
}
