<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>cetak kartu</title>
</head>

<body>

    @php
        $activeTemplate = $templates[$jenis . '.' . $sisi] ?? null;
        $identity = $person->kartu_identitas ?? \App\Services\CardIdentityFields::map($jenis, $person);
        $name = $identity['name'];
        $idNumber = $identity['id'];
        $idCard = $person->kartu_id ?? null;
        $photoUrl = $person->kartu_foto_url ?? null;
        $nameFont = max(3.4, min(4.4, 58 / max(1, mb_strlen($name ?? ''))));
        $studentNameFont = max(2.6, min(4.4, 100 / max(1, mb_strlen($name ?? ''))));
        $idText = $idNumber ?: 'Belum tersedia';
        $birthDate = 'Belum tersedia';
        if (!empty($person->tanggal_lahir)) {
            try {
                $birthDate = \Illuminate\Support\Carbon::parse($person->tanggal_lahir)->format('d/m/Y');
            } catch (\Throwable $exception) {
                $birthDate = $person->tanggal_lahir;
            }
        }
    @endphp
    <div class="print-sheet">
        <article class="id-card id-card-{{ $jenis }} id-card-{{ $jenis }}-{{ $sisi }}"
            aria-label="{{ ucfirst($sisi) }} kartu {{ $jenis }} untuk {{ $name }}">
            @if ($activeTemplate)
                <img class="id-card-template" src="{{ route('kartu-identitas.template.image', $activeTemplate) }}"
                    alt="">

                @if ($jenis === 'pelajar' && $sisi === 'depan')
                    @if ($photoUrl)
                        <img class="id-card-photo student-photo" src="{{ $photoUrl }}"
                            alt="Foto {{ $name }}">
                    @else
                        <div class="id-card-photo-placeholder student-photo">Foto belum tersedia</div>
                    @endif
                    <div class="student-nis-line">
                        ID Card : {{ $idCard ?: '0000000000' }}
                    </div>
                    <div class="student-identity-band">
                        <div class="id-card-name student-front-name" style="font-size: {{ $studentNameFont }}mm">
                            {{ $name ?: 'Nama belum tersedia' }}
                        </div>
                        <div class="student-class-line fw-bold">
                            {{ $person->kartu_jurusan ?: 'Jurusan belum tersedia' }}
                        </div>
                    </div>
                @elseif ($jenis === 'pelajar' && $sisi === 'belakang')
                    <div class="student-back-details">
                        <div class="student-back-name" style="font-size: {{ $studentNameFont }}mm">
                            <b>{{ $name ?: 'Nama belum tersedia' }}</b>
                        </div>
                        <div class="student-back-id">NISN: {{ $identity['secondary_id'] ?: 'Belum tersedia' }}</div>
                        <div class="student-back-birth">
                            {{ $person->tempat_lahir ?: 'Tempat lahir belum tersedia' }}, {{ $birthDate }}
                        </div>
                        <div class="student-back-address">
                            Alamat: {{ $person->alamat ?: 'Belum tersedia' }}
                        </div>
                    </div>
                    @if (!empty($person->kartu_qr))
                        <img class="student-qr" src="data:image/svg+xml;base64,{{ $person->kartu_qr }}"
                            alt="QR identitas siswa">
                    @endif
                @elseif ($jenis === 'guru' && $sisi === 'depan')
                    <div class="teacher-name-cover">
                        <div class="teacher-name" style="font-size: {{ $nameFont }}mm">
                            {{ $name ?: 'Nama belum tersedia' }}
                        </div>
                    </div>
                    <div class="teacher-role-cover">{{ $identity['detail'] }}</div>
                    @if ($photoUrl)
                        <img class="id-card-photo teacher-photo" src="{{ $photoUrl }}"
                            alt="Foto {{ $name }}">
                    @else
                        <div class="id-card-photo-placeholder teacher-photo">Foto belum tersedia</div>
                    @endif
                    <div class="teacher-id-band">NIP: {{ $idText }}</div>
                @elseif ($jenis === 'guru' && $sisi === 'belakang')
                    <div class="teacher-back-identity">
                        <strong>{{ $name }}</strong>
                        <span>NIP: {{ $idText }}</span>
                    </div>
                @endif
            @else
                <div class="id-card-no-template">Template sisi {{ $sisi }} belum tersedia</div>
            @endif
        </article>
    </div>

</body>

</html>
