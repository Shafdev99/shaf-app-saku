<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\KetidakhadiranSiswa;
use App\Models\NotifikasiAbsensiSiswa;
use App\Models\PresensiSiswa;
use App\Models\Siswa;
use App\Services\StudentAttendanceService;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class EvaluateStudentAttendance extends Command
{
    protected $signature = 'attendance:students-evaluate {--date= : Tanggal evaluasi YYYY-MM-DD}';

    protected $description = 'Evaluasi Belum Hadir/Alpa dan notifikasi absensi siswa';

    public function handle(StudentAttendanceService $attendance, WhatsappService $whatsapp): int
    {
        $settings = Absensi::query()->first();
        if (! $settings) {
            $this->warn('Pengaturan absensi belum tersedia.');
            return self::SUCCESS;
        }

        $date = $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', $this->option('date'), config('app.timezone'))->startOfDay()
            : now(config('app.timezone'))->startOfDay();
        $now = now(config('app.timezone'));

        if (! $date->isToday()) {
            $this->warn('Notifikasi hanya diproses untuk tanggal sekolah hari ini; backfill status tetap berjalan untuk tanggal yang diminta.');
            $attendance->evaluate($date, $date->copy()->endOfDay(), $settings);
            return self::SUCCESS;
        }

        $statusCounts = $attendance->evaluate($date, $now, $settings);
        $this->line("Evaluasi status selesai: {$statusCounts['belum_hadir']} Belum Hadir baru, {$statusCounts['alpa']} Alpa baru.");

        if (! $attendance->isSchoolDay($date, $settings)) {
            return self::SUCCESS;
        }

        $notificationTime = $settings->waktu_notifikasi_siswa ?: ($settings->batas_scan_masuk_siswa ?: '07:00:00');
        $effectiveTime = $notificationTime >= ($settings->batas_scan_masuk_siswa ?: '07:00:00') ? $notificationTime : ($settings->batas_scan_masuk_siswa ?: '07:00:00');
        $sendAt = Carbon::createFromFormat('Y-m-d H:i:s', $date->toDateString() . ' ' . $effectiveTime, config('app.timezone'));
        if ($now->lt($sendAt)) {
            $this->info('Waktu evaluasi notifikasi belum tiba.');
            return self::SUCCESS;
        }

        $sent = 0;
        Siswa::query()->where('status', 'aktif')->whereIn('id', DB::table('kelola_siswa')->where('tahun_ajar_id', session('tahun_ajar_id'))->select('siswa_id'))->orderBy('id')->chunkById(200, function ($students) use ($date, $whatsapp, &$sent): void {
            foreach ($students as $student) {
                if (PresensiSiswa::where('siswa_id', $student->id)->whereDate('tanggal', $date->toDateString())->whereNotNull('jam_masuk')->exists()) {
                    continue;
                }
                if (KetidakhadiranSiswa::where('siswa_id', $student->id)->whereDate('tanggal_mulai', '<=', $date->toDateString())->whereDate('tanggal_selesai', '>=', $date->toDateString())->whereIn('status', ['IZIN', 'SAKIT'])->exists()) {
                    continue;
                }

                try {
                    $notification = DB::transaction(function () use ($student, $date): ?NotifikasiAbsensiSiswa {
                        $existing = NotifikasiAbsensiSiswa::where('siswa_id', $student->id)
                            ->whereDate('tanggal', $date->toDateString())
                            ->where('jenis', 'belum_hadir')
                            ->lockForUpdate()
                            ->first();
                        if ($existing) {
                            return null;
                        }
                        $inserted = DB::table('notifikasi_absensi_siswa')->insertOrIgnore([
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'siswa_id' => $student->id,
                            'tanggal' => $date->toDateString(),
                            'jenis' => 'belum_hadir',
                            'status' => 'pending',
                            'jumlah_percobaan' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        if (! $inserted) {
                            return null;
                        }

                        $log = NotifikasiAbsensiSiswa::where('siswa_id', $student->id)->whereDate('tanggal', $date->toDateString())->where('jenis', 'belum_hadir')->lockForUpdate()->firstOrFail();
                        $log->update(['jumlah_percobaan' => 1, 'dicoba_pada' => now()]);
                        return $log;
                    });
                } catch (Throwable $exception) {
                    report($exception);
                    continue;
                }

                if (! $notification) {
                    continue;
                }

                $normalized = $student->no_wa_ortu ? $whatsapp->normalisasiNomor($student->no_wa_ortu) : null;
                if ($normalized === null) {
                    $notification->update(['status' => 'skipped', 'alasan_aman' => $student->no_wa_ortu ? 'nomor_tidak_valid' : 'nomor_belum_tersedia']);
                    continue;
                }

                try {
                    $delivered = $whatsapp->kirimNotifikasiKetidakhadiranSiswa($normalized, $student->nama_lengkap, $date->format('d-m-Y'));
                    $notification->update([
                        'status' => $delivered ? 'sent' : 'failed',
                        'terkirim_pada' => $delivered ? now() : null,
                        'alasan_aman' => $delivered ? null : 'provider_tidak_mengonfirmasi',
                    ]);
                } catch (Throwable $exception) {
                    $notification->update(['status' => 'failed', 'alasan_aman' => 'kegagalan_provider']);
                    report($exception);
                    continue;
                }
                if ($delivered) {
                    $sent++;
                }
            }
        }, 'id');

        $this->info("Notifikasi berhasil diproses: {$sent}. Status failed/skipped tersimpan pada log.");
        return self::SUCCESS;
    }
}
