<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasUuids;

    protected $table = 'presensi';

    protected $fillable = [
        'guru_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
