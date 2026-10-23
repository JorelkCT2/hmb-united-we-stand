<?php
namespace Joomla\Component\Efemerides\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Modelo de efemérides para el frontend.
 * Calcula la próxima ocurrencia de cada efeméride en el año actual
 * y permite mostrarlas ordenadas por proximidad al día de hoy
 * (sin importar si es antes o después).
 */
class EfemeridesModel extends BaseDatabaseModel
{
    /**
     * Obtiene las efemérides del día actual (mismo mes y día, cualquier año).
     *
     * @param   int|null  $categoriaId  Filtrar por categoría
     * @return  array
     */
    public function getEfemeridesHoy(?int $categoriaId = null): array
    {
        $db  = $this->getDatabase();
        $hoy = new \DateTime();
        $mes = (int)$hoy->format('m');
        $dia = (int)$hoy->format('d');

        $query = $db->getQuery(true);
        $query->select(['e.*', 'c.name AS categoria_nombre'])
              ->from($db->quoteName('#__efemerides', 'e'))
              ->join('LEFT', $db->quoteName('#__efemerides_categorias', 'c') . ' ON c.id = e.categoria_id')
              ->where('e.state = 1')
              ->where('MONTH(e.fecha_evento) = ' . $mes)
              ->where('DAY(e.fecha_evento) = ' . $dia)
              ->order('e.fecha_evento DESC');

        if ($categoriaId) {
            $query->where('e.categoria_id = ' . (int)$categoriaId);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Obtiene las N efemérides más próximas al día de hoy
     * (sin contar el año, ordenadas por cercanía al día actual en el calendario).
     *
     * @param   int        $limit        Número de efemérides a mostrar
     * @param   int|null   $categoriaId  Filtrar por categoría
     * @param   string     $fecha        Fecha base YYYY-MM-DD (default: hoy)
     * @return  array
     */
    public function getEfemeridesProximas(int $limit = 10, ?int $categoriaId = null, string $fecha = ''): array
    {
        $db  = $this->getDatabase();

        if (empty($fecha)) {
            $fecha = date('Y-m-d');
        }

        $base = new \DateTime($fecha);
        $mesDia = $base->format('m-d'); // MM-DD de referencia

        // Obtenemos todas las efemérides publicadas (sin filtro de fecha)
        $query = $db->getQuery(true);
        $query->select(['e.*', 'c.name AS categoria_nombre'])
              ->from($db->quoteName('#__efemerides', 'e'))
              ->join('LEFT', $db->quoteName('#__efemerides_categorias', 'c') . ' ON c.id = e.categoria_id')
              ->where('e.state = 1');

        if ($categoriaId) {
            $query->where('e.categoria_id = ' . (int)$categoriaId);
        }

        $query->order('e.fecha_evento DESC');
        $db->setQuery($query);
        $all = $db->loadObjectList() ?: [];

        // Eliminar duplicados mismo artista+mes+día (quedarnos con el más antiguo como representativo)
        $seen = [];
        $unique = [];
        foreach ($all as $item) {
            $d = new \DateTime($item->fecha_evento);
            $key = $item->artista . '|' . $d->format('m-d');
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $item;
            }
        }

        // Calcular distancia circular en días respecto a la fecha base (ignorando año)
        $currentYear = (int)$base->format('Y');
        $refDoy = (int)$base->format('z'); // día del año de referencia

        foreach ($unique as $item) {
            $d = new \DateTime($item->fecha_evento);
            // Construir la ocurrencia en el año actual
            $occurrence = \DateTime::createFromFormat('Y-m-d',
                $currentYear . '-' . $d->format('m-d')
            );
            // Si falla (ej. 29-feb en año no bisiesto), usar 28-feb
            if (!$occurrence) {
                $occurrence = \DateTime::createFromFormat('Y-m-d', $currentYear . '-02-28');
            }

            $occDoy = (int)$occurrence->format('z');
            $diff   = $occDoy - $refDoy;

            // Distancia circular (±182 días máximo)
            if ($diff > 182)  $diff -= 365;
            if ($diff < -182) $diff += 365;

            $item->_diff_dias   = $diff;
            $item->_ocurrencia  = $occurrence->format('Y-m-d');
            $item->_anios_atras = $currentYear - (int)$d->format('Y');
        }

        // Ordenar por distancia absoluta (más cercano primero)
        usort($unique, function($a, $b) { return abs($a->_diff_dias) <=> abs($b->_diff_dias); });

        return array_slice($unique, 0, $limit);
    }

    /**
     * Devuelve los artistas fallecidos del memorial (frontend).
     */
    public function getFallecidos(?int $year = null): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('f.*, e.titulo AS efemeride_titulo, e.descripcion AS efemeride_desc')
              ->from($db->quoteName('#__efemerides_fallecidos', 'f'))
              ->join('LEFT', $db->quoteName('#__efemerides', 'e') . ' ON e.id = f.efemeride_id')
              ->where('f.state = 1')
              ->order('YEAR(f.fecha_fallecimiento) DESC, f.artista ASC');

        if ($year) {
            $query->where('YEAR(f.fecha_fallecimiento) = ' . (int)$year);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }
}
