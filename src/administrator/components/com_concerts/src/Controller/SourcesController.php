<?php
namespace Joomla\Component\Concerts\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Concerts\Administrator\Helper\SyncHelper as Sync;

/**
 * Fuentes automaticas, importacion desde una web, geolocalizacion y duplicados.
 * Todas las acciones exigen token y vuelven a la pantalla "Importar y actualizar".
 */
class SourcesController extends BaseController
{
    private function volver(): void
    {
        $this->setRedirect(Route::_('index.php?option=com_concerts&view=import', false));
    }

    private function msg(string $m, string $tipo = 'message'): void
    {
        Factory::getApplication()->enqueueMessage($m, $tipo);
    }

    private function resumen(array $r): string
    {
        $txt = $r['nuevos'] . ' nuevos (pendientes de revisar), ' . $r['actualizados'] . ' actualizados, ' . $r['iguales'] . ' sin cambios de ' . $r['leidos'] . ' leídos'
            . ($r['descartados'] ? ' (' . $r['descartados'] . ' descartados por no parecer metal)' : '') . '.';

        if ($r['errores']) {
            $txt .= ' Avisos: ' . implode(' | ', array_slice($r['errores'], 0, 3)) . (count($r['errores']) > 3 ? '…' : '');
        }

        return $txt;
    }

    public function save(): void
    {
        $this->checkToken();
        $in  = $this->input;
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $id  = $in->getInt('id', 0);
        $tipo = $in->getCmd('type', 'jsonld');

        if (!isset(Sync::TIPOS_FUENTE[$tipo])) {
            $tipo = 'jsonld';
        }

        $params = array_filter([
            'pais'        => strtoupper(substr($in->getString('p_pais', ''), 0, 2)),
            'keyword'     => $in->getString('p_keyword', ''),
            'generos'     => $in->getString('p_generos', ''),
            'meses'       => $in->getInt('p_meses', 0) ?: null,
            'seguir'      => $in->getString('p_seguir', ''),
            'max_paginas' => $in->getInt('p_max', 0) ?: null,
            'solo_pais'   => strtoupper(substr($in->getString('p_solo', ''), 0, 2)),
            'palabras'    => $in->getString('p_palabras', ''),
        ], fn($v) => $v !== '' && $v !== null);
        $params['filtro_metal'] = $in->getInt('p_filtro', 0) ? 1 : 0;

        $row = (object) [
            'name'         => trim($in->getString('name', '')) ?: 'Fuente',
            'type'         => $tipo,
            'url'          => trim($in->getString('url', '')),
            'params'       => json_encode($params, JSON_UNESCAPED_UNICODE),
            'auto_publish' => $in->getInt('auto_publish', 0) ? 1 : 0,
            'active'       => 1,
        ];

        if ($tipo !== 'ticketmaster' && !preg_match('#^https?://#i', $row->url)) {
            $this->msg('La dirección de la fuente no es válida.', 'error');
            $this->volver();

            return;
        }

        if ($id) {
            $row->id = $id;
            $db->updateObject('#__concerts_sources', $row, 'id');
        } else {
            $db->insertObject('#__concerts_sources', $row);
        }

        $this->msg('Fuente guardada.');
        $this->volver();
    }

    public function toggle(): void
    {
        $this->checkToken();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $this->input->getInt('id', 0);
        $db->setQuery('UPDATE #__concerts_sources SET active = 1 - active WHERE id = ' . $id)->execute();
        $this->volver();
    }

    public function delete(): void
    {
        $this->checkToken();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('DELETE FROM #__concerts_sources WHERE id = ' . $this->input->getInt('id', 0))->execute();
        $this->msg('Fuente eliminada. Los eventos ya importados se conservan.');
        $this->volver();
    }

    public function run(): void
    {
        $this->checkToken();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $src = $db->setQuery($db->getQuery(true)->select('*')->from('#__concerts_sources')->where('id = ' . $this->input->getInt('id', 0)))->loadObject();

        if (!$src) {
            $this->msg('Fuente no encontrada.', 'error');
        } else {
            $r = Sync::ejecutarFuente($src);
            $this->msg($src->name . ': ' . $this->resumen($r), $r['leidos'] ? 'message' : 'warning');
        }

        $this->volver();
    }

    public function runall(): void
    {
        $this->checkToken();
        $r = Sync::ejecutarTodas();

        foreach ($r['fuentes'] as $n => $x) {
            $this->msg($n . ': ' . $this->resumen($x), $x['leidos'] ? 'message' : 'warning');
        }

        if (!$r['fuentes']) {
            $this->msg('No hay fuentes activas. Añade una abajo.', 'warning');
        }

        $this->msg('Geolocalización: ' . $r['geo']['hechos'] . ' eventos situados en el mapa, ' . $r['geo']['pendientes'] . ' pendientes.');
        $this->volver();
    }

    /** Importar de una dirección suelta (JSON-LD o .ics, se detecta solo). */
    public function url(): void
    {
        $this->checkToken();
        $url = trim($this->input->getString('url', ''));
        $src = (object) [
            'id' => 0, 'name' => 'URL suelta', 'url' => $url, 'params' => json_encode(['seguir' => $this->input->getString('seguir', ''), 'max_paginas' => 20]),
            'auto_publish' => $this->input->getInt('auto_publish', 0) ? 1 : 0,
            'type' => (preg_match('#\.ics($|\?)#i', $url) || stripos($url, 'ical') !== false || stripos($url, 'webcal') !== false) ? 'ics' : 'jsonld',
        ];

        if (!preg_match('#^https?://#i', $url)) {
            $this->msg('Escribe una dirección que empiece por http:// o https://', 'error');
        } else {
            $r = Sync::ejecutarFuente($src);
            $this->msg($this->resumen($r), $r['leidos'] ? 'message' : 'warning');

            if (!$r['leidos']) {
                $this->msg('No se encontraron eventos con datos estructurados en esa página. Prueba con su calendario .ics, con una página de evento concreta o con el CSV.', 'warning');
            }
        }

        $this->volver();
    }

    public function geo(): void
    {
        $this->checkToken();
        $r = Sync::geocodificarPendientes(25);
        $this->msg($r['hechos'] . ' eventos situados en el mapa. Pendientes: ' . $r['pendientes'] . ($r['pendientes'] ? ' (vuelve a pulsar para seguir; el servicio de mapas admite 1 consulta por segundo).' : '.'));
        $this->volver();
    }

    /** Publica todos los pendientes de revisar (state 0 procedentes de fuentes). */
    public function publicarpendientes(): void
    {
        $this->checkToken();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery("UPDATE #__concerts SET state = 1 WHERE state = 0 AND source IS NOT NULL AND source NOT IN ('manual')")->execute();
        $this->msg($db->getAffectedRows() . ' eventos publicados.');
        $this->setRedirect(Route::_('index.php?option=com_concerts&view=concerts', false));
    }

    public function despublicar(): void
    {
        $this->checkToken();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('UPDATE #__concerts SET state = 0 WHERE id = ' . $this->input->getInt('id', 0))->execute();
        $this->msg('Evento despublicado.');
        $this->volver();
    }
}
