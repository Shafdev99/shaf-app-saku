<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\KetidakhadiranSiswa;
use App\Models\PresensiSiswa;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class StudentAttendanceService
{
    public function isSchoolDay(Carbon $date, Absensi $settings): bool
    {
        if (HariLibur::whereDate('tanggal', $date->toDateString())->exists()) {
            return false;
        }

        $days = $settings->hari_kerja;
        if (is_string($days)) {
            $days = json_decode($days, true) ?: [];
        }
        $days = $days ?: [1, 2, 3, 4, 5];
        $days = array_map('intval', $days);

        return in_array($date->dayOfWeekIso, $days, true);
    }

    public function attendanceStatus(Carbon $scanAt, Absensi $settings): string
    {
        $deadline = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $scanAt->toDateString() . ' ' . ($settings->batas_scan_masuk_siswa ?: '07:00:00'),
            config('app.timezone')
        );

        return $scanAt->lessThanOrEqualTo($deadline) ? 'HADIR' : 'TERLAMBAT';
    }

    public function scan(Siswa $student, Carbon $scanAt, Absensi $settings): array
    {
        $date = $scanAt->toDateString();
        $deadlineTime = $settings->batas_scan_masuk_siswa ?: '07:00:00';
        $departureTime = $settings->jam_pulang_siswa ?: '13:00:00';
        $departureLimit = $settings->batas_jam_pulang_siswa ?: '17:00:00';

        if (! $this->isSchoolDay($scanAt, $settings)) {
            return ['error' => 'Hari ini bukan hari absensi siswa.', 'code' => 422];
        }

        if ($scanAt->lt($this->at($date, $settings->jam_masuk_siswa ?: '06:00:00'))) {
            return ['error' => 'Presensi siswa belum dibuka.', 'code' => 422];
        }

        $absence = KetidakhadiranSiswa::where('siswa_id', $student->id)
            ->whereDate('tanggal_mulai', '<=', $date)
            ->whereDate('tanggal_selesai', '>=', $date)
            ->whereIn('status', ['IZIN', 'SAKIT'])
            ->first();

        if ($absence) {
            return ['error' => "Siswa memiliki status {$absence->status} pada tanggal ini.", 'code' => 422];
        }

        try {
            return DB::transaction(function () use ($student, $scanAt, $date, $deadlineTime, $departureTime, $departureLimit): array {
                $attendance = PresensiSiswa::where('siswa_id', $student->id)->whereDate('tanggal', $date)->lockForUpdate()->first();

                if (! $attendance) {
                    $attendance = PresensiSiswa::create([
                        'siswa_id' => $student->id,
                        'tanggal' => $date,
                        'jam_masuk' => $scanAt->format('H:i:s'),
                        'status' => $scanAt->format('H:i:s') <= $deadlineTime ? 'HADIR' : 'TERLAMBAT',
                    ]);

                    return ['attendance' => $attendance, 'event' => 'masuk', 'created' => true];
                }

                if ($attendance->jam_pulang !== null) {
                    return ['attendance' => $attendance, 'event' => 'duplikat', 'created' => false];
                }

                if ($attendance->jam_masuk === null) {
                    return ['attendance' => $attendance, 'event' => 'duplikat', 'created' => false];
                }

                if (in_array($attendance->status, ['IZIN', 'SAKIT'], true)) {
                    return ['error' => 'Siswa memiliki status ketidakhadiran pada tanggal ini.', 'code' => 422];
                }

                if ($scanAt->lt($this->at($date, $departureTime))) {
                    return ['attendance' => $attendance, 'event' => 'masuk_tercatat', 'created' => false];
                }

                if ($scanAt->gt($this->at($date, $departureLimit))) {
                    return ['error' => 'Batas waktu presensi pulang siswa telah berakhir.', 'code' => 422];
                }

                $attendance->update(['jam_pulang' => $scanAt->format('H:i:s')]);

                return ['attendance' => $attendance->refresh(), 'event' => 'pulang', 'created' => true];
            });
        } catch (QueryException $exception) {
            $attendance = PresensiSiswa::where('siswa_id', $student->id)->whereDate('tanggal', $date)->first();

            if (! $attendance) {
                throw $exception;
            }

            return ['attendance' => $attendance, 'event' => 'duplikat', 'created' => false];
        }
    }

    public function evaluate(Carbon $date, Carbon $now, Absensi $settings): array
    {
        if (! $this->isSchoolDay($date, $settings)) {
            return ['belum_hadir' => 0, 'alpa' => 0];
        }

        $deadline = $this->at($date->toDateString(), $settings->batas_scan_masuk_siswa ?: '07:00:00');
        if ($now->lt($deadline)) {
            return ['belum_hadir' => 0, 'alpa' => 0];
        }

        $alpaAt = $deadline->copy()->addHours((int) ($settings->jeda_alpa_siswa ?? 3));
        $targetStatus = $now->gte($alpaAt) ? 'ALPA' : 'BELUM_HADIR';
        $counts = ['belum_hadir' => 0, 'alpa' => 0];

        Siswa::query()->where('status', 'aktif')->whereIn('id', DB::table('kelola_siswa')->where('tahun_ajar_id', session('tahun_ajar_id'))->select('siswa_id'))->orderBy('id')->chunkById(200, function ($students) use ($date, $targetStatus, &$counts): void {
            foreach ($students as $student) {
                $approvedLeave = KetidakhadiranSiswa::where('siswa_id', $student->id)
                    ->whereDate('tanggal_mulai', '<=', $date->toDateString())
                    ->whereDate('tanggal_selesai', '>=', $date->toDateString())
                    ->whereIn('status', ['IZIN', 'SAKIT'])
                    ->exists();
                if ($approvedLeave) {
                    continue;
                }

                if (PresensiSiswa::where('siswa_id', $student->id)->whereDate('tanggal', $date->toDateString())->whereNotNull('jam_masuk')->exists()) {
                    continue;
                }

                try {
                    $attendance = PresensiSiswa::firstOrCreate(
                        ['siswa_id' => $student->id, 'tanggal' => $date->toDateString()],
                        ['status' => $targetStatus]
                    );
                } catch (QueryException $exception) {
                    $attendance = PresensiSiswa::where('siswa_id', $student->id)->whereDate('tanggal', $date->toDateString())->first();
                    if (! $attendance) {
                        throw $exception;
                    }
                }
                if ($attendance->wasRecentlyCreated) {
                    $counts[strtolower($targetStatus) === 'alpa' ? 'alpa' : 'belum_hadir']++;
                } elseif ($attendance->jam_masuk === null && $attendance->status !== 'ALPA' && $targetStatus === 'ALPA') {
                    $attendance->update(['status' => 'ALPA']);
                    $counts['alpa']++;
                }
            }
        }, 'id');

        return $counts;
    }

    private function at(string $date, string $time): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time, config('app.timezone'));
    }
}
