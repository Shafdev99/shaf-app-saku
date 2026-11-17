<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasUuids;

    protected $table = 'pengaturan_absensi';

    protected $fillable = [
        'jam_masuk',
        'toleransi_terlambat',
        'jam_pulang',
        'batas_jam_pulang',
        'hari_kerja',
        'jam_masuk_siswa',
        'batas_scan_masuk_siswa',
        'jam_pulang_siswa',
        'batas_jam_pulang_siswa',
        'waktu_notifikasi_siswa',
        'jeda_alpa_siswa',
    ];

    protected $casts = [
        'toleransi_terlambat' => 'integer',
        'hari_kerja' => 'array',
        'jeda_alpa_siswa' => 'integer',
    ];

    public function getJamMasukSiswaAttribute($value): string
    {
        return $value ?: '06:00:00';
    }

    public function getBatasScanMasukSiswaAttribute($value): string
    {
        return $value ?: '07:00:00';
    }

    public function getJamPulangSiswaAttribute($value): string
    {
        return $value ?: '13:00:00';
    }

    public function getBatasJamPulangSiswaAttribute($value): string
    {
        return $value ?: '17:00:00';
    }

    public function getWaktuNotifikasiSiswaAttribute($value): string
    {
        return $value ?: '07:00:00';
    }

    public function getJedaAlpaSiswaAttribute($value): int
    {
        return (int) ($value ?? 3);
    }
}
