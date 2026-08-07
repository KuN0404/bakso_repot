<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_order_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('self_order_item_id');
            $table->unsignedBigInteger('modifier_id')->nullable();
            $table->string('modifier_name', 200);
            $table->decimal('price_adjustment', 12, 2)->default(0);
            $table->unsignedTinyInteger('quantity')->default(1);

            $table->index('self_order_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('self_order_item_modifiers');
    }
};
