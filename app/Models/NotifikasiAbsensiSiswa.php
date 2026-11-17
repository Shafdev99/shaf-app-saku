<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NotifikasiAbsensiSiswa extends Model
{
    use HasUuids;

    protected $table = 'notifikasi_absensi_siswa';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'dicoba_pada' => 'datetime',
        'terkirim_pada' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
