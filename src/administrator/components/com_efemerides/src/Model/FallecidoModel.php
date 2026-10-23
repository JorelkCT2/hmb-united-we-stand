<?php
namespace Joomla\Component\Efemerides\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class FallecidoModel extends AdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm(
            'com_efemerides.fallecido', 'fallecido',
            ['control' => 'jform', 'load_data' => $loadData]
        ) ?: false;
    }

    protected function loadFormData()
    {
        $d = Factory::getApplication()->getUserState('com_efemerides.edit.fallecido.data', []);
        return empty($d) ? $this->getItem() : $d;
    }

    public function getTable($n = 'Fallecido', $p = 'Administrator', $o = [])
    {
        return parent::getTable($n, $p, $o);
    }

    /**
     * Al guardar un fallecido, sincronizar fechas con el músico si existe
     */
    public function save($data)
    {
        $result = parent::save($data);

        if ($result) {
            $artista = trim((string)($data['artista'] ?? ''));
            if ($artista) {
                $this->syncFechasMusico($artista, $data);
            }
        }

        return $result;
    }

    private function syncFechasMusico(string $artista, array $data): void
    {
        try {
            $db  = Factory::getContainer()->get('DatabaseDriver');
            $v0  = '0000-00-00';

            // Buscar si el artista existe en músicos
            $db->setQuery(
                'SELECT id, fecha_nacimiento, fecha_defuncion FROM #__efemerides_musicos
                 WHERE nombre = ' . $db->quote($artista) . ' AND state = 1 LIMIT 1'
            );
            $mus = $db->loadObject();
            if (!$mus) return;

            $updates  = [];
            $defMusico = (string)($mus->fecha_defuncion  ?? '');
            $nacMusico = (string)($mus->fecha_nacimiento ?? '');
            $sinDef    = ($defMusico === '' || $defMusico === $v0 || $defMusico === null);
            $sinNac    = ($nacMusico === '' || $nacMusico === $v0 || $nacMusico === null);

            // 1. Desde los datos del fallecido que se acaba de guardar
            $defFallecido = trim((string)($data['fecha_fallecimiento'] ?? ''));
            $nacFallecido = trim((string)($data['fecha_nacimiento'] ?? ''));
            if ($sinDef && $defFallecido && $defFallecido !== $v0) {
                $updates[] = 'fecha_defuncion = ' . $db->quote($defFallecido);
                $sinDef = false;
            }
            if ($sinNac && $nacFallecido && $nacFallecido !== $v0) {
                $updates[] = 'fecha_nacimiento = ' . $db->quote($nacFallecido);
                $sinNac = false;
            }

            // 2. Desde la tabla de efemérides si sigue faltando
            if ($sinDef || $sinNac) {
                $db->setQuery(
                    'SELECT e.fecha_evento, c.name AS cat
                     FROM #__efemerides e
                     LEFT JOIN #__efemerides_categorias c ON c.id = e.categoria_id
                     WHERE e.artista = ' . $db->quote($artista) . ' AND e.state = 1
                       AND (LOWER(c.name) LIKE "%nacim%" OR LOWER(c.name) LIKE "%defun%"
                            OR LOWER(c.name) LIKE "%fallec%" OR LOWER(c.name) LIKE "%birth%")'
                );
                foreach ($db->loadObjectList() ?: [] as $eRow) {
                    $cat   = mb_strtolower((string)($eRow->cat ?? ''));
                    $fecha = (string)($eRow->fecha_evento ?? '');
                    if (!$fecha || $fecha === $v0) continue;
                    if ($sinNac && (strpos($cat,'nacim') !== false || strpos($cat,'birth') !== false)) {
                        $updates[] = 'fecha_nacimiento = ' . $db->quote($fecha);
                        $sinNac = false;
                    }
                    if ($sinDef && (strpos($cat,'defun') !== false || strpos($cat,'fallec') !== false)) {
                        $updates[] = 'fecha_defuncion = ' . $db->quote($fecha);
                        $sinDef = false;
                    }
                }
            }

            if (!empty($updates)) {
                $db->setQuery(
                    'UPDATE #__efemerides_musicos SET ' . implode(', ', $updates) .
                    ' WHERE id = ' . (int)$mus->id
                );
                $db->execute();
            }
        } catch (\Throwable $e) {
            // Silencioso
        }
    }
}
