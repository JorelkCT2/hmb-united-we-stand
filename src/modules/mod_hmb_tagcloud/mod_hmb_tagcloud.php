<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;

$moduleId  = 'hmb-tagcloud-' . $module->id;
$maxTags   = (int) $params->get('max_tags', 40);
$minCount  = (int) $params->get('min_count', 1);
$minSize   = (int) $params->get('min_size', 11);
$maxSize   = (int) $params->get('max_size', 28);
$ordering  = $params->get('ordering', 'count');  // count | title | random

$db    = Factory::getDbo();
$query = $db->getQuery(true)
    ->select([
        $db->quoteName('t.id'),
        $db->quoteName('t.title'),
        $db->quoteName('t.alias'),
        'COUNT(' . $db->quoteName('ct.tag_id') . ') AS ' . $db->quoteName('count')
    ])
    ->from($db->quoteName('#__tags', 't'))
    ->join('INNER', $db->quoteName('#__contentitem_tag_map', 'ct') . ' ON ' . $db->quoteName('ct.tag_id') . ' = ' . $db->quoteName('t.id'))
    ->where($db->quoteName('t.published') . ' = 1')
    ->where($db->quoteName('t.access') . ' IN (' . implode(',', array_map('intval', Factory::getApplication()->getIdentity()->getAuthorisedViewLevels())) . ')')
    ->where($db->quoteName('t.level') . ' > 0') // excluir root
    ->group($db->quoteName('t.id'))
    ->having('COUNT(' . $db->quoteName('ct.tag_id') . ') >= ' . $minCount);

switch ($ordering) {
    case 'title':  $query->order($db->quoteName('t.title') . ' ASC'); break;
    case 'random': $query->order('RAND()'); break;
    default:       $query->order('count DESC'); break;
}

$query->setLimit($maxTags);
$db->setQuery($query);
$tags = $db->loadObjectList() ?: [];

if (empty($tags)) return;

// Calcular tamaños proporcionales
$counts = array_column($tags, 'count');
$min    = min($counts);
$max    = max($counts);
$range  = max(1, $max - $min);

foreach ($tags as &$tag) {
    $ratio      = ($tag->count - $min) / $range;
    $tag->size  = round($minSize + $ratio * ($maxSize - $minSize));
    $tag->weight = round(300 + $ratio * 400); // 300–700
    $tag->url   = Route::_('index.php?option=com_tags&view=tag&id=' . $tag->id . '-' . $tag->alias);
}
unset($tag);

// Mezclar para que no salgan siempre los mismos primeros
if ($ordering !== 'title') shuffle($tags);

require ModuleHelper::getLayoutPath('mod_hmb_tagcloud', 'default');
