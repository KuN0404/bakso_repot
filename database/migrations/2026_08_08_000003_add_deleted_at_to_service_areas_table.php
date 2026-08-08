<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyamakan skema 'service_areas' di report dengan main app. Lihat catatan
 * idempotensi & alasan unique->index di 2026_08_08_000001_add_deleted_at_to_users_table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_areas', 'deleted_at')) {
            Schema::table('service_areas', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasIndex('service_areas', 'service_areas_code_unique')) {
            Schema::table('service_areas', function (Blueprint $table) {
                $table->dropUnique('service_areas_code_unique');
            });
        }
        if (! Schema::hasIndex('service_areas', 'service_areas_code_index')) {
            Schema::table('service_areas', function (Blueprint $table) {
                $table->index('code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('service_areas', 'service_areas_code_index')) {
            Schema::table('service_areas', function (Blueprint $table) {
                $table->dropIndex('service_areas_code_index');
            });
        }
        if (! Schema::hasIndex('service_areas', 'service_areas_code_unique')) {
            Schema::table('service_areas', function (Blueprint $table) {
                $table->unique('code');
            });
        }

        if (Schema::hasColumn('service_areas', 'deleted_at')) {
            Schema::table('service_areas', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
