<!-- Content -->
@extends('layout.app')
@section('title', 'Atur Absensi')
@section('content')
    <div class="content content-sm">

        <div class="container">

            {{-- Breadcrumb --}}
            <div class="breadcrumb">
                Atur Absensi
            </div>
            {{-- End --}}

            <div class="row mt-4">
                {{-- Sisi Kiri --}}
                <div class="col-6 mx-auto col-lg-6 col-md-4 d-none d-md-block">

                    {{-- Card Halaman Absensi --}}
                    <div class="card p-lg-3 pb-lg-0 p-2 card-content mb-3">
                        <div class="alert alert-primary">
                            <h6 class="fw-bold mb-3">Halaman Absensi</h6>
                            <p class="text-muted mb-0">Silakan klik tombol di bawah ini untuk masuk ke halaman absensi.</p>
                            <a href="{{ route('absensi.scan.view') }}" target="_blank" class="btn btn-primary mt-3">
                                Halaman Absensi
                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Card Pengaturan Hari Absensi --}}
                    <div class="card p-lg-3 p-2 card-content">
                        <div class="mb-3 alert alert-info">
                            <h6 class="fw-bold mb-3">Atur Hari Absensi</h6>
                            <p class="text-muted mb-0">Pengaturan hari absensi digunakan untuk mengatur hari guru dan
                                pegawai
                                dapat melakukan absensi. Pengaturan ini hanya dapat diubah oleh administrator.</p>

                        </div>
                        <div class="p-4 border rounded">
                            <h6 class="fw-bold">Hari Kerja</h6>
                            <div class="row g-2 mb-4">
                                @foreach ([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'] as $nomorHari => $namaHari)
                                    <div class="col-6">
                                        @if (in_array($nomorHari, old('hari_kerja', $pengaturan->hari_kerja ?: [1, 2, 3, 4, 5])))
                                            @can('admin')
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="hari_kerja[]"
                                                        value="{{ $nomorHari }}" form="form-pengaturan-absensi" checked>
                                                    <span class="form-check-label">{{ $namaHari }}</span>
                                                </label>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $namaHari }}</span>
                                            @endcan
                                        @else
                                            @can('admin')
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="hari_kerja[]"
                                                        value="{{ $nomorHari }}" form="form-pengaturan-absensi">
                                                    <span class="form-check-label">{{ $namaHari }}</span>
                                                </label>
                                            @else
                                                <span class="text-muted">{{ $namaHari }}</span>
                                            @endcan
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <h6 class="fw-bold">Hari Libur Khusus</h6>
                            @can('admin')
                                <form action="{{ route('hari.libur.store') }}" method="post" class="row g-2 mb-3">
                                    @csrf
                                    <div class="col-md-5">
                                        <input type="date" name="tanggal" class="form-control form-borderless" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="keterangan" class="form-control form-borderless"
                                            placeholder="Keterangan" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-outline-primary w-100" type="submit">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </form>
                            @endcan
                            @forelse ($hariLibur as $libur)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <span>{{ $libur->tanggal->format('d-m-Y') }} - {{ $libur->keterangan }}</span>
                                    @can('admin')
                                        <form action="{{ route('hari.libur.destroy', $libur) }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit"
                                                title="Hapus hari libur">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            @empty
                                <small class="text-muted">Belum ada hari libur khusus.</small>
                            @endforelse
                        </div>
                    </div>

                </div>

                {{-- Sisi Kanan --}}
                <div class="col-6 mx-auto col-lg-6 col-md-4 d-none d-md-block">

                    {{-- Card Pengaturan Waktu Absensi --}}
                    <div class="card p-lg-3 p-2 card-content">
                        {{-- ================================
                                  |    MAIN CONTENT   |
                             ================================ --}}
                        <div class="mb-3 alert alert-info">
                            <h6 class="fw-bold mb-3">Atur Waktu Absensi</h6>
                            <p class="text-muted mb-0">Pengaturan waktu absensi digunakan untuk mengatur jam masuk dan
                                jam pulang guru dan pegawai. Pengaturan ini hanya dapat diubah oleh administrator.</p>

                        </div>
                        <div class="p-4 border rounded">
                            <div class="alert alert-secondary">
                                <h6 class="fw-bold">Pengaturan Siswa</h6>
                                <p class="mb-0">Aturan siswa disimpan terpisah dari jam dan toleransi guru. Evaluasi status dan WhatsApp berjalan melalui scheduler Laravel.</p>
                            </div>
                            @can('admin')
                                <form id="form-pengaturan-absensi" action="{{ route('atur.absensi.update') }}" method="post">
                                    @csrf
                                    @method('PUT')

                                    <h6 class="fw-bold">Aturan Guru dan Tenaga Pendidik</h6>

                                    <div class="mb-4">
                                        <label for="jam_masuk" class="form-label fw-bold">Jam Masuk</label>
                                        <input type="time" name="jam_masuk" id="jam_masuk"
                                            class="form-control form-borderless @error('jam_masuk') is-invalid @enderror"
                                            value="{{ old('jam_masuk', substr($pengaturan->jam_masuk, 0, 5)) }}" required>
                                        <small class="text-muted">Waktu mulai presensi masuk.</small>
                                        @error('jam_masuk')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="toleransi_terlambat" class="form-label fw-bold">Toleransi Keterlambatan Guru</label>
                                        <div class="input-group"><input type="number" name="toleransi_terlambat" id="toleransi_terlambat" min="0" class="form-control @error('toleransi_terlambat') is-invalid @enderror" value="{{ old('toleransi_terlambat', $pengaturan->toleransi_terlambat) }}" required><span class="input-group-text">menit</span></div>
                                        @error('toleransi_terlambat') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <hr><h6 class="fw-bold">Aturan Siswa</h6>
                                    @foreach ([
                                        'jam_masuk_siswa' => ['Jam mulai scan masuk siswa', $pengaturan->jam_masuk_siswa ?: '06:00'],
                                        'batas_scan_masuk_siswa' => ['Batas scan masuk siswa', $pengaturan->batas_scan_masuk_siswa ?: '07:00'],
                                        'jam_pulang_siswa' => ['Jam mulai scan pulang siswa', $pengaturan->jam_pulang_siswa ?: '13:00'],
                                        'batas_jam_pulang_siswa' => ['Batas scan pulang siswa', $pengaturan->batas_jam_pulang_siswa ?: '17:00'],
                                        'waktu_notifikasi_siswa' => ['Waktu evaluasi WhatsApp ketidakhadiran', $pengaturan->waktu_notifikasi_siswa ?: '07:00'],
                                    ] as $name => [$label, $value])
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" for="{{ $name }}">{{ $label }}</label>
                                            <input type="time" id="{{ $name }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" value="{{ old($name, substr($value, 0, 5)) }}" required>
                                            @error($name) <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    @endforeach
                                    <div class="mb-4">
                                        <label class="form-label fw-bold" for="jeda_alpa_siswa">Jeda sebelum status Alpa</label>
                                        <div class="input-group"><input type="number" id="jeda_alpa_siswa" name="jeda_alpa_siswa" min="0" max="12" class="form-control @error('jeda_alpa_siswa') is-invalid @enderror" value="{{ old('jeda_alpa_siswa', $pengaturan->jeda_alpa_siswa ?? 3) }}" required><span class="input-group-text">jam setelah batas scan masuk</span></div>
                                        @error('jeda_alpa_siswa') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="jam_pulang" class="form-label fw-bold">Jam Pulang</label>
                                        <input type="time" name="jam_pulang" id="jam_pulang"
                                            class="form-control form-borderless @error('jam_pulang') is-invalid @enderror"
                                            value="{{ old('jam_pulang', substr($pengaturan->jam_pulang, 0, 5)) }}" required>
                                        <small class="text-muted">Waktu paling awal guru atau pegawai dapat melakukan presensi
                                            pulang.</small>
                                        @error('jam_pulang')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="batas_jam_pulang" class="form-label fw-bold">Batas Jam Pulang</label>
                                        <input type="time" name="batas_jam_pulang" id="batas_jam_pulang"
                                            class="form-control form-borderless @error('batas_jam_pulang') is-invalid @enderror"
                                            value="{{ old('batas_jam_pulang', substr($pengaturan->batas_jam_pulang, 0, 5)) }}"
                                            required>
                                        <small class="text-muted">Batas terakhir presensi pulang.</small>
                                        @error('batas_jam_pulang')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary float-end">
                                        <i class="bi bi-save me-1"></i> Simpan
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-info mb-0">Pengaturan waktu absensi hanya dapat diubah oleh
                                    administrator.
                                </div>
                            @endcan
                            {{-- ================================
                                  | END MAIN CONTENT |
                             ================================ --}}
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <a href="{{ route('siswa.add') }}" class="btn btn-primary add-btn-siswa-mbl d-lg-none d-md-none">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div>



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
    </script>
@endpush
