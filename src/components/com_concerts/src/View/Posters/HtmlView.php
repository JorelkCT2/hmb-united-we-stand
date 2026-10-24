<?php
namespace Joomla\Component\Concerts\Site\View\Posters;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $items;

    public function display($tpl = null): void
    {
        // Un mismo cartel sirve para toda una gira: se muestra una sola vez, con el numero de fechas
        $unicos = [];

        foreach ((array) $this->getModel()->getItems() as $it) {
            $k = (string) $it->poster_url;

            if (!isset($unicos[$k])) {
                $it->n_fechas = 1;
                $unicos[$k]   = $it;
            } else {
                $unicos[$k]->n_fechas++;
            }
        }

        $this->items = array_values($unicos);
        Factory::getApplication()->getDocument()->setTitle('Carteles de Conciertos');
        parent::display($tpl);
    }
}
