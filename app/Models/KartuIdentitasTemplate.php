<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuIdentitasTemplate extends Model
{
    protected $table = 'kartu_identitas_templates';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }
}
