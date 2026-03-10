<?php
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

if (!function_exists('kirimWa')) {
    function kirimWa($target, $pesan, $imageUrl = null)
    {
        try {
            // Ambil setting dari DB kalau ada
            $setting = DB::table('settings')->first();
            $token   = $setting->japati_api_token ?? env('JAPATI_API_TOKEN');
            $gateway = $setting->japati_gateway_number ?? env('JAPATI_GATEWAY_NUMBER');

            if (!$token || !$gateway) {
                throw new \Exception("Token atau gateway belum diatur");
            }

            Log::info('🔵 kirimWa dipanggil', [
                'target' => $target,
                'has_image' => !empty($imageUrl),
                'message_length' => strlen($pesan)
            ]);

            // ✅ JIKA ADA GAMBAR: kirim media dengan caption
            if (!empty($imageUrl)) {
                Log::info('📸 Mengirim gambar dengan caption');

                $response = Http::timeout(30)
                    ->asForm()
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                    ])
                    ->post('https://app.japati.id/api/send-message', [
                        'gateway' => $gateway,
                        'number'  => $target,
                        'type'    => 'media',  // ✅ type = media
                        'media_file' => $imageUrl,  // ✅ URL gambar
                        'message' => $pesan,  // ✅ Text jadi caption di bawah gambar
                    ]);

                Log::info('📥 Response media:', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                if ($response->failed()) {
                    throw new \Exception("Gagal mengirim WA media: " . $response->body());
                }

            } else {
                // ✅ JIKA TIDAK ADA GAMBAR: kirim text biasa
                Log::info('📝 Mengirim text saja');

                $response = Http::timeout(30)
                    ->asForm()
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                    ])
                    ->post('https://app.japati.id/api/send-message', [
                        'gateway' => $gateway,
                        'number'  => $target,
                        'type'    => 'text',  // ✅ type = text
                        'message' => $pesan,  // ✅ Pesan text
                    ]);

                Log::info('📥 Response text:', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                if ($response->failed()) {
                    throw new \Exception("Gagal mengirim WA text: " . $response->body());
                }
            }

            Log::info('✅ WA berhasil dikirim');
            return ['success' => true, 'message' => 'Pesan berhasil dikirim'];

        } catch (\Exception $e) {
            Log::error('❌ Error kirimWa: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}