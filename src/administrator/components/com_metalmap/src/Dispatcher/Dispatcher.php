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
}
