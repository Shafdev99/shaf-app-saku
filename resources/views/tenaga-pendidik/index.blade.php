<!-- Content -->
@extends('layout.app')
@section('title', 'Tenaga Pendidik')
@section('content')
    <div class="content content-sm">

        <div class="container">

            {{-- Breadcrumb --}}
            <div class="breadcrumb">
                Tenaga Pendidik
            </div>
            {{-- End --}}

            <div class="row mt-4">
                <div class="col-lg-12 col-md-6 col-12 mb-3">

                    {{-- Navtabs data tenaga pendidik --}}
                    <ul class="nav nav-tabs mt-2" id="myTab" role="tablist" style="margin-bottom: -4px;">
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('tenaga.pendidik') }}"
                                class="nav-link {{ Request::is('tenaga-pendidik*') ? 'active' : '' }}">
                                Tenaga Pendidik
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('jabatan') }}" class="nav-link {{ Request::is('jabatan') ? 'active' : '' }}">
                                Jabatan
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('status.kepegawaian') }}"
                                class="nav-link {{ Request::is('status-kepegawaian') ? 'active' : '' }}">
                                Status Kepegawaian
                            </a>
                        </li>
                    </ul>
                    {{-- End --}}

                    <div class=" card p-lg-3 p-2 card-content">
                        {{-- Header Button --}}
                        <div class="d-flex mt-2 mb-3 mb-lg-0 ">
                            <a class="btn btn-primary d-none d-lg-block d-md-block"
                                href="{{ route('tambah.tenaga.pendidik', request()->query()) }}" aria-expanded="false">
                                <i class="bi bi-plus-lg"></i>
                                Tambah
                            </a>

                            {{-- Search Button --}}
                            <form action="{{ route('tenaga.pendidik') }}" method="get" class="ms-lg-auto ms-md-auto me-2">
                                <div class="d-flex">

                                    <select
                                        class="form-select form-select-sm me-2 mb-lg-0 mb-md-0 select-status-kartu-id msiswa"
                                        name="status_kartu" id="pilih-status-kartu-id">
                                        @forelse ($status_kartu as $item)
                                            <option value="{{ $item }}"
                                                {{ request('status_kartu') === $item ? 'selected' : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>


                                    <div class="input-group me-2">
                                        <input type="text" class="form-control form-control-sm form-default"
                                            placeholder="Cari data.." aria-describedby="button-addon2" name="nama_pegawai"
                                            value="{{ request('nama_pegawai') }}" id="nama-pegawai">
                                        <button type="submit" class="btn btn-default-2" type="button" id="button-addon2">
                                            Cari
                                        </button>
                                    </div>

                                    <a href="javascript:void(0);" id="dropdownMenuButtonMobileExtend"
                                        data-bs-toggle="dropdown" aria-expanded="false"
                                        class="align-self-end dropdown-toggle text-decoration-none text-dark btn btn-default-2 d-lg-none d-md-none">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </a>
                                </div>

                            </form>
                            {{-- End --}}

                            {{-- Tombol Hapus --}}
                            <a href="javascript:void(0);" id="tmb-hapus"
                                class="btn btn-danger disabled me-2 ms-auto ms-lg-0 d-none d-lg-block d-md-block  py-1 px-2 pe-1 pe-lg-2">
                                <i class="bi bi-trash-fill me-1" style="font-size: 16px;"></i>
                                <span class="d-none d-lg-inline d-md-inline">
                                    Hapus
                                </span>
                            </a>

                            {{-- Tombol aktivasi --}}
                            <a href="javascript:void(0);" id="tmb-aktivasi"
                                class="btn btn-success disabled me-2 ms-auto ms-lg-0 d-none d-lg-block d-md-block  py-1 px-2 pe-1 pe-lg-2">
                                <i class="bi bi-check-lg me-1" style="font-size: 16px;"></i>
                                <span class="d-none d-lg-inline d-md-inline">
                                    Aktivasi
                                </span>
                            </a>

                            {{-- Tombol Refresh --}}
                            <a href="{{ route('tenaga.pendidik') }}"
                                class="btn btn-default-2 d-none d-lg-block d-md-block">
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

                            {{-- Tombol Import --}}
                            <a href="javascript:void(0);" class="btn btn-default-2 ms-2 d-none d-lg-block d-md-block"
                                data-bs-toggle="modal" data-bs-target="#importExcel">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-file-earmark-arrow-up me-1" viewBox="0 0 16 16">
                                    <path
                                        d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707z" />
                                    <path
                                        d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                                </svg>
                                Import
                            </a>

                            <div class="d-inline d-lg-none ms-lg-auto align-self-center fw-bold">
                                <i class="bi bi-person-vcard-fill mx-2 d-lg-none" style="font-size: 16px;"></i>
                                Data Guru
                            </div>
                        </div>
                        {{-- End --}}

                        {{-- Table guru Desktop View --}}
                        <table class="table d-none d-lg-table d-md-table desktop-table mt-4">
                            <tr class="tr-table">
                                <th>
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>ID Card</th>
                                <th class="ps-5">Nama</th>
                                <th>No WA</th>
                                <th>NIP/NIPY</th>
                                <th>Kepegawaian</th>
                                <th class="ps-3">Status Akun</th>
                                <th class="ps-3">Status Kartu</th>
                                <th></th>
                            </tr>

                            @forelse ($guru as $row)
                                <tr style="color: rgb(26, 26, 26);">
                                    <td>
                                        <input type="checkbox" value="{{ $row?->id }}" class="check-item"
                                            name="id_pegawai[]">
                                    </td>
                                    <td>
                                        {{ $row->card_id ? $row->card_id : '0000000000' }}
                                    </td>
                                    <td class="d-flex align-items-center ps-5">
                                        <img src="{{ $row->foto_pegawai ? asset('storage/' . $row->foto_pegawai) : asset('storage/image/profil/profile.jpg') }}"
                                            alt="foto-profil" class="img-fluid rounded-circle me-2 img-user"
                                            width="40">
                                        <div class="text-wrap" style="font-size: 12px;">
                                            <span class="fw-bold">
                                                {{ $row->nama_guru }} <br>
                                            </span>
                                            @foreach ($jabatan as $Jabatan)
                                                {{ $Jabatan->id === $row->jabatan ? $Jabatan->nama_jabatan : '' }}
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        {{ $row->no_hp ? $row->no_hp : '-' }}
                                    </td>
                                    <td>
                                        {{ $row->nip }}
                                    </td>
                                    <td class="ps-5">
                                        <div class="badge-blobe px-3 my-auto">
                                            <span style="font-size: 12px;">
                                                {{ $row->status_pegawai }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="ps-4">
                                        <a href="javascript:void(0);" onclick="ubahStatusAkun('{{ $row->id }}')"
                                            class="{{ $row->status_akun === 'Aktif' ? 'badge-globe' : 'badge-wlobe' }} px-3 my-auto text-decoration-none">
                                            <span style="font-size: 12px;">
                                                {{ $row->status_akun }}
                                            </span>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <a class="btn {{ $row->card_id ? 'btn-primary' : 'btn-danger' }}  dropdown-toggle rounded-pill"
                                                href="#" role="button" style="font-size: 12px;"
                                                id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-person-vcard-fill me-1"></i>
                                                {{ $row->card_id ? 'Sudah tertaut' : 'Belum Tertaut' }}
                                            </a>

                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                <li>
                                                    @if ($row->card_id)
                                                        <a class="dropdown-item" style="font-size: 12px;"
                                                            href="{{ route('tenaga.pendidik.tautkan.kartu', [$row->id, 'edit']) }}">
                                                            Tautkan ulang
                                                        </a>
                                                        <form action="{{ route('tenaga.pendidik.reset.kartu', $row->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-danger" style="font-size: 12px;">
                                                                Reset ID Kartu
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a class="dropdown-item" style="font-size: 12px;"
                                                            href="{{ route('tenaga.pendidik.tautkan.kartu', [$row->id, 'edit']) }}">
                                                            Tautkan kartu RFID
                                                        </a>
                                                    @endif
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('edit.tenaga.pendidik', array_merge(['id' => $row->id], request()->query())) }}"
                                            class="text-decoration-none">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="deleteGuru('{{ $row->id }}')"
                                            class="text-decoration-none text-danger ms-1">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center border-0">
                                        <p class="text-center my-5 d-none d-md-inline d-lg-inline">Belum ada data guru!</p>
                                    </td>
                                </tr>
                            @endforelse
                        </table>

                        {{-- End --}}

                        {{-- Table guru Mobile View --}}
                        @forelse ($guru as $item)
                            <div class="card mb-2 p-2 d-lg-none d-md-none">
                                <div class="d-flex">
                                    <div class="d-inline">
                                        <div class="fs-nama">
                                            {{ $loop->iteration }}). &nbsp;{{ $item->nama_guru }}
                                            <span class="badge-globe px-2 ms-2">
                                                {{ $item->status_pegawai }}
                                            </span>
                                        </div>
                                        <br>
                                        <span class="ps-4">
                                            {{ $item->nip }}
                                        </span>
                                    </div>
                                    <a href="javascript:void(0);" id="dropdownMenuButtonMobile" data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        class="dropdown-toggle text-decoration-none text-dark ms-auto">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-list shadow"
                                        aria-labelledby="dropdownMenuButtonMobile">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                onclick="editGuru('{{ $item->nama_guru }}','{{ $item->nip }}','{{ $item->jabatan }}','{{ $item->status_guru }}','{{ $item->id }}')">
                                                <i class="me-1 bi bi-pencil-square"></i>
                                                Ubah
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" onclick="deleteGuru('{{ $item->id }}')"
                                                class="dropdown-item">
                                                <i class="me-1 bi bi-trash3-fill"></i>
                                                Hapus
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @empty
                            <p class="text-center mb-4 mt-3 d-lg-none d-md-none">Belum ada data guru!</p>
                        @endforelse
                        {{-- End --}}

                        {{-- Pagination --}}
                        {{ $guru->links() }}
                        {{-- End --}}
                    </div>
                </div>

            </div>
        </div>

        <div class="dropdown">
            <a href="javascript:void(0);" class="btn btn-primary add-btn-siswa-mbl d-lg-none d-md-none dropdown-toggle"
                data-bs-toggle="dropdown">
                <i class="bi bi-plus-lg"></i>
            </a>
            <ul id="pilihKelas" class="dropdown-menu pilihan-kelas">
                <li>
                    <a href="javascript:void(0);" onclick="addGuru()" class="dropdown-item fs-6"
                        style="font-size: 12px !important;">
                        Guru
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" onclick="addStaPeg()" class="dropdown-item fs-6"
                        style="font-size: 12px !important;">
                        Status Kepegawaian
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Modal Delete guru --}}
    <div class="modal fade" id="deleteGuru" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="deleteGuruLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="deleteGuruLabel">Hapus Data Guru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('tenaga.pendidik.destroy', request()->query()) }}" method="post">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <p id="keterangan-to-guru" class="text-center peringatan-modal my-auto">
                            Apakah anda yakin ingin menghapus guru ini?
                        </p>
                        <input type="hidden" id="guru-id-delete" name="guru_id">
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-default-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" id="delBtnguru">
                            <i class="me-1 bi bi-trash3-fill"></i>
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- End --}}


    {{-- Modal Hapus Beberapa guru --}}
    <div class="modal fade" id="hapusSomePegawai" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="hapusSomePegawaiLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="hapusSomePegawaiLabel">Hapus pegawai</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('tenaga.pendidik.delete.selected', request()->query()) }}" method="post">
                        @csrf
                        @method('delete')
                        <p id="keterangan-guru-terpilih" class="text-center peringatan-modal">Anda yakin ingin menghapus
                            pegawai yang terpilih?</p>
                        <input type="hidden" name="pegawai_id[]" id="pegawai_id">
                        <div class="button-modal d-flex justify-content-center">
                            <button type="button" class="btn btn-default-2 me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger" id="delBtnGuruTerpilih">
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

    {{-- Modal Aktivasi Guru --}}
    <div class="modal fade" id="aktivasiGuru" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="aktivasiGuruLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="aktivasiGuruLabel">Ubah Status Akun</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('tenaga.pendidik.aktivasi', request()->query()) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p id="keterangan-to-guru" class="text-center peringatan-modal my-auto">
                            Anda yakin ingin mengubah status akun tenaga pendidik ini?
                        </p>
                        <input type="hidden" id="guru-id-aktivasi" name="guru_id">
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-default-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            Ubah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- End --}}

    {{-- Modal Aktivasi Guru Terpilih --}}
    <div class="modal fade" id="aktivasiSomePegawai" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="aktivasiSomePegawaiLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="aktivasiSomePegawaiLabel">Ubah Status Akun</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('tenaga.pendidik.terpilih.aktivasi', request()->query()) }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <p id="keterangan-to-guru" class="text-center peringatan-modal my-auto">
                            Anda yakin ingin mengubah status akun tenaga pendidik ini?
                        </p>
                        <input type="hidden" id="guru-id-terpilih-aktivasi" name="guru_id_arr[]">
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-default-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            Ubah
                        </button>
                    </div>
                </form>
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
                <form action="{{ route('tenaga.pendidik.import') }}" method="POST" enctype="multipart/form-data">
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
                        <div class="alert alert-primary p-4 ps-3 mt-3 d-flex" style="border: 2px dashed" role="alert">
                            <i class="bi bi-exclamation-circle-fill align-self-center me-4 fs-1"></i>
                            <div>
                                <span class="fw-bold">
                                    Template Import
                                </span>
                                <br>
                                <span style="font-size: 12px;" class="text-secondary">
                                    Jika belum pernah upload file, silakan download template import
                                    <a class="border border-primary text-decoration-none px-2  rounded-3 fw-bold"
                                        href="{{ route('tenaga.pendidik.template.import') }}">disini</a> !
                                </span>
                            </div>
                        </div>
                        <ol
                            style="font-size: 12px; border-left: 3px solid rgb(188, 0, 0); background-color: #f4f4f4; padding: 8px 0px 8px 25px; border-radius: 4px;">
                            <li>
                                Harap hapus contoh ketika ingin mengisi data secata jamak!
                            </li>
                            <li>
                                Harap kolom Jabatan dan Status Kepegawaian diisi sesuai keterangan dibawah ini!
                            </li>
                            <li>
                                Nama Jabatan dan Status Kepegawaian harus sesuai dengan data yang ada pada aplikasi!
                            </li>
                            <li>
                                Jika belum ada, maka silakan tambah
                                data Jabatan dan Status Kepegawaian terlebih dahulu di aplikasi!
                            </li>
                            <li>
                                NIP/NIPY wajib diisi dan tidak boleh kosong!
                            </li>
                            <li>
                                Jika belum memiliki NIP/NIPY, maka Saku diisi simbol
                                "-" !
                            </li>
                        </ol>

                        <table class="table d-none d-lg-table d-md-table desktop-table">
                            <tr>
                                <th colspan="3">
                                    Keterangan Jabatan:
                                </th>
                            </tr>
                            <tr class="tr-table">
                                <th><b>No</b></th>
                                <th><b>Nama Jabatan</b></th>
                            </tr>
                            @foreach ($jabatan as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->nama_jabatan }}</td>
                                </tr>
                            @endforeach
                        </table>

                        <table class="table d-none d-lg-table d-md-table desktop-table">
                            <tr>
                                <th colspan="4">
                                    Keterangan Status Kepegawaian:
                                </th>
                            </tr>
                            <tr class="tr-table">
                                <th><b>No</b></th>
                                <th><b>Status Pegawai</b></th>
                                <th><b>Keterangan</b></th>
                            </tr>
                            @foreach ($stapeg as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->status_pegawai }}</td>
                                    <td>{{ $item->ket_status_pegawai }}</td>
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



@endsection
@push('scripts')
    <script type="text/javascript">
        // Konfigurasi Dropify preview import excel
        $('.dropify').dropify({
            messages: {
                default: 'Pilih file anda! <br> Format file yang diwajibkan <br> adalah .XLS, .XLSX!',
                replace: 'Mau ganti file anda?',
                remove: 'Hapus',
                error: 'Ada kesalahan pada proses upload file!'
            }
        });

        // Notif untuk data yang berhasil diproses
        @if (session('sukses'))
            let pesan = "{{ session('sukses') }}"
            toastr.success(pesan)
        @endif

        // Notif untuk data yang tidak lolos validasi
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.warning('{{ ucwords($error) }}');
            @endforeach
        @endif
        // End

        // Modal tambah guru
        function addGuru() {
            $('#AddGuru').modal('show');
        }

        // Modal edit guru
        function editGuru(nama, nip, jabatan, sts_guru, id) {
            $('#guru-id-edit').val(id);
            $('#nama-guru').val(nama);
            $('#nip').val(nip);
            $('#jabatan').val(jabatan);
            $('#status-guru').val(sts_guru);
            $('#editGuru').modal('show');
        }

        // Modal hapus guru
        function deleteGuru(id) {
            // url
            const url = "{{ url('/cekValidasiGuru') }}/";

            $.getJSON(url + id,
                function(json) {
                    if (json != 0) {
                        $('#keterangan-to-guru').text('Guru ini sekarang wali kelas!');
                        $('#delBtnguru').addClass('d-none');
                    } else {
                        $('#keterangan-to-guru').text('Apakah anda yakin ingin menghapus guru ini?');
                        $('#delBtnguru').removeClass('d-none');
                        $('#guru-id-delete').val(id);
                    }
                    $('#deleteGuru').modal('show');
                }
            );

        }

        function ubahStatusAkun(id) {
            $('#guru-id-aktivasi').val(id);
            $('#aktivasiGuru').modal('show');
        }

        // Checklist atau centang semua pegawai
        $("#check-all").click(function() {

            if ($(this).is(":checked")) {
                $(".check-item").prop("checked", true);
                $('#tmb-hapus').removeClass('disabled');
                $('#tmb-aktivasi').removeClass('disabled');
            } else {
                $(".check-item").prop("checked", false);
                $('#tmb-hapus').addClass('disabled');
                $('#tmb-aktivasi').addClass('disabled');
            }
        });

        // Checklist atau centang beberapa pegawai
        $('.check-item').click(function() {
            if ($(".check-item").is(":checked")) {
                $('#tmb-hapus').removeClass('disabled');
                $('#tmb-aktivasi').removeClass('disabled');
                $('#tmb-hapus-mbl').removeClass('disabled');
            } else {
                $('#tmb-hapus').addClass('disabled');
                $('#tmb-aktivasi').addClass('disabled');
                $('#tmb-hapus-mbl').addClass('disabled');
            }
        });

        // Tombol hapus pegawai
        $('#tmb-hapus').click(function() {

            const url = "{{ url('/cekValidasiGuruArr') }}/";

            let pegawai_id = [];

            if ($(".check-item").is(":checked")) {

                $("input[name^='id_pegawai']:checked").each(function() {
                    pegawai_id.push($(this).val());
                });

                $.getJSON(url + pegawai_id,
                    function(json) {
                        if (json === true) {
                            console.log(json);

                            $('#keterangan-guru-terpilih').text('Ada guru yang jadi wali kelas!');
                            $('#delBtnGuruTerpilih').addClass('d-none');
                        } else {
                            $('#keterangan-guru-terpilih').text(
                                'Anda yakin ingin menghapus pegawai yang terpilih?');
                            $('#delBtnGuruTerpilih').removeClass('d-none');
                        }
                        $('#hapusSomePegawai').modal('show');
                    }
                );

                $('#pegawai_id').val(pegawai_id);

            } else {
                $('#pegawai_id').val(pegawai_id);
            }

        });

        // Tombol aktivasi pegawai
        $('#tmb-aktivasi').click(function() {

            let pegawai_id_aktivsi = [];

            if ($(".check-item").is(":checked")) {

                $("input[name^='id_pegawai']:checked").each(function() {
                    pegawai_id_aktivsi.push($(this).val());
                });

                $('#aktivasiSomePegawai').modal('show');

                $('#guru-id-terpilih-aktivasi').val(pegawai_id_aktivsi);

            } else {
                $('#guru-id-terpilih-aktivasi').val(pegawai_id_aktivsi);
            }

        });

        // Tombol hapus Pegawai Mobile View
        function hapusPegawaiTerpilih() {
            $('#hapusSomePegawai').modal('show');

            let pegawai_id = [];
            if ($(".check-item").is(":checked")) {
                $("input[name^='id_pegawai']:checked").each(function() {
                    pegawai_id.push($(this).val());
                });
                $('#pegawai_id').val(pegawai_id);
            } else {
                $('#pegawai_id').val(pegawai_id);
            }
        }
    </script>
@endpush
