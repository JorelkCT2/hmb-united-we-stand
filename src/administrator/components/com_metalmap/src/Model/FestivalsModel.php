<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

final class FestivalsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'name', 'a.name',
                'event_type', 'a.event_type',
                'country', 'a.country',
                'start_date', 'a.start_date',
                'state', 'a.state',
                'ordering', 'a.ordering',
            ];
        }
        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.start_date', $direction = 'ASC')
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $state = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $state);

        $country = $app->getUserStateFromRequest($this->context . '.filter.country', 'filter_country', '', 'string');
        $this->setState('filter.country', $country);

        $type = $app->getUserStateFromRequest($this->context . '.filter.event_type', 'filter_event_type', '', 'string');
        $this->setState('filter.event_type', $type);

        // Llamar a parent (gestiona limitstart, limit)
        parent::populateState($ordering, $direction);

        // Leer filter_order y filter_order_Dir DIRECTAMENTE de la URL (input).
        // Evitamos getUserStateFromRequest aquí porque la caché de sesión puede
        // estar machacando el valor de la URL con el almacenado.
        $orderColRaw = $input->getString('filter_order', '');
        $orderDirRaw = $input->getCmd('filter_order_Dir', '');

        // Fallback al state guardado (de un submit anterior)
        if ($orderColRaw === '') {
            $orderColRaw = (string) $this->state->get('list.ordering', $ordering);
        }
        if ($orderDirRaw === '') {
            $orderDirRaw = (string) $this->state->get('list.direction', $direction);
        }

        // Validar la columna contra la lista permitida
        if (!in_array($orderColRaw, $this->filter_fields, true)) {
            $orderColRaw = $ordering;
        }
        // Validar dirección
        $orderDirRaw = strtoupper($orderDirRaw);
        if (!in_array($orderDirRaw, ['ASC', 'DESC'], true)) {
            $orderDirRaw = $direction;
        }

        $this->setState('list.ordering',  $orderColRaw);
        $this->setState('list.direction', $orderDirRaw);
    }

    /**
     * Cache key que incluye todos los filtros para evitar resultados cacheados
     * de queries anteriores cuando el usuario cambia orden o filtros.
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.country');
        $id .= ':' . $this->getState('filter.event_type');
        $id .= ':' . $this->getState('list.ordering');
        $id .= ':' . $this->getState('list.direction');
        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->from($db->quoteName('#__metalmap_events', 'a'));

        // Filtro búsqueda
        $search = trim((string) $this->getState('filter.search', ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where('(' .
                $db->quoteName('a.name')    . ' LIKE :s1 OR ' .
                $db->quoteName('a.city')    . ' LIKE :s2 OR ' .
                $db->quoteName('a.country') . ' LIKE :s3 OR ' .
                $db->quoteName('a.bands')   . ' LIKE :s4)'
            )
            ->bind(':s1', $like)
            ->bind(':s2', $like)
            ->bind(':s3', $like)
            ->bind(':s4', $like);
        }

        // Filtro state
        $state = $this->getState('filter.state', '');
        if ($state !== '' && $state !== null) {
            $state = (int) $state;
            $query->where($db->quoteName('a.state') . ' = :state')->bind(':state', $state, ParameterType::INTEGER);
        } else {
            $query->where($db->quoteName('a.state') . ' >= 0');
        }

        // Filtro país
        $country = (string) $this->getState('filter.country', '');
        if ($country !== '') {
            $query->where($db->quoteName('a.country_code') . ' = :cc')->bind(':cc', $country);
        }

        // Filtro tipo
        $type = (string) $this->getState('filter.event_type', '');
        if ($type !== '') {
            $query->where($db->quoteName('a.event_type') . ' = :et')->bind(':et', $type);
        }

        // ─── Orden — leemos del state, con fallback a la URL ───
        $orderCol = (string) $this->state->get('list.ordering',  '');
        $orderDir = (string) $this->state->get('list.direction', '');

        // Si el state está vacío, leer de la URL como último recurso
        if ($orderCol === '') {
            $orderCol = (string) Factory::getApplication()->getInput()->getString('filter_order', 'a.start_date');
        }
        if ($orderDir === '') {
            $orderDir = (string) Factory::getApplication()->getInput()->getCmd('filter_order_Dir', 'ASC');
        }

        // Validar columna contra filter_fields
        if (!in_array($orderCol, $this->filter_fields, true)) {
            $orderCol = 'a.start_date';
        }
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

        $query->order($db->escape($orderCol) . ' ' . $orderDir);

        return $query;
    }
}
