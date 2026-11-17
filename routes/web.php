<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KartuIdentitasController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\MutasiController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PindahKelasController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\SpmbController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\SiswaFotoZipController;
use App\Http\Controllers\TransisiSemester;
use App\Http\Controllers\WaliKelasController;
use Illuminate\Support\Facades\Route;

//======== 404 Not Found ========//
Route::get('/404', function () {
    return view('404');
});

Route::get('/', [AuthController::class, 'index'])->middleware(['login']);
Route::get('/login', [AuthController::class, 'login'])->middleware(['login'])->name('login');

// Halaman konfirmasi perlu bisa membuka URL aktivasi secara langsung (GET).
// Perubahan status template tetap hanya lewat POST admin di bawah.
// Route::get('/kartu-identitas/template/{templateId}/aktifkan', [KartuIdentitasController::class, 'confirmActivateTemplate'])
//     ->whereNumber('templateId')
//     ->name('kartu-identitas.template.activate.confirm');

//======== Index ========//
Route::get('/resetPassword', [AuthController::class, 'resetPassword'])->name('resetPassword');
Route::post('/resetPassword', [AuthController::class, 'validasiPassword'])->name('prosesReset');

//======== Validasi User ========//
Route::post('/auth', [AuthController::class, 'validasi']);

//======== Logout ========//
Route::post('/logout', [AuthController::class, 'logout']);


//======== Absensi ========//
Route::get('/absensi/scan', [AbsensiController::class, 'scanView'])
    ->middleware('can:absensi.scan')
    ->name('absensi.scan.view');
Route::get('/kartu-pelajar/{siswa}', [KartuIdentitasController::class, 'studentDetail'])
    ->whereUuid('siswa')
    ->middleware('signed')
    ->name('kartu-identitas.siswa.detail');

//======== SPMB For Visitor ========//
Route::controller(SpmbController::class)->group(function () {
    Route::post('/proses-daftar-cmb', 'prosesDaftarCmb')->name('spmb.daftar.cmb');
    Route::get('/form-daftar', 'formDaftar')->name('form.daftar');
    Route::get('/halaman-daftar', 'halamanDaftar')->name('halaman.daftar');
    Route::get('/bukti-pendaftaran/{pendaftarId}', 'buktiPendaftaran')->name('bukti.pendaftaran');
    Route::get('/halaman-konfirmasi/{pendaftarId}', 'halamanKonfirmasi')->name('halaman.konfirmasi');
    Route::get('/alur-pendaftaran', 'alurPendaftaran')->name('alur.pendaftaran');
    Route::get('/syarat-pendaftaran', 'syaratPendaftaran')->name('syarat.pendaftaran');
    Route::get('/unduh-brosur', 'unduhBrosur')->name('unduh.brosur');
    Route::get('/cek-status', 'cekStatus')->name('cek.status');
    Route::post('/cek-status', 'cekStatus')->name('cek.status');
    Route::get('/validasiKelas/{tingkatId}', 'validasiKelas');
});

/** 
 * Admin & User => Hanya admin dan user yang Saku mengakses halaman ini
 */
Route::middleware(['all'])->group(function () {
    //======== Beranda ========//
    Route::get('/beranda', [MasterController::class, 'index'])->name('beranda');

    //======== SPMB ========//
    Route::controller(SpmbController::class)->middleware('can:staff_dan_admin')->group(function () {
        Route::get('/pendaftar', 'pendaftar')->name('pendaftar');
        Route::get('/pengaturan-spmb/{side?}', 'pengaturanSpmb')->name('spmb.pengaturan');
        Route::post('/add-syarat', 'addSyarat')->name('spmb.add.syarat');
        Route::post('/add-alur', 'addAlur')->name('spmb.add.alur');
        Route::post('/add-sosmed', 'addSosmed')->name('spmb.add.sosmed');
        Route::put('/edit-sosmed', 'editSosmed')->name('spmb.edit.sosmed');
        Route::delete('/delete-sosmed', 'deleteSosmed')->name('spmb.delete.sosmed');
        Route::put('/update-umum', 'updateUmum')->name('spmb.update.umum');
        Route::delete('/hapus-gambar-spmb', 'hapusGambarSpmb')->name('spmb.hapus.gambar');
        Route::post('/ubah-status-pendaftar', 'ubahStatusPendaftar')->name('spmb.ubah.status');
        Route::get('/detail-pendaftar/{id}/{menu}', 'detailPendaftar')->name('spmb.detail.pendaftar');
        Route::get('/tambah-pendaftar', 'tambahPendaftar')->name('spmb.tambah.pendaftar');
        Route::post('/tambah-pendaftar', 'simpanPendaftar')->name('spmb.simpan.pendaftar');
        Route::get('/ubah-pendaftar/{pendaftarId}', 'ubahPendaftar')->name('spmb.ubah.pendaftar');
        Route::put('/ubah-pendaftar/{pendaftarId}', 'perbaruiPendaftar')->name('spmb.perbarui.pendaftar');
        Route::delete('/hapus-pendaftar', 'hapusPendaftar')->name('spmb.hapus.pendaftar');
        Route::post('/cabut-berkas', 'cabutBerkas')->name('spmb.cabut.berkas');
        Route::get('/getDataCabutBerkas/{pendaftarId}', 'getCabutBerkas');
        Route::get('/export-pendaftar', 'exportPendaftar')->name('export.pendaftar');
        Route::post('/migrasi', 'spmbMigrasi')->name('spmb.migrasi');
        Route::get('/spmb-rekap', 'rekapSpmb')->name('spmb.rekap');
        Route::post('/terima-beberapa', 'terimaBeberapa')->name('spmb.terima.beberapa');
    });

    Route::controller(StudentAttendanceController::class)->middleware('can:staff_dan_admin')->group(function () {
        Route::get('/siswa-tautkan-kartu/{siswa}/{trigger}',  'linkCardView')->name('siswa.kartu');
        Route::post('/siswa-proses-tautkan-kartu', 'linkCard')->name('siswa.kartu.store');
        Route::delete('/siswa-delete-kartu/{siswa}', 'resetCard')->whereUuid('siswa')->name('siswa.kartu.reset');
        Route::put('/siswa-koreksi-absensi/{id}', 'correctAttendance')->name('siswa.absensi.koreksi');
    });

    Route::controller(MasterController::class)->group(function () {
        //======== Menu Siswa ========//
        Route::get('/siswa', 'siswa')->middleware('can:siswa.read')->name('siswa');
        Route::get('/getSiswa/{id}', 'getSiswa')->middleware('can:siswa.read');
        Route::get('/siswa-create', 'createSiswa')->middleware('can:siswa.write')->name('siswa.add');
        Route::post('/siswa-add', 'storeSiswa')->middleware('can:siswa.write')->name('siswa.store');
        Route::get('/siswa-edit/{id}', 'editSiswa')->middleware('can:siswa.write')->name('siswa.edit');
        Route::put('/siswa-update/{siswa:id}', 'updateSiswa')->middleware('can:siswa.write')->name('siswa.update');
        Route::delete('/siswa', 'deleteSiswa')->middleware('can:siswa.write')->name('siswa.delete');
        Route::delete('/siswa-some-delete', 'hapusSiswaTerpilih')->middleware('can:siswa.write')->name('siswa.someDelete');
        Route::get('/export-siswa', 'exportSiswa')->middleware('can:siswa.read')->name('export.siswa');
        Route::post('/import-siswa', 'importSiswa')->middleware('can:siswa.write')->name('import.siswa');
        Route::post('/siswa/foto-import-zip', [SiswaFotoZipController::class, 'store'])->middleware('can:siswa.write')->name('siswa.foto.import.zip');
        Route::get('/Sample-Import-Data-Siswa', 'templateImport')->middleware('can:siswa.write')->name('import.sample');
        Route::get('/getKelasByJurusan/{jurusanId}/{tingkatId}', 'getKelasByJurusan')->middleware('can:siswa.write');
        Route::get('/getOneKelasByJurusan/{jurusanId}/{tingkatId}', 'getOneKelasByJurusan')->middleware('can:siswa.write');
        Route::get('/getKelasByTahunAjar/{tahunAjarId}', 'getKelasByTahunAjar')->middleware('can:siswa.write');

        //======== Menu Profil ========// 
        Route::get('/profil', 'profilView')->name('profil');
        Route::put('/profil', 'updateProfil')->name('profil.update');
        Route::post('/ubah-foto', 'ubahFotoProfil')->name('ubahFotoProfil');
        Route::post('/respas', 'resetPassProf')->name('profil.respas');
    });

    Route::controller(RekapController::class)->middleware('can:rekap.read')->group(function () {
        //======== Menu Rekap Binduk ========//
        Route::get('/rekap', 'rekap')->name('rekap');

        // Siswa
        Route::get('alumni/form-data/siswa/{id}', 'formSiswa')->name('alumni.form.siswa');
        Route::get('/form-data/siswa/{id}', 'formSiswa')->name('rekap.form.siswa');
        Route::put('/form-data/siswa/{siswa:id}', 'formSiswaUpdate')->middleware('can:rekap.write')->name('rekap.form.siswa.update');

        // Orang tua
        Route::get('alumni/form-data/ortu/{id}', 'formOrtu')->name('alumni.form.ortu');
        Route::get('/form-data/ortu/{id}', 'formOrtu')->name('rekap.form.ortu');
        Route::put('/form-data/ortu/{id}', 'formOrtuUpdate')->middleware('can:rekap.write')->name('rekap.form.ortu.update');

        // Biodata
        Route::get('alumni/form-data/biodata/{id}', 'formBiodata')->name('alumni.form.biodata');
        Route::get('/form-data/biodata/{id}', 'formBiodata')->name('rekap.form.biodata');
        Route::put('/form-data/biodata/{id}', 'formBiodataUpdate')->middleware('can:rekap.write')->name('rekap.form.biodata.update');

        // Wali murid
        Route::get('alumni/form-data/walmur/{id}', 'formWalmur')->name('alumni.form.walmur');
        Route::get('/form-data/walmur/{id}', 'formWalmur')->name('rekap.form.walmur');
        Route::put('/form-data/walmur/{id}', 'formWalmurUpdate')->middleware('can:rekap.write')->name('rekap.form.walmur.update');

        // Riwayat pendidikan
        Route::get('alumni/form-data/pendidikan/{id}', 'formPendidikan')->name('alumni.form.pendidikan');
        Route::get('/form-data/pendidikan/{id}', 'formPendidikan')->name('rekap.form.pendidikan');
        Route::put('/form-data/pendidikan-update/{id}', 'formPendidikanUpdate')->middleware('can:rekap.write')->name('rekap.form.pendidikan.update');

        // Kesehatan
        Route::get('alumni/form-data/kesehatan/{id}', 'formKesehatan')->name('alumni.form.kesehatan');
        Route::get('/form-data/kesehatan/{id}', 'formKesehatan')->name('rekap.form.kesehatan');
        Route::put('/form-data/kesehatan/{id}', 'formKesehatanUpdate')->middleware('can:rekap.write')->name('rekap.form.kesehatan.update');

        // Minat
        Route::get('alumni/form-data/minat/{id}', 'formMinat')->name('alumni.form.minat');
        Route::get('/form-data/minat/{id}', 'formMinat')->name('rekap.form.minat');
        Route::put('/form-data/minat/{id}', 'formMinatUpdate')->middleware('can:rekap.write')->name('rekap.form.minat.update');

        // Beasiswa
        Route::get('alumni/form-data/beasiswa/{id}', 'formBeasiswa')->name('alumni.form.beasiswa');
        Route::get('/form-data/beasiswa/{id}', 'formBeasiswa')->name('rekap.form.beasiswa');
        Route::post('/form-data/beasiswa/{id}', 'formBeasiswaCreate')->middleware('can:rekap.write')->name('rekap.form.beasiswa.create');
        Route::get('/getBeasiswa/{id}', 'getBeasiswa');
        Route::put('/form-data/beasiswa/{id}', 'formBeasiswaUpdate')->middleware('can:rekap.write')->name('rekap.form.beasiswa.update');
        Route::delete('/form-data/beasiswa/{id}', 'formBeasiswaDelete')->middleware('can:rekap.write')->name('rekap.form.beasiswa.delete');

        // Prestasi
        Route::get('alumni/form-data/prestasi/{id}', 'formPrestasi')->name('alumni.form.prestasi');
        Route::get('/form-data/prestasi/{id}', 'formPrestasi')->name('rekap.form.prestasi');
        Route::post('/form-data/prestasi/{id}', 'formPrestasiCreate')->middleware('can:rekap.write')->name('rekap.form.prestasi.create');
        Route::get('/getPrestasi/{id}', 'getPrestasi');
        Route::put('/form-data/prestasi/{id}', 'formPrestasiUpdate')->middleware('can:rekap.write')->name('rekap.form.prestasi.update');
        Route::delete('/form-data/prestasi/{id}', 'formPrestasiDelete')->middleware('can:rekap.write')->name('rekap.form.prestasi.delete');

        // Lampiran
        Route::get('alumni/form-data/lampiran/{id}', 'formLampiran')->name('alumni.form.lampiran');
        Route::get('/form-data/lampiran/{id}', 'formLampiran')->name('rekap.form.lampiran');
        Route::post('/form-data/lampiran/{id}', 'formLampiranCreate')->middleware('can:rekap.write')->name('rekap.form.lampiran.create');
        Route::get('/getLampiran/{id}', 'getLampiran');
        Route::put('/form-data/lampiran/{id}', 'formLampiranUpdate')->middleware('can:rekap.write')->name('rekap.form.lampiran.update');
        Route::delete('/form-data/lampiran/{id}', 'formLampiranDelete')->middleware('can:rekap.write')->name('rekap.form.lampiran.delete');

        // Nilai
        Route::get('alumni/form-data/nilai/{id}', 'formNilai')->name('alumni.form.nilai');
        Route::get('/form-data/nilai/{id}', 'formNilai')->name('rekap.form.nilai');
        Route::put('/form-data/nilai/{id}', 'formNilaiUpdate')->middleware('can:rekap.write')->name('rekap.form.nilai.update');

        // Cetak buku induk
        Route::get('/dataSiswa/{siswa:id}', 'cetakDataSiswa')->name('rekap.cetak.data.siswa');
    });

    Route::controller(MutasiController::class)->middleware('can:staff_dan_admin')->group(function () {

        //======== Menu Mutasi Siswa ========//
        Route::get('/mutasi', 'mutasi')->name('mutasi');
        Route::get('/mutasi-siswa/{id}', 'mutasiSiswa')->name('mutasi.siswa');
        Route::put('/mutasi-siswa-update/{id}', 'mutasiSiswaUpdate')->name('mutasi.siswa.update');
        Route::get('/cetak-mutasi/{siswa:id}', 'cetakMutasiSiswa')->name('cetak.mutasi');
    });

    Route::controller(AlumniController::class)->middleware('can:staff_dan_admin')->group(function () {
        //======== Menu Alumni ========//
        Route::get('/alumni', 'index')->name('alumni');
    });

    Route::controller(PindahKelasController::class)->middleware('can:siswa.write')->group(function () {
        //======== Menu Pindah Kelas dan Lulus ========//
        Route::post('/pindah-kelas', 'prosesPindahKelas')->name('siswa.pindahKelas');
    });

    Route::controller(ProcessController::class)->middleware('can:admin')->group(function () {

        //======== Menu Naik Kelas dan Lulus ========//
        Route::get('/naik-kelas', 'naikKelas')->name('siswa.naik');
        Route::post('/naik-kelas', 'prosesNaikKelas')->name('naik.kelas');
        Route::post('/lulus-sekolah', 'prosesLulusSekolah')->name('lulus');

        //======== Menu Activity Log ========//
        Route::get('/activity-log', 'activityLog')->name('log');
    });

    Route::controller(AbsensiController::class)->group(function () {
        Route::get('/master-absensi', 'masterAbsensi')->middleware('can:absensi.read')->name('master.absensi');
        Route::get('/master-absensi-add/{siswaId}', 'masterAdd')->middleware('can:staff_dan_admin')->name('master.add');
        Route::get('/atur-absensi', 'aturAbsensi')->middleware('can:absensi.read')->name('atur.absensi');
        Route::put('/atur-absensi', 'updateAturAbsensi')->middleware('admin')->name('atur.absensi.update');
        Route::post('/hari-libur', 'simpanHariLibur')->middleware('admin')->name('hari.libur.store');
        Route::delete('/hari-libur/{hariLibur}', 'hapusHariLibur')->middleware('admin')->name('hari.libur.destroy');
        Route::get('/ketidakhadiran', 'ketidakhadiran')->middleware('can:absensi.read')->name('ketidakhadiran');
        Route::post('/ketidakhadiran', 'simpanKetidakhadiran')->middleware('admin')->name('ketidakhadiran.store');
        Route::delete('/ketidakhadiran/{ketidakhadiran}', 'hapusKetidakhadiran')->middleware('admin')->name('ketidakhadiran.destroy');
        Route::post('/ketidakhadiran/siswa', [App\Http\Controllers\StudentAttendanceController::class, 'saveLeave'])->middleware('admin')->name('ketidakhadiran.siswa.store');
        Route::delete('/ketidakhadiran/siswa/{ketidakhadiranSiswa}', [App\Http\Controllers\StudentAttendanceController::class, 'deleteLeave'])->middleware('admin')->name('ketidakhadiran.siswa.destroy');
        Route::get('/laporan-absensi', 'laporan')->middleware('can:absensi.read')->name('laporan.absensi');
        Route::get('/laporan-absensi/export', 'exportLaporan')->middleware('can:absensi.read')->name('laporan.absensi.export');
        Route::post('/absensi/scan', 'scan')->middleware('can:absensi.scan')->name('absensi.scan');
    });

    Route::controller(KartuIdentitasController::class)->middleware('can:staff_dan_admin')->group(function () {
        Route::get('/kartu-identitas', 'index')->name('kartu-identitas');
        Route::post('/kartu-identitas/pratinjau', 'preview')->name('kartu-identitas.preview');
        Route::post('/kartu-identitas/generate-all', 'generateAll')->name('kartu-identitas.generate-all');
        Route::get('/kartu-identitas/template/{template}/gambar', 'image')->name('kartu-identitas.template.image');
        Route::get('/kartu-identitas/foto/{jenis}/{id}', 'photo')->name('kartu-identitas.foto');
        Route::get('/kartu-identitas/template', 'templates')->name('kartu-identitas.templates');
        Route::post('/kartu-identitas/template/{jenis}/{sisi}', 'uploadTemplate')->name('kartu-identitas.template.upload');
        Route::get('/kartu-identitas/template/{template}/pratinjau', 'previewTemplate')->name('kartu-identitas.template.preview');
        Route::post('/kartu-identitas/aktifkan-template/{templateId}', 'activateTemplate')->name('kartu-identitas.template.aktifkan');
        Route::delete('/kartu-identitas/template/{template}', 'deletePendingTemplate')->name('kartu-identitas.template.delete');
    });
});


/** 
 * Admin => Hanya admin yang Saku mengakses halaman ini
 */
Route::middleware(['admin'])->group(function () {

    Route::controller(PenggunaController::class)->group(function () {

        //======== Menu User ========//
        Route::get('/user/{role?}', 'userView')->name('user');
        Route::get('/user-add', 'createUser')->name('user.add');
        Route::post('/user-add-guru', 'generateUserGuru')->name('user.add.guru');
        Route::post('/user-respas-guru', 'userRespasGuru')->name('user.reset.pass.guru');
        Route::post('/user-add-staff', 'generateUserStaff')->name('user.add.staff');
        Route::post('/user-respas-staff', 'userRespasStaff')->name('user.reset.pass.staff');
        Route::post('/user-add-kepsek', 'generateUserKepsek')->name('user.add.kepsek');
        Route::post('/user-respas-kepsek', 'userRespasKepsek')->name('user.reset.pass.kepsek');
        Route::post('/user-store', 'storeUser')->name('user.store');
        Route::get('/user-edit/{user:id}', 'editUser')->name('user.edit');
        Route::put('/user-edit/{user:id}', 'updateUser')->name('user.update');
        Route::delete('/user-delete', 'deleteUser')->name('user.delete');
        Route::post('/user/status/{id}/{role?}', 'statusUser')->name('user.status');
        Route::post('/user/reset/{role?}', 'resetPassUser')->name('user.reset');
    });

    Route::controller(PengaturanController::class)->group(function () {

        //======== Menu Setting ========// 
        Route::get('/setting', 'settingView')->name('setting');
        Route::post('/setting', 'ubahSetting')->name('setting.update');

        //======== Menu Tahun Akademik ========//
        // Route::get('/buat-tahun-akademik', 'buatTahunAkademik')->name('buat.tahun.akademik');
        // Route::get('/aktifasi-tahun-akademik/{id}', 'aktifasiTahunAkademik')->name('aktifasi.tahun.akademik');
        // Route::post('/tambah-tahun-akademik', 'tambahTahunAkademik')->name('tambah.tahun.akademik');
        // Route::put('/ubah-tahun-akademik', 'ubahTahunAkademik')->name('ubah.tahun.akademik');
        // Route::delete('/hapus-tahun-akademik', 'hapusTahunAkademik')->name('hapus.tahun.akademik');
    });

    Route::controller(GuruController::class)->group(function () {
        //======== Menu Tenaga Pendidik ========//
        Route::get('/tenaga-pendidik', 'tenagaPendidik')->name('tenaga.pendidik');
        Route::get('/tambah-tenaga-pendidik', 'tambahTenagaPendidik')->name('tambah.tenaga.pendidik');
        Route::get('/edit-tenaga-pendidik/{id}/edit', 'editTenagaPendidik')->name('edit.tenaga.pendidik');
        Route::post('/tenaga-pendidik-aktivasi', 'aktivasiTenagaPendidik')->name('tenaga.pendidik.aktivasi');

        Route::get('/tenaga-pendidik-tautkan-kartu/{id}/{trigger}', 'tautkanKartu')->name('tenaga.pendidik.tautkan.kartu');
        Route::post('/tenaga-pendidik-reset-kartu/{id}', 'resetKartu')->name('tenaga.pendidik.reset.kartu');
        Route::post('/proses-tautkan-kartu', 'prosesTautkanKartu')->name('tenaga.pendidik.proses.tautkan');

        Route::post('/tenaga-pendidik-add', 'storeTenagaPendidik')->name('tenaga.pendidik.store');
        Route::put('/tenaga-pendidik-update/{id}', 'updateTenagaPendidik')->name('tenaga.pendidik.update');
        Route::delete('/tenaga-pendidik-delete', 'delTenagaPendidik')->name('tenaga.pendidik.destroy');
        Route::delete('/tenaga-pendidik-delete-selected', 'hapusPegawaiTerpilih')->name('tenaga.pendidik.delete.selected');
        Route::post('/tenaga-pendidik-aktivasi-selected', 'aktivasiTenagaPendidikTerpilih')->name('tenaga.pendidik.terpilih.aktivasi');
        Route::get('/tenaga-pendidik-export', 'exportTenagaPendidik')->name('tenaga.pendidik.export');
        Route::get('/tenaga-pendidik-template-import', 'templateImportPegawai')->name('tenaga.pendidik.template.import');
        Route::post('/tenaga-pendidik-import', 'importTenagaPendidik')->name('tenaga.pendidik.import');

        //======== Menu Status Kepegawaian ========//
        Route::get('/status-kepegawaian', 'statusKepegawaian')->name('status.kepegawaian');
        Route::post('/stapeg-add', 'addStaPeg')->name('stapeg.add');
        Route::put('/stapeg-edit', 'editStaPeg')->name('stapeg.edit');
        Route::delete('/stapeg-delete', 'delStaPeg')->name('stapeg.delete');
        Route::get('/getStatusPegawaiDalamGuru/{id}', 'getStatusPegawaiDalamGuru');
        Route::get('/cekValidasiGuru/{id}', 'cekValidasiGuru');
        Route::get('/cekValidasiGuruArr/{arrId}', 'cekValidasiGuruArr');

        Route::get('/jabatan', 'jabatan')->name('jabatan');
        Route::post('/jabatan-add', 'addJabatan')->name('jabatan.add');
        Route::put('/jabatan-edit', 'editJabatan')->name('jabatan.edit');
        Route::delete('/jabatan-delete', 'delJabatan')->name('jabatan.delete');
        Route::get('/getJabatanDalamGuru/{id}', 'getJabatanDalamGuru');
    });

    Route::controller(KelasController::class)->group(function () {
        //======== Menu tingkat ========//
        Route::get('/tingkat', 'tingkat')->name('tingkat');
        Route::post('/tingkat-add', 'addtingkat')->name('tingkat.add');
        Route::put('/tingkat-update', 'updatetingkat')->name('tingkat.edit');
        Route::delete('/tingkat-delete', 'deletetingkat')->name('tingkat.delete');
        Route::get('/getKelasDalamTingkat/{id}', 'getKelasDalamTingkat');

        //======== Menu Kelas ========//
        Route::get('/kelas', 'kelas')->name('kelas');
        Route::post('/kelas-add', 'addKelas')->name('kelas.add');
        Route::put('/kelas-update', 'updateKelas')->name('kelas.edit');
        Route::delete('/kelas-delete', 'deleteKelas')->name('kelas.delete');
        Route::get('/getSiswaDalamKelas/{id}', 'getSiswaDalamKelas');

        //======== Menu Jurusan ========//
        Route::get('/jurusan', 'jurusan')->name('jurusan');
        Route::post('/jurusan-add', 'addJurusan')->name('jurusan.add');
        Route::put('/jurusan-update', 'updateJurusan')->name('jurusan.edit');
        Route::delete('/jurusan-delete', 'deleteJurusan')->name('jurusan.delete');
        Route::get('/getSiswaDalamJurusan/{id}', 'getSiswaDalamJurusan');
    });

    Route::controller(MapelController::class)->group(function () {

        //======== Menu Kelompok Mapel ========//
        Route::post('/kelompok-mapel-add', 'addKelompokMapel')->name('kelma.add');
        Route::put('/kelompok-mapel-edit', 'editKelompokMapel')->name('kelma.edit');
        Route::delete('/kelompok-mapel-delete', 'delKelompokMapel')->name('kelma.delete');

        //======== Menu Mapel ========//
        Route::get('/mapel', 'mapel')->name('mapel');
        Route::post('/mapel-add', 'addMapel')->name('mapel.add');
        Route::put('/mapel-edit', 'editMapel')->name('mapel.edit');
        Route::put('/mapel-edit-kkm', 'editMapelKkm')->name('mapel.edit.kkm');
        Route::delete('/mapel-delete', 'delMapel')->name('mapel.delete');
        Route::get('/getKelmaDalamKurlumMapel/{id}', 'getKelmaDalamKurlumMapel');
        Route::get('/getMapelDalamKurlumMapel/{id}', 'getMapelDalamKurlumMapel');
    });

    Route::controller(KurikulumController::class)->group(function () {

        //======== Menu Kurikulum ========//
        Route::get('/kurikulum', 'kurikulum')->name('kurikulum');
        Route::post('/kurikulum-add', 'addKurlum')->name('kurikulum.add');
        Route::put('/kurikulum-edit', 'editKurlum')->name('kurikulum.edit');
        Route::delete('/kurikulum-delete', 'delKurlum')->name('kurikulum.delete');
        Route::get('/kurikulum-mapel/{id}', 'kurikulumMapel')->name('kurikulum.mapel');
        Route::post('/kurikulum-mapel-add', 'addKurMapel')->name('kurikulum.mapel.add');
        Route::delete('/kurikulum-mapel-delete', 'delKurMapel')->name('kurikulum.mapel.delete');
        Route::post('/ubah-urutan-mapel', 'ubahUrutanMapel')->name('ubahUrutanMapel');
        Route::get('/cek-kurikulum/{id}', 'cekKurikulum');
        Route::get('/cekMapelPadaNilaiSiswa/{id}', 'cekMapelPadaNilaiSiswa');
    });

    Route::controller(WaliKelasController::class)->group(function () {

        //======== Menu Wali Kelas ========//
        Route::get('/walkel', 'walkel')->name('walkel');
        Route::post('/walkel-update', 'updateWalkel')->name('walkel.update');
    });

    Route::controller(AdminController::class)->group(function () {

        //======== Menu Tahun Ajar ========//
        Route::get('/tahun-ajar', 'tahunAjar')->name('tahun.ajar');
        Route::get('/get-tahun-ajar/{id}', 'getTahunAjar');
        Route::post('/tahun-ajar-add', 'addTahunAjar')->name('tahun.ajar.add');
        Route::put('/tahun-ajar-edit', 'editTahunAjar')->name('tahun.ajar.edit');
        Route::delete('/tahun-ajar-delete', 'delTahunAjar')->name('tahun.ajar.delete');
        Route::post('/aktifasi-tahun-ajar/{id}', 'aktifasiTahunAjar')->name('aktifasi.tahun.ajar');
        Route::get('/getDataTahunAjar/{periode}', 'getDataTahunAjar');
        Route::put('/tahun-ajar/kurikulum', 'ubahKurikulumTajar')->name('tahun.ajar.kurikulum');


        //======== Menu Agama ========//
        Route::get('/agama', 'agama')->name('agama');
        Route::post('/agama-add', 'addAgama')->name('agama.add');
        Route::put('/agama-edit', 'editAgama')->name('agama.edit');
        Route::delete('/agama-delete', 'deleteAgama')->name('agama.delete');
        Route::get('/cekAgama/{id}', 'cekAgama');

        //======== Menu Pendidikan ========//
        Route::get('/pendidikan', 'pendidikan')->name('pendidikan');
        Route::post('/pendidikan-add', 'addPendidikan')->name('pendidikan.add');
        Route::put('/pendidikan-edit', 'editPendidikan')->name('pendidikan.edit');
        Route::delete('/pendidikan-delete', 'delPendidikan')->name('pendidikan.delete');
        Route::get('/cekPendidikan/{id}', 'cekPendidikan');

        //======== Menu Semester ========//
        Route::get('/semester', 'semester')->name('semester');
        Route::put('/semester-update', 'updateSemester')->name('semester.update');
        Route::delete('/semester-delete', 'delSemester')->name('semester.delete');

        //======== Menu Lampiran ========//
        Route::get('/lampiran', 'lampiran')->name('lampiran');
        Route::post('/lampiran-add', 'addLampiran')->name('lampiran.add');
        Route::put('/lampiran-edit', 'editLampiran')->name('lampiran.edit');
        Route::delete('/lampiran-delete', 'delLampiran')->name('lampiran.delete');
        Route::get('/cekLampiran/{id}', 'cekLampiran');
    });

    Route::controller(TransisiSemester::class)->group(function () {
        Route::get('/transisi-semester', 'index')->name('transisi.semester');
        Route::post('/proses-transisi-semester/{id}', 'prosesTransisi')->whereUuid('id')->name('proses.transisi.semester');
    });
});
