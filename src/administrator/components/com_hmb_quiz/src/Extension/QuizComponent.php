<?php
namespace Hmb\Component\Quiz\Administrator\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;

class QuizComponent extends MVCComponent implements BootableExtensionInterface, RouterServiceInterface
{
	use RouterServiceTrait;

	public function boot(ContainerInterface $container): void
	{
	}
}
