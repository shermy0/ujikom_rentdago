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
        Schema::table('orders', function (Blueprint $table) {
            // Cek jika kolom belum ada
            if (!Schema::hasColumn('orders', 'is_read_by_seller')) {
                $table->boolean('is_read_by_seller')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'is_read_by_seller')) {
                $table->dropColumn('is_read_by_seller');
            }
        });
    }
};