<?php

namespace App\Imports;

use App\Models\Ayah;
use App\Models\Agama;
use App\Models\Ibu;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\KelolaSiswa;
use App\Models\KeteranganKeterima;
use App\Models\Pendidikan;
use App\Models\RiwayatPendidikan;
use App\Models\Siswa;
use App\Models\TahunAjar;
use App\Models\Tingkat;
use App\Models\Walmur;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * Normalize heading keys before both validation and model creation.
     * This keeps imports working if the app's cached Excel config uses the
     * raw heading formatter instead of the configured slug formatter.
     */
    public function prepareForValidation($row, $index)
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[Str::slug(trim((string) $key), '_')] = $value;
        }

        return $normalized;
    }

    /** Excel row one is the template header; import data starts on row two. */
    public function startRow(): int
    {
        return 2;
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // dd($row['kelas_keterima']);
        $kurikulumId = TahunAjar::where('id', session('tahun_ajar_id'))->value('kurikulum_id');

        // Kolom Kelas Keterima berisi nama_kelas persis seperti pada data master
        // (misalnya "A"); tingkat dan jurusan membedakan kelas dengan label sama.
        $kelas = Kelas::query()
            ->select('kelas.id', 'kelas.nama_kelas', 'kelas.tingkat_id', 'kelas.jurusan_id')
            ->join('tingkat', 'tingkat.id', '=', 'kelas.tingkat_id')
            ->join('jurusan', 'jurusan.id', '=', 'kelas.jurusan_id')
            ->where('kelas.kurlum_id', $kurikulumId)
            ->where('kelas.nama_kelas', $row['kelas_keterima'])
            ->where('tingkat.tingkat', $row['tingkat_keterima'])
            ->where('jurusan.nama_jurusan', $row['jurusan_keterima'])
            ->first();

        // Validasi kelas di sini bersama data master terkait, karena label
        // seperti "A" harus dicocokkan sebagai kelas.nama_kelas.
        if (!$kelas) {
            $labelKelas = $row['kelas_keterima'] ?? '';
            $pesan = $labelKelas === '' ? 'Nama kelas belum diisi. Isi kolom "Kelas Keterima" sesuai nama_kelas pada data master.'
                : 'Kelas "' . $labelKelas . '" tidak ditemukan untuk Tingkat "' . trim((string) ($row['tingkat_keterima'] ?? '')) . '" dan Jurusan "' . trim((string) ($row['jurusan_keterima'] ?? '')) . '" pada kurikulum aktif.';
            throw \Illuminate\Validation\ValidationException::withMessages([
                'kelas_keterima' => $pesan,
            ]);
        }

        $tingkat    = Tingkat::select('id')->where('id', $kelas->tingkat_id)->first();
        $jurusan    = Jurusan::select('id')->where('id', $kelas->jurusan_id)->first();
        $pendidikan = Pendidikan::select('id')->where('pendidikan', $row['tingkat_pendidikan'] ?? null)->first();
        if (!$pendidikan) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tingkat_pendidikan' => 'Tingkat pendidikan pada baris ini tidak ditemukan di data master. Periksa kolom "Tingkat Pendidikan" atau tambahkan pilihannya di menu Data Master.',
            ]);
        }
        $nik = trim($row['nik'], "'");

        $agamaNama = trim((string) ($row['agama'] ?? ''));
        $agamaNames = [mb_strtolower($agamaNama)];
        if (in_array(mb_strtolower($agamaNama), ['budha', 'buddha'], true)) {
            $agamaNames = ['budha', 'buddha'];
        }
        $agamaId = Agama::query()
            ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(agama)'), $agamaNames)
            ->value('id');

        if (!$agamaId) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'agama' => 'Agama pada file tidak ditemukan di daftar agama.',
            ]);
        }

        $siswa = Siswa::create([
            'kurikulum_id'      => $kurikulumId,
            'nama_lengkap'      => $row['nama_lengkap'],
            'tanggal_lahir'     => $this->normalizeExcelDate($row['tanggal_lahir'] ?? null, 'tanggal_lahir'),
            'tempat_lahir'      => $row['tempat_lahir'],
            'nisn'              => $row['nisn'],
            'nik'               => $nik,
            'nis'               => $row['nis'],
            'alamat'            => $row['alamat'],
            'jenis_kelamin'     => $row['jenis_kelamin'],
            'agama'             => $agamaId,
            'foto'              => NULL,
            'status'            => 'aktif'
        ]);

        Ayah::create([
            'siswa_id'      => $siswa->id,
            'nama_ayah'     => $row['nama_ayah'],
            'alamat_ayah'   => $row['alamat_orang_tua']
        ]);

        Ibu::create([
            'siswa_id'      => $siswa->id,
            'nama_ibu'      => $row['nama_ibu']
        ]);

        Walmur::create([
            'siswa_id'      => $siswa->id,
            'nama_walmur'   => $row['nama_wali'] ?? null,
            'alamat_walmur' => $row['alamat_wali'] ?? null,
        ]);

        RiwayatPendidikan::create([
            'siswa_id'       => $siswa->id,
            'pendidikan_id'  => $pendidikan->id,
            'nama_sekolah'   => $row['nama_sekolah_asal'],
            'alamat_sekolah' => $row['alamat_sekolah_asal'],
            'tahun_lulus'    => $row['tahun_lulus'],
            'no_ijazah'      => $row['nomer_ijazah'],
            'tanggal_ijazah' => $this->normalizeExcelDate($row['tanggal_ijazah'] ?? null, 'tanggal_ijazah'),
            'lama_belajar'   => $row['lama_belajar']
        ]);

        KelolaSiswa::create([
            'siswa_id'      => $siswa->id,
            'kelas_id'      => $kelas->id,
            'tahun_ajar_id' => session('tahun_ajar_id'),
        ]);

        KeteranganKeterima::create([
            'siswa_id'         => $siswa->id,
            'tingkat_id'       => $tingkat->id,
            'kelas_id'         => $kelas->id,
            'jurusan_id'       => $jurusan->id,
            'tahun_ajar_id'    => session('tahun_ajar_id'),
            'tanggal_keterima' => $this->normalizeExcelDate($row['tanggal_keterima'] ?? null, 'tanggal_keterima')
        ]);

        return $siswa;
    }

    private function normalizeExcelDate($value, string $field): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        if ($value === null) {
            return null;
        }

        $dateText = trim((string) $value);
        if ($dateText === '' || in_array(mb_strtolower($dateText), ['-', '—', '–', 'n/a', 'na'], true)) {
            return null;
        }

        if (is_numeric($value) && (float) $value >= 1) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        foreach (['!Y-m-d', '!d/m/Y', '!d-m-Y', '!d.m.Y', '!Y/m/d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateText);
                $errors = Carbon::getLastErrors();
                if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $exception) {
                // Coba format tanggal teks berikutnya.
            }
        }

        try {
            return Carbon::parse($dateText)->format('Y-m-d');
        } catch (\Throwable $exception) {
            $label = match ($field) {
                'tanggal_lahir' => 'Tanggal Lahir',
                'tanggal_ijazah' => 'Tanggal Ijazah',
                'tanggal_keterima' => 'Tanggal Keterima',
                default => $field,
            };

            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => 'Format tanggal pada kolom "' . $label . '" tidak dikenali. Gunakan tanggal Excel atau teks berformat YYYY-MM-DD (contoh: 2010-08-17) atau DD/MM/YYYY (contoh: 17/08/2010).',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nama_lengkap'      => 'required',
            'tanggal_lahir'     => 'required',
            'agama'             => 'required',
            'tanggal_ijazah'    => [function ($attribute, $value, $fail) {
                if (is_string($value) && in_array(mb_strtolower(trim($value)), ['-', '—', '–'], true)) {
                    $fail('Tanggal Ijazah pada file berisi tanda "-". Isi tanggal ijazah yang valid, misalnya 2010-08-17 atau 17/08/2010, lalu impor kembali.');
                }
            }],
            // 'tanggal_lahir'     => 'required|date',
            // 'tempat_lahir'      => 'required',
            // 'nisn'              => 'required|unique:siswa,nisn',
            // 'nik'               => 'required|unique:siswa,nik',
            // 'nis'               => 'required|unique:siswa,nis',
            // 'alamat'            => 'required',
            // 'jenis_kelamin'     => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            // 'agama'             => ['required', Rule::in(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])],
            // 'tingkat_keterima'  => ['required', Rule::exists('tingkat', 'tingkat')],
            'tingkat_keterima'  => ['required', Rule::exists('tingkat', 'tingkat')],
            // Kelas dicocokkan di model dengan kelas.nama_kelas serta tingkat,
            // jurusan, dan kurikulum; jangan menolak label kelas di sini.
            'jurusan_keterima'  => ['required', Rule::exists('jurusan', 'nama_jurusan')],
            'tingkat_pendidikan' => ['required', Rule::exists('pendidikan', 'pendidikan')],
            // 'jurusan_keterima'  => ['required', Rule::exists('jurusan', 'nama_jurusan')],
            // 'tanggal_keterima'  => 'required|date',
            // 'tingkat_pendidikan' => ['required', Rule::exists('pendidikan', 'pendidikan')],
            // 'nama_sekolah_asal' => 'required',
            // 'alamat_sekolah_asal' => 'required',
            // 'tahun_lulus'       => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            // 'nomer_ijazah'      => 'required',
            // 'tanggal_ijazah'    => 'required|date',
            // 'lama_belajar'      => 'required|integer|min:1|max:20',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap belum diisi. Mohon isi nama siswa pada kolom "Nama Lengkap".',
            'tanggal_lahir.required' => 'Tanggal lahir belum diisi. Isi tanggal yang valid, misalnya 2010-08-17 atau 17/08/2010.',
            'agama.required' => 'Agama belum diisi. Pilih nama agama pada kolom "Agama".',
            'tingkat_keterima.required' => 'Tingkat masuk belum diisi. Isi kolom "Tingkat Keterima".',
            'tingkat_keterima.exists' => 'Tingkat pada kolom "Tingkat Keterima" tidak ditemukan di data master. Periksa ejaan atau tambahkan tingkat tersebut terlebih dahulu.',
            'jurusan_keterima.required' => 'Jurusan masuk belum diisi. Isi kolom "Jurusan Keterima".',
            'jurusan_keterima.exists' => 'Jurusan pada kolom "Jurusan Keterima" tidak ditemukan di data master. Periksa ejaan atau tambahkan jurusan tersebut terlebih dahulu.',
            'tingkat_pendidikan.required' => 'Tingkat pendidikan asal belum diisi. Isi kolom "Tingkat Pendidikan".',
            'tingkat_pendidikan.exists' => 'Tingkat pendidikan asal tidak ditemukan di data master. Periksa kolom "Tingkat Pendidikan" atau tambahkan pilihannya di menu Data Master.',
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'nama_lengkap' => 'Nama Lengkap',
            'tanggal_lahir' => 'Tanggal Lahir',
            'agama' => 'Agama',
            'tingkat_keterima' => 'Tingkat Keterima',
            'kelas_keterima' => 'Kelas Keterima',
            'jurusan_keterima' => 'Jurusan Keterima',
            'tingkat_pendidikan' => 'Tingkat Pendidikan',
            'tanggal_ijazah' => 'Tanggal Ijazah',
        ];
    }
}
