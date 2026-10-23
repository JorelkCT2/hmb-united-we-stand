<?php
namespace Joomla\Component\Concerts\Site\View\Concert;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $item;

    public function display($tpl = null): void
    {
        $this->item = $this->getModel()->getItem();
        if (!$this->item) throw new \Exception('Concierto no encontrado', 404);
        Factory::getApplication()->getDocument()->setTitle($this->item->title ?: $this->item->band);
        parent::display($tpl);
    }
}
