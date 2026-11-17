<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $token;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token = config('services.fonnte.token', env('FONNTE_TOKEN'));
        $this->apiUrl = 'https://api.fonnte.com/send';
    }

    /**
     * Mengirim pesan text via Fonnte Gateway
     */
    public function kirimPesan(string $noHp, string $pesan): bool
    {
        $normalized = $this->normalisasiNomor($noHp);
        if ($normalized === null) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->timeout(10)->post($this->apiUrl, [
                'target' => $normalized,
                'message' => $pesan,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Fonnte menolak pengiriman pesan WhatsApp.', ['status' => $response->status()]);
            return false;
        } catch (\Exception $e) {
            Log::warning('Koneksi WhatsApp gagal.', ['exception' => get_class($e)]);
            return false;
        }
    }

    public function normalisasiNomor(string $nomor): ?string
    {
        $digits = preg_replace('/\D+/', '', trim($nomor));
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return preg_match('/^62[0-9]{8,13}$/', $digits) ? $digits : null;
    }

    public function kirimNotifikasiKetidakhadiranSiswa(string $noHp, string $namaSiswa, string $tanggal): bool
    {
        $pesan = "Yth. Bapak/Ibu, kami belum menerima catatan kehadiran {$namaSiswa} pada {$tanggal}. Jika siswa sudah hadir, silakan abaikan pesan ini atau hubungi pihak sekolah.";

        return $this->kirimPesan($noHp, $pesan);
    }

    /**
     * Template khusus pesan presensi
     */
    public function kirimNotifikasiPresensi(string $noHp, string $namaGuru, string $tipe, string $jam, string $status = ''): void
    {
        $statusText = $tipe === 'masuk' ? '🟢 *PRESENSI MASUK*' : '🔴 *PRESENSI PULANG*';

        $pesan = "Yth. Bapak/Ibu *{$namaGuru}*,\n\n"
            . "Sistem mencatat kehadiran Anda pada hari ini:\n"
            . "Status: {$statusText}\n"
            . "Jam Tap: *{$jam} WIB*\n";

        if ($tipe === 'masuk' && $status !== '') {
            $pesan .= "Keterangan: *{$status}*\n";
        }

        $pesan .= "\nTerima kasih atas dedikasinya hari ini. Selamat bertugas! 🙏";

        // Eksekusi kirim pesan secara mandiri tanpa mengganggu response utama
        $this->kirimPesan($noHp, $pesan);
    }
}
