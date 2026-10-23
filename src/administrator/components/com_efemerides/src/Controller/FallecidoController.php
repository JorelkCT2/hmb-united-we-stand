<?php
namespace Joomla\Component\Efemerides\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

class FallecidoController extends FormController
{
    protected $view_list = 'fallecidos';

    public function delete(&$data = null)
    {
        $q = $this->app->getInput()->getString('q', '');
        parent::delete($data);
        if ($q !== '') {
            $this->setRedirect('index.php?option=com_efemerides&view=busqueda&q=' . urlencode($q));
        }
    }
}
