<?php
/**
 * @package    com_hmb_videos
 * @copyright  Heavy Metal Brothers 2024
 * @license    GNU/GPL v2+
 *
 * KEY: We use Joomla's built-in MVCComponent directly instead of a
 * custom HmbVideosComponent class. The custom class required autoloading
 * via `use` which could fail if the namespace was not yet registered when
 * ComponentHelper requires this file on the second HTTP request (after
 * redirect). MVCComponent is a Joomla core class — always available.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\HmbVideos'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\HmbVideos'));

        $container->set(
            ComponentInterface::class,
            static function (Container $container): ComponentInterface {
                $component = new MVCComponent(
                    $container->get(ComponentDispatcherFactoryInterface::class)
                );
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                return $component;
            }
        );
    }
};
