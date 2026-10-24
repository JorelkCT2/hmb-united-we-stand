<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;

class HmbshopComponent extends MVCComponent implements RouterServiceInterface
{
    use RouterServiceTrait;
}
