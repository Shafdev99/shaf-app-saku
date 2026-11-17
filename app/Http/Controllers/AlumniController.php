<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\TahunAjar;

class AlumniController extends Controller
{
    public function index()
    {
        $kelas = Alumni::kelas()->get();
        $tahunAjar = TahunAjar::query()
            ->select('id', 'tahun_ajar')
            ->where('semester', 'genap')
            ->latest('tahun_ajar')
            ->get();

        $tahunAjarId = request('tahun_ajar_id', $tahunAjar->first()?->id);
        if ($tahunAjarId && !$tahunAjar->contains('id', $tahunAjarId)) {
            $tahunAjarId = $tahunAjar->first()?->id;
        }

        $kelasId = request('kelas_id');
        if ($kelasId && !$kelas->contains('id', $kelasId)) {
            $kelasId = null;
        }

        $alumni = Alumni::urutanSiswa()
            ->when($tahunAjarId, fn ($query) => $query->where('alumni.tahun_ajar_id', $tahunAjarId))
            ->when($kelasId, fn ($query) => $query->where('alumni.kelas_id', $kelasId))
            ->when(request('nama_alumni'), fn ($query, $nama) => $query->where('siswa.nama_lengkap', 'like', '%' . $nama . '%'));

        return view('alumni.index', [
            'kelas'       => $kelas,
            'tahunAjar'   => $tahunAjar,
            'tahunAjarId' => $tahunAjarId,
            'kelasId'     => $kelasId,
            'alumni'      => $alumni->paginate(10)->withQueryString(),
        ]);
    }
}
