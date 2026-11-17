<?php

namespace App\Services;

class CardIdentityFields
{
    /** @return array{name: string, id: ?string, secondary_id: ?string, detail: string, photo: ?string, missing: list<string>} */
    public static function map(string $jenis, object $person): array
    {
        if ($jenis === 'pelajar') {
            $name = filled($person->nama_lengkap) ? (string) $person->nama_lengkap : null;
            $id = filled($person->nis) ? (string) $person->nis : null;
            $secondaryId = filled($person->nisn) ? (string) $person->nisn : null;
            $detail = filled($person->kartu_kelas) ? (string) $person->kartu_kelas : null;
            $photo = $person->foto ?: null;

            return [
                'name' => $name ?: 'Nama belum tersedia',
                'id' => $id,
                'secondary_id' => $secondaryId,
                'detail' => $detail ?: 'Kelas belum tersedia',
                'photo' => $photo,
                'missing' => array_values(array_keys(array_filter([
                    'name' => $name === null,
                    'id' => $id === null,
                    'secondary_id' => $secondaryId === null,
                    'detail' => $detail === null,
                    'photo' => $photo === null,
                ]))),
            ];
        }

        $name = filled($person->nama_guru) ? (string) $person->nama_guru : null;
        $id = filled($person->nip) ? (string) $person->nip : null;
        $detail = filled($person->namaJabatan?->nama_jabatan) ? (string) $person->namaJabatan->nama_jabatan : null;
        $photo = $person->foto_pegawai ?: null;

        return [
            'name' => $name ?: 'Nama belum tersedia',
            'id' => $id,
            'secondary_id' => null,
            'detail' => $detail ?: 'Jabatan belum tersedia',
            'photo' => $photo,
            'missing' => array_values(array_keys(array_filter([
                'name' => $name === null,
                'id' => $id === null,
                'detail' => $detail === null,
                'photo' => $photo === null,
            ]))),
        ];
    }
}
