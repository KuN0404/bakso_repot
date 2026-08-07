<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_orders', function (Blueprint $table) {
            $table->id();

            // Identitas & Tracking
            $table->string('order_token', 64)->unique();
            $table->unsignedSmallInteger('queue_number')->nullable();
            $table->string('invoice_number', 30)->nullable();

            // Data Customer
            $table->string('customer_name', 100);
            $table->string('customer_phone', 20);
            $table->string('customer_email', 150)->nullable();

            // Pickup details
            $table->string('pickup_name', 100)->nullable();
            $table->string('pickup_phone', 20)->nullable();

            // Finansial
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Metadata Order
            $table->enum('order_type', ['dine_in', 'take_away'])->default('dine_in');
            $table->unsignedBigInteger('service_area_id')->nullable();
            $table->text('notes')->nullable();

            // Payment
            $table->enum('payment_method', ['qris', 'cash_on_counter'])->default('qris');

            // Status
            $table->string('status', 30)->default('pending_payment');

            // Relasi (stored as IDs, no FK constraints in report DB)
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();

            // Pembatalan
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Timestamps proses
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('pickup_confirmed_at')->nullable();

            // Idempotency
            $table->string('idempotency_key', 64)->nullable()->unique();

            // Security
            $table->string('customer_ip', 45)->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('customer_phone');
            $table->index('processed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('self_orders');
    }
};
