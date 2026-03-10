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
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['about', 'address', 'open_time', 'document_description', 'footer_text']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('about')->nullable();
            $table->text('address')->nullable();
            $table->string('open_time')->nullable();
            $table->text('document_description')->nullable();
            $table->text('footer_text')->nullable();
        });
    }
};
