<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;

class HmbTemasComponent extends MVCComponent implements BootableExtensionInterface
{
    public function boot(ContainerInterface $container): void {}
}
