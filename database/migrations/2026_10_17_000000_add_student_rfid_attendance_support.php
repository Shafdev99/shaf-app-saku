<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addedCardId = ! Schema::hasColumn('siswa', 'card_id');
        $addedNoWa = ! Schema::hasColumn('siswa', 'no_wa_ortu');
        if ($addedCardId || $addedNoWa) {
            Schema::table('siswa', function (Blueprint $table) use ($addedCardId, $addedNoWa) {
                if ($addedCardId) {
                    $table->string('card_id')->nullable()->unique('siswa_card_id_unique');
                }
                if ($addedNoWa) {
                    $table->string('no_wa_ortu', 32)->nullable();
                }
            });
        }
        $settingsColumns = [
            'jam_masuk_siswa' => ! Schema::hasColumn('pengaturan_absensi', 'jam_masuk_siswa'),
            'batas_scan_masuk_siswa' => ! Schema::hasColumn('pengaturan_absensi', 'batas_scan_masuk_siswa'),
            'jam_pulang_siswa' => ! Schema::hasColumn('pengaturan_absensi', 'jam_pulang_siswa'),
            'batas_jam_pulang_siswa' => ! Schema::hasColumn('pengaturan_absensi', 'batas_jam_pulang_siswa'),
            'waktu_notifikasi_siswa' => ! Schema::hasColumn('pengaturan_absensi', 'waktu_notifikasi_siswa'),
            'jeda_alpa_siswa' => ! Schema::hasColumn('pengaturan_absensi', 'jeda_alpa_siswa'),
        ];
        if (in_array(true, $settingsColumns, true)) {
            Schema::table('pengaturan_absensi', function (Blueprint $table) use ($settingsColumns) {
                if ($settingsColumns['jam_masuk_siswa']) {
                    $table->time('jam_masuk_siswa')->nullable();
                }
                if ($settingsColumns['batas_scan_masuk_siswa']) {
                    $table->time('batas_scan_masuk_siswa')->nullable();
                }
                if ($settingsColumns['jam_pulang_siswa']) {
                    $table->time('jam_pulang_siswa')->nullable();
                }
                if ($settingsColumns['batas_jam_pulang_siswa']) {
                    $table->time('batas_jam_pulang_siswa')->nullable();
                }
                if ($settingsColumns['waktu_notifikasi_siswa']) {
                    $table->time('waktu_notifikasi_siswa')->nullable();
                }
                if ($settingsColumns['jeda_alpa_siswa']) {
                    $table->unsignedSmallInteger('jeda_alpa_siswa')->nullable();
                }
            });
        }

        if (! Schema::hasTable('rfid_cards')) {
            Schema::create('rfid_cards', function (Blueprint $table) {
                $table->string('card_id')->primary();
                $table->string('entitas', 10);
                $table->uuid('entitas_id');
                $table->timestamps();
                $table->unique(['entitas', 'entitas_id'], 'rfid_cards_entity_uq');
                $table->index(['entitas', 'entitas_id'], 'rfid_cards_entity_idx');
            });
        }

        if (Schema::hasTable('guru') && Schema::hasColumn('guru', 'card_id')) {
            // Preserve existing teacher cards in the global registry. Duplicate card IDs
            // are not silently overwritten; the insert will fail and stop this migration.
            DB::table('guru')->whereNotNull('card_id')->orderBy('id')->chunk(200, function ($gurus): void {
                foreach ($gurus as $guru) {
                    DB::table('rfid_cards')->insertOrIgnore([
                        'card_id' => $guru->card_id,
                        'entitas' => 'guru',
                        'entitas_id' => $guru->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $registeredCard = DB::table('rfid_cards')->where('card_id', $guru->card_id)->first();
                    if (! $registeredCard || $registeredCard->entitas !== 'guru' || $registeredCard->entitas_id !== $guru->id) {
                        throw new RuntimeException('Kartu RFID guru bertabrakan dengan kartu lain; migration dihentikan.');
                    }
                }
            });
        }

        if (! Schema::hasTable('presensi_siswa')) {
            Schema::create('presensi_siswa', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('siswa_id');
                $table->date('tanggal');
                $table->time('jam_masuk')->nullable();
                $table->time('jam_pulang')->nullable();
                $table->string('status', 20);
                $table->string('status_dokumen', 20)->nullable();
                $table->text('keterangan')->nullable();
                $table->uuid('dikoreksi_oleh')->nullable();
                $table->timestamp('dikoreksi_pada')->nullable();
                $table->timestamps();
                $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
                $table->unique(['siswa_id', 'tanggal'], 'presensi_siswa_day_uq');
                $table->index(['tanggal', 'status'], 'presensi_siswa_day_status_idx');
            });
        }

        if (! Schema::hasTable('ketidakhadiran_siswa')) {
            Schema::create('ketidakhadiran_siswa', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('siswa_id');
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->string('status', 12);
                $table->string('kategori_izin', 30)->nullable();
                $table->text('keterangan');
                $table->string('status_dokumen', 20)->default('belum_perlu');
                $table->boolean('perlu_pemeriksaan')->default(false);
                $table->uuid('diinput_oleh')->nullable();
                $table->timestamps();
                $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
                $table->index(['siswa_id', 'tanggal_mulai', 'tanggal_selesai'], 'kts_siswa_period_idx');
                $table->index(['tanggal_mulai', 'tanggal_selesai', 'status'], 'kts_period_status_idx');
            });
        }

        if (! Schema::hasTable('notifikasi_absensi_siswa')) {
            Schema::create('notifikasi_absensi_siswa', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('siswa_id');
                $table->date('tanggal');
                $table->string('jenis', 40);
                $table->string('status', 20)->default('pending');
                $table->unsignedSmallInteger('jumlah_percobaan')->default(0);
                $table->timestamp('dicoba_pada')->nullable();
                $table->timestamp('terkirim_pada')->nullable();
                $table->text('alasan_aman')->nullable();
                $table->timestamps();
                $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
                $table->unique(['siswa_id', 'tanggal', 'jenis'], 'notif_siswa_day_type_uq');
                $table->index(['status', 'dicoba_pada'], 'notif_siswa_status_retry_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_absensi_siswa');
        Schema::dropIfExists('ketidakhadiran_siswa');
        Schema::dropIfExists('presensi_siswa');
        Schema::dropIfExists('rfid_cards');

        // Added configuration/contact columns are retained on rollback to avoid
        // deleting values written after this migration.
    }
};
