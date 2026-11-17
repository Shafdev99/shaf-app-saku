# AGENT.md — Instruksi Implementasi Codex

## Misi

Implementasikan kebutuhan **absensi RFID harian siswa/guru dan Kartu Identitas pelajar/guru** pada repositori Laravel `shaf-app-saku`, berdasarkan `PRD.md`. Pertahankan alur absensi guru yang ada. Kerjakan dengan migration yang aman, validasi server-side, test yang relevan, dan laporan akhir yang jujur.

## Prioritas cakupan

### Wajib pada fase ini

- Penautan RFID siswa setelah siswa dibuat, dapat dilewati, dan dapat dikelola dari daftar siswa.
- Nomor WhatsApp orang tua/wali untuk notifikasi absensi (opsional saat awal).
- Absensi RFID harian siswa: masuk dan pulang.
- Izin/sakit guru dan siswa, termasuk kebijakan pemeriksaan durasi lebih dari 3 hari.
- Rekap guru dan siswa.
- Satu menu Pengaturan Absensi, dengan konfigurasi guru dan siswa terpisah secara logis.
- Status `Belum Hadir` dan transisi ke `Alpa` sesuai jeda yang ditentukan admin.
- Notifikasi WhatsApp ketidakhadiran siswa, log status, idempotensi, dan instruksi scheduler/queue.
- Pengujian regresi untuk alur absensi guru.
- Modul Kartu Identitas: template dapat diganti admin, pratinjau, cetak satuan/massal pelajar dan guru.

### Jangan implementasikan pada fase ini

- QR Code untuk presensi per mapel.
- Modul timetable/jadwal pelajaran.
- Aktivasi QR Code untuk absensi/fitur digital pada kartu (QR belakang pelajar hanya menjadi bagian template statis pada fase ini).
- Modul timetable/jadwal pelajaran.
- Portal orang tua.

Catat fitur masa depan di PRD; jangan menambahkan menu/route QR aktif atau timetable yang berpura-pura berfungsi. Pengelolaan template dan cetak kartu tanpa aktivasi QR justru wajib diimplementasikan pada fase ini.

## Aturan wajib sebelum coding

1. Baca `PRD.md` seluruhnya dan audit working tree yang benar-benar akan diubah.
2. Jangan keluarkan atau tampilkan isi `.env`, kredensial, token WhatsApp, data siswa/guru nyata, atau git history yang mengandung rahasia.
3. Jangan mengedit migration lama. Buat migration baru yang kompatibel dengan database yang mungkin sudah menjalankan migration sebelumnya.
4. Jangan simpan absensi siswa ke `presensi.guru_id` atau `ketidakhadiran.guru_id`.
5. Jangan membuat integrasi WhatsApp kedua; audit dan gunakan `App\Services\WhatsappService` serta konfigurasi provider yang ada.
6. Jangan menganggap migration archive lengkap. Model `Ayah`, `Ibu`, `Walmur` dan service terkait menggunakan tabel/field yang migration-nya tidak terlihat di archive. Periksa schema database aktual, seluruh migration, dan environment test tanpa membaca secret.
7. Jangan mengaktifkan blok kode scan yang dikomentari sebelum membandingkannya dengan implementasi aktif.
8. Jangan menyatakan test/migration/deployment berhasil kecuali perintahnya benar-benar dijalankan dan hasilnya diperiksa.
9. Bila ada detail yang belum eksplisit, ikuti asumsi konservatif yang didokumentasikan di bawah ini, lalu laporkan sebagai asumsi. Jangan menghentikan seluruh pekerjaan karena detail kosmetik.

## Temuan arsitektur yang harus diverifikasi ulang

- Laravel `^11.9`, PHP `^8.3`.
- Model `Guru` memakai `card_id`, `no_hp`, `status_akun`; route tautkan/reset kartu ada di `GuruController`.
- Model `Siswa` memakai UUID dan tabel `siswa`; migration yang terlihat belum memuat `card_id` atau nomor WhatsApp kontak notifikasi.
- `Siswa` mempunyai relasi `ayah`, `ibu`, `wali`; sumber telepon yang ada berpotensi bernama `telp_ayah`, `telp_ibu`, `telp_walmur`. Jangan menimpa/menyalin data tersebut tanpa keputusan sumber data yang eksplisit.
- `AbsensiController` mengelola pengaturan, hari libur, scan RFID guru, izin/sakit, dan rekap. Implementasi scan aktif berorientasi guru.
- Tabel `presensi` dan `ketidakhadiran` yang terlihat berorientasi guru. Absensi siswa harus terpisah.
- Model `Absensi` menggunakan tabel `pengaturan_absensi`, dengan jam masuk, toleransi terlambat, jam pulang, batas jam pulang, hari kerja.
- `WhatsappService` menggunakan provider Fonnte; normalisasi nomor yang ada hanya menangani awalan `0` menjadi `62`, sehingga validasi/normalisasi perlu diaudit dan diuji.
- Library `simplesoftwareio/simple-qrcode` tersedia di `composer.json`, tetapi QR bukan scope fase ini.
- Route `/absensi/scan` didefinisikan di luar group middleware `all`; audit akses sebelum mengubah route.

## Cara kerja

### Tahap 1 — Audit dan rencana singkat

- Periksa `git status`, struktur folder, `composer.json`, route, middleware, model, controller, view, migration, service WhatsApp, scheduler/queue, serta test yang ada.
- Jangan menjalankan perintah yang mengubah database produksi atau menjalankan migration tanpa environment uji/backup yang jelas.
- Audit flow tenaga pendidik: create/edit, tautkan/reset kartu, duplikasi kartu, scan masuk/pulang, status terlambat, hari kerja/libur, izin/sakit, rekap, WhatsApp.
- Periksa schema database yang aktual/tersedia, khususnya tabel `siswa`, `guru`, `walmur`, `ayah`, `ibu`, `biodata`, `pengaturan_absensi`, `presensi`, dan `ketidakhadiran`.
- Setelah audit, buat ringkasan file yang akan berubah dan skema baru, lalu lanjutkan implementasi tanpa menunggu konfirmasi untuk hal yang sudah diputuskan dalam PRD.

### Tahap 2 — Skema dan model

Rancang dan implementasikan perubahan schema setelah audit:

- Tambahkan `card_id` untuk siswa bila belum ada.
- Tambahkan field khusus kontak WhatsApp notifikasi absensi, misalnya `no_wa_ortu`, bila belum ada sumber kontak tunggal yang tepat. Field nullable.
- Buat tabel absensi harian siswa terpisah dari `presensi` guru. Minimal: UUID, `siswa_id`, tanggal, `jam_masuk` nullable, `jam_pulang` nullable, status, timestamps, dan metadata koreksi bila sesuai.
- Tambahkan unique constraint siswa+tanggal dan index yang dibutuhkan.
- Perluas pengaturan dengan field siswa tanpa mengubah perilaku field guru. Tetap tampilkan dalam satu menu UI.
- Buat tabel log notifikasi untuk idempotensi/audit. Minimal: siswa, tanggal, jenis notifikasi, status, jumlah percobaan, timestamps percobaan/pengiriman, dan error aman yang sudah disanitasi.
- Pertimbangkan audit trail perubahan status bila belum ada mekanisme yang sesuai.
- Gunakan foreign key/UUID sesuai schema aktual dan database engine.
- Jangan menghapus histori data guru dan jangan membuat migration yang mengasumsikan database selalu kosong.

**Keunikan RFID lintas entitas:** kartu harus unik di antara siswa dan guru. Unique index pada `siswa.card_id` dan `guru.card_id` saja tidak mencegah nilai yang sama pada dua tabel. Pilih strategi paling minim risiko yang benar-benar menjamin integritas lintas entitas (misalnya registry kartu terpusat) dan pertahankan kompatibilitas data kartu guru yang ada. Cek data duplikat sebelum menerapkan constraint/migrasi.

### Tahap 3 — Penautan kartu dan kontak orang tua

- Audit dan ikuti pola UI/reader dari `tenaga-pendidik-tautkan-kartu`.
- Setelah tambah siswa berhasil, redirect ke halaman tautkan kartu untuk siswa yang dibuat.
- Tombol **Lewati** mengembalikan pengguna ke daftar tanpa menghapus siswa.
- Tambahkan status kartu dan aksi **Tautkan kartu** di daftar siswa; konfirmasi saat reset/ganti kartu.
- Validasi server-side kartu kosong, kartu tak dikenal/duplikat, ID siswa, serta hak akses.
- Jangan menghapus histori absensi saat kartu diganti.
- Tambahkan field kontak WhatsApp opsional pada form tambah/edit siswa. Simpan sebagai string dan normalisasi sesuai provider.
- Jangan gunakan nomor telepon sebagai autentikasi atau mengganti `telp_ayah`/`telp_ibu`/`telp_walmur` secara diam-diam.
- Jika nomor kosong/invalid, jangan gagalkan absensi; tandai notifikasi sebagai skipped dan beri status yang terlihat admin.

### Tahap 4 — Scan RFID siswa

- Implementasikan scan masuk/pulang dengan pola feedback yang konsisten dengan halaman scan saat ini.
- Identifikasi kartu berdasarkan data yang telah ditautkan; periksa status siswa aktif sesuai definisi yang ditemukan dari aplikasi.
- Pada scan pertama hari itu, simpan jam masuk aktual dan status sesuai tenggat siswa.
- Scan setelah tenggat menghasilkan `TERLAMBAT`, tanpa mengubah waktu scan.
- Scan pulang hanya mengisi `jam_pulang` bila belum ada, setelah validasi rentang waktu yang dikonfigurasi. Scan ganda tidak membuat row baru.
- Jika tidak scan pulang, biarkan `jam_pulang = NULL`. Tidak ada WA karena tidak scan pulang pada fase ini; admin menanganinya secara manual.
- Hari libur/non-hari sekolah harus ditangani konsisten dengan konfigurasi.
- Gunakan transaksi, locking bila diperlukan, dan unique constraint agar request bersamaan/scan ganda tidak menghasilkan data ganda.
- Jangan mengubah alur guru yang sudah aktif selain yang diperlukan untuk menghindari konflik.

### Tahap 5 — Pengaturan, status otomatis, izin/sakit

Menu Pengaturan Absensi tetap satu, tetapi konfigurasi siswa dan guru terpisah secara logis.

Konfigurasi siswa wajib mencakup:

- Batas waktu scan masuk.
- Waktu evaluasi/pengiriman notifikasi WhatsApp yang dapat diatur admin.
- Jeda perubahan status `Belum Hadir` menjadi `Alpa`.

Aturan:

- Setelah batas scan masuk terlewati, siswa tanpa scan masuk menjadi `Belum Hadir`.
- Jeda perubahan status dihitung sejak batas waktu scan masuk. Contoh 07.00 + 3 jam = evaluasi perubahan mulai 10.00.
- Hanya ubah menjadi `Alpa` setelah jeda berakhir, bila belum ada scan dan tidak ada izin/sakit sah.
- Bila siswa datang setelah notifikasi, simpan scan aktual dengan status `TERLAMBAT`; jangan hapus riwayat notifikasi.
- Job status harus idempotent. Konfigurasi baru tidak boleh mengubah histori sembarangan; jelaskan perilaku tanggal berjalan dan keterlambatan scheduler.
- Izin/sakit yang berlaku pada tanggal tersebut mengecualikan siswa dari notifikasi dan perubahan otomatis ke `Alpa`.
- Izin/sakit lebih dari 3 hari menampilkan indikator pemeriksaan. Untuk sakit, admin memeriksa surat dokter; untuk izin kegiatan/acara keluarga, admin perlu alasan kuat.
- Jangan menolak/menghapus pengajuan otomatis hanya karena durasi. Catat status dokumen pendukung. Gunakan pola upload yang sudah aman jika tersedia; jangan membangun upload file yang tidak tervalidasi.
- Jangan mencampur status izin/sakit siswa ke kolom `guru_id`.

### Tahap 6 — Rekap

- Tambahkan tab/pemilih Guru/Siswa pada menu rekap yang sudah ada.
- Pertahankan hasil rekap/export guru sebagai baseline.
- Rekap siswa menampilkan nama, kelas, tanggal, jam masuk/pulang, status, dan informasi izin/koreksi bila relevan.
- Filter minimal tanggal mulai/akhir dan status; tambahkan kelas/nama siswa bila relasi ada.
- Bedakan `Belum Hadir` dari `Alpa` pada tampilan dan statistik.
- Ekspor (jika diubah) harus mempertahankan filter dan memberi kolom jenis entitas; jangan mencampur guru/siswa tanpa label.
- Hindari N+1 query dan pertahankan pagination.

### Tahap 7 — Notifikasi WhatsApp

- Gunakan `WhatsappService`; audit respons Fonnte, timeout, konfigurasi token, dan logging sebelum mengubahnya.
- Jalankan evaluasi melalui Laravel Scheduler/Queue/command sesuai infrastruktur yang ditemukan. Jangan mengandalkan page load.
- Evaluasi siswa aktif pada hari sekolah, belum scan masuk, dan tidak memiliki izin/sakit sah.
- Notifikasi menyebut siswa belum tercatat hadir; jangan menyatakan `Alpa` sebelum jeda konfigurasi berakhir.
- Jangan mengirim bila nomor kosong/invalid, hari libur, siswa sudah scan masuk, atau izin/sakit sah berlaku.
- Simpan status `pending`/`sent`/`failed`/`skipped`, jumlah percobaan, waktu, dan alasan aman.
- Gunakan unique key/lock/transaksi untuk mencegah pesan ganda per siswa+tanggal+jenis pesan. Scheduler yang berjalan berulang harus aman.
- Retry kegagalan dengan batas yang masuk akal. Jangan menganggap request HTTP sukses selalu berarti pesan sudah diterima; gunakan status provider jika tersedia.
- Jangan pernah menulis token provider, nomor penuh, atau isi rahasia ke log.
- Kegagalan WhatsApp tidak boleh membatalkan absensi.
- Jangan membuat notifikasi untuk tidak scan pulang.

### Tahap 8 — Pengujian dan verifikasi

Tambahkan/ubah test sesuai framework yang ada. Minimal uji:

1. Siswa baru diarahkan ke tautkan kartu; tombol Lewati tetap mempertahankan siswa.
2. Tautkan kartu siswa, reset/ganti kartu, ID kosong, duplikat siswa, duplikat lintas guru-siswa, dan request bersamaan.
3. Field nomor WhatsApp opsional, normalisasi, format invalid, dan nomor kosong.
4. Scan masuk tepat waktu, scan terlambat, scan pulang, scan berulang, kartu tak dikenal, siswa nonaktif, hari libur, serta race condition.
5. Absensi siswa tidak membuat record di tabel guru.
6. Pengaturan siswa tidak mengubah jam/toleransi guru.
7. Status Belum Hadir, batas tenggat, jeda Alpa, izin/sakit, dan koreksi admin.
8. Kebijakan izin lebih dari 3 hari dan status pemeriksaan surat dokter/alasan kuat.
9. Rekap guru tidak regresi; rekap siswa/filter/statistik benar.
10. WA tidak terkirim sebelum evaluasi, duplikasi dicegah, izin/sakit dan hadir dikecualikan, nomor kosong skipped, provider gagal dicatat.
11. Migrasi pada database uji yang memiliki data lama tidak menghapus histori.

Jalankan sesuai environment yang tersedia, misalnya:

- `php artisan route:list`
- `php artisan migrate:status`
- `php artisan test`
- `vendor/bin/pint --test` jika tersedia
- `php artisan view:cache` dan `php artisan view:clear` jika aman
- pemeriksaan diff, status git, dan secret scan

Jangan menjalankan migrasi pada database produksi atau menjalankan perintah destruktif tanpa backup dan otorisasi yang jelas.

## Keputusan/asumsi operasional yang wajib diikuti

- Scope saat ini adalah RFID harian untuk siswa dan guru.
- QR per mapel, timetable, cetak kartu QR, dan portal orang tua ditunda ke fase berikutnya.
- Satu menu Pengaturan Absensi; konfigurasi siswa dan guru terpisah secara logis.
- Nomor WA orang tua/wali opsional saat awal. Absensi tidak bergantung pada nomor tersebut.
- Siswa tanpa scan setelah tenggat menjadi `Belum Hadir`; admin mengatur jeda hingga `Alpa`.
- Jeda `Alpa` dihitung sejak tenggat scan masuk. Contoh 07.00 + 3 jam = 10.00.
- Siswa yang scan setelah tenggat berstatus `TERLAMBAT`; riwayat WA yang sudah terkirim tetap tersimpan.
- Siswa dengan izin/sakit sah pada tanggal terkait tidak menerima WA ketidakhadiran dan tidak otomatis menjadi Alpa selama periode itu berlaku.
- Jam pulang yang tidak tercatat tetap `NULL`; admin menanganinya secara manual. Tidak ada WA pulang yang hilang.
- Kebijakan izin berlaku sampai 3 hari. Lebih dari 3 hari: surat dokter diperiksa untuk sakit; alasan kuat diperiksa untuk izin kegiatan/acara keluarga.
- Waktu lokal mengikuti `config('app.timezone')`/timezone deployment yang terverifikasi.

## Format laporan akhir Codex

Sampaikan:

1. Ringkasan perubahan dan perilaku yang diimplementasikan.
2. File yang diubah beserta alasannya.
3. Migration/model/constraint baru dan dampak kompatibilitas.
4. Keputusan arsitektur untuk keunikan RFID lintas entitas dan sumber nomor kontak notifikasi.
5. Hasil test dan perintah yang benar-benar dijalankan.
6. Konfigurasi cron/scheduler/queue dan provider yang diperlukan.
7. Asumsi atau bagian yang belum dapat diverifikasi, termasuk perbedaan antara archive dan database aktual.
8. Konfirmasi eksplisit bahwa QR/timetable/kartu QR/portal orang tua tidak dibangun dalam fase ini.


## Tahap 9 — Kartu Identitas pelajar dan guru

### Tujuan
Implementasikan menu Kartu Identitas sesuai bagian 13 `PRD.md`. Fitur harus berdiri terpisah dari alur absensi; jangan mengubah RFID, histori absensi, atau identitas master selain yang dibutuhkan untuk membaca data.

### Aset template yang diberikan pengguna

- `Kartu pelajar — depan.jpg` (590×1004 px): template depan pelajar.
- `Kartu pelajar — belakang.jpg` (590×1004 px): template belakang pelajar, dengan area QR yang merupakan desain statis untuk fase ini.
- `Kartu guru — depan.png` (591×1004 px): template depan guru.
- Template belakang guru belum ada. Jangan membuat/mengarang desain belakang guru; sediakan slot upload untuk admin.

Jika aset belum berada di working tree Codex, minta pengguna menempatkannya ke folder yang disepakati atau gunakan attachment yang tersedia. Jangan menebak path. Jangan menyimpan salinan aset pada lokasi publik sebelum mengevaluasi pola storage dan akses aplikasi.

### Urutan kerja wajib

1. Audit route, middleware, role/permission, layout/menu, model `Siswa`/`Guru`, relasi kelas/jurusan/jabatan, kolom foto dan ID, disk storage, serta pola upload yang ada. Jangan baca `.env` atau nilai secret.
2. Tuliskan ringkasan mapping kolom aktual ke elemen kartu, termasuk fallback untuk field kosong, tanpa mengeluarkan data orang sungguhan.
3. Rancang metadata template untuk tipe dan sisi. Gunakan migration baru jika perlu, storage Laravel, validasi file berdasarkan konten, nama file server-generated, dan perlindungan akses server-side. Pertahankan template aktif bila upload pengganti gagal.
4. Buat pengelolaan template yang hanya bisa diakses admin: unggah, pratinjau, ganti/aktifkan template per sisi. Penghapusan harus memiliki konfirmasi dan tidak boleh menghapus file yang sedang aktif tanpa pengganti yang valid.
5. Implementasikan pratinjau kartu pelajar/guru dengan overlay foto/teks. Atur posisi berdasarkan tiga template yang tersedia; pertahankan aspect ratio dan lakukan pemeriksaan visual. Jangan tambahkan editor koordinat manual untuk admin.
6. Implementasikan pemilihan individu/massal, cetak dengan CSS `@media print`, pasangan depan-belakang yang benar, dan alur cetak depan saja ketika template belakang guru belum tersedia. Jangan menyatakan duplex otomatis berfungsi tanpa pengujian perangkat nyata.
7. QR belakang pelajar tetap gambar statis dari template. Jangan menghasilkan token, route scan, atau mengaktifkan absensi QR. Catat sebagai roadmap.
8. Tambahkan test otorisasi admin/non-admin, validasi upload, aktivasi/penggantian template, template sisi belakang kosong, mapping data/foto kosong, pemilihan ID, cetak satuan/massal dan urutan pasangan. Tambahkan regression tests yang relevan.
9. Jalankan test dan pemeriksaan aman yang tersedia. Jangan menjalankan migration pada produksi atau perintah destruktif. Laporkan hasil aktual, keterbatasan environment, dan langkah uji cetak manual.

### Batas keamanan dan kualitas

- Tidak ada endpoint pengelolaan template yang mengandalkan UI saja; semua tindakan write harus diotorisasi di server.
- Batasi tipe/ukuran/dimensi gambar dan tangani file rusak dengan aman. Hindari SVG/HTML sebagai template.
- Jangan membocorkan foto/data pribadi lewat URL publik jika kebijakan aplikasi tidak mengizinkan.
- Jangan menampilkan identitas yang salah jika foto/field tidak ada; tampilkan placeholder/peringatan. Jangan gunakan data orang lain sebagai fallback.
- Jangan menambahkan QR aktif atau mengubah proses RFID yang sudah berjalan.
- Jangan memasukkan `.env`, `vendor/`, `.git/`, cache, log, database dump, atau kredensial ke paket deliverable.

### Checklist penerimaan tambahan

- [ ] Hanya admin yang dapat mengubah template; pengujian non-admin ditolak server-side.
- [ ] Template depan/belakang pelajar/guru dikelola terpisah.
- [ ] Belakang guru yang belum ada ditangani jelas tanpa menghalangi cetak depan.
- [ ] Foto, nama, ID, kelas/jabatan menggunakan kolom yang sudah diverifikasi.
- [ ] Cetak satuan dan massal tidak tertukar identitas atau sisi.
- [ ] QR belakang pelajar tidak aktif dan tetap menjadi bagian latar template.
- [ ] Tes regresi absensi dan test suite yang tersedia dijalankan; hasil dicatat jujur.
