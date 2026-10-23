<?php
namespace Joomla\Component\Efemerides\Site\View\Banda;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public ?object $banda    = null;
    public array   $miembros = [];
    public array   $efems    = [];
    public string  $componentBase = '';

    public function display($tpl = null): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = (int)Factory::getApplication()->getInput()->getInt('id', 0);

        $db->setQuery('SELECT * FROM #__efemerides_bandas WHERE id = ' . $id . ' AND state = 1');
        $this->banda = $db->loadObject();

        if (!$this->banda) {
            parent::display($tpl);
            return;
        }

        // Miembros principales
        $db->setQuery(
            'SELECT *, 1 AS es_principal FROM #__efemerides_musicos
             WHERE banda_id = ' . $id . ' AND state = 1 ORDER BY nombre ASC'
        );
        $principales = $db->loadObjectList() ?: [];

        if (empty($principales)) {
            $db->setQuery(
                'SELECT *, 1 AS es_principal FROM #__efemerides_musicos
                 WHERE banda_texto = ' . $db->quote($this->banda->nombre) . ' AND state = 1 ORDER BY nombre ASC'
            );
            $principales = $db->loadObjectList() ?: [];
        }

        // Miembros secundarios (banda_id_2)
        $db->setQuery(
            'SELECT *, 0 AS es_principal FROM #__efemerides_musicos
             WHERE banda_id_2 = ' . $id . ' AND state = 1 ORDER BY nombre ASC'
        );
        $secundarios = $db->loadObjectList() ?: [];

        // Unir evitando duplicados
        $ids = array();
        foreach ($principales as $p) {
            $ids[] = $p->id;
        }
        foreach ($secundarios as $s) {
            if (!in_array($s->id, $ids)) {
                $principales[] = $s;
            }
        }
        $this->miembros = $principales;

        // Efemérides de la banda
        $db->setQuery(
            'SELECT e.*, c.name AS categoria_nombre
             FROM #__efemerides e
             LEFT JOIN #__efemerides_categorias c ON c.id = e.categoria_id
             WHERE e.artista = ' . $db->quote($this->banda->nombre) . ' AND e.state = 1
             ORDER BY e.fecha_evento DESC LIMIT 20'
        );
        $this->efems = $db->loadObjectList() ?: [];

        // URL base del componente
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
