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
        Schema::create('product_rentals', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete(); 
    $table->integer('price');
    $table->integer('cycle_value');
    $table->integer('penalties_price'); 
    $table->integer('penalties_cycle_value');
    $table->enum('is_delivery', [
        'pickup',
        'delivery',
        'pickup_delivery'
    ])->default('pickup');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_rentals');
    }
};
