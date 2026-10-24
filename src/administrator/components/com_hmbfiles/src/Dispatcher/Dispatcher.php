<?php
namespace HeavyMetalBrothers\Component\Hmbfiles\Administrator\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

class Dispatcher extends ComponentDispatcher
{
    protected $defaultController = 'display';

    /** Solo super administradores: tocar archivos del sitio no es algo que se delegue. */
    protected function checkAccess(): void
    {
        parent::checkAccess();

        $user = $this->app->getIdentity();

        if (!$user || !$user->id || !$user->authorise('core.admin')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
