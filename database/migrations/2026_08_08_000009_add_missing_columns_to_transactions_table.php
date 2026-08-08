<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Report's 'transactions' table masih memakai bentuk lama migration
 * 2024_12_27_000007_create_transactions_table.php, sedangkan main app sudah
 * merapikan migration yang sama untuk menambahkan receipt_token, customer_phone,
 * dan customer_email langsung di situ. Karena report tidak ikut diupdate,
 * ReportSyncService::syncTransaction() gagal (Unknown column) setiap kali —
 * bug lama, bukan cuma soal deleted_at, lihat catatan audit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'receipt_token')) {
                $table->string('receipt_token', 64)->nullable()->unique()->after('invoice_number');
            }
            if (! Schema::hasColumn('transactions', 'customer_phone')) {
                $table->string('customer_phone', 20)->nullable()->after('customer_name');
            }
            if (! Schema::hasColumn('transactions', 'customer_email')) {
                $table->string('customer_email', 150)->nullable()->after('customer_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'customer_email')) {
                $table->dropColumn('customer_email');
            }
            if (Schema::hasColumn('transactions', 'customer_phone')) {
                $table->dropColumn('customer_phone');
            }
            if (Schema::hasColumn('transactions', 'receipt_token')) {
                $table->dropColumn('receipt_token');
            }
        });
    }
};
