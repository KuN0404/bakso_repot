<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('self_order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name', 200);
            $table->decimal('unit_price', 12, 2);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('modifier_total', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('self_order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('self_order_items');
    }
};
