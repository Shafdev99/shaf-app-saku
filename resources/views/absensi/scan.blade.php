@extends('layout.auth')
@section('title', 'Display Absensi RFID')
@section('content')
    <style>
        .attendance-display {
            --ink: #102a43;
            --muted: #627d98;
            --blue: #1769aa;
            --teal: #0b8f87;
            --line: #d9e2ec;
            min-height: 100dvh;
            margin: -1.5rem -0.75rem 0;
            padding: clamp(1.25rem, 3vw, 3rem);
            color: var(--ink);
            background: radial-gradient(circle at 85% 0%, #d8f3f0 0, transparent 31%), linear-gradient(135deg, #f7fbff 0%, #eef5f7 52%, #f9fcfb 100%);
        }

        .display-header {
            max-width: 1440px;
            margin: 0 auto clamp(2rem, 5vh, 4rem);
        }

        .display-brand {
            display: flex;
            align-items: center;
            gap: .8rem;
        }

        .display-brand img {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }

        .display-brand strong {
            display: block;
            font-size: clamp(1.1rem, 2vw, 1.6rem);
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .display-brand small {
            color: var(--muted);
            font-size: .85rem;
        }

        .display-clock {
            color: var(--blue);
            font-size: clamp(2rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: .03em;
        }

        .display-date {
            color: var(--muted);
            font-size: clamp(.8rem, 1.5vw, 1rem);
            margin-top: .45rem;
        }

        .display-grid {
            max-width: 1440px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(280px, .6fr);
            gap: clamp(1rem, 2vw, 2rem);
        }

        .display-panel {
            background: rgba(255, 255, 255, .86);
            border: 1px solid rgba(217, 226, 236, .9);
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(16, 42, 67, .08);
        }

        .scan-stage {
            min-height: 490px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(2rem, 6vw, 5rem);
            text-align: center;
        }

        .rfid-orbit {
            width: clamp(100px, 12vw, 150px);
            aspect-ratio: 1;
            display: grid;
            place-items: center;
            margin-bottom: 1.5rem;
            border: 2px solid #9edbd6;
            border-radius: 50%;
            color: var(--teal);
            font-size: clamp(3rem, 6vw, 5rem);
            background: #effbf9;
            box-shadow: 0 0 0 14px rgba(11, 143, 135, .06), 0 0 0 28px rgba(11, 143, 135, .035);
            animation: pulse 2.6s ease-in-out infinite;
        }

        .scan-stage h1 {
            margin-bottom: .6rem;
            font-size: clamp(1.65rem, 3.5vw, 3rem);
            font-weight: 800;
        }

        .scan-stage>#scan-waiting>p {
            max-width: 520px;
            margin-bottom: 1.8rem;
            color: var(--muted);
            font-size: clamp(.95rem, 1.5vw, 0.85rem);
        }

        .scan-status {
            width: min(100%, 620px);
            margin: 0;
            padding: 1rem 1.25rem;
            border: 0;
            border-radius: 12px;
            font-size: clamp(.95rem, 1.6vw, 1.2rem);
            font-weight: 700;
            transition: background .2s, color .2s;
        }

        .scan-status.waiting {
            color: #1769aa;
            background: #e8f1fb;
        }

        .scan-status.reading {
            color: #8a5a00;
            background: #fff4d6;
        }

        .scan-status.success {
            color: #087f5b;
            background: #dff7ee;
        }

        .scan-status.error {
            color: #b42318;
            background: #ffebe9;
        }

        .result-panel {
            width: min(100%, 620px);
            margin-top: 1.25rem;
            padding: 1.25rem;
            border: 1px solid var(--line);
            border-radius: 14px;
            text-align: left;
            animation: reveal .35s ease both;
        }

        .result-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 1.05rem;
        }

        .result-name {
            margin-bottom: 1rem;
            font-size: clamp(1.5rem, 3vw, 2.3rem);
            font-weight: 800;
        }

        .result-times {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        .result-time {
            padding: .75rem;
            background: #f5f8fa;
            border-radius: 10px;
        }

        .result-time small {
            display: block;
            margin-bottom: .25rem;
            color: var(--muted);
        }

        .result-time strong {
            font-size: 1.35rem;
        }

        .side-panel {
            padding: clamp(1.25rem, 2.5vw, 2rem);
        }

        .side-panel h2 {
            margin-bottom: 1.5rem;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .rule-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .85rem 0;
            border-bottom: 1px solid var(--line);
        }

        .rule-row span {
            color: var(--muted);
        }

        .rule-row strong {
            color: var(--ink);
        }

        .display-footer {
            max-width: 1440px;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin: 1.5rem auto 0;
            color: var(--muted);
            font-size: .8rem;
        }

        .ready-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            margin-right: .4rem;
            border-radius: 50%;
            background: #12b886;
        }

        .display-actions a {
            color: var(--blue);
            text-decoration: none;
        }

        .display-actions a:hover {
            text-decoration: underline;
        }

        body:has(.attendance-display) {
            overflow-x: hidden;
        }

        .container-login:has(.attendance-display) {
            max-width: none;
            padding: 0;
        }

        .container-login:has(.attendance-display) .logo-page,
        .container-login:has(.attendance-display) .footer-second {
            display: none;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.04);
            }
        }

        @keyframes reveal {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 900px) {
            .display-grid {
                grid-template-columns: 1fr;
            }

            .scan-stage {
                min-height: 430px;
            }
        }

        @media (max-width: 560px) {
            .attendance-display {
                margin: -1rem -.75rem 0;
            }

            .display-header {
                margin-bottom: 2rem;
            }

            .display-clock {
                font-size: 2.35rem;
            }

            .display-footer {
                flex-direction: column;
            }
        }
    </style>

    <main class="attendance-display" aria-label="Display Absensi RFID">
        <header class="display-header d-flex justify-content-between align-items-start gap-4 flex-wrap">
            <div class="display-brand">
                <img src="{{ asset('img/logo/logo-saku.png') }}" alt="Logo sekolah">
                <div><strong>Display Absensi</strong><small>Presensi siswa dan tenaga pendidik</small></div>
            </div>
            <div class="text-start text-lg-end">
                <div id="clock" class="display-clock">{{ now()->format('H:i:s') }}</div>
                <div id="date-label" class="display-date">{{ now()->translatedFormat('l, d F Y') }}</div>
            </div>
        </header>

        <div class="display-grid">
            <section class="display-panel scan-stage">
                <div id="scan-waiting" class="waiting-panel mx-auto" aria-live="polite">
                    <div class="rfid-orbit mx-auto" aria-hidden="true">
                        <i class="bi bi-rss"></i>
                    </div>
                    <h1>Silakan Scan Kartu</h1>
                    <p>Tempelkan kartu RFID pada reader. Hasil presensi akan tampil di layar ini.</p>
                </div>
                <div id="rfid-status" class="scan-status waiting" role="status" aria-live="polite">
                    <i class="bi bi-broadcast-pin me-2"></i>
                    Reader siap digunakan
                </div>
                <div id="presensi-feedback" class="result-panel d-none" aria-live="polite">
                    <div class="result-title">
                        <span id="feedback-title" class="fw-bold">Presensi berhasil</span>
                        <span id="feedback-status" class="badge"></span>
                    </div>
                    <div class="feedback-foto-profil text-center mb-3">
                        <img id="feedback-foto" alt="Foto Profil" class="rounded-circle img-user"
                            style="width: 120px !important; height: 120px !important; object-fit: cover;">
                    </div>
                    <div class="result-detail">

                        <div id="feedback-nama" class="result-name"></div>
                        <div class="result-times">
                            <div class="result-time"><small>Jam masuk</small><strong id="feedback-jam-masuk">-</strong>
                            </div>
                            <div class="result-time"><small>Jam pulang</small><strong id="feedback-jam-pulang">-</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="display-panel side-panel">
                <h2><i class="bi bi-clock-history me-2"></i>Aturan Absensi</h2>
                <div class="rule-row"><span>Jam masuk</span><strong>{{ substr($pengaturan->jam_masuk, 0, 5) }}</strong>
                </div>
                <div class="rule-row"><span>Batas
                        hadir</span><strong>{{ \Carbon\Carbon::parse($pengaturan->jam_masuk)->addMinutes($pengaturan->toleransi_terlambat)->format('H:i') }}</strong>
                </div>
                <div class="rule-row"><span>Jam pulang</span><strong>{{ substr($pengaturan->jam_pulang, 0, 5) }}</strong>
                </div>
                <div class="rule-row"><span>Batas
                        pulang</span><strong>{{ substr($pengaturan->batas_jam_pulang, 0, 5) }}</strong></div>
                <h2 class="mt-4 mb-3"><i class="bi bi-list-check me-2"></i>Scan Terakhir</h2>
                <div id="scan-history" class="text-muted small">Belum ada scan pada sesi ini.</div>
            </aside>
        </div>

        <footer class="display-footer"><span><i class="ready-dot"></i>Reader aktif dan menunggu kartu</span>
            <span class="display-actions">
                <a href="{{ url('/') }}">Beranda</a>
            </span>
        </footer>
        <form id="rfid-scan-form" class="d-none">@csrf<input type="text" id="card_id" name="card_id"
                autocomplete="off"></form>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('rfid-scan-form'),
                input = document.getElementById('card_id');
            const status = document.getElementById('rfid-status'),
                feedback = document.getElementById('presensi-feedback');
            const history = document.getElementById('scan-history');
            const waiting = document.getElementById('scan-waiting');
            const maxInterval = 80;
            let buffer = '',
                lastKeyTime = 0,
                isSubmitting = false,
                historyItems = [];

            function setStatus(message, type, icon) {
                status.className = 'scan-status ' + type;
                status.innerHTML = '<i class="bi ' + icon + ' me-2"></i>' + message;
            }

            function showFeedback(data) {
                const assetPath = `{{ asset('storage') }}/`;
                document.getElementById('feedback-foto').src = data.foto ? assetPath + data.foto :
                    `{{ asset('img/logo/profile.jpg') }}`;
                document.getElementById('feedback-nama').textContent = data.nama || '-';
                document.getElementById('feedback-jam-masuk').textContent = data.jam_masuk || data.jam || '-';
                document.getElementById('feedback-jam-pulang').textContent = data.jam_pulang || '-';
                document.getElementById('feedback-title').textContent = data.jam_pulang ? 'Presensi pulang' :
                    'Presensi masuk';
                const badge = document.getElementById('feedback-status');
                badge.textContent = data.status || '-';
                badge.className = 'badge ' + (data.status === 'HADIR' ? 'bg-success' : 'bg-warning text-dark');
                feedback.classList.remove('d-none');
            }

            function addHistory(data) {
                historyItems.unshift(data);
                historyItems = historyItems.slice(0, 4);
                history.innerHTML = historyItems.map(item =>
                    '<div class="d-flex justify-content-between border-bottom py-2"><span>' + item.nama +
                    '</span><strong>' + item.waktu + '</strong></div>').join('');
            }

            function submitScan(cardId) {
                isSubmitting = true;
                input.value = cardId;
                waiting.classList.add('d-none');
                setStatus('Memproses kartu...', 'reading', 'bi-hourglass-split');
                fetch('{{ route('absensi.scan') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    })
                    .then(async response => {
                        const result = await response.json();
                        console.log('Response:', result);
                        if (!response.ok) {

                            let message = result.message || 'Presensi ditolak.';
                            if (result.jam_pulang) message += ' Mulai pukul ' + result.jam_pulang + '.';
                            if (result.batas_jam_pulang) message += ' Batas pukul ' + result
                                .batas_jam_pulang + '.';
                            throw new Error(message);
                        }
                        showFeedback(result.data);
                        setStatus(result.message, 'success', 'bi-check-circle-fill');
                        addHistory({
                            nama: result.data.nama,
                            waktu: result.data.jam_pulang || result.data.jam_masuk || result.data
                                .jam
                        });
                    })
                    .catch(error => {
                        console.log(error);

                        feedback.classList.add('d-none');
                        waiting.classList.remove('d-none');
                        setStatus(error.message, 'error', 'bi-x-circle-fill');
                        setTimeout(() => {
                            setStatus('Reader siap digunakan', 'waiting', 'bi-broadcast-pin');
                        }, 3000);

                    })
                    .finally(() => {
                        isSubmitting = false;
                        buffer = '';
                        setTimeout(() => {
                            setStatus('Reader siap digunakan', 'waiting', 'bi-broadcast-pin');
                            waiting.classList.remove('d-none');
                            feedback.classList.add('d-none');
                        }, 3000);
                        input.value = '';
                    });
            }

            document.addEventListener('keydown', function(event) {
                const currentTime = Date.now();
                if (currentTime - lastKeyTime > maxInterval) buffer = '';
                lastKeyTime = currentTime;
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (buffer.length >= 4 && !isSubmitting) submitScan(buffer.trim());
                    buffer = '';
                    return;
                }
                if (event.key.length === 1) {
                    event.preventDefault();
                    buffer += event.key;
                    if (!isSubmitting) setStatus('Kartu terbaca, menunggu...', 'reading',
                        'bi-credit-card-2-front');
                }
            });

            function updateClock() {
                const now = new Date();
                document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID', {
                    hour12: false
                });
            }
            setInterval(updateClock, 1000);
            updateClock();
        });
    </script>
@endpush
