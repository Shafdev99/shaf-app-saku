@extends('layout.app')
@section('title', 'Pratinjau Template')
@section('content')
    <div class="content content-sm">
        <div class="container">
            <div class="breadcrumb">Pratinjau Template</div>
            <section class="card p-3 p-lg-4 card-content mt-4">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                    <div>
                        <span
                            class="badge {{ $template->aktif ? 'bg-success' : 'bg-warning text-dark' }}">{{ $template->aktif ? 'Aktif' : 'Menunggu aktivasi' }}</span>
                        <h4 class="fw-bold mt-2 mb-1">Template {{ $template->jenis }} · {{ $template->sisi }}</h4>
                        <p class="text-muted">Periksa orientasi, rasio, dan keterbacaan sebelum mengaktifkan.</p>
                    </div>
                    <a class="btn btn-outline-secondary align-self-start"
                        href="{{ route('kartu-identitas.templates') }}">Kembali</a>
                </div>
                <div class="template-preview-frame mb-4">
                    <img src="{{ route('kartu-identitas.template.image', $template) }}"
                        alt="Pratinjau template {{ $template->jenis }} {{ $template->sisi }}">
                </div>
                @if ($active && $active->id !== $template->id)
                    <p class="small text-muted">Template aktif saat ini tetap digunakan sampai template baru diaktifkan.</p>
                @endif
                @if (!$template->aktif)
                    <div class="d-flex flex-wrap gap-2">
                        <form method="post"
                            action="{{ route('kartu-identitas.template.aktifkan', ['templateId' => $template->id]) }}"
                            onsubmit="return confirm('Aktifkan template ini untuk sisi {{ $template->sisi }} {{ $template->jenis }}? Template aktif saat ini akan diganti.');">
                            @csrf
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-check2-circle me-1"></i>
                                Aktifkan template ini
                            </button>
                        </form>
                        <form method="post"
                            action="{{ route('kartu-identitas.template.delete', ['template' => $template->id]) }}"
                            onsubmit="return confirm('Hapus template pratinjau ini? Template aktif tetap digunakan.');">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger" type="submit">
                                <i class="bi bi-trash me-1"></i>
                                Hapus pratinjau
                            </button>
                        </form>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
