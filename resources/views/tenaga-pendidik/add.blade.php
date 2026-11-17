<!-- Content -->
@extends('layout.app')
@section('title', 'Tambah Tenaga Pendidik')
@section('content')
    <div class="content">

        <div class="container ">

            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="{{ route('tenaga.pendidik', request()->query()) }}">Tenaga Pendidik </a>
                / Tambah Data
            </div>
            {{-- <h5>Tambah Data</h5> --}}
            <div class="row mt-4">
                <div class="col col-lg-9 mx-auto">
                    <div class="card p-lg-3 py-3 px-0 card-content">
                        {{-- Head Button --}}
                        <div class="d-flex mt-2 mb-3 px-2 justify-content-between">
                            <a href="{{ route('tenaga.pendidik', request()->query()) }}" class="btn btn-primary ms-2">
                                <i class="bi bi-arrow-left"></i>
                                Kembali
                            </a>
                            <h6 class="fw-bold align-self-center me-2">Tambah Data</h6>
                        </div>
                        {{-- End --}}

                        {{-- Form Data --}}
                        <form action="{{ route('tenaga.pendidik.store') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            {{-- Tenaga Pendidik --}}
                            <div class="container-fluid mt-4">
                                <h5 class="fw-bold">Tenaga Pendidik</h5>
                                <hr>
                                <div class="row">
                                    <div class="col">

                                        {{-- Nama Lengkap --}}
                                        <div class="mb-4 mt-2">
                                            <label for="nama-guru" class="form-label">Nama Lengkap
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control @error('nama_guru') is-invalid @enderror" id="nama-guru"
                                                placeholder="Masukan nama lengkap!" name="nama_guru"
                                                value="{{ old('nama_guru') }}">
                                            @error('nama_guru')
                                                <div id="validationServer03Feedback" class="invalid-feedback">
                                                    {{ ucwords($message) }}
                                                </div>
                                            @enderror
                                        </div>

                                        {{-- Jabatan --}}
                                        <div class="mb-4">
                                            <label for="tanggal-lahir" class="form-label">Jabatan
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="jabatan"
                                                class="form-control mb-3 @error('jabatan') is-invalid @enderror" required>
                                                <option value="">Pilih Jabatan</option>
                                                @foreach ($jabatan as $Jabatan)
                                                    <option value="{{ $Jabatan->id }}"
                                                        {{ old('jabatan') == $Jabatan->id ? 'selected' : '' }}>
                                                        {{ $Jabatan->nama_jabatan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('jabatan')
                                                <div id="validationServer03Feedback" class="invalid-feedback">
                                                    {{ ucwords($message) }}
                                                </div>
                                            @enderror
                                        </div>

                                        {{-- No HP --}}
                                        <div class="mb-4">
                                            <label for="no_hp" class="form-label">No HP
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control @error('no_hp') is-invalid @enderror"
                                                id="no_hp" placeholder="Masukan No HP!" name="no_hp"
                                                value="{{ old('no_hp') }}">
                                            @error('no_hp')
                                                <div id="validationServer03Feedback" class="invalid-feedback">
                                                    {{ ucwords($message) }}
                                                </div>
                                            @enderror
                                        </div>

                                        {{-- NIP/NIPY --}}
                                        <div class="mb-4">
                                            <label for="nip" class="form-label">NIP/NIPY
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" class="form-control @error('nip') is-invalid @enderror"
                                                id="nip" placeholder="Masukan NIP/NIPY!" name="nip"
                                                value="{{ old('nip') }}">
                                            @error('nip')
                                                <div id="validationServer03Feedback" class="invalid-feedback">
                                                    {{ ucwords($message) }}
                                                </div>
                                            @enderror
                                        </div>

                                        {{-- Status Kepegawaian --}}
                                        <div class="mb-4">
                                            <label for="status_guru" class="form-label">Status Kepegawaian
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="status_guru"
                                                class="form-control mb-3 @error('status_guru') is-invalid @enderror"
                                                required>
                                                <option value="">Pilih Status</option>
                                                @foreach ($stapeg as $item)
                                                    <option value="{{ $item->id }}"
                                                        {{ old('status_guru') == $item->id ? 'selected' : '' }}>
                                                        {{ $item->status_pegawai . ' ( ' . $item->ket_status_pegawai . ' )' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('status_guru')
                                                <div id="validationServer03Feedback" class="invalid-feedback">
                                                    {{ ucwords($message) }}
                                                </div>
                                            @enderror
                                        </div>

                                        {{-- Foto --}}
                                        <div class="">
                                            <label for="foto_pegawai" class="form-label">Foto Tenaga
                                                Pendidik</label>
                                            <div class="@error('foto_pegawai') dropify-invalid @enderror">
                                                <input type="file" name="foto_pegawai"
                                                    data-allowed-file-extensions="jpg png jpeg" class="dropify" />
                                            </div>
                                            @error('foto_pegawai')
                                                <div id="validationServer03Feedback" class="text-danger mt-1">
                                                    {{ ucwords($message) }}
                                                </div>
                                            @enderror
                                            <ul class="text-secondary mt-2" style="font-size: 12px;">
                                                <li>Format file yang di dukung (JPEG, JPG, PNG)</li>
                                                <li>Maksimum kapasitas file ( 1024 Kb / 1 Mb)</li>
                                                <li>Dimensi yang disarankan ( 479 x 709 Pixel)</li>
                                            </ul>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Simpan --}}
                            <div class="container-fluid mt-5">
                                <div class="row col">
                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary float-end">
                                            <i class="bi bi-send-fill me-1"></i>
                                            Simpan
                                        </button>
                                        <button type="Reset" class="btn btn-default-2 float-end me-2">Reset</button>
                                    </div>
                                </div>
                            </div>

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
