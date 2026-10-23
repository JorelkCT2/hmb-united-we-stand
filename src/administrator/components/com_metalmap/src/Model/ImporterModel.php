<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseDriver;

final class ImporterModel extends BaseDatabaseModel
{
    private const COLUMNS = [
        'name', 'event_type', 'start_date', 'end_date', 'city', 'country',
        'country_code', 'latitude', 'longitude', 'website', 'description', 'bands',
    ];

    public function import(string $filePath, bool $updateExisting = true, string $delimiter = ','): array
    {
        $result = ['inserted' => 0, 'updated' => 0, 'errors' => [], 'debug' => []];

        if (!is_readable($filePath)) {
            $result['errors'][] = 'No se puede leer el archivo CSV en: ' . $filePath;
            return $result;
        }

        // Leer contenido completo y quitar BOM si lo tiene
        $content = file_get_contents($filePath);
        if ($content === false || $content === '') {
            $result['errors'][] = 'El archivo CSV está vacío.';
            return $result;
        }
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }

        // Normalizar saltos de línea
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Parsear línea a línea con str_getcsv (sin el parámetro escape deprecado)
        $lines = explode("\n", $content);
        $lines = array_filter($lines, fn($l) => trim($l) !== '');

        if (count($lines) < 2) {
            $result['errors'][] = 'El CSV no tiene datos (solo cabecera o vacío).';
            return $result;
        }

        // Cabecera
        $headerLine = array_shift($lines);
        $header = str_getcsv($headerLine, $delimiter, '"', '');
        $header = array_map(fn($h) => strtolower(trim((string) $h)), $header);

        $result['debug'][] = 'Cabecera detectada: ' . implode(', ', $header);
        $result['debug'][] = 'Total líneas de datos: ' . count($lines);

        // Mapeo cabecera → índice
        $map = [];
        foreach (self::COLUMNS as $col) {
            $idx = array_search($col, $header, true);
            if ($idx !== false) {
                $map[$col] = $idx;
            }
        }

        if (!isset($map['name'], $map['latitude'], $map['longitude'])) {
            $result['errors'][] = 'Faltan columnas obligatorias. Necesito: name, latitude, longitude.';
            $result['errors'][] = 'Detectadas: ' . implode(', ', array_keys($map));
            return $result;
        }

        /** @var DatabaseDriver $db */
        $db   = $this->getDatabase();
        $user = Factory::getApplication()->getIdentity();
        $now  = Factory::getDate()->toSql();
        $lineNum = 1;

        foreach ($lines as $line) {
            $lineNum++;
            $row = str_getcsv($line, $delimiter, '"', '');

            if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $data = [];
            foreach ($map as $col => $idx) {
                $data[$col] = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
            }

            if (empty($data['name'])) {
                $result['errors'][] = "Línea $lineNum: name vacío.";
                continue;
            }

            $lat = (float) str_replace(',', '.', $data['latitude'] ?? '0');
            $lng = (float) str_replace(',', '.', $data['longitude'] ?? '0');
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                $result['errors'][] = "Línea $lineNum: coordenadas fuera de rango ({$data['name']}: $lat, $lng)";
                continue;
            }

            $alias = ApplicationHelper::stringURLSafe($data['name']);
            if ($alias === '') {
                $alias = 'evento-' . md5($data['name']);
            }

            $existingId = 0;
            if ($updateExisting) {
                try {
                    $q = $db->getQuery(true)
                        ->select($db->quoteName('id'))
                        ->from($db->quoteName('#__metalmap_events'))
                        ->where($db->quoteName('alias') . ' = :alias')
                        ->bind(':alias', $alias);
                    $existingId = (int) $db->setQuery($q)->loadResult();
                } catch (\Throwable $e) {
                    $result['errors'][] = "Línea $lineNum: error checkando duplicado: " . $e->getMessage();
                }
            }

            $record = (object) [
                'name'         => $data['name'],
                'alias'        => $alias,
                'event_type'   => in_array(strtolower($data['event_type'] ?? ''), ['concert','concierto'], true) ? 'concert' : 'festival',
                'description'  => $data['description'] ?? '',
                'start_date'   => $this->normalizeDate($data['start_date'] ?? ''),
                'end_date'     => $this->normalizeDate($data['end_date'] ?? ''),
                'city'         => $data['city'] ?? '',
                'country'      => $data['country'] ?? '',
                'country_code' => strtolower(substr(trim($data['country_code'] ?? ''), 0, 2)),
                'latitude'     => $lat,
                'longitude'    => $lng,
                'website'      => $data['website'] ?? '',
                'bands'        => $data['bands'] ?? '',
                'state'        => 1,
                'modified'     => $now,
                'modified_by'  => $user ? (int) $user->id : 0,
            ];

            try {
                if ($existingId > 0) {
                    $record->id = $existingId;
                    $db->updateObject('#__metalmap_events', $record, 'id');
                    $result['updated']++;
                } else {
                    $record->created    = $now;
                    $record->created_by = $user ? (int) $user->id : 0;
                    $db->insertObject('#__metalmap_events', $record);
                    $result['inserted']++;
                }
            } catch (\Throwable $e) {
                $result['errors'][] = "Línea $lineNum ({$data['name']}): " . $e->getMessage();
            }
        }

        return $result;
    }

    private function normalizeDate(string $date): ?string
    {
        $date = trim($date);
        if ($date === '') return null;

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $date);
            if ($dt && $dt->format($fmt) === $date) {
                return $dt->format('Y-m-d');
            }
        }
        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
