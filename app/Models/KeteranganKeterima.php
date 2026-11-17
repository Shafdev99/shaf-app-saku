<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class KeteranganKeterima extends Model
{
    //// tabel akan terisi secara otomatis dengan data acak
    use HasFactory;

    // ID akan secara otomatis berisi kode acak
    use HasUuids;

    //Nama tabel adalah keterangan keterima 
    protected $table = 'keterangan_keterima';

    // Kolom yang tidak boleh di isi
    protected $guarded = ['id'];

    public function getTanggalKeterimaAttribute($value)
    {
        return is_numeric($value) && (float) $value >= 1
            ? ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d')
            : $value;
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function scopeJurusanTerpilih(Builder $query, $pendaftarId)
    {
        return $query->where('siswa_id', $pendaftarId)
            ->join('jurusan', 'jurusan.id', '=', 'keterangan_keterima.jurusan_id')
            ->select('jurusan.nama_jurusan');
    }

    public function scopeKeteranganPendaftaran(Builder $query, $pendaftarId)
    {
        return $query->where('siswa_id', $pendaftarId)
            ->join('jurusan', 'jurusan.id', '=', 'keterangan_keterima.jurusan_id')
            ->join('tingkat', 'tingkat.id', '=', 'keterangan_keterima.tingkat_id')
            ->join('tahun_ajar', 'tahun_ajar.id', '=', 'keterangan_keterima.tahun_ajar_id')
            ->select(
                'jurusan.nama_jurusan',
                'tingkat.tingkat',
                'tahun_ajar.tahun_ajar',
                'keterangan_keterima.tanggal_keterima',
            );
    }
}
