<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;

/**
 * Extension principal del componente (backend).
 *
 * IMPORTANTE Joomla 6: boot() recibe ContainerInterface (PSR-11),
 * NO CMSApplicationInterface como en Joomla 4/5.
 */
final class MetalmapComponent extends MVCComponent implements BootableExtensionInterface
{
    public function boot(ContainerInterface $container): void
    {
        // Aquí se podrían registrar servicios adicionales si hiciera falta.
    }
}
