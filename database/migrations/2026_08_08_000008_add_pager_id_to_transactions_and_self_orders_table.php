<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ReportSyncService::syncTransaction() dan syncSelfOrder() sudah lama mengirim
 * 'pager_id' ke report, tapi kolomnya tidak pernah ada di sini — SETIAP sync
 * transaksi & self order (bukan cuma yang pakai pager) gagal total karena upsert-nya
 * satu statement mencakup semua kolom sekaligus (SQLSTATE 42S22: Unknown column).
 * Ini bug lama yang sudah berjalan sejak fitur pager dirilis di main app, tidak
 * cuma soal deleted_at — lihat catatan audit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transactions', 'pager_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('pager_id')
                    ->nullable()
                    ->after('service_area_id')
                    ->constrained('pagers')
                    ->nullOnDelete();

                $table->index('pager_id');
            });
        }

        if (Schema::hasTable('self_orders') && ! Schema::hasColumn('self_orders', 'pager_id')) {
            Schema::table('self_orders', function (Blueprint $table) {
                $table->foreignId('pager_id')
                    ->nullable()
                    ->after('service_area_id')
                    ->constrained('pagers')
                    ->nullOnDelete();

                $table->index('pager_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'pager_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['pager_id']);
                $table->dropIndex(['pager_id']);
                $table->dropColumn('pager_id');
            });
        }

        if (Schema::hasTable('self_orders') && Schema::hasColumn('self_orders', 'pager_id')) {
            Schema::table('self_orders', function (Blueprint $table) {
                $table->dropForeign(['pager_id']);
                $table->dropIndex(['pager_id']);
                $table->dropColumn('pager_id');
            });
        }
    }
};
