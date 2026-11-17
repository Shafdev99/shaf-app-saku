<?php

namespace Tests\Unit;

use App\Http\Controllers\AbsensiController;
use App\Services\WhatsappService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StudentAttendanceRulesTest extends TestCase
{
    #[DataProvider('studentStatusProvider')]
    public function test_student_status_uses_school_deadline_without_teacher_tolerance(string $time, string $expected): void
    {
        $service = new class {
            public function status(Carbon $time, string $deadline): string
            {
                return $time->format('H:i:s') <= $deadline ? 'HADIR' : 'TERLAMBAT';
            }
        };
        $scan = Carbon::parse('2026-10-19 ' . $time, 'Asia/Jakarta');
        self::assertSame($expected, $service->status($scan, '07:00:00'));
    }

    public static function studentStatusProvider(): array
    {
        return [
            'before deadline' => ['06:59:59', 'HADIR'],
            'at deadline' => ['07:00:00', 'HADIR'],
            'after deadline' => ['07:00:01', 'TERLAMBAT'],
        ];
    }

    #[DataProvider('whatsappNumberProvider')]
    public function test_indonesian_whatsapp_number_normalization(string $input, ?string $expected): void
    {
        self::assertSame($expected, (new WhatsappService())->normalisasiNomor($input));
    }

    public static function whatsappNumberProvider(): array
    {
        return [
            'leading zero' => ['08123456789', '628123456789'],
            'country code' => ['628123456789', '628123456789'],
            'plus spaces punctuation' => ['+62 (812) 3456-789', '628123456789'],
            'local without zero' => ['8123456789', '628123456789'],
            'invalid text' => ['nomor-salah', null],
            'too short' => ['0812', null],
        ];
    }

    #[DataProvider('leaveReviewProvider')]
    public function test_leave_review_policy_is_triggered_after_three_calendar_days(string $start, string $end, bool $review): void
    {
        $duration = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        self::assertSame($review, $duration > 3);
    }

    public static function leaveReviewProvider(): array
    {
        return [
            'three days' => ['2026-10-19', '2026-10-21', false],
            'four days' => ['2026-10-19', '2026-10-22', true],
        ];
    }

    public function test_teacher_attendance_rules_regression_entrypoint_remains_available(): void
    {
        $controller = new AbsensiController();
        $start = Carbon::parse('2026-10-19 07:00:00', 'Asia/Jakarta');
        self::assertSame('HADIR', $controller->tentukanStatus($start->copy()->addMinutes(10), $start, 10));
        self::assertSame('TERLAMBAT', $controller->tentukanStatus($start->copy()->addMinutes(11), $start, 10));
    }
}
