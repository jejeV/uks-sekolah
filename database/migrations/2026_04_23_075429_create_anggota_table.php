<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenjang_id')->constrained('jenjang')->cascadeOnDelete();
            $table->string('nis_nip')->unique();
            $table->string('nama');
            $table->enum('tipe', ['siswa', 'guru', 'tenaga_kependidikan']);
            $table->string('kelas')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
