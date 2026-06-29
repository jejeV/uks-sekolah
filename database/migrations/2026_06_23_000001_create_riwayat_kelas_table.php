<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignId('jenjang_id')->constrained('jenjang')->cascadeOnDelete();
            $table->year('tahun_ajaran');
            $table->string('kelas_lama')->nullable();
            $table->string('kelas_baru')->nullable();
            $table->enum('aksi', ['naik_kelas', 'lulus']);
            $table->timestamp('diproses_pada');
            $table->timestamps();

            $table->unique(['anggota_id', 'tahun_ajaran', 'aksi'], 'riwayat_kelas_anggota_tahun_aksi_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas');
    }
};
