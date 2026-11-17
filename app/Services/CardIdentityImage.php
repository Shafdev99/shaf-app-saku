<?php

namespace App\Services;

class CardIdentityImage
{
    public static function validationError(string $path, string $extension, int $bytes): ?string
    {
        $extension = strtolower($extension);
        if (! in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return 'Ekstensi file harus JPG, JPEG, atau PNG.';
        }
        if ($bytes < 1 || $bytes > 5 * 1024 * 1024) {
            return 'Ukuran template maksimal 5 MB.';
        }

        if (! is_file($path)) {
            return 'File template tidak dapat dibaca.';
        }
        $image = @getimagesize($path);
        $mime = $image['mime'] ?? null;
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $contentMime = $fileInfo->file($path);
        $expectedMime = in_array($extension, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/png';
        if (! $image || $mime !== $expectedMime || $contentMime !== $expectedMime) {
            return 'Isi file bukan gambar JPG/JPEG/PNG yang valid.';
        }

        [$width, $height] = $image;
        $ratio = $width / max(1, $height);
        if ($width < 300 || $height < 500 || $width > 3000 || $height > 5100 || abs($ratio - (590 / 1004)) > 0.02) {
            return 'Dimensi harus minimal 300 × 500 px, maksimal 3000 × 5100 px, dengan rasio kartu potret yang sesuai.';
        }

        return null;
    }
}
