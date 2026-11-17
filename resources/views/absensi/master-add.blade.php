<!-- Content -->
@extends('layout.app')
@section('title', 'Tambah Data Absensi Siswa')
@section('content')
    <div class="content">

        <div class="container ">

            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="{{ route('master.absensi') }}">Master Absensi</a>
                / Tambah Data Absensi
            </div>
            {{-- @dump($siswa) --}}
            {{-- <h5>Tambah Data</h5> --}}
            <div class="row mt-4">
                <div class="col col-lg-9 mx-auto">
                    <div class="card p-lg-3 py-3 px-0 card-content">
                        {{-- Head Button --}}
                        <div class="d-flex mt-2 mb-3 px-2 justify-content-between">
                            @php
                                $url = parse_url(url()->full());
                            @endphp
                            <a href="{{ route('master.absensi', $url['query'] ?? false) }}" class="btn btn-primary ms-2">
                                <i class="bi bi-arrow-left"></i>
                                Kembali
                            </a>
                            <h6 class="fw-bold align-self-center me-2">Tambah Data</h6>
                        </div>
                        {{-- End --}}


                        {{-- Form Data --}}
                        <form action="{{ route('siswa.store', $url['query'] ?? false) }}" method="post"
                            enctype="multipart/form-data">
                            @csrf


                            {{-- Master Absensi --}}
                            <div class="container-fluid mt-4">
                                <h5 class="fw-bold">Master Absensi</h5>
                                <hr>
                                <div class="row">
                                    <div class="col">
                                        <div class="row mb-5">
                                            <div class="col-3">
                                                <img src="{{ asset('storage/' . $siswa->foto) }}" alt=""
                                                    class="img-thumbnail img-fluid" width="150">
                                            </div>
                                            <div class="col-6">
                                                <label for="nama-siswa">Nama Siswa</label>
                                                <h4 class="mb-3">{{ $siswa->nama_lengkap }}</h4>
                                                <label for="nama-siswa">NISN</label>
                                                <h4 class="mb-3">{{ $siswa->nisn }}</h4>
                                                <label for="nama-siswa">Kelas</label>
                                                <h4 class="mb-3">
                                                    {{ $siswa->tingkat . ' ' . $siswa->nama_jurusan . ' ' . $siswa->nama_kelas }}
                                                </h4>
                                            </div>
                                        </div>

                                        {{-- ID Card --}}
                                        <div class="mb-4 mt-5">
                                            <label for="id-card" class="form-label">ID Card
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="bd-callout bd-callout-info">
                                                Silakan tempelkan kartu RFID untuk dapat menautkan data siswa dengan ID
                                                Kartu
                                            </div>
                                            <input type="text"
                                                class="form-control @error('id_card') is-invalid @enderror" id="id-card"
                                                placeholder="Masukan ID Card!" name="id_card" value="{{ old('id_card') }}"
                                                autofocus>
                                            @error('username')
                                                <div id="validationServer03Feedback" class="invalid-feedback">
                                                    {{ ucwords($message) }}
                                                </div>
                                            @enderror
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Simpan --}}
                            {{-- <div class="container-fluid mt-5">
                                <div class="row col">
                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary float-end">
                                            <i class="bi bi-send-fill me-1"></i>
                                            Simpan
                                        </button>
                                        <button type="Reset" class="btn btn-default-2 float-end me-2">Reset</button>
                                    </div>
                                </div>
                            </div> --}}

                        </form>
                        {{-- End --}}

                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection
@push('scripts')
    <script type="text/javascript">
        // Konfigurasi Dropify preview Image
        $('.dropify').dropify({
            messages: {
                default: 'Pilih gambar anda! <br> Format gambar yang diwajibkan <br> adalah .JPG, .JPEG, .PNG!',
                replace: 'Mau ganti gambar anda?',
                remove: 'Hapus',
                error: 'Ada kesalahan pada proses upload gambar!'
            }
        });

        function getKelas(jurusanId, tingkatId) {
            const url = "{{ url('/getKelasByJurusan') }}/";
            const oldKelasId = "{{ old('kelas_id') }}";

            $.getJSON(url + jurusanId + '/' + tingkatId,
                function(data) {
                    let html = '';
                    if (data.length === 0) {
                        html += `<option value="">Tidak memiliki kelas</option>`;
                    } else {
                        $.each(data, function(index, value) {
                            html +=
                                `<option value = "${value.id}" >${value.tingkat} ${value.nama_jurusan} ${value.nama_kelas} ( ${value.nama_kurikulum} )</option>`;
                        });
                    }
                    $('#kelas-id').html(html);

                    $("#kelas-id option").map(function() {
                        if ($(this).val() == oldKelasId) {
                            $(this).attr("selected", "selected");
                        }
                    });
                }
            );
        }

        $('#jurusan_id').change(function(e) {
            e.preventDefault();
            const tingkatId = $('#tingkat_id').val();
            getKelas(this.value, tingkatId);
        });

        $('#tingkat_id').change(function(e) {
            e.preventDefault();
            const jurusanId = $('#jurusan_id').val();
            getKelas(jurusanId, this.value);
        });

        $(document).ready(function() {
            const jurusanId = $('#jurusan_id').val();
            const tingkatId = $('#tingkat_id').val();
            getKelas(jurusanId, tingkatId);
        });
    </script>
@endpush
