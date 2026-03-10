<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // App
            $table->string('logo')->nullable(); // path logo
            $table->string('app_name', 100)->nullable();
            $table->text('about')->nullable();
        

            // Payment Gateway
            $table->string('midtrans_client_key')->nullable();
            $table->string('midtrans_server_key')->nullable();
         $table->enum('midtrans_mode', ['sandbox', 'production'])
                  ->default('sandbox');

            // WhatsApp Gateway
            $table->string('wa_endpoint_url')->nullable();
            $table->text('wa_token')->nullable();
            $table->string('wa_sender')->nullable();
            $table->string('address')->nullable();
            $table->string('open_time')->nullable();
            $table->string('document_description')->nullable();


            // Footer
            $table->text('footer_text')->nullable();

            $table->timestamps();
        });

        // Insert default setting
        DB::table('settings')->insert([
            'logo'              => null,
            'app_name'          => config('app.name'),
            'about'             => 'Komunitas Pengguna Kendaraan Listrik di Indonesia',
     

            'midtrans_client_key' => null,
            'midtrans_server_key' => null,
         

             'wa_endpoint_url' => 'https://app.japati.id/api/send-message',
            'wa_token' => 'API-TOKEN-RpQNLFbV8bNHluG01bJ77qdt7KM28lScrhqdHb0Tax0WNYzhguo78Y',
            'wa_sender' => '628997440314',
     
          

            'footer_text'       => 'Supported by <a href="https://example.com" target="_blank">Example</a>',

            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
