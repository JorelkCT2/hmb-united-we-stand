<?php
namespace Joomla\Component\Efemerides\Site\View\Fallecidos;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public array $fallecidosPorAnio = [];
    public array $aniosDisponibles  = [];

    public function display($tpl = null): void
    {
        $app  = Factory::getApplication();
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $year = (int)$app->getInput()->getInt('year', 0);
        $v0   = '0000-00-00';
        $qManual = $db->quote('manual');
        $qMusico = $db->quote('musico');
        $qEmpty  = $db->quote('');
        $qV0     = $db->quote($v0);

        // 1. Fallecidos manuales
        $sql = 'SELECT f.*, ' . $qManual . ' AS origen
                FROM #__efemerides_fallecidos f
                WHERE f.state = 1';
        if ($year) {
            $sql .= ' AND YEAR(f.fecha_fallecimiento) = ' . $year;
        }
        $sql .= ' GROUP BY f.artista, f.fecha_fallecimiento ORDER BY f.artista ASC';
        $db->setQuery($sql);
        $manuales = $db->loadObjectList() ?: [];

        $nombresYa = [];
        foreach ($manuales as $f) {
            $nombresYa[mb_strtolower(trim((string)$f->artista))] = true;
        }

        // 2. Músicos con fecha de defunción no solapados
        $sqlM = 'SELECT m.id AS musico_id, m.nombre AS artista,
                     m.fecha_defuncion AS fecha_fallecimiento,
                     m.foto, m.descripcion, NULL AS efemeride_titulo,
                     ' . $qMusico . ' AS origen,
                     COALESCE(b.nombre, m.banda_texto, ' . $qEmpty . ') AS banda_nombre
                 FROM #__efemerides_musicos m
                 LEFT JOIN #__efemerides_bandas b ON b.id = m.banda_id
                 WHERE m.state = 1
                   AND m.fecha_defuncion IS NOT NULL
                   AND m.fecha_defuncion != ' . $qV0;
        if ($year) {
            $sqlM .= ' AND YEAR(m.fecha_defuncion) = ' . $year;
        }
        $sqlM .= ' ORDER BY m.nombre ASC';
        $db->setQuery($sqlM);
        $deMusicos = $db->loadObjectList() ?: [];

        $autoFallecidos = [];
        foreach ($deMusicos as $m) {
            $key = mb_strtolower(trim((string)$m->artista));
            if (!isset($nombresYa[$key])) {
                $m->musico_id    = (int)$m->musico_id;
                $m->banda_nombre = (string)($m->banda_nombre ?? '');
                $autoFallecidos[] = $m;
            }
        }

        // 3. Enriquecer manuales con datos del músico si existe
        $db->setQuery(
            'SELECT m.id, m.nombre, m.foto,
                    COALESCE(b.nombre, m.banda_texto, ' . $qEmpty . ') AS banda_nombre
             FROM #__efemerides_musicos m
             LEFT JOIN #__efemerides_bandas b ON b.id = m.banda_id
             WHERE m.state = 1'
        );
        $musicoMap = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $musicoMap[mb_strtolower(trim($row->nombre))] = $row;
        }
        foreach ($manuales as $f) {
            $key = mb_strtolower(trim((string)$f->artista));
            if (isset($musicoMap[$key])) {
                $mus = $musicoMap[$key];
                $f->musico_id    = (int)$mus->id;
                $f->banda_nombre = (string)($mus->banda_nombre ?? '');
                if (empty($f->foto) && !empty($mus->foto)) {
                    $f->foto = $mus->foto;
                }
            } else {
                $f->musico_id    = 0;
                $f->banda_nombre = '';
            }
        }

        // 4. Combinar y agrupar por año
        $todos = array_merge($manuales, $autoFallecidos);
        $porAnio = [];
        foreach ($todos as $f) {
            $fecha = (string)($f->fecha_fallecimiento ?? '');
            $y = ($fecha && $fecha !== $v0)
                ? date('Y', strtotime($fecha))
                : 'Sin fecha';
            $porAnio[$y][] = $f;
        }
        foreach ($porAnio as $y => &$arr) {
            usort($arr, function($a, $b) {
                return strcmp((string)($a->artista ?? ''), (string)($b->artista ?? ''));
            });
        }
        unset($arr);
        krsort($porAnio);
        $this->fallecidosPorAnio = $porAnio;

        // 5. Años disponibles
        $db->setQuery(
            'SELECT DISTINCT YEAR(fecha_fallecimiento) AS anio
             FROM #__efemerides_fallecidos
             WHERE state = 1 AND fecha_fallecimiento IS NOT NULL
             UNION
             SELECT DISTINCT YEAR(fecha_defuncion) AS anio
             FROM #__efemerides_musicos
             WHERE state = 1
               AND fecha_defuncion IS NOT NULL
               AND fecha_defuncion != ' . $qV0 . '
             ORDER BY anio DESC'
        );
        $this->aniosDisponibles = $db->loadColumn() ?: [];

        parent::display($tpl);
    }
}
