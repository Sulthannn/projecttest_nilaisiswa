<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('siswas')) return;
        try { DB::statement('DROP INDEX IF EXISTS siswas_nama_kelas_unique'); } catch (\Throwable $e) {}
        DB::statement('CREATE UNIQUE INDEX siswas_nama_kelas_ci_unique ON siswas (LOWER(nama), LOWER(kelas))');
    }

    public function down(): void
    {
        try { DB::statement('DROP INDEX IF EXISTS siswas_nama_kelas_ci_unique'); } catch (\Throwable $e) {}
    }
};
