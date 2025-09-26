<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('siswas')) return;
        try {
            Schema::table('siswas', function (Blueprint $table) {
                $table->unique(['nama','kelas'], 'siswas_nama_kelas_unique');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('siswas')) return;
        try {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropUnique('siswas_nama_kelas_unique');
            });
        } catch (\Throwable $e) {
        }
    }
};
