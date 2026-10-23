<?php
namespace Joomla\Component\Efemerides\Administrator\View\Cpanel;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public int $totalEfemerides  = 0;
    public int $totalCategorias  = 0;
    public int $totalFallecidos  = 0;
    public int $efemerideHoy     = 0;

    public function display($tpl = null): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $db->setQuery('SELECT COUNT(*) FROM #__efemerides WHERE state = 1');
        $this->totalEfemerides = (int)$db->loadResult();

        $db->setQuery('SELECT COUNT(*) FROM #__efemerides_categorias WHERE state = 1');
        $this->totalCategorias = (int)$db->loadResult();

        $db->setQuery('SELECT COUNT(*) FROM #__efemerides_fallecidos WHERE state = 1');
        $this->totalFallecidos = (int)$db->loadResult();

        $hoy = date('m-d');
        [$mes, $dia] = explode('-', $hoy);
        $db->setQuery(
            'SELECT COUNT(*) FROM #__efemerides WHERE state = 1 '
            . 'AND MONTH(fecha_evento) = ' . (int)$mes
            . ' AND DAY(fecha_evento) = ' . (int)$dia
        );
        $this->efemerideHoy = (int)$db->loadResult();

        parent::display($tpl);
    }
}
