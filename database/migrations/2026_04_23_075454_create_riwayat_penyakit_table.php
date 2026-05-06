<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_penyakit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignId('kunjungan_id')->nullable()
                  ->constrained('kunjungan_uks')->nullOnDelete();
            $table->string('nama_penyakit');
            $table->string('kode_icd')->nullable(); // kode ICD-10
            $table->date('tgl_mulai');
            $table->date('tgl_sembuh')->nullable();
            $table->enum('status', ['aktif', 'sembuh', 'kronis'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_penyakit');
    }
};
