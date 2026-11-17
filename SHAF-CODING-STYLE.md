# Shaf Coding Style and Project Guide

Dokumen ini merangkum struktur dan gaya coding yang terlihat pada project Saku. Gunakan sebagai instruksi awal ketika membuat project Laravel berikutnya agar struktur, penamaan, alur kerja, dan tampilan tetap terasa konsisten.

> Catatan: bagian **Konvensi yang Terlihat** mendeskripsikan pola yang sudah dipakai di repo. Bagian **Aturan untuk Implementasi Baru** adalah bentuk yang lebih rapi dan aman dari pola tersebut.

## 1. Identitas Project

- Nama aplikasi: Saku / Buku Induk Siswa.
- Bahasa antarmuka: Bahasa Indonesia.
- Framework: Laravel 11.
- PHP: 8.3.
- Database: MySQL.
- Frontend utama: Blade, Bootstrap 5, jQuery, Bootstrap Icons.
- Plugin: Toastr, Dropify, Summernote, Laravel Excel, mPDF, Simple QR Code, Yajra DataTables.
- Build tool: Vite.
- Style utama: Bootstrap utility class ditambah CSS custom di `public/css`.

## 2. Struktur Folder

```text
app/
  Console/Commands/       # Artisan command custom
  Exports/                # Class export Excel berbasis view
  Http/Controllers/       # Controller per modul/domain
  Imports/                # Import Excel dan validasinya
  Mail/                   # Mailable dan proses email
  Models/                 # Eloquent model, relasi, dan query scope
  Providers/              # Service provider dan Gate
  Repository/             # Operasi query/proses data yang lebih spesifik
  Services/               # Validasi form, business process, upload, CRUD
bootstrap/
config/
database/
  factories/
  migrations/
  seeders/
lang/
public/
  css/                    # Bootstrap, plugin, dan CSS custom legacy
  js/                     # jQuery, Bootstrap, plugin, script global
  img/                    # Logo dan gambar aplikasi
  import/                 # File/template import
resources/
  css/app.css             # Entry CSS Vite; saat ini minimal/kosong
  js/app.js               # Entry JS Vite
  views/                  # Blade view
    layout/               # layout.app dan layout.auth
    binduk/               # Buku induk/rekap siswa
    spmb/                 # Penerimaan siswa baru
    siswa/                # Data siswa
    user/                 # Pengguna
    walkel/               # Wali kelas
    ...                   # Modul lain
routes/
  web.php                 # Route web dan middleware
storage/
tests/
  Feature/
  Unit/
```

### Aturan lokasi file

- Tambahkan controller ke `app/Http/Controllers`.
- Tambahkan model ke `app/Models` dengan nama PascalCase tunggal.
- Tambahkan proses bisnis atau proses form ke `app/Services`.
- Tambahkan query/proses yang reusable dan spesifik ke `app/Repository` atau model scope.
- Tambahkan view ke folder modul yang sesuai di `resources/views`.
- Gunakan `layout.app` untuk halaman internal dan `layout.auth` untuk login/visitor.
- Letakkan JavaScript/CSS global lama di `public`; gunakan entry Vite untuk asset yang memang dikelola Vite.

## 3. Arsitektur Aplikasi

Alur umum yang dipakai:

```text
Route
  -> Controller
      -> Service (validasi dan business process)
          -> Repository/Model (query dan perubahan database)
      -> View / redirect / JSON response
```

### Controller

Controller bertugas untuk:

- menerima request dan parameter route;
- mengambil data yang diperlukan untuk view;
- memanggil service untuk proses create/update/delete;
- mencatat activity log jika perubahan data perlu dilacak;
- mengembalikan `view()`, `redirect()`, `response()->json()`, download, atau PDF.

Controller di repo biasanya di-inject melalui constructor:

```php
protected $masterService;

public function __construct(MasterService $masterService)
{
    $this->masterService = $masterService;
}
```

Untuk fitur baru, pertahankan controller tetap tipis. Hindari menaruh validasi panjang, upload file, atau rangkaian `Model::create()` langsung di controller jika prosesnya menjadi lebih dari beberapa baris.

### Service

Service digunakan untuk proses domain, terutama:

- validasi request dengan `$request->validate()` atau `request()->validate()`;
- create/update beberapa model;
- upload dan penghapusan file lama;
- pemanggilan repository;
- proses yang terdiri dari beberapa langkah.

Contoh pola yang digunakan:

```php
public function formBiodataUpdate($siswaId)
{
    $data = request()->validate([
        'siswa_id'        => 'required',
        'anak_ke'         => 'required',
        'kewarganegaraan' => 'required',
    ], [
        'required' => ':attribute wajib diisi!',
    ]);

    return Biodata::updateOrCreate(['siswa_id' => $siswaId], $data);
}
```

Untuk implementasi baru:

- gunakan nama service sesuai domain, misalnya `SiswaService`, `SpmbService`, atau `AbsensiService`;
- gunakan parameter `Request $request` bila service membutuhkan request;
- kembalikan model/hasil proses bila controller membutuhkannya;
- gunakan transaction untuk proses yang membuat atau mengubah beberapa tabel sekaligus;
- jangan bergantung pada `request()` global jika dependency request dapat dikirim secara eksplisit.

### Repository dan Model

Model menyimpan:

- nama tabel custom melalui `$table` bila nama tabel bukan bentuk plural Laravel;
- mass-assignment rule, biasanya `$guarded = ['id']`;
- relasi Eloquent;
- local scope untuk query yang dipakai berulang;
- select, join, sorting, dan pagination tertentu.

Contoh gaya scope:

```php
public function scopeUrutanSiswa(Builder $query)
{
    return $query->select(
        'siswa.id',
        'siswa.nama_lengkap',
        'siswa.nisn'
    )
        ->join('keterangan_keterima', 'keterangan_keterima.siswa_id', '=', 'siswa.id')
        ->join('kelas', 'kelas.id', '=', 'keterangan_keterima.kelas_id')
        ->paginate(10)
        ->withQueryString();
}
```

Gaya query yang dipakai:

- `select()` untuk membatasi kolom yang diambil;
- `with()` untuk eager loading relasi;
- `where()`, `whereIn()`, `when()`, `join()`, `orderBy()`, dan `latest()`;
- `paginate(10)->withQueryString()` untuk daftar halaman;
- `updateOrCreate()` untuk data one-to-one yang boleh dibuat atau diperbarui;
- `Model::create()`, `where()->update()`, dan `destroy()` untuk CRUD sederhana.

Untuk kode baru, letakkan query reusable di scope bernama deskriptif, misalnya `scopeCariDetail`, `scopeUrutanSiswa`, atau `scopeTampilKelas`. Hindari scope yang diam-diam melakukan `get()` atau `paginate()` bila scope tersebut kemungkinan perlu dirangkai oleh caller.

## 4. Naming Convention

### PHP

- Namespace dan class: PascalCase, misalnya `RekapController`, `BukuIndukRepository`.
- Method: camelCase, misalnya `formSiswaUpdate`, `getKelasByJurusan`.
- Variable: camelCase, misalnya `$tahunAjarId`, `$siswaId`, `$queryString`.
- Nama domain dan field mengikuti istilah aplikasi dalam Bahasa Indonesia.
- Nama tabel database umumnya singular custom, misalnya `siswa`, `ayah`, `ibu`, `kelas`, `tahun_ajar`.
- Foreign key memakai pola `<model>_id`, misalnya `siswa_id`, `kelas_id`, `tahun_ajar_id`.
- Boolean/status menggunakan istilah domain, misalnya `aktif`, `pindah`, `HADIR`, `TERLAMBAT`.

### Route

- URL menggunakan lowercase dan tanda hubung untuk kata majemuk, misalnya `/siswa-create`, `/pengaturan-spmb`.
- Nama route menggunakan dot notation berdasarkan modul dan aksi, misalnya `rekap.form.biodata.update` dan `spmb.tambah.pendaftar`.
- Route dikelompokkan memakai `Route::controller(...)->group()`.
- Route internal berada di middleware `all`.
- Route aksi memakai HTTP verb yang sesuai: `get`, `post`, `put`, dan `delete`.

Contoh:

```php
Route::controller(MasterController::class)->group(function () {
    Route::get('/siswa', 'siswa')->name('siswa');
    Route::post('/siswa-add', 'storeSiswa')->name('siswa.store');
    Route::put('/siswa-update/{siswa:id}', 'updateSiswa')->name('siswa.update');
    Route::delete('/siswa', 'deleteSiswa')->name('siswa.delete');
});
```

### View dan asset

- Nama file Blade lowercase dengan underscore atau tanda hubung sesuai modul yang sudah ada.
- Folder view mengikuti domain, misalnya `binduk/rekap/form`, `siswa`, atau `spmb`.
- Function section memakai `@section('title', ...)` dan `@section('content')`.
- JavaScript global berada di `public/js/script.js`.
- CSS utama berada di `public/css/style.css`, dengan file terpisah untuk auth dan landing page.

## 5. Blade dan UI

Layout internal:

```blade
@extends('layout.app')
@section('title', 'Judul Halaman')
@section('content')
    <div class="content">
        <div class="container">
            {{-- Breadcrumb --}}
            <div class="breadcrumb">
                Nama Modul
            </div>

            {{-- Isi halaman --}}
        </div>
    </div>
@endsection
```

Layout autentikasi/visitor memakai `layout.auth`.

Pola UI yang konsisten:

- Bootstrap 5 grid: `container`, `row`, `col-12`, `col-lg-*`.
- Card utama menggunakan `card card-content`.
- Tombol aksi menggunakan `btn btn-primary`, `btn-danger`, dan `btn-default-2`.
- Ikon menggunakan Bootstrap Icons dengan format `bi bi-*`.
- Form memakai `form-control`, `form-select`, dan kelas `is-invalid`.
- Error ditampilkan dengan directive `@error`.
- Data list memakai `@forelse` agar empty state tertangani.
- Table desktop dapat disembunyikan di mobile dengan `d-none d-lg-table d-md-table`.
- Modal Bootstrap dipakai untuk tambah, edit, preview lampiran, dan konfirmasi hapus.
- File gambar memakai `asset('storage/' . $path)` setelah disimpan pada disk public.

Pola form:

```blade
<form action="{{ route('rekap.form.biodata.update', $siswa->id) }}" method="post">
    @csrf
    @method('put')

    <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">

    <input type="text"
        class="form-control @error('nama_field') is-invalid @enderror"
        name="nama_field"
        value="{{ old('nama_field') ? old('nama_field') : $model?->nama_field }}">

    @error('nama_field')
        <div class="invalid-feedback">
            {{ ucwords($message) }}
        </div>
    @enderror
</form>
```

Untuk implementasi baru, lebih ringkas dan aman memakai:

```blade
value="{{ old('nama_field', $model?->nama_field) }}"
```

Jangan menaruh query database berat di Blade. Siapkan data di controller/service, lalu gunakan Blade untuk presentasi dan kondisi UI.

## 6. Validasi dan Pesan

Validasi form dilakukan dekat proses domain, biasanya di service:

- `required` untuk field wajib;
- `nullable` untuk field opsional;
- `unique` untuk NIK, NISN, NIS, nomor ijazah, dan data unik lain;
- `image|file|max:1024|mimes:jpg,jpeg,png` untuk gambar;
- `Rule::exists()` atau `Rule::in()` untuk validasi referensi/enum;
- pesan error Bahasa Indonesia;
- ukuran upload gambar/file biasanya maksimal 1024 KB.

Pesan umum yang sering dipakai:

```php
[
    'required' => ':attribute wajib diisi!',
    'max'      => ':attribute diisi maksimal :max karakter!',
    'min'      => ':attribute diisi minimal :min karakter!',
    'unique'   => ':attribute sudah digunakan!',
]
```

Untuk fitur baru:

- pastikan nama key pesan sama dengan rule sebenarnya;
- gunakan `exists` untuk foreign key penting;
- gunakan `nullable` bila file update boleh kosong;
- gunakan Form Request bila rule mulai panjang atau dipakai lintas endpoint;
- jangan mengandalkan validasi HTML saja.

## 7. File Upload, Export, PDF, dan Email

File upload umumnya diproses di service atau repository:

1. validasi file;
2. hapus file lama jika ada;
3. buat nama file custom bila perlu;
4. simpan ke folder domain;
5. simpan path ke database;
6. tampilkan melalui `asset('storage/' . $path)`.

Contoh pola nama file:

```php
$customName = $namaFile . '_' . time() . '.' . $inputFile->getClientOriginalExtension();
return $inputFile->storeAs('spmb/' . $folder, $customName, 'public');
```

Fitur Excel menggunakan class di `app/Imports` dan `app/Exports`, dengan concern seperti `ToModel`, `WithHeadingRow`, `WithValidation`, `FromView`, dan `ShouldAutoSize`.

PDF memakai mPDF dan view Blade khusus cetak, misalnya view di folder `cetak-*`.

Email memakai class di `app/Mail` dan controller/service untuk proses pengiriman.

Untuk fitur baru, gunakan `Storage` facade dan disk yang jelas. Pastikan file lama hanya dihapus setelah path lama benar-benar valid, dan pertimbangkan transaction/cleanup bila database gagal setelah upload.

## 8. Authentication, Authorization, dan Session

- User diakses melalui `Auth::user()` atau `Auth::user()?->`.
- Role yang terlihat: `admin`, `staff`, dan `wali_kelas`.
- Gate didefinisikan di `AppServiceProvider`:
  - `admin`
  - `staff_dan_admin`
  - `wali_kelas`
- Modul internal memakai middleware `all`.
- Tahun ajar dan semester aktif disimpan di session, misalnya `session('tahun_ajar_id')`, `session('tahun_ajar')`, dan `session('semester')`.
- Notifikasi aksi memakai flash session, misalnya `with('sukses', 'Data berhasil diubah!')`.

Untuk implementasi baru, gunakan middleware/Gate/Policy untuk izin akses dan jangan hanya menyembunyikan tombol di Blade. Pastikan endpoint juga terlindungi.

## 9. JavaScript dan Frontend Behavior

- jQuery dipakai untuk interaksi halaman dan request AJAX.
- Bootstrap JS, Popper, Toastr, dan Dropify dimuat dari asset `public`.
- Script khusus halaman ditempatkan melalui:

```blade
@push('scripts')
    <script>
        // Script khusus halaman
    </script>
@endpush
```

- Endpoint JSON dibuat di controller dengan `response()->json(...)`.
- Fungsi UI memakai nama camelCase yang deskriptif, misalnya `addBeasiswa`, `editBeasiswa`, `deleteBeasiswa`, dan `viewLampiran`.
- Konfirmasi hapus dan modal edit dilakukan melalui Bootstrap modal.

Untuk implementasi baru:

- kirim data memakai route bernama bila memungkinkan;
- tangani status error AJAX dan tampilkan pesan yang Saku dipahami user;
- hindari inline `onclick` pada fitur baru jika handler dapat didaftarkan dari script halaman;
- tetap ikuti pola lama bila harus menyentuh halaman legacy agar perilakunya tidak pecah.

## 10. Database dan Migration

- Migration berada di `database/migrations` dengan timestamp Laravel.
- Tabel aplikasi memakai nama Bahasa Indonesia/singular custom.
- Relasi menggunakan foreign key dengan suffix `_id`.
- Perubahan schema baru dibuat sebagai migration tambahan, bukan mengubah migration lama yang sudah pernah dijalankan.
- Data awal/master dapat ditempatkan di `database/seeders`.

Untuk perubahan multi-tabel:

- gunakan foreign key dan index yang sesuai;
- pertimbangkan nullable/default untuk data lama;
- buat migration `up()` dan `down()` yang dapat dipahami;
- uji migration pada database kosong dan database yang sudah berisi data.

## 11. Testing dan Quality Check

Test saat ini memakai PHPUnit 11 dan memiliki Unit/Feature test. Contoh gaya test domain:

- nama test Bahasa Indonesia yang menjelaskan perilaku;
- data boundary diuji dengan `DataProvider`;
- waktu diuji menggunakan Carbon dengan timezone `Asia/Jakarta`;
- assertion menggunakan `assertSame()`.

Contoh pola penamaan:

```php
public function test_status_presensi_mengikuti_jam_masuk_dan_toleransi(): void
```

Perintah utama:

```bash
php artisan test
php artisan test tests/Unit/AbsensiStatusTest.php
vendor/bin/pint
npm run build
npm run dev
```

Untuk fitur baru, minimal tambahkan test untuk:

- alur berhasil;
- validasi gagal;
- batas waktu/status bila ada;
- authorization untuk role yang berbeda;
- upload atau perubahan data penting;
- empty state bila halaman menampilkan daftar.

## 12. Checklist Implementasi Fitur Baru

1. Tentukan modul dan route yang memiliki fitur tersebut.
2. Buat atau gunakan model dan relasi yang sesuai.
3. Buat migration tambahan bila schema berubah.
4. Tambahkan scope model untuk query yang reusable.
5. Letakkan validasi dan proses bisnis di service.
6. Gunakan repository bila proses/query cukup kompleks atau digunakan lintas service.
7. Tambahkan route bernama dengan HTTP verb yang benar.
8. Buat view di folder domain dan extend layout yang sesuai.
9. Ikuti pola Bootstrap, card, modal, form error, dan empty state yang sudah ada.
10. Tambahkan route authorization/Gate/Policy untuk endpoint.
11. Tambahkan activity log untuk operasi administratif penting.
12. Tambahkan test yang paling dekat dengan perilaku baru.
13. Jalankan test terarah, Pint, dan build Vite.

## 13. Hal yang Perlu Diperhatikan dari Repo Lama

Pola berikut terlihat di kode saat ini, tetapi sebaiknya tidak diperbanyak tanpa alasan:

- beberapa query dan validasi masih memakai `request()` global;
- sebagian controller melakukan query langsung dan redirect dengan query string manual;
- terdapat komentar panjang dan komentar debug yang sebaiknya dibersihkan setelah fitur stabil;
- terdapat penamaan yang belum seragam seperti `SpmbRepository` sebagai property berhuruf besar;
- beberapa view memakai inline style, inline SVG, dan inline `onclick`;
- `resources/css/app.css` dan `resources/js/app.js` adalah entry Vite, sementara banyak asset aktual berada di `public`;
- test coverage masih terbatas, terutama pada feature test dan authorization;
- beberapa typo historis pada key error/property jangan dijadikan pola baru;
- proses yang membuat beberapa record sebaiknya memakai `DB::transaction()` untuk menjaga konsistensi data.

Saat memperbaiki kode lama, ubah hanya bagian yang berkaitan dengan task dan jaga kompatibilitas route, nama field, serta query string yang sedang dipakai halaman.

## 14. Prinsip Singkat

- Gunakan Bahasa Indonesia untuk nama domain dan teks UI.
- Controller mengarahkan, service memproses, model/repository mengambil data.
- Blade fokus pada presentasi.
- Route diberi nama dan dikelompokkan per controller/modul.
- Validasi selalu dilakukan di server.
- File lama dibersihkan saat file baru menggantikannya.
- Status, role, dan tahun ajar harus konsisten dengan domain aplikasi.
- Pertahankan Bootstrap 5 dan Bootstrap Icons untuk UI internal.
- Uji perilaku penting, terutama validasi, status, authorization, dan batas waktu.
- Sebelum merge, jalankan test terarah, formatter, dan build asset.
