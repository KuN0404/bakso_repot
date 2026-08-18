<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Report's 'payment_transactions' table belum ikut diupdate saat main app
 * menambahkan self_order_id di 2026_08_03_000007_alter_payment_transactions_for_self_order.php,
 * sehingga ReportSyncService::syncPaymentTransaction() gagal (Unknown column 'self_order_id').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_transactions', 'self_order_id')) {
                $table->foreignId('self_order_id')
                    ->nullable()
                    ->after('source')
                    ->constrained('self_orders')
                    ->nullOnDelete();

                $table->index('self_order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('payment_transactions', 'self_order_id')) {
                $table->dropForeign(['self_order_id']);
                $table->dropIndex(['payment_transactions_self_order_id_index']);
                $table->dropColumn('self_order_id');
            }
        });
    }
};
