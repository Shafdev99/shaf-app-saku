# PRD — Pengembangan Absensi RFID Harian Siswa dan Tenaga Pendidik

**Status:** Spesifikasi implementasi untuk Codex  
**Bahasa:** Indonesia  
**Repositori acuan:** `shaf-app-saku`  
**Teknologi yang teridentifikasi:** Laravel 11, PHP 8.3+, Blade, Eloquent, PHPUnit, MySQL-compatible migrations; provider WhatsApp Fonnte melalui `WhatsappService`.

## 1. Ringkasan

Pengembangan ini memperluas aplikasi sekolah yang sudah ada untuk mendukung **absensi harian berbasis RFID bagi siswa dan tenaga pendidik**, tanpa merusak alur absensi guru yang sudah berjalan. Sistem mencatat scan masuk dan scan pulang, mengelola izin/sakit, menyediakan rekap, dan mengirim pemberitahuan WhatsApp kepada orang tua/wali ketika siswa belum melakukan scan masuk setelah tenggat yang dikonfigurasi admin.

Seluruh pengaturan waktu absensi ditempatkan di satu menu **Pengaturan Absensi**, tetapi aturan siswa dan guru disimpan serta diterapkan secara terpisah secara logis.

Dokumen ini mencakup kebutuhan dan kriteria penerimaan. `AGENT.md` menjadi instruksi kerja Codex. Keduanya harus dibaca bersama.

## 2. Hasil audit awal source archive

Temuan berikut berasal dari pemeriksaan source archive `shaf-app-saku.zip`, bukan dari menjalankan aplikasi terhadap database produksi. Codex tetap wajib memverifikasi ulang pada working tree dan environment implementasi.

- `composer.json` mensyaratkan PHP `^8.3` dan Laravel `^11.9`.
- Model `Guru` memiliki kolom `card_id`, `no_hp`, `status_akun`, serta informasi nama/foto. Menu tenaga pendidik memiliki route tautkan/reset kartu dan validasi `unique:guru,card_id`.
- Model `Siswa` menggunakan UUID dan tabel `siswa`. Migration yang terlihat memuat data siswa, tetapi tidak menunjukkan kolom `card_id` atau nomor WhatsApp orang tua khusus notifikasi.
- Model `Siswa` memiliki relasi `ayah`, `ibu`, dan `wali`; model `Ayah`, `Ibu`, `Walmur` serta service terkait menggunakan field seperti `telp_ayah`, `telp_ibu`, dan `telp_walmur`. Namun migration tabel-tabel tersebut tidak tampak pada daftar migration di archive. **Jangan menyimpulkan skema database aktual dari daftar migration archive saja.** Periksa database/schema yang tersedia dan seluruh migration/seed sebelum menentukan integrasi.
- `AbsensiController` memiliki konfigurasi jam masuk/pulang, toleransi keterlambatan, hari kerja/libur, scan RFID guru, izin/sakit, dan rekap. Implementasi aktif scan berorientasi pada `Guru`; ada implementasi lama yang dikomentari. Codex harus mengaudit keduanya dan tidak menghidupkan blok komentar tanpa alasan.
- Tabel `presensi` yang terlihat berorientasi pada guru (`guru_id`, tanggal, jam masuk/pulang, status, unique guru+tanggal). `ketidakhadiran` juga berorientasi pada `guru_id`. **Jangan menyimpan data siswa dengan mengisi kolom `guru_id`.**
- Pengaturan saat ini disimpan pada model `Absensi`/tabel `pengaturan_absensi` dan memiliki jam masuk, toleransi terlambat, jam pulang, batas jam pulang, serta hari kerja.
- `WhatsappService` mengirim pesan melalui Fonnte dan mengubah awalan nomor `0` menjadi `62`. Service saat ini tidak menyediakan pencatatan status notifikasi ketidakhadiran siswa yang tahan duplikasi. Audit kontrak respons provider dan konfigurasi melalui `config()`; jangan mengeluarkan token atau data sensitif.
- Route scan view `/absensi/scan` berada di luar route group `all`, sedangkan operasi absensi lain berada di dalam group tersebut. Periksa middleware/CSRF/otorisasi aktual sebelum menambah atau memindahkan route.
- `simplesoftwareio/simple-qrcode` sudah tercantum di `composer.json`, tetapi QR Code **tidak termasuk cakupan fase ini**.
- Archive memuat `.env` dan `.git`. Jangan membaca, menyalin, mengeluarkan, atau menyertakan nilai rahasia dari `.env`, git history, database, log, atau output.

## 3. Tujuan dan indikator keberhasilan

1. Siswa dapat ditautkan ke kartu RFID melalui alur yang jelas dan dapat dilewati saat proses penambahan siswa.
2. Satu kartu RFID tidak dapat tertaut ke lebih dari satu siswa/guru.
3. Scan masuk dan pulang siswa tersimpan terpisah dari absensi guru.
4. Pengaturan admin mengendalikan tenggat scan masuk siswa, waktu evaluasi/pengiriman notifikasi, dan jeda perubahan status `Belum Hadir` menjadi `Alpa`.
5. Siswa yang tidak memiliki nomor WhatsApp orang tua/wali tetap dapat diproses absensinya; pengiriman dilewati dan kondisi itu terlihat oleh admin.
6. Pengiriman notifikasi tidak berulang akibat scheduler berjalan lebih dari sekali, retry, atau scan/request bersamaan.
7. Izin/sakit yang sah pada tanggal bersangkutan mengecualikan siswa dari notifikasi ketidakhadiran dan perubahan otomatis menjadi `Alpa` selama periode yang disetujui.
8. Rekap guru tetap konsisten dengan perilaku sebelum perubahan; rekap siswa dapat difilter dan dibedakan dengan jelas.

## 4. Ruang lingkup fase ini

### Termasuk

- Audit menu tenaga pendidik dan pola tautkan/reset kartu RFID yang sudah ada.
- Penautan kartu RFID siswa setelah tambah siswa, dengan opsi **Lewati** tanpa menghapus data siswa.
- Aksi **Tautkan kartu** pada daftar siswa dan pengelolaan kartu yang sudah tertaut.
- Field nomor WhatsApp orang tua/wali khusus notifikasi absensi pada form tambah/edit siswa. Field ini **opsional pada fase awal**, agar data lama dan siswa yang nomor kontaknya belum tersedia tetap dapat dikelola.
- Absensi harian siswa melalui RFID: scan masuk dan scan pulang.
- Pengaturan absensi pada satu menu yang sama, dengan konfigurasi siswa dan guru terpisah secara logis.
- Status siswa `Belum Hadir`, `HADIR`, `TERLAMBAT`, `IZIN`, `SAKIT`, dan `ALPA`, dengan transisi yang dijelaskan di dokumen ini.
- Pencatatan izin/sakit siswa dan guru pada sub-menu yang sama, dengan pilihan jenis entitas yang jelas dan validasi berbeda sesuai kebutuhan.
- Rekap absensi siswa dan guru pada menu rekap yang sama, dengan tab/pemilih jenis data yang jelas.
- Notifikasi WhatsApp kepada orang tua/wali ketika siswa belum scan masuk setelah tenggat, melalui service/provider WhatsApp yang sudah ada.
- Log status pengiriman, pencegahan duplikasi, koreksi manual admin, dan pengujian regresi.

### Tidak termasuk pada fase ini

- Absensi QR Code per mata pelajaran/jam pelajaran.
- Pengelolaan jadwal pelajaran/timetable. Fitur ini akan dikembangkan sebagai modul tersendiri berdasarkan aplikasi Timetables lama dan menjadi sumber jadwal untuk absensi per mapel di fase mendatang.
- Fitur Kartu Identitas (pelajar dan guru), pengelolaan template oleh admin, pratinjau, dan cetak satuan/massal ditambahkan sebagai deliverable fase ini. QR Code pada kartu pelajar disiapkan sebagai area/desain untuk fitur mendatang; QR tidak boleh dianggap sebagai fitur absensi aktif.
- Portal/aplikasi orang tua untuk melihat rekap siswa. Catat sebagai roadmap, bukan implementasi sekarang.
- Penggantian provider WhatsApp, redesign besar aplikasi, atau perubahan modul yang tidak diperlukan untuk kebutuhan ini.

## 5. Pengguna dan hak akses

- **Admin/petugas berwenang:** mengelola siswa, tautkan/reset RFID, nomor WhatsApp kontak orang tua/wali, pengaturan absensi, izin/sakit, koreksi status, serta melihat rekap dan status notifikasi.
- **Operator absensi:** menjalankan halaman scan sesuai pola otorisasi yang sudah ada. Jangan memperluas akses ke fungsi administrasi tanpa kebutuhan dan middleware yang jelas.
- **Guru/tenaga pendidik:** tetap menggunakan alur RFID harian yang sudah ada sesuai kebijakan aplikasi saat ini.

Codex harus mengikuti middleware/permission yang ditemukan. Endpoint POST/PUT/DELETE wajib memvalidasi dan mengotorisasi pada server. Jangan menganggap menyembunyikan tombol di Blade sebagai pengganti otorisasi.

## 6. Kebutuhan fungsional

### FR-1 — Audit dan pertahankan alur tenaga pendidik

Sebelum mengubah menu siswa:

1. Petakan route, controller, view, validasi, middleware, scan input, feedback sukses/gagal, reset kartu, dan pengecekan kartu duplikat.
2. Catat baseline perilaku scan masuk/pulang, toleransi terlambat, hari kerja/libur, izin/sakit, rekap, dan integrasi WhatsApp guru.
3. Pertahankan perilaku absensi guru kecuali perubahan yang diperlukan agar siswa didukung dengan aman.
4. Jangan menyalin blok kode yang dikomentari sebagai implementasi baru tanpa membandingkannya dengan kode aktif.

**Kriteria penerimaan:** alur guru tetap berjalan dan test regresi mencakup scan masuk, scan pulang, kartu tidak dikenal, izin/sakit, hari libur, dan duplikasi scan.

### FR-2 — Tambah siswa dan tautkan RFID

- Setelah siswa berhasil disimpan, arahkan pengguna ke halaman tautkan kartu untuk siswa tersebut.
- Halaman menampilkan identitas ringkas yang cukup untuk memastikan siswa benar.
- Input harus kompatibel dengan RFID reader dan perilaku input yang digunakan pada halaman tautkan kartu guru.
- Sediakan **Lewati**. Siswa tetap tersimpan dan pengguna kembali ke daftar siswa.
- Pada daftar siswa, tampilkan status kartu dan aksi **Tautkan kartu** jika belum tertaut. Jika sudah tertaut, sediakan aksi penggantian/reset sesuai pola guru dan minta konfirmasi sebelum melepas kartu.
- Penggantian/reset kartu tidak menghapus histori absensi.
- Validasi kartu di server, tangani kartu kosong/tidak dikenal/duplikat dengan pesan yang mudah dipahami.

**Integritas ID kartu:** kartu harus unik secara global di antara siswa dan guru. Unique index pada dua tabel terpisah saja tidak menjamin keunikan lintas tabel. Codex harus memilih pendekatan paling minim risiko yang cocok dengan struktur aplikasi—misalnya registry kartu bersama atau mekanisme integritas terpusat—dan membuktikan perlindungannya terhadap request bersamaan. Jangan hanya mengandalkan validasi browser.

### FR-3 — Nomor WhatsApp orang tua/wali

- Tambahkan field bernama jelas, misalnya **Nomor WhatsApp orang tua/wali untuk notifikasi absensi**, pada form tambah/edit siswa.
- Field nullable/opsional pada fase awal; siswa tanpa nomor tetap dapat disimpan, ditautkan kartu, dan melakukan absensi.
- Simpan sebagai string agar nol awal dan awalan `+` tidak hilang.
- Normalisasi dengan aturan yang sesuai provider. Audit `WhatsappService` dan uji nomor dengan awalan `08`, `62`, `+62`, spasi, serta karakter non-digit. Jangan mengubah nomor menjadi integer.
- Saat nomor kosong/tidak valid, jangan mencoba mengirim pesan. Tampilkan indikator yang bisa dilihat admin, seperti **Nomor belum tersedia — notifikasi dilewati**.
- Model siswa saat ini memiliki relasi ayah/ibu/wali dan sumber telepon yang mungkin berbeda. Untuk kebutuhan notifikasi, gunakan satu nomor tujuan yang dipilih secara eksplisit sebagai kontak notifikasi, jangan menimpa atau menyalin data `telp_ayah`/`telp_ibu`/`telp_walmur` secara diam-diam. Sebelum migration, periksa schema database aktual dan putuskan lokasi kolom yang paling konsisten; bila tidak ada sumber tunggal yang dapat dipakai, tambahkan kolom khusus notifikasi pada `siswa` melalui migration baru.

### FR-4 — Absensi RFID harian siswa

- Gunakan alur scan RFID yang konsisten dengan halaman scan guru, dengan feedback nama siswa, waktu scan, dan status.
- Catatan absensi siswa harus disimpan dalam struktur terpisah dari tabel `presensi` guru. Jangan membuat record siswa palsu pada `guru_id`.
- Minimal catatan siswa harian memuat ID siswa, tanggal, jam masuk nullable, jam pulang nullable, status, timestamps, dan informasi koreksi/admin jika pola aplikasi mendukungnya.
- Batasi satu catatan harian per siswa per tanggal sekolah. Gunakan unique constraint dan transaksi/locking yang sesuai agar scan bersamaan tidak membuat duplikasi.
- Scan pertama yang sah pada hari itu mencatat jam masuk. Scan berikutnya yang memenuhi aturan jam pulang mengisi jam pulang jika belum terisi. Scan berulang setelah jam pulang tercatat tidak membuat catatan baru dan memberi pesan “absensi pulang sudah tercatat”.
- Terapkan hari kerja dan hari libur berdasarkan mekanisme yang sudah ada, dengan evaluasi apakah konfigurasi kalender guru bisa digunakan bersama atau perlu data/aturan terpisah untuk siswa.
- Jam masuk setelah tenggat siswa tetap dicatat dengan waktu sebenarnya dan status `TERLAMBAT`.
- Jika siswa belum melakukan scan pulang, `jam_pulang` dibiarkan `NULL`. Admin memeriksa dan menangani kasus tersebut secara manual. **Tidak ada notifikasi WhatsApp untuk scan pulang yang belum tercatat pada fase ini.**
- Tetapkan perilaku scan sebelum waktu mulai dan setelah batas scan pulang berdasarkan aturan konfigurasi; jangan diam-diam mewarisi jam guru jika kebijakan siswa berbeda.

### FR-5 — Pengaturan Absensi satu menu

Pertahankan satu menu **Pengaturan Absensi**. Pisahkan field/section dan penyimpanan logis untuk guru dan siswa agar mengubah aturan siswa tidak mengubah perilaku guru.

Konfigurasi minimum siswa:

1. **Batas waktu scan masuk siswa** — waktu lokal sekolah setelahnya siswa tanpa scan masuk masuk ke proses `Belum Hadir`.
2. **Waktu evaluasi/pengiriman notifikasi WhatsApp** — konfigurasi admin untuk kapan sistem memeriksa siswa yang belum scan dan mengirim pesan. Secara default, rekomendasi desain adalah mengaitkan evaluasi awal dengan tenggat scan masuk, kecuali admin memilih waktu lain. Validasi harus mencegah pengiriman sebelum tenggat yang efektif.
3. **Jeda perubahan status menjadi Alpa** — bilangan durasi yang diatur admin, dihitung sejak batas waktu scan masuk terlewati. Contoh: tenggat 07.00 dan jeda 3 jam menghasilkan waktu evaluasi perubahan mulai 10.00.
4. Pengaturan hari kerja/libur menggunakan pola kalender aplikasi yang ada jika cocok.

Konfigurasi guru yang sudah ada—jam masuk, toleransi terlambat, jam pulang, batas jam pulang, hari kerja/libur—harus tetap tersedia dan tidak berubah ketika admin menyimpan pengaturan siswa.

**Validasi:** jam berformat valid; durasi berupa integer dalam rentang masuk akal; jam pulang sesudah jam masuk dan batas pulang tidak lebih awal dari jam pulang; nilai kosong/tidak valid ditolak dengan pesan pada field terkait. Hindari membuat migration destruktif atau mengubah migration lama.

### FR-6 — Status, transisi, dan koreksi admin

Gunakan label UI bahasa Indonesia yang konsisten. Nilai internal boleh mengikuti konvensi kode proyek, tetapi pemetaan harus tunggal.

| Status UI | Makna dan aturan |
|---|---|
| Belum Hadir | Tenggat scan masuk sudah terlewati, tetapi siswa belum scan dan masa jeda perubahan menjadi Alpa belum selesai. Ini status sementara, bukan status final. |
| Hadir | Siswa scan masuk dalam rentang waktu yang dianggap tepat waktu menurut pengaturan siswa. |
| Terlambat | Siswa scan setelah tenggat scan masuk. Waktu scan sebenarnya tetap disimpan. |
| Izin | Admin mencatat izin yang berlaku pada tanggal tersebut dan menyimpan keterangan/alasan. |
| Sakit | Admin mencatat sakit yang berlaku pada tanggal tersebut dan menyimpan keterangan serta status dokumen pendukung jika relevan. |
| Alpa | Siswa belum scan masuk dan tidak memiliki izin/sakit yang sah setelah jeda konfigurasi admin berakhir, atau admin menetapkan status ini melalui koreksi yang berwenang. |

Aturan transisi:

1. Sistem tidak boleh mengubah status menjadi `Alpa` sebelum tenggat ditambah jeda konfigurasi admin.
2. Evaluasi otomatis hanya mengubah siswa yang masih memenuhi kondisi: hari sekolah, belum ada scan masuk, dan tidak memiliki izin/sakit sah yang mencakup tanggal tersebut.
3. Jika siswa datang setelah notifikasi terkirim, sistem menyimpan waktu scan aktual dan status akhir `TERLAMBAT`. Riwayat notifikasi yang sudah terkirim tetap tersimpan dan tidak dapat dihapus oleh perubahan status.
4. Admin dapat mengoreksi status. Simpan siapa/kapan/mengapa perubahan dilakukan jika arsitektur audit log mendukung; jika perlu menambah audit trail, buat migration/fitur yang aman.
5. Job evaluasi harus idempotent dan aman dijalankan berkali-kali. Perubahan konfigurasi tidak boleh menyebabkan status historis berubah tanpa aturan backfill yang eksplisit. Konfigurasi berlaku ke depan; bila perlu mengevaluasi tanggal berjalan, lakukan dengan kondisi yang jelas dan terdokumentasi.

### FR-7 — Izin/sakit dan kebijakan durasi

- Sub-menu izin/sakit yang sama harus bisa mengelola guru maupun siswa melalui pilihan jenis entitas yang jelas.
- Data izin/sakit siswa dan guru harus dipisahkan secara logis; jangan menggunakan kolom `guru_id` untuk siswa.
- Untuk absensi harian fase ini, izin/sakit siswa berlaku berdasarkan rentang tanggal kalender sekolah, bukan per sesi mata pelajaran.
- Satu pengajuan harus memiliki jenis status, tanggal mulai/akhir atau representasi durasi yang konsisten, keterangan/alasan, penginput, dan status verifikasi bila relevan.
- Kebijakan sekolah: izin berlaku sampai 3 hari. Untuk izin sakit yang lebih dari 3 hari, admin perlu memeriksa surat dokter. Untuk izin keperluan/kegiatan/acara keluarga yang lebih dari 3 hari, admin perlu memeriksa adanya alasan kuat.
- Implementasikan bantuan administrasi, bukan penolakan otomatis yang menghapus/membatalkan catatan. Beri indikator **Perlu pemeriksaan dokumen/alasan** ketika durasi melebihi 3 hari. Untuk sakit, sediakan pencatatan status surat dokter (belum ada/diterima/diverifikasi) dan upload lampiran hanya jika pola upload aplikasi yang ada aman dan dapat digunakan kembali. Untuk izin keperluan keluarga/kegiatan, sediakan alasan wajib untuk kasus lebih dari 3 hari; jangan mewajibkan “surat dokter” pada jenis ini.
- Izin/sakit yang telah disetujui dan berlaku pada tanggal tersebut mengecualikan siswa dari notifikasi ketidakhadiran serta perubahan otomatis menjadi Alpa.
- Validasi rentang tanggal, duplikasi/overlap, dan konflik dengan absensi yang sudah tercatat. Jangan menghapus histori scan saat admin mencatat izin; tentukan aturan prioritas dan audit perubahan secara eksplisit.

### FR-8 — Rekap absensi siswa dan guru

- Pertahankan menu rekap yang ada dan tambahkan tab/pemilih jenis data **Guru** dan **Siswa**.
- Rekap guru mempertahankan filter dan format yang sudah berjalan.
- Rekap siswa minimum memuat nama siswa, kelas, tanggal, jam masuk, jam pulang, status, dan keterangan koreksi/izin jika relevan.
- Filter minimum: tanggal mulai/akhir dan status. Untuk siswa tambahkan kelas/nama siswa bila relasi aktual mendukungnya.
- Tampilkan status `Belum Hadir` secara jelas untuk status sementara. Jangan menggabungkan `Belum Hadir` dengan `Alpa` dalam agregasi statistik.
- Jika ekspor diimplementasikan, pertahankan filter aktif dan bedakan jenis data. Jangan membuat ekspor yang diam-diam mencampur baris guru dan siswa tanpa kolom jenis entitas.
- Gunakan pagination/penanganan data besar sesuai pola aplikasi; jangan melakukan query per baris (N+1) bila bisa dihindari.

### FR-9 — Notifikasi WhatsApp ketidakhadiran siswa

- Reuse `App\Services\WhatsappService` dan provider yang dikonfigurasi; jangan membuat integrasi kedua.
- Sistem harus mengevaluasi siswa melalui Laravel Scheduler/Queue/console command yang sesuai dengan arsitektur proyek. Jangan menggantungkan pengiriman pada pengguna membuka halaman.
- Pada waktu evaluasi, cari siswa aktif yang wajib hadir pada tanggal tersebut dan belum melakukan scan masuk serta tidak memiliki izin/sakit sah.
- Notifikasi memberitahu bahwa siswa belum tercatat hadir; jangan menyebut status final `Alpa` sebelum jeda konfigurasi berakhir.
- Jika nomor WhatsApp kosong/tidak valid, lewati pengiriman dan catat status `skipped` beserta alasan aman yang dapat dilihat admin.
- Log setiap notifikasi sekurang-kurangnya mengaitkan siswa, tanggal, jenis notifikasi, status (`pending`/`sent`/`failed`/`skipped`), waktu percobaan/pengiriman, jumlah percobaan, dan error yang sudah disanitasi.
- Gunakan unique key/lock/transaction untuk mencegah pesan ganda per siswa+tanggal+jenis notifikasi. Retry provider gagal harus terkontrol dan tidak mengirim duplikat jika provider sudah menerima pesan.
- Catat hasil pengiriman yang benar-benar dilaporkan provider, bukan menganggap request HTTP berhasil selalu berarti pesan terkirim ke penerima.
- Jika siswa datang terlambat setelah notifikasi terkirim, jangan menghapus log atau mencoba menarik pesan yang sudah terkirim.
- Jangan kirim notifikasi untuk siswa dengan izin/sakit yang sah, siswa yang sudah hadir sebelum waktu evaluasi, hari libur, atau hari yang bukan hari sekolah.
- Jangan mengirim notifikasi ketidakhadiran karena siswa tidak scan pulang pada fase ini.
- Admin dapat melihat hasil notifikasi dan status nomor kontak tanpa melihat token provider atau data sensitif di log.

### FR-10 — Integritas data dan migrasi

- Tambahkan migration baru; jangan mengedit migration lama yang mungkin sudah dijalankan.
- Gunakan UUID dan konvensi relasi yang sesuai model `Siswa` yang ada.
- Tabel absensi siswa harian minimal memiliki unique siswa+tanggal dan index untuk tanggal/status.
- Tambahkan field `card_id` siswa dan field nomor WhatsApp kontak notifikasi melalui migration baru hanya setelah memeriksa schema aktual dan rencana integritas kartu lintas entitas.
- Tambahkan tabel log notifikasi dengan unique constraint/idempotency key yang sesuai.
- Konfigurasi siswa dapat disimpan di tabel pengaturan absensi yang sudah ada dengan field baru jika tidak merusak kompatibilitas, atau tabel/struktur konfigurasi terpisah jika diperlukan. Pertahankan satu menu UI.
- Buat foreign key/index bila kompatibel dengan tipe UUID dan skema database aktual. Periksa engine/driver dan migration status sebelum memilih teknik schema change.
- Jangan menghapus atau mengubah histori absensi guru.

## 7. Ketentuan operasional

- Semua waktu memakai timezone aplikasi yang dikonfigurasi; verifikasi timezone deployment dan konsistensi `now()`/Carbon.
- Scheduler perlu dijalankan rutin oleh cron/hosting. Dokumentasikan konfigurasi scheduler dan queue worker bila digunakan. Jangan mengklaim notifikasi otomatis berjalan sebelum scheduler benar-benar dikonfigurasi.
- Kegagalan WhatsApp tidak boleh membatalkan pencatatan absensi.
- Jika proses otomatis gagal, log error yang aman dan sediakan cara admin mengetahui status; jangan menyembunyikan kegagalan.
- Status `Belum Hadir`/`Alpa` harus dihitung untuk tanggal sekolah yang benar, termasuk ketika scheduler sempat mati dan kembali berjalan. Hindari backfill yang mengirim banyak pesan lama tanpa kebijakan eksplisit.

## 8. Keamanan dan privasi

- Validasi server-side untuk semua input dan route sensitif.
- Jangan log ID kartu mentah, nomor telepon penuh, token Fonnte, isi `.env`, token session, atau data pribadi yang tidak diperlukan.
- Gunakan escape output Blade dan perlindungan CSRF sesuai framework.
- Jangan mengirim pesan kepada nomor yang tidak dinormalisasi/valid.
- Lindungi koreksi status dan pengaturan dengan otorisasi admin.
- Audit log mencatat perubahan status tanpa menyimpan rahasia.
- Jangan mengubah atau mengeluarkan file `.env`/`.git` dari source archive.

## 9. Kriteria penerimaan

### Data siswa dan RFID

- [ ] Tambah siswa berhasil lalu diarahkan ke halaman tautkan RFID.
- [ ] Tombol Lewati mempertahankan data siswa dan kembali ke daftar.
- [ ] Siswa dapat ditautkan kartu dari daftar siswa.
- [ ] Kartu kosong, tidak dikenal, duplikat pada siswa, dan duplikat dengan guru ditolak.
- [ ] Reset/ganti kartu membutuhkan konfirmasi dan tidak menghapus histori.
- [ ] Field nomor WhatsApp opsional; nilai kosong tidak menggagalkan penyimpanan siswa.

### Absensi siswa

- [ ] Scan pertama mencatat jam masuk dan status yang benar.
- [ ] Scan setelah tenggat mencatat waktu aktual dan status `TERLAMBAT`.
- [ ] Scan pulang mengisi `jam_pulang`; scan berulang tidak membuat catatan baru.
- [ ] Siswa tanpa scan pulang memiliki `jam_pulang = NULL` dan tidak memicu WA pulang.
- [ ] Request/scan bersamaan tidak membuat absensi ganda.
- [ ] Hari libur/non-hari sekolah mengikuti konfigurasi yang disepakati.
- [ ] Data siswa tersimpan terpisah dari tabel presensi guru.

### Status dan pengaturan

- [ ] Pengaturan siswa dan guru tampil dalam satu menu, tetapi perubahan konfigurasi siswa tidak mengubah konfigurasi guru.
- [ ] `Belum Hadir` baru dimulai setelah tenggat scan masuk.
- [ ] `Alpa` tidak ditetapkan sebelum tenggat + jeda yang diatur admin.
- [ ] Izin/sakit sah mengecualikan siswa dari perubahan otomatis menjadi Alpa.
- [ ] Perubahan status otomatis idempotent; koreksi admin tidak menghapus histori scan/notifikasi.
- [ ] Siswa yang datang setelah notifikasi menjadi `TERLAMBAT`, sedangkan log notifikasi tetap ada.

### Izin/sakit dan rekap

- [ ] Izin/sakit guru dan siswa disimpan pada entitas yang benar.
- [ ] Durasi lebih dari 3 hari memunculkan indikator pemeriksaan dokumen/alasan.
- [ ] Sakit lebih dari 3 hari mendukung pencatatan pemeriksaan surat dokter.
- [ ] Izin kegiatan/acara keluarga lebih dari 3 hari meminta alasan kuat.
- [ ] Rekap guru tidak regresi.
- [ ] Rekap siswa memuat identitas, kelas, tanggal, jam, status, dan filter minimum.

### WhatsApp

- [ ] Pesan tidak dikirim sebelum waktu evaluasi/tenggat yang berlaku.
- [ ] Siswa hadir atau memiliki izin/sakit sah tidak menerima notifikasi ketidakhadiran.
- [ ] Nomor kosong/invalid menghasilkan status skipped yang terlihat admin.
- [ ] Scheduler berjalan berkali-kali tidak mengirim pesan ganda.
- [ ] Kegagalan provider dicatat dan tidak membatalkan absensi.
- [ ] Tidak ada notifikasi karena jam pulang kosong.

### Regresi dan deployment

- [ ] Alur scan RFID guru tetap berjalan.
- [ ] Migration baru dapat dijalankan pada database uji tanpa kehilangan data.
- [ ] Test suite, route list, dan pemeriksaan view/lint dijalankan sesuai kemampuan environment.
- [ ] Instruksi cron/queue dan prasyarat provider terdokumentasi.

## 10. Rencana implementasi yang disarankan

1. Audit ulang source, middleware, model, migration, schema aktual, dan perilaku guru; tulis baseline regression.
2. Putuskan skema minimum untuk `card_id` siswa, kontak notifikasi, absensi siswa, pengaturan siswa, log notifikasi, dan audit perubahan.
3. Tambahkan migration aman dan model/relasi yang diperlukan.
4. Implementasikan penautan kartu siswa dan field kontak notifikasi.
5. Implementasikan scan RFID harian siswa dan feedback UI.
6. Implementasikan konfigurasi siswa pada menu Pengaturan Absensi yang sama dengan guru tetapi terpisah secara logis.
7. Implementasikan izin/sakit, kebijakan 3 hari, status sementara/Alpa, dan koreksi admin.
8. Implementasikan rekap siswa tanpa mengubah hasil rekap guru.
9. Implementasikan job/command notifikasi WhatsApp idempotent dan dokumentasi deployment.
10. Jalankan test/regresi dan laporkan setiap bagian yang tidak bisa diverifikasi.

## 11. Roadmap fase berikutnya

- Modul pengelolaan timetable/jadwal pelajaran berdasarkan aplikasi Timetables lama.
- Absensi QR Code siswa per mata pelajaran/jam, terhubung ke jadwal resmi.
- Aktivasi fungsi QR Code pada kartu pelajar untuk kebutuhan mendatang (setelah spesifikasi keamanan dan alur pemindaian ditetapkan).
- Portal orang tua yang hanya menampilkan siswa yang terhubung secara terverifikasi ke akun orang tua/wali. Nomor telepon saja tidak boleh menjadi satu-satunya mekanisme autentikasi.

## 12. Catatan keputusan yang harus dipertahankan

- Fase sekarang berfokus pada RFID absensi harian siswa dan guru.
- QR per mapel, timetable, aktivasi fungsi QR pada kartu, dan portal orang tua bukan deliverable fase ini. Pengelolaan/cetak kartu identitas tanpa aktivasi QR termasuk deliverable fase ini.
- Nomor WhatsApp orang tua/wali boleh kosong pada awal penggunaan; absensi tetap berfungsi.
- Batas scan masuk dan jeda perubahan `Belum Hadir` menjadi `Alpa` diatur admin.
- Jeda `Alpa` dihitung dari batas waktu scan masuk. Contoh tenggat 07.00 + jeda 3 jam = evaluasi mulai 10.00.
- Siswa yang belum scan masuk menjadi `Belum Hadir` terlebih dahulu. Status berubah menjadi `Alpa` setelah jeda yang dikonfigurasi jika tidak ada scan atau izin/sakit sah.
- Siswa yang datang setelah tenggat menjadi `TERLAMBAT`, dan catatan notifikasi yang sudah terkirim tetap disimpan.
- Tidak ada notifikasi WA karena siswa tidak scan pulang pada fase ini; `jam_pulang` tetap `NULL` untuk pemeriksaan admin.
- Pengaturan absensi satu menu, konfigurasi guru/siswa dipisah secara logis.


## 13. Fitur Kartu Identitas Pelajar dan Guru (deliverable fase ini)

### Tujuan dan prinsip

Sediakan modul **Kartu Identitas** di aplikasi `shaf-app-saku` untuk menampilkan, mengelola template, melihat pratinjau, serta mencetak kartu pelajar dan guru. Template desain dapat diganti oleh admin tanpa mengubah kode aplikasi. Fitur ini tidak boleh mengubah atau mengganggu proses absensi RFID harian.

### Template awal yang disediakan

- `Kartu pelajar — depan.jpg` — template sisi depan pelajar.
- `Kartu pelajar — belakang.jpg` — template sisi belakang pelajar; memiliki area QR visual untuk penggunaan masa depan.
- `Kartu guru — depan.png` — template sisi depan guru.
- Template sisi belakang guru belum tersedia; sistem harus mengizinkan admin menambahkannya kemudian. Sisi depan guru tetap dapat dipratinjau/dicetak jika template belakang belum diunggah.

Simpan file template awal di folder dokumentasi/template-kartu yang sesuai dan salin ke storage aplikasi hanya melalui implementasi yang disengaja. Jangan menganggap file yang dilampirkan otomatis sudah ada di repositori.

### FR-KI-1 — Akses dan otorisasi

- Tambahkan menu **Kartu Identitas** mengikuti pola navigasi aplikasi.
- Hanya admin/role yang secara eksplisit berwenang boleh mengunggah, mengganti, mengaktifkan, atau menghapus template. Gunakan middleware/policy/otorisasi server-side yang sudah ada; menyembunyikan tombol saja tidak cukup.
- Pengguna yang diberi akses cetak boleh memilih dan mencetak kartu, tetapi tidak boleh mengubah template kecuali juga memiliki izin admin. Jangan memperluas hak akses secara default.

### FR-KI-2 — Pengelolaan template

- Template dikelola terpisah menurut tipe entitas (`pelajar`, `guru`) dan sisi (`depan`, `belakang`).
- Format upload yang didukung: JPG/JPEG dan PNG. Validasi MIME/content sebenarnya, ekstensi, ukuran file, serta dimensi; jangan mempercayai nama file saja. Simpan dengan nama yang dihasilkan server, cegah path traversal, dan jangan mengeksekusi file upload.
- Admin dapat melihat pratinjau template baru sebelum mengaktifkannya. Template aktif lama tetap digunakan jika upload/validasi template baru gagal.
- Penggantian template tidak menghapus data siswa/guru, foto, NIS/NISN/NIP/ID, RFID `card_id`, histori absensi, atau template sisi lain.
- Template belakang guru boleh kosong. UI harus menjelaskan bahwa sisi belakang belum dikonfigurasi dan tidak boleh membuat gambar belakang palsu.
- Implementasikan status aktif dan penggantian yang aman; hindari kebutuhan mengubah source code untuk mengganti desain.

### FR-KI-3 — Data dan overlay identitas

- Audit model, relasi, kolom, serta mekanisme foto yang benar-benar digunakan aplikasi sebelum menentukan mapping. Jangan mengasumsikan nama kolom foto tanpa memeriksanya.
- Pelajar: gunakan foto, nama lengkap, NIS dan/atau NISN, serta kelas sesuai data yang tersedia dan desain template.
- Guru: gunakan foto pegawai, nama guru, ID/NIP bila tersedia, dan jabatan/bidang sesuai data aktual dan desain. Jangan mengarang nilai yang tidak ada.
- Posisi foto dan teks ditentukan saat implementasi dengan mengacu pada tiga template yang disediakan; admin tidak diwajibkan mengatur koordinat manual. Gunakan konfigurasi/komponen terpisah per tipe kartu/sisi agar mudah disesuaikan setelah uji cetak.
- Pertahankan rasio template dan foto; gunakan crop/fit yang sesuai. Tangani nama panjang, field kosong, foto tidak tersedia, dan karakter khusus dengan aman. Tampilkan peringatan untuk data penting yang hilang dan jangan menampilkan identitas milik orang lain sebagai fallback.
- QR pada template belakang pelajar untuk fase ini hanya dipertahankan sebagai bagian gambar latar. Jangan menimpa area tersebut dengan QR dinamis, jangan mengklaim QR berfungsi untuk absensi, dan jangan menambahkan route scan QR. Aktivasi QR membutuhkan spesifikasi keamanan terpisah.

### FR-KI-4 — Pratinjau dan pencetakan

- Pengguna dapat memilih jenis kartu (pelajar/guru), mencari dan memilih satu orang atau beberapa orang, mempratinjau sisi depan/belakang, lalu mencetak satuan atau massal.
- Cetak menggunakan dialog print browser dan CSS print khusus; sembunyikan navigasi/kontrol UI saat cetak, pertahankan ukuran/rasio gambar, hindari pemotongan elemen, dan sediakan instruksi duplex/bolak-balik bila didukung printer.
- Susun halaman agar sisi depan dan belakang tetap berpasangan untuk pemilik yang sama pada cetak massal. Dokumentasikan urutan halaman dan pilihan flip tepi panjang/pendek berdasarkan orientasi desain; jangan mengklaim hasil duplex terjamin di semua printer.
- Jika belakang guru belum tersedia, tawarkan cetak depan saja atau minta admin mengonfirmasi cetak satu sisi; jangan menyisipkan sisi belakang pelajar atau template orang lain.
- Validasi bahwa semua ID yang dipilih benar-benar ada dan pengguna berwenang mengakses data tersebut. Hindari query tidak terbatas untuk cetak massal; gunakan pencarian/filter/paginasi dan batas batch yang wajar.

### Kriteria penerimaan Kartu Identitas

1. Admin dapat mengganti template depan/belakang pelajar dan guru secara terpisah.
2. Kartu pelajar dapat dipratinjau/cetak dua sisi; area QR belakang tetap bagian desain statis pada fase ini.
3. Kartu guru depan dapat dipakai tanpa template belakang; admin dapat mengunggah belakang di kemudian hari.
4. Foto/teks yang tampil cocok dengan orang yang dipilih dan data aktual; data kosong ditangani secara eksplisit.
5. Cetak satuan dan massal menampilkan pasangan depan-belakang yang tepat tanpa mencampur identitas.
6. Penggantian template dibatasi ke admin dan tidak memengaruhi data identitas/RFID/absensi.
7. File upload yang tidak valid ditolak dengan pesan jelas; template aktif sebelumnya tetap aman.
8. Test mencakup otorisasi, validasi upload, template sisi yang hilang, data/foto kosong, pemilihan orang, serta layout/urutan cetak.
9. Test regresi absensi guru/siswa yang sudah ada tetap lulus.

### Keputusan teknis untuk Codex

- Prioritaskan pendekatan Laravel/Blade yang paling sesuai dengan struktur aplikasi; jangan menambahkan dependency besar jika HTML/CSS print dan storage Laravel cukup.
- Pilih lokasi penyimpanan privat atau publik berdasarkan kebutuhan akses dan pola `storage` yang ada; jangan mengekspos template admin atau data foto privat tanpa audit.
- Gunakan migration baru bila penyimpanan metadata template memerlukan tabel baru; jangan mengedit migration lama. Jika penyimpanan file tunggal lebih sesuai dengan arsitektur yang ada, dokumentasikan alasannya dan pastikan penggantian/rollback aman.
- Jangan membaca atau mencetak `.env`, token, data siswa/guru nyata, atau secret dari git history.
