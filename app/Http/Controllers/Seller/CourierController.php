<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourierController extends Controller
{
    /**
     * Generate random password
     */
    private function generatePassword($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    /**
     * Kirim Password ke WhatsApp Kurir
     */
    private function sendPasswordToWhatsApp($courierName, $phone, $password, $shopName)
    {
        try {
            // Format nomor WA (pastikan format Indonesia)
            $waNumber = $phone;
            if (substr($waNumber, 0, 1) === '0') {
                $waNumber = '62' . substr($waNumber, 1);
            }

            // Buat pesan WA
            $message = "🎉 *AKUN KURIR BERHASIL DIBUAT*\n\n";
            $message .= "Halo *{$courierName}*,\n\n";
            $message .= "Akun kurir Anda untuk toko *{$shopName}* telah berhasil dibuat.\n\n";
            $message .= "📱 *INFORMASI LOGIN:*\n";
            $message .= "━━━━━━━━━━━━━━━━━\n";
            $message .= "👤 Username: `{$phone}`\n";
            $message .= "🔐 Password: `{$password}`\n";
            $message .= "━━━━━━━━━━━━━━━━━\n\n";
            $message .= "⚠️ *PENTING:*\n";
            $message .= "• Simpan password ini dengan aman\n";
            $message .= "• Jangan berikan kepada siapapun\n";
            $message .= "• Gunakan untuk login ke aplikasi kurir\n\n";
            $message .= "🔗 Login di: rentdago.kakara.my.id\n\n";
            $message .= "Jika ada kendala, hubungi admin toko.\n\n";
            $message .= "_Pesan otomatis dari sistem_";

            // Kirim via helper kirimWa
            $result = kirimWa($waNumber, $message);

            if ($result['success']) {
                Log::info('✅ Password berhasil dikirim ke WA', [
                    'courier' => $courierName,
                    'phone' => $waNumber
                ]);
                return true;
            } else {
                Log::error('❌ Gagal kirim WA password', [
                    'courier' => $courierName,
                    'phone' => $waNumber,
                    'error' => $result['message']
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('❌ Error sendPasswordToWhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Halaman Daftar Courier
     */
    public function index()
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (!$shop) {
            return redirect()->route('seller.mypage.index')
                           ->with('error', 'Anda harus memiliki toko terlebih dahulu');
        }

        $couriers = $shop->couriers()->with('user')->get();

        return view('seller.couriers.index', compact('shop', 'couriers'))->with('title', 'Kurir Saya');
    }

    /**
     * Form Tambah Courier
     */
    public function create()
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (!$shop) {
            return redirect()->route('seller.mypage.index')
                           ->with('error', 'Anda harus memiliki toko terlebih dahulu');
        }

        return view('seller.couriers.create', compact('shop'))->with('title', 'Tambah Kurir');
    }

    /**
     * Simpan Courier Baru
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (!$shop) {
            return redirect()->route('seller.mypage.index')
                           ->with('error', 'Anda harus memiliki toko terlebih dahulu');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone|regex:/^[0-9]{10,15}$/',
        ]);

        DB::beginTransaction();
        try {
            // Generate password otomatis
            $generatedPassword = $this->generatePassword(8);

            // Buat akun User dengan role courier
            $courierUser = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make($generatedPassword),
                'role' => 'courier',
                'phone_verified_at' => now(),
                'user_verified_at' => now(),
            ]);

            // Hubungkan dengan toko
            Courier::create([
                'shop_id' => $shop->id,
                'user_id' => $courierUser->id,
                'status' => 'active',
                'created_by' => $user->id,
            ]);

            DB::commit();

            // Kirim password ke WhatsApp kurir
            $waStatus = $this->sendPasswordToWhatsApp(
                $request->name,
                $request->phone,
                $generatedPassword,
                $shop->name_store
            );

            // Pesan sukses dengan info pengiriman WA
            $successMessage = 'Courier berhasil ditambahkan!';
            if ($waStatus) {
                $successMessage .= ' Password telah dikirim ke WhatsApp kurir.';
            } else {
                $successMessage .= ' Namun gagal mengirim password ke WhatsApp. Silakan reset password manual.';
            }

            return redirect()->route('seller.couriers.index')
                           ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store courier: ' . $e->getMessage());
            return back()->withInput()
                       ->with('error', 'Gagal menambahkan courier: ' . $e->getMessage());
        }
    }

    /**
     * Form Edit Courier
     */
    public function edit($id)
    {
        $user = Auth::user();
        $shop = $user->shop;

        $courier = Courier::where('shop_id', $shop->id)
                         ->with('user')
                         ->findOrFail($id);

        return view('seller.couriers.edit', compact('shop', 'courier'));
    }

    /**
     * Update Courier
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $shop = $user->shop;

        $courier = Courier::where('shop_id', $shop->id)
                         ->with('user')
                         ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/|unique:users,phone,' . $courier->user_id,
        ]);

        DB::beginTransaction();
        try {
            $courierUser = $courier->user;
            $courierUser->name = $request->name;
            $courierUser->phone = $request->phone;
            $courierUser->save();

            DB::commit();

            return redirect()->route('seller.couriers.index')
                           ->with('success', 'Data courier berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update courier: ' . $e->getMessage());
            return back()->withInput()
                       ->with('error', 'Gagal memperbarui courier: ' . $e->getMessage());
        }
    }

    /**
     * Reset Password Courier
     */
    public function resetPassword($id)
    {
        $user = Auth::user();
        $shop = $user->shop;

        $courier = Courier::where('shop_id', $shop->id)
                         ->with('user')
                         ->findOrFail($id);

        DB::beginTransaction();
        try {
            // Generate password baru
            $newPassword = $this->generatePassword(8);

            // Update password
            $courierUser = $courier->user;
            $courierUser->password = Hash::make($newPassword);
            $courierUser->save();

            DB::commit();

            // Kirim password baru ke WhatsApp
            $waStatus = $this->sendPasswordToWhatsApp(
                $courierUser->name,
                $courierUser->phone,
                $newPassword,
                $shop->name_store
            );

            // Pesan sukses dengan info pengiriman WA
            $successMessage = 'Password berhasil direset!';
            if ($waStatus) {
                $successMessage .= ' Password baru telah dikirim ke WhatsApp kurir.';
            } else {
                $successMessage .= ' Namun gagal mengirim password ke WhatsApp. Silakan informasikan manual ke kurir.';
            }

            return redirect()->route('seller.couriers.index')
                           ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reset password courier: ' . $e->getMessage());
            return back()->with('error', 'Gagal mereset password: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Status Courier
     */
    public function toggleStatus($id)
    {
        $user = Auth::user();
        $shop = $user->shop;

        $courier = Courier::where('shop_id', $shop->id)->findOrFail($id);

        $courier->status = $courier->status === 'active' ? 'inactive' : 'active';
        $courier->save();

        $status = $courier->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('seller.couriers.index')
                       ->with('success', "Courier berhasil {$status}!");
    }

    /**
     * Hapus Courier
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $shop = $user->shop;

        $courier = Courier::where('shop_id', $shop->id)
                         ->with('user')
                         ->findOrFail($id);

        DB::beginTransaction();
        try {
            $courierUser = $courier->user;
            
            $courier->delete();
            $courierUser->delete();

            DB::commit();

            return redirect()->route('seller.couriers.index')
                           ->with('success', 'Courier berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete courier: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus courier: ' . $e->getMessage());
        }
    }
}