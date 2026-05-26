<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->index(['aktif', 'tipe'], 'anggota_aktif_tipe_index');
        });

        Schema::table('kunjungan_uks', function (Blueprint $table) {
            $table->index('tanggal', 'kunjungan_uks_tanggal_index');
            $table->index(['tanggal', 'status'], 'kunjungan_uks_tanggal_status_index');
        });

        Schema::table('pemeriksaan_kesehatan', function (Blueprint $table) {
            $table->index('created_at', 'pemeriksaan_kesehatan_created_at_index');
            $table->index(['tahun_ajaran', 'semester'], 'pemeriksaan_kesehatan_tahun_semester_index');
        });

        Schema::table('riwayat_penyakit', function (Blueprint $table) {
            $table->index(['anggota_id', 'status'], 'riwayat_penyakit_anggota_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_penyakit', function (Blueprint $table) {
            $table->dropIndex('riwayat_penyakit_anggota_status_index');
        });

        Schema::table('pemeriksaan_kesehatan', function (Blueprint $table) {
            $table->dropIndex('pemeriksaan_kesehatan_created_at_index');
            $table->dropIndex('pemeriksaan_kesehatan_tahun_semester_index');
        });

        Schema::table('kunjungan_uks', function (Blueprint $table) {
            $table->dropIndex('kunjungan_uks_tanggal_index');
            $table->dropIndex('kunjungan_uks_tanggal_status_index');
        });

        Schema::table('anggota', function (Blueprint $table) {
            $table->dropIndex('anggota_aktif_tipe_index');
        });
    }
};
