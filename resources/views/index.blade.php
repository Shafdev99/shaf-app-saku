@extends('layout.auth')
@section('title', 'Saku')
@section('content')
    <style>

    </style>

    <main class="portal-page" aria-label="Portal utama Saku">
        <div class="portal-inner">
            <nav class="portal-nav">
                <a class="portal-brand" href="{{ url('/') }}">
                    <img src="{{ asset('img/logo/logo-saku.png') }}" alt="Logo Saku">
                    <span><strong>Saku</strong>Portal pendidikan digital</span>
                </a>
                <a class="portal-nav-link" href="#layanan">Jelajahi layanan <i class="bi bi-arrow-down-right ms-1"></i></a>
            </nav>

            <section class="portal-hero">
                <div>
                    <div class="portal-kicker">Satu ruang untuk sekolah</div>
                    <h1>Kerja sekolah, <em>lebih mengalir.</em></h1>
                    <p class="portal-lead">Akses cepat ke layanan administrasi, presensi RFID, dan pendaftaran siswa baru
                        dalam satu portal yang rapi.</p>
                    <div class="portal-actions">
                        <a class="portal-btn portal-btn-primary" href="{{ route('login') }}"><i
                                class="bi bi-box-arrow-in-right"></i>Masuk ke aplikasi</a>
                        <a class="portal-btn portal-btn-secondary" href="{{ route('absensi.scan.view') }}"><i
                                class="bi bi-credit-card-2-front"></i>Display absensi</a>
                    </div>
                </div>
                <div class="portal-visual" aria-label="Preview display presensi">
                    <div class="portal-board">
                        <div class="portal-board-top"><span>Saku / 01</span><span class="portal-live">RFID
                                online</span></div>
                        <div class="portal-board-main">
                            <div class="portal-board-label">Presensi hari ini</div>
                            <h2>Selamat datang di ruang kerja yang lebih tertata.</h2>
                            <div class="portal-time-row">
                                <div id="portal-clock" class="portal-time">
                                    {{ now()->format('H:i') }}<small>{{ now()->translatedFormat('l, d F Y') }}</small></div>
                                <span class="portal-chip">Asia / Jakarta</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="layanan" class="portal-services" aria-label="Layanan utama">
                <article class="portal-service">
                    <div class="portal-service-icon"><i class="bi bi-shield-lock"></i></div>
                    <h3>Portal Admin</h3>
                    <p>Kelola data sekolah dan administrasi melalui ruang kerja yang aman.</p><a
                        href="{{ route('login') }}">Buka portal <i class="bi bi-arrow-up-right ms-1"></i></a>
                </article>
                <article class="portal-service">
                    <div class="portal-service-icon"><i class="bi bi-broadcast-pin"></i></div>
                    <h3>Absensi RFID</h3>
                    <p>Hasil scan guru tampil jelas dan real-time di depan kantor guru.</p><a
                        href="{{ route('absensi.scan.view') }}">Lihat display <i class="bi bi-arrow-up-right ms-1"></i></a>
                </article>
                <article class="portal-service">
                    <div class="portal-service-icon"><i class="bi bi-mortarboard"></i></div>
                    <h3>SPMB</h3>
                    <p>Temukan informasi dan mulai proses pendaftaran peserta didik baru.</p><a
                        href="{{ route('halaman.daftar') }}">Buka SPMB <i class="bi bi-arrow-up-right ms-1"></i></a>
                </article>
            </section>

            <footer class="portal-footer"><span>© {{ date('Y') }} Saku</span><span>Belajar. Mengelola.
                    Bertumbuh.</span></footer>
        </div>
    </main>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clock = document.getElementById('portal-clock');

            function updateClock() {
                const now = new Date();
                clock.firstChild.textContent = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
            }
            setInterval(updateClock, 1000);
            updateClock();
        });
    </script>
@endpush
