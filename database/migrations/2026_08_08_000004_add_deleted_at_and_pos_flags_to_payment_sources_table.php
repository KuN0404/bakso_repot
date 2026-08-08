<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyamakan skema 'payment_sources' di report dengan main app.
 *
 * 1. Tambah deleted_at (soft delete parity).
 * 2. Tambah is_active_pos & is_active_self_order: main app sejak fitur Self Order
 *    memecah kolom 'is_active' menjadi dua flag terpisah (visibilitas POS vs
 *    Self Order). Kolom 'is_active' lama TIDAK dihapus di sini karena report
 *    punya CRUD admin sendiri (app/Livewire/Admin/PaymentSources.php) yang
 *    masih membaca/menulis kolom itu langsung — menghapusnya akan merusak
 *    fitur report yang independen dari sync. ReportSyncService di main app
 *    sekarang mengisi 'is_active' = (is_active_pos OR is_active_self_order)
 *    supaya kolom lama tetap bermakna untuk kode report yang belum diupdate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_sources', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_sources', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('payment_sources', 'is_active_pos')) {
                $table->boolean('is_active_pos')->default(true)->after('is_active');
            }
            if (! Schema::hasColumn('payment_sources', 'is_active_self_order')) {
                $table->boolean('is_active_self_order')->default(true)->after('is_active_pos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_sources', function (Blueprint $table) {
            if (Schema::hasColumn('payment_sources', 'is_active_self_order')) {
                $table->dropColumn('is_active_self_order');
            }
            if (Schema::hasColumn('payment_sources', 'is_active_pos')) {
                $table->dropColumn('is_active_pos');
            }
            if (Schema::hasColumn('payment_sources', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
