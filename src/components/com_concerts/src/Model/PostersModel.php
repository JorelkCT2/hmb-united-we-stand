<?php
namespace Joomla\Component\Concerts\Site\Model;

defined('_JEXEC') or die;

/**
 * PostersModel: reutiliza ConcertsModel para la vista de carteles.
 * Joomla crea el modelo con el nombre de la vista (Posters),
 * por lo que necesita existir esta clase.
 *
 * A diferencia de la agenda, la galeria de carteles NO oculta los conciertos ya celebrados:
 * es un archivo visual del año. Salen primero los proximos (del mas cercano al mas lejano)
 * y despues los pasados (del mas reciente al mas antiguo).
 */
class PostersModel extends ConcertsModel
{
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $q  = $db->getQuery(true)->select('a.*')->from('#__concerts a')->where('a.state = 1')
            ->where("a.poster_url IS NOT NULL AND a.poster_url <> ''");

        $country = $this->getState('filter.country_code');
        if (!empty($country)) $q->where('a.country_code = ' . $db->q($country));
        $province = $this->getState('filter.province');
        if ($province) $q->where('a.province = ' . $db->q($province));
        $genre = $this->getState('filter.genre_id');
        if ($genre) $q->where('FIND_IN_SET(' . (int) $genre . ', a.genre_ids)');
        $from = $this->getState('filter.date_from');
        $to   = $this->getState('filter.date_to');
        if ($from) $q->where('a.date_start >= ' . $db->q($from));
        if ($to)   $q->where('a.date_start <= ' . $db->q($to));

        $q->order('(a.date_start >= CURDATE()) DESC')
          ->order('CASE WHEN a.date_start >= CURDATE() THEN a.date_start END ASC')
          ->order('a.date_start DESC');

        return $q;
    }
}
