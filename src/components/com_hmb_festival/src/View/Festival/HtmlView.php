<?php
namespace Hmb\Component\Festival\Site\View\Festival;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    public array $cfg = [];

    public function display($tpl = null)
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        if (!$user || $user->guest) {
            $return = base64_encode(Uri::getInstance()->toString());
            $app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $return, false));

            return;
        }

        $this->cfg = [
            'endpoint' => Uri::root(true) . '/index.php?option=com_hmb_festival&format=json&task=ajax.',
            'token'    => Session::getFormToken(),
            'me'       => (int) $user->id,
            'myName'   => (string) $user->name,
        ];

        $doc  = $app->getDocument();
        $root = Uri::root(true);

        $cssFile = JPATH_ROOT . '/media/com_hmb_festival/css/festival.css';
        $jsFile  = JPATH_ROOT . '/media/com_hmb_festival/js/festival.js';

        $cssV = is_file($cssFile) ? filemtime($cssFile) : '0';
        $jsV  = is_file($jsFile) ? filemtime($jsFile) : '0';

        $doc->addStyleSheet($root . '/media/com_hmb_festival/css/festival.css?v=' . $cssV);
        $doc->addScript($root . '/media/com_hmb_festival/js/festival.js?v=' . $jsV, [], ['defer' => true]);

        $doc->setTitle('Festival Mode 🤘 Heavy Metal Brothers');

        parent::display($tpl);
    }
}
