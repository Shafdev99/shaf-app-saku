<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceCardRegistry
{
    public function claim(string $cardId, string $entity, string $entityId): void
    {
        try {
            DB::table('rfid_cards')->insert([
                'card_id' => $cardId,
                'entitas' => $entity,
                'entitas_id' => $entityId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages([
                'card_id' => 'Kartu tersebut sudah tertaut ke siswa atau tenaga pendidik lain.',
            ]);
        } catch (\Illuminate\Database\QueryException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'unique') || str_contains(strtolower($exception->getMessage()), 'duplicate')) {
                throw ValidationException::withMessages([
                    'card_id' => 'Kartu tersebut sudah tertaut ke siswa atau tenaga pendidik lain.',
                ]);
            }

            throw $exception;
        }
    }

    public function release(string $entity, string $entityId): void
    {
        DB::table('rfid_cards')->where('entitas', $entity)->where('entitas_id', $entityId)->delete();
    }
}
