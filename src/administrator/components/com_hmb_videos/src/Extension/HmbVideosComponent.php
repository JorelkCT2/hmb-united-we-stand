<?php
namespace Joomla\Component\HmbVideos\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Psr\Container\ContainerInterface;

class HmbVideosComponent extends MVCComponent implements BootableExtensionInterface
{
    public function boot(ContainerInterface $container): void {}
}
