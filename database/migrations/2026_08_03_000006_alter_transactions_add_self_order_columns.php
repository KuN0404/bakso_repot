<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom source dan self_order_id ke transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('source', ['pos', 'self_order'])
                ->default('pos')
                ->after('order_type');

            $table->unsignedBigInteger('self_order_id')
                ->nullable()
                ->after('source');

            $table->index('source');
            $table->index('self_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropIndex(['self_order_id']);
            $table->dropColumn(['source', 'self_order_id']);
        });
    }
};
