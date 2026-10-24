<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

final class MapModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'name', 'country', 'start_date', 'event_type'];
        }
        parent::__construct($config);
    }

    /**
     * La fuente unica de eventos es #__concerts (agenda): conciertos y festivales con coordenadas.
     * #__metalmap_events queda solo como copia historica (se migro entera a #__concerts).
     */
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('a.id'),
                $db->quoteName('a.title', 'name'),
                $db->quoteName('a.alias'),
                $db->quoteName('a.event_type'),
                $db->quoteName('a.description'),
                $db->quoteName('a.date_start', 'start_date'),
                $db->quoteName('a.date_end', 'end_date'),
                $db->quoteName('a.city'),
                $db->quoteName('a.country_name', 'country'),
                $db->quoteName('a.country_code'),
                $db->quoteName('a.latitude'),
                $db->quoteName('a.longitude'),
                'COALESCE(NULLIF(a.website_url, ' . $db->quote('') . '), a.ticket_url) AS ' . $db->quoteName('website'),
                $db->quoteName('a.poster_url', 'image'),
                'COALESCE(NULLIF(a.lineup, ' . $db->quote('') . '), CONCAT_WS(' . $db->quote(', ') . ', NULLIF(a.band, ' . $db->quote('') . '), NULLIF(a.support_bands, ' . $db->quote('') . '))) AS ' . $db->quoteName('bands'),
            ])
            ->from($db->quoteName('#__concerts', 'a'))
            ->where($db->quoteName('a.state') . ' = 1')
            ->where($db->quoteName('a.latitude') . ' IS NOT NULL')
            ->where($db->quoteName('a.latitude') . ' <> 0')
            ->where($db->quoteName('a.longitude') . ' <> 0');

        // Solo eventos futuros si está activado en la config
        $params = \Joomla\CMS\Component\ComponentHelper::getParams('com_metalmap');
        if ((int) $params->get('show_only_future', 0) === 1) {
            $query->where('COALESCE(a.date_end, a.date_start) >= CURDATE()');
        }

        $query->order($db->quoteName('a.date_start') . ' ASC');

        return $query;
    }

    /**
     * Devuelve TODOS los eventos para el mapa, sin paginación.
     * Cruza el campo "bands" con las tablas #__efemerides_bandas y
     * #__efemerides_musicos para enlazar a las fichas de com_efemerides.
     */
    public function getMapData(): array
    {
        $db    = $this->getDatabase();
        $query = $this->getListQuery();

        $db->setQuery($query);
        $items = $db->loadObjectList();

        if (!$items) {
            return [];
        }

        // Itemid único (el de la 2.3 que sí funcionaba)
        $itemid = $this->findEfemeridesItemid();

        $bandasMap  = $this->loadEfemeridesMap('bandas',  $itemid);
        $musicosMap = $this->loadEfemeridesMap('musicos', $itemid);

        $data = [];
        foreach ($items as $item) {
            $bandsRaw = trim((string) $item->bands);
            $bandsList = [];
            if ($bandsRaw !== '') {
                foreach (explode(',', $bandsRaw) as $b) {
                    $name = trim($b);
                    if ($name === '') continue;
                    $key = $this->normalizeBandKey($name);

                    if (isset($bandasMap[$key])) {
                        $bandsList[] = [
                            'name' => $name,
                            'id'   => (int) $bandasMap[$key]['id'],
                            'type' => 'banda',
                            'url'  => $bandasMap[$key]['url'],
                        ];
                    } elseif (isset($musicosMap[$key])) {
                        $bandsList[] = [
                            'name' => $name,
                            'id'   => (int) $musicosMap[$key]['id'],
                            'type' => 'musico',
                            'url'  => $musicosMap[$key]['url'],
                        ];
                    } else {
                        $bandsList[] = [
                            'name' => $name,
                            'id'   => 0,
                            'type' => null,
                            'url'  => null,
                        ];
                    }
                }
            }

            $data[] = [
                'id'           => (int) $item->id,
                'name'         => $item->name,
                'type'         => $item->event_type,
                'description'  => strip_tags((string) $item->description),
                'start_date'   => $item->start_date,
                'end_date'     => $item->end_date,
                'city'         => $item->city,
                'country'      => $item->country,
                'country_code' => strtolower((string) $item->country_code),
                'lat'          => (float) $item->latitude,
                'lng'          => (float) $item->longitude,
                'website'      => $item->website,
                'bands'        => $bandsRaw,
                'bands_list'   => $bandsList,
            ];
        }
        return $data;
    }

    /**
     * Localiza el Itemid del menú frontend que apunta a com_efemerides.
     * Misma lógica que la 2.3: prioriza view=bandas, luego view=musicos, luego cualquiera.
     */
    private function findEfemeridesItemid(): ?int
    {
        $db = $this->getDatabase();
        try {
            $q = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('link')])
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('published') . ' = 1')
                ->where($db->quoteName('client_id') . ' = 0')
                ->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_efemerides%'))
                ->order($db->quoteName('id') . ' ASC');
            $db->setQuery($q);
            $rows = $db->loadObjectList();
        } catch (\Throwable $e) {
            return null;
        }

        if (!$rows) return null;

        foreach ($rows as $r) {
            if (stripos((string) $r->link, 'view=bandas') !== false) return (int) $r->id;
        }
        foreach ($rows as $r) {
            if (stripos((string) $r->link, 'view=musicos') !== false) return (int) $r->id;
        }
        return (int) $rows[0]->id;
    }

    /**
     * Carga publicados de #__efemerides_{bandas|musicos} construyendo URLs frontend.
     */
    private function loadEfemeridesMap(string $tipo, ?int $itemid): array
    {
        if (!in_array($tipo, ['bandas', 'musicos'], true)) {
            return [];
        }

        $db        = $this->getDatabase();
        $tableName = '#__efemerides_' . $tipo;
        $viewName  = ($tipo === 'bandas') ? 'banda' : 'musico';

        try {
            $prefix   = $db->getPrefix();
            $rawTable = $prefix . 'efemerides_' . $tipo;
            $db->setQuery('SHOW TABLES LIKE ' . $db->quote($rawTable));
            if (!$db->loadResult()) {
                return [];
            }
        } catch (\Throwable $e) {
            return [];
        }

        try {
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('nombre')])
                ->from($db->quoteName($tableName))
                ->where($db->quoteName('state') . ' = 1');
            $db->setQuery($query);
            $rows = $db->loadObjectList();
        } catch (\Throwable $e) {
            return [];
        }

        $map = [];
        foreach ($rows as $r) {
            $key = $this->normalizeBandKey((string) $r->nombre);
            if ($key === '' || isset($map[$key])) continue;

            $params = 'index.php?option=com_efemerides&view=' . $viewName . '&id=' . (int) $r->id;
            if ($itemid) {
                $params .= '&Itemid=' . $itemid;
            }
            $url = \Joomla\CMS\Router\Route::_($params);

            $map[$key] = [
                'id'     => (int) $r->id,
                'nombre' => $r->nombre,
                'url'    => $url,
            ];
        }
        return $map;
    }

    /**
     * Normaliza un nombre de banda/artista para matching robusto.
     */
    private function normalizeBandKey(string $name): string
    {
        $name = trim($name);
        if ($name === '') return '';
        $clean = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        if ($clean === false || $clean === '') {
            $clean = $name;
        }
        $clean = strtolower($clean);
        $clean = preg_replace('/[^a-z0-9 ]+/', ' ', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        return trim($clean);
    }
}
