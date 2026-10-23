<?php
namespace Hmb\Component\Festival\Site\Helper;

defined('_JEXEC') or die;

/**
 * Afinidad musical entre dos usuarios: similitud coseno
 * sobre sus vectores de géneros del quiz.
 */
class AffinityHelper
{
    /**
     * @return array{score: int|null, common: array<string>}
     */
    public static function between(int $userA, int $userB): array
    {
        $va = IntegrationHelper::getGenreVector($userA);
        $vb = IntegrationHelper::getGenreVector($userB);

        if (empty($va) || empty($vb)) {
            return ['score' => null, 'common' => []];
        }

        $dot = 0.0;
        $common = [];

        foreach ($va as $genre => $weight) {
            if (isset($vb[$genre])) {
                $dot += $weight * $vb[$genre];
                $common[] = $genre;
            }
        }

        $normA = sqrt(array_sum(array_map(static fn ($w) => $w * $w, $va)));
        $normB = sqrt(array_sum(array_map(static fn ($w) => $w * $w, $vb)));

        if ($dot <= 0 || $normA <= 0 || $normB <= 0) {
            return ['score' => 0, 'common' => []];
        }

        // Géneros comunes ordenados por peso combinado, top 3 para la tarjeta
        usort($common, static function ($g1, $g2) use ($va, $vb) {
            $w1 = $va[$g1] * $vb[$g1];
            $w2 = $va[$g2] * $vb[$g2];

            return $w2 <=> $w1;
        });

        return [
            'score'  => (int) round(($dot / ($normA * $normB)) * 100),
            'common' => array_slice($common, 0, 3),
        ];
    }
}
