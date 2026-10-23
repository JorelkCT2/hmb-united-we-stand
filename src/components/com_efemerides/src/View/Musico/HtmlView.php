<?php
namespace Joomla\Component\Efemerides\Site\View\Musico;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public ?object $musico  = null;
    public ?object $banda   = null;
    public ?object $banda2  = null;
    public array   $efems   = [];
    public array   $frases  = [];
    public array   $compas  = [];

    public function display($tpl = null): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = (int)Factory::getApplication()->getInput()->getInt('id', 0);

        $db->setQuery(
            'SELECT m.*, b.nombre AS banda_nombre
             FROM #__efemerides_musicos m
             LEFT JOIN #__efemerides_bandas b ON b.id = m.banda_id
             WHERE m.id = ' . $id . ' AND m.state = 1'
        );
        $this->musico = $db->loadObject();

        if (!$this->musico) {
            parent::display($tpl);
            return;
        }

        // Completar fechas desde fallecidos y efemérides
        $this->completarFechas($db);

        // Banda principal
        if (!empty($this->musico->banda_id)) {
            $db->setQuery('SELECT * FROM #__efemerides_bandas WHERE id = ' . (int)$this->musico->banda_id);
            $this->banda = $db->loadObject();
        }

        // Segunda banda
        if (!empty($this->musico->banda_id_2)) {
            $db->setQuery('SELECT * FROM #__efemerides_bandas WHERE id = ' . (int)$this->musico->banda_id_2);
            $this->banda2 = $db->loadObject();
        }

        // Efemérides del músico
        $db->setQuery(
            'SELECT e.*, c.name AS categoria_nombre
             FROM #__efemerides e
             LEFT JOIN #__efemerides_categorias c ON c.id = e.categoria_id
             WHERE e.artista = ' . $db->quote($this->musico->nombre) . '
               AND e.state = 1
             ORDER BY e.fecha_evento DESC'
        );
        $this->efems = $db->loadObjectList() ?: [];

        // Frases
        $db->setQuery(
            'SELECT * FROM #__efemerides_frases
             WHERE autor = ' . $db->quote($this->musico->nombre) . ' AND state = 1
             ORDER BY id DESC'
        );
        $this->frases = $db->loadObjectList() ?: [];

        // Compañeros de banda
        if (!empty($this->musico->banda_id)) {
            $db->setQuery(
                'SELECT * FROM #__efemerides_musicos
                 WHERE banda_id = ' . (int)$this->musico->banda_id . '
                   AND id != ' . $id . ' AND state = 1
                 ORDER BY nombre ASC LIMIT 12'
            );
            $this->compas = $db->loadObjectList() ?: [];
        }

        parent::display($tpl);
    }

    private function completarFechas($db): void
    {
        $m   = $this->musico;
        $v0  = '0000-00-00';
        $sinNac = empty($m->fecha_nacimiento) || $m->fecha_nacimiento === $v0;
        $sinDef = empty($m->fecha_defuncion)  || $m->fecha_defuncion  === $v0;

        if (!$sinNac && !$sinDef) {
            return;
        }

        // 1. Tabla fallecidos (solo fecha_fallecimiento)
        if ($sinDef) {
            $db->setQuery(
                'SELECT fecha_fallecimiento FROM #__efemerides_fallecidos
                 WHERE artista = ' . $db->quote($m->nombre) . '
                   AND state = 1 LIMIT 1'
            );
            $row = $db->loadObject();
            if ($row && !empty($row->fecha_fallecimiento) && $row->fecha_fallecimiento !== $v0) {
                $this->musico->fecha_defuncion = $row->fecha_fallecimiento;
                $sinDef = false;
            }
        }

        // 2. Tabla efemérides por categoria_id
        if ($sinNac || $sinDef) {
            $db->setQuery('SELECT id, name FROM #__efemerides_categorias');
            $rows = $db->loadObjectList() ?: [];
            $catNac = [];
            $catDef = [];
            foreach ($rows as $row) {
                $n = mb_strtolower(trim((string)$row->name));
                if (strpos($n, 'nacim') !== false || strpos($n, 'birth') !== false) {
                    $catNac[] = (int)$row->id;
                }
                if (strpos($n, 'defun') !== false || strpos($n, 'fallec') !== false) {
                    $catDef[] = (int)$row->id;
                }
            }
            $allCats = array_merge($catNac, $catDef);
            if (!empty($allCats)) {
                $db->setQuery(
                    'SELECT fecha_evento, categoria_id FROM #__efemerides
                     WHERE artista = ' . $db->quote($m->nombre) . '
                       AND state = 1
                       AND categoria_id IN (' . implode(',', $allCats) . ')'
                );
                $eRows = $db->loadObjectList() ?: [];
                foreach ($eRows as $eRow) {
                    $cid   = (int)$eRow->categoria_id;
                    $fecha = (string)($eRow->fecha_evento ?? '');
                    if (!$fecha || $fecha === $v0) {
                        continue;
                    }
                    if ($sinNac && in_array($cid, $catNac)) {
                        $this->musico->fecha_nacimiento = $fecha;
                        $sinNac = false;
                    }
                    if ($sinDef && in_array($cid, $catDef)) {
                        $this->musico->fecha_defuncion = $fecha;
                        $sinDef = false;
                    }
                }
            }
        }
    }
}
