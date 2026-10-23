<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;

class ModHmbLatestNewsHelper
{
    public static function getArticles(\Joomla\Registry\Registry $params): array
    {
        $app    = Factory::getApplication();
        $db     = Factory::getDbo();
        $user   = $app->getIdentity();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        $count    = (int) $params->get('count', 6);
        $ordering = $params->get('ordering', 'created_desc');

        // ── Leer configuración de categorías (subform) ────────────────────────
        $categoriesRaw = $params->get('categories', []);
        if (is_object($categoriesRaw)) {
            $categoriesRaw = (array) $categoriesRaw;
        }

        // Construir mapa catId => maxPorCategoria
        $catLimits = []; // [catId => max]
        foreach ($categoriesRaw as $row) {
            $row = (object) $row;
            $cid = (int) ($row->catid ?? 0);
            $max = (int) ($row->max_count ?? 3);
            if ($cid > 0) {
                $catLimits[$cid] = $max;
            }
        }

        // Fallback: compatibilidad con campo catid antiguo (texto plano)
        if (empty($catLimits)) {
            $catIdRaw = $params->get('catid', '');
            $countPerCat = (int) $params->get('count_per_cat', 0);
            if (is_array($catIdRaw)) {
                $ids = array_values(array_filter(array_map('intval', $catIdRaw)));
            } else {
                $ids = array_values(array_filter(array_map('intval', explode(',', (string)$catIdRaw))));
            }
            foreach ($ids as $cid) {
                $catLimits[$cid] = $countPerCat > 0 ? $countPerCat : 999;
            }
        }

        $allCatIds = array_keys($catLimits);
        $featured  = (int) $params->get('show_featured', 1);

        // ── FASE 1: artículos respetando límite por categoría ─────────────────
        $collected = [];
        $usedIds   = [];

        if (!empty($catLimits)) {
            // Obtener artículos de cada categoría por separado para respetar el máximo
            foreach ($catLimits as $catId => $maxForCat) {
                $rows = self::queryArticles($db, $groups, [$catId], $featured, $ordering, $usedIds, $maxForCat);
                foreach ($rows as $row) {
                    $collected[] = $row;
                    $usedIds[]   = (int) $row->id;
                }
            }
        }

        // ── FASE 2: rellenar hasta $count con artículos de las mismas categorías ──
        $needed = $count - count($collected);
        if ($needed > 0 && !empty($allCatIds)) {
            $extra = self::queryArticles($db, $groups, $allCatIds, $featured, $ordering, $usedIds, $needed);
            foreach ($extra as $row) {
                $collected[] = $row;
                $usedIds[]   = (int) $row->id;
            }
        }

        // ── FASE 3: si aún faltan artículos, buscar en TODAS las categorías ──
        $needed = $count - count($collected);
        if ($needed > 0) {
            $extra = self::queryArticles($db, $groups, [], $featured, $ordering, $usedIds, $needed);
            foreach ($extra as $row) {
                $collected[] = $row;
            }
        }

        // Reordenar el resultado combinado según ordering
        $collected = self::sortArticles($collected, $ordering);

        // Limitar al total final
        $collected = array_slice($collected, 0, $count);

        // Enriquecer con URLs, imágenes, fechas
        foreach ($collected as &$row) {
            self::enrichArticle($row);
        }
        unset($row);

        return $collected;
    }

    // ── Consulta artículos de una o varias categorías ─────────────────────────
    private static function queryArticles(
        \Joomla\Database\DatabaseInterface $db,
        string $groups,
        array $catIds,
        int $featured,
        string $ordering,
        array $excludeIds,
        int $limit
    ): array {
        if ($limit <= 0) return [];

        $query = $db->getQuery(true);
        $query->select([
            'DISTINCT ' . $db->quoteName('a.id'),
            $db->quoteName('a.title'),
            $db->quoteName('a.alias'),
            $db->quoteName('a.introtext'),
            $db->quoteName('a.fulltext', 'fulltext_content'),
            $db->quoteName('a.images'),
            $db->quoteName('a.created'),
            $db->quoteName('a.modified'),
            $db->quoteName('a.publish_up'),
            $db->quoteName('a.hits'),
            $db->quoteName('a.featured'),
            $db->quoteName('a.catid'),
            $db->quoteName('a.language'),
            $db->quoteName('c.title', 'category_title'),
            $db->quoteName('c.alias', 'category_alias'),
            $db->quoteName('u.name', 'author'),
        ])
        ->from($db->quoteName('#__content', 'a'))
        ->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
        ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'))
        ->where($db->quoteName('a.state') . ' = 1')
        ->where($db->quoteName('a.access') . ' IN (' . $groups . ')')
        ->where($db->quoteName('c.access') . ' IN (' . $groups . ')');

        // Fechas (Joomla 6 puede tener NULL)
        $now      = $db->quote(Factory::getDate()->toSql());
        $nullDate = $db->quote($db->getNullDate());
        $query->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' = ' . $nullDate . ' OR ' . $db->quoteName('a.publish_up') . ' <= ' . $now . ')');
        $query->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' = ' . $nullDate . ' OR ' . $db->quoteName('a.publish_down') . ' >= ' . $now . ')');

        // Destacados
        if ($featured === 0) $query->where($db->quoteName('a.featured') . ' = 0');
        elseif ($featured === 2) $query->where($db->quoteName('a.featured') . ' = 1');

        // Categorías
        if (!empty($catIds)) {
            $query->where($db->quoteName('a.catid') . ' IN (' . implode(',', array_map('intval', $catIds)) . ')');
        }

        // Excluir IDs ya recogidos
        if (!empty($excludeIds)) {
            $query->where($db->quoteName('a.id') . ' NOT IN (' . implode(',', array_map('intval', $excludeIds)) . ')');
        }

        // Ordenación
        switch ($ordering) {
            case 'created_asc':   $query->order($db->quoteName('a.created') . ' ASC');   break;
            case 'modified_desc': $query->order($db->quoteName('a.modified') . ' DESC'); break;
            case 'hits_desc':     $query->order($db->quoteName('a.hits') . ' DESC');     break;
            case 'rand':          $query->order('RAND()');                                break;
            default:              $query->order($db->quoteName('a.created') . ' DESC');  break;
        }

        $db->setQuery($query, 0, $limit);

        try {
            return $db->loadObjectList() ?? [];
        } catch (\RuntimeException $e) {
            return [];
        }
    }

    // ── Reordena el array combinado según ordering ────────────────────────────
    private static function sortArticles(array $articles, string $ordering): array
    {
        if ($ordering === 'rand') {
            shuffle($articles);
            return $articles;
        }
        usort($articles, function ($a, $b) use ($ordering) {
            switch ($ordering) {
                case 'created_asc':   return strcmp($a->created ?? '', $b->created ?? '');
                case 'modified_desc': return strcmp($b->modified ?? '', $a->modified ?? '');
                case 'hits_desc':     return (int)($b->hits ?? 0) - (int)($a->hits ?? 0);
                default:              return strcmp($b->created ?? '', $a->created ?? '');
            }
        });
        return $articles;
    }

    // ── Enriquece un artículo con URL, imagen y fecha ─────────────────────────
    private static function enrichArticle(object &$row): void
    {
        $row->link = Route::_(
            RouteHelper::getArticleRoute($row->id, $row->catid, $row->language ?? '*')
        );

        // Imagen: JSON intro → JSON fulltext → primera <img> introtext → primera <img> fulltext
        $images        = json_decode($row->images ?? '{}', true);
        $imageIntro    = $images['image_intro']    ?? '';
        $imageFulltext = $images['image_fulltext'] ?? '';

        if (empty($imageIntro) && !empty($imageFulltext)) {
            $imageIntro = $imageFulltext;
        }
        if (empty($imageIntro)) {
            $imageIntro = self::extractFirstImage($row->introtext ?? '');
        }
        if (empty($imageIntro)) {
            $imageIntro = self::extractFirstImage($row->fulltext_content ?? '');
        }
        if (!empty($imageIntro) && strpos($imageIntro, 'http') !== 0 && strpos($imageIntro, '//') !== 0) {
            $imageIntro = Uri::root() . ltrim($imageIntro, '/');
        }

        $row->image_intro     = $imageIntro;
        $row->image_intro_alt     = $images['image_intro_alt']     ?? htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');
        $row->image_intro_caption = $images['image_intro_caption'] ?? '';
        $row->introtext_clean = html_entity_decode(strip_tags($row->introtext ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $row->display_date    = !empty($row->created)
            ? Factory::getDate($row->created)->format('d M Y', true) : '';
        $row->category_color  = self::getCategoryColor($row->category_alias ?? '');
        $row->category_link   = Route::_(RouteHelper::getCategoryRoute($row->catid, $row->language ?? '*'));
    }

    private static function extractFirstImage(string $html): string
    {
        if (empty($html)) return '';
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
            return $m[1];
        }
        return '';
    }

    public static function getCategoryColor(string $alias): string
    {
        $map = [
            'noticias'    => '#CC0000', 'news'        => '#CC0000',
            'reviews'     => '#FF6600', 'criticas'    => '#FF6600',
            'cronicas'    => '#9900CC', 'entrevistas' => '#0066CC',
            'interviews'  => '#0066CC', 'reportajes'  => '#006633',
            'videos'      => '#CC6600', 'conciertos'  => '#993300',
        ];
        $alias = strtolower(trim($alias));
        if (isset($map[$alias])) return $map[$alias];
        foreach ($map as $key => $color) {
            if (strpos($alias, $key) !== false) return $color;
        }
        return '#CC0000';
    }

    public static function truncateText(string $text, int $limit = 120): string
    {
        if ($limit <= 0 || mb_strlen($text) <= $limit) return $text;
        $t = mb_substr($text, 0, $limit);
        $s = mb_strrpos($t, ' ');
        return ($s !== false ? mb_substr($t, 0, $s) : $t) . '…';
    }
}
