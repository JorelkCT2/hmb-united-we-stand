<?php
namespace Joomla\Component\Concerts\Site\View\Concerts;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $grouped;
    protected $genres;      // id=>name map
    protected $provinces;
    protected $filters;

    public function display($tpl = null): void
    {
        $model   = $this->getModel();
        $app     = Factory::getApplication();
        $country = $app->input->getString('country_code', '');

        $this->items     = $model->getItems();
        $this->genres    = $model->getGenres();   // id=>name for filter + display
        $this->provinces = $country ? $model->getProvinces($country) : [];
        $this->filters   = [
            'country_code' => $country,
            'province'     => $app->input->getString('province', ''),
            'genre_id'     => $app->input->getInt('genre_id', 0),
            'date_from'    => $app->input->getString('date_from', ''),
            'date_to'      => $app->input->getString('date_to', ''),
        ];
        $this->grouped = [];
        foreach ($this->items as $item) {
            $this->grouped[$item->date_start][] = $item;
        }
        $app->getDocument()->setTitle('Agenda de Conciertos');
        parent::display($tpl);
    }
}
