<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class templateImportPegawai implements FromView, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        return view('tenaga-pendidik.template-import-pegawai', [
            'jabatan' => ['Guru', 'Staff', 'Kepala Sekolah'],
            'spg' => DB::table('status_pegawai')->first(),
            'status_pegawai' => DB::table('status_pegawai')->get(),
        ]);
    }
}
