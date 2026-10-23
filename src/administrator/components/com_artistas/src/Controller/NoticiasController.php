<?php
namespace Joomla\Component\Artistas\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class NoticiasController extends AdminController
{
    public function getModel($name = 'Noticias', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function delete(): void
    {
        $this->checkToken();
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $ids = array_filter((array)$app->getInput()->get('cid', [], 'int'));
        foreach ($ids as $id) {
            $db->setQuery('SELECT article_id FROM #__artistas_noticias WHERE id=' . (int)$id);
            $artId = (int)$db->loadResult();
            if ($artId) $db->setQuery('DELETE FROM #__content WHERE id=' . $artId)->execute();
            $db->setQuery('DELETE FROM #__artistas_noticias WHERE id=' . (int)$id)->execute();
        }
        $app->enqueueMessage(count($ids) . ' noticia(s) eliminada(s).', 'success');
        $app->redirect('index.php?option=com_artistas&view=noticias');
    }

    public function publish(): void
    {
        $this->checkToken();
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $ids = array_filter((array)$app->getInput()->get('cid', [], 'int'));
        foreach ($ids as $id) {
            $db->setQuery('UPDATE #__artistas_noticias SET state=1 WHERE id=' . (int)$id)->execute();
        }
        $app->enqueueMessage(count($ids) . ' noticia(s) publicada(s).', 'success');
        $app->redirect('index.php?option=com_artistas&view=noticias');
    }

    public function togglestate(): void
    {
        $this->checkToken('get');
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $id    = $app->getInput()->getInt('id');
        $state = $app->getInput()->getInt('state');
        $db->setQuery('UPDATE #__artistas_noticias SET state=' . (int)$state . ' WHERE id=' . (int)$id)->execute();
        $app->redirect('index.php?option=com_artistas&view=noticias');
    }

    public function unpublish(): void
    {
        $this->checkToken();
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $ids = array_filter((array)$app->getInput()->get('cid', [], 'int'));
        foreach ($ids as $id) {
            $db->setQuery('UPDATE #__artistas_noticias SET state=0 WHERE id=' . (int)$id)->execute();
        }
        $app->enqueueMessage(count($ids) . ' noticia(s) despublicada(s).', 'success');
        $app->redirect('index.php?option=com_artistas&view=noticias');
    }
}
