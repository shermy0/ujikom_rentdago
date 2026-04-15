<?php

namespace App\Http\Controllers\Kurir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Storage facade dihapus — menggunakan move() ke public/ langsung
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class KurirQrController extends Controller
{
    /**
     * Show selection page for handover method
     */
    /**
     * Show selection page for handover method (REMOVED)
     */
    // public function showHandoverOptions($shipmentId)
    // {
    //     // Removed as per request to skip to photo page
    // }
/**
 * Show photo upload page for specific order
 */
public function showPhotoPage($shipmentId)
{
    // 🔍 Ambil data kurir dari user login
    $courier = Courier::where('user_id', Auth::id())->first();

    // ❌ Jika tidak ada kurir → tidak boleh akses
    if (!$courier) {
        return redirect()->route('kurir.dashboard')->with('error', 'Data kurir tidak ditemukan');
    }

    // 🔍 Ambil shipment berdasarkan ID
    $shipment = Shipment::with([
            'order.user',                     // customer
            'order.productRental.product',   // produk
            'order.address'                  // alamat tujuan
        ])
        ->where('id', $shipmentId)           // berdasarkan shipment ID
        ->where('courier_id', $courier->id)  // harus milik kurir ini (security)

        ->whereIn('type', [Shipment::TYPE_DELIVERY]) // hanya DELIVERY

        // ambil 1 data atau error jika tidak ada
        ->firstOrFail();

    // 🚫 Validasi status:
    // hanya boleh upload foto jika:
    // - sedang di perjalanan (on_the_way)
    // - atau sudah sampai (arrived)
    if (!in_array($shipment->status, [
        Shipment::STATUS_ON_THE_WAY, 
        Shipment::STATUS_ARRIVED
    ])) {
        return redirect()->route('kurir.orders')
            ->with('error', 'Status pengiriman tidak valid untuk foto bukti');
    }

    // 🎯 Tampilkan halaman upload foto bukti
    return view('kurir.delivery-photo.take-photo', compact('shipment'));
}

/**
 * Complete delivery with photo proof
 */
public function completeDelivery(Request $request)
{
    // ✅ Validasi input:
    // - shipment_id harus ada di tabel shipments
    // - photo wajib berupa gambar (jpg/png) max 5MB
    $request->validate([
        'shipment_id' => 'required|exists:shipments,id',
        'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
    ]);

    // 🔍 Ambil data kurir berdasarkan user login
    $courier = Courier::where('user_id', Auth::id())->firstOrFail();

    // 🔍 Ambil shipment:
    // - berdasarkan ID
    // - harus milik kurir ini (security)
    $shipment = Shipment::with('order')
        ->where('id', $request->shipment_id)
        ->where('courier_id', $courier->id)
        ->firstOrFail();

    // 📸 Proses upload foto bukti
    $photoPath = null;
    $photoTimestamp = null;

    if ($request->hasFile('photo')) {
        $photo = $request->file('photo');

        // 🏷️ Buat nama file unik:
        // format: delivery_ORDERCODE_timestamp.jpg
        $prefix = 'delivery_';
        $photoName = $prefix . $shipment->order->order_code . '_' . time() . '.' . $photo->getClientOriginalExtension();

        // 💾 Simpan ke public/delivery-proofs
        if (!file_exists(public_path('delivery-proofs'))) {
            mkdir(public_path('delivery-proofs'), 0755, true);
        }
        $photo->move(public_path('delivery-proofs'), $photoName);
        $photoPath = 'delivery-proofs/' . $photoName;

        // 🕒 Simpan waktu upload foto
        $photoTimestamp = now();
    }

    // 🔄 Lanjut ke proses serah terima (handover)
    return $this->processHandover($shipment, 'photo', $photoPath, $photoTimestamp);
}

/**
 * Shared logic to process handover
 */
private function processHandover($shipment, $method, $photoPath = null, $photoTimestamp = null)
{
    // 🔒 Mulai database transaction (biar aman kalau gagal)
    DB::beginTransaction();

    try {
        $order = $shipment->order;

        // 🚚 HANDLE DELIVERY (pengiriman ke customer)
        if ($shipment->type === Shipment::TYPE_DELIVERY) {

            // 📝 Update data shipment
            $existingNotes = trim((string) $shipment->courier_notes);
            $existingNotes = str_replace([
                'Diverifikasi via PHOTO',
                'Serah terima ke customer diverifikasi via FOTO kurir',
                'Verifikasi serah terima: FOTO kurir',
            ], '', $existingNotes);
            $existingNotes = trim(preg_replace("/\n{2,}/", "\n", $existingNotes));

            $verificationNote = $method === 'photo'
                ? 'Verifikasi serah terima: FOTO kurir'
                : 'Verifikasi serah terima: ' . strtoupper($method);

            $shipment->update([
                'status' => Shipment::STATUS_DELIVERED,        // status jadi delivered
                'delivered_at' => now(),                       // waktu selesai
                'is_tracking_active' => false,                 // tracking dimatikan
                'delivery_proof_photo' => $photoPath,          // simpan path foto
                'delivery_proof_photo_at' => $photoTimestamp,  // waktu foto diambil
                'courier_notes' => trim($existingNotes . "\n" . $verificationNote), // catatan kurir
            ]);

            // 🔄 Update status order jadi ONGOING (barang sedang dipakai/disewa)
            $order->update([
                'status' => Order::STATUS_ONGOING,
            ]);

            // 🔔 Kirim notifikasi ke customer (barang sudah diterima)
            \App\Helpers\CustomerNotificationHelper::notifyOrderPickedUp($order);

            // 📢 Pesan sukses
            $successMessage = 'Pesanan berhasil diserahkan! Status kini: Sedang Berlangsung.';
        }

        // ✅ Commit perubahan ke database
        DB::commit();

        // 🔔 Kirim notifikasi ke seller & customer
        $this->sendHandoverNotifications($order, $shipment);

        // 🎯 Return response sukses
        return response()->json([
            'success' => true,
            'message' => $successMessage,
            'redirect' => route('kurir.history') // redirect ke riwayat
        ]);

    } catch (\Exception $e) {
        // ❌ Jika error → rollback semua perubahan
        DB::rollBack();

        // 📝 Log error
        Log::error('Handover Error: ' . $e->getMessage());

        // ❌ Return response gagal
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Send notifications after handover
 */
private function sendHandoverNotifications($order, $shipment)
{
    try {
        // 🚚 Jika ini pengiriman delivery
        if ($shipment->type === Shipment::TYPE_DELIVERY) {

            // 📸 Jika ada foto bukti → kirim notifikasi dengan foto
            if ($shipment->delivery_proof_photo) {
                \App\Helpers\CourierNotificationHelper::notifySellerHandover($order, $shipment);
            } else {
                // 📩 Jika tidak ada foto → notifikasi biasa
                \App\Helpers\CourierNotificationHelper::notifySellerDeliveryComplete($order);
            }
        }

    } catch (\Exception $e) {
        // ❌ Jika gagal kirim notifikasi → hanya log (tidak ganggu proses utama)
        Log::error('Failed to send handover notifications: ' . $e->getMessage());
    }
}
}
