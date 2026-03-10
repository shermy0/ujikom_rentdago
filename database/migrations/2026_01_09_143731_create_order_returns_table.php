<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('penalties_amount')->default(0);

            $table->enum('payment_status', ['unpaid', 'paid'])
                ->default('unpaid');
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Anti double return (1 order = 1 penalty record max)
            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_returns');
    }
};
