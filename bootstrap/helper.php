<?php

use Illuminate\Support\Facades\Http;

if (!function_exists('kirimWa')) {
    function kirimWa($target, $pesan)
    {
        Http::asForm()->withHeaders([
            'Authorization' => 'Bearer ' . env('JAPATI_API_TOKEN'),
        ])->post('https://app.japati.id/api/send-message', [
            'gateway' => env('JAPATI_GATEWAY_NUMBER'),
            'number' => $target,
            'type' => 'text',
            'message' => $pesan,
        ]);
    }
}
