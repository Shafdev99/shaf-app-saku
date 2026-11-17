<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Ketidakhadiran extends Model
{
    use HasUuids;

    protected $table = 'ketidakhadiran';

    protected $fillable = ['guru_id', 'tanggal', 'status', 'keterangan'];

    protected $casts = ['tanggal' => 'date:Y-m-d'];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
