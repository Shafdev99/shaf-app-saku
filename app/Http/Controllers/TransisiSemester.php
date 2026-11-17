<?php

namespace App\Http\Controllers;

use App\Models\KelolaSiswa;
use App\Models\TahunAjar;
use Illuminate\Support\Facades\DB;

class TransisiSemester extends Controller
{
    public function index()
    {
        if (session('semester') == 'ganjil') {
            $periode  = session('tahun_ajar');
            $semester = 'genap';
        } else {
            $pecah    = explode('/', session('tahun_ajar'));
            $periode  = $pecah[0] + 1 . '/' . $pecah[1] + 1;
            $semester = 'ganjil';
        }
        $tahunAjar = TahunAjar::select('id', 'tahun_ajar', 'semester')->where('tahun_ajar', $periode)->where('semester', $semester)->first();
        return view('transisi.index', [
            'periode' => $tahunAjar,
            'indikasi' => $tahunAjar
                ? KelolaSiswa::where('tahun_ajar_id', $tahunAjar->id)->count()
                : null,
        ]);
    }

    public function prosesTransisi($tahunAjarBaruId)
    {
        $tahunAjarTujuan = TahunAjar::query()->findOrFail($tahunAjarBaruId);
        $tahunAjarId       = session('tahun_ajar_id');

        if (KelolaSiswa::where('tahun_ajar_id', $tahunAjarBaruId)->exists()) {
            return to_route('transisi.semester')->with('gagal', 'Transisi untuk periode tujuan sudah pernah diproses. Data tidak disalin ulang.');
        }

        $kelolaSiswa = KelolaSiswa::select('siswa_id', 'kelas_id')->where('tahun_ajar_id', $tahunAjarId)->get();
        if ($kelolaSiswa->isEmpty()) {
            return to_route('transisi.semester')->with('gagal', 'Tidak ada data siswa pada periode berjalan yang dapat disalin.');
        }

        DB::transaction(function () use ($kelolaSiswa, $tahunAjarBaruId): void {
            foreach ($kelolaSiswa as $item) {
                KelolaSiswa::create([
                    'siswa_id'      => $item->siswa_id,
                    'kelas_id'      => $item->kelas_id,
                    'tahun_ajar_id' => $tahunAjarBaruId,
                ]);
            }
        });

        request()->session()->put('tahun_ajar_transisi', $tahunAjarTujuan->tahun_ajar);
        request()->session()->put('semester_transisi', $tahunAjarTujuan->semester);
        return to_route('transisi.semester')->with('sukses', 'Proses Transisi Semester berhasil!');
    }
}
