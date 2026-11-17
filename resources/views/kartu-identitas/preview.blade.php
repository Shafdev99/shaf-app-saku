@extends('layout.app')
@section('title', 'Pratinjau Kartu Identitas')
@section('content')

    <div class="content content-sm">
        <div class="container">
            <div class="breadcrumb screen-only">Pratinjau Kartu Identitas</div>
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4 mb-3 print-controls">
                <div>
                    <h4 class="fw-bold mb-1">Pratinjau kartu {{ $jenis }}</h4>
                    <p class="text-muted mb-0">
                        {{ $people->count() }} kartu dipilih · urutan cetak setiap pemilik: depan lalu belakang.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('kartu-identitas', ['jenis' => $jenis]) }}">
                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali
                    </a>
                    <button class="btn btn-primary" id="print-cards-button" type="button" onclick="window.print()" disabled>
                        <i class="bi bi-printer me-1"></i>
                        Menyiapkan foto...
                    </button>
                </div>
            </div>

            @if ($jenis === 'guru' && !isset($templates['guru.belakang']))
                <div class="alert alert-info print-warning print-controls">
                    <i class="bi bi-info-circle me-1"></i>Template
                    belakang guru belum diunggah. Pratinjau dan cetak hanya menampilkan sisi depan. Untuk duplex, unggah
                    template belakang terlebih dahulu; pengaturan cetak satu sisi dipilih pada dialog printer.
                </div>
            @endif
            @if ($jenis === 'pelajar')
                <div class="alert alert-secondary small print-warning print-controls">
                    QR kartu pelajar membuka ringkasan identitas saat dipindai dengan kamera biasa. Pemindai di aplikasi
                    mengenali ID siswa dari QR tersebut untuk proses presensi.
                </div>
            @endif
            <p class="small text-muted print-controls">
                Pratinjau menggunakan lembar A4 yang sama dengan browser print: tiga kolom, tiga baris, dan sembilan
                kartu per lembar. Pilih kertas A4, skala 100%, margin tanpa batas, dan aktifkan grafik latar pada dialog cetak.
            </p>
            @if ($jenis === 'pelajar')
                <div class="d-flex flex-wrap align-items-center gap-3 mb-2 print-controls">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="remove-photo-background" checked>
                        <label class="form-check-label" for="remove-photo-background">Hapus latar belakang foto</label>
                    </div>
                    <button class="btn btn-outline-primary" id="prepare-student-photos-button" type="button">
                        <i class="bi bi-person-bounding-box me-1"></i> Siapkan foto
                    </button>
                </div>
            @endif
            <div id="student-photo-processing-status" class="alert alert-info small print-controls" role="status">
                Pilih opsi foto, lalu tekan “Siapkan foto”.
            </div>

            {{-- Print area --}}
            <div class="print-area">
                {{-- Print Sheets --}}
                @foreach ($printSheets as $page)
                    <div class="print-sheet">
                        @foreach ($page['ids'] as $id)
                            @include('kartu-identitas._face', [
                                'person' => $peopleById->get($id),
                                'jenis' => $jenis,
                                'sisi' => $page['sisi'],
                                'templates' => $templates,
                            ])
                        @endforeach
                    </div>
                @endforeach
                {{-- End Print Sheets --}}

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        (() => {
            const images = [...document.querySelectorAll('.print-sheet img.student-photo')];
            const button = document.getElementById('print-cards-button');
            const status = document.getElementById('student-photo-processing-status');
            const prepareButton = document.getElementById('prepare-student-photos-button');
            const removeBackgroundToggle = document.getElementById('remove-photo-background');

            const finish = (message, warning = false) => {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-printer me-1"></i> Cetak';
                if (prepareButton) prepareButton.disabled = false;
                if (removeBackgroundToggle) removeBackgroundToggle.disabled = false;
                status.className = `alert ${warning ? 'alert-warning' : 'alert-success'} small print-controls`;
                status.textContent = message;
            };

            if (images.length === 0) {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-printer me-1"></i> Cetak';
                prepareButton?.closest('.d-flex')?.classList.add('d-none');
                status.textContent = 'Tidak ada foto siswa yang perlu disiapkan.';
                return;
            }

            const imageGroups = new Map();
            for (const image of images) {
                const source = image.dataset.photoSrc;
                if (!source) continue;
                if (!imageGroups.has(source)) imageGroups.set(source, []);
                imageGroups.get(source).push(image);
            }

            const loadLibrary = () => new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation@0.1.1675465747/selfie_segmentation.js';
                script.crossOrigin = 'anonymous';
                script.onload = resolve;
                script.onerror = () => reject(new Error('Model penghapus latar foto tidak dapat dimuat.'));
                document.head.appendChild(script);
            });

            const loadImage = image => {
                if (image.complete && image.naturalWidth > 0) return Promise.resolve();
                return new Promise((resolve, reject) => {
                    image.addEventListener('load', resolve, { once: true });
                    image.addEventListener('error', () => reject(new Error('Foto tidak dapat dibuka.')), { once: true });
                });
            };

            const processPhotos = async removeBackground => {
                let segmenter;
                let resolveResults;
                let processed = 0;
                let failed = 0;
                let backgroundRemovalAvailable = removeBackground;

                button.disabled = true;
                prepareButton.disabled = true;
                removeBackgroundToggle.disabled = true;
                button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Menyiapkan foto...';

                if (removeBackground) {
                    try {
                        status.textContent = 'Mengunduh model penghapus latar foto ke browser. Foto tidak dikirim ke layanan pemrosesan.';
                        await loadLibrary();
                        segmenter = new SelfieSegmentation({
                            locateFile: file => `https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation@0.1.1675465747/${file}`
                        });
                        segmenter.setOptions({ modelSelection: 0 });
                        segmenter.onResults(results => resolveResults?.(results));
                        await segmenter.initialize();
                    } catch (error) {
                        backgroundRemovalAvailable = false;
                        segmenter?.close();
                        segmenter = null;
                        console.warn('Model penghapus latar foto tidak tersedia:', error);
                    }
                }

                try {
                    let position = 0;
                    for (const [source, group] of imageGroups) {
                        position++;
                        status.textContent = `Memuat dan memproses foto ${position} dari ${imageGroups.size}...`;
                        try {
                            const image = group[0];
                            // Defer the URL until this turn so the hosting server does not
                            // receive a burst of one PHP request per card at once.
                            image.src = source;
                            await loadImage(image);

                            let mask = null;
                            if (segmenter) {
                                const resultPromise = new Promise(resolve => {
                                    resolveResults = resolve;
                                });
                                await segmenter.send({ image });
                                const result = await resultPromise;
                                resolveResults = null;
                                mask = result.segmentationMask;
                            }

                            const scale = Math.min(1, 1200 / Math.max(image.naturalWidth, image.naturalHeight));
                            const canvas = document.createElement('canvas');
                            canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
                            canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
                            const context = canvas.getContext('2d');
                            context.drawImage(image, 0, 0, canvas.width, canvas.height);
                            if (mask) {
                                const sourcePixels = context.getImageData(0, 0, canvas.width, canvas.height);
                                const maskCanvas = document.createElement('canvas');
                                maskCanvas.width = canvas.width;
                                maskCanvas.height = canvas.height;
                                const maskContext = maskCanvas.getContext('2d');
                                maskContext.drawImage(mask, 0, 0, canvas.width, canvas.height);
                                const maskPixels = maskContext.getImageData(0, 0, canvas.width, canvas.height);

                                // Estimate the solid red backdrop from the photo corners so
                                // its color is removed from antialiased pixels around the subject.
                                const corners = [
                                    [0.03, 0.03], [0.97, 0.03], [0.03, 0.97], [0.97, 0.97]
                                ];
                                const background = [0, 0, 0];
                                let samples = 0;
                                for (const [xRatio, yRatio] of corners) {
                                    const centerX = Math.round(canvas.width * xRatio);
                                    const centerY = Math.round(canvas.height * yRatio);
                                    for (let y = centerY - 2; y <= centerY + 2; y++) {
                                        for (let x = centerX - 2; x <= centerX + 2; x++) {
                                            const offset = (Math.max(0, Math.min(canvas.height - 1, y)) * canvas.width
                                                + Math.max(0, Math.min(canvas.width - 1, x))) * 4;
                                            background[0] += sourcePixels.data[offset];
                                            background[1] += sourcePixels.data[offset + 1];
                                            background[2] += sourcePixels.data[offset + 2];
                                            samples++;
                                        }
                                    }
                                }
                                for (let channel = 0; channel < 3; channel++) background[channel] /= samples;
                                const redBackdrop = background[0] > 120
                                    && background[0] > background[1] * 1.35
                                    && background[0] > background[2] * 1.35;

                                for (let offset = 0; offset < maskPixels.data.length; offset += 4) {
                                    const confidence = maskPixels.data[offset + 3] / 255;
                                    const alpha = confidence <= 0.3
                                        ? 0
                                        : Math.min(1, (confidence - 0.3) / 0.7);

                                    if (redBackdrop && confidence > 0.3 && confidence < 0.98) {
                                        for (let channel = 0; channel < 3; channel++) {
                                            const foreground = (sourcePixels.data[offset + channel]
                                                - background[channel] * (1 - confidence)) / confidence;
                                            sourcePixels.data[offset + channel] = Math.max(0, Math.min(255, foreground));
                                        }
                                    }

                                    maskPixels.data[offset + 3] = Math.round(alpha * 255);
                                }

                                context.putImageData(sourcePixels, 0, 0);
                                maskContext.putImageData(maskPixels, 0, 0);
                                context.globalCompositeOperation = 'destination-in';
                                context.drawImage(maskCanvas, 0, 0);
                                context.globalCompositeOperation = 'source-over';
                                maskCanvas.width = maskCanvas.height = 0;
                            }

                            let processedPhoto = canvas.toDataURL('image/webp', 0.92);
                            if (!processedPhoto.startsWith('data:image/webp')) processedPhoto = canvas.toDataURL('image/png');
                            for (const groupedImage of group) groupedImage.src = processedPhoto;
                            canvas.width = canvas.height = 0;
                            processed++;
                        } catch (error) {
                            resolveResults = null;
                            failed++;
                            console.warn('Foto siswa tidak berhasil dimuat atau diproses:', source, error);
                        }

                        await new Promise(resolve => window.setTimeout(resolve, 0));
                    }
                } finally {
                    segmenter?.close();
                    if (!removeBackground) {
                        finish(`${processed} foto dimuat tanpa penghapusan latar; ${failed} foto gagal dimuat.`, failed > 0);
                    } else if (failed > 0 || !backgroundRemovalAvailable) {
                        const removalStatus = backgroundRemovalAvailable ? 'latar foto dihapus' : 'latar tidak dapat dihapus';
                        finish(`${processed} foto berhasil dimuat (${removalStatus}); ${failed} foto gagal dimuat.`, true);
                    } else {
                        finish(`${processed} foto siap dicetak dengan latar transparan.`);
                    }
                }
            };

            prepareButton?.addEventListener('click', () => processPhotos(removeBackgroundToggle.checked));
        })();
    </script>
@endpush
