<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'logo',
        'app_name',
        'about',

        // Payment Gateway
        'midtrans_client_key',
        'midtrans_server_key',
        'midtrans_mode',

        // WhatsApp Gateway
        'wa_endpoint_url',
        'wa_token',
        'wa_sender',
        
        //Data Toko
        'address',
        'open_time',
        'document_description',

        // Footer
        'footer_text',
    ];

}



