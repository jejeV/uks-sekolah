<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_uks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam')->nullable();
            $table->text('keluhan');
            $table->text('diagnosis')->nullable();
            $table->text('tindakan')->nullable();
            $table->string('obat')->nullable();
            $table->enum('status', ['ringan', 'sedang', 'berat', 'dirujuk'])
                  ->default('ringan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_uks');
    }
};
