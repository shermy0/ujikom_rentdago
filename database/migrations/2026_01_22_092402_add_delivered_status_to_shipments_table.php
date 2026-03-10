<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter ENUM to add 'handed_over'
        DB::statement("ALTER TABLE `shipments` MODIFY COLUMN `status` ENUM(
            'pending',
            'assigned',
            'picked_up',
            'on_the_way',
            'arrived',
            'delivered',
            'failed',
            'returned',
            'rejected'
        ) DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Remove 'handed_over' from ENUM
        DB::statement("ALTER TABLE `shipments` MODIFY COLUMN `status` ENUM(
            'pending',
            'assigned',
            'picked_up',
            'on_the_way',
            'arrived',
            'failed',
            'returned',
            'rejected'
        ) DEFAULT 'pending'");
    }
};