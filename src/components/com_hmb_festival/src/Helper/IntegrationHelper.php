<?php
namespace Hmb\Component\Festival\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

/**
 * Punto único de integración con com_metalmap y com_hmb_quiz.
 * Todo lo que dependa de tablas externas vive aquí.
 */
class IntegrationHelper
{
    /**
     * Festivales activos o futuros desde la tabla de MetalMap.
     * Tabla y columnas son configurables en las opciones del componente.
     */
    public static function getActiveFestivals(): array
    {
        $params = ComponentHelper::getParams('com_hmb_festival');
        $db     = Factory::getContainer()->get('DatabaseDriver');

        $table   = trim((string) $params->get('fest_table', '#__metalmap_festivals'));
        $colId   = trim((string) $params->get('fest_col_id', 'id'));
        $colName = trim((string) $params->get('fest_col_name', 'name'));
        $colIni  = trim((string) $params->get('fest_col_start', 'date_start'));
        $colFin  = trim((string) $params->get('fest_col_end', 'date_end'));
        $colCity = trim((string) $params->get('fest_col_city', 'city'));

        if ($table === '' || $colId === '' || $colName === '' || $colFin === '') {
            return [];
        }

        $select = $db->quoteName($colId) . ' AS id, '
            . $db->quoteName($colName) . ' AS name, '
            . $db->quoteName($colIni) . ' AS date_start, '
            . $db->quoteName($colFin) . ' AS date_end';

        if ($colCity !== '') {
            $select .= ', ' . $db->quoteName($colCity) . ' AS city';
        }

        $sql = 'SELECT ' . $select
            . ' FROM ' . $db->quoteName($table)
            . ' WHERE ' . $db->quoteName($colFin) . ' >= CURDATE()'
            . ' ORDER BY ' . $db->quoteName($colIni) . ' ASC'
            . ' LIMIT 30';

        try {
            $db->setQuery($sql);
            $rows = (array) $db->loadObjectList();
        } catch (\Throwable $e) {
            return [];
        }

        foreach ($rows as $row) {
            if (!isset($row->city)) {
                $row->city = '';
            }
        }

        return $rows;
    }

    /**
     * Vector de géneros del usuario para el cálculo de afinidad.
     * Se define vía la opción affinity_sql con el marcador {user}.
     * Si no está configurada o falla, devuelve [] y la afinidad se desactiva.
     */
    public static function getGenreVector(int $userId): array
    {
        static $cache = [];

        if (isset($cache[$userId])) {
            return $cache[$userId];
        }

        $params = ComponentHelper::getParams('com_hmb_festival');
        $sql    = trim((string) $params->get('affinity_sql', ''));

        if ($sql === '' || stripos($sql, 'select') !== 0) {
            return $cache[$userId] = [];
        }

        $sql = str_replace('{user}', (string) $userId, $sql);
        $db  = Factory::getContainer()->get('DatabaseDriver');

        try {
            $db->setQuery($sql);
            $rows = (array) $db->loadObjectList();
        } catch (\Throwable $e) {
            return $cache[$userId] = [];
        }

        $vector = [];

        foreach ($rows as $row) {
            $genre = isset($row->genre) ? trim((string) $row->genre) : '';
            $weight = isset($row->weight) ? (float) $row->weight : 0.0;

            if ($genre !== '' && $weight > 0) {
                $vector[mb_strtolower($genre)] = ($vector[mb_strtolower($genre)] ?? 0) + $weight;
            }
        }

        return $cache[$userId] = $vector;
    }
}
