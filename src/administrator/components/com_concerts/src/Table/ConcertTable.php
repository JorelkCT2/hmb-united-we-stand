<?php
namespace Joomla\Component\Concerts\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class ConcertTable extends Table
{
    private const CN = [
        'ES'=>'España','DE'=>'Alemania','AT'=>'Austria','BE'=>'Bélgica','BG'=>'Bulgaria',
        'HR'=>'Croacia','CZ'=>'República Checa','DK'=>'Dinamarca','SK'=>'Eslovaquia',
        'SI'=>'Eslovenia','EE'=>'Estonia','FI'=>'Finlandia','FR'=>'Francia','GR'=>'Grecia',
        'HU'=>'Hungría','IE'=>'Irlanda','IS'=>'Islandia','IT'=>'Italia','LV'=>'Letonia',
        'LT'=>'Lituania','LU'=>'Luxemburgo','MT'=>'Malta','NO'=>'Noruega',
        'NL'=>'Países Bajos','PL'=>'Polonia','PT'=>'Portugal','GB'=>'Reino Unido',
        'RO'=>'Rumanía','SE'=>'Suecia','CH'=>'Suiza',
    ];

    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__concerts', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function check(): bool
    {
        // Headliners: textarea newlines → JSON
        if (!empty($this->headliners) && $this->headliners[0] !== '[') {
            $lines = array_values(array_filter(array_map('trim', explode("\n", $this->headliners))));
            $this->headliners = json_encode($lines, JSON_UNESCAPED_UNICODE);
        }
        $hls = (!empty($this->headliners) && $this->headliners[0] === '[')
            ? (json_decode($this->headliners, true) ?? []) : [];

        // band = first headliner (for alias/search compatibility)
        $this->band = !empty($hls) ? $hls[0] : ($this->title ?? '');

        // Auto alias
        if (empty(trim($this->alias ?? ''))) {
            $this->alias = ApplicationHelper::stringURLSafe(
                ($this->title ?: $this->band) . '-' . $this->city . '-' . $this->date_start
            );
        }

        // genre_ids: handle all formats Joomla might submit
        // Could be: array, JSON string ["1","2"], or comma-separated "1,2,3"
        if (is_array($this->genre_ids)) {
            // Array from form → comma-separated integers
            $this->genre_ids = implode(',', array_map('intval', array_filter($this->genre_ids)));
        } elseif (!empty($this->genre_ids) && $this->genre_ids[0] === '[') {
            // JSON array string → comma-separated
            $arr = json_decode($this->genre_ids, true) ?? [];
            $this->genre_ids = implode(',', array_map('intval', $arr));
        }
        // If already comma-separated, leave as is

        // Country name
        $code = strtoupper(trim($this->country_code ?: 'ES'));
        $this->country_name = self::CN[$code] ?? $code;

        // Prices: remove empty rows
        if (!empty($this->prices) && $this->prices[0] === '[') {
            $prices = array_values(array_filter(
                json_decode($this->prices, true) ?? [],
                fn($p) => !empty($p['label']) || !empty($p['amount'])
            ));
            $this->prices = json_encode($prices, JSON_UNESCAPED_UNICODE);
        }

        $now = (new \DateTime())->format('Y-m-d H:i:s');
        if (empty($this->id)) $this->created = $now;
        $this->modified = $now;

        return parent::check();
    }
}
