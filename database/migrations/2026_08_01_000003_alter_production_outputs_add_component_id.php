<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_outputs', function (Blueprint $table) {
            $table->foreignId('component_id')
                ->nullable()
                ->after('production_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('production_outputs', function (Blueprint $table) {
            $table->dropForeign(['component_id']);
            $table->dropColumn('component_id');
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
