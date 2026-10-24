<?php
namespace Joomla\Component\Concerts\Site\View\Concerts;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;

class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $grouped;     // 'YYYY-MM' => ['YYYY-MM-DD' => [concierto, ...]]
    protected $genres;      // id=>name map
    protected $provinces;   // provincia => nº de conciertos
    protected $months;      // 'YYYY-MM' => nº de conciertos
    protected $stats;
    protected $past = false;
    protected $mapUrl = '';

    public function display($tpl = null): void
    {
        $model = $this->getModel();
        $app   = Factory::getApplication();

        $this->past   = $app->input->getInt('past', 0) === 1;
        $this->items  = $model->getItems();
        $this->genres = $model->getGenres();

        $this->grouped   = [];
        $this->provinces = [];
        $this->months    = [];
        $ciudades        = [];

        foreach ($this->items as $item) {
            $mes = substr($item->date_start, 0, 7);
            $this->grouped[$mes][$item->date_start][] = $item;
            $this->months[$mes] = ($this->months[$mes] ?? 0) + 1;

            if (!empty($item->province)) {
                $this->provinces[$item->province] = ($this->provinces[$item->province] ?? 0) + 1;
            }

            $ciudades[mb_strtolower($item->city)] = true;
        }

        ksort($this->provinces, SORT_LOCALE_STRING);

        $primero = $this->items ? reset($this->items) : null;
        $this->stats = [
            'total'    => count($this->items),
            'ciudades' => count($ciudades),
            'proximo'  => $primero ? $primero->date_start : '',
            'hayPasados' => $this->past ? true : $model->hayPasados(),
        ];

        try {
            $this->mapUrl = Route::_('index.php?option=com_metalmap&view=map');
        } catch (\Throwable $e) {
        }

        $app->getDocument()->setTitle($this->past ? 'Conciertos celebrados' : 'Agenda de Conciertos');
        parent::display($tpl);
    }
}
