<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom quantity di pivot transaction_detail_modifier
        Schema::table('transaction_detail_modifier', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_detail_modifier', 'quantity')) {
                $table->unsignedSmallInteger('quantity')
                    ->default(1)
                    ->after('price_adjustment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaction_detail_modifier', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
