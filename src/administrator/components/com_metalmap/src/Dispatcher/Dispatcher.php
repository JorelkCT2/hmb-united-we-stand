<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

final class Dispatcher extends ComponentDispatcher
{
    protected function checkAccess()
    {
        if (!$this->app->getIdentity()->authorise('core.manage', 'com_metalmap')) {
            throw new \Joomla\CMS\Access\Exception\NotAllowed(\Joomla\CMS\Language\Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * El mapa se alimenta ahora de la agenda unificada (#__concerts): la edicion de festivales y
     * conciertos se hace alli. Cualquier pantalla de este componente redirige a la nueva.
     */
    public function dispatch()
    {
        $this->checkAccess();
        $this->app->enqueueMessage('El mapa de conciertos se alimenta ahora de la Agenda: aquí editas, importas y actualizas conciertos y festivales en un solo sitio.', 'info');
        $this->app->redirect(\Joomla\CMS\Router\Route::_('index.php?option=com_concerts&view=concerts', false));
    }
}
