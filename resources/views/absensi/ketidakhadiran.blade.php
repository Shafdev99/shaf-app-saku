@extends('layout.app')
@section('title', 'Izin dan Ketidakhadiran')
@section('content')
    @php($tab = request('tab', 'guru'))
    <div class="content content-sm">
        <div class="container">
            <div class="breadcrumb">Izin / Sakit / Dinas / Dispensasi</div>

            <div class="row mt-4">
                <div class="col-12">
                    <ul class="nav nav-tabs mt-2" role="tablist" style="margin-bottom: -4px;">
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('ketidakhadiran', ['tab' => 'guru']) }}"
                                class="nav-link {{ $tab === 'guru' ? 'active' : '' }}" @if ($tab === 'guru') aria-current="page" @endif>
                                Guru
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('ketidakhadiran', ['tab' => 'siswa']) }}"
                                class="nav-link {{ $tab === 'siswa' ? 'active' : '' }}" @if ($tab === 'siswa') aria-current="page" @endif>
                                Siswa
                            </a>
                        </li>
                    </ul>

                    <div class="card p-3 p-lg-4 card-content">
                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif
                        @if (session('sukses'))
                            <div class="alert alert-success">{{ session('sukses') }}</div>
                        @endif

                        @if ($tab === 'siswa')
                            <h6 class="fw-bold mb-3">Permohonan Izin / Sakit Siswa</h6>
                            @can('admin')
                                <form action="{{ route('ketidakhadiran.siswa.store') }}" method="post" class="mb-4">
                                    @csrf
                                    <input type="hidden" name="tab" value="siswa">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="siswa_id">Siswa</label>
                                            <select class="form-select" id="siswa_id" name="siswa_id" required>
                                                <option value="">Pilih siswa</option>
                                                @foreach ($siswaAktif as $siswa)
                                                    <option value="{{ $siswa->id }}" @selected(old('siswa_id') === $siswa->id)>
                                                        {{ $siswa->nama_lengkap }} · {{ $siswa->nis }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="tanggal_mulai">Dari</label>
                                            <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control"
                                                value="{{ old('tanggal_mulai') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="tanggal_selesai">Sampai</label>
                                            <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control"
                                                value="{{ old('tanggal_selesai') }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="status_siswa">Status</label>
                                            <select class="form-select" id="status_siswa" name="status" required>
                                                <option value="IZIN" @selected(old('status') === 'IZIN')>Izin</option>
                                                <option value="SAKIT" @selected(old('status') === 'SAKIT')>Sakit</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="kategori_izin">Kategori izin</label>
                                            <select class="form-select" id="kategori_izin" name="kategori_izin">
                                                <option value="">Pilih kategori</option>
                                                <option value="keluarga" @selected(old('kategori_izin') === 'keluarga')>Keluarga</option>
                                                <option value="kegiatan" @selected(old('kategori_izin') === 'kegiatan')>Kegiatan</option>
                                                <option value="lainnya" @selected(old('kategori_izin') === 'lainnya')>Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="status_dokumen">Status surat dokter</label>
                                            <select class="form-select" id="status_dokumen" name="status_dokumen">
                                                <option value="belum_ada" @selected(old('status_dokumen') === 'belum_ada')>Belum ada</option>
                                                <option value="diterima" @selected(old('status_dokumen') === 'diterima')>Diterima</option>
                                                <option value="diverifikasi" @selected(old('status_dokumen') === 'diverifikasi')>Diverifikasi</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="keterangan_siswa">Keterangan / alasan</label>
                                            <textarea class="form-control" id="keterangan_siswa" name="keterangan" rows="3" required>{{ old('keterangan') }}</textarea>
                                            <small class="text-muted">Untuk izin lebih dari 3 hari, tuliskan alasan secara lengkap.</small>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary" type="submit">Simpan izin / sakit siswa</button>
                                        </div>
                                    </div>
                                </form>
                            @endcan

                            <h6 class="fw-bold mt-2 mb-3">Daftar Izin / Sakit Siswa</h6>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr><th>Siswa</th><th>Periode</th><th>Status</th><th>Keterangan</th><th>Pemeriksaan</th><th></th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($dataSiswa as $item)
                                            <tr>
                                                <td>{{ $item->siswa?->nama_lengkap ?? 'Siswa tidak tersedia' }}</td>
                                                <td>{{ $item->tanggal_mulai->format('d-m-Y') }} — {{ $item->tanggal_selesai->format('d-m-Y') }}</td>
                                                <td>{{ $item->status }}</td>
                                                <td>{{ $item->keterangan }}</td>
                                                <td>
                                                    @if ($item->perlu_pemeriksaan)
                                                        <span class="badge bg-warning text-dark">Perlu pemeriksaan {{ $item->status === 'SAKIT' ? 'surat dokter' : 'alasan kuat' }}</span>
                                                    @else
                                                        <span class="badge bg-success">Tidak perlu</span>
                                                    @endif
                                                    <br><small>{{ $item->status_dokumen }}</small>
                                                </td>
                                                <td>
                                                    @can('admin')
                                                        <form action="{{ route('ketidakhadiran.siswa.destroy', $item) }}" method="post">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus"><i class="bi bi-trash"></i></button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted">Belum ada izin / sakit siswa.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $dataSiswa->appends(['tab' => 'siswa'])->links() }}
                        @else
                            @can('admin')
                                <h6 class="fw-bold mb-3">Permohonan Izin Guru</h6>
                                <form action="{{ route('ketidakhadiran.store') }}" method="post" class="row g-3 mb-4">
                                    @csrf
                                    <input type="hidden" name="tab" value="guru">
                                    <div class="col-md-4">
                                        <label class="form-label" for="guru_id">Guru</label>
                                        <select class="form-select" id="guru_id" name="guru_id" required>
                                            <option value="">Pilih guru</option>
                                            @foreach ($guru as $item)
                                                <option value="{{ $item->id }}" @selected(old('guru_id') === $item->id)>{{ $item->nama_guru }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="tanggal">Tanggal</label>
                                        <input class="form-control" type="date" id="tanggal" name="tanggal"
                                            value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="status_guru">Status</label>
                                        <select class="form-select" id="status_guru" name="status" required>
                                            @foreach (['IZIN', 'SAKIT', 'DINAS', 'DISPENSASI'] as $status)
                                                <option value="{{ $status }}" @selected(old('status') === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-save me-1"></i>Simpan</button>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="keterangan_guru">Keterangan</label>
                                        <textarea class="form-control" id="keterangan_guru" name="keterangan" rows="3" required>{{ old('keterangan') }}</textarea>
                                    </div>
                                </form>
                            @endcan

                            <h6 class="fw-bold mt-2 mb-3">Daftar Ketidakhadiran Guru</h6>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead><tr><th>Tanggal</th><th>Guru</th><th>Status</th><th>Keterangan</th><th></th></tr></thead>
                                    <tbody>
                                        @forelse ($data as $item)
                                            <tr>
                                                <td>{{ $item->tanggal->format('d-m-Y') }}</td>
                                                <td>{{ $item->guru?->nama_guru ?? 'Guru tidak tersedia' }}</td>
                                                <td><span class="badge bg-info text-dark">{{ $item->status }}</span></td>
                                                <td>{{ $item->keterangan }}</td>
                                                <td>
                                                    @can('admin')
                                                        <form action="{{ route('ketidakhadiran.destroy', $item) }}" method="post">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus"><i class="bi bi-trash"></i></button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">Belum ada data ketidakhadiran guru.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $data->appends(['tab' => 'guru'])->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
