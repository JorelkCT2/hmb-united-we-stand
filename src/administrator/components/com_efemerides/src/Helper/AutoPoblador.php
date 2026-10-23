<?php
namespace Joomla\Component\Efemerides\Administrator\Helper;
defined('_JEXEC') or die;

class AutoPoblador
{
    /**
     * Añade artista a músicos SOLO si no existe ya como banda.
     * Si ya está en bandas → no hace nada (es una banda, no un músico).
     */
    public static function addMusico(string $nombre, $db): void
    {
        $nombre = trim($nombre);
        if (!$nombre || mb_strtolower($nombre) === 'varios artistas') return;

        // ── ¿Ya existe en la tabla de bandas? → NO crear músico ──────────────
        try {
            $db->setQuery('SELECT COUNT(*) FROM #__efemerides_bandas WHERE nombre = ' . $db->quote($nombre));
            if ((int)$db->loadResult() > 0) return;
        } catch (\Exception $e) {}

        // ── ¿Ya existe en músicos? → INSERT IGNORE hace nada ─────────────────
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        try {
            $db->setQuery('INSERT IGNORE INTO #__efemerides_musicos (nombre,state,created,modified) VALUES ('
                . $db->quote($nombre) . ',1,' . $db->quote($now) . ',' . $db->quote($now) . ')')->execute();
        } catch (\Exception $e) {}
    }

    public static function addBanda(string $nombre, $db): void
    {
        $nombre = trim($nombre);
        if (!$nombre) return;
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        try {
            $db->setQuery('INSERT IGNORE INTO #__efemerides_bandas (nombre,state,created,modified) VALUES ('
                . $db->quote($nombre) . ',1,' . $db->quote($now) . ',' . $db->quote($now) . ')')->execute();
        } catch (\Exception $e) {}
    }

    public static function getBandaId(string $nombre, $db): ?int
    {
        if (!trim($nombre)) return null;
        $db->setQuery('SELECT id FROM #__efemerides_bandas WHERE nombre=' . $db->quote(trim($nombre)));
        $id = $db->loadResult();
        return $id ? (int)$id : null;
    }
}
