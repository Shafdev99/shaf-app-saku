<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PresensiSiswa extends Model
{
    use HasUuids;

    protected $table = 'presensi_siswa';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'dikoreksi_pada' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function userPengoreksi()
    {
        return $this->belongsTo(User::class, 'dikoreksi_oleh');
    }

}
