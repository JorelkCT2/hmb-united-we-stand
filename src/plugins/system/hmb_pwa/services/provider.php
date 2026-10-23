<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Hmb\Plugin\System\HmbPwa\Extension\HmbPwa;

return new class () implements ServiceProviderInterface {
	public function register(Container $container): void
	{
		$container->set(
			PluginInterface::class,
			function (Container $container) {
				$config  = (array) PluginHelper::getPlugin('system', 'hmb_pwa');
				$subject = $container->get(DispatcherInterface::class);

				$plugin = new HmbPwa($subject, $config);
				$plugin->setApplication(Factory::getApplication());

				return $plugin;
			}
		);
	}
};
