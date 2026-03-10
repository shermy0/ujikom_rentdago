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
         Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete(); 
                $table->foreignId('product_rental_id')->constrained()->cascadeOnDelete(); // unit yg disewa

                $table->string('order_code')->unique(); 
                $table->timestamp('start_time')->nullable(); // Jadwal mulai sewa (untuk pickup)
                $table->timestamp('end_time')->nullable();
                   $table->timestamp('returned_at')
                ->nullable()
                ;
                $table->integer('total_amount'); 
                
                $table->enum('status', ['pending', 'confirmed', 'ongoing', 'completed', 'cancelled', 'penalty','arrived'])->default('pending');
                $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
                $table->enum('delivery_method', ['pickup', 'delivery'])->nullable();
               
                $table->timestamp('paid_at')->nullable(); // Waktu pembayaran (waktu mulai sewa aktual untuk delivery)
                $table->string('qr_code')->nullable();
                $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->text('courier_notes')->nullable()->comment('Delivery instructions for courier');
                $table->foreignId('user_address_id')
                ->nullable()
                
                ->constrained('user_addresses')
                ->nullOnDelete();
                 $table->text('delivery_address_snapshot')
                ->nullable()
                ;
                 $table->boolean('is_tracking_active')->default(false);
            $table->decimal('last_lat', 10, 8)->nullable();
            $table->decimal('last_lng', 11, 8)->nullable();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};