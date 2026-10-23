<?php
namespace Joomla\Component\Efemerides\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Efemerides\Administrator\Helper\AutoPoblador;

/**
 * ImportModel - procesa el CSV de efemérides.
 *
 * Formato esperado del CSV (con cabecera):
 * titulo, fecha_evento, categoria, artista, descripcion, detalle,
 * nombre_disco, lugar_concierto, asistentes
 *
 * fecha_evento: YYYY-MM-DD  o  DD/MM/YYYY
 * categoria: nombre de la categoría (se busca por nombre, insensible a mayúsculas)
 */
class ImportModel extends BaseDatabaseModel
{
    public function importCsv(string $tmpFile): array
    {
        $db      = $this->getDatabase();
        $imported = 0;
        $skipped  = 0;
        $skipped  = 0;
        $errors   = [];

        // Cargar categorías para mapeo nombre→id
        $db->setQuery('SELECT id, name FROM #__efemerides_categorias');
        $cats    = $db->loadObjectList();
        $catMap  = [];
        foreach ($cats as $cat) {
            $catMap[mb_strtolower(trim($cat->name))] = $cat->id;
        }

        $handle = fopen($tmpFile, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo CSV.'];
        }

        // Detectar separador: ; o ,
        $firstLine = fgets($handle);
        rewind($handle);
        $separator = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        // Leer cabecera
        $header = fgetcsv($handle, 0, $separator);
        if (!$header) {
            fclose($handle);
            return ['success' => false, 'message' => 'CSV vacío o sin cabecera.'];
        }

        // Normalizar nombres de columnas
        // Eliminar BOM UTF-8 del primer campo si existe
        if (!empty($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }
        $header = array_map(fn($h) => mb_strtolower(trim(str_replace([' ', '-'], '_', $h))), $header);

        $row = 1;
        while (($line = fgetcsv($handle, 0, $separator)) !== false) {
            $row++;
            if (count($line) < 2) { $skipped++; continue; }

            $row_padded = array_pad($line, count($header), '');
            $data = array_combine($header, array_slice($row_padded, 0, count($header)));

            // Resolver fecha
            $rawFecha = trim($data['fecha_evento'] ?? ($data['fecha'] ?? ''));
            if (empty($rawFecha)) { $skipped++; continue; }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawFecha)) {
                $fecha = $rawFecha;
            } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $rawFecha, $m)) {
                $fecha = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            } else {
                $errors[] = "Fila {$row}: fecha inválida '{$rawFecha}'";
                $skipped++;
                continue;
            }

            // Validar fecha
            if (!\DateTime::createFromFormat('Y-m-d', $fecha)) { $skipped++; continue; }

            // Resolver categoría
            $catNombre = mb_strtolower(trim($data['categoria'] ?? ''));
            $catId     = 0;
            foreach ($catMap as $nombre => $id) {
                if (strpos($nombre, $catNombre) !== false || strpos($catNombre, $nombre) !== false) {
                    $catId = $id; break;
                }
            }
            // Si no encontramos coincidencia parcial, buscar exacta
            if (!$catId && isset($catMap[$catNombre])) {
                $catId = $catMap[$catNombre];
            }

            $obj = (object)[
                'titulo'          => trim($data['titulo']          ?? 'Sin título'),
                'fecha_evento'    => $fecha,
                'categoria_id'    => $catId,
                'artista'         => trim($data['artista']         ?? ''),
                'descripcion'     => trim($data['descripcion']     ?? ''),
                'detalle'         => trim($data['detalle']         ?? ''),
                'nombre_disco'    => trim($data['nombre_disco']    ?? ''),
                'lugar_concierto' => trim($data['lugar_concierto'] ?? ''),
                'asistentes'      => !empty($data['asistentes']) ? (int)$data['asistentes'] : null,
                'state'           => 1,
                'ordering'        => 0,
                'created'         => (new \DateTime())->format('Y-m-d H:i:s'),
                'created_by'      => Factory::getApplication()->getIdentity()->id,
                'modified'        => (new \DateTime())->format('Y-m-d H:i:s'),
                'modified_by'     => Factory::getApplication()->getIdentity()->id,
            ];

            // ── Anti-duplicados: misma fecha + mismo título ──────────────
            $db->setQuery(
                'SELECT id FROM #__efemerides WHERE fecha_evento = '
                . $db->quote($obj->fecha_evento)
                . ' AND titulo = ' . $db->quote($obj->titulo)
                . ' LIMIT 1'
            );
            if ($db->loadResult()) {
                $skipped++;
                continue;
            }

            try {
                $db->insertObject('#__efemerides', $obj);
                $newId = $db->insertid();
                $imported++;

                // Auto-alimentar músicos solo si la categoría es Defunciones o Nacimientos
                // Para Conciertos y Lanzamientos, el artista suele ser una banda
                $catLower = mb_strtolower(trim((string)($obj->categoria_nombre ?? $data['categoria'] ?? '')));
                $esArtismoIndividual = (strpos($catLower, 'defun')  !== false)
                    || (strpos($catLower, 'fallec') !== false)
                    || (strpos($catLower, 'nacim')  !== false)
                    || (strpos($catLower, 'birth')  !== false);
                if (!empty($obj->artista) && $esArtismoIndividual) {
                    AutoPoblador::addMusico($obj->artista, $db);
                }

                // Auto-sync fallecidos si es Defunciones
                if ($catId) {
                    $db->setQuery('SELECT name FROM #__efemerides_categorias WHERE id = ' . (int)$catId);
                    $catName = mb_strtolower((string)$db->loadResult());
                    if (strpos($catName, 'defunci') !== false || strpos($catName, 'falleci') !== false) {
                        $fObj = (object)[
                            'efemeride_id'        => $newId,
                            'artista'             => $obj->artista,
                            'fecha_fallecimiento' => $fecha,
                            'state'               => 1,
                            'ordering'            => 0,
                            'created'             => $obj->created,
                            'modified'            => $obj->modified,
                        ];
                        $db->insertObject('#__efemerides_fallecidos', $fObj);
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Fila {$row}: " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        return [
            'success'  => true,
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }
}
