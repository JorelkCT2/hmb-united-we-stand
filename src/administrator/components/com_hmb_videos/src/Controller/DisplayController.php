<?php
namespace Joomla\Component\HmbVideos\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * CRITICAL: In Joomla 6, BaseController::getModel() derives the prefix
 * from ucfirst($this->option) → 'Com_hmb_videos'.
 * MVCFactory::createModel() then looks for class:
 *   Joomla\Component\HmbVideos\Com_hmb_videos\Model\FolderModel  ← WRONG
 * instead of:
 *   Joomla\Component\HmbVideos\Administrator\Model\FolderModel   ← CORRECT
 *
 * Overriding getModel() with prefix='Administrator' fixes the 404.
 */
class DisplayController extends BaseController
{
    protected $default_view = 'folders';

    public function display($cachable = false, $urlparams = []): static
    {
        return parent::display($cachable, $urlparams);
    }

    public function getModel($name = '', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
