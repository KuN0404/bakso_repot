<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50);
            $table->decimal('amount', 12, 3);
            $table->decimal('final_stock', 12, 3);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->timestamps();

            $table->index('component_id');
            $table->index('type');
            $table->index('created_at');
            $table->index(['reference_id', 'reference_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_stock_logs');
    }
};
