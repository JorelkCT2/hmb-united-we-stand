<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;

$db = Factory::getContainer()->get('DatabaseDriver');

$query = $db->getQuery(true)
    ->select('*')
    ->from($db->qn('#__concerts'))
    ->where('state = 1')
    ->where('date_start >= CURDATE()')
    ->order('date_start ASC');

$country = trim($params->get('country_code', ''));
if ($country !== '') {
    $query->where('country_code = ' . $db->q(strtoupper($country)));
}

$concert = $db->setQuery($query)->loadObject();

if (!$concert) {
    return;
}

// Decode headliners
$concert->headlinersList = [];
if (!empty($concert->headliners) && $concert->headliners[0] === '[') {
    $concert->headlinersList = json_decode($concert->headliners, true) ?? [];
}

// Decode prices
$concert->pricesList = [];
if (!empty($concert->prices)) {
    $decoded = json_decode($concert->prices, true);
    if (is_array($decoded)) {
        $concert->pricesList = array_values(
            array_filter($decoded, fn($p) => !empty($p['label']) || !empty($p['amount']))
        );
    }
}

// ── URLs ──────────────────────────────────────────────────────────────────
// IMPORTANTE: hay que pasar la URL por Route::_() para que Joomla resuelva
// el Itemid del menú real (ej: /agenda/agenda-de-conciertos) en vez de
// devolver la URL directa del componente (/component/concerts).
//
// Si el parámetro "agenda_itemid" está vacío, Route::_() no sabe a qué
// ítem de menú asociar la ruta y Joomla cae al patrón por defecto
// "/component/concerts?...". Para evitar depender de que se configure
// a mano, buscamos automáticamente el Itemid de la agenda en #__menu.
$itemId = (int) $params->get('agenda_itemid', 0);

if ($itemId <= 0) {
    // Buscar el ítem de menú publicado que apunta a la vista "concerts"
    // (la agenda), o en su defecto cualquier ítem de com_concerts.
    $menuQuery = $db->getQuery(true)
        ->select('id')
        ->from($db->qn('#__menu'))
        ->where('published = 1')
        ->where('client_id = 0')
        ->where("link LIKE " . $db->q('%option=com_concerts%'))
        ->order("CASE WHEN link LIKE " . $db->q('%view=concerts%') . " THEN 0 ELSE 1 END ASC")
        ->setLimit(1);
    try {
        $foundId = $db->setQuery($menuQuery)->loadResult();
        if ($foundId) {
            $itemId = (int) $foundId;
        }
    } catch (\Exception $e) {
        // si falla la búsqueda, seguimos sin Itemid (mejor que romper la página)
    }
}

$rawUrl = 'index.php?option=com_concerts&view=concert&id=' . $concert->id
        . ($itemId ? '&Itemid=' . $itemId : '');
$concert->detailUrl = Route::_($rawUrl);

$concert->icsUrl = Route::_('index.php?option=com_concerts&task=display.ics&id=' . $concert->id);

// Google Calendar URL (externa, no necesita Route::_())
$gcTitle = urlencode(
    ($concert->title ?: (!empty($concert->headlinersList) ? $concert->headlinersList[0] : ''))
    . ($concert->city ? ' — ' . $concert->city : '')
);
$gcDs  = str_replace('-', '', $concert->date_start)
       . (!empty($concert->concert_time) ? 'T' . str_replace(':', '', substr($concert->concert_time, 0, 5)) . '00' : '');
$isMul = !empty($concert->date_end) && $concert->date_end !== $concert->date_start;
$gcDe  = $isMul ? str_replace('-', '', $concert->date_end) . 'T235959' : $gcDs;
$gcLoc = urlencode(implode(', ', array_filter([$concert->venue, $concert->city, $concert->country_name])));
$concert->gcUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gcTitle}&dates={$gcDs}/{$gcDe}&location={$gcLoc}";

require ModuleHelper::getLayoutPath('mod_concerts_next', $params->get('layout', 'default'));
