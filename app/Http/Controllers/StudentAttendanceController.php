<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\KetidakhadiranSiswa;
use App\Models\NotifikasiAbsensiSiswa;
use App\Models\Guru;
use App\Models\PresensiSiswa;
use App\Models\Siswa;
use App\Services\AttendanceCardRegistry;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentAttendanceController extends Controller
{
    public function linkCardView(Siswa $siswa, $trigger = null)
    {
        $siswa->load([
            'kelas.tingkat',
            'kelas.jurusan',
            'keteranganKeterima.kelas.tingkat',
            'keteranganKeterima.kelas.jurusan',
        ]);
        $kelasSiswa = $siswa->kelas ?? $siswa->keteranganKeterima?->kelas;
        // dd($siswa);
        return view('siswa.tautkan-kartu', compact('siswa', 'trigger', 'kelasSiswa'));
    }

    public function linkCard(Request $request, AttendanceCardRegistry $registry)
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'uuid', 'exists:siswa,id'],
            'card_id' => ['required', 'string', 'max:100', 'unique:siswa,card_id', 'unique:guru,card_id'],
        ], [
            'required' => ':attribute wajib diisi!',
            'unique' => 'Kartu tersebut sudah tertaut ke siswa atau tenaga pendidik lain.'
        ]);

        if (Guru::where('card_id', $data['card_id'])->exists()) {
            throw ValidationException::withMessages(['card_id' => 'Kartu tersebut sudah tertaut ke tenaga pendidik.']);
        }

        try {
            DB::transaction(function () use ($registry, $data): void {
                $siswa = Siswa::whereKey($data['siswa_id'])->lockForUpdate()->firstOrFail();
                if ($siswa->card_id) {
                    $registry->release('siswa', $siswa->id);
                }
                $registry->claim($data['card_id'], 'siswa', $siswa->id);
                $siswa->update(['card_id' => $data['card_id']]);
            });
        } catch (QueryException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'unique') || str_contains(strtolower($exception->getMessage()), 'duplicate')) {
                throw ValidationException::withMessages(['card_id' => 'Kartu tersebut baru saja tertaut ke akun lain.']);
            }
            throw $exception;
        }

        return to_route('siswa')->with('sukses', 'Kartu RFID berhasil ditautkan ke siswa.');
    }

    public function resetCard(Siswa $siswa, AttendanceCardRegistry $registry)
    {
        DB::transaction(function () use ($siswa, $registry): void {
            $lockedStudent = Siswa::whereKey($siswa->id)->lockForUpdate()->firstOrFail();
            $registry->release('siswa', $lockedStudent->id);
            $lockedStudent->update(['card_id' => null]);
        });

        return to_route('siswa')->with('sukses', 'Kartu RFID dilepas. Histori absensi tetap tersimpan.');
    }

    public function saveLeave(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'uuid', 'exists:siswa,id'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', Rule::in(['IZIN', 'SAKIT'])],
            'kategori_izin' => ['nullable', Rule::requiredIf($request->input('status') === 'IZIN'), Rule::in(['keluarga', 'kegiatan', 'lainnya'])],
            'keterangan' => ['required', 'string', 'max:1000'],
            'status_dokumen' => ['nullable', Rule::in(['belum_ada', 'diterima', 'diverifikasi'])],
        ]);

        if ($data['status'] === 'SAKIT' && $data['tanggal_mulai'] !== $data['tanggal_selesai']) {
            $data['kategori_izin'] = null;
        }
        if ($data['status'] === 'SAKIT' && $data['tanggal_mulai'] === $data['tanggal_selesai']) {
            unset($data['kategori_izin']);
        }

        $duration = \Carbon\Carbon::parse($data['tanggal_mulai'])->diffInDays(\Carbon\Carbon::parse($data['tanggal_selesai'])) + 1;
        $review = $duration > 3;
        if ($review && $data['status'] === 'IZIN' && mb_strlen(trim($data['keterangan'])) < 30) {
            throw ValidationException::withMessages(['keterangan' => 'Untuk izin lebih dari 3 hari, isi alasan kuat secara lebih lengkap.']);
        }

        $existingAttendance = PresensiSiswa::where('siswa_id', $data['siswa_id'])
            ->whereDate('tanggal', '>=', $data['tanggal_mulai'])
            ->whereDate('tanggal', '<=', $data['tanggal_selesai'])
            ->whereNotNull('jam_masuk')
            ->exists();
        if ($existingAttendance) {
            throw ValidationException::withMessages(['tanggal_mulai' => 'Siswa sudah memiliki scan masuk pada rentang tanggal tersebut. Histori scan dipertahankan; izin/sakit tidak disimpan.']);
        }

        $overlap = KetidakhadiranSiswa::where('siswa_id', $data['siswa_id'])
            ->whereDate('tanggal_mulai', '<=', $data['tanggal_selesai'])
            ->whereDate('tanggal_selesai', '>=', $data['tanggal_mulai'])
            ->exists();
        if ($overlap) {
            throw ValidationException::withMessages(['tanggal_mulai' => 'Rentang tanggal bertumpang tindih dengan izin/sakit yang sudah tercatat.']);
        }

        KetidakhadiranSiswa::create([
            ...$data,
            'perlu_pemeriksaan' => $review,
            'status_dokumen' => $data['status'] === 'SAKIT' && $review ? ($data['status_dokumen'] ?? 'belum_ada') : 'belum_perlu',
            'diinput_oleh' => $request->user()?->id,
        ]);

        return to_route('ketidakhadiran', ['tab' => 'siswa'])->with('sukses', $review ? 'Izin/sakit tersimpan dengan indikator pemeriksaan.' : 'Izin/sakit siswa tersimpan.');
    }

    public function deleteLeave(KetidakhadiranSiswa $ketidakhadiranSiswa)
    {
        $ketidakhadiranSiswa->delete();

        return to_route('ketidakhadiran', ['tab' => 'siswa'])->with('sukses', 'Izin/sakit siswa dihapus.');
    }

    public function correctAttendance(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'status' => ['required', Rule::in(['HADIR', 'TERLAMBAT', 'BELUM_HADIR', 'ALPA', 'IZIN', 'SAKIT'])],
            'keterangan' => ['required', 'string', 'max:1000'],
        ]);

        $attendance = PresensiSiswa::firstOrNew(['siswa_id' => $siswa->id, 'tanggal' => $data['tanggal']]);
        $attendance->status = $data['status'];
        $attendance->keterangan = $data['keterangan'];
        $attendance->dikoreksi_oleh = $request->user()?->id;
        $attendance->dikoreksi_pada = now();
        $attendance->save();

        return to_route('laporan.absensi', ['jenis' => 'siswa', 'tanggal_mulai' => $data['tanggal'], 'tanggal_akhir' => $data['tanggal']])
            ->with('sukses', 'Koreksi absensi tersimpan. Riwayat scan dan notifikasi dipertahankan.');
    }

    public function notifications()
    {
        return NotifikasiAbsensiSiswa::with('siswa')->latest('tanggal')->paginate(20);
    }
}
