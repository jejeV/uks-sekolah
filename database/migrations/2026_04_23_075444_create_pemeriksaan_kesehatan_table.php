<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeriksaan_kesehatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('semester');       // 1 atau 2
            $table->year('tahun_ajaran');
            $table->decimal('berat_badan', 5, 2)->nullable();  // kg
            $table->decimal('tinggi_badan', 5, 2)->nullable(); // cm
            $table->decimal('bmi', 4, 2)->nullable();
            $table->string('penglihatan_kiri')->nullable();
            $table->string('penglihatan_kanan')->nullable();
            $table->enum('pendengaran', ['normal', 'kurang', 'tuli'])->nullable();
            $table->enum('kondisi_gigi', ['baik', 'caries', 'perlu_perawatan'])->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Satu anggota hanya 1x periksa per semester per tahun
            $table->unique(['anggota_id', 'semester', 'tahun_ajaran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_kesehatan');
    }
};  
