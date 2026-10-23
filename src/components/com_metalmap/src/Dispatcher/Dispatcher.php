<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Factory;

final class Dispatcher extends ComponentDispatcher
{
    public function dispatch(): void
    {
        $input = Factory::getApplication()->getInput();
        // Vista por defecto: el mapa
        if (!$input->get('view')) {
            $input->set('view', 'map');
        }
        parent::dispatch();
    }
}
