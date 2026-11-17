<?php

namespace Tests\Unit;

use App\Models\Guru;
use App\Models\Siswa;
use App\Services\CardIdentityFields;
use App\Services\CardIdentityImage;
use App\Services\CardIdentityPrintPlan;
use PHPUnit\Framework\Attributes\DataProvider;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tests\TestCase;

class CardIdentityTest extends TestCase
{
    public function test_student_identity_qr_can_be_generated_without_personal_details(): void
    {
        $svg = QrCode::format('svg')->size(180)->margin(0)->generate('shaf-siswa:internal-id');

        self::assertStringContainsString('<svg', (string) $svg);
    }

    public function test_print_sheets_pair_same_owners_on_front_and_back_pages(): void
    {
        self::assertSame([
            ['sisi' => 'depan', 'ids' => ['pelajar-1', 'pelajar-2']],
            ['sisi' => 'belakang', 'ids' => ['pelajar-1', 'pelajar-2']],
        ], CardIdentityPrintPlan::forSheets(['pelajar-1', 'pelajar-2'], 'pelajar', true));
    }

    public function test_guru_without_back_template_prints_front_side_only(): void
    {
        self::assertSame([['sisi' => 'depan', 'ids' => ['guru-1']]], CardIdentityPrintPlan::forSheets(['guru-1'], 'guru', false));
    }

    #[DataProvider('starterTemplateProvider')]
    public function test_supplied_template_assets_are_valid_portrait_images(string $relativePath, string $extension): void
    {
        $path = base_path($relativePath);
        self::assertFileExists($path);
        self::assertNull(CardIdentityImage::validationError($path, $extension, (int) filesize($path)));
    }

    public static function starterTemplateProvider(): array
    {
        return [
            'student front' => ['public/dokumentasi/template-kartu/Kartu pelajar — depan.jpg', 'jpg'],
            'student back' => ['public/dokumentasi/template-kartu/Kartu pelajar — belakang.jpg', 'jpg'],
            'teacher front' => ['public/dokumentasi/template-kartu/Kartu guru — depan.png', 'png'],
        ];
    }

    public function test_image_validation_rejects_wrong_extension_and_oversized_files(): void
    {
        $asset = base_path('public/dokumentasi/template-kartu/Kartu pelajar — depan.jpg');

        self::assertNotNull(CardIdentityImage::validationError($asset, 'svg', (int) filesize($asset)));
        self::assertNotNull(CardIdentityImage::validationError($asset, 'jpg', 5 * 1024 * 1024 + 1));
    }

    public function test_image_validation_rejects_invalid_card_dimensions(): void
    {
        $image = imagecreatetruecolor(800, 400);
        $path = tempnam(sys_get_temp_dir(), 'card-template-');
        imagepng($image, $path);
        imagedestroy($image);

        try {
            self::assertNotNull(CardIdentityImage::validationError($path, 'png', (int) filesize($path)));
        } finally {
            unlink($path);
        }
    }

    public function test_missing_student_fields_and_photo_use_safe_placeholders(): void
    {
        $student = new Siswa;
        $student->setAttribute('nama_lengkap', '');
        $student->setAttribute('nis', null);
        $student->setAttribute('nisn', null);
        $student->setAttribute('kartu_kelas', null);
        $student->setAttribute('foto', null);

        self::assertSame([
            'name' => 'Nama belum tersedia',
            'id' => null,
            'secondary_id' => null,
            'detail' => 'Kelas belum tersedia',
            'photo' => null,
            'missing' => ['name', 'id', 'secondary_id', 'detail', 'photo'],
        ], CardIdentityFields::map('pelajar', $student));
    }

    public function test_missing_teacher_fields_and_photo_use_safe_placeholders(): void
    {
        $teacher = new Guru;
        $teacher->setAttribute('nama_guru', null);
        $teacher->setAttribute('nip', null);
        $teacher->setAttribute('foto_pegawai', null);
        $teacher->setRelation('namaJabatan', null);

        self::assertSame([
            'name' => 'Nama belum tersedia',
            'id' => null,
            'secondary_id' => null,
            'detail' => 'Jabatan belum tersedia',
            'photo' => null,
            'missing' => ['name', 'id', 'detail', 'photo'],
        ], CardIdentityFields::map('guru', $teacher));
    }
}
