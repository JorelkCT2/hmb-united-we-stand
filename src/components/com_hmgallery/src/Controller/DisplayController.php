<?php
namespace Joomla\Component\Hmgallery\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    protected $default_view = 'gallery';

    public function display($cachable = true, $urlparams = []): static
    {
        $urlparams = array_merge($urlparams, [
            'option'   => 'CMD', 'view' => 'CMD', 'layout' => 'CMD',
            'id'       => 'INT', 'album_id' => 'INT', 'Itemid' => 'INT',
        ]);
        return parent::display($cachable, $urlparams);
    }
}
