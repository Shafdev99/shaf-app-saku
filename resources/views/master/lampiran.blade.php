<!-- Content -->
@extends('layout.app')
@section('title', 'Daftar Lampiran')
@section('content')
    <div class="content content-sm">

        <div class="container">

            {{-- Breadcrumb --}}
            <div class="breadcrumb">
                Data Lampiran
            </div>
            {{-- End --}}

            <div class="row mt-4">
                <div class="col-lg-6 col-md-6 col mx-auto">

                    <div class=" card p-lg-3 p-2 card-content">
                        {{-- Header Button --}}
                        <div class="d-flex mt-2 mb-3 mb-lg-0 ">
                            <h6 class="d-md-none d-lg-none ms-2 my-auto">
                                Daftar Lampiran
                            </h6>
                            <a class="btn btn-primary d-none d-lg-block d-md-block" href="javascript:void(0);"
                                onclick="addLampiran()" aria-expanded="false">
                                <i class="bi bi-plus-lg"></i>
                                Tambah
                            </a>

                            {{-- Tombol Refresh --}}
                            <a href="{{ route('lampiran') }}" class="btn btn-default-2 ms-auto">
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
                        </div>
                        {{-- End --}}

                        {{-- Table lampiran Desktop View --}}
                        @if ($lampiran)
                            <table class="table desktop-table mt-md-4 mt-lg-4">
                                <tr class="tr-table">
                                    <th>No</th>
                                    <th>Lampiran</th>
                                    <th>Keterangan</th>
                                    <th></th>
                                </tr>
                                @foreach ($lampiran as $row)
                                    <tr style="color: rgb(26, 26, 26);">
                                        <td>
                                            {{ $loop->iteration }}).
                                        </td>
                                        <td>
                                            <div class="badge-globe px-3 my-auto">
                                                <span class="fs-6">
                                                    {{ $row->lampiran }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $row->status }}
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);"
                                                onclick="editLampiran('{{ $row->lampiran }}','{{ $row->status }}','{{ $row->id }}')"
                                                class="text-decoration-none">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="javascript:void(0);" onclick="deleteLampiran('{{ $row->id }}')"
                                                class="text-decoration-none text-danger ms-1">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @else
                            <p class="text-center my-5 d-none d-md-inline d-lg-inline">Belum ada data lampiran!</p>
                        @endif
                        {{-- End --}}

                    </div>
                </div>
            </div>
        </div>

        <a href="javascript:void(0);" onclick="addLampiran()" class="btn btn-primary add-btn-siswa-mbl d-lg-none d-md-none">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div>

    {{-- Modal Tambah lampiran --}}
    <div class="modal fade" id="AddLampiran" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="AddLampiranLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="AddLampiranLabel">Tambah Lampiran</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('lampiran.add') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <label for="" class="mb-2">Nama Lampiran</label>
                        <input type="text" class="form-control mb-4" name="lampiran" placeholder="Masukan lampiran!"
                            required>
                        <label for="" class="mb-2">Status</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="Wajib" {{ old('status') == 'Wajib' ? 'selected' : '' }}>Wajib</option>
                            <option value="Tidak Wajib" {{ old('status') == 'Tidak Wajib' ? 'selected' : '' }}>Tidak Wajib
                            </option>
                        </select>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="reset" class="btn btn-default-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send-fill me-1"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- End --}}

    {{-- Modal Edit lampiran --}}
    <div class="modal fade" id="editLampiran" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="editLampiranLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="editLampiranLabel">Ubah lampiran</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('lampiran.edit') }}" method="post">
                    @csrf
                    @method('put')
                    <div class="modal-body">
                        <label for="" class="mb-2">Nama Lampiran</label>
                        <input type="hidden" class="form-control" id="lampiran-id-edit" name="lampiran_id">
                        <input type="text" class="form-control mb-4" id="lampiran" name="lampiran"
                            placeholder="Masukan lampiran!" required>
                        <label for="" class="mb-2">Status</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" id="status"
                            required>
                            <option value="Wajib" {{ old('status') == 'Wajib' ? 'selected' : '' }}>Wajib</option>
                            <option value="Tidak Wajib" {{ old('status') == 'Tidak Wajib' ? 'selected' : '' }}>Tidak Wajib
                            </option>
                        </select>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="reset" class="btn btn-default-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send-fill me-1"></i>
                            Ubah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- End --}}

    {{-- Modal Delete lampiran --}}
    <div class="modal fade" id="deleteLampiran" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="deleteLampiranLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold" id="deleteLampiranLabel">Hapus lampiran</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('lampiran.delete') }}" method="post">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <p id="keterangan-to-lampiran" class="text-center peringatan-modal my-auto">
                            Apakah anda yakin ingin menghapus lampiran ini?
                        </p>
                        <input type="hidden" id="lampiran-id-delete" name="lampiran_id">
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-default-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger" id="delBtnlampiran">
                            <i class="me-1 bi bi-trash3-fill"></i>
                            Hapus
                        </button>
                    </div>
                </form>
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

        // Notif untuk data yang tidak lolos validasi
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.warning('{{ ucwords($error) }}');
            @endforeach
        @endif
        // End

        // Modal tambah lampiran
        function addLampiran() {
            $('#AddLampiran').modal('show');
        }

        // Modal edit lampiran
        function editLampiran(lampiran, ketlampiran, id) {
            $('#lampiran-id-edit').val(id);
            $('#lampiran').val(lampiran);
            $('#status').val(ketlampiran);
            $('#editLampiran').modal('show');
        }

        // Modal hapus lampiran
        function deleteLampiran(id) {
            // url
            const url = "{{ url('/cekLampiran') }}/";

            $.getJSON(url + id,
                function(json) {
                    if (json != false) {
                        $('#keterangan-to-lampiran').text('Lampiran ini sudah digunakan!');
                        $('#delBtnlampiran').addClass('d-none');
                    } else {
                        $('#keterangan-to-lampiran').text('Apakah anda yakin ingin menghapus lampiran ini?');
                        $('#delBtnlampiran').removeClass('d-none');
                        $('#lampiran-id-delete').val(id);
                    }
                    $('#lampiran-id-delete').val(id);
                    $('#deleteLampiran').modal('show');
                }
            );

        }
    </script>
@endpush
