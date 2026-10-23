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
        $this->items = $this->getModel()->getItems();
        Factory::getApplication()->getDocument()->setTitle('Carteles de Conciertos');
        parent::display($tpl);
    }
}
