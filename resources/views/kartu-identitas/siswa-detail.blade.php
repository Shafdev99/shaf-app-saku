<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Pelajar</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #f1f5f9;
            color: #172033;
            font: 16px/1.5 system-ui, sans-serif;
        }

        main {
            width: min(100%, 620px);
            margin: 5vh auto;
            padding: 28px;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 12px 36px #0f172a14;
        }

        h1 {
            margin: 0 0 6px;
            font-size: 1.5rem;
        }

        .intro {
            margin: 0 0 22px;
            color: #64748b;
        }

        dl {
            display: grid;
            grid-template-columns: minmax(115px, 0.38fr) 1fr;
            gap: 12px 18px;
            margin: 0;
        }

        dt {
            color: #64748b;
        }

        dd {
            margin: 0;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .student-id {
            padding: 12px 14px;
            border-radius: 10px;
            background: #eff6ff;
            color: #1d4ed8;
            font-family: ui-monospace, monospace;
        }

        @media (max-width: 480px) {
            body {
                padding: 14px;
            }

            main {
                margin: 2vh auto;
                padding: 20px;
            }

            dl {
                grid-template-columns: 1fr;
                gap: 3px;
            }

            dd {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    @php
        $tanggalLahir = 'Belum tersedia';
        if ($siswa->tanggal_lahir) {
            try {
                $tanggalLahir = \Illuminate\Support\Carbon::parse($siswa->tanggal_lahir)->format('d/m/Y');
            } catch (\Throwable $exception) {
                $tanggalLahir = $siswa->tanggal_lahir;
            }
        }
        $namaKelas = collect([
            $kelasSiswa?->tingkat?->tingkat,
            $kelasSiswa?->jurusan?->nama_jurusan,
            $kelasSiswa?->nama_kelas,
        ])
            ->filter()
            ->implode(' ');
    @endphp

    <main>
        <h1>Data Pelajar</h1>
        <p class="intro">Informasi identitas yang tercantum pada kartu pelajar.</p>
        <dl>
            <div class="d-none">
                <dt>ID Siswa</dt>
                <dd class="student-id">{{ $siswa->id }}</dd>
            </div>
            <dt>Nama</dt>
            <dd>{{ $siswa->nama_lengkap ?: 'Belum tersedia' }}</dd>
            <dt>NIS</dt>
            <dd>{{ $siswa->nis ?: 'Belum tersedia' }}</dd>
            <dt>NISN</dt>
            <dd>{{ $siswa->nisn ?: 'Belum tersedia' }}</dd>
            <dt>Kelas</dt>
            <dd>{{ $namaKelas ?: 'Belum tersedia' }}</dd>
            <dt>Tempat, tanggal lahir</dt>
            <dd>{{ $siswa->tempat_lahir ?: 'Belum tersedia' }}, {{ $tanggalLahir }}</dd>
            <dt>Alamat</dt>
            <dd>{{ $siswa->alamat ?: 'Belum tersedia' }}</dd>
        </dl>
    </main>
</body>

</html>
