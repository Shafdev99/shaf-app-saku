<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_absensi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->time('jam_masuk')->default('07:00:00');
            $table->unsignedInteger('toleransi_terlambat')->default(10);
            $table->time('jam_pulang')->default('15:00:00');
            $table->time('batas_jam_pulang')->default('17:00:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_absensi');
    }
};
