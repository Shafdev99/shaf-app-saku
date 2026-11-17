<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu_identitas_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('jenis', 12);
            $table->string('sisi', 12);
            $table->string('disk', 32);
            $table->string('path', 255);
            $table->boolean('aktif')->default(false);
            $table->string('diunggah_oleh', 36)->nullable();
            $table->timestamps();
            $table->index(['jenis', 'sisi', 'aktif'], 'ki_template_active_idx');
        });

        $now = now();
        DB::table('kartu_identitas_templates')->insert([
            [
                'jenis' => 'pelajar', 'sisi' => 'depan', 'disk' => 'card_assets',
                'path' => 'Kartu pelajar — depan.jpg', 'aktif' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'jenis' => 'pelajar', 'sisi' => 'belakang', 'disk' => 'card_assets',
                'path' => 'Kartu pelajar — belakang.jpg', 'aktif' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'jenis' => 'guru', 'sisi' => 'depan', 'disk' => 'card_assets',
                'path' => 'Kartu guru — depan.png', 'aktif' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_identitas_templates');
    }
};
