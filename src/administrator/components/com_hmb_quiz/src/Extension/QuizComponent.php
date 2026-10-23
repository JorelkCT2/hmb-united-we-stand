<?php
namespace Hmb\Component\Quiz\Administrator\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;

class QuizComponent extends MVCComponent implements BootableExtensionInterface
{
	public function boot(ContainerInterface $container): void
	{
	}
}
