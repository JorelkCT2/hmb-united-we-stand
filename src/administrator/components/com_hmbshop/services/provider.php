<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbshop\Administrator\Extension\HmbshopComponent;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('\HeavyMetalBrothers\Component\Hmbshop'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\HeavyMetalBrothers\Component\Hmbshop'));
        $container->registerServiceProvider(new RouterFactory('\HeavyMetalBrothers\Component\Hmbshop'));

        $container->set(
            ComponentInterface::class,
            static function (Container $container): ComponentInterface {
                $component = new HmbshopComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};
