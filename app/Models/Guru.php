<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guru extends Model
{
    /// tabel akan terisi secara otomatis dengan data acak
    use HasFactory;

    // ID akan secara otomatis berisi kode acak
    use HasUuids;

    //Nama tabel adalah guru
    protected $table = 'guru';

    // Kolom yang tidak boleh di isi
    protected $fillable = ['card_id', 'nama_guru', 'nip', 'jabatan', 'status_guru', 'foto_pegawai', 'status_akun', 'no_hp'];


    public function ScopeUserGuru(Builder $query)
    {
        return $query->select(
            'guru.*',
            'user_guru.user_id',
            'users.username',
            'users.email',
            'users.role',
            'users.status',
            'users.request',
            'users.created_at as user_created_at'
        )->join('user_guru', 'user_guru.guru_id', '=', 'guru.id')
            ->join('users', 'users.id', '=', 'user_guru.user_id')
            ->where('role', 'guru')
            ->orderByDesc('users.created_at');
    }

    public function ScopeUserStaff(Builder $query)
    {
        return $query->select(
            'guru.*',
            'user_staff.user_id',
            'users.username',
            'users.email',
            'users.role',
            'users.status',
            'users.request',
            'users.created_at as user_created_at'
        )->join('user_staff', 'user_staff.staff_id', '=', 'guru.id')
            ->join('users', 'users.id', '=', 'user_staff.user_id')
            ->where('jabatan', 'Staff')
            ->orderByDesc('users.created_at');
    }

    public function ScopeUserKepsek(Builder $query)
    {
        return $query->select(
            'guru.*',
            'user_kepsek.user_id',
            'users.username',
            'users.email',
            'users.role',
            'users.status',
            'users.request',
            'users.created_at as user_created_at'
        )->join('user_kepsek', 'user_kepsek.kepsek_id', '=', 'guru.id')
            ->join('users', 'users.id', '=', 'user_kepsek.user_id')
            ->where('jabatan', 'Kepala Sekolah')
            ->orderByDesc('users.created_at');
    }

    public function scopeGetPegawai(Builder $query, $keyword)
    {

        return $query->select(
            'guru.id',
            'guru.card_id',
            'guru.nama_guru',
            'guru.jabatan',
            'guru.nip',
            'guru.no_hp',
            'guru.status_guru',
            'status_pegawai.status_pegawai',
            'guru.foto_pegawai',
            'guru.status_akun'
        )
            ->join(
                'status_pegawai',
                'status_pegawai.id',
                '=',
                'guru.status_guru'
            )
            ->orderBy(
                'guru.nama_guru',
                'asc'
            )
            ->when(
                $keyword['nama_pegawai'] ?? false,
                function ($query, $search) {
                    return $query->where('guru.nama_guru', 'like', '%' . $search . '%');
                }
            )
            ->when(
                ($keyword['status_kartu'] ?? null) === 'Sudah Tertaut',
                function ($query) {
                    return $query->whereNotNull('guru.card_id');
                }
            )
            ->when(
                ($keyword['status_kartu'] ?? null) === 'Belum Tertaut',
                function ($query) {
                    return $query->whereNull('guru.card_id');
                }
            );
    }

    public function scopeCari(Builder $query, $filter = [])
    {
        $query->when($filter['keyword'] ?? false, function ($query, $search) {
            return $query->where('nama_guru', 'like', '%' . $search . '%');
        });
    }

    public static function cekValidasiGuru($guruId)
    {
        return Walkel::where('guru_id', $guruId)->exists();
    }

    public static function cekValidasiGuruArr($arrId)
    {
        return Walkel::whereIn('guru_id', $arrId)->exists();
    }

    public function namaJabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan', 'id');
    }

    public function statusPegawai()
    {
        return $this->belongsTo(StatusPegawai::class, 'status_guru', 'id');
    }
}
