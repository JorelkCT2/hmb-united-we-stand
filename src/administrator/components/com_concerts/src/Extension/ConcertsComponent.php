<?php
namespace Joomla\Component\Concerts\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;

class ConcertsComponent extends MVCComponent implements BootableExtensionInterface
{
    public function boot(ContainerInterface $container): void {}
}
