<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pendidikan extends Model
{
    /// tabel akan terisi secara otomatis dengan data acak
    use HasFactory;

    // ID akan secara otomatis berisi kode acak
    use HasUuids;

    //Nama tabel adalah pendidikan  
    protected $table = 'pendidikan';

    // Kolom yang tidak boleh di isi
    protected $guarded = ['id'];

    public static function cekPendidikan($id){
        return RiwayatPendidikan::where('pendidikan_id', $id)->exists()
        || Ayah::where('pendidikan_ayah', $id)->exists()
        || Ibu::where('pendidikan_ibu', $id)->exists()
        || Walmur::where('pendidikan_walmur', $id)->exists();
    }
}
