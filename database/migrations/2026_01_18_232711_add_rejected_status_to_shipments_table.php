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
            // Modify status enum to include 'rejected'
            DB::statement("ALTER TABLE shipments MODIFY COLUMN status ENUM('pending', 'assigned', 'picked_up', 'on_the_way', 'arrived', 'failed', 'returned', 'rejected') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Remove 'rejected' from status enum
            DB::statement("ALTER TABLE shipments MODIFY COLUMN status ENUM('pending', 'assigned', 'picked_up', 'on_the_way', 'arrived', 'failed', 'returned') DEFAULT 'pending'");
        });
    }
};
