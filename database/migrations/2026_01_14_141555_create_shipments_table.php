<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();

            // relasi
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete();

            // jenis shipment
            $table->enum('type', ['delivery', 'return']);

            // status logistik
            $table->enum('status', [
                'pending',
                'assigned',
                'picked_up',
                'on_the_way',
                'arrived',
                'failed',
                'returned'
            ])->default('pending');

            // alamat snapshot
            $table->text('pickup_address_snapshot')->nullable();
            $table->text('delivery_address_snapshot')->nullable();

            // tracking
            $table->boolean('is_tracking_active')->default(false);
            $table->decimal('last_lat', 10, 8)->nullable();
            $table->decimal('last_lng', 11, 8)->nullable();

            // timestamps penting
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // catatan
            $table->text('courier_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
