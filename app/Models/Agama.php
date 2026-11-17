<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agama extends Model
{
    /// tabel akan terisi secara otomatis dengan data acak
    use HasFactory;

    // ID akan secara otomatis berisi kode acak
    use HasUuids;

    //Nama tabel adalah agama  
    protected $table = 'agama';

    // Kolom yang tidak boleh di isi
    protected $guarded = ['id'];

    public static function cekAgama($id)
    {
        return Siswa::where('agama', $id)->exists()
        || Pendaftar::where('agama', $id)->exists()
        || RekapSpmb::where('agama', $id)->exists()
        || Ayah::where('agama_ayah', $id)->exists()
        || Ibu::where('agama_ibu', $id)->exists()
        || Walmur::where('agama_walmur', $id)->exists();
    }
}
