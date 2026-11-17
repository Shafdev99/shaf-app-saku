<!-- Content -->
@extends('layout.app')
@section('title', 'Tautkan RFID Siswa')
@section('content')
    <div class="content">

        <div class="container">

            {{-- Breadcrumb --}}
            <div class="breadcrumb">
                <a href="{{ route('siswa') }}">Data Siswa</a>
                / Tautkan Kartu RFID
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card p-lg-3 py-3 px-0 card-content">
                        {{-- Head Button --}}
                        <div class="d-flex mt-2 mb-3 px-2 justify-content-between">
                            @if ($trigger === 'edit')
                                <a href="{{ route('siswa', request()->query()) }}" class="btn btn-primary ms-2">
                                    <i class="bi bi-arrow-left"></i>
                                    Kembali
                                </a>
                            @else
                                <h5>
                                    Menautkan Data
                                </h5>
                            @endif
                            <h6 class="fw-bold align-self-center me-2">Tautkan Kartu</h6>
                        </div>
                        {{-- End --}}

                        {{-- Form Data --}}
                        <form action="{{ route('siswa.kartu.store') }}" method="post" id="rfid-form">
                            @csrf

                            {{-- Tautkan Kartu --}}
                            <div class="container-fluid mt-4">
                                <h5 class="fw-bold">Tautkan Kartu</h5>
                                <hr>
                                {{-- Baris Data Siswa --}}
                                <div class="row py-3 rounded-3 mb-4 border border-2 shadow-sm">
                                    <div class="col-3 text-center">

                                        {{-- Foto --}}
                                        <img src="{{ $siswa->foto_siswa ? asset('storage/' . $siswa->foto_siswa) : asset('storage/image/profil/profile.jpg') }}"
                                            class="img-fluid d-block mt-2 mx-auto rounded-3 shadow-sm" width="180"
                                            alt="foto_siswa">

                                    </div>
                                    <div class="col">

                                        {{-- Nama Lengkap --}}
                                        <div class="mb-4 mt-2">
                                            <label for="nama-siswa" class="form-label">Nama Lengkap
                                                <span class="text-danger">*</span>
                                            </label>
                                            <h6 class="fw-bold">
                                                {{ $siswa->nama_lengkap }}
                                            </h6>
                                        </div>

                                        {{-- NIS --}}
                                        <div class="mb-4">
                                            <label for="nis" class="form-label">NIS
                                                <span class="text-danger">*</span>
                                            </label>
                                            <h6 class="fw-bold">
                                                {{ $siswa->nis }}
                                            </h6>
                                        </div>

                                        {{-- Kelas --}}
                                        <div class="mb-4">
                                            <label for="kelas" class="form-label">Kelas
                                                <span class="text-danger">*</span>
                                            </label>
                                            <h6 class="fw-bold">
                                                {{ collect([
                                                    $kelasSiswa?->tingkat?->tingkat,
                                                    $kelasSiswa?->jurusan?->nama_jurusan,
                                                    $kelasSiswa?->nama_kelas,
                                                ])->filter()->implode(' ') ?: '-' }}
                                            </h6>
                                        </div>

                                        {{-- @dd($kelasSiswa) --}}

                                    </div>
                                    <div class="col">
                                        <div class="alert alert-primary text-center" style="border: 2px dashed #0842986c;">
                                            <div class="pt-3 mx-auto">
                                                <h6>
                                                    Silakan tempelkan kartu ke mesin RFID untuk menautkan data siswa dengan
                                                    kartu!
                                                </h6>
                                                <p>
                                                    Saat kartu ditempelkan, maka sistem akan secara otomatis menyimpan data
                                                    ke
                                                    database!
                                                </p>
                                            </div>
                                            <img src="{{ asset('img/logo/logo-rfid.png') }}" width="100"
                                                class="img-fluid d-block mx-auto mb-3" alt="">
                                            <span>ID Card</span>
                                            <h5>
                                                <input type="text" id="card_id" name="card_id" readonly
                                                    autocomplete="off" placeholder="0000000000"
                                                    style="background: none; width:130px; border: none; color:#084298;">
                                            </h5>
                                            <small id="rfid-status" class="text-muted">
                                                🔵 Menunggu kartu...
                                            </small>
                                            <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($trigger === 'tambah')
                                {{-- Tombol --}}
                                <div class="container-fluid mt-5">
                                    <div class="row col">
                                        <div class="mb-3">
                                            <a href="{{ route('siswa') }}" class="btn btn-primary float-end">
                                                Lewati
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        // Notif untuk data yang tidak lolos validasi
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.warning('{{ ucwords($error) }}');
            @endforeach
        @endif
        // End

        $('input[name="card_id"]').keyup(function() {
            $('#tampil-id-card').text($(this).val());
        });


        document.addEventListener('DOMContentLoaded', function() {

            const input = document.getElementById('card_id');
            const form = document.getElementById('rfid-form');
            const status = document.getElementById('rfid-status');

            let buffer = '';
            let lastKeyTime = 0;
            let isSubmitting = false;

            // Jeda maksimum antar karakter RFID
            const maxInterval = 50;

            document.addEventListener('keydown', function(event) {

                const now = Date.now();

                // Jika jeda terlalu lama, mulai pembacaan baru
                if (now - lastKeyTime > maxInterval) {
                    buffer = '';
                }

                lastKeyTime = now;

                // RFID biasanya mengirim ENTER setelah UID
                if (event.key === 'Enter') {

                    event.preventDefault();

                    if (buffer.length >= 4 && !isSubmitting) {

                        // Masukkan UID ke input
                        input.value = buffer;

                        // Ubah status
                        if (status) {
                            status.innerHTML = '🟢 Kartu RFID berhasil terbaca';
                            status.classList.remove('text-muted', 'text-warning');
                            status.classList.add('text-success');
                        }

                        console.log('UID RFID:', buffer);

                        // Tandai sedang submit
                        isSubmitting = true;

                        // Beri sedikit waktu agar UI sempat berubah
                        setTimeout(function() {

                            form.requestSubmit();

                        }, 300);
                    }

                    buffer = '';

                    return;
                }

                // Terima karakter dari RFID reader
                if (event.key.length === 1) {

                    event.preventDefault();

                    buffer += event.key;

                    if (status) {
                        status.innerHTML = '🟡 Membaca kartu RFID...';
                        status.classList.remove('text-muted', 'text-success');
                        status.classList.add('text-warning');
                    }
                }

            });

        });
    </script>
@endpush
