<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    use HasUuids;

    protected $table = 'hari_libur';

    protected $fillable = ['tanggal', 'keterangan'];

    protected $casts = ['tanggal' => 'date:Y-m-d'];
}
