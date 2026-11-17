<!-- Content -->
@extends('layout.app')
@section('title', 'Daftar Pengguna Aplikasi')
@section('content')
    <div class="content content-sm">

        <div class="container">

            {{-- Breadcrumb --}}
            <div class="breadcrumb">
                Pengguna Aplikasi
            </div>
            {{-- End --}}

            {{-- <h>Daftar User</h --}}
            <div class="row mt-4">
                <div class="col">

                    {{-- Navtabs data user --}}
                    <ul class="nav nav-tabs mt-2" id="myTab" role="tablist" style="margin-bottom: -4px;">
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('user') }}" class="nav-link {{ Request::is('user') ? 'active' : '' }}"
                                id="siswa-tab">
                                Admin
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('user', 'staff') }}"
                                class="nav-link {{ Request::is('user/staff') ? 'active' : '' }}" id="siswa-tab">
                                Staff
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('user', 'guru') }}"
                                class="nav-link {{ Request::is('user/guru') ? 'active' : '' }}" id="siswa-tab">
                                Guru
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('user', 'kepala-sekolah') }}"
                                class="nav-link {{ Request::is('user/kepala-sekolah') ? 'active' : '' }}" id="siswa-tab">
                                Kepala Sekolah
                            </a>
                        </li>
                    </ul>
                    {{-- End --}}

                    <div class="card p-lg-3 p-2 card-content shadow-sm">

                        {{-- Header Button --}}
                        <div class="d-flex mt-2 mb-3 mb-lg-0">

                            @if (Request::is('user/guru'))
                                <form action="{{ route('user.add.guru') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary d-none d-lg-block d-md-block me-2">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Generate
                                    </button>
                                </form>
                                @if ($userGuru->count() != 0)
                                    <form action="{{ route('user.reset.pass.guru') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success d-none d-lg-block d-md-block">
                                        <i class="bi bi-arrow-repeat"></i>
                                        Reset Password
                                        </button>
                                    </form>
                                @endif
                            @elseif(Request::is('user/staff'))
                                <form action="{{ route('user.add.staff') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary d-none d-lg-block d-md-block me-2">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Generate
                                    </button>
                                </form>
                                @if ($userStaff->count() != 0)
                                    <form action="{{ route('user.reset.pass.staff') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success d-none d-lg-block d-md-block">
                                        <i class="bi bi-arrow-repeat"></i>
                                        Reset Password
                                        </button>
                                    </form>
                                @endif
                            @elseif(Request::is('user/kepala-sekolah'))
                                <form action="{{ route('user.add.kepsek') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary d-none d-lg-block d-md-block me-2">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Generate
                                    </button>
                                </form>
                                @if ($userKepsek->count() != 0)
                                    <form action="{{ route('user.reset.pass.kepsek') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success d-none d-lg-block d-md-block">
                                        <i class="bi bi-arrow-repeat"></i>
                                        Reset Password
                                        </button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('user.add') }}" class="btn btn-primary d-none d-lg-block d-md-block">
                                    <i class="bi bi-plus-lg"></i>
                                    Tambah
                                </a>
                            @endif

                            {{-- Search Button --}}
                            <form action="{{ route('user', $role) }}" method="get" class="ms-lg-auto me-2">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm form-default"
                                        placeholder="Cari data.." aria-describedby="button-addon2" name="keyword"
                                        value="{{ request('keyword') }}">
                                    <button type="submit" class="btn btn-default-2" type="button" id="button-addon2">
                                        Cari
                                    </button>
                                </div>
                            </form>
                            {{-- End --}}

                            {{-- Search Button --}}
                            <a href="{{ route('user', $role) }}" class="btn btn-default-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-arrow-clockwise ms-1" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                    <path
                                        d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                </svg>
                                <span class="d-none d-lg-inline d-md-inline ms-1">
                                    Refresh
                                </span>
                            </a>
                            {{-- End --}}
                        </div>
                        {{-- End --}}

                        @if (Request::is('user/guru'))
                            {{-- Data User Guru Desktop View --}}
                            <div class="alert alert-primary mt-4">
                                <h6>Informasi</h6>
                                <ul>
                                    <li>
                                        Akun guru menggunakan password default seperti akun user yang lain.
                                    </li>
                                    <li>
                                        Jika ingin menambahkan semua/beberapa akun guru, cukup tekan tombol
                                        <b>"Generate"</b>
                                    </li>
                                    <li>
                                        Password default Saku dilihat di menu pengaturan bagian password user.
                                    </li>
                                    <li>
                                        Jika ingin mengubah semua password guru, cukup tekan tombol <b>"Reset Password"</b>
                                    </li>
                                </ul>
                            </div>
                            <table class="table d-none d-lg-table d-md-table desktop-table mt-2">
                                <tr class="tr-table">
                                    <th>No</th>
                                    <th>Nama Guru</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Terdaftar Pada</th>
                                    <th>Password</th>
                                </tr>
                                @forelse ($guru as $Guru)
                                    <tr style="color: rgb(26, 26, 26);">
                                        <td>
                                            {{ $loop->iteration }}).
                                        </td>
                                        <td class="d-flex">
                                            <img src="{{ asset('storage/image/profil/profile.jpg') }}"
                                                class="d-none d-lg-block img-fluid rounded-pill img-user align-self-center me-3"
                                                alt="user-photo">
                                            <div class="d-inline">
                                                <div class="fs-nama">{{ $Guru->nama_guru }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $Guru->username }}</td>
                                        <td>{{ $Guru->email }}</td>
                                        <td>
                                            {!! $Guru->role == 'guru' ? '<i class="bi bi-person-fill-check"></i>' : '' !!}
                                            {{ ucwords($Guru->role) }}
                                        </td>
                                        <td>
                                            @if ($Guru->status == 'aktif')
                                                <form action="{{ route('user.status', ['id' => $Guru->user_id, 'role' => $role]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="badge rounded-pill py-2 text-decoration-none px-3 user-aktif border-0">
                                                    Aktif
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('user.status', ['id' => $Guru->user_id, 'role' => $role]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="badge rounded-pill py-2 text-decoration-none px-3 bg-danger user-non-aktif border-0">
                                                    Non Aktif
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($Guru->user_created_at)->translatedFormat('d F Y') }}
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" onclick="resetPassUser('{{ $Guru->user_id }}')"
                                                class="badge rounded-pill py-2 text-decoration-none px-3 bg-primary reset-pw-user">
                                                Reset
                                                @if ($Guru->request == 'true')
                                                    <i class="bi bi-lock-fill"></i>
                                                @endif
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center border-0">
                                            <p class="text-center my-5 d-none d-md-inline d-lg-inline">Belum ada data user
                                                untuk guru!
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </table>
                            {{-- End Of Data --}}
                        @elseif(Request::is('user/staff'))
                            {{-- Data User Staff Desktop View --}}
                            <div class="alert alert-primary mt-4">
                                <h6>Informasi</h6>
                                <ul>
                                    <li>
                                        Akun staff menggunakan password default seperti akun user yang lain.
                                    </li>
                                    <li>
                                        Jika ingin menambahkan semua/beberapa akun staff, cukup tekan tombol
                                        <b>"Generate"</b>
                                    </li>
                                    <li>
                                        Password default Saku dilihat di menu pengaturan bagian password user.
                                    </li>
                                    <li>
                                        Jika ingin mengubah semua password staff, cukup tekan tombol <b>"Reset Password"</b>
                                    </li>
                                </ul>
                            </div>
                            <table class="table d-none d-lg-table d-md-table desktop-table mt-2">
                                <tr class="tr-table">
                                    <th>No</th>
                                    <th>Nama Staff</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Terdaftar Pada</th>
                                    <th>Password</th>
                                </tr>
                                @forelse ($staff as $Staff)
                                    <tr style="color: rgb(26, 26, 26);">
                                        <td>
                                            {{ $loop->iteration }}).
                                        </td>
                                        <td class="d-flex">
                                            <img src="{{ asset('storage/image/profil/profile.jpg') }}"
                                                class="d-none d-lg-block img-fluid rounded-pill img-user align-self-center me-3"
                                                alt="user-photo">
                                            <div class="d-inline">
                                                <div class="fs-nama">{{ $Staff->nama_guru }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $Staff->username }}</td>
                                        <td>{{ $Staff->email }}</td>
                                        <td>
                                            {!! $Staff->role == 'staff' ? '<i class="bi bi-person-fill-check"></i>' : '' !!}
                                            {{ ucwords($Staff->role) }}
                                        </td>
                                        <td>
                                            @if ($Staff->status == 'aktif')
                                                <form action="{{ route('user.status', ['id' => $Staff->user_id, 'role' => $role]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="badge rounded-pill py-2 text-decoration-none px-3 user-aktif border-0">
                                                    Aktif
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('user.status', ['id' => $Staff->user_id, 'role' => $role]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="badge rounded-pill py-2 text-decoration-none px-3 bg-danger user-non-aktif border-0">
                                                    Non Aktif
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($Staff->user_created_at)->translatedFormat('d F Y') }}
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" onclick="resetPassUser('{{ $Staff->user_id }}')"
                                                class="badge rounded-pill py-2 text-decoration-none px-3 bg-primary reset-pw-user">
                                                Reset
                                                @if ($Staff->request == 'true')
                                                    <i class="bi bi-lock-fill"></i>
                                                @endif
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center border-0">
                                            <p class="text-center my-5 d-none d-md-inline d-lg-inline">Belum ada data user
                                                untuk staff!
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </table>
                            {{-- End Of Data --}}
                        @elseif(Request::is('user/kepala-sekolah'))
                            {{-- Data User Kepala Sekolah Desktop View --}}
                            <div class="alert alert-primary mt-4">
                                <h6>Informasi</h6>
                                <ul>
                                    <li>
                                        Akun staff menggunakan password default seperti akun user yang lain.
                                    </li>
                                    <li>
                                        Jika ingin menambahkan semua/beberapa akun staff, cukup tekan tombol
                                        <b>"Generate"</b>
                                    </li>
                                    <li>
                                        Password default Saku dilihat di menu pengaturan bagian password user.
                                    </li>
                                    <li>
                                        Jika ingin mengubah semua password staff, cukup tekan tombol <b>"Reset Password"</b>
                                    </li>
                                </ul>
                            </div>
                            <table class="table d-none d-lg-table d-md-table desktop-table mt-2">
                                <tr class="tr-table">
                                    <th>No</th>
                                    <th>Nama Staff</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Terdaftar Pada</th>
                                    <th>Password</th>
                                </tr>
                                @forelse ($kepsek as $Kepsek)
                                    <tr style="color: rgb(26, 26, 26);">
                                        <td>
                                            {{ $loop->iteration }}).
                                        </td>
                                        <td class="d-flex">
                                            <img src="{{ asset('storage/image/profil/profile.jpg') }}"
                                                class="d-none d-lg-block img-fluid rounded-pill img-user align-self-center me-3"
                                                alt="user-photo">
                                            <div class="d-inline">
                                                <div class="fs-nama">{{ $Kepsek->nama_guru }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $Kepsek->username }}</td>
                                        <td>{{ $Kepsek->email }}</td>
                                        <td>
                                            {!! $Kepsek->role == 'kepala-sekolah' ? '<i class="bi bi-person-fill-check"></i>' : '' !!}
                                            {{ ucwords($Kepsek->role) }}
                                        </td>
                                        <td>
                                            @if ($Kepsek->status == 'aktif')
                                                <form action="{{ route('user.status', ['id' => $Kepsek->user_id, 'role' => $role]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="badge rounded-pill py-2 text-decoration-none px-3 user-aktif border-0">
                                                    Aktif
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('user.status', ['id' => $Kepsek->user_id, 'role' => $role]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="badge rounded-pill py-2 text-decoration-none px-3 bg-danger user-non-aktif border-0">
                                                    Non Aktif
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($Kepsek->user_created_at)->translatedFormat('d F Y') }}
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" onclick="resetPassUser('{{ $Kepsek->user_id }}')"
                                                class="badge rounded-pill py-2 text-decoration-none px-3 bg-primary reset-pw-user">
                                                Reset
                                                @if ($Kepsek->request == 'true')
                                                    <i class="bi bi-lock-fill"></i>
                                                @endif
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center border-0">
                                            <p class="text-center my-5 d-none d-md-inline d-lg-inline">Belum ada data user
                                                untuk kepala sekolah!
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </table>
                            {{-- End Of Data --}}
                        @else
                            <table class="table d-none d-lg-table d-md-table desktop-table mt-4 table-responsive">
                                <tr class="tr-table">
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Tanggal daftar</th>
                                    <th>Password</th>
                                    <th></th>
                                </tr>
                                @foreach ($user as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}).</td>
                                        <td class="d-flex">
                                            <img src="{{ $item?->foto_profil ? asset('storage/' . $item->foto_profil) : asset('storage/image/profil/profile.jpg') }}"
                                                class="d-none d-lg-block img-fluid rounded-pill img-user align-self-center me-3"
                                                alt="user-photo">
                                            <div class="d-inline">
                                                <div class="fs-nama">{{ $item->name }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $item->username }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>
                                            {!! $item->role == 'admin' ? '<i class="bi bi-person-fill-gear"></i>' : '' !!}
                                            {{ ucwords($item->role) }}
                                        </td>
                                        <td>
                                            @if (Auth::user()->id == $item->id)
                                                <i class="bi bi-key-fill fs-5"></i>
                                            @else
                                                @if ($item->status == 'aktif')
                                                    <form action="{{ route('user.status', $item->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="badge rounded-pill py-2 text-decoration-none px-3 user-aktif border-0">
                                                        Aktif
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('user.status', $item->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="badge rounded-pill py-2 text-decoration-none px-3 bg-danger user-non-aktif border-0">
                                                        Non Aktif
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}
                                        </td>
                                        <td>
                                            @if (Auth::user()->id == $item->id)
                                                <i class="bi bi-key-fill fs-5"></i>
                                            @else
                                                <a href="javascript:void(0);"
                                                    onclick="resetPassUser('{{ $item->id }}')"
                                                    class="badge rounded-pill py-2 text-decoration-none px-3 bg-primary reset-pw-user">
                                                    Reset
                                                    @if ($item->request == 'true')
                                                        <i class="bi bi-lock-fill"></i>
                                                    @endif
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            {!! Auth::user()->id == $item->id
                                                ? '<i class="bi bi-key-fill fs-5"></i>'
                                                : '<a href="javascript:void(0);" id="dropdownMenuButtonMobile" data-bs-toggle="dropdown"aria-expanded="false" class="dropdown-toggle text-decoration-none text-dark"><i class="bi bi-three-dots-vertical"></i></a>' !!}
                                            <ul class="dropdown-menu dropdown-menu-tdesktop shadow"
                                                aria-labelledby="dropdownMenuButtonMobile">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('user.edit', $item->id) }}">
                                                        <i class="me-1 bi bi-pencil-square"></i>
                                                        Ubah
                                                    </a>
                                                </li>
                                                <li>
                                                    <a onclick="hapusData('{{ $item->id }}')" class="dropdown-item"
                                                        href="javascript:void(0);">
                                                        <i class="me-1 bi bi-trash3-fill"></i>
                                                        Hapus
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                        {{-- End --}}

                        {{-- Table User Mobile View --}}
                        @if (request('keyword') && !$user->items())
                            <p class="text-center my-3 d-lg-none d-md-none">Data yang anda cari tidak ada!</p>
                        @elseif(!$user->items())
                            <p class="text-center my-3 d-lg-none d-md-none">Belum ada data user!</p>
                        @else
                            @foreach ($user as $item)
                                <div class="card mb-2 p-2 d-lg-none d-md-none">
                                    <div class="d-flex">
                                        <img src="{{ $item?->foto_profil ? asset('storage/' . $item->foto_profil) : asset('img/logo/profile.jpg') }}"
                                            class="img-fluid rounded-pill img-user align-self-center me-3"
                                            alt="user-photo">
                                        <div class="d-inline">
                                            <div class="fs-nama">{{ $item->name }}</div><br>
                                            {!! $item->role == 'admin'
                                                ? '<i class="bi bi-person-fill-gear"></i>'
                                                : '<i class="bi bi-person-fill-check"></i>' !!}
                                            {{ ucwords($item->role) }}
                                        </div>
                                        @if (Auth::user()->id == $item->id)
                                            <i class="bi bi-key-fill fs-5 ms-auto"></i>
                                        @else
                                            <a href="javascript:void(0);" id="dropdownMenuButtonMobile"
                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                class="dropdown-toggle text-decoration-none text-dark ms-auto">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </a>
                                        @endif
                                        <ul class="dropdown-menu dropdown-menu-tdesktop shadow"
                                            aria-labelledby="dropdownMenuButtonMobile">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('user.edit', $item->id) }}">
                                                    <i class="me-1 bi bi-pencil-square"></i>
                                                    Ubah
                                                </a>
                                            </li>
                                            <li>
                                                @if ($item->status == 'aktif')
                                                    <form action="{{ route('user.status', $item->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success text-decoration-none">
                                                        <i class="me-1 bi bi-toggle-on"></i>
                                                        Aktif
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('user.status', $item->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-danger text-decoration-none">
                                                        <i class="me-1 bi bi-toggle-off"></i>
                                                        Non Aktif
                                                        </button>
                                                    </form>
                                                @endif
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);"
                                                    onclick="resetPassUser('{{ $item->id }}')"
                                                    class="dropdown-item text-primary text-decoration-none reset-pw-user">
                                                    <i class="me-1 bi bi-shield-lock-fill"></i>
                                                    Reset
                                                    @if ($item->request == 'true')
                                                        <i class="bi bi-lock-fill"></i>
                                                    @endif
                                                </a>
                                            </li>
                                            <li>
                                                <a onclick="hapusData('{{ $item->id }}')" class="dropdown-item"
                                                    href="javascript:void(0);">
                                                    <i class="me-1 bi bi-trash3-fill"></i>
                                                    Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        {{-- End --}}

                        {{-- Pagination --}}
                        {{ $user->links() }}
                        {{-- End --}}
                    </div>
                </div>
            </div>

        </div>
        <a href="{{ route('user.add') }}" class="btn btn-primary add-btn-siswa-mbl d-lg-none d-md-none">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div>

    {{-- Modal Hapus User --}}
    <div class="modal fade" id="hapusUser" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="hapusUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="hapusUserLabel">Hapus User</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('user.delete') }}" method="post">
                        @csrf
                        @method('delete')
                        <p class="text-center peringatan-modal">Anda yakin ingin menghapus data ini?</p>
                        <div class="button-modal d-flex justify-content-center">
                            <input type="hidden" name="id" id="id-user">
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

    {{-- Modal reset password --}}
    <div class="modal fade" id="resetUser" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="resetUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="resetUserLabel">Reset Password</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('user.reset', $role) }}" method="post">
                        @csrf
                        @method('post')
                        <p class="text-center peringatan-modal">Anda yakin ingin mereset password user ini?</p>
                        <div class="button-modal d-flex justify-content-center">
                            <input type="hidden" name="id" id="id-rp-user">
                            <button type="button" class="btn btn-default-2 me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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

        // Hapus data user
        function hapusData(id) {
            $('#hapusUser').modal('show');
            $('#id-user').val(id);
        }

        // Reset Password user
        function resetPassUser(id) {
            $('#resetUser').modal('show');
            $('#id-rp-user').val(id);
        }
    </script>
@endpush
