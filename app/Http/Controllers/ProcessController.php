<?php

namespace App\Http\Controllers;

use App\Models\Aclog;
use App\Models\Kelas;
use App\Models\Alumni;
use App\Models\Tingkat;
use App\Models\RekapSpmb;
use App\Models\TahunAjar;
use App\Models\KelolaSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use App\Services\ProcessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ProcessController extends Controller
{

    //======== Terhubung ke ProcessService ========//
    protected $processService;

    public function __construct(ProcessService $processService)
    {
        $this->processService = $processService;
    }


    /** 
     * Naik Kelas
     */
    //======== Index Naik Kelas ========//
    public function naikKelas()
    {
        $kelasId = Kelas::urutanKelas()->first()?->kelas_id;

        $tahun_ajarId = session('tahun_ajar_id');

        $tahunAjarNextId = TahunAjar::where('id', $tahun_ajarId)
            ->whereNot(
                'semester',
                session('semester')
            )->first()?->id;

        $siswa = KelolaSiswa::urutanSiswa()
            ->cariDetail(
                request(['kelas', 'nama_siswa']),
                $kelasId,
                $tahun_ajarId
            );

        $tingkatSaatIni = null;
        $jurusanSaatIni = null;

        foreach ($siswa->get() as $item) {
            $tingkatSaatIni = $item?->tingkat;
            $jurusanSaatIni = $item?->nama_jurusan;
        }

        $tingkatNaikKelas = Tingkat::pluck('tingkat')->after($tingkatSaatIni);

        return view('siswa.naik-kelas', [
            'naikKelas'     => ['Naik Kelas', 'Tinggal Kelas'],
            'lulusSekolah'  => ['Lulus Sekolah', 'Tidak Lulus'],
            'kelas'         => Kelas::urutanKelas()->get(),
            'alumni'        => Alumni::urutanSiswa()
                ->cariDetail(
                    request([
                        'kelas',
                        'nama_siswa'
                    ]),
                    $kelasId,
                    $tahunAjarNextId
                )
                ->get(),
            'siswaNaik'     => KelolaSiswa::urutanSiswa()
                ->cariDetail(
                    request([
                        'kelas',
                        'nama_siswa'
                    ]),
                    $kelasId,
                    $tahunAjarNextId
                )
                ->get(),
            'siswa'         => $siswa->paginate(10)->withQueryString(),
            'pilihKelas'    => Kelas::tampilKelas()
                ->where('nama_jurusan', $jurusanSaatIni)
                ->where('tingkat', $tingkatNaikKelas)
                ->orderBy('tingkat', 'asc')
                ->orderBy('nama_jurusan', 'asc')
                ->orderBy('nama_kelas', 'asc')
                ->get()
        ]);
    }

    //======== Proses Naik Kelas ========//
    public function prosesNaikKelas(Request $request)
    {
        $siswaInput = $request->input('siswa_id', []);
        $jumlahSiswa = is_array($siswaInput) ? count($siswaInput) : 0;
        $data = $request->validate([
            'siswa_id' => ['required', 'array', 'min:1'],
            'siswa_id.*' => ['required', 'uuid', 'exists:siswa,id'],
            'kelas_saat_ini' => ['required', 'array', 'size:' . $jumlahSiswa],
            'kelas_saat_ini.*' => ['required', 'uuid', 'exists:kelas,id'],
            'status_kenaikan_kelas' => ['required', 'array', 'size:' . $jumlahSiswa],
            'status_kenaikan_kelas.*' => ['required', 'in:Naik Kelas,Tinggal Kelas,Lulus Sekolah,Tidak Lulus'],
            'kelas_id' => ['nullable', 'uuid', 'exists:kelas,id'],
        ], [
            'siswa_id.required' => 'Pilih minimal satu siswa untuk diproses.',
            'siswa_id.min' => 'Pilih minimal satu siswa untuk diproses.',
            'siswa_id.*.exists' => 'Salah satu siswa yang dipilih sudah tidak tersedia.',
            'kelas_saat_ini.*.exists' => 'Kelas asal salah satu siswa sudah tidak tersedia. Muat ulang halaman lalu coba lagi.',
            'status_kenaikan_kelas.*.in' => 'Status kenaikan kelas tidak dikenali. Muat ulang halaman lalu coba lagi.',
            'kelas_id.exists' => 'Kelas tujuan sudah tidak tersedia. Pilih kelas tujuan kembali.',
        ]);

        $semesterTujuan = session('semester') === 'ganjil' ? 'genap' : 'ganjil';
        $tahunAjarIdTujuan = TahunAjar::query()
            ->where('tahun_ajar', session('tahun_ajar'))
            ->where('semester', $semesterTujuan)
            ->value('id');

        if (!$tahunAjarIdTujuan) {
            return back()->withInput()->withErrors([
                'kelas_id' => "Data tahun ajar semester {$semesterTujuan} untuk tahun " . session('tahun_ajar') . ' belum tersedia. Tambahkan periode tersebut sebelum memproses kenaikan kelas.',
            ]);
        }

        $siswaIds = $data['siswa_id'];
        $kelasSaatIni = $data['kelas_saat_ini'];
        $statusKenaikan = $data['status_kenaikan_kelas'];

        DB::transaction(function () use ($data, $tahunAjarIdTujuan, $siswaIds, $kelasSaatIni, $statusKenaikan, $jumlahSiswa): void {
            if ($data['kelas_id'] ?? null) {
                for ($i = 0; $i < $jumlahSiswa; $i++) {
                    if ($statusKenaikan[$i] == 'Naik Kelas') {
                        KelolaSiswa::updateOrCreate([
                            'siswa_id' => $siswaIds[$i],
                            'tahun_ajar_id' => $tahunAjarIdTujuan,
                        ], [
                            'kelas_id' => $data['kelas_id'],
                        ]);
                    } else {
                        KelolaSiswa::updateOrCreate([
                            'siswa_id' => $siswaIds[$i],
                            'tahun_ajar_id' => $tahunAjarIdTujuan,
                        ], [
                            'kelas_id' => $kelasSaatIni[$i],
                        ]);
                    }
                }
            } else {
                for ($i = 0; $i < $jumlahSiswa; $i++) {
                    if ($statusKenaikan[$i] == 'Lulus Sekolah') {
                        Alumni::updateOrCreate([
                            'siswa_id'      => $siswaIds[$i],
                            'tahun_ajar_id' => session('tahun_ajar_id'),
                        ], [
                            'kelas_id'      => $kelasSaatIni[$i],
                        ]);

                        Siswa::where('id', $siswaIds[$i])->update([
                            'status' => 'alumni'
                        ]);
                    } else {
                        KelolaSiswa::updateOrCreate([
                            'siswa_id'      => $siswaIds[$i],
                            'tahun_ajar_id' => $tahunAjarIdTujuan,
                        ], [
                            'kelas_id'      => $kelasSaatIni[$i],
                        ]);
                    }
                }
            }
        });

        $url = 'kelas=' . $request->input('kelas') . '&nama_siswa=' . $request->input('nama_siswa');

        // Alihkan ke halamam siswa //
        return redirect()->route('siswa.naik', $url)->with('sukses', 'Perubahan naik kelas atau lulus sekolah berhasil disimpan!');
    }


    /** 
     * Activity Log 
     */
    //======== View Aclog ========//
    public function activityLog()
    {
        if (Auth::user()->role == 'admin') {
            $aclog = Aclog::cari(request(['keyword']))->latest()->paginate(10);
        } else {
            $aclog = Aclog::whereRelation('user', 'role', 'user')->cari(request(['keyword']))->latest()->paginate(10);
        }

        return view('aclog.index', [
            'aclog' => $aclog
        ]);
    }
}
