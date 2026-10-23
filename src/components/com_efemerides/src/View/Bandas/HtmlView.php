<?php
namespace Joomla\Component\Efemerides\Site\View\Bandas;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public array  $items        = [];
    public string $search       = '';
    public string $generoFiltro = '';
    public int    $total        = 0;
    public int    $limit        = 40;
    public int    $start        = 0;
    public array  $generos      = [];

    public function display($tpl = null): void
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $input = Factory::getApplication()->getInput();

        $this->search       = trim((string)$input->getString('search', ''));
        $this->limit        = max(1, (int)$input->getInt('limit', 40));
        $this->start        = max(0, (int)$input->getInt('limitstart', 0));
        $this->generoFiltro = trim((string)$input->getString('genero', ''));

        $where = ['state = 1'];
        if ($this->search !== '') {
            $s = $db->quote('%' . $db->escape($this->search, true) . '%');
            $where[] = "(nombre LIKE $s OR pais LIKE $s OR genero LIKE $s)";
        }
        if ($this->generoFiltro !== '') {
            $where[] = 'genero LIKE ' . $db->quote('%' . $db->escape($this->generoFiltro, true) . '%');
        }
        $wSql = implode(' AND ', $where);

        $db->setQuery("SELECT COUNT(*) FROM #__efemerides_bandas WHERE $wSql");
        $this->total = (int)$db->loadResult();

        $db->setQuery("SELECT * FROM #__efemerides_bandas WHERE $wSql ORDER BY nombre ASC", $this->start, $this->limit);
        $this->items = $db->loadObjectList() ?: [];

        $db->setQuery("SELECT DISTINCT genero FROM #__efemerides_bandas WHERE state=1 AND genero IS NOT NULL AND genero != '' ORDER BY genero ASC");
        $this->generos = $db->loadColumn() ?: [];

        parent::display($tpl);
    }
}
