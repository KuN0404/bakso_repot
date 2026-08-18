<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cermin dari main app (bakso-malang) migration dengan nama yang sama —
 * snapshot komposisi komponen (BOM/substitusi) yang benar-benar dipakai per
 * baris transaksi. Tanpa tabel ini, ReportSyncService::syncTransaction() gagal
 * (Unknown table) setiap kali mencoba menulis ke transaction_detail_components,
 * persis seperti riwayat gap 'pagers' dan kolom transactions sebelumnya.
 *
 * product_bom_id dan substitution_rule_id SENGAJA tanpa foreign key (sama
 * seperti main app) — catatan historis, dan report DB tidak memiliki tabel
 * product_bom_substitutions sama sekali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_detail_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_detail_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('component_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('component_name', 150);
            $table->decimal('quantity_per_unit', 12, 3);
            $table->decimal('quantity_total', 12, 3);
            $table->string('source', 20);
            $table->foreignId('replaced_component_id')
                ->nullable()
                ->constrained('components')
                ->nullOnDelete();
            $table->decimal('replaced_quantity', 12, 3)->nullable();

            // Sengaja tanpa foreign key — lihat catatan di atas.
            $table->unsignedBigInteger('product_bom_id')->nullable();
            $table->unsignedBigInteger('substitution_rule_id')->nullable();

            $table->timestamps();

            $table->index('transaction_detail_id');
            $table->index('component_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_detail_components');
    }
};
