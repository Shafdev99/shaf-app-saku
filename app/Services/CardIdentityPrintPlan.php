<?php

namespace App\Services;

class CardIdentityPrintPlan
{
    /** @return list<string> */
    public static function sides(string $jenis, bool $hasBack): array
    {
        return $hasBack ? ['depan', 'belakang'] : ['depan'];
    }

    /** @param list<string> $ownerIds
     * @return list<array{sisi: string, ids: list<string>}>
     */
    public static function forSheets(array $ownerIds, string $jenis, bool $hasBack, int $perSheet = 9): array
    {
        $pages = [];
        foreach (array_chunk($ownerIds, $perSheet) as $batch) {
            foreach (self::sides($jenis, $hasBack) as $side) {
                $pages[] = ['sisi' => $side, 'ids' => $batch];
            }
        }

        return $pages;
    }
}
