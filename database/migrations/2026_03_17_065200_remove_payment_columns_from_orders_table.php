<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Memindahkan kolom pembayaran dari tabel orders ke tabel payments.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'payment_status',
                'paid_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('total_amount')->after('returned_at');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid')->after('total_amount');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }
};
