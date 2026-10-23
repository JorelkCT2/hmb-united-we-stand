<?php
namespace Hmb\Component\Festival\Site\Model;

defined('_JEXEC') or die;

use Hmb\Component\Festival\Site\Helper\AffinityHelper;
use Hmb\Component\Festival\Site\Helper\IntegrationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class FestivalModel extends BaseDatabaseModel
{
    /**
     * Purga perezosa: en cada carga de estado se eliminan los datos
     * de festivales ya terminados (cumplimiento GDPR sin cron).
     */
    public function purgeExpired(): void
    {
        $db        = $this->getDatabase();
        $params    = ComponentHelper::getParams('com_hmb_festival');
        $purgeDays = max(1, (int) $params->get('purge_days', 7));
        $limit     = (new Date('-' . $purgeDays . ' days'))->format('Y-m-d');
        $now       = (new Date())->toSql();

        try {
            // Check-ins caducados
            $db->setQuery(
                'DELETE FROM ' . $db->quoteName('#__hmb_fest_checkins')
                . ' WHERE ' . $db->quoteName('expires') . ' < ' . $db->quote($now)
            )->execute();

            // Matches de festivales terminados hace más de purge_days, con sus mensajes
            $db->setQuery(
                'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__hmb_fest_matches')
                . ' WHERE ' . $db->quoteName('festival_end') . ' IS NOT NULL'
                . ' AND ' . $db->quoteName('festival_end') . ' < ' . $db->quote($limit)
            );
            $ids = (array) $db->loadColumn();

            if ($ids) {
                $ids = array_map('intval', $ids);
                $db->setQuery(
                    'DELETE FROM ' . $db->quoteName('#__hmb_fest_messages')
                    . ' WHERE ' . $db->quoteName('match_id') . ' IN (' . implode(',', $ids) . ')'
                )->execute();
                $db->setQuery(
                    'DELETE FROM ' . $db->quoteName('#__hmb_fest_matches')
                    . ' WHERE ' . $db->quoteName('id') . ' IN (' . implode(',', $ids) . ')'
                )->execute();
            }

            // Cuernos huérfanos de check-ins desaparecidos
            $db->setQuery(
                'DELETE h FROM ' . $db->quoteName('#__hmb_fest_horns', 'h')
                . ' LEFT JOIN ' . $db->quoteName('#__hmb_fest_checkins', 'c')
                . ' ON c.user_id = h.user_from AND c.festival_id = h.festival_id'
                . ' WHERE c.id IS NULL'
            )->execute();
        } catch (\Throwable $e) {
            // La purga nunca debe romper la página
        }
    }

    public function getFestivals(): array
    {
        $db    = $this->getDatabase();
        $rows  = IntegrationHelper::getActiveFestivals();
        $ids   = array_map(static fn ($r) => (int) $r->id, $rows);
        $count = [];

        if ($ids) {
            try {
                $db->setQuery(
                    'SELECT festival_id, COUNT(*) AS n FROM ' . $db->quoteName('#__hmb_fest_checkins')
                    . ' WHERE visible = 1 AND expires > NOW()'
                    . ' AND festival_id IN (' . implode(',', $ids) . ')'
                    . ' GROUP BY festival_id'
                );

                foreach ((array) $db->loadObjectList() as $c) {
                    $count[(int) $c->festival_id] = (int) $c->n;
                }
            } catch (\Throwable $e) {
                $count = [];
            }
        }

        $out = [];

        foreach ($rows as $r) {
            $out[] = [
                'id'    => (int) $r->id,
                'name'  => (string) $r->name,
                'start' => (string) $r->date_start,
                'end'   => (string) $r->date_end,
                'city'  => (string) $r->city,
                'count' => $count[(int) $r->id] ?? 0,
            ];
        }

        return $out;
    }

    public function getMyCheckins(int $userId): array
    {
        $db = $this->getDatabase();

        $db->setQuery(
            'SELECT * FROM ' . $db->quoteName('#__hmb_fest_checkins')
            . ' WHERE user_id = ' . (int) $userId . ' AND expires > NOW()'
        );

        $out = [];

        foreach ((array) $db->loadObjectList() as $c) {
            $out[] = [
                'festival_id'   => (int) $c->festival_id,
                'festival_name' => (string) $c->festival_name,
                'intent'        => (string) $c->intent,
                'bio'           => (string) $c->bio,
                'visible'       => (int) $c->visible,
            ];
        }

        return $out;
    }

    public function checkin(int $userId, int $festivalId, string $intent, string $bio, bool $adult): array
    {
        if (!in_array($intent, ['colegas', 'ligar', 'ambas'], true)) {
            return ['ok' => false, 'error' => 'Intención no válida.'];
        }

        if (in_array($intent, ['ligar', 'ambas'], true) && !$adult) {
            return ['ok' => false, 'error' => 'Para ese modo debes confirmar que eres mayor de 18.'];
        }

        $festival = null;

        foreach (IntegrationHelper::getActiveFestivals() as $f) {
            if ((int) $f->id === $festivalId) {
                $festival = $f;
                break;
            }
        }

        if (!$festival) {
            return ['ok' => false, 'error' => 'Festival no encontrado o ya terminado.'];
        }

        $bio = mb_substr(trim(strip_tags($bio)), 0, 160);
        $end = substr((string) $festival->date_end, 0, 10);

        try {
            $expires = (new Date($end . ' 23:59:59'))->modify('+1 day')->toSql();
        } catch (\Throwable $e) {
            $expires = (new Date('+14 days'))->toSql();
        }

        $db  = $this->getDatabase();
        $now = (new Date())->toSql();

        $db->setQuery(
            'DELETE FROM ' . $db->quoteName('#__hmb_fest_checkins')
            . ' WHERE user_id = ' . (int) $userId . ' AND festival_id = ' . (int) $festivalId
        )->execute();

        $row = (object) [
            'user_id'         => $userId,
            'festival_id'     => $festivalId,
            'festival_name'   => (string) $festival->name,
            'festival_end'    => $end,
            'intent'          => $intent,
            'adult_confirmed' => $adult ? 1 : 0,
            'bio'             => $bio,
            'visible'         => 1,
            'created'         => $now,
            'expires'         => $expires,
        ];

        $db->insertObject('#__hmb_fest_checkins', $row);

        return ['ok' => true];
    }

    public function checkout(int $userId, int $festivalId): void
    {
        $db = $this->getDatabase();
        $db->setQuery(
            'DELETE FROM ' . $db->quoteName('#__hmb_fest_checkins')
            . ' WHERE user_id = ' . (int) $userId . ' AND festival_id = ' . (int) $festivalId
        )->execute();
    }

    /**
     * Gente checked-in en un festival, filtrada por bloqueos
     * y compatibilidad de intención, con afinidad calculada.
     */
    public function getPeople(int $festivalId, int $me): array
    {
        $db = $this->getDatabase();

        // Mi check-in (necesario para conocer mi intención)
        $db->setQuery(
            'SELECT * FROM ' . $db->quoteName('#__hmb_fest_checkins')
            . ' WHERE user_id = ' . (int) $me . ' AND festival_id = ' . (int) $festivalId
            . ' AND expires > NOW()'
        );
        $mine = $db->loadObject();

        if (!$mine) {
            return ['ok' => false, 'error' => 'Haz check-in primero.'];
        }

        $db->setQuery(
            'SELECT c.*, u.name AS display_name, u.username'
            . ' FROM ' . $db->quoteName('#__hmb_fest_checkins', 'c')
            . ' JOIN ' . $db->quoteName('#__users', 'u') . ' ON u.id = c.user_id'
            . ' WHERE c.festival_id = ' . (int) $festivalId
            . ' AND c.visible = 1 AND c.expires > NOW()'
            . ' AND c.user_id != ' . (int) $me
            . ' AND u.block = 0'
            . ' AND c.user_id NOT IN ('
            . '   SELECT blocked_id FROM ' . $db->quoteName('#__hmb_fest_blocks')
            . '   WHERE user_id = ' . (int) $me . ')'
            . ' AND c.user_id NOT IN ('
            . '   SELECT user_id FROM ' . $db->quoteName('#__hmb_fest_blocks')
            . '   WHERE blocked_id = ' . (int) $me . ')'
            . ' ORDER BY c.created DESC LIMIT 200'
        );
        $rows = (array) $db->loadObjectList();

        // Cuernos que yo ya he dado y matches existentes en este festival
        $db->setQuery(
            'SELECT user_to FROM ' . $db->quoteName('#__hmb_fest_horns')
            . ' WHERE user_from = ' . (int) $me . ' AND festival_id = ' . (int) $festivalId
        );
        $horned = array_map('intval', (array) $db->loadColumn());

        $db->setQuery(
            'SELECT user_a, user_b FROM ' . $db->quoteName('#__hmb_fest_matches')
            . ' WHERE festival_id = ' . (int) $festivalId
            . ' AND (user_a = ' . (int) $me . ' OR user_b = ' . (int) $me . ')'
        );
        $matched = [];

        foreach ((array) $db->loadObjectList() as $m) {
            $matched[] = ((int) $m->user_a === $me) ? (int) $m->user_b : (int) $m->user_a;
        }

        $people = [];

        foreach ($rows as $r) {
            if (!self::intentsCompatible((string) $mine->intent, (string) $r->intent)) {
                continue;
            }

            $affinity = AffinityHelper::between($me, (int) $r->user_id);

            $people[] = [
                'user_id'  => (int) $r->user_id,
                'name'     => (string) ($r->display_name ?: $r->username),
                'intent'   => (string) $r->intent,
                'bio'      => (string) $r->bio,
                'affinity' => $affinity['score'],
                'common'   => $affinity['common'],
                'horned'   => in_array((int) $r->user_id, $horned, true),
                'matched'  => in_array((int) $r->user_id, $matched, true),
            ];
        }

        // Mayor afinidad primero; sin datos de afinidad, al final
        usort($people, static function ($a, $b) {
            return ($b['affinity'] ?? -1) <=> ($a['affinity'] ?? -1);
        });

        // Cuernos recibidos (anónimos): incentivo para repartir cuernos
        $db->setQuery(
            'SELECT COUNT(*) FROM ' . $db->quoteName('#__hmb_fest_horns')
            . ' WHERE user_to = ' . (int) $me . ' AND festival_id = ' . (int) $festivalId
            . ' AND user_from NOT IN ('
            . '   SELECT blocked_id FROM ' . $db->quoteName('#__hmb_fest_blocks')
            . '   WHERE user_id = ' . (int) $me . ')'
        );
        $hornsReceived = (int) $db->loadResult();

        return [
            'ok'             => true,
            'people'         => $people,
            'my_intent'      => (string) $mine->intent,
            'horns_received' => $hornsReceived,
        ];
    }

    public static function intentsCompatible(string $a, string $b): bool
    {
        if ($a === 'ambas' || $b === 'ambas') {
            return true;
        }

        return $a === $b;
    }
}
