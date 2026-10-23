<?php
defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\Component;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        // MVCFactory DEBE registrarse aunque no se use directamente
        // ComponentDispatcherFactory lo necesita internamente
        $container->registerServiceProvider(
            new MVCFactory('\\HeavyMetalBrothers\\Component\\Hmb')
        );
        $container->registerServiceProvider(
            new ComponentDispatcherFactory('\\HeavyMetalBrothers\\Component\\Hmb')
        );

        $container->set(
            ComponentInterface::class,
            static function (Container $container): ComponentInterface {
                return new Component(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );
            }
        );
    }
};
