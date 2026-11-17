@extends('layout.app')
@section('title', 'Rekap Absensi')
@section('content')
    @php($jenisRekap = $jenis ?? 'guru')
    <div class="content content-sm">
        <div class="container">
            <div class="breadcrumb">Rekap Absensi</div>

            @if (session('sukses'))
                <div class="alert alert-success mt-3">{{ session('sukses') }}</div>
            @endif

            <div class="mt-4">
                <ul class="nav nav-tabs" role="tablist" aria-label="Jenis rekap absensi">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $jenisRekap === 'guru' ? 'active' : '' }}"
                            href="{{ route('laporan.absensi', ['jenis' => 'guru']) }}"
                            @if ($jenisRekap === 'guru') aria-current="page" @endif>
                            <i class="bi bi-person-workspace me-1"></i>Guru
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $jenisRekap === 'siswa' ? 'active' : '' }}"
                            href="{{ route('laporan.absensi', ['jenis' => 'siswa']) }}"
                            @if ($jenisRekap === 'siswa') aria-current="page" @endif>
                            <i class="bi bi-people me-1"></i>Siswa
                        </a>
                    </li>
                </ul>

                <section class="card p-3 p-lg-4 card-content" aria-labelledby="filter-heading">
                    <div class="mb-3">
                        <h5 class="fw-bold mb-1" id="filter-heading">Filter rekap {{ $jenisRekap }}</h5>
                        <p class="text-muted small mb-0">Pilih rentang tanggal dan data yang ingin ditampilkan.</p>
                    </div>
                    <form method="get" action="{{ route('laporan.absensi') }}" class="row g-3 align-items-end">
                        <input type="hidden" name="jenis" value="{{ $jenisRekap }}">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label" for="tanggal_mulai">Tanggal mulai</label>
                            <input class="form-control" id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ $filter['tanggal_mulai'] }}">
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label" for="tanggal_akhir">Tanggal akhir</label>
                            <input class="form-control" id="tanggal_akhir" type="date" name="tanggal_akhir" value="{{ $filter['tanggal_akhir'] }}">
                        </div>
                        @if ($jenisRekap === 'siswa')
                            <div class="col-12 col-sm-6 col-lg-2">
                                <label class="form-label" for="siswa_id">Siswa</label>
                                <select class="form-select" id="siswa_id" name="siswa_id">
                                    <option value="">Semua siswa</option>
                                    @foreach ($siswaFilter as $item)
                                        <option value="{{ $item->id }}" @selected(($filter['siswa_id'] ?? '') === $item->id)>{{ $item->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-2">
                                <label class="form-label" for="kelas_id">Kelas</label>
                                <select class="form-select" id="kelas_id" name="kelas_id">
                                    <option value="">Semua kelas</option>
                                    @foreach ($kelasFilter as $item)
                                        <option value="{{ $item->id }}" @selected(($filter['kelas_id'] ?? '') === $item->id)>{{ $item->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label" for="guru_id">Guru</label>
                                <select class="form-select" id="guru_id" name="guru_id">
                                    <option value="">Semua guru</option>
                                    @foreach ($guru as $item)
                                        <option value="{{ $item->id }}" @selected(($filter['guru_id'] ?? '') === $item->id)>{{ $item->nama_guru }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-12 col-sm-6 col-lg-{{ $jenisRekap === 'siswa' ? '2' : '2' }}">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Semua status</option>
                                @foreach ($statistik as $status => $jumlah)
                                    <option value="{{ $status }}" @selected(($filter['status'] ?? '') === $status)>{{ $status === 'BELUM_HADIR' ? 'Belum hadir' : $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Terapkan filter</button>
                            <a class="btn btn-outline-secondary" href="{{ route('laporan.absensi', ['jenis' => $jenisRekap]) }}">Reset</a>
                        </div>
                    </form>
                </section>
            </div>

            <section class="mt-4" aria-label="Ringkasan status absensi">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold mb-0">Ringkasan status</h6>
                    <span class="small text-muted">{{ $filter['tanggal_mulai'] }} — {{ $filter['tanggal_akhir'] }}</span>
                </div>
                <div class="row g-2 g-lg-3">
                    @foreach ($statistik as $status => $jumlah)
                        <div class="col-6 col-md-4 col-xl">
                            <div class="card h-100 p-3 card-content">
                                <small class="text-muted">{{ $status === 'BELUM_HADIR' ? 'Belum hadir' : ucfirst(strtolower($status)) }}</small>
                                <strong class="fs-4 mt-1">{{ number_format($jumlah) }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="card p-3 p-lg-4 card-content mt-4" aria-labelledby="hasil-heading">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1" id="hasil-heading">Data absensi {{ $jenisRekap }}</h5>
                        <p class="small text-muted mb-0">Hasil sesuai filter yang dipilih.</p>
                    </div>
                    <a class="btn btn-outline-success align-self-start align-self-md-auto"
                        href="{{ route('laporan.absensi.export', request()->query()) }}">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Excel
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">{{ $jenisRekap === 'siswa' ? 'Siswa' : 'Guru' }}</th>
                                @if ($jenisRekap === 'siswa') <th scope="col">Kelas</th> @endif
                                <th scope="col">Masuk</th>
                                <th scope="col">Pulang</th>
                                <th scope="col">Status</th>
                                <th scope="col">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($jenisRekap === 'siswa')
                                @forelse ($rows as $row)
                                    <tr>
                                        <td class="text-nowrap">{{ $row->tanggal->format('d-m-Y') }}</td>
                                        <td>{{ $row->siswa?->nama_lengkap ?? 'Siswa tidak tersedia' }}</td>
                                        <td>{{ $row->nama_kelas }}</td>
                                        <td>{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</td>
                                        <td>{{ $row->jam_pulang ? substr($row->jam_pulang, 0, 5) : '-' }}</td>
                                        <td><span class="badge {{ $row->status === 'HADIR' ? 'bg-success-subtle text-success-emphasis' : (in_array($row->status, ['ALPA', 'BELUM_HADIR']) ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis') }}">{{ $row->status === 'BELUM_HADIR' ? 'Belum hadir' : $row->status }}</span></td>
                                        <td>
                                            <span>{{ $row->keterangan ?? '-' }}</span>
                                            @can('admin')
                                                <button class="btn btn-sm btn-outline-secondary ms-1" data-bs-toggle="modal" data-bs-target="#koreksi-{{ $row->id }}">Koreksi</button>
                                                <div class="modal fade" id="koreksi-{{ $row->id }}" tabindex="-1" aria-labelledby="koreksi-label-{{ $row->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
                                                        <form action="{{ route('siswa.absensi.koreksi', $row->siswa_id) }}" method="post">
                                                            @csrf @method('PUT')
                                                            <div class="modal-header"><h5 class="modal-title" id="koreksi-label-{{ $row->id }}">Koreksi absensi {{ $row->siswa?->nama_lengkap }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                                                            <div class="modal-body"><input type="hidden" name="tanggal" value="{{ $row->tanggal->format('Y-m-d') }}"><label class="form-label" for="status-koreksi-{{ $row->id }}">Status</label><select class="form-select mb-3" id="status-koreksi-{{ $row->id }}" name="status">@foreach (['HADIR', 'TERLAMBAT', 'BELUM_HADIR', 'ALPA', 'IZIN', 'SAKIT'] as $status)<option value="{{ $status }}" @selected($row->status === $status)>{{ $status }}</option>@endforeach</select><label class="form-label" for="keterangan-koreksi-{{ $row->id }}">Alasan koreksi</label><textarea class="form-control" id="keterangan-koreksi-{{ $row->id }}" name="keterangan" required placeholder="Jelaskan alasan koreksi">{{ $row->keterangan }}</textarea></div>
                                                            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan koreksi</button></div>
                                                        </form>
                                                    </div></div>
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Belum ada data absensi siswa pada filter ini.</td></tr>
                                @endforelse
                            @else
                                @forelse ($rows as $row)
                                    <tr>
                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d-m-Y') }}</td>
                                        <td>{{ $row['guru'] }}</td>
                                        <td>{{ $row['jam_masuk'] ?? '-' }}</td>
                                        <td>{{ $row['jam_pulang'] ?? '-' }}</td>
                                        <td><span class="badge {{ $row['status'] === 'HADIR' ? 'bg-success-subtle text-success-emphasis' : ($row['status'] === 'TERLAMBAT' ? 'bg-warning-subtle text-warning-emphasis' : ($row['status'] === 'ALFA' ? 'bg-danger-subtle text-danger-emphasis' : 'bg-info-subtle text-info-emphasis')) }}">{{ $row['status'] }}</span></td>
                                        <td>{{ $row['keterangan'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Belum ada data absensi guru pada filter ini.</td></tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
                @if ($jenisRekap === 'siswa')
                    <div class="mt-3">{{ $rows->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
