@extends('layout.app')
@section('title', 'Kartu Identitas')
@section('content')
    <div class="content content-sm">
        <div class="container">
            <div class="breadcrumb">Kartu Identitas</div>

            @if (session('sukses'))
                <div class="alert alert-success mt-3">{{ session('sukses') }}</div>
            @endif

            <div class="mt-4">
                <ul class="nav nav-tabs" role="tablist" aria-label="Jenis kartu identitas">
                    <li class="nav-item">
                        <a class="nav-link {{ $jenis === 'pelajar' ? 'active' : '' }}"
                            href="{{ route('kartu-identitas', ['jenis' => 'pelajar']) }}"><i
                                class="bi bi-mortarboard me-1"></i>
                            Pelajar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $jenis === 'guru' ? 'active' : '' }}"
                            href="{{ route('kartu-identitas', ['jenis' => 'guru']) }}"><i
                                class="bi bi-person-workspace me-1"></i>
                            Guru
                        </a>
                    </li>
                </ul>
                <section class="card p-3 p-lg-4 card-content">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Pilih {{ $jenis }}</h5>
                            <p class="text-muted small mb-0">Pilih satuan atau buat semua hasil. Maksimal 500 kartu per
                                proses.</p>
                        </div>
                        @can('admin')
                            <a href="{{ route('kartu-identitas.templates') }}"
                                class="btn btn-outline-primary align-self-start"><i class="bi bi-images me-1"></i>Kelola
                                template</a>
                        @endcan
                    </div>

                    @if (!isset($templates[$jenis . '.depan']))
                        <div class="alert alert-warning">Template depan {{ $jenis }} belum aktif. Hubungi admin untuk
                            mengatur template.</div>
                    @endif
                    @if ($jenis === 'guru' && !isset($templates['guru.belakang']))
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i>
                            Template belakang guru belum
                            tersedia. Kartu guru dapat dicetak sisi depan saja.
                        </div>
                    @endif

                    <form method="get" action="{{ route('kartu-identitas') }}" class="row g-2 mb-3">
                        <input type="hidden" name="jenis" value="{{ $jenis }}">
                        @if ($jenis === 'pelajar')
                            <div class="col-12 col-md-5 col-lg-4">
                                <label for="filter-kelas" class="form-label">Kelas</label>
                                <select id="filter-kelas" name="kelas" class="form-select">
                                    @foreach ($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}" @selected($kelasId === $kelas->id)>
                                            {{ $kelas->tingkat }} {{ $kelas->nama_jurusan }} {{ $kelas->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-12 col-md-8 col-lg-6">
                            <label for="search-person" class="form-label">Cari nama</label>
                            <div class="input-group">
                                <input id="search-person" name="q" value="{{ $keyword }}" class="form-control"
                                    placeholder="Masukkan nama">
                                <button class="btn btn-outline-secondary" type="submit"><i
                                        class="bi bi-search me-1"></i>Cari
                                </button>
                            </div>
                        </div>
                    </form>

                    <form method="post" action="{{ route('kartu-identitas.preview') }}">
                        @csrf
                        <input type="hidden" name="jenis" value="{{ $jenis }}">
                        @if ($kelasId)
                            <input type="hidden" name="kelas" value="{{ $kelasId }}">
                        @endif
                        <input type="hidden" name="q" value="{{ $keyword }}">
                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width:3rem">
                                            <span class="visually-hidden">Pilih</span>
                                        </th>
                                        <th scope="col">
                                            Nama
                                        </th>
                                        <th scope="col">
                                            {{ $jenis === 'pelajar' ? 'NIS' : 'NIP' }}
                                        </th>
                                        <th scope="col">
                                            {{ $jenis === 'pelajar' ? 'Kelas' : 'Jabatan' }}
                                        </th>
                                        <th scope="col">
                                            Foto
                                        </th>
                                    </tr>
                                </thead>
                                {{-- @dd($people) --}}
                                <tbody>
                                    @forelse ($people as $person)
                                        @php
                                            $personName =
                                                $jenis === 'pelajar' ? $person->nama_lengkap : $person->nama_guru;
                                            $personId =
                                                $jenis === 'pelajar' ? $person->nis ?? '-' : ($person->nip ?: '-');
                                            $personDetail =
                                                $jenis === 'pelajar'
                                                    ? $person->kartu_tingkat .
                                                            ' ' .
                                                            $person->kartu_jurusan .
                                                            ' ' .
                                                            $person->kartu_kelas ??
                                                        '-'
                                                    : ($person->namaJabatan?->nama_jabatan ?:
                                                    '-');
                                            $hasPhoto = $person->kartu_foto_tersedia;
                                        @endphp
                                        <tr>
                                            <td>
                                                <input class="form-check-input" type="checkbox" name="ids[]"
                                                    value="{{ $person->id }}" aria-label="Pilih {{ $personName }}">
                                            </td>
                                            <td class="fw-semibold">{{ $personName }}</td>
                                            <td>{{ $personId ?: '-' }}</td>
                                            <td>{{ $personDetail }}</td>
                                            <td>
                                                @if ($hasPhoto)
                                                    <span class="badge bg-success text-success-emphasis">
                                                        Tersedia
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-warning-emphasis">
                                                        Belum tersedia
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                Tidak ada data yang
                                                cocok.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                            <small class="text-muted">Daftar memakai roster tahun ajar aktif. Pilihan harus berasal dari
                                halaman ini.</small>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-outline-primary" type="submit" @disabled(!isset($templates[$jenis . '.depan']))>
                                    <i class="bi bi-eye me-1"></i>
                                    Pratinjau pilihan
                                </button>
                                <button class="btn btn-primary" type="submit"
                                    formaction="{{ route('kartu-identitas.generate-all') }}" formmethod="post"
                                    onclick="return confirm('Buat kartu untuk semua data pada kelas dan pencarian ini?')"
                                    @disabled(!isset($templates[$jenis . '.depan']))>
                                    <i class="bi bi-collection me-1"></i>
                                    Generate semua hasil
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="mt-3">{{ $people->links() }}</div>
                </section>
            </div>
        </div>
    </div>
@endsection
