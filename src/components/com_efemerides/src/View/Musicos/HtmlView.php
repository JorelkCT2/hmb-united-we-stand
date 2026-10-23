<?php
namespace Joomla\Component\Efemerides\Site\View\Musicos;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public array  $items        = [];
    public array  $bandas       = [];
    public string $search       = '';
    public int    $total        = 0;
    public int    $limit        = 40;
    public int    $start        = 0;
    public int    $bandaFiltro  = 0;
    public string $componentBase = '';

    public function display($tpl = null): void
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $input = Factory::getApplication()->getInput();

        $this->search      = trim((string)$input->getString('search', ''));
        $this->limit       = max(1, (int)$input->getInt('limit', 40));
        $this->start       = max(0, (int)$input->getInt('limitstart', 0));
        $this->bandaFiltro = (int)$input->getInt('banda', 0);

        $where = ['m.state = 1'];
        if ($this->search !== '') {
            $s = $db->quote('%' . $db->escape($this->search, true) . '%');
            $where[] = "(m.nombre LIKE $s OR m.banda_texto LIKE $s)";
        }
        if ($this->bandaFiltro > 0) {
            $where[] = 'm.banda_id = ' . $this->bandaFiltro;
        }
        $wSql = implode(' AND ', $where);

        $db->setQuery("SELECT COUNT(*) FROM #__efemerides_musicos m WHERE $wSql");
        $this->total = (int)$db->loadResult();

        $db->setQuery(
            "SELECT m.*, b.nombre AS banda_nombre
             FROM #__efemerides_musicos m
             LEFT JOIN #__efemerides_bandas b ON b.id = m.banda_id
             WHERE $wSql ORDER BY m.nombre ASC",
            $this->start, $this->limit
        );
        $this->items = $db->loadObjectList() ?: [];

        $db->setQuery('SELECT id, nombre FROM #__efemerides_bandas WHERE state=1 ORDER BY nombre ASC');
        $this->bandas = $db->loadObjectList() ?: [];

        // URL base del componente para links a fichas
        try {
            $app   = Factory::getApplication();
            $menu  = $app->getMenu();
            $comId = \Joomla\CMS\Component\ComponentHelper::getComponent('com_efemerides')->id;
            $items = $menu->getItems('component_id', $comId);
            if (!empty($items)) {
                $this->componentBase = rtrim(Uri::root(), '/') . '/' . ltrim((string)$items[0]->route, '/');
            }
        } catch (\Throwable $e) {}

        if (empty($this->componentBase)) {
            $this->componentBase = Uri::current();
        }

        parent::display($tpl);
    }
}
