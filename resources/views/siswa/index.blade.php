<!-- Content -->
@extends('layout.app')
@section('title', 'Daftar Siswa')
@section('content')

    @php
        $parse = parse_url(url()->full());
        $url = isset($parse['query']) ? $parse['query'] : false;
    @endphp

    <div class="content content-sm">

        <div class="container">

            {{-- Breadcrumb --}}
            <div class="breadcrumb">
                Data Siswa
            </div>
            {{-- End --}}

            <div class="row mt-4">
                <div class="col">
                    <div class="card p-lg-3 p-2 card-content">
                        {{-- Header Button --}}
                        <div class="d-flex mt-2 mb-3 mb-lg-0">

                            @can('siswa.write')
                                    <a href="{{ route('siswa.add', $url) }}" class="btn btn-primary d-none d-md-block d-lg-block">
                                        Tambah
                                    </a>
                            @endcan

                            @can('kepala-sekolah')
                                <div class="align-self-center badge bg-blobe p-2 pb-0 me-3">
                                    <h6 class="fw-bold">
                                        Daftar Siswa
                                    </h6>
                                </div>
                            @endcan

                            {{-- @can('wali_kelas', $nama_kelas)
                                <div class="align-self-center badge bg-blobe p-2 pb-0 me-3">
                                    <h6 class="fw-bold">
                                        Kelas :
                                        {{ $nama_kelas->tingkat . ' ' . $nama_kelas->nama_jurusan . ' ' . $nama_kelas->nama_kelas }}
                                    </h6>
                                </div>
                            @endcan --}}

                            {{-- Search Button --}}
                            <form action="{{ route('siswa') }}" method="get" class="ms-lg-auto ms-md-auto me-2">
                                <div class="d-flex">
                                    {{-- @dump($kelas->toArray()) --}}
                                    <select class="form-select form-select-sm me-2 mb-lg-0 mb-md-0 select-kelas-id msiswa"
                                        name="kelas" id="pilih-kelas-id">
                                        @forelse ($kelas as $item)
                                            <option value="{{ $item->kelas_id }}"
                                                {{ request('kelas') === $item->kelas_id ? 'selected' : '' }}>
                                                {{ $item->tingkat . ' ' . $item->nama_jurusan . ' ' . $item->nama_kelas }}
                                            </option>
                                        @empty
                                            <option value="">Belum ada kelas!</option>
                                        @endforelse
                                    </select>

                                    <div class="input-group me-2">
                                        <input type="text" class="form-control form-control-sm form-default"
                                            placeholder="Cari data.." aria-describedby="button-addon2" name="nama_siswa"
                                            value="{{ request('nama_siswa') }}" id="nama-siswa">
                                        <button type="submit" class="btn btn-default-2" type="button" id="button-addon2">
                                            Cari
                                        </button>
                                    </div>

                                    <a href="javascript:void(0);" id="dropdownMenuButtonMobileExtend"
                                        data-bs-toggle="dropdown" aria-expanded="false"
                                        class="align-self-end dropdown-toggle text-decoration-none text-dark btn btn-default-2 d-lg-none d-md-none">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </a>

                                    {{-- Extend button --}}
                                    <ul class="dropdown-menu dropdown-list msiswa shadow"
                                        aria-labelledby="dropdownMenuButtonMobileExtend">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item disabled"
                                                id="tmb-pindah-kelas-mbl" onclick="pindahKelas()">
                                                <i class="bi bi-arrow-right-circle-fill me-1"></i>
                                                Pindah
                                            </a>
                                        </li>
                                        @can('siswa.write')
                                            <li>
                                                <a href="javascript:void(0);" id="tmb-hapus-mbl" class="dropdown-item disabled"
                                                    onclick="hapusSiswaTerpilih()">
                                                    <i class="bi bi-trash-fill me-1"></i>
                                                    Hapus
                                                </a>
                                            </li>
                                        @endcan
                                        <li>
                                            <a class="dropdown-item" href="">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="currentColor" class="bi bi-arrow-clockwise " viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd"
                                                        d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                                    <path
                                                        d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                                </svg>
                                                Refresh
                                            </a>
                                        </li>
                                        @can('siswa.write')
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal"
                                                    data-bs-target="#exportExcel">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        fill="currentColor" class="bi bi-file-earmark-arrow-up me-1"
                                                        viewBox="0 0 16 16">
                                                        <path
                                                            d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707z" />
                                                        <path
                                                            d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                                                    </svg>
                                                    Import
                                                </a>
                                            </li>
                                            @can('siswa.write')
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal"
                                                        data-bs-target="#importFotoZip">
                                                        <i class="bi bi-file-earmark-zip me-1"></i> Upload Foto ZIP
                                                    </a>
                                                </li>
                                            @endcan
                                        @endcan
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal"
                                                data-bs-target="#exportExcel">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="currentColor" class="bi bi-file-earmark-arrow-down me-1"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                                    <path
                                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                                                </svg>
                                                Export
                                            </a>
                                        </li>
                                    </ul>
                                    {{-- End --}}
                                </div>

                            </form>
                            {{-- End --}}


                            {{-- Pindah Kelas --}}
                            @can('siswa.write')
                                <a href="javascript:void(0);" id="tmb-pindah-kelas" onclick="pindahKelas()"
                                    class="btn btn-primary disabled me-2 ms-auto ms-lg-0 d-none d-lg-block d-md-block  py-1 px-2 pe-1 pe-lg-2">
                                    <i class="bi bi-arrow-right-circle-fill me-1" style="font-size: 16px;"></i>
                                    <span class="d-none d-lg-inline d-md-inline">
                                        Pindah Kelas
                                    </span>
                                </a>
                            @endcan

                            @can('siswa.write')
                                @can('siswa.write')
                                    {{-- Tombol Hapus --}}
                                    <a href="javascript:void(0);" id="tmb-hapus" onclick="hapusSiswaTerpilih()"
                                        class="btn btn-danger disabled me-2 ms-auto ms-lg-0 d-none d-lg-block d-md-block  py-1 px-2 pe-1 pe-lg-2">
                                        <i class="bi bi-trash-fill me-1" style="font-size: 16px;"></i>
                                        <span class="d-none d-lg-inline d-md-inline">
                                            Hapus
                                        </span>
                                    </a>
                                @endcan
                            @endcan

                            {{-- Tombol Refresh --}}
                            <a href="{{ route('siswa') }}" class="btn btn-default-2 d-none d-lg-block d-md-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-arrow-clockwise " viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                    <path
                                        d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                </svg>
                                <span class=" ms-1">
                                    Refresh
                                </span>
                            </a>

                            @can('siswa.write')
                                @can('siswa.write')
                                    {{-- Tombol Import --}}
                                    <a href="javascript:void(0);" class="btn btn-default-2 ms-2 d-none d-lg-block d-md-block"
                                        data-bs-toggle="modal" data-bs-target="#importExcel">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-file-earmark-arrow-up me-1" viewBox="0 0 16 16">
                                            <path
                                                d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707z" />
                                            <path
                                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                                        </svg>
                                        Import
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-default-2 ms-2 d-none d-lg-block d-md-block"
                                        data-bs-toggle="modal" data-bs-target="#importFotoZip">
                                        <i class="bi bi-file-earmark-zip me-1"></i>
                                        Foto ZIP
                                    </a>
                                @endcan
                            @endcan

                            {{-- Tombol Export --}}
                            <a href="javascript:void(0);" onclick="exportExcel()" data-bs-toggle="modal"
                                data-bs-target="#exportExcel" class="btn btn-default-2 ms-2 d-none d-lg-block d-md-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-file-earmark-arrow-down me-1" viewBox="0 0 16 16">
                                    <path
                                        d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                    <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                                </svg>
                                Export
                            </a>

                        </div>
                        {{-- End --}}

                        {{-- Alet Import Excel --}}
                        @if ($errors->any())
                            <div class="alert alert-danger mt-3">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('foto_zip_warnings'))
                            <div class="alert alert-warning mt-3" role="alert">
                                <strong>Foto ZIP selesai diproses dengan beberapa kendala:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach (session('foto_zip_warnings') as $warning)
                                        <li>{{ $warning }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        {{-- End --}}

                        {{-- Data Siswa Responsive --}}
                        @if ($siswa->count() > 0)

                            <div class="siswa-list mt-4">

                                {{-- Header Desktop --}}
                                <div class="siswa-header">
                                    <div class="siswa-check">
                                        @can('siswa.write')
                                            <input type="checkbox" id="check-all">
                                        @endcan
                                    </div>

                                    <div class="siswa-id-card">ID Card</div>
                                    <div class="siswa-nama">Nama</div>
                                    <div class="siswa-nis">NIS</div>
                                    <div class="siswa-jenkel">Jenis Kelamin</div>
                                    <div class="siswa-kelas">Kelas</div>
                                    <div class="siswa-status">Status</div>
                                    <div class="siswa-status-kartu">Status Kartu</div>
                                    <div class="siswa-action"></div>
                                </div>

                                {{-- Select All Mobile --}}
                                @can('siswa.write')
                                    <div class="siswa-select-mobile">
                                        <input type="checkbox" id="check-all-mobile" class="me-1">
                                        <span>Pilih semua</span>
                                    </div>
                                @endcan

                                {{-- Data Siswa: HANYA SATU LOOP --}}
                                @foreach ($siswa as $item)
                                    <div class="siswa-item">

                                        {{-- Checkbox --}}
                                        <div class="siswa-check">
                                            @if ($item->status != 'alumni')
                                                <input type="checkbox" value="{{ $item->id }}" class="check-item"
                                                    name="id_siswa[]">
                                            @endif
                                        </div>

                                        {{-- ID Card --}}
                                        <div class="siswa-id-card">
                                            {{ $item->card_id ? $item->card_id : '0000000000' }}
                                        </div>

                                        {{-- Nama --}}
                                        <div class="siswa-nama">
                                            <img src="{{ $item->foto ? asset('storage/' . $item->foto) : asset('img/logo/profile.jpg') }}"
                                                class="img-fluid rounded-pill img-user" alt="user-photo">

                                            <div class="siswa-identitas">
                                                <div class="fs-nama">
                                                    {{ $item->nama_lengkap }}
                                                </div>

                                                <div class="siswa-mobile-info">
                                                    {{ $item->nis }} | {{ ucwords($item->status) }}
                                                </div>

                                                <div class="siswa-nisn">
                                                    {{ $item->nisn }}
                                                </div>
                                            </div>
                                        </div>

                                        {{-- NIS --}}
                                        <div class="siswa-nis">
                                            {{ $item->nis }}
                                        </div>

                                        {{-- Jenis Kelamin --}}
                                        <div class="siswa-jenkel">
                                            <div
                                                class="{{ $item->jenis_kelamin == 'Laki-laki' ? 'jenkel-l' : 'jenkel-p' }}">
                                                {{ $item->jenis_kelamin }}
                                            </div>
                                        </div>

                                        {{-- Kelas --}}
                                        <div class="siswa-kelas">
                                            <b>
                                                <i class="bi bi-door-closed-fill"></i>
                                                {{ $item->tingkat . ' ' . $item->nama_jurusan . ' ' . $item->nama_kelas }}
                                            </b>
                                        </div>

                                        {{-- Status --}}
                                        <div class="siswa-status">
                                            <span
                                                class="badge {{ $item->status == 'aktif' || $item->status == 'alumni' ? 'badge-status-aktif' : 'badge-status-nonaktif' }}">
                                                {{ ucwords($item->status) }}
                                            </span>
                                        </div>

                                        {{-- Status Kartu --}}
                                        <div class="siswa-status-kartu">
                                            {{-- <span
                                                class="badge {{ $item->card_id ? 'badge-status-aktif' : 'badge-status-nonaktif' }}">
                                                {{ $item->card_id ? 'Terdaftar' : 'Belum Terdaftar' }}
                                            </span> --}}
                                        @can('staff_dan_admin')
                                        <div class="dropdown">
                                                <a class="btn {{ $item->card_id ? 'btn-primary' : 'btn-danger' }}  dropdown-toggle rounded-pill"
                                                    href="#" role="button" style="font-size: 12px;"
                                                    id="dropdownMenuLink" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="bi bi-person-vcard-fill me-1"></i>
                                                    {{ $item->card_id ? 'Sudah tertaut' : 'Belum Tertaut' }}
                                                </a>

                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                    <li>
                                                        @if ($item->card_id)
                                                            <a class="dropdown-item" style="font-size: 12px;"
                                                                href="{{ route('siswa.kartu', [$item->id, 'edit']) }}">
                                                                Tautkan ulang
                                                            </a>
                                                            <form action="{{ route('siswa.kartu.reset', $item->id) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger" style="font-size: 12px;">
                                                                    Reset ID Kartu
                                                                </button>
                                                            </form>
                                                        @else
                                                            <a class="dropdown-item" style="font-size: 12px;"
                                                                href="{{ route('siswa.kartu', [$item->id, 'edit']) }}">
                                                                Tautkan kartu RFID
                                                            </a>
                                                        @endif
                                                    </li>
                                                </ul>
                                            </div>
                                        @endcan
                                        </div>


                                        {{-- Action --}}
                                        <div class="siswa-action">
                                            @if ($item->status != 'alumni')
                                                <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                    aria-expanded="false"
                                                    class="dropdown-toggle text-decoration-none text-dark">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </a>

                                                <ul class="dropdown-menu dropdown-menu-siswa  dropdown-list shadow">
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0);"
                                                            onclick="detailData('{{ $item->id }}')">
                                                            <i class="me-1 bi bi-file-earmark-fill"></i>
                                                            Detail
                                                        </a>
                                                    </li>
                                                    {{-- @can('admin')
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('siswa.kartu', $item->id) }}"><i
                                                                    class="me-1 bi bi-credit-card"></i>{{ $item->card_id ? 'Ganti kartu RFID' : 'Tautkan kartu RFID' }}</a>
                                                        </li>
                                                        @if ($item->card_id)
                                                            <li>
                                                                <form action="{{ route('siswa.kartu.reset', $item->id) }}"
                                                                    method="post"
                                                                    onsubmit="return confirm('Lepas kartu RFID siswa ini? Histori absensi tetap tersimpan.')">
                                                                    @csrf @method('DELETE')<button
                                                                        class="dropdown-item text-danger" type="submit"><i
                                                                            class="me-1 bi bi-x-circle"></i>Reset kartu
                                                                        RFID</button></form>
                                                            </li>
                                                        @endif
                                                        @if (!$item->no_wa_ortu)
                                                            <li><span class="dropdown-item-text small text-warning">Nomor belum
                                                                    tersedia — notifikasi WA dilewati</span></li>
                                                        @endif
                                                    @endcan --}}

                                                    @can('siswa.write')
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('siswa.edit', $item->id . '?' . $url) }}">
                                                                <i class="me-1 bi bi-pencil-square"></i>
                                                                Ubah
                                                            </a>
                                                        </li>
                                                    @endcan

                                                    @can('siswa.write')
                                                        @can('siswa.write')
                                                            <li>
                                                                <a onclick="hapusData('{{ $item->id }}')"
                                                                    class="dropdown-item" href="javascript:void(0);">
                                                                    <i class="me-1 bi bi-trash3-fill"></i>
                                                                    Hapus
                                                                </a>
                                                            </li>
                                                        @endcan
                                                    @endcan
                                                </ul>
                                            @endif
                                        </div>

                                    </div>
                                @endforeach

                            </div>
                        @else
                            <p class="text-center my-3">
                                @if (request('keyword'))
                                    Data yang anda cari tidak ada!
                                @else
                                    Belum ada data siswa!
                                @endif
                            </p>

                        @endif

                        {{-- Pagination --}}
                        {{ $siswa->links() }}
                        {{-- End --}}
                    </div>
                </div>
            </div>

        </div>
        <a href="{{ route('siswa.add') }}" class="btn btn-primary add-btn-siswa-mbl d-lg-none d-md-none">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div>

    {{-- Modal Detail Siswa --}}
    <div class="modal fade" id="detailSiswa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="detailSiswaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="detailSiswaLabel">Detail Data</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Data Siswa --}}
                    <div class="container">
                        <h6>Data Siswa</h6>
                        <hr>
                        <div class="row bg-layer data-detail-siswa rounded pt-2 text-wrap">
                            <div class="col-6">
                                <span class="text-secondary">Nama Lengkap</span>
                                <h6 class="mb-3" id="nama"></h6>
                                <span>Tempat Lahir</span>
                                <h6 class="mb-3" id="tempat_lahir"></h6>
                                <span>Tanggal Lahir</span>
                                <h6 class="mb-3" id="tanggal_lahir"></h6>
                                <span>Jenis Kelamin</span>
                                <h6 class="mb-3" id="jenis_kelamin"></h6>
                                <span>Agama</span>
                                <h6 class="mb-3" id="agama"></h6>
                            </div>
                            <div class="col-6">
                                <span>NIK</span>
                                <h6 class="mb-3" id="nik"></h6>
                                <span>NISN</span>
                                <h6 class="mb-3" id="nisn"></h6>
                                <span>NIS</span>
                                <h6 class="mb-3" id="nis"></h6>
                                <span>Alamat</span>
                                <h6 class="mb-3" id="alamat"></h6>
                            </div>
                        </div>
                    </div>
                    {{-- Data Orang Tua --}}
                    <div class="container mt-5">
                        <h6>Data Orang Tua</h6>
                        <hr>
                        <div class="row bg-layer data-detail-siswa rounded pt-2">
                            <div class="col-6">
                                <span>Nama Ayah</span>
                                <h6 class="mb-3" id="nama_ayah"></h6>
                                <span>Nama Ibu</span>
                                <h6 class="mb-3" id="nama_ibu"></h6>
                            </div>
                            <div class="col-6">
                                <span>Alamat Orang Tua</span>
                                <h6 class="mb-3" id="alamat_ortu"></h6>
                            </div>
                        </div>
                    </div>
                    {{-- Data Wali --}}
                    <div class="container mt-5">
                        <h6>Data Wali</h6>
                        <hr>
                        <div class="row bg-layer data-detail-siswa rounded pt-2">
                            <div class="col-6">
                                <span>Nama Wali</span>
                                <h6 class="mb-3" id="nama_wali"></h6>
                            </div>
                            <div class="col-6">
                                <span>Alamat Wali</span>
                                <h6 class="mb-3" id="alamat_wali"></h6>
                            </div>
                        </div>
                    </div>
                    {{-- Data Asal Sekolah --}}
                    <div class="container mt-5">
                        <h6>Data Asal Sekolah</h6>
                        <hr>
                        <div class="row bg-layer data-detail-siswa rounded pt-2">
                            <div class="col-6">
                                <span>Nama Sekolah</span>
                                <h6 class="mb-3" id="nama_sekolah"></h6>
                                <span>Alamat Sekolah</span>
                                <h6 class="mb-3" id="alamat_sekolah"></h6>
                            </div>
                            <div class="col-6">
                                <span>Tahun Lulus</span>
                                <h6 class="mb-3" id="tahun_lulus"></h6>
                                <span>Nomer Ijazah</span>
                                <h6 class="mb-3" id="nomer_ijazah"></h6>
                            </div>
                        </div>
                    </div>
                    {{-- Data Keterangan Keterima --}}
                    <div class="container mt-5">
                        <h6>Keterangan Keterima</h6>
                        <hr>
                        <div class="row bg-layer data-detail-siswa rounded pt-2">
                            <div class="col-6">
                                <span>Di Tingkat</span>
                                <h6 class="mb-3" id="tingkat_keterima"></h6>
                                <span>Tanggal Keterima</span>
                                <h6 class="mb-3" id="tanggal_keterima"></h6>

                            </div>
                            <div class="col-6">
                                <span>Tahun Pelajaran</span>
                                <h6 class="mb-3" id="tahun_ajar"></h6>
                                <span>Status Kelulusan</span>
                                <h6 class="mb-3" id="lulus"></h6>
                            </div>
                        </div>
                    </div>
                    {{-- Foto Siswa --}}
                    <div class="container-fluid mt-3">
                        <div class="row">
                            <div class="col">
                                <span class="fw-bold">Foto Siswa</span>
                                <img src="" class="img-fluid d-block img-thumbnail shadow mt-2" width="150"
                                    alt="" id="foto">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    {{-- End --}}

    {{-- Modal Hapus Siswa --}}
    <div class="modal fade" id="hapusSiswa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="hapusSiswaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="hapusSiswaLabel">Hapus Siswa</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('siswa.delete', $url) }}" method="post">
                        @csrf
                        @method('delete')
                        <p class="text-center peringatan-modal">Anda yakin ingin menghapus data ini?</p>
                        <div class="button-modal d-flex justify-content-center">
                            <input type="hidden" name="id" id="id-siswa">
                            <button type="button" class="btn btn-default-2 me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash-fill me-1"></i>
                                Hapus
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- End --}}

    <!-- Modal Import Excel -->
    <div class="modal fade" id="importExcel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="importExcelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="importExcelLabel">Import Excel</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('import.siswa') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        {{-- Import Excel Data Siswa --}}
                        <div class="@error('importFile') dropify-invalid @enderror">
                            <input type="file" name="importFile" class="dropify"
                                data-allowed-file-extensions="xls xlsx" />
                        </div>
                        @error('importFile')
                            <div id="validationServer03Feedback" class="text-danger mt-1">{{ ucwords($message) }}
                            </div>
                        @enderror

                        {{-- Download Sample Data Siswa --}}
                        <div class="alert alert-primary p-2 ps-3 mt-3" role="alert">
                            <span style="font-size: 12px;">Jika belum pernah upload file, silakan download sample file
                                <a href="{{ route('import.sample') }}">disini</a> !
                            </span>
                        </div>

                        <table class="table d-none d-lg-table d-md-table desktop-table">
                            <tr>
                                <th colspan="3">

                                    Keterangan Tingkat Pendidikan:

                                </th>
                            </tr>
                            <tr class="tr-table">
                                <th>No</th>
                                <th>Tingkat Pendidikan</th>
                                <th>Keterangan Pendidikan</th>
                            </tr>
                            @foreach ($pendidikan as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->pendidikan }}</td>
                                    <td>{{ $item->ket_pendidikan }}</td>
                                </tr>
                            @endforeach
                        </table>

                        <br>
                        <table class="table d-none d-lg-table d-md-table desktop-table">
                            <tr>
                                <th colspan="4">

                                    Keterangan Kelas Yang Tersedia:

                                </th>
                            </tr>
                            <tr class="tr-table">
                                <th>No</th>
                                <th>tingkat</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                            </tr>
                            @foreach ($kelas as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->tingkat }}</td>
                                    <td>{{ $item->nama_kelas }}</td>
                                    <td>{{ $item->nama_jurusan }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-file-earmark-arrow-up ms-1" viewBox="0 0 16 16">
                                <path
                                    d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707z" />
                                <path
                                    d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                            </svg>
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Upload Foto Siswa dari ZIP --}}
    <div class="modal fade" id="importFotoZip" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="importFotoZipLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="importFotoZipLabel">Upload Foto Siswa dari ZIP</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('siswa.foto.import.zip') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <label for="foto_zip" class="form-label">File ZIP foto</label>
                        <input type="file" name="foto_zip" id="foto_zip" class="form-control"
                            accept=".zip,application/zip" required>
                        <div class="alert alert-primary p-2 mt-3 mb-0" role="alert">
                            <small>
                                Nama setiap foto harus sama dengan NISN 10 digit, misalnya
                                <code>0107632215.jpg</code>. Format yang didukung JPG, JPEG, dan PNG.
                                Letakkan foto langsung di dalam ZIP, tanpa subfolder. Maksimal 500 foto,
                                5 MB per foto, dan 100 MB total setelah dibuka. Semua file divalidasi sebelum
                                data siswa diperbarui.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Upload Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Export Excel -->
    <div class="modal fade" id="exportExcel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="exportExcelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="ExportExcelLabel">Export Excel</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('export.siswa') }}" method="GET" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        {{-- Export Excel Data Siswa --}}
                        <input type="hidden" id="kelas-id-export" name="kelas">
                        <div class="d-none d-lg-flex justify-content-center text-center">
                            Apakah anda yakin ingin mengekspor data siswa di kelas ini ke Excel?
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        {{-- <button type="reset" class="btn btn-danger">Reset</button> --}}
                        <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-file-earmark-arrow-down me-1" viewBox="0 0 16 16">
                                <path
                                    d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z" />
                                <path
                                    d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                            </svg>
                            Yakin!
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Hapus Beberapa Siswa Dari Kelas --}}
    <div class="modal fade" id="hapusSomeSiswa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="hapusSomeSiswaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="hapusSomeSiswaLabel">Hapus Siswa</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('siswa.someDelete', $url) }}" method="post">
                        @csrf
                        @method('delete')
                        <p class="text-center peringatan-modal">Anda yakin ingin menghapus siswa yang terpilih?</p>
                        <div id="selected-siswa-ids"></div>
                        <div class="button-modal d-flex justify-content-center">
                            <button type="button" class="btn btn-default-2 me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash-fill me-1"></i>
                                Yakin!
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- End --}}

    {{-- Modal Pindah Kelas --}}
    <div class="modal fade" id="pindahKelasSiswa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="pindahKelasSiswaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="pindahKelasSiswaLabel">Pindah Kelas</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('siswa.pindahKelas', $url) }}" method="post">
                        @csrf
                        <p class="text-center peringatan-modal">Anda yakin ingin memindahkan siswa yang terpilih?</p>
                        <input type="hidden" name="siswa_id[]" id="siswa_pindah_id">
                        <div class="col-7 mx-auto">
                            <select name="kelas_id" id="kelas_id" class="form-select mb-3">
                                <option value="">Pilih Kelas</option>
                                @forelse ($pindahKelas as $item)
                                    @if ($pindah === $item->tingkat)
                                        <option value="{{ $item->id }}">
                                            {{ $item->tingkat . ' ' . $item->nama_jurusan . ' ' . $item->nama_kelas }}
                                        </option>
                                    @endif
                                @empty
                                    <option value="">Belum ada kelas!</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="button-modal d-flex justify-content-center">
                            <button type="button" class="btn btn-default-2 me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-right-circle me-1"></i>
                                Pindahkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- End --}}

    {{-- Loading screen --}}
    <div class="loading-screen d-none">
        <span class="loader"></span>
    </div>
    {{-- End --}}

@endsection
@push('scripts')
    <script type="text/javascript">
        // Notif untuk data yang berhasil diproses
        @if (session('sukses'))
            let pesan = "{{ session('sukses') }}"
            toastr.success(pesan)
        @endif

        // Konfigurasi Dropify preview import excel
        $('.dropify').dropify({
            messages: {
                default: 'Pilih file anda! <br> Format file yang diwajibkan <br> adalah .XLS, .XLSX!',
                replace: 'Mau ganti file anda?',
                remove: 'Hapus',
                error: 'Ada kesalahan pada proses upload file!'
            }
        });

        // Ambil data siswa berupa json
        function detailData(id) {
            $('.loading-screen').removeClass('d-none');
            // url
            const url = "{{ url('/getSiswa') }}/";

            // asset
            const asset = "{{ asset('storage') }}/";
            $.getJSON(url + id, function(json) {
                $('.loading-screen').addClass('d-none');

                $('#detailSiswa').modal('show');
                $('#nama').html(json.nama_lengkap);
                $('#tempat_lahir').html(json.tempat_lahir);
                $('#tanggal_lahir').html(tgl_indo(json.tanggal_lahir));
                $('#nik').html(json.nik);
                $('#nisn').html(json.nisn);
                $('#nis').html(json.nis);
                $('#alamat').html(json.alamat);
                $('#agama').html(json?.agama?.agama ?? json?.agama_value ?? json?.agama ?? 'Data Belum Ada!');
                $('#jenis_kelamin').html(json.jenis_kelamin);
                $('#nama_ayah').html(json?.ayah?.nama_ayah ?? 'Data Belum Ada!');
                $('#nama_ibu').html(json?.ibu?.nama_ibu ?? 'Data Belum Ada!');
                $('#alamat_ortu').html(json?.ayah?.alamat_ayah ?? 'Data Belum Ada!');
                $('#nama_wali').html(json?.wali?.nama_walmur ?? json?.wali?.nama_wali ?? 'Data Belum Ada!');
                $('#alamat_wali').html(json?.wali?.alamat_walmur ?? json?.wali?.alamat_wali ?? 'Data Belum Ada!');
                $('#nama_sekolah').html(json?.pendidikan?.nama_sekolah ?? 'Data Belum Ada!');
                $('#alamat_sekolah').html(json?.pendidikan?.alamat_sekolah ?? 'Data Belum Ada!');
                $('#tahun_lulus').html(json?.pendidikan?.tahun_lulus ?? 'Data Belum Ada!');
                $('#nomer_ijazah').html(json?.pendidikan?.no_ijazah ?? 'Data Belum Ada!');
                const detailValue = (value, keys) => {
                    if (value === null || value === undefined) return '';
                    if (typeof value !== 'object') return String(value).trim();
                    for (const key of keys) {
                        if (value[key] !== null && value[key] !== undefined && typeof value[key] !== 'object') {
                            return String(value[key]).trim();
                        }
                    }
                    return '';
                };
                const keteranganKeterima = [
                    detailValue(json.tingkat, ['tingkat', 'nama_tingkat']),
                    detailValue(json.nama_jurusan, ['nama_jurusan']),
                    detailValue(json.nama_kelas, ['nama_kelas'])
                ].filter(Boolean).join(' ');
                $('#tingkat_keterima').text(keteranganKeterima || 'Data Belum Ada!');
                $('#tanggal_keterima').html(tgl_indo(json.tanggal_keterima));
                $('#tahun_ajar').html(json.tahun_ajar);
                if (json.status == 'aktif') {
                    $('#lulus').html('Belum Lulus');
                } else {
                    $('#lulus').html('Sudah Lulus');
                }
                if (json.foto) {
                    $('#foto').attr("src", asset + json.foto);
                } else {
                    $('#foto').attr("src", "{{ asset('img/logo/profile.jpg') }}");
                }
            });
        }

        // Hapus data siswa
        function hapusData(id) {
            $('#hapusSiswa').modal('show');
            $('#id-siswa').val(id);
        }

        function exportExcel() {
            const kelasId = $('#pilih-kelas-id').val();
            $('#kelas-id-export').val(kelasId);

            $('#exportExcel').modal('show');
        }

        $('#pilih-kelas-id').change(function(e) {
            e.preventDefault();

            const namaSiswa = $('#nama-siswa').val();

            // Diarahkan ke URL di bawah ini
            window.location.href = `siswa?kelas=${this.value}&nama_siswa=${namaSiswa}`;
        });



        // =========================================================
        // CHECKLIST SISWA
        // =========================================================

        function updateTombolSiswa() {
            const adaYangDipilih = $(".check-item:checked").length > 0;

            if (adaYangDipilih) {
                $('#tmb-pindah-kelas').removeClass('disabled');
                $('#tmb-hapus').removeClass('disabled');
                $('#tmb-pindah-kelas-mbl').removeClass('disabled');
                $('#tmb-hapus-mbl').removeClass('disabled');
            } else {
                $('#tmb-pindah-kelas').addClass('disabled');
                $('#tmb-hapus').addClass('disabled');
                $('#tmb-pindah-kelas-mbl').addClass('disabled');
                $('#tmb-hapus-mbl').addClass('disabled');
            }
        }

        // Pilih semua - Desktop
        $("#check-all").click(function() {
            const checked = $(this).is(":checked");

            $(".check-item").prop("checked", checked);
            $("#check-all-mobile").prop("checked", checked);

            updateTombolSiswa();
        });

        // Pilih semua - Mobile
        $("#check-all-mobile").click(function() {
            const checked = $(this).is(":checked");

            $(".check-item").prop("checked", checked);
            $("#check-all").prop("checked", checked);

            updateTombolSiswa();
        });

        // Pilih siswa satu per satu
        $(document).on('click', '.check-item', function() {
            const total = $(".check-item").length;
            const checked = $(".check-item:checked").length;
            const semuaDipilih = total > 0 && total === checked;

            $("#check-all").prop("checked", semuaDipilih);
            $("#check-all-mobile").prop("checked", semuaDipilih);

            updateTombolSiswa();
        });

        // Tombol hapus siswa terpilih (desktop dan mobile).
        function hapusSiswaTerpilih() {
            const siswaIds = $('.check-item:checked').map(function() {
                return $(this).val();
            }).get();

            if (siswaIds.length === 0) return;

            const $container = $('#selected-siswa-ids').empty();
            siswaIds.forEach(function(id) {
                $('<input>', {
                    type: 'hidden',
                    name: 'siswa_id[]',
                    value: id
                }).appendTo($container);
            });

            $('#hapusSomeSiswa').modal('show');
        }


        // Pindah Kelas
        function pindahKelas() {
            $('#pindahKelasSiswa').modal('show');

            let siswa_id = [];
            if ($(".check-item").is(":checked")) {
                $("input[name^='id_siswa']:checked").each(function() {
                    siswa_id.push($(this).val());
                });
                $('#siswa_pindah_id').val(siswa_id);
            } else {
                $('#siswa_pindah_id').val(siswa_id);
            }
        }
    </script>
@endpush
