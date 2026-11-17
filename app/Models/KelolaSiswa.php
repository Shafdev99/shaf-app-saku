<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KelolaSiswa extends Model
{
    // tabel akan terisi secara otomatis dengan data acak
    use HasFactory;

    // ID akan secara otomatis berisi kode acak
    use HasUuids;

    // Kolom yang tidak boleh terisi
    protected $guarded = ['id'];

    protected $table = 'kelola_siswa';

    public function scopeUrutanSiswa(Builder $query)
    {
        $query->select(
            'siswa.id',
            'siswa.foto',
            'siswa.nama_lengkap',
            'siswa.nisn',
            'siswa.nis',
            'siswa.jenis_kelamin',
            'siswa.status',
            'kelas.nama_kelas',
            'jurusan.nama_jurusan',
            'tingkat.tingkat',
            'kelola_siswa.kelas_id',
        )
            ->join('siswa', 'siswa.id', '=', 'kelola_siswa.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'kelola_siswa.kelas_id')
            ->join('jurusan', 'jurusan.id', '=', 'kelas.jurusan_id')
            ->join('tingkat', 'tingkat.id', '=', 'kelas.tingkat_id');
    }

    public function scopeExportSiswa(Builder $query)
    {
        return $query->select(
            'siswa.nama_lengkap',
            'siswa.tanggal_lahir',
            'siswa.tempat_lahir',
            'siswa.nisn',
            'siswa.nik',
            'siswa.nis',
            'siswa.alamat',
            'siswa.jenis_kelamin',
            \Illuminate\Support\Facades\DB::raw('COALESCE(agama.agama, siswa.agama) as agama'),
            'ayah.nama_ayah',
            'ibu.nama_ibu',
            'ayah.alamat_ayah',
            'walmur.nama_walmur as nama_wali',
            'walmur.alamat_walmur as alamat_wali',
            'pendidikan.pendidikan as tingkat_pendidikan',
            'riwayat_pendidikan.nama_sekolah as nama_sekolah_asal',
            'riwayat_pendidikan.alamat_sekolah as alamat_sekolah_asal',
            'riwayat_pendidikan.tahun_lulus',
            'riwayat_pendidikan.no_ijazah as nomer_ijazah',
            'riwayat_pendidikan.tanggal_ijazah',
            'riwayat_pendidikan.lama_belajar',
            'tingkat.tingkat as tingkat_keterima',
            'kelas.nama_kelas as kelas_keterima',
            'jurusan.nama_jurusan as jurusan_keterima',
            'keterangan_keterima.tanggal_keterima'
        )
            ->join('siswa', 'siswa.id', '=', 'kelola_siswa.siswa_id')
            ->leftJoin('walmur', 'walmur.siswa_id', '=', 'siswa.id')
            ->leftJoin('ayah', 'ayah.siswa_id', '=', 'siswa.id')
            ->leftJoin('ibu', 'ibu.siswa_id', '=', 'siswa.id')
            ->leftJoin('keterangan_keterima', 'keterangan_keterima.siswa_id', '=', 'kelola_siswa.siswa_id')
            ->leftJoin('agama', 'agama.id', '=', 'siswa.agama')
            ->leftJoin('riwayat_pendidikan', 'riwayat_pendidikan.siswa_id', '=', 'siswa.id')
            ->leftJoin('pendidikan', 'pendidikan.id', '=', 'riwayat_pendidikan.pendidikan_id')
            ->join('kelas', 'kelas.id', '=', 'kelola_siswa.kelas_id')
            ->join('jurusan', 'jurusan.id', '=', 'kelas.jurusan_id')
            ->join('tingkat', 'tingkat.id', '=', 'kelas.tingkat_id');
    }

    public function scopeCariDetail(Builder $query, $filter = [], $kelasId = null, $tahunAjar = null)
    {
        $kelas      = $filter['kelas'] ?? $kelasId;
        $siswa      = $filter['nama_siswa'] ?? false;

        if ($kelas && $siswa) {
            return $query
                ->where('kelas.id', $kelas)
                ->where('siswa.nama_lengkap', 'like', '%' . $siswa . '%')
                ->where('tahun_ajar_id', $tahunAjar);
        } else {
            return $query
                ->where('kelas.id', $kelas)
                ->where('tahun_ajar_id', $tahunAjar);
        }
    }
}
