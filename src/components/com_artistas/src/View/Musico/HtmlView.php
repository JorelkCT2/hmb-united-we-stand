<?php
namespace Joomla\Component\Artistas\Site\View\Musico;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    public $musico = null;

    public function display($tpl = null): void
    {
        Factory::getApplication()->allowCache(false);
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        if ($user->guest) {
            $app->redirect('index.php?option=com_users&view=login');
            return;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('SELECT * FROM #__artistas_musicos WHERE user_id=' . (int)$user->id . ' LIMIT 1');
        $this->musico = $db->loadObject();

        $app->getDocument()->setTitle('Mi perfil de músico | Heavy Metal Brothers');
        parent::display($tpl);
    }
}
