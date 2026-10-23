<?php
/**
 * @package     mod_sorteo
 * @subpackage  Helper
 * @author      MuJG
 * @license     GNU/GPL v2 or later
 */

namespace MuJG\Module\Sorteo\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Helper del módulo Sorteo.
 *
 * La tabla #__sorteo_ganadores (= mujg_sorteo_ganadores con el prefijo del sitio)
 * se crea automáticamente al instalar el módulo vía sql/install.mysql.sql.
 */
final class ModSorteoHelper
{
    /**
     * Obtiene la lista de usuarios registrados y activos que participan en el sorteo.
     *
     * @param   Registry  $params  Parámetros del módulo
     *
     * @return  array<int, array{id: string, name: string, username: string, email: string}>
     */
    public static function getUsuarios(Registry $params): array
    {
        // Joomla 6: intentar el contenedor DI primero, caer a Factory::getDbo() si falla
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
        } catch (\Throwable $e) {
            $db = Factory::getDbo();
        }
        $maxUsuarios = (int) $params->get('max_usuarios', 200);
        $excluirStr  = trim((string) $params->get('excluir_ids', '1'));
        $soloGrupo   = (int) $params->get('solo_grupo', 0);

        // Construir lista de IDs a excluir (siempre excluir superadmin id=1 por defecto si está en el campo)
        $excluirIds = [];
        if ($excluirStr !== '') {
            foreach (explode(',', $excluirStr) as $id) {
                $id = (int) trim($id);
                if ($id > 0) {
                    $excluirIds[] = $id;
                }
            }
        }

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('u.id'),
                $db->quoteName('u.name'),
                $db->quoteName('u.username'),
                $db->quoteName('u.email'),
            ])
            ->from($db->quoteName('#__users', 'u'))
            ->where($db->quoteName('u.block') . ' = 0')
            ->where($db->quoteName('u.activation') . ' = ' . $db->quote(''))
            ->order('RAND()');

        // Filtrar por grupo si se especificó
        if ($soloGrupo > 0) {
            $query->join(
                'INNER',
                $db->quoteName('#__user_usergroup_map', 'ugm'),
                $db->quoteName('ugm.user_id') . ' = ' . $db->quoteName('u.id')
            )->where($db->quoteName('ugm.group_id') . ' = ' . $soloGrupo);
        }

        // Excluir IDs indicados
        if (!empty($excluirIds)) {
            $query->whereNotIn($db->quoteName('u.id'), $excluirIds);
        }

        // Limitar resultados
        if ($maxUsuarios > 0) {
            $db->setQuery($query, 0, $maxUsuarios);
        } else {
            $db->setQuery($query);
        }

        return $db->loadAssocList() ?? [];
    }

    /**
     * Registra un ganador en la tabla de historial.
     *
     * @param   int     $userId      ID del usuario ganador
     * @param   string  $userName    Nombre del ganador
     * @param   string  $username    Username del ganador
     * @param   bool    $presente    Si estaba presente (true) o fue saltado (false)
     *
     * @return  bool
     */
    public static function guardarGanador(
        int $userId,
        string $userName,
        string $username,
        bool $presente = true
    ): bool {
        try {
            try {
                $db = Factory::getContainer()->get(DatabaseInterface::class);
            } catch (\Throwable $e2) {
                $db = Factory::getDbo();
            }

            $row = (object) [
                'user_id'      => $userId,
                'user_name'    => $userName,
                'user_username'=> $username,
                'presente'     => (int) $presente,
                'fecha_sorteo' => (new \DateTime())->format('Y-m-d H:i:s'),
                'created'      => (new \DateTime())->format('Y-m-d H:i:s'),
            ];

            $db->insertObject('#__sorteo_ganadores', $row);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtiene el historial de ganadores confirmados.
     *
     * @return  array
     */
    public static function getHistorialGanadores(): array
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
        } catch (\Throwable $e) {
            $db = Factory::getDbo();
        }

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sorteo_ganadores'))
            ->where($db->quoteName('presente') . ' = 1')
            ->order($db->quoteName('created') . ' DESC');

        $db->setQuery($query);
        return $db->loadAssocList() ?? [];
    }
}
