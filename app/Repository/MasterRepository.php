<?php

namespace App\Repository;

use App\Models\Siswa;
use App\Models\Binduk;
use App\Models\Kurikulum;
use App\Models\KeteranganKeterima;
use App\Http\Controllers\Controller;
use App\Models\KelolaSiswa;
use App\Models\TahunAjar;
use Illuminate\Support\Facades\Storage;

class MasterRepository extends Controller
{
    public function insertSiswa($request)
    {
        // Validasi data dari form //
        $data = $request->validate([
            'no_wa_ortu'        => ['nullable', 'string', 'max:32', 'regex:/^[+0-9() .-]*$/'],
            'nama_lengkap'      => 'required',
            'tempat_lahir'      => 'required',
            'tanggal_lahir'     => 'required',
            'alamat'            => 'required',
            'agama'             => 'required',
            'nik'               => 'required|unique:siswa|min:16|max:16',
            'nisn'              => 'required|unique:siswa|min:10',
            'nis'               => 'required|unique:siswa|min:6|max:10',
            'alamat'            => 'required',
            'jenis_kelamin'     => 'required',
            'agama'             => 'required',
            'foto'              => 'image|file|max:1024|mimes:jpg,jpeg,png',
            'kelas_id'          => 'required',
            'tingkat_id'        => 'required',
            'jurusan_id'        => 'required',
            'tanggal_keterima'  => 'required',
        ], [
            'required'              => ':attribute wajib diisi!',
            'kelas_id.required'     => 'Kelas wajib diisi!',
            'jurusan_id.required'   => 'Jurusan wajib diisi!',
            'tingkat_id.required'   => 'Tingkat wajib diisi!',
            'max'                   => ':attribute diisi maksimal :max karakter!',
            'min'                   => ':attribute diisi minimal :min karakter!',
            'unique'                => ':attribute sudah digunakan!',
            'foto.max'              => 'size :attribute maksimal :max Kb!',
            'foto.mimes'            => 'format :attribute harus :values!'
        ]);


        // $data = $request->all();


        // Upload foto
        if ($request->file('foto')) {
            $data['foto'] = $request->file('foto')->store('image/siswa/awal');
        }

        $data['kurikulum_id'] = TahunAjar::select('kurikulum_id')->where('id', session('tahun_ajar_id'))->first()->kurikulum_id;

        // Include Keterangan Lulus
        $data['status'] = 'aktif';

        return Siswa::create($data);
    }

    public function updateSiswa($request, $siswa)
    {
        // Validasi data dari form //
        $request->validate([
            'no_wa_ortu'        => ['nullable', 'string', 'max:32', 'regex:/^[+0-9() .-]*$/'],
            'nama_lengkap'      => 'required',
            'tempat_lahir'      => 'required',
            'tanggal_lahir'     => 'required',
            'alamat'            => 'required',
            'agama'             => 'required',
            'nik'               => 'required|min:16|max:16|unique:siswa,nik,' . $siswa->id,
            'nisn'              => 'required|min:10|max:10|unique:siswa,nisn,' . $siswa->id,
            'nis'               => 'required|min:6|max:10|unique:siswa,nis,' . $siswa->id,
            'alamat'            => 'required',
            'jenis_kelamin'     => 'required',
            'agama'             => 'required',
            'foto'              => 'image|file|max:1024|mimes:jpg,jpeg,png',
            'kelas_id'          => 'required',
            'jurusan_id'        => 'required',
            'tingkat_id'        => 'required',
            'tanggal_keterima'  => 'required',
        ], [
            'required'              => ':attribute wajib diisi!',
            'kelas_id.required'     => 'Kelas wajib diisi!',
            'jurusan_id.required'   => 'Jurusan wajib diisi!',
            'tingkat_id.required'   => 'Tingkat wajib diisi!',
            'max'                   => ':attribute diisi maksimal :max karakter!',
            'min'                   => ':attribute diisi minimal :min karakter!',
            'unique'                => ':attribute sudah digunakan!',
            'foto.max'              => 'size :attribute maksimal :max Kb!',
            'foto.mimes'            => 'format :attribute harus :values!'
        ]);

        $data = $request->only(['nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'agama', 'nik', 'nisn', 'nis', 'jenis_kelamin', 'foto', 'nama_ayah', 'nama_ibu', 'alamat_ortu', 'nama_wali', 'alamat_wali', 'no_wa_ortu']);

        // Upload foto
        if ($request->file('foto')) {

            // Jika terdapat foto siswa tersimpan di database
            if ($siswa->foto) {

                // Maka hapus foto yang lama
                Storage::delete($siswa->foto);
            }
            $data['foto'] = $request->file('foto')->store('image/siswa/awal');
        }

        // Include Keterangan Lulus
        $data['status'] = 'aktif';

        // Update Data ke database //
        return Siswa::where('id', $siswa->id)->update($data);
    }

    // private function updateFotoSiswa($request, $siswa)
    // {
    //     return ;
    // }

    public function updateFotoProfil($foto_profil, $request)
    {
        // Maka lakukan proses dibawah ini
        // .
        // .
        // Jika foto_profil dari database sama dengan profile.jpg
        if ($foto_profil == 'image/profil/profile.jpg') {

            // Maka masukan data foto_profil baru
            return $data['foto_profil'] = $request->file('foto_profil')->store('image/profil');

            // Jika tidak
        } else {

            // Maka hapus data foto_profil lama
            Storage::delete($foto_profil);

            // Dan masukan data foto_profil baru 
            return $data['foto_profil'] = $request->file('foto_profil')->store('image/profil');
        }
    }
}
