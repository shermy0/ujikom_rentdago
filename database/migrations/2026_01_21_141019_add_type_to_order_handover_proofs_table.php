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
    Schema::table('order_handover_proofs', function (Blueprint $table) {
        $table->string('type')->after('photo_path'); // start / return
    });
}

public function down(): void
{
    Schema::table('order_handover_proofs', function (Blueprint $table) {
        $table->dropColumn(['type']);
    });
}

};
