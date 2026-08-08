<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyamakan skema 'categories' di report dengan main app. Lihat catatan
 * idempotensi & alasan unique->index di 2026_08_08_000001_add_deleted_at_to_users_table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'deleted_at')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasIndex('categories', 'categories_slug_unique')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique('categories_slug_unique');
            });
        }
        if (! Schema::hasIndex('categories', 'categories_slug_index')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->index('slug');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('categories', 'categories_slug_index')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('categories_slug_index');
            });
        }
        if (! Schema::hasIndex('categories', 'categories_slug_unique')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unique('slug');
            });
        }

        if (Schema::hasColumn('categories', 'deleted_at')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
