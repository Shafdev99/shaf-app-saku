<?php

namespace App\Http\Controllers;

use App\Exports\templateImportPegawai;
use App\Http\Controllers\Controller;
use App\Imports\PegawaiImport;
use App\Models\Guru;
use App\Models\Jabatan;
use App\Models\Siswa;
use App\Models\StatusPegawai;
use App\Models\UserGuru;
use App\Models\UserKepsek;
use App\Models\UserStaff;
use App\Services\AttendanceCardRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;


class GuruController extends Controller
{
    /** 
     * Tenaga Pendidik 
     */
    //======== Index Tenaga Pendidik ========//
    public function tenagaPendidik()
    {
        return view('tenaga-pendidik.index', [
            'guru'         => Guru::getPegawai(request(['nama_pegawai', 'status_kartu']))
                ->paginate(10)
                ->withQueryString(),
            'status_kartu' => ['Semua', 'Sudah Tertaut', 'Belum Tertaut'],
            'spg'          => DB::table('status_pegawai')->first(),
            'stapeg'       => DB::table('status_pegawai')->select('id', 'status_pegawai', 'ket_status_pegawai')->get(),
            'jabatan'      => DB::table('jabatan')->select('id', 'nama_jabatan', 'ket_jabatan')->get()
        ]);
    }

    public function tambahTenagaPendidik()
    {
        return view('tenaga-pendidik.add', [
            'stapeg'    => DB::table('status_pegawai')->select('id', 'status_pegawai', 'ket_status_pegawai')->get(),
            'jabatan'  => DB::table('jabatan')->select('id', 'nama_jabatan', 'ket_jabatan')->get()
        ]);
    }

    //======== Store Tenaga Pendidik ========//
    public function storeTenagaPendidik()
    {
        $data = request()->validate([
            'nama_guru'     => 'required',
            'no_hp'         => 'required|unique:guru|min:1|max:15',
            'nip'           => 'required|unique:guru|min:1|max:18',
            'jabatan'       => 'required',
            'status_guru'   => 'required',
            'foto_pegawai'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'required'      => ':attribute wajib diisi!',
            'max'           => 'Data tenaga pendidik diisi maksimal :max karakter!',
            'min'           => 'Data tenaga pendidik diisi minimal :min karakter!',
            'no_hp.unique'  => 'No HP tersebut sudah digunakan!',
            'nip.unique'    => 'NIP tersebut sudah digunakan!'
        ]);

        // Upload foto
        if (request()->file('foto_pegawai')) {
            $data['foto_pegawai'] = request()->file('foto_pegawai')->store('image/tenaga-pendidik');
        }
        $data['status_akun'] = 'Aktif';

        $pegawai_id = Guru::create($data);

        return redirect()->route('tenaga.pendidik.tautkan.kartu', ['id' => $pegawai_id->id, 'trigger' => 'tambah'])->with('sukses', 'Data tenaga pendidik berhasil ditambahkan! Silahkan tautkan kartu untuk tenaga pendidik ini.');
    }

    public function editTenagaPendidik($id)
    {
        $guru = Guru::findOrFail($id);

        return view('tenaga-pendidik.edit', [
            'guru' => $guru,
            'stapeg' => DB::table('status_pegawai')->select('id', 'status_pegawai', 'ket_status_pegawai')->get(),
            'jabatan'  => DB::table('jabatan')->select('id', 'nama_jabatan', 'ket_jabatan')->get()
        ]);
    }

    //======== Update Data tenaga pendidik ========//
    public function updateTenagaPendidik($id)
    {
        $data = request()->validate([
            'nama_guru'     => 'required',
            'no_hp'         => 'required|unique:guru,no_hp,' . $id . '|min:1|max:15',
            'nip'           => 'required|unique:guru,nip,' . $id . '|min:1|max:18',
            'jabatan'       => 'required',
            'status_guru'   => 'required',
            'foto_pegawai'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'required'       => ':attribute wajib diisi!',
            'max'            => 'Data tenaga pendidik diisi maksimal :max karakter!',
            'min'            => 'Data tenaga pendidik diisi minimal :min karakter!',
            'no_hp.unique'   => 'No HP tenaga pendidik tersebut sudah digunakan!',
            'nip.unique'     => 'NIP tenaga pendidik tersebut sudah digunakan!'
        ]);

        $guru = Guru::select('foto_pegawai')->where('id', $id)->first();
        // Upload foto
        if (request()->file('foto_pegawai')) {

            // Jika terdapat foto siswa tersimpan di database
            if ($guru->foto_pegawai) {

                // Maka hapus foto yang lama
                Storage::delete($guru->foto_pegawai);
            }
            $data['foto_pegawai'] = request()->file('foto_pegawai')->store('image/tenaga-pendidik');
        }

        Guru::where('id', $id)->update($data);

        // Alihkan ke halaman tenaga pendidik
        return redirect()
            ->route('tenaga.pendidik', request()->query())
            ->with('sukses', 'Data tenaga pendidik berhasil diperbarui!');
    }

    //======== Delete Data tenaga pendidik ========//
    public function delTenagaPendidik()
    {
        $guru = Guru::select('foto_pegawai')->where('id', request()->guru_id)->first();
        UserGuru::where('guru_id', request()->guru_id)->exists() ? UserGuru::where('guru_id', request()->guru_id)->delete() : null;
        UserStaff::where('staff_id', request()->guru_id)->exists() ? UserStaff::where('staff_id', request()->guru_id)->delete() : null;
        UserKepsek::where('kepsek_id', request()->guru_id)->exists() ? UserKepsek::where('kepsek_id', request()->guru_id)->delete() : null;
        // Jika terdapat foto pegawai tersimpan di database
        if ($guru?->foto_pegawai) {

            // Maka hapus foto yang lama
            Storage::delete($guru->foto_pegawai);
        }
        Guru::destroy(request()->guru_id);

        // Alihkan ke halaman tenaga pendidik
        return redirect()->route('tenaga.pendidik', request()->query())->with('sukses', 'Data tenaga pendidik berhasil dihapus!');
    }

    public function aktivasiTenagaPendidik()
    {
        $data = request()->validate([
            'guru_id' => ['required', 'uuid', 'exists:guru,id'],
        ]);
        $guru = Guru::select('status_akun')->where('id', $data['guru_id'])->firstOrFail();
        $status = $guru->status_akun === 'Aktif' ? 'Nonaktif' : 'Aktif';
        Guru::where('id', $data['guru_id'])->update(['status_akun' => $status]);
        return redirect()->route('tenaga.pendidik', request()->query())->with('sukses', 'Status tenaga pendidik berhasil diperbarui!');
    }

    public function aktivasiTenagaPendidikTerpilih()
    {
        $data = request()->validate([
            'guru_id_arr' => ['required', 'array', 'min:1'],
            'guru_id_arr.*' => ['required', 'string'],
        ], [
            'guru_id_arr.required' => 'Pilih minimal satu tenaga pendidik.',
            'guru_id_arr.min' => 'Pilih minimal satu tenaga pendidik.',
        ]);

        // The current selection UI submits comma-separated IDs in one hidden
        // input. Normalize it while also supporting multiple hidden inputs.
        $ids = collect($data['guru_id_arr'])
            ->flatMap(fn ($value) => explode(',', $value))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $ids = Validator::make(['guru_id_arr' => $ids], [
            'guru_id_arr' => ['required', 'array', 'min:1'],
            'guru_id_arr.*' => ['required', 'uuid', 'exists:guru,id'],
        ], [
            'guru_id_arr.*.exists' => 'Salah satu tenaga pendidik yang dipilih sudah tidak tersedia. Muat ulang halaman lalu coba lagi.',
        ])->validate()['guru_id_arr'];

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $id) {
                $guru = Guru::query()->whereKey($id)->lockForUpdate()->firstOrFail();
                $guru->update([
                    'status_akun' => $guru->status_akun === 'Aktif' ? 'Nonaktif' : 'Aktif',
                ]);
            }
        });

        return redirect()->route('tenaga.pendidik', request()->query())->with('sukses', 'Status tenaga pendidik berhasil diperbarui!');
    }

    public function tautkanKartu($id, $trigger = null)
    {
        // Implementation for linking card
        return view('tenaga-pendidik.tautkan-kartu', [
            'trigger' => $trigger,
            'tenaga_pendidik' => Guru::with('namaJabatan', 'statusPegawai')->where('id', $id)->first(),
        ]);
    }

    public function resetKartu($id, AttendanceCardRegistry $registry)
    {
        $guru = Guru::findOrFail($id);
        DB::transaction(function () use ($guru, $registry): void {
            $registry->release('guru', $guru->id);
            $guru->update(['card_id' => null]);
        });
        return redirect()->route('tenaga.pendidik', request()->query())->with('sukses', 'Id Card berhasil direset!');
    }

    public function prosesTautkanKartu(Request $request, AttendanceCardRegistry $registry)
    {
        $data = $request->validate([
            'tenaga_pendidik_id' => ['required', 'uuid', 'exists:guru,id'],
            'card_id' => ['required', 'string', 'max:100', 'unique:guru,card_id', 'unique:siswa,card_id'],
        ], [
            'required' => ':attribute wajib diisi!',
            'unique' => 'Kartu tersebut sudah tertaut ke siswa atau tenaga pendidik lain.'
        ]);

        if (Siswa::where('card_id', $data['card_id'])->exists()) {
            throw ValidationException::withMessages(['card_id' => 'Kartu tersebut sudah tertaut ke siswa.']);
        }

        DB::transaction(function () use ($data, $registry): void {
            $guru = Guru::whereKey($data['tenaga_pendidik_id'])->lockForUpdate()->firstOrFail();
            if ($guru->card_id) {
                $registry->release('guru', $guru->id);
            }
            $registry->claim($data['card_id'], 'guru', $guru->id);
            $guru->update(['card_id' => $data['card_id']]);
        });

        return redirect()->route('tenaga.pendidik', $request->query())->with('sukses', 'Kartu berhasil ditautkan ke tenaga pendidik!');
    }

    public function hapusPegawaiTerpilih()
    {

        // Ambil ID Siswa berupa string array dari form
        $pegawai = request()->pegawai_id;

        // Gabungkan string array
        $id = implode(",", $pegawai);

        // Dan pecah jadi array murni
        $pegawai_id = explode(",", $id);

        // Proses menghapus berulang berdasarkan ID
        foreach ($pegawai_id as $id) {
            $this->prosesHapusTenagaPendidik($id);
        }

        return redirect()->route('tenaga.pendidik', request()->query())->with('sukses', 'Data tenaga pendidik berhasil dihapus!');
    }

    //======== Delete Data tenaga pendidik ========//
    public function prosesHapusTenagaPendidik($id)
    {
        $guru = Guru::select('foto_pegawai')->where('id', $id)->first();
        UserGuru::where('guru_id', $id)->exists() ? UserGuru::where('guru_id', $id)->delete() : null;
        UserStaff::where('staff_id', $id)->exists() ? UserStaff::where('staff_id', $id)->delete() : null;
        UserKepsek::where('kepsek_id', $id)->exists() ? UserKepsek::where('kepsek_id', $id)->delete() : null;
        // Jika terdapat foto pegawai tersimpan di database
        if ($guru->foto_pegawai) {

            // Maka hapus foto yang lama
            Storage::delete($guru->foto_pegawai);
        }
        Guru::destroy($id);

        return true;
    }

    public function cekValidasiGuru($guruId)
    {
        $cek = Guru::cekValidasiGuru($guruId);
        return response()->json($cek);
    }

    public function cekValidasiGuruArr($arrId)
    { // Ambil ID Siswa berupa string array dari form
        // $pegawai = $arrId;

        // Gabungkan string array
        // $id = implode(",", $arrId);

        // Dan pecah jadi array murni
        $pegawai_id = explode(",", $arrId);

        $cek = Guru::cekValidasiGuruArr($pegawai_id);
        return response()->json($cek);
    }

    //======== Download Template Import Excel Data Pegawai ========//
    public function templateImportPegawai()
    {
        // Proses download file template import data pegawai
        return Excel::download(new templateImportPegawai, 'Template_Import_Data_Pegawai.xlsx');
    }

    public function importTenagaPendidik()
    {
        // Proses import data pegawai dari file excel
        // Implementasi import data pegawai

        // Mengambil data file dari form
        $file = request()->file('importFile');

        // Memberikan nama baru pada file yang diambil
        $namaFile = $file->getClientOriginalName();

        // Proses upload file 
        $file->move('import', $namaFile);

        // Memasukan data ke database
        Excel::import(new PegawaiImport, public_path('/import/' . $namaFile));
        return redirect()->route('tenaga.pendidik', request()->query())->with('sukses', 'Data berhasil di Import!');
    }



    /** 
     * Status Kepegawaian
     */
    public function statusKepegawaian()
    {
        return view('tenaga-pendidik.status-kepegawaian', [
            'stapeg'  => DB::table('status_pegawai')
                ->select(
                    'id',
                    'status_pegawai',
                    'ket_status_pegawai'
                )
                ->where('status_pegawai', 'like', '%' . request('status_pegawai') . '%')
                ->when(request('status_pegawai'), function ($query) {
                    $query->where('status_pegawai', 'like', '%' . request('status_pegawai') . '%')
                        ->orWhere('ket_status_pegawai', 'like', '%' . request('status_pegawai') . '%');
                })
                ->paginate(10)
                ->withQueryString(),
        ]);
    }
    //======== Add Data Status Kepegawaian ========//
    public function addStaPeg()
    {
        $data = request()->validate([
            'status_pegawai'     => 'required|unique:status_pegawai',
            'ket_status_pegawai' => 'required|unique:status_pegawai',
        ], [
            'required'          => ':attribute wajib diisi!',
            'max'               => 'Data Status Kepegawaian diisi maksimal :max karakter!',
            'min'               => 'Data Status Kepegawaian diisi minimal :min karakter!',
            'status_pegawai.unique'       => 'Status tersebut sudah digunakan!',
            'ket_status_pegawai.unique'   => 'Keterangan tersebut sudah digunakan!'
        ]);

        StatusPegawai::create($data);

        // Alihkan ke halaman tenaga pendidik
        return redirect()->route('status.kepegawaian')->with('sukses', 'Data Status Kepegawaian berhasil ditambahkan!');
    }

    //======== Update Data Status Kepegawaian ========//
    public function editStaPeg()
    {
        $data = request()->validate([
            'status_pegawai'      => 'required|unique:status_pegawai,status_pegawai,' . request()->status_pegawai_id,
            'ket_status_pegawai'  => 'required|unique:status_pegawai,ket_status_pegawai,' . request()->status_pegawai_id,
        ], [
            'required'       => ':attribute wajib diisi!',
            'max'            => 'Data Status Kepegawaian diisi maksimal :max karakter!',
            'min'            => 'Data Status Kepegawaian diisi minimal :min karakter!',
            'status_pegawai.unique'       => 'Status tersebut sudah digunakan!',
            'ket_status_pegawai.unique'   => 'Keterangan tersebut sudah digunakan!'
        ]);

        StatusPegawai::where('id', request()->status_pegawai_id)->update($data);

        // Alihkan ke halaman tenaga pendidik
        return redirect()->route('status.kepegawaian')->with('sukses', 'Data Status Kepegawaian berhasil diperbarui!');
    }

    //======== Delete Data Status Kepegawaian ========//
    public function delStaPeg()
    {
        StatusPegawai::destroy(request()->status_pegawai_id);

        // Alihkan ke halaman tenaga pendidik
        return redirect()->route('status.kepegawaian')->with('sukses', 'Data Status Kepegawaian berhasil dihapus!');
    }

    //======== Send Data Status Kepegawain Dalam Guru To Json ========//
    public function getStatusPegawaiDalamGuru($status_pegawai_id)
    {
        // Ambil data Guru dari database

        // ubah data menjadi json
        return response()->json(
            Guru::select('status_guru')->where('status_guru', $status_pegawai_id)->exists()
        );
    }



    public function jabatan()
    {
        return view('tenaga-pendidik.jabatan', [
            'jabatan'  => DB::table('jabatan')
                ->select(
                    'id',
                    'nama_jabatan',
                    'ket_jabatan'
                )->when(request('nama_jabatan'), function ($query) {
                    $query->where('nama_jabatan', 'like', '%' . request('nama_jabatan') . '%')
                        ->orWhere('ket_jabatan', 'like', '%' . request('nama_jabatan') . '%');
                })
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    //======== Add Data Jabatan ========//
    public function addJabatan()
    {
        $data = request()->validate([
            'nama_jabatan'     => 'required|unique:jabatan',
            'ket_jabatan' => 'required|unique:jabatan',
        ], [
            'required'          => ':attribute wajib diisi!',
            'max'               => 'Data Jabatan diisi maksimal :max karakter!',
            'min'               => 'Data Jabatan diisi minimal :min karakter!',
            'nama_jabatan.unique'       => 'Jabatan tersebut sudah digunakan!',
            'ket_jabatan.unique'   => 'Keterangan tersebut sudah digunakan!'
        ]);

        Jabatan::create($data);

        // Alihkan ke halaman jabatan
        return redirect()->route('jabatan')->with('sukses', 'Data Jabatan berhasil ditambahkan!');
    }

    //======== Update Data Jabatan ========//
    public function editJabatan()
    {
        $data = request()->validate([
            'nama_jabatan'     => 'required|unique:jabatan,nama_jabatan,' . request()->jabatan_id,
            'ket_jabatan' => 'required|unique:jabatan,ket_jabatan,' . request()->jabatan_id,
        ], [
            'required'          => ':attribute wajib diisi!',
            'max'               => 'Data Jabatan diisi maksimal :max karakter!',
            'min'               => 'Data Jabatan diisi minimal :min karakter!',
            'nama_jabatan.unique'       => 'Jabatan tersebut sudah digunakan!',
            'ket_jabatan.unique'   => 'Keterangan tersebut sudah digunakan!'
        ]);

        Jabatan::where('id', request()->jabatan_id)->update($data);

        // Alihkan ke halaman jabatan
        return redirect()->route('jabatan')->with('sukses', 'Data Jabatan berhasil diperbarui!');
    }

    //======== Delete Data Jabatan ========//
    public function delJabatan()
    {
        Jabatan::destroy(request()->jabatan_id);

        // Alihkan ke halaman jabatan
        return redirect()->route('jabatan')->with('sukses', 'Data Jabatan berhasil dihapus!');
    }

    //======== Send Data Jabatan Dalam Guru To Json ========//
    public function getJabatanDalamGuru($jabatan_id)
    {
        // Ambil data Guru dari database

        // ubah data menjadi json
        return response()->json(
            Guru::select('jabatan_id')->where('jabatan_id', $jabatan_id)->exists()
        );
    }
}
