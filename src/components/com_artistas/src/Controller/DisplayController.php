<?php
namespace Joomla\Component\Artistas\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    protected $default_view = 'artistas';

    public function display($cachable = false, $urlparams = []): static
    {
        $view = $this->input->get('view', 'artistas');
        $id   = $this->input->getInt('id');

        if ($view === 'artista' && $id) {
            $cachable = true;
        }
        if ($view === 'registro') {
            $cachable = false;
        }

        return parent::display($cachable, $urlparams);
    }
}
