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
        Schema::table('shipments', function (Blueprint $table) {
            // Rejection tracking
            $table->timestamp('rejected_at')->nullable()->after('failed_at');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->json('rejected_by')->nullable()->after('rejection_reason')->comment('Array of courier IDs who rejected this shipment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['rejected_at', 'rejection_reason', 'rejected_by']);
        });
    }
};
