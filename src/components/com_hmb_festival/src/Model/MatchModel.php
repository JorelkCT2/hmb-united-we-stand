<?php
namespace Hmb\Component\Festival\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class MatchModel extends BaseDatabaseModel
{
    /**
     * Dar cuernos 🤘 a alguien. Si son recíprocos, se crea el match.
     */
    public function horn(int $me, int $target, int $festivalId): array
    {
        if ($me === $target) {
            return ['ok' => false, 'error' => 'Quererse a uno mismo está bien, pero no así.'];
        }

        $db = $this->getDatabase();

        // Ambos deben tener check-in vigente en el festival
        $db->setQuery(
            'SELECT user_id, intent, festival_end FROM ' . $db->quoteName('#__hmb_fest_checkins')
            . ' WHERE festival_id = ' . (int) $festivalId
            . ' AND expires > NOW() AND visible = 1'
            . ' AND user_id IN (' . (int) $me . ',' . (int) $target . ')'
        );
        $rows = (array) $db->loadObjectList('user_id');

        if (count($rows) < 2) {
            return ['ok' => false, 'error' => 'Ese usuario ya no está en el festival.'];
        }

        if (!FestivalModel::intentsCompatible((string) $rows[$me]->intent, (string) $rows[$target]->intent)) {
            return ['ok' => false, 'error' => 'Intenciones no compatibles.'];
        }

        // Bloqueos en cualquier dirección
        $db->setQuery(
            'SELECT COUNT(*) FROM ' . $db->quoteName('#__hmb_fest_blocks')
            . ' WHERE (user_id = ' . (int) $me . ' AND blocked_id = ' . (int) $target . ')'
            . ' OR (user_id = ' . (int) $target . ' AND blocked_id = ' . (int) $me . ')'
        );

        if ((int) $db->loadResult() > 0) {
            return ['ok' => false, 'error' => 'No disponible.'];
        }

        $now = (new Date())->toSql();

        try {
            $row = (object) [
                'user_from'   => $me,
                'user_to'     => $target,
                'festival_id' => $festivalId,
                'created'     => $now,
            ];
            $db->insertObject('#__hmb_fest_horns', $row);
        } catch (\Throwable $e) {
            // Duplicado: ya le habías dado cuernos. No es error.
        }

        // ¿Recíproco?
        $db->setQuery(
            'SELECT COUNT(*) FROM ' . $db->quoteName('#__hmb_fest_horns')
            . ' WHERE user_from = ' . (int) $target . ' AND user_to = ' . (int) $me
            . ' AND festival_id = ' . (int) $festivalId
        );

        if ((int) $db->loadResult() === 0) {
            return ['ok' => true, 'matched' => false];
        }

        $a = min($me, $target);
        $b = max($me, $target);

        try {
            $match = (object) [
                'user_a'       => $a,
                'user_b'       => $b,
                'festival_id'  => $festivalId,
                'festival_end' => $rows[$me]->festival_end ?: null,
                'created'      => $now,
            ];
            $db->insertObject('#__hmb_fest_matches', $match);
        } catch (\Throwable $e) {
            // Match ya existente
        }

        return ['ok' => true, 'matched' => true];
    }

    public function getMatches(int $me): array
    {
        $db = $this->getDatabase();

        $db->setQuery(
            'SELECT m.*, ua.name AS name_a, ua.username AS user_a_login,'
            . ' ub.name AS name_b, ub.username AS user_b_login'
            . ' FROM ' . $db->quoteName('#__hmb_fest_matches', 'm')
            . ' JOIN ' . $db->quoteName('#__users', 'ua') . ' ON ua.id = m.user_a'
            . ' JOIN ' . $db->quoteName('#__users', 'ub') . ' ON ub.id = m.user_b'
            . ' WHERE (m.user_a = ' . (int) $me . ' OR m.user_b = ' . (int) $me . ')'
            . ' AND m.user_a NOT IN (SELECT blocked_id FROM ' . $db->quoteName('#__hmb_fest_blocks')
            . '   WHERE user_id = ' . (int) $me . ')'
            . ' AND m.user_b NOT IN (SELECT blocked_id FROM ' . $db->quoteName('#__hmb_fest_blocks')
            . '   WHERE user_id = ' . (int) $me . ')'
            . ' ORDER BY m.created DESC LIMIT 100'
        );
        $rows = (array) $db->loadObjectList();

        $out = [];

        foreach ($rows as $m) {
            $otherId   = ((int) $m->user_a === $me) ? (int) $m->user_b : (int) $m->user_a;
            $otherName = ((int) $m->user_a === $me)
                ? (string) ($m->name_b ?: $m->user_b_login)
                : (string) ($m->name_a ?: $m->user_a_login);

            // Último mensaje y no leídos (volúmenes de MVP: un par de consultas por match es asumible)
            $db->setQuery(
                'SELECT body, user_from, created FROM ' . $db->quoteName('#__hmb_fest_messages')
                . ' WHERE match_id = ' . (int) $m->id . ' ORDER BY id DESC LIMIT 1'
            );
            $last = $db->loadObject();

            $db->setQuery(
                'SELECT COUNT(*) FROM ' . $db->quoteName('#__hmb_fest_messages')
                . ' WHERE match_id = ' . (int) $m->id
                . ' AND user_from = ' . (int) $otherId . ' AND is_read = 0'
            );
            $unread = (int) $db->loadResult();

            // Nombre del festival desde el check-in denormalizado (puede haber caducado: fallback genérico)
            $db->setQuery(
                'SELECT festival_name FROM ' . $db->quoteName('#__hmb_fest_checkins')
                . ' WHERE festival_id = ' . (int) $m->festival_id . ' LIMIT 1'
            );
            $festName = (string) ($db->loadResult() ?: 'Festival');

            $out[] = [
                'match_id'    => (int) $m->id,
                'festival_id' => (int) $m->festival_id,
                'user_id'     => $otherId,
                'name'        => $otherName,
                'festival'  => $festName,
                'last'      => $last ? mb_substr((string) $last->body, 0, 60) : '',
                'unread'    => $unread,
                'created'   => (string) $m->created,
            ];
        }

        return $out;
    }

    private function assertMember(int $matchId, int $me): ?object
    {
        $db = $this->getDatabase();
        $db->setQuery(
            'SELECT * FROM ' . $db->quoteName('#__hmb_fest_matches')
            . ' WHERE id = ' . (int) $matchId
            . ' AND (user_a = ' . (int) $me . ' OR user_b = ' . (int) $me . ')'
        );

        return $db->loadObject();
    }

    public function getMessages(int $matchId, int $me, int $sinceId): array
    {
        $match = $this->assertMember($matchId, $me);

        if (!$match) {
            return ['ok' => false, 'error' => 'Match no encontrado.'];
        }

        $db    = $this->getDatabase();
        $other = ((int) $match->user_a === $me) ? (int) $match->user_b : (int) $match->user_a;

        // Marcar como leídos los del otro
        $db->setQuery(
            'UPDATE ' . $db->quoteName('#__hmb_fest_messages')
            . ' SET is_read = 1 WHERE match_id = ' . (int) $matchId
            . ' AND user_from = ' . (int) $other . ' AND is_read = 0'
        )->execute();

        $db->setQuery(
            'SELECT id, user_from, body, created FROM ' . $db->quoteName('#__hmb_fest_messages')
            . ' WHERE match_id = ' . (int) $matchId
            . ' AND id > ' . (int) $sinceId
            . ' ORDER BY id ASC LIMIT 100'
        );

        $messages = [];

        foreach ((array) $db->loadObjectList() as $msg) {
            $messages[] = [
                'id'      => (int) $msg->id,
                'mine'    => ((int) $msg->user_from === $me),
                'body'    => (string) $msg->body,
                'created' => (string) $msg->created,
            ];
        }

        return ['ok' => true, 'messages' => $messages];
    }

    public function send(int $matchId, int $me, string $body): array
    {
        $match = $this->assertMember($matchId, $me);

        if (!$match) {
            return ['ok' => false, 'error' => 'Match no encontrado.'];
        }

        $body = mb_substr(trim(strip_tags($body)), 0, 1000);

        if ($body === '') {
            return ['ok' => false, 'error' => 'Mensaje vacío.'];
        }

        $db = $this->getDatabase();

        // Anti-flood básico: máximo 10 mensajes por minuto
        $db->setQuery(
            'SELECT COUNT(*) FROM ' . $db->quoteName('#__hmb_fest_messages')
            . ' WHERE user_from = ' . (int) $me
            . ' AND created > DATE_SUB(NOW(), INTERVAL 1 MINUTE)'
        );

        if ((int) $db->loadResult() >= 10) {
            return ['ok' => false, 'error' => 'Frena un poco, máquina. Espera un minuto.'];
        }

        $row = (object) [
            'match_id'  => $matchId,
            'user_from' => $me,
            'body'      => $body,
            'is_read'   => 0,
            'created'   => (new Date())->toSql(),
        ];
        $db->insertObject('#__hmb_fest_messages', $row);

        return ['ok' => true];
    }

    public function block(int $me, int $target): array
    {
        if ($me === $target) {
            return ['ok' => false, 'error' => 'No puedes bloquearte a ti mismo.'];
        }

        $db = $this->getDatabase();

        try {
            $row = (object) [
                'user_id'    => $me,
                'blocked_id' => $target,
                'created'    => (new Date())->toSql(),
            ];
            $db->insertObject('#__hmb_fest_blocks', $row);
        } catch (\Throwable $e) {
            // Ya bloqueado
        }

        return ['ok' => true];
    }

    public function report(int $me, int $target, int $festivalId, string $reason): array
    {
        $db     = $this->getDatabase();
        $reason = mb_substr(trim(strip_tags($reason)), 0, 500);

        $row = (object) [
            'user_id'     => $me,
            'reported_id' => $target,
            'festival_id' => $festivalId,
            'reason'      => $reason,
            'created'     => (new Date())->toSql(),
        ];
        $db->insertObject('#__hmb_fest_reports', $row);

        return ['ok' => true];
    }
}
