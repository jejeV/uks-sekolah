<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenjang', function (Blueprint $table) {
            $table->id();
            $table->enum('nama', ['TK', 'SD', 'SMP']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenjang');
    }
};
