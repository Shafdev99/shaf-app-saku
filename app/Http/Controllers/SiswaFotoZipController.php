<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class SiswaFotoZipController extends Controller
{
    private const MAX_ARCHIVE_KB = 102400;
    private const MAX_PHOTOS = 500;
    private const MAX_PHOTO_BYTES = 5 * 1024 * 1024;
    private const MAX_TOTAL_BYTES = 100 * 1024 * 1024;

    public function store(Request $request)
    {
        $request->validate([
            'foto_zip' => ['required', 'file', 'extensions:zip', 'max:' . self::MAX_ARCHIVE_KB],
        ], [
            'foto_zip.required' => 'Pilih file ZIP foto siswa.',
            'foto_zip.extensions' => 'File harus berformat .zip.',
            'foto_zip.max' => 'Ukuran ZIP maksimal 100 MB.',
        ]);

        if (!class_exists(ZipArchive::class)) {
            return back()->withErrors(['foto_zip' => 'Fitur ZIP tidak tersedia karena ekstensi PHP ZipArchive belum aktif.']);
        }

        $zip = new ZipArchive();
        if ($zip->open($request->file('foto_zip')->getRealPath()) !== true) {
            return back()->withErrors(['foto_zip' => 'File ZIP rusak atau tidak dapat dibuka.']);
        }

        try {
            [$photos, $warnings, $archiveErrors] = $this->inspectArchive($zip);
            if ($archiveErrors !== []) {
                return back()->withErrors(['foto_zip' => implode(' ', $archiveErrors)]);
            }

            $nisnValues = [];
            foreach ($photos as $photo) {
                $nisnValues[] = $photo['display_nisn'];
                $nisnValues[] = $photo['nisn'];
            }
            $nisnValues = array_values(array_unique($nisnValues));
            $students = Siswa::query()->whereIn('nisn', $nisnValues)->get(['id', 'nisn', 'foto']);
            $studentsByNisn = [];
            foreach ($students as $student) {
                $studentsByNisn[$this->normalizeNisn($student->nisn)][] = $student;
            }

            foreach ($photos as $index => &$photo) {
                $matches = $studentsByNisn[$photo['nisn']] ?? [];
                if (count($matches) !== 1) {
                    $warnings[] = count($matches) === 0
                        ? 'Tidak ada siswa dengan NISN ' . $photo['display_nisn'] . '.'
                        : 'NISN ' . $photo['display_nisn'] . ' cocok ke lebih dari satu siswa; foto dilewati.';
                    unset($photos[$index]);
                    continue;
                }

                $photo['student_id'] = $matches[0]->id;
                $photo['old_photo'] = $matches[0]->foto;
            }
            unset($photo);

            $photos = array_values($photos);
            if ($photos === []) {
                return back()->with('foto_zip_warnings', $this->limitedWarnings($warnings ?: ['Tidak ada foto yang dapat dipasangkan dengan data siswa.']));
            }

            $newPaths = [];
            try {
                DB::transaction(function () use ($zip, $photos, &$newPaths): void {
                    foreach ($photos as $photo) {
                        $stream = $zip->getStream($photo['entry']);
                        if ($stream === false) {
                            throw new RuntimeException('Tidak dapat membaca ' . $photo['entry'] . ' dari ZIP.');
                        }

                        try {
                            $contents = stream_get_contents($stream, self::MAX_PHOTO_BYTES + 1);
                        } finally {
                            fclose($stream);
                        }

                        if ($contents === false || strlen($contents) > self::MAX_PHOTO_BYTES) {
                            throw new RuntimeException('Ukuran foto berubah atau melebihi batas saat diproses.');
                        }

                        $path = 'image/siswa/awal/' . Str::uuid() . '.' . $photo['extension'];
                        if (!Storage::put($path, $contents)) {
                            throw new RuntimeException('Gagal menyimpan salah satu file foto.');
                        }
                        $newPaths[] = $path;

                        Siswa::whereKey($photo['student_id'])->update(['foto' => $path]);
                    }
                });
            } catch (Throwable $exception) {
                Storage::delete($newPaths);
                report($exception);

                return back()->withErrors(['foto_zip' => 'Foto gagal disimpan. Tidak ada data siswa yang diperbarui; periksa log aplikasi jika masalah berulang.']);
            }

            $oldPaths = array_values(array_unique(array_filter(array_column($photos, 'old_photo'))));
            foreach ($oldPaths as $oldPath) {
                if (!Siswa::where('foto', $oldPath)->exists()) {
                    Storage::delete($oldPath);
                }
            }

            $response = redirect()->route('siswa')->with('sukses', count($photos) . ' foto siswa berhasil diunggah dan dipasangkan berdasarkan NISN.');
            if ($warnings !== []) {
                $response->with('foto_zip_warnings', $this->limitedWarnings($warnings));
            }

            return $response;
        } finally {
            $zip->close();
        }
    }

    private function inspectArchive(ZipArchive $zip): array
    {
        $photos = [];
        $warnings = [];
        $archiveErrors = [];
        $seenNisn = [];
        $totalBytes = 0;

        if ($zip->numFiles > self::MAX_PHOTOS + 100) {
            return [[], [], ['ZIP berisi terlalu banyak item. Maksimal 500 foto.']];
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            $entry = $stat['name'] ?? '';

            if ($entry === '' || str_ends_with($entry, '/')) {
                continue;
            }

            if (str_starts_with($entry, '__MACOSX/') || basename($entry) === '.DS_Store') {
                continue;
            }

            if (str_contains($entry, '/') || str_contains($entry, '\\')) {
                $warnings[] = 'File ' . basename($entry) . ' berada di subfolder dan dilewati.';
                continue;
            }

            if (!preg_match('/^(\d{10})\.(jpg|jpeg|png)$/i', $entry, $matches)) {
                $warnings[] = 'Nama file ' . $entry . ' bukan NISN 10 digit dengan ekstensi JPG/JPEG/PNG; file dilewati.';
                continue;
            }

            $nisn = $this->normalizeNisn($matches[1]);
            if (isset($seenNisn[$nisn])) {
                $warnings[] = 'Ada lebih dari satu foto untuk NISN ' . $matches[1] . '; foto duplikat dilewati.';
                continue;
            }

            $size = (int) ($stat['size'] ?? 0);
            $totalBytes += $size;
            if ($size < 1 || $size > self::MAX_PHOTO_BYTES) {
                $warnings[] = 'Ukuran ' . $entry . ' tidak valid atau melebihi 5 MB; foto dilewati.';
                continue;
            }
            if ($totalBytes > self::MAX_TOTAL_BYTES) {
                $warnings[] = 'Batas total 100 MB tercapai; sisa foto di dalam ZIP dilewati.';
                break;
            }

            if (count($photos) >= self::MAX_PHOTOS) {
                $warnings[] = 'Batas 500 foto tercapai; sisa foto di dalam ZIP dilewati.';
                break;
            }

            $stream = $zip->getStream($entry);
            if ($stream === false) {
                $warnings[] = 'Foto ' . $entry . ' tidak dapat dibaca dan dilewati.';
                continue;
            }
            try {
                $contents = stream_get_contents($stream, self::MAX_PHOTO_BYTES + 1);
            } finally {
                fclose($stream);
            }

            $image = $contents === false ? false : getimagesizefromstring($contents);
            $expectedType = in_array(strtolower($matches[2]), ['jpg', 'jpeg'], true) ? IMAGETYPE_JPEG : IMAGETYPE_PNG;
            if (!$image || $image[2] !== $expectedType || (($image[0] * $image[1]) > 25000000)) {
                $warnings[] = 'Isi file ' . $entry . ' bukan gambar yang valid atau dimensinya terlalu besar; foto dilewati.';
                continue;
            }

            $photos[] = [
                'entry' => $entry,
                'nisn' => $nisn,
                'display_nisn' => $matches[1],
                'extension' => strtolower($matches[2]),
            ];
            $seenNisn[$nisn] = true;
        }

        if ($photos === [] && $warnings === []) {
            $warnings[] = 'ZIP tidak berisi foto dengan nama file sesuai NISN.';
        }

        return [$photos, $warnings, $archiveErrors];
    }

    private function normalizeNisn($value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return ltrim($digits, '0') ?: '0';
    }

    private function limitedWarnings(array $warnings): array
    {
        $shown = array_slice($warnings, 0, 20);
        if (count($warnings) > count($shown)) {
            $shown[] = 'Dan ' . (count($warnings) - count($shown)) . ' kendala lainnya.';
        }

        return $shown;
    }
}
