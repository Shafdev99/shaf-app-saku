<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    /// tabel akan terisi secara otomatis dengan data acak
    use HasFactory;

    // ID akan secara otomatis berisi kode acak
    use HasUuids;

    //Nama tabel adalah kurikulum 
    protected $table = 'kurikulum';

    // Kolom yang tidak boleh di isi
    protected $guarded = ['id'];

    public static function cekKurikulum($id): bool
    {
        return KurikulumMapel::where('kurikulum_id', $id)->exists()
        || TahunAjar::where('kurikulum_id', $id)->exists() 
        || Siswa::where('kurikulum_id', $id)->exists()
        || Pendaftar::where('kurikulum_id', $id)->exists()
        || RekapSpmb::where('kurikulum_id', $id)->exists();

    }
}
