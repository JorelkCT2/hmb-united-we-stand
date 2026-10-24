<?php
namespace Joomla\Component\Concerts\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class ConcertsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id', 'band', 'city', 'date_start', 'state', 'event_type', 'source'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $q  = $db->getQuery(true)->select('a.*')->from($db->qn('#__concerts', 'a'));

        $state = (string) $this->getState('filter.state');

        if ($state === 'pend') {
            // pendientes de revisar: llegaron de una fuente y nadie los ha publicado
            $q->where('a.state = 0')->where("a.source IS NOT NULL AND a.source <> 'manual'");
        } elseif (is_numeric($state)) {
            $q->where('a.state = ' . (int) $state);
        }

        $tipo = (string) $this->getState('filter.tipo');

        if (in_array($tipo, ['concert', 'festival'], true)) {
            $q->where('a.event_type = ' . $db->q($tipo));
        }

        $fecha = (string) $this->getState('filter.fecha');

        if ($fecha === 'proximos') {
            $q->where('COALESCE(a.date_end, a.date_start) >= CURDATE()');
        } elseif ($fecha === 'pasados') {
            $q->where('COALESCE(a.date_end, a.date_start) < CURDATE()');
        }

        if ((string) $this->getState('filter.cartel') === '0') {
            $q->where("(a.poster_url IS NULL OR a.poster_url = '')");
        }

        if ((string) $this->getState('filter.geo') === '0') {
            $q->where('(a.latitude IS NULL OR a.latitude = 0)');
        }

        $origen = (string) $this->getState('filter.origen');

        if ($origen !== '') {
            $q->where('a.source = ' . $db->q($origen));
        }

        $search = $this->getState('filter.search');

        if ($search) {
            $like = $db->q('%' . $db->escape($search, true) . '%');
            $q->where("(a.title LIKE $like OR a.band LIKE $like OR a.headliners LIKE $like "
                     . "OR a.support_bands LIKE $like OR a.lineup LIKE $like OR a.city LIKE $like "
                     . "OR a.venue LIKE $like OR a.address LIKE $like)");
        }

        $q->order($fecha === 'pasados' ? 'a.date_start DESC' : 'a.date_start ASC');

        return $q;
    }

    protected function populateState($ordering = 'a.date_start', $direction = 'ASC'): void
    {
        foreach (['search', 'state', 'tipo', 'fecha', 'cartel', 'geo', 'origen'] as $f) {
            $def = $f === 'fecha' ? 'proximos' : '';
            $this->setState('filter.' . $f, $this->getUserStateFromRequest($this->context . '.filter.' . $f, 'filter_' . $f, $def));
        }

        parent::populateState($ordering, $direction);
    }

    /** Orígenes distintos presentes en la tabla (para el filtro). */
    public function getOrigenes(): array
    {
        $db = $this->getDatabase();

        return $db->setQuery("SELECT DISTINCT source FROM #__concerts WHERE source IS NOT NULL AND source <> '' ORDER BY source")->loadColumn() ?: [];
    }
}
