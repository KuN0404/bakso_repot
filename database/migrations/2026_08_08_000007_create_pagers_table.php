<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel 'pagers' TIDAK PERNAH dibuat di report — ReportSyncService::ensurePagerSynced()/
 * syncPager() sudah lama mencoba menulis ke tabel ini dan gagal (ditangkap try/catch,
 * dicatat ke log, tidak fatal, tapi berarti data pager tidak pernah tersinkron sama sekali).
 *
 * Skema dibuat langsung dalam bentuk akhirnya (termasuk deleted_at dan index 'number'
 * non-unique) mengikuti main app setelah migration
 * 2026_08_06_000001_create_pagers_table.php + 2026_08_08_000005_add_soft_deletes_to_pagers_table.php.
 * 'number' sengaja TIDAK unique (bukan bug) — soft-deleted pager tidak boleh mengunci
 * nomor pager itu secara permanen di DB; keunikan untuk pager aktif dijaga di aplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pagers')) {
            return;
        }

        Schema::create('pagers', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20);
            $table->string('docking_number', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('number');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagers');
    }
};
