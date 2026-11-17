@extends('layout.app')
@section('title', 'Kelola Template Kartu')
@section('content')
    <div class="content content-sm">
        <div class="container">
            <div class="breadcrumb">Kelola Template Kartu</div>
            @if (session('sukses')) <div class="alert alert-success mt-3">{{ session('sukses') }}</div> @endif
            @if ($errors->any()) <div class="alert alert-danger mt-3">{{ $errors->first() }}</div> @endif
            <div class="d-flex justify-content-between align-items-center gap-2 mt-4 mb-3">
                <div><h4 class="fw-bold mb-1">Template kartu identitas</h4><p class="text-muted mb-0">Setiap jenis dan sisi dikelola sendiri. File baru baru digunakan setelah admin memeriksa pratinjau dan mengaktifkannya.</p></div>
                <a class="btn btn-outline-secondary" href="{{ route('kartu-identitas') }}">Kembali ke kartu</a>
            </div>

            @foreach (['pelajar' => 'Pelajar', 'guru' => 'Guru'] as $jenis => $label)
                <section class="card p-3 p-lg-4 card-content mb-4">
                    <h5 class="fw-bold mb-3">{{ $label }}</h5>
                    <div class="row g-3">
                        @foreach (['depan' => 'Depan', 'belakang' => 'Belakang'] as $sisi => $sisiLabel)
                            @php
                                $active = $templates->first(fn ($template) => $template->jenis === $jenis && $template->sisi === $sisi && $template->aktif);
                                $pendingTemplates = $templates->filter(fn ($template) => $template->jenis === $jenis && $template->sisi === $sisi && !$template->aktif);
                            @endphp
                            <div class="col-12 col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-semibold mb-0">Sisi {{ $sisiLabel }}</h6>
                                        @if ($active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Belum tersedia</span>@endif
                                    </div>
                                    @if ($active)
                                        <div class="template-thumb mb-3"><img src="{{ route('kartu-identitas.template.image', $active) }}" alt="Template {{ $label }} sisi {{ $sisiLabel }}"></div>
                                    @elseif ($jenis === 'guru' && $sisi === 'belakang')
                                        <div class="template-empty mb-3"><i class="bi bi-image fs-2"></i><span>Template belakang guru belum diunggah.</span></div>
                                    @else
                                        <div class="template-empty mb-3"><span>Upload template untuk mengaktifkan sisi ini.</span></div>
                                    @endif
                                    <form method="post" action="{{ route('kartu-identitas.template.upload', ['jenis' => $jenis, 'sisi' => $sisi]) }}" enctype="multipart/form-data" class="border-top pt-3">
                                        @csrf
                                        <label class="form-label" for="file-{{ $jenis }}-{{ $sisi }}">Unggah / ganti template</label>
                                        <input class="form-control" id="file-{{ $jenis }}-{{ $sisi }}" name="template" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
                                        <div class="form-text mb-2">JPG/JPEG/PNG, maksimal 5 MB, rasio kartu potret, 300–3000 × 500–5100 piksel.</div>
                                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-upload me-1"></i>Unggah untuk pratinjau</button>
                                    </form>
                                    @if ($pendingTemplates->isNotEmpty())
                                        <div class="border-top mt-3 pt-3">
                                            <small class="text-muted d-block mb-2">Menunggu pemeriksaan</small>
                                            @foreach ($pendingTemplates as $pending)
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('kartu-identitas.template.preview', $pending) }}">Lihat pratinjau</a>
                                                    <form method="post" action="{{ route('kartu-identitas.template.delete', $pending) }}" onsubmit="return confirm('Hapus file pratinjau ini? Template aktif tidak akan berubah.');">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" type="submit">Hapus pratinjau</button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($jenis === 'pelajar')
                        <p class="small text-muted mt-3 mb-0">QR pada desain sisi belakang adalah gambar statis dari template, belum menjadi fitur presensi.</p>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
    <style>
        .template-thumb,.template-empty { width: min(100%, 12rem); aspect-ratio: 590 / 1004; margin-inline: auto; border-radius: .6rem; overflow: hidden; }
        .template-thumb img { width: 100%; height: 100%; object-fit: contain; }
        .template-empty { display: grid; place-items: center; align-content: center; gap: .5rem; padding: 1rem; background: #f1f5f2; color: #68756d; text-align: center; }
    </style>
@endsection
