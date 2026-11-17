<?php

namespace App\Imports;

use App\Models\Guru;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PegawaiImport implements ToModel, SkipsEmptyRows, WithHeadingRow, WithValidation
{
    use Importable;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $statusPegawai = DB::table('status_pegawai')->where('status_pegawai', $row['status_kepegawaian'])->value('id');
        $jabatan = DB::table('jabatan')
            ->where('nama_jabatan', $row['jabatan'])
            ->orWhere('ket_jabatan', $row['jabatan'])->value('id');

        $pegawai = Guru::create([
            'nama_guru'    => $row['nama_lengkap'],
            'jabatan'      => $jabatan,
            'no_hp'        => $row['no_hp'],
            'nip'          => $row['nipnipy'],
            'status_guru'  => $statusPegawai,
            'status_akun'  => 'Aktif',
            'foto_pegawai' => NULL,
        ]);

        return $pegawai;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap'       => 'required',
            'jabatan'            => ['required', Rule::exists('jabatan', 'nama_jabatan')],
            'nip/nipy'           => 'nullable|unique:guru,nip',
            'status_kepegawaian' => ['required', Rule::exists('status_pegawai', 'status_pegawai')],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'nip/nipy.required' => 'NIP/NIYP wajib diisi.',
            'status_kepegawaian.required' => 'Status kepegawaian wajib diisi.',
            'status_kepegawaian.exists' => 'Status kepegawaian tidak sesuai anjuran!.',
            'jabatan.exists' => 'Jabatan tidak sesuai anjuran!.',
        ];
    }
}
