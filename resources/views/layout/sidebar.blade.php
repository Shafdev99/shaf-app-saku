{{-- Home menu --}}
<a href="{{ route('beranda') }}">
    <div class="list-item {{ Request::is('beranda') ? 'active' : ' ' }}">
        <i class="bi bi-house-fill"></i>
        <span class="list-title">Beranda</span>
    </div>
</a>
{{-- Akhir --}}


{{-- **Menu Untuk Admin** --}}
@can('admin')
    {{-- Menu Data Master Collapse --}}
    <a data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample"
        class="collapse-btn collapsed {{ Request::is('jurusan*') || Request::is('kelas*') || Request::is('tahun-ajar*') || Request::is('agama*') || Request::is('pendidikan*') || Request::is('semester*') || Request::is('lampiran*') || Request::is('mapel*') || Request::is('kurikulum*') ? 'data-master' : ' ' }}"
        id="collapse-btn" onclick="rotate(this)">
        <div class="list-item d-flex justify-content-between">
            <div>
                <i class="bi bi-menu-button-wide"></i>
                <span class="list-title">Data Master</span>
            </div>
            <div class="drop-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-chevron-right ms-auto me-3 drop-icon-item" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                </svg>
            </div>
        </div>
    </a>



    {{-- Menu Kelas --}}
    <div class="collapse collapse-master" id="collapseExample">
        <a href="{{ route('kelas') }}">
            <div class="list-item {{ Request::is('kelas*') ? 'active' : ' ' }}">
                <i class="bi bi-door-closed-fill"></i>
                <span class="list-title">Kelas</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}



    {{-- Menu Mapel --}}
    <div class="collapse collapse-master" id="collapseExample">
        <a href="{{ route('mapel') }}">
            <div class="list-item {{ Request::is('mapel*') ? 'active' : ' ' }}">
                <i class="bi bi-journal-text"></i>
                <span class="list-title">Mapel</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}



    {{-- Menu Tahun Ajar --}}
    <div class="collapse collapse-master" id="collapseExample">
        <a href="{{ route('tahun.ajar') }}">
            <div class="list-item {{ Request::is('tahun-ajar*') ? 'active' : ' ' }}">
                <i class="bi bi-calendar-week"></i>
                <span class="list-title">Tahun Ajar</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}



    {{-- Menu Semester --}}
    <div class="collapse collapse-master" id="collapseExample">
        <a href="{{ route('semester') }}">
            <div class="list-item {{ Request::is('semester*') ? 'active' : ' ' }}">
                <i class="bi bi-book"></i>
                <span class="list-title">Semester</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}



    {{-- Menu Kurikulum --}}
    <div class="collapse collapse-master" id="collapseExample">
        <a href="{{ route('kurikulum') }}">
            <div class="list-item {{ Request::is('kurikulum*') ? 'active' : ' ' }}">
                <i class="bi bi-building-fill-gear"></i>
                <span class="list-title">Kurikulum</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}



    {{-- Menu Agama --}}
    <div class="collapse collapse-master" id="collapseExample">
        <a href="{{ route('agama') }}">
            <div class="list-item {{ Request::is('agama*') ? 'active' : ' ' }}">
                <i class="bi bi-person-standing"></i>
                <span class="list-title">Agama</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}



    {{-- Menu Pendidikan --}}
    <div class="collapse collapse-master" id="collapseExample">
        <a href="{{ route('pendidikan') }}">
            <div class="list-item {{ Request::is('pendidikan*') ? 'active' : ' ' }}">
                <i class="bi bi-buildings-fill"></i>
                <span class="list-title">Pendidikan</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}



    {{-- Menu Lampiran --}}
    <div class="collapse collapse-master" id="collapseExample">
        <a href="{{ route('lampiran') }}">
            <div class="list-item {{ Request::is('lampiran*') ? 'active' : ' ' }}">
                <i class="bi bi-files"></i>
                <span class="list-title">Lampiran</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}

    {{-- Akhir Collapse --}}
@endcan

@can('staff_dan_admin')
{{-- Menu SPMB Collapse --}}
<a data-bs-toggle="collapse" href="#collapseSpmb" role="button" aria-expanded="false" aria-controls="collapseSpmb"
    class="collapse-btn collapsed {{ Request::is('pengaturan-spmb*') || Request::is('pendaftar*') || Request::is('detail-pendaftar*') || Request::is('ubah-pendaftar*') || Request::is('tambah-pendaftar*') || Request::is('spmb-rekap*') ? 'data-spmb' : ' ' }}"
    id="collapse-btn-spmb" onclick="rotate(this)">
    <div class="list-item d-flex justify-content-between">
        <div>
            <i class="bi bi-person-workspace"></i>
            <span class="list-title">SPMB</span>
        </div>
        <div class="drop-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-chevron-right ms-auto me-3 drop-icon-item" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
            </svg>
        </div>
    </div>
</a>


{{-- Menu Halaman SPMB --}}
<div class="collapse collapse-spmb" id="collapseSpmb">
    <a href="{{ route('halaman.daftar') }}" target="_blank">
        <div class="list-item {{ Request::is('halaman-spmb*') ? 'active' : ' ' }}">
            <i class="bi bi-clipboard2-plus-fill"></i>
            <span class="list-title">Halaman SPMB</span>
        </div>
    </a>
</div>
{{-- Akhir --}}

{{-- Menu Pendaftar SPMB --}}
<div class="collapse collapse-spmb" id="collapseSpmb">
    <a href="{{ route('pendaftar') }}">
        <div
            class="list-item {{ Request::is('pendaftar*') || Request::is('detail-pendaftar*pendaftar') || Request::is('ubah-pendaftar*') || Request::is('tambah-pendaftar*') ? 'active' : ' ' }}">
            <i class="bi bi-file-earmark-person-fill"></i>
            <span class="list-title">Pendaftar</span>
        </div>
    </a>
</div>
{{-- Akhir --}}

{{-- Menu Rekap Pendaftaran SPMB --}}
<div class="collapse collapse-spmb" id="collapseSpmb">
    <a href="{{ route('spmb.rekap') }}">
        <div
            class="list-item {{ Request::is('spmb-rekap*') || Request::is('detail-pendaftar*rekap') ? 'active' : ' ' }}">
            <i class="bi bi-hourglass-split"></i>
            <span class="list-title">Rekap SPMB</span>
        </div>
    </a>
</div>
{{-- Akhir --}}

{{-- **Menu Untuk Admin** --}}
@can('admin')
    {{-- Menu Pengaturan SPMB --}}
    <div class="collapse collapse-spmb" id="collapseSpmb">
        <a href="{{ route('spmb.pengaturan') }}">
            <div class="list-item {{ Request::is('pengaturan-spmb*') ? 'active' : ' ' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span class="list-title">Pengaturan</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}
@endcan

{{-- Akhir Collapse --}}
@endcan


{{-- **Menu Untuk Admin** --}}
@can('admin')
    {{-- Menu Tenaga Pendidik --}}
    <a href="{{ route('tenaga.pendidik') }}">
        <div
            class="list-item {{ Request::is('tenaga-pendidik*') || Request::is('jabatan*') || Request::is('status-kepegawaian*') ? 'active' : ' ' }}">
            <i class="bi bi-person-badge"></i>
            <span class="list-title">Tenaga Pendidik</span>
        </div>
    </a>
    {{-- Akhir --}}
@endcan


{{-- **Menu Untuk Staff Dan Admin** --}}
@can('siswa.read')
    {{-- Menu Siswa --}}
    <a href="{{ route('siswa') }}">
        <div class="list-item {{ Request::is('siswa*') ? 'active' : ' ' }}">
            <i class="bi bi-backpack-fill"></i>
            <span class="list-title">Siswa</span>
        </div>
    </a>
    {{-- Akhir --}}
@endcan


{{-- **Menu Untuk Admin** --}}
@can('admin')
    {{-- Menu Naik Kelas --}}
    @if (session('semester') == 'genap')
        <a href="{{ route('siswa.naik') }}">
            <div class="list-item {{ Request::is('naik-kelas*') ? 'active' : ' ' }}">
                <i class="bi bi-building-fill-up"></i>
                <span class="list-title">Naik Kelas</span>
            </div>
        </a>
    @endif
    {{-- Akhir --}}

    {{-- Menu Wali Kelas --}}
    <a href="{{ route('walkel') }}">
        <div class="list-item {{ Request::is('walkel*') ? 'active' : ' ' }}">
            <i class="bi bi-person-vcard-fill"></i>
            <span class="list-title">Wali Kelas</span>
        </div>
    </a>
    {{-- Akhir --}}
@endcan


{{-- **Menu Untuk Staff Dan Admin** --}}
@can('absensi.read')
    {{-- Menu Pindah Kelas --}}
    {{-- <a href="{{ route('siswa.pindah') }}">
        <div class="list-item {{ Request::is('pindah-kelas*') ? 'active' : ' ' }}">
            <i class="bi bi-arrow-left-right"></i>
            <span class="list-title">Pindah Kelas</span>
        </div>
    </a> --}}
    {{-- Akhir --}}

    {{-- Menu Absensi Collapse --}}
    <a data-bs-toggle="collapse" href="#collapseAbsensi" role="button" aria-expanded="false"
        aria-controls="collapseAbsensi"
        class="collapse-btn collapsed {{ Request::is('ketidakhadiran*') || Request::is('atur-absensi*') || Request::is('laporan-absensi*') ? 'data-absensi' : ' ' }}"
        id="collapse-btn-absensi" onclick="rotate(this)">
        <div class="list-item d-flex justify-content-between">
            <div>
                <i class="bi bi-person-badge-fill"></i>
                <span class="list-title">Absensi</span>
            </div>
            <div class="drop-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-chevron-right ms-auto me-3 drop-icon-item" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                </svg>
            </div>
        </div>
    </a>

    {{-- Menu Input absensi Siswa --}}
    {{-- <div class="collapse collapse-absensi" id="collapseAbsensi">
        <a href="{{ route('master.absensi') }}">
            <div class="list-item {{ Request::is('master-absensi*') ? 'active' : ' ' }}">
                <i class="bi bi-file-person"></i>
                <span class="list-title">Master</span>
            </div>
        </a>
    </div> --}}
    {{-- Akhir --}}

    {{-- Menu Input absensi Siswa --}}
    <div class="collapse collapse-absensi" id="collapseAbsensi">
        <a href="{{ route('ketidakhadiran') }}">
            <div class="list-item {{ Request::is('ketidakhadiran*') ? 'active' : ' ' }}">
                <i class="bi bi-clipboard-x"></i>
                <span class="list-title">Izin / Sakit</span>
            </div>
        </a>
    </div>

    {{-- <div class="collapse collapse-absensi" id="collapseAbsensi">
        <a href="{{ route('absensi.scan.view') }}">
            <div class="list-item {{ Request::is('absensi/scan') ? 'active' : ' ' }}">
                <i class="bi bi-credit-card-2-front"></i>
                <span class="list-title">Scan Absensi</span>
            </div>
        </a>
    </div> --}}

    <div class="collapse collapse-absensi" id="collapseAbsensi">
        <a href="{{ route('atur.absensi') }}">
            <div class="list-item {{ Request::is('atur-absensi*') ? 'active' : ' ' }}">
                <i class="bi bi-menu-button"></i>
                <span class="list-title">Atur Absensi</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}

    {{-- Menu Input absensi Siswa --}}
    <div class="collapse collapse-absensi" id="collapseAbsensi">
        <a href="{{ route('laporan.absensi') }}">
            <div class="list-item {{ Request::is('laporan-absensi*') ? 'active' : ' ' }}">
                <i class="bi bi-clipboard2-data"></i>
                <span class="list-title">Rekap Absensi</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}

@endcan

@can('staff_dan_admin')
    <a href="{{ route('kartu-identitas') }}">
        <div class="list-item {{ Request::is('kartu-identitas*') ? 'active' : ' ' }}">
            <i class="bi bi-person-vcard"></i>
            <span class="list-title">Kartu Identitas</span>
        </div>
    </a>

    {{-- Menu Buku Induk Collapse --}}
    <a data-bs-toggle="collapse" href="#collapseBinduk" role="button" aria-expanded="false"
        aria-controls="collapseBinduk"
        class="collapse-btn collapsed {{ Request::is('rekap*') || Request::is('form-data*') || Request::is('mutasi*') ? 'data-binduk' : ' ' }}"
        id="collapse-btn-binduk" onclick="rotate(this)">
        <div class="list-item d-flex justify-content-between">
            <div>
                <i class="bi bi-journal-richtext"></i>
                <span class="list-title">Buku induk</span>
            </div>
            <div class="drop-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-chevron-right ms-auto me-3 drop-icon-item" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                </svg>
            </div>
        </div>
    </a>

    {{-- Menu Mutasi Siswa --}}
    <div class="collapse collapse-binduk" id="collapseBinduk">
        <a href="{{ route('mutasi') }}">
            <div class="list-item {{ Request::is('mutasi*') ? 'active' : ' ' }}">
                <i class="bi bi-file-person"></i>
                <span class="list-title">Mutasi</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}

    {{-- Menu Rekap Data Siswa --}}
    <div class="collapse collapse-binduk" id="collapseBinduk">
        <a href="{{ route('rekap') }}">
            <div class="list-item {{ Request::is('rekap*') || Request::is('form-data*') ? 'active' : ' ' }}">
                <i class="bi bi-files"></i>
                <span class="list-title">Rekap</span>
            </div>
        </a>
    </div>
    {{-- Akhir --}}

    {{-- Akhir Collapse --}}

    {{-- Menu Alumni Kelas --}}
    <a href="{{ route('alumni') }}">
        <div class="list-item {{ Request::is('alumni*') ? 'active' : ' ' }}">
            <i class="bi bi-mortarboard-fill"></i>
            <span class="list-title">Alumni</span>
        </div>
    </a>
    {{-- Akhir --}}
@endcan

@can('rekap.read')
    @cannot('staff_dan_admin')
        <a href="{{ route('rekap') }}">
            <div class="list-item {{ Request::is('rekap*') || Request::is('form-data*') ? 'active' : ' ' }}">
                <i class="bi bi-files"></i>
                <span class="list-title">Rekap</span>
            </div>
        </a>
    @endcannot
@endcan


{{-- **Menu Untuk Admin** --}}
@can('admin')
    {{-- Menu Activity log --}}
    <a href="{{ route('log') }}">
        <div class="list-item {{ Request::is('activity-log*') ? 'active' : ' ' }}">
            <i class="bi bi-clock-history"></i>
            <span class="list-title">Riwayat</span>
        </div>
    </a>
    {{-- Akhir --}}
@endcan


{{-- Menu Profil --}}
<a href="{{ route('profil') }}">
    <div class="list-item {{ Request::is('profil*') ? 'active' : ' ' }}">
        <i class="bi bi-person-circle"></i>
        <span class="list-title">Profil</span>
    </div>
</a>
{{-- Akhir --}}


{{-- **Menu Untuk Admin** --}}
@can('admin')
    {{-- Menu Admin/User --}}
    <a href="{{ route('user') }}">
        <div class="list-item {{ Request::is('user*') ? 'active' : ' ' }}">
            <i class="bi bi-people-fill"></i>
            <span class="list-title position-relative">
                Pengguna
                @php
                    $request = DB::table('users')->select('request')->where('request', 'true');
                @endphp
                @if ($request->count() > 0)
                    <span class="badge rounded-pill bg-danger float-end me-2">
                        {{ $request->count() }}
                    </span>
                @endif
            </span>
        </div>
    </a>

    <a href="{{ route('transisi.semester') }}">
        <div class="list-item {{ Request::is('transisi-semester*') ? 'active' : ' ' }}">
            <i class="bi bi-layers-half"></i>
            <span class="list-title">Transisi Semester</span>
        </div>
    </a>

    {{-- Menu Admin/Setting --}}
    <a href="{{ route('setting') }}">
        <div class="list-item {{ Request::is('setting*') ? 'active' : ' ' }}">
            <i class="bi bi-gear-fill"></i>
            <span class="list-title">Pengaturan</span>
        </div>
    </a>
@endcan
{{-- **End Menu** --}}





<a href="javascript:void(0);" onclick="slide()" data-bs-toggle="modal" data-bs-target="#modalLogout">
    <div class="list-item">
        <i class="bi bi-box-arrow-right"></i>
        <span class="list-title">Keluar</span>
    </div>
</a>
