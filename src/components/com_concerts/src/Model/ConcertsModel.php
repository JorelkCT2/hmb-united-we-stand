<?php
namespace Joomla\Component\Concerts\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

class ConcertsModel extends ListModel
{
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['id','band','city','date_start'];
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db  = $this->getDatabase();
        $app = Factory::getApplication();
        $q   = $db->getQuery(true)->select('a.*')->from('#__concerts a')->where('a.state = 1');

        $params = $app->getParams('com_concerts');
        $past   = $app->input->getInt('past', 0) === 1;
        if ($past) {
            // vista «celebrados»: solo los que ya terminaron
            $q->where('COALESCE(a.date_end, a.date_start) < CURDATE()');
        } elseif (!$params->get('show_past', 0)) {
            // un festival de varios dias sigue en la agenda hasta que termina
            $q->where('COALESCE(a.date_end, a.date_start) >= CURDATE()');
        }
        $country  = $this->getState('filter.country_code');
        if (!empty($country)) $q->where('a.country_code = ' . $db->q($country));
        $province = $this->getState('filter.province');
        if ($province) $q->where('a.province = ' . $db->q($province));
        $genre    = $this->getState('filter.genre_id');
        if ($genre)    $q->where('FIND_IN_SET(' . (int)$genre . ', a.genre_ids)');
        $from = $this->getState('filter.date_from');
        $to   = $this->getState('filter.date_to');
        if ($from) $q->where('a.date_start >= ' . $db->q($from));
        if ($to)   $q->where('a.date_start <= ' . $db->q($to));
        $q->order($past ? 'a.date_start DESC, a.concert_time ASC' : 'a.date_start ASC, a.concert_time ASC');
        return $q;
    }

    protected function populateState($ordering = 'a.date_start', $direction = 'ASC'): void
    {
        $app = Factory::getApplication();
        $this->setState('filter.country_code', $app->input->getString('country_code', ''));
        $this->setState('filter.province',     $app->input->getString('province', ''));
        $this->setState('filter.genre_id',     $app->input->getInt('genre_id', 0));
        $this->setState('filter.date_from',    $app->input->getString('date_from', ''));
        $this->setState('filter.date_to',      $app->input->getString('date_to', ''));
        parent::populateState($ordering, $direction);
        // la agenda se muestra completa (con filtros en pantalla), no paginada de 20 en 20
        $this->setState('list.limit', 500);
        $this->setState('list.start', 0);
    }

    /** ¿Hay conciertos ya celebrados? (para ofrecer el enlace «ver celebrados») */
    public function hayPasados(): bool
    {
        $db = $this->getDatabase();

        return (int) $db->setQuery($db->getQuery(true)->select('COUNT(*)')->from('#__concerts')
            ->where('state = 1')->where('COALESCE(date_end, date_start) < CURDATE()'))->loadResult() > 0;
    }

    /** All genres from mujg_artistas_generos for filter dropdown */
    public function getGenres(): array
    {
        $db = $this->getDatabase();
        try {
            return $db->setQuery(
                $db->getQuery(true)
                   ->select('id, name')
                   ->from('#__artistas_generos')
                   ->where('state = 1')
                   ->order('name ASC')
            )->loadAssocList('id', 'name');
        } catch (\Exception) {
            return [];
        }
    }

    /** Resolve genre IDs to names for display */
    public static function resolveGenreNames(string $ids, array $allGenres): array
    {
        if (empty($ids)) return [];
        $result = [];
        foreach (explode(',', $ids) as $id) {
            $id = (int)trim($id);
            if ($id && isset($allGenres[$id])) {
                $result[] = $allGenres[$id];
            }
        }
        return $result;
    }

    public function getProvinces(string $country = 'ES'): array
    {
        $db = $this->getDatabase();
        return $db->setQuery(
            $db->getQuery(true)->select('DISTINCT province')->from('#__concerts')
               ->where('state=1')->where('country_code=' . $db->q($country))
               ->where('province IS NOT NULL')->where("province != ''")
               ->order('province ASC')
        )->loadColumn();
    }
}
