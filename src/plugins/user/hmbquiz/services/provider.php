<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\User\Hmbquiz\Extension\Hmbquiz;

return new class implements ServiceProviderInterface {
	public function register(Container $container): void
	{
		$container->set(
			PluginInterface::class,
			function (Container $container) {
				$config  = (array) PluginHelper::getPlugin('user', 'hmbquiz');
				$subject = $container->get(DispatcherInterface::class);
				$plugin  = new Hmbquiz($subject, $config);
				$plugin->setApplication(Factory::getApplication());

				return $plugin;
			}
		);
	}
};
