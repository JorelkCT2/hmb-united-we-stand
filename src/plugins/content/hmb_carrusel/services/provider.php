<?php
defined('_JEXEC') or die;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Content\HmbCarrusel\Extension\HmbCarrusel;
return new class() implements ServiceProviderInterface {
    public function register(Container $container) {
        $container->set(PluginInterface::class, function (Container $container) {
            $plugin = new HmbCarrusel(
                $container->get(DispatcherInterface::class),
                (array) PluginHelper::getPlugin('content', 'hmb_carrusel')
            );
            $plugin->setApplication(Factory::getApplication());
            return $plugin;
        });
    }
};
