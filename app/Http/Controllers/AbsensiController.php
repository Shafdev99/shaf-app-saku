<?php

namespace App\Http\Controllers;

use App\Exports\PresensiExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\Kelas;
use App\Models\KelolaSiswa;
use App\Models\Ketidakhadiran;
use App\Models\Pendidikan;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\KetidakhadiranSiswa;
use App\Models\PresensiSiswa;
use App\Services\StudentAttendanceService;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    public function masterAbsensi()
    {
        $kelas = Kelas::urutanKelas();

        if (request('kelas') == NULL) {
            $tingkatDefault = Kelas::urutanKelas()->get()->first()->tingkat;
        } else {
            $tingkatDefault = Kelas::urutanKelas()->where('kelas_id', request('kelas'))->first()->tingkat;
        }


        $tahunAjarId = session('tahun_ajar_id');

        return view('absensi.master-absensi', [
            'pindah' => $tingkatDefault,
            'kelas' => $kelas->get(),
            'siswa' => KelolaSiswa::urutanSiswa()
                ->leftJoin('siswa as siswa_kartu', 'siswa_kartu.id', '=', 'kelola_siswa.siswa_id')
                ->addSelect('siswa_kartu.card_id as card_id', 'siswa_kartu.no_wa_ortu as no_wa_ortu')
                ->cariDetail(request(['kelas', 'nama_siswa']), $kelas->first()?->kelas_id, $tahunAjarId)
                ->paginate(10)
                ->withQueryString(),
            'pindahKelas' => Kelas::daftarKelas(),
            'pendidikan' => Pendidikan::select('pendidikan', 'ket_pendidikan')->get(),
        ]);
    }

    public function masterAdd($siswaId)
    {
        return view('absensi.master-add', [
            'siswa'     => KelolaSiswa::urutanSiswa()->where('siswa.id', $siswaId)->first(),
        ]);
    }

    public function aturAbsensi()
    {
        $pengaturan = $this->ambilPengaturan();

        return view('absensi.atur-absensi', [
            'pengaturan' => $pengaturan,
            'hariLibur' => HariLibur::orderBy('tanggal')->get(),
        ]);
    }

    public function scanView()
    {
        $pengaturan = $this->ambilPengaturan();

        return view('absensi.scan', compact('pengaturan'));
    }

    public function updateAturAbsensi(Request $request)
    {
        $data = $request->validate([
            'jam_masuk' => ['required', 'date_format:H:i'],
            'toleransi_terlambat' => ['required', 'integer', 'min:0'],
            'jam_pulang' => ['required', 'date_format:H:i'],
            'batas_jam_pulang' => ['required', 'date_format:H:i'],
            'hari_kerja' => ['nullable', 'array'],
            'hari_kerja.*' => ['integer', 'between:1,7'],
            'jam_masuk_siswa' => ['required', 'date_format:H:i'],
            'batas_scan_masuk_siswa' => ['required', 'date_format:H:i'],
            'jam_pulang_siswa' => ['required', 'date_format:H:i'],
            'batas_jam_pulang_siswa' => ['required', 'date_format:H:i'],
            'waktu_notifikasi_siswa' => ['required', 'date_format:H:i'],
            'jeda_alpa_siswa' => ['required', 'integer', 'min:0', 'max:12'],
        ]);

        $guruData = array_intersect_key($data, array_flip(['jam_masuk', 'toleransi_terlambat', 'jam_pulang', 'batas_jam_pulang', 'hari_kerja']));
        $siswaData = array_intersect_key($data, array_flip(['jam_masuk_siswa', 'batas_scan_masuk_siswa', 'jam_pulang_siswa', 'batas_jam_pulang_siswa', 'waktu_notifikasi_siswa', 'jeda_alpa_siswa']));

        foreach ($data['hari_kerja'] ?? [] as $hari) {
            if (! in_array((int) $hari, [1, 2, 3, 4, 5, 6, 7], true)) {
                throw ValidationException::withMessages(['hari_kerja' => 'Pilih hari kerja yang valid.']);
            }
        }

        $jamMasuk = $this->waktuKeDetik($data['jam_masuk']);
        $jamPulang = $this->waktuKeDetik($data['jam_pulang']);
        $batasJamPulang = $this->waktuKeDetik($data['batas_jam_pulang']);

        if ($jamPulang <= $jamMasuk) {
            throw ValidationException::withMessages([
                'jam_pulang' => 'Jam pulang harus lebih besar dari jam masuk.',
            ]);
        }

        if ($batasJamPulang < $jamPulang) {
            throw ValidationException::withMessages([
                'batas_jam_pulang' => 'Batas jam pulang harus sama atau lebih besar dari jam pulang.',
            ]);
        }

        if ($this->waktuKeDetik($data['batas_scan_masuk_siswa']) < $this->waktuKeDetik($data['jam_masuk_siswa'])) {
            throw ValidationException::withMessages(['batas_scan_masuk_siswa' => 'Batas scan masuk siswa tidak boleh lebih awal dari jam mulai scan.']);
        }
        if ($this->waktuKeDetik($data['jam_pulang_siswa']) <= $this->waktuKeDetik($data['batas_scan_masuk_siswa'])) {
            throw ValidationException::withMessages(['jam_pulang_siswa' => 'Jam pulang siswa harus setelah batas scan masuk.']);
        }
        if ($this->waktuKeDetik($data['batas_jam_pulang_siswa']) < $this->waktuKeDetik($data['jam_pulang_siswa'])) {
            throw ValidationException::withMessages(['batas_jam_pulang_siswa' => 'Batas pulang siswa harus sama atau setelah jam pulang.']);
        }
        if ($this->waktuKeDetik($data['waktu_notifikasi_siswa']) < $this->waktuKeDetik($data['batas_scan_masuk_siswa'])) {
            throw ValidationException::withMessages(['waktu_notifikasi_siswa' => 'Notifikasi siswa tidak boleh dijadwalkan sebelum batas scan masuk.']);
        }
        if ($this->waktuKeDetik($data['jam_pulang_siswa']) <= $this->waktuKeDetik($data['jam_masuk_siswa'])) {
            throw ValidationException::withMessages(['jam_pulang_siswa' => 'Jam pulang siswa harus setelah jam mulai scan masuk.']);
        }

        $guruData['hari_kerja'] = array_map('intval', $data['hari_kerja'] ?? []);
        $this->ambilPengaturan()->update([...$guruData, ...$siswaData]);

        return to_route('atur.absensi')->with('sukses', 'Pengaturan waktu absensi berhasil disimpan!');
    }

    // public function scan(Request $request, WhatsappService $whatsappService)
    // {
    //     $cardId = trim((string) $request->input('card_id', ''));

    //     if ($cardId === '') {
    //         throw ValidationException::withMessages([
    //             'card_id' => 'ID kartu RFID wajib diisi.',
    //         ]);
    //     }

    //     $guru = Guru::query()->where('card_id', $cardId)->first();

    //     if (!$guru) {
    //         return response()->json([
    //             'message' => 'Kartu RFID tidak terdaftar.',
    //         ], 404);
    //     }

    //     if ($guru->status_akun !== 'Aktif') {
    //         return response()->json([
    //             'message' => 'Guru tidak aktif dan tidak dapat melakukan presensi.',
    //         ], 403);
    //     }

    //     $pengaturan = $this->ambilPengaturan();

    //     $now = now();
    //     $tanggal = $now->toDateString();

    //     if (!$this->isHariKerja($now, $pengaturan)) {
    //         return response()->json(['message' => 'Hari ini bukan hari absensi.'], 422);
    //     }
    //     $presensi = Presensi::where('guru_id', $guru->id)
    //         ->where('tanggal', $tanggal)
    //         ->first();

    //     $ketidakhadiran = Ketidakhadiran::where('guru_id', $guru->id)
    //         ->where('tanggal', $tanggal)
    //         ->first();

    //     if ($ketidakhadiran) {
    //         return response()->json([
    //             'message' => "Guru sudah memiliki status {$ketidakhadiran->status} pada tanggal ini.",
    //         ], 422);
    //     }

    //     if ($presensi?->jam_pulang !== null) {
    //         return response()->json([
    //             'message' => 'Presensi pulang sudah tercatat hari ini.',
    //             'data' => $this->formatFeedback($guru, $presensi),
    //         ]);
    //     }

    //     if ($presensi) {
    //         $jamPulang = $this->waktuHariIni($tanggal, $pengaturan->jam_pulang);
    //         $batasJamPulang = $this->waktuHariIni($tanggal, $pengaturan->batas_jam_pulang);

    //         $statusPulang = $this->tentukanStatusPulang($now, $jamPulang, $batasJamPulang);

    //         if ($statusPulang === 'TERLALU_AWAL') {
    //             return response()->json([
    //                 'message' => 'Belum waktunya presensi pulang.',
    //                 'jam_pulang' => $jamPulang->format('H:i'),
    //             ], 422);
    //         }

    //         if ($statusPulang === 'SUDAH_BERAKHIR') {
    //             return response()->json([
    //                 'message' => 'Waktu presensi pulang telah berakhir.',
    //                 'batas_jam_pulang' => $batasJamPulang->format('H:i'),
    //             ], 422);
    //         }

    //         [$presensi, $pulangBaru] = DB::transaction(function () use ($guru, $tanggal, $now) {
    //             $presensi = Presensi::where('guru_id', $guru->id)
    //                 ->where('tanggal', $tanggal)
    //                 ->lockForUpdate()
    //                 ->first();

    //             $pulangBaru = false;
    //             if ($presensi->jam_pulang === null) {
    //                 $presensi->update(['jam_pulang' => $now->format('H:i:s')]);
    //                 $pulangBaru = true;
    //             }

    //             return [$presensi->refresh(), $pulangBaru];
    //         });

    //         return response()->json([
    //             'message' => $pulangBaru
    //                 ? 'Presensi pulang berhasil.'
    //                 : 'Presensi pulang sudah tercatat hari ini.',
    //             'data' => $this->formatFeedback($guru, $presensi),
    //         ]);
    //     }

    //     $jamMasuk = $this->waktuHariIni($tanggal, $pengaturan->jam_masuk);

    //     $status = $this->tentukanStatus($now, $jamMasuk, $pengaturan->toleransi_terlambat);
    //     if ($status === null) {
    //         return response()->json([
    //             'message' => 'Belum waktunya presensi masuk.',
    //         ], 422);
    //     }

    //     try {
    //         $presensi = DB::transaction(function () use ($guru, $tanggal, $now, $status) {
    //             return Presensi::firstOrCreate(
    //                 ['guru_id' => $guru->id, 'tanggal' => $tanggal],
    //                 ['jam_masuk' => $now->format('H:i:s'), 'status' => $status]
    //             );
    //         });
    //     } catch (QueryException $exception) {
    //         $presensi = Presensi::where('guru_id', $guru->id)
    //             ->where('tanggal', $tanggal)
    //             ->first();

    //         if (!$presensi) {
    //             Log::error('Gagal menyimpan presensi RFID.', [
    //                 'guru_id' => $guru->id,
    //                 'tanggal' => $tanggal,
    //                 'exception' => $exception->getMessage(),
    //             ]);

    //             return response()->json([
    //                 'message' => 'Presensi gagal disimpan. Silakan coba lagi.',
    //             ], 500);
    //         }
    //     }

    //     // TRIGGER: Kirim WhatsApp Masuk jika data baru berhasil dibuat dan nomor HP tersedia
    //     if ($presensi->wasRecentlyCreated && !empty($guru->no_hp)) {
    //         $whatsappService->kirimNotifikasiPresensi(
    //             $guru->no_hp,
    //             $guru->nama_guru,
    //             'masuk',
    //             Carbon::parse($presensi->jam_masuk)->format('H:i'),
    //             $presensi->status // Membawa status (misal: TEPAT_WAKTU / TERLAMBAT)
    //         );
    //     }

    //     $statusCode = $presensi->wasRecentlyCreated ? 201 : 200;

    //     return response()->json([
    //         'message' => $presensi->wasRecentlyCreated
    //             ? 'Presensi berhasil.'
    //             : 'Presensi masuk hari ini sudah tercatat.',
    //         'data' => $this->formatFeedback($guru, $presensi),
    //     ], $statusCode);
    // }

    public function scan(Request $request, WhatsappService $whatsappService, StudentAttendanceService $studentAttendance)
    {

        // 1. Ambil dan validasi Card ID dari RFID
        $cardId = trim((string) $request->input('card_id', ''));

        if ($cardId === '') {
            throw ValidationException::withMessages([
                'card_id' => 'ID kartu RFID wajib diisi.',
            ]);
        }

        // QR kartu pelajar berisi URL detail bertanda tangan. Pemindai aplikasi
        // mengekstrak ID siswa dari URL tersebut; RFID tetap menggunakan ID kartu.
        $qrStudent = null;
        if (filter_var($cardId, FILTER_VALIDATE_URL)) {
            $qrRequest = Request::create($cardId);
            if (
                URL::hasValidSignature($qrRequest)
                && preg_match('#^/kartu-pelajar/([0-9a-f-]{36})$#i', $qrRequest->getPathInfo(), $matches)
            ) {
                $qrStudent = Siswa::query()->whereKey($matches[1])->first();
            }
        }

        // Student cards are routed to their separate attendance table; the teacher flow below remains intact.
        $cardOwner = $qrStudent ? null : DB::table('rfid_cards')->where('card_id', $cardId)->first();
        $siswa = $qrStudent ?? ($cardOwner?->entitas === 'siswa' ? Siswa::query()->whereKey($cardOwner->entitas_id)->first() : null);
        if ($siswa) {
            $aktifDiKelas = DB::table('kelola_siswa')->where('siswa_id', $siswa->id)->where('tahun_ajar_id', session('tahun_ajar_id'))->exists();
            if (strtolower((string) $siswa->status) !== 'aktif' || ! $aktifDiKelas) {
                return response()->json(['message' => 'Siswa tidak aktif dan tidak dapat melakukan presensi.'], 403);
            }

            $result = $studentAttendance->scan($siswa, now(), $this->ambilPengaturan());
            if (isset($result['error'])) {
                return response()->json(['message' => $result['error']], $result['code']);
            }

            $record = $result['attendance'];
            $event = $result['event'];
            $message = match ($event) {
                'masuk' => 'Presensi masuk siswa berhasil.',
                'pulang' => 'Presensi pulang siswa berhasil.',
                default => $record->jam_pulang ? 'Presensi pulang sudah tercatat hari ini.' : 'Presensi masuk hari ini sudah tercatat.',
            };

            return response()->json([
                'message' => $message,
                'data' => [
                    'id_siswa' => $siswa->id,
                    'nama' => $siswa->nama_lengkap,
                    'foto' => $siswa->foto,
                    'jam' => substr($record->jam_masuk ?? '', 0, 5),
                    'jam_masuk' => $record->jam_masuk ? substr($record->jam_masuk, 0, 5) : null,
                    'jam_pulang' => $record->jam_pulang ? substr($record->jam_pulang, 0, 5) : null,
                    'status' => $record->status,
                ],
            ], $event === 'masuk' && $result['created'] ? 201 : 200);
        }

        // 2. Cari data guru berdasarkan kartu
        $guru = $cardOwner?->entitas === 'guru'
            ? Guru::query()->whereKey($cardOwner->entitas_id)->first()
            : null;
        $guru ??= $cardOwner === null ? Guru::query()->where('card_id', $cardId)->first() : null;
        if (! $cardOwner && $guru) {
            DB::table('rfid_cards')->insertOrIgnore([
                'card_id' => $cardId, 'entitas' => 'guru', 'entitas_id' => $guru->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        if (!$guru) {
            return response()->json([
                'message' => 'Kartu RFID tidak terdaftar.',
            ], 404);
        }

        if ($guru->status_akun !== 'Aktif') {
            return response()->json([
                'message' => 'Guru tidak aktif dan tidak dapat melakukan presensi.',
            ], 403);
        }

        $pengaturan = $this->ambilPengaturan();
        $now = now();
        $tanggal = $now->toDateString();

        if (!$this->isHariKerja($now, $pengaturan)) {
            return response()->json(['message' => 'Hari ini bukan hari absensi.'], 422);
        }

        $presensi = Presensi::where('guru_id', $guru->id)
            ->where('tanggal', $tanggal)
            ->first();

        $ketidakhadiran = Ketidakhadiran::where('guru_id', $guru->id)
            ->where('tanggal', $tanggal)
            ->first();

        if ($ketidakhadiran) {
            return response()->json([
                'message' => "Guru sudah memiliki status {$ketidakhadiran->status} pada tanggal ini.",
            ], 422);
        }

        if ($presensi?->jam_pulang !== null) {
            return response()->json([
                'message' => 'Presensi pulang sudah tercatat hari ini.',
                'data' => $this->formatFeedback($guru, $presensi),
            ]);
        }

        // ==========================================
        // PROSES PRESENSI PULANG
        // ==========================================
        if ($presensi) {
            $jamPulang = $this->waktuHariIni($tanggal, $pengaturan->jam_pulang);
            $batasJamPulang = $this->waktuHariIni($tanggal, $pengaturan->batas_jam_pulang);

            $statusPulang = $this->tentukanStatusPulang($now, $jamPulang, $batasJamPulang);

            if ($statusPulang === 'TERLALU_AWAL') {
                return response()->json([
                    'message' => 'Belum waktunya presensi pulang.',
                    'jam_pulang' => $jamPulang->format('H:i'),
                ], 422);
            }

            if ($statusPulang === 'SUDAH_BERAKHIR') {
                return response()->json([
                    'message' => 'Waktu presensi pulang telah berakhir.',
                    'batas_jam_pulang' => $batasJamPulang->format('H:i'),
                ], 422);
            }

            [$presensi, $pulangBaru] = DB::transaction(function () use ($guru, $tanggal, $now) {
                $presensi = Presensi::where('guru_id', $guru->id)
                    ->where('tanggal', $tanggal)
                    ->lockForUpdate()
                    ->first();

                $pulangBaru = false;
                if ($presensi->jam_pulang === null) {
                    $presensi->update(['jam_pulang' => $now->format('H:i:s')]);
                    $pulangBaru = true;
                }

                return [$presensi->refresh(), $pulangBaru];
            });

            // TRIGGER WA PULANG: Dikirim jika sukses catat pulang baru & nomor HP ada
            if ($pulangBaru && !empty($guru->no_hp)) {
                $whatsappService->kirimNotifikasiPresensi(
                    $guru->no_hp,
                    $guru->nama_guru,
                    'pulang',
                    Carbon::parse($presensi->jam_pulang)->format('H:i')
                );
            }

            return response()->json([
                'message' => $pulangBaru
                    ? 'Presensi pulang berhasil.'
                    : 'Presensi pulang sudah tercatat hari ini.',
                'data' => $this->formatFeedback($guru, $presensi),
            ]);
        }

        // ==========================================
        // PROSES PRESENSI MASUK
        // ==========================================
        $jamMasuk = $this->waktuHariIni($tanggal, $pengaturan->jam_masuk);

        $status = $this->tentukanStatus($now, $jamMasuk, $pengaturan->toleransi_terlambat);
        if ($status === null) {
            return response()->json([
                'message' => 'Belum waktunya presensi masuk.',
            ], 422);
        }

        try {
            $presensi = DB::transaction(function () use ($guru, $tanggal, $now, $status) {
                return Presensi::firstOrCreate(
                    ['guru_id' => $guru->id, 'tanggal' => $tanggal],
                    ['jam_masuk' => $now->format('H:i:s'), 'status' => $status]
                );
            });
        } catch (QueryException $exception) {
            $presensi = Presensi::where('guru_id', $guru->id)
                ->where('tanggal', $tanggal)
                ->first();

            if (!$presensi) {
                Log::error('Gagal menyimpan presensi RFID.', [
                    'guru_id' => $guru->id,
                    'tanggal' => $tanggal,
                    'exception' => $exception->getMessage(),
                ]);

                return response()->json([
                    'message' => 'Presensi gagal disimpan. Silakan coba lagi.',
                ], 500);
            }
        }

        // TRIGGER WA MASUK: Dikirim jika sukses buat data masuk baru & nomor HP ada
        if ($presensi->wasRecentlyCreated && !empty($guru->no_hp)) {
            $whatsappService->kirimNotifikasiPresensi(
                $guru->no_hp,
                $guru->nama_guru,
                'masuk',
                Carbon::parse($presensi->jam_masuk)->format('H:i'),
                $presensi->status
            );
        }

        $statusCode = $presensi->wasRecentlyCreated ? 201 : 200;

        return response()->json([
            'message' => $presensi->wasRecentlyCreated
                ? 'Presensi berhasil.'
                : 'Presensi masuk hari ini sudah tercatat.',
            'data' => $this->formatFeedback($guru, $presensi),
        ], $statusCode);
    }


    public function tentukanStatus(Carbon $now, Carbon $jamMasuk, int $toleransi): ?string
    {
        if ($now->lessThan($jamMasuk)) {
            return null;
        }

        return $now->lessThanOrEqualTo($jamMasuk->copy()->addMinutes($toleransi))
            ? 'HADIR'
            : 'TERLAMBAT';
    }

    public function tentukanStatusPulang(Carbon $now, Carbon $jamPulang, Carbon $batasJamPulang): string
    {
        if ($now->lessThan($jamPulang)) {
            return 'TERLALU_AWAL';
        }

        if ($now->greaterThan($batasJamPulang)) {
            return 'SUDAH_BERAKHIR';
        }

        return 'BOLEH_PULANG';
    }

    public function simpanHariLibur(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'keterangan' => ['required', 'string', 'max:255'],
        ]);

        HariLibur::updateOrCreate(['tanggal' => $data['tanggal']], $data);

        return to_route('atur.absensi')->with('sukses', 'Hari libur berhasil disimpan.');
    }

    public function hapusHariLibur(HariLibur $hariLibur)
    {
        $hariLibur->delete();

        return to_route('atur.absensi')->with('sukses', 'Hari libur berhasil dihapus.');
    }

    public function ketidakhadiran()
    {
        return view('absensi.ketidakhadiran', [
            'guru' => Guru::query()->where('status_akun', 'Aktif')->orderBy('nama_guru')->get(),
            'data' => Ketidakhadiran::with('guru')->latest('tanggal')->paginate(15, ['*'], 'guru_page'),
            'siswaAktif' => Siswa::query()->where('status', 'aktif')->whereIn('id', DB::table('kelola_siswa')->where('tahun_ajar_id', session('tahun_ajar_id'))->select('siswa_id'))->orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'nis']),
            'dataSiswa' => KetidakhadiranSiswa::with('siswa')->latest('tanggal_mulai')->paginate(15, ['*'], 'siswa_page'),
        ]);
    }

    public function simpanKetidakhadiran(Request $request)
    {
        $data = $request->validate([
            'guru_id' => ['required', 'string'],
            'tanggal' => ['required', 'date'],
            'status' => ['required', 'in:IZIN,SAKIT,DINAS,DISPENSASI'],
            'keterangan' => ['required', 'string', 'max:1000'],
        ]);

        if (! $this->isHariKerja(Carbon::parse($data['tanggal']))) {
            return back()->withErrors(['tanggal' => 'Hari libur tidak perlu dibuatkan status ketidakhadiran.'])->withInput();
        }

        $presensi = Presensi::where('guru_id', $data['guru_id'])
            ->where('tanggal', $data['tanggal'])
            ->first();

        if ($presensi && $presensi->status !== 'ALFA') {
            return back()->withErrors(['guru_id' => 'Guru sudah memiliki presensi pada tanggal tersebut.'])->withInput();
        }

        DB::transaction(function () use ($data, $presensi) {
            if ($presensi?->status === 'ALFA') {
                $presensi->delete();
            }

            Ketidakhadiran::updateOrCreate(
                ['guru_id' => $data['guru_id'], 'tanggal' => $data['tanggal']],
                ['status' => $data['status'], 'keterangan' => $data['keterangan']]
            );
        });

        return to_route('ketidakhadiran', ['tab' => 'guru'])->with('sukses', 'Status ketidakhadiran berhasil disimpan.');
    }

    public function hapusKetidakhadiran(Ketidakhadiran $ketidakhadiran)
    {
        $ketidakhadiran->delete();

        return to_route('ketidakhadiran', ['tab' => 'guru'])->with('sukses', 'Status ketidakhadiran berhasil dihapus.');
    }

    public function laporan(Request $request)
    {
        $filter = $this->validasiFilterRekap($request);
        $jenis = $request->validate(['jenis' => ['nullable', 'in:guru,siswa'], 'status' => ['nullable', 'string'], 'siswa_id' => ['nullable', 'uuid'], 'kelas_id' => ['nullable', 'string']])['jenis'] ?? 'guru';
        if ($jenis === 'siswa') {
            return $this->laporanSiswa($request, $filter);
        }
        $rows = $this->ambilRekap($filter);
        $statusList = ['HADIR', 'TERLAMBAT', 'IZIN', 'SAKIT', 'DINAS', 'DISPENSASI', 'ALFA'];
        $statistik = collect($statusList)->mapWithKeys(fn(string $status) => [$status => $this->ambilRekap(array_diff_key($filter, ['status' => true]))->where('status', $status)->count()]);
        $rows = $rows->when($filter['status'] ?? null, fn($items, $status) => $items->where('status', $status));

        return view('absensi.rekap', [
            'rows' => $rows,
            'statistik' => $statistik,
            'guru' => Guru::query()->select('id', 'nama_guru')->orderBy('nama_guru')->get(),
            'filter' => $filter,
        ]);
    }

    public function exportLaporan(Request $request)
    {
        $filter = $this->validasiFilterRekap($request);
        $jenis = $request->validate(['jenis' => ['nullable', 'in:guru,siswa']])['jenis'] ?? 'guru';
        if ($jenis === 'siswa') {
            return $this->exportLaporanSiswa($request, $filter);
        }

        return Excel::download(new PresensiExport($this->ambilRekap($filter)), 'laporan-absensi.xlsx');
    }

    private function laporanSiswa(Request $request, array $filter)
    {
        $extra = $request->validate([
            'status' => ['nullable', 'in:HADIR,TERLAMBAT,BELUM_HADIR,ALPA,IZIN,SAKIT'],
            'siswa_id' => ['nullable', 'uuid', 'exists:siswa,id'],
            'kelas_id' => ['nullable', 'string'],
        ]);
        $query = PresensiSiswa::query()->with('siswa')
            ->whereDate('tanggal', '>=', $filter['tanggal_mulai'])
            ->whereDate('tanggal', '<=', $filter['tanggal_akhir'])
            ->when($extra['status'] ?? null, fn($builder, $status) => $builder->where('status', $status))
            ->when($extra['siswa_id'] ?? null, fn($builder, $id) => $builder->where('siswa_id', $id))
            ->when($extra['kelas_id'] ?? null, fn($builder, $id) => $builder->whereIn('siswa_id', DB::table('kelola_siswa')->where('tahun_ajar_id', session('tahun_ajar_id'))->select('siswa_id')->where('kelas_id', $id)))
            ->when(! ($extra['kelas_id'] ?? null), fn($builder) => $builder->whereIn('siswa_id', DB::table('kelola_siswa')->where('tahun_ajar_id', session('tahun_ajar_id'))->select('siswa_id')));
        $rows = $query->orderByDesc('tanggal')->paginate(25)->withQueryString();
        $studentIds = $rows->getCollection()->pluck('siswa_id')->unique()->values();
        $classNames = DB::table('kelola_siswa')
            ->join('kelas', 'kelas.id', '=', 'kelola_siswa.kelas_id')
            ->where('kelola_siswa.tahun_ajar_id', session('tahun_ajar_id'))
            ->whereIn('kelola_siswa.siswa_id', $studentIds)
            ->pluck('kelas.nama_kelas', 'kelola_siswa.siswa_id');
        $rows->getCollection()->transform(function (PresensiSiswa $row) use ($classNames) {
            $row->setAttribute('nama_kelas', $classNames[$row->siswa_id] ?? '-');

            return $row;
        });
        $stats = collect(['HADIR', 'TERLAMBAT', 'BELUM_HADIR', 'ALPA', 'IZIN', 'SAKIT'])
            ->mapWithKeys(fn(string $status) => [$status => PresensiSiswa::whereDate('tanggal', '>=', $filter['tanggal_mulai'])->whereDate('tanggal', '<=', $filter['tanggal_akhir'])->where('status', $status)
                ->when($extra['siswa_id'] ?? null, fn($builder, $id) => $builder->where('siswa_id', $id))
                ->when($extra['kelas_id'] ?? null, fn($builder, $id) => $builder->whereIn('siswa_id', DB::table('kelola_siswa')->where('tahun_ajar_id', session('tahun_ajar_id'))->select('siswa_id')->where('kelas_id', $id)))
                ->when(! ($extra['kelas_id'] ?? null), fn($builder) => $builder->whereIn('siswa_id', DB::table('kelola_siswa')->where('tahun_ajar_id', session('tahun_ajar_id'))->select('siswa_id')))->count()]);

        return view('absensi.rekap', [
            'jenis' => 'siswa', 'rows' => $rows, 'statistik' => $stats, 'guru' => collect(),
            'siswaFilter' => Siswa::select('id', 'nama_lengkap')->orderBy('nama_lengkap')->get(),
            'kelasFilter' => \App\Models\Kelas::whereIn('id', DB::table('kelola_siswa')->where('tahun_ajar_id', session('tahun_ajar_id'))->select('kelas_id'))->orderBy('nama_kelas')->get(['id', 'nama_kelas']),
            'filter' => [...$filter, ...$extra],
        ]);
    }

    private function exportLaporanSiswa(Request $request, array $filter)
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:HADIR,TERLAMBAT,BELUM_HADIR,ALPA,IZIN,SAKIT'],
            'siswa_id' => ['nullable', 'uuid', 'exists:siswa,id'],
            'kelas_id' => ['nullable', 'string'],
        ]);
        $rows = PresensiSiswa::query()->with('siswa')
            ->whereDate('tanggal', '>=', $filter['tanggal_mulai'])->whereDate('tanggal', '<=', $filter['tanggal_akhir'])
            ->when($data['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->when($data['siswa_id'] ?? null, fn($query, $id) => $query->where('siswa_id', $id))
            ->when($data['kelas_id'] ?? null, fn($query, $id) => $query->whereIn('siswa_id', DB::table('kelola_siswa')->where('tahun_ajar_id', session('tahun_ajar_id'))->select('siswa_id')->where('kelas_id', $id)))
            ->when(! ($data['kelas_id'] ?? null), fn($query) => $query->whereIn('siswa_id', DB::table('kelola_siswa')->where('tahun_ajar_id', session('tahun_ajar_id'))->select('siswa_id')))
            ->orderByDesc('tanggal')->get()
            ->map(fn(PresensiSiswa $item) => [
                'Jenis' => 'Siswa', 'Tanggal' => $item->tanggal->format('Y-m-d'),
                'Nama Siswa' => $item->siswa?->nama_lengkap ?? 'Siswa tidak tersedia',
                'Kelas' => DB::table('kelola_siswa')->join('kelas', 'kelas.id', '=', 'kelola_siswa.kelas_id')->where('kelola_siswa.tahun_ajar_id', session('tahun_ajar_id'))->where('kelola_siswa.siswa_id', $item->siswa_id)->value('kelas.nama_kelas') ?? '-',
                'Jam Masuk' => $item->jam_masuk ? substr($item->jam_masuk, 0, 5) : '-',
                'Jam Pulang' => $item->jam_pulang ? substr($item->jam_pulang, 0, 5) : '-',
                'Status' => $item->status, 'Keterangan' => $item->keterangan ?? '-',
            ]);
        return Excel::download(new \App\Exports\StudentAttendanceExport($rows), 'laporan-absensi-siswa.xlsx');
    }

    private function validasiFilterRekap(Request $request): array
    {
        $filter = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'guru_id' => ['nullable', 'string'],
            'status' => ['nullable', 'in:HADIR,TERLAMBAT,IZIN,SAKIT,DINAS,DISPENSASI,ALFA'],
        ]);

        $filter['tanggal_mulai'] ??= now()->toDateString();
        $filter['tanggal_akhir'] ??= now()->toDateString();

        return $filter;
    }

    private function ambilRekap(array $filter)
    {
        $presensi = Presensi::with('guru')
            ->when($filter['tanggal_mulai'] ?? null, fn($query, $date) => $query->where('tanggal', '>=', $date))
            ->when($filter['tanggal_akhir'] ?? null, fn($query, $date) => $query->where('tanggal', '<=', $date))
            ->when($filter['guru_id'] ?? null, fn($query, $id) => $query->where('guru_id', $id))
            ->get()
            ->map(fn(Presensi $item) => [
                'tanggal' => $item->tanggal->format('Y-m-d'),
                'guru' => $item->guru?->nama_guru ?? 'Guru tidak tersedia',
                'jam_masuk' => $item->jam_masuk ? substr($item->jam_masuk, 0, 5) : null,
                'jam_pulang' => $item->jam_pulang ? substr($item->jam_pulang, 0, 5) : null,
                'status' => $item->status,
                'keterangan' => null,
            ]);

        $ketidakhadiran = Ketidakhadiran::with('guru')
            ->when($filter['tanggal_mulai'] ?? null, fn($query, $date) => $query->where('tanggal', '>=', $date))
            ->when($filter['tanggal_akhir'] ?? null, fn($query, $date) => $query->where('tanggal', '<=', $date))
            ->when($filter['guru_id'] ?? null, fn($query, $id) => $query->where('guru_id', $id))
            ->get()
            ->map(fn(Ketidakhadiran $item) => [
                'tanggal' => $item->tanggal->format('Y-m-d'),
                'guru' => $item->guru?->nama_guru ?? 'Guru tidak tersedia',
                'jam_masuk' => null,
                'jam_pulang' => null,
                'status' => $item->status,
                'keterangan' => $item->keterangan,
            ]);

        return $presensi->merge($ketidakhadiran)
            ->when($filter['status'] ?? null, fn($rows, $status) => $rows->where('status', $status))
            ->sortByDesc(fn(array $row) => $row['tanggal'] . $row['guru'])
            ->values();
    }

    public function isHariKerja(Carbon $tanggal, ?Absensi $pengaturan = null): bool
    {
        $pengaturan ??= $this->ambilPengaturan();
        if (HariLibur::where('tanggal', $tanggal->toDateString())->exists()) {
            return false;
        }

        $hariKerja = $pengaturan->hari_kerja ?: [1, 2, 3, 4, 5];

        return $this->tentukanHariKerja($tanggal->dayOfWeekIso, $hariKerja);
    }

    public function tentukanHariKerja(int $nomorHari, array $hariKerja): bool
    {
        return in_array($nomorHari, $hariKerja, true);
    }

    private function formatFeedback(Guru $guru, Presensi $presensi): array
    {
        return [
            'nama' => $guru->nama_guru,
            'foto' => $guru->foto_pegawai,
            'jam' => substr($presensi->jam_masuk, 0, 5),
            'jam_masuk' => substr($presensi->jam_masuk, 0, 5),
            'jam_pulang' => $presensi->jam_pulang ? substr($presensi->jam_pulang, 0, 5) : null,
            'status' => $presensi->status,
        ];
    }

    private function ambilPengaturan(): Absensi
    {
        return Absensi::query()->firstOrCreate([], [
            'jam_masuk' => '07:00:00',
            'toleransi_terlambat' => 10,
            'jam_pulang' => '15:00:00',
            'batas_jam_pulang' => '17:00:00',
            'hari_kerja' => [1, 2, 3, 4, 5],
            'jam_masuk_siswa' => '06:00:00',
            'batas_scan_masuk_siswa' => '07:00:00',
            'jam_pulang_siswa' => '13:00:00',
            'batas_jam_pulang_siswa' => '17:00:00',
            'waktu_notifikasi_siswa' => '07:00:00',
            'jeda_alpa_siswa' => 3,
        ]);
    }

    private function waktuHariIni(string $tanggal, string $waktu): Carbon
    {
        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $tanggal . ' ' . $waktu,
            config('app.timezone')
        );
    }

    private function waktuKeDetik(string $waktu): int
    {
        [$jam, $menit] = array_map('intval', explode(':', $waktu));

        return ($jam * 3600) + ($menit * 60);
    }
}
