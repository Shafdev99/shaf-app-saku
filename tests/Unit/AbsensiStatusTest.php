<?php

namespace Tests\Unit;

use App\Http\Controllers\AbsensiController;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AbsensiStatusTest extends TestCase
{
    #[DataProvider('waktuPresensiProvider')]
    public function test_status_presensi_mengikuti_jam_masuk_dan_toleransi(string $waktu, ?string $status): void
    {
        $controller = new AbsensiController();
        $jamMasuk = Carbon::create(2026, 8, 25, 7, 0, 0, 'Asia/Jakarta');
        $now = Carbon::createFromFormat('Y-m-d H:i', '2026-08-25 ' . $waktu, 'Asia/Jakarta');

        $this->assertSame($status, $controller->tentukanStatus($now, $jamMasuk, 10));
    }

    public static function waktuPresensiProvider(): array
    {
        return [
            'sebelum jam masuk' => ['06:59', null],
            'tepat jam masuk' => ['07:00', 'HADIR'],
            'dalam toleransi awal' => ['07:05', 'HADIR'],
            'tepat batas toleransi' => ['07:10', 'HADIR'],
            'melewati toleransi' => ['07:11', 'TERLAMBAT'],
            'terlambat jauh' => ['08:00', 'TERLAMBAT'],
        ];
    }

    #[DataProvider('waktuPulangProvider')]
    public function test_status_presensi_pulang_menggunakan_batas_inclusive(string $waktu, string $status): void
    {
        $controller = new AbsensiController();
        $jamPulang = Carbon::create(2026, 8, 25, 15, 0, 0, 'Asia/Jakarta');
        $batasJamPulang = Carbon::create(2026, 8, 25, 17, 0, 0, 'Asia/Jakarta');
        $now = Carbon::createFromFormat('Y-m-d H:i', '2026-08-25 ' . $waktu, 'Asia/Jakarta');

        $this->assertSame($status, $controller->tentukanStatusPulang($now, $jamPulang, $batasJamPulang));
    }

    public static function waktuPulangProvider(): array
    {
        return [
            'sebelum jam pulang' => ['14:59', 'TERLALU_AWAL'],
            'tepat jam pulang' => ['15:00', 'BOLEH_PULANG'],
            'tengah waktu pulang' => ['16:00', 'BOLEH_PULANG'],
            'tepat batas pulang' => ['17:00', 'BOLEH_PULANG'],
            'setelah batas pulang' => ['17:01', 'SUDAH_BERAKHIR'],
        ];
    }

    #[DataProvider('hariKerjaProvider')]
    public function test_pengaturan_hari_kerja_menentukan_hari_aktif(int $nomorHari, bool $aktif): void
    {
        $controller = new AbsensiController();

        $this->assertSame($aktif, $controller->tentukanHariKerja($nomorHari, [1, 2, 3, 4, 5]));
    }

    public static function hariKerjaProvider(): array
    {
        return [
            'senin aktif' => [1, true],
            'jumat aktif' => [5, true],
            'sabtu nonaktif' => [6, false],
            'minggu nonaktif' => [7, false],
        ];
    }
}
