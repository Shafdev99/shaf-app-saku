<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\Ketidakhadiran;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAbsent extends Command
{
    protected $signature = 'attendance:mark-absent {--date= : Tanggal evaluasi dalam format Y-m-d}';

    protected $description = 'Menandai guru aktif yang tidak hadir sebagai ALFA';

    public function handle(): int
    {
        $pengaturan = Absensi::first();
        if (!$pengaturan) {
            $this->warn('Pengaturan absensi belum tersedia.');
            return self::SUCCESS;
        }

        $tanggal = $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', $this->option('date'), config('app.timezone'))->startOfDay()
            : now()->startOfDay();

        if (!$this->hariKerja($tanggal, $pengaturan)) {
            $this->info('Tanggal bukan hari kerja atau merupakan hari libur.');
            return self::SUCCESS;
        }

        $batasHadir = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $tanggal->toDateString() . ' ' . $pengaturan->jam_masuk,
            config('app.timezone')
        )->addMinutes($pengaturan->toleransi_terlambat);

        if ($tanggal->isToday() && now()->lessThanOrEqualTo($batasHadir)) {
            $this->warn('Batas toleransi masuk belum selesai.');
            return self::SUCCESS;
        }

        $jumlah = 0;
        Guru::query()->where('status_akun', 'Aktif')->orderBy('id')->each(function (Guru $guru) use ($tanggal, &$jumlah) {
            if (Ketidakhadiran::where('guru_id', $guru->id)->where('tanggal', $tanggal->toDateString())->exists()) {
                return;
            }

            $presensi = Presensi::firstOrCreate(
                ['guru_id' => $guru->id, 'tanggal' => $tanggal->toDateString()],
                ['jam_masuk' => null, 'status' => 'ALFA']
            );

            if ($presensi->wasRecentlyCreated) {
                $jumlah++;
            }
        });

        $this->info("{$jumlah} guru ditandai ALFA.");
        return self::SUCCESS;
    }

    private function hariKerja(Carbon $tanggal, Absensi $pengaturan): bool
    {
        if (HariLibur::where('tanggal', $tanggal->toDateString())->exists()) {
            return false;
        }

        return in_array($tanggal->dayOfWeekIso, $pengaturan->hari_kerja ?: [1, 2, 3, 4, 5], true);
    }
}
