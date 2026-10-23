<?php
namespace Joomla\Component\Artistas\Site\View\Miperfil;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

class HtmlView extends BaseHtmlView
{
    public $item    = null;
    public $user    = null;
    public $generos = [];

    public function display($tpl = null): void
    {
        // Disable ALL caching - Gantry5 caches pages causing stale CSRF tokens
        $app = Factory::getApplication();
        $app->allowCache(false);
        $app->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate', true);
        $app->setHeader('Pragma', 'no-cache', true);
        $app->setHeader('Expires', '0', true);

        // $app already declared above
        $user = $app->getIdentity();

        if ($user->guest) {
            $app->enqueueMessage('Debes iniciar sesión para gestionar tu ficha.', 'warning');
            $currentUrl  = \Joomla\CMS\Uri\Uri::getInstance()->toString();
            $loginItemid = '';
            $loginItem   = $app->getMenu()->getItems('link', 'index.php?option=com_users&view=login', true);
            if ($loginItem) { $loginItemid = '&Itemid=' . $loginItem->id; }
            $app->redirect('index.php?option=com_users&view=login&return=' . base64_encode($currentUrl) . $loginItemid);
            return;
        }

        $this->user = $user;
        $db = Factory::getContainer()->get('DatabaseDriver');

        $db->setQuery('SELECT * FROM #__artistas WHERE created_by = ' . (int)$user->id . ' LIMIT 1');
        $item = $db->loadObject();

        if ($item) {
            foreach (['formats','events','componentes','provincias_destino','gallery','videos'] as $f) {
                $item->$f = !empty($item->$f) ? (is_array($item->$f) ? $item->$f : (json_decode($item->$f, true) ?: [])) : [];
            }
            if (empty($item->videos) && !empty($item->video_url)) {
                $item->videos = [$item->video_url];
            }
        }

        $this->item = $item;

        try {
            $db->setQuery('SELECT name FROM #__artistas_generos WHERE state=1 ORDER BY ordering ASC, name ASC');
            $this->generos = $db->loadColumn() ?: [];
        } catch (\Exception $e) {
            $this->generos = [];
        }

        $app->getDocument()->setTitle('Mi ficha de artista');
        parent::display($tpl);
    }
}
