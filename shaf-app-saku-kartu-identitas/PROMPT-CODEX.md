# Instruksi siap-tempel untuk Codex — Kartu Identitas `shaf-app-saku`

Baca `PRD.md` dan `AGENT.md` sampai selesai. Implementasikan Tahap 9 pada `AGENT.md` sebagai bagian dari kebutuhan fase saat ini, sambil mempertahankan semua fungsi absensi RFID yang sudah ada.

## Cara kerja wajib
1. Mulai dengan audit read-only terhadap working tree, `git status`, route/middleware/role, menu, model `Siswa`/`Guru`, kolom foto/identitas, relasi, storage, upload, dan test. Jangan membuka atau menampilkan `.env`, kredensial, data siswa/guru nyata, atau rahasia git history. Jangan menganggap source archive identik dengan working tree aktif.
2. Sebelum edit, ringkas temuan arsitektur dan rencana file yang akan diubah. Jangan berhenti hanya karena detail kosmetik; gunakan asumsi konservatif dalam PRD dan dokumentasikan asumsi.
3. Implementasikan modul admin-only untuk mengunggah, melihat pratinjau, mengganti, dan mengaktifkan template terpisah: pelajar depan/belakang, guru depan/belakang. Validasi konten gambar JPG/JPEG/PNG, ukuran, dimensi, dan ekstensi; gunakan nama file server-generated; pertahankan template aktif saat upload gagal.
4. Gunakan aset yang disediakan: `Kartu pelajar — depan.jpg`, `Kartu pelajar — belakang.jpg`, `Kartu guru — depan.png`. Jangan membuat desain belakang guru yang belum tersedia. Jika file tidak ada di working tree, jelaskan dengan jelas dan siapkan folder/nama tujuan yang diminta, bukan menebak path.
5. Audit field aktual sebelum mapping. Overlay foto/teks mengikuti desain template; admin tidak perlu editor koordinat. Handle field/foto kosong dan nama panjang secara aman.
6. Tambahkan preview serta cetak satuan/massal menggunakan CSS print, pastikan sisi depan/belakang tetap dipasangkan dengan pemilik yang tepat. Jika belakang guru belum diunggah, izinkan cetak depan saja dengan penjelasan. Jangan menjanjikan duplex sempurna tanpa uji printer fisik.
7. QR pada belakang kartu pelajar hanya gambar statis dari template pada fase ini. Jangan menghasilkan QR dinamis, menambah scan QR, atau mengklaim fitur QR aktif.
8. Jangan mengedit migration lama. Gunakan migration baru hanya jika dibutuhkan, dan jangan mengubah data produksi. Jangan ubah alur RFID, data `card_id`, atau histori absensi.
9. Tambahkan test yang relevan untuk authorization, upload validation, template sisi hilang, data/foto kosong, pilihan satuan/massal, urutan depan-belakang, dan regression. Jalankan test/check yang benar-benar dapat dijalankan. Jangan klaim test lulus jika belum dijalankan.
10. Tinjau diff dan pastikan `.env`, `.git`, `vendor`, log, cache, database dump, token, dan data pribadi tidak ikut deliverable.

## Kriteria selesai
- Pengelolaan template hanya oleh admin di server-side.
- Template tiap tipe dan sisi independen serta dapat diganti tanpa perubahan source code.
- Preview/cetak menggunakan foto dan field identitas yang benar.
- Cetak satuan/massal menjaga pairing depan-belakang.
- Template belakang guru boleh belum ada.
- QR pelajar tetap statis/nonaktif.
- Regresi absensi tidak ditemukan dari test yang dijalankan.

## Laporan akhir wajib
Berikan daftar file berubah, fitur selesai, migration (jika ada), test yang dijalankan beserta hasil sebenarnya, hal yang belum diverifikasi (khususnya uji cetak/printer), instruksi setup/deploy, dan risiko tersisa. Jangan tampilkan secret atau data pribadi.
