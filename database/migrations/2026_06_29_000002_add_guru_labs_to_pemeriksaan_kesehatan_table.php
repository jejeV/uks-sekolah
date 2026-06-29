<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemeriksaan_kesehatan', function (Blueprint $table) {
            $table->decimal('gula_darah', 6, 2)->nullable()->after('kondisi_gigi');
            $table->decimal('kolesterol', 6, 2)->nullable()->after('gula_darah');
        });
    }

    public function down(): void
    {
        Schema::table('pemeriksaan_kesehatan', function (Blueprint $table) {
            $table->dropColumn(['gula_darah', 'kolesterol']);
        });
    }
};
