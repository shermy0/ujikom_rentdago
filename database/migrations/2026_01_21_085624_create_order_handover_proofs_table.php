<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_handover_proofs', function (Blueprint $table) {
            $table->id();

            // 1 order = 1 bukti serah barang
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete()
                ->unique();

            // Path foto bukti (customer + barang)
            $table->string('photo_path');

            // Waktu foto diambil (default sekarang)
            $table->timestamp('taken_at')->useCurrent();

            // Catatan opsional (misal: kondisi barang)
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_handover_proofs');
    }
};
