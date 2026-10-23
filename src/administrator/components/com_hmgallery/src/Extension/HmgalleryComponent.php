<?php
namespace Joomla\Component\Hmgallery\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;

class HmgalleryComponent extends MVCComponent implements BootableExtensionInterface
{
    use HTMLRegistryAwareTrait;
    public function boot(ContainerInterface $container): void {}
}
