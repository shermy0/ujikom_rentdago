<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            // hapus kolom logistik
            $table->dropForeign(['courier_id']);
            $table->dropColumn([
                'courier_id',
                'assigned_at',
                'courier_notes',
                'is_tracking_active',
                'last_lat',
                'last_lng',
                'delivery_address_snapshot',
            ]);

            // update enum status
            DB::statement("
                ALTER TABLE orders 
                MODIFY status ENUM(
                    'pending',
                    'paid',
                      'confirmed',
                    'ongoing',
                    
                    'completed',
                    'cancelled',
                    'penalty'
                ) DEFAULT 'pending'
            ");
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->text('courier_notes')->nullable();
            $table->boolean('is_tracking_active')->default(false);
            $table->decimal('last_lat', 10, 8)->nullable();
            $table->decimal('last_lng', 11, 8)->nullable();
            $table->text('delivery_address_snapshot')->nullable();

            DB::statement("
                ALTER TABLE orders 
                MODIFY status ENUM(
                    'pending',
                    'confirmed',
                    'ongoing',
                    'completed',
                    'cancelled',
                    'penalty',
                    'arrived'
                ) DEFAULT 'pending'
            ");
        });
    }
};
