<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('shops', 'slug')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name_store');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('shops', 'slug')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};