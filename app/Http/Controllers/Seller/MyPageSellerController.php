<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MyPageSellerController extends Controller
{
    // Halaman Index My Page
    public function index()
    {
        $user = Auth::user();
        $shop = $user->shop; // Bisa null kalau belum buka toko
        
        return view('seller.mypage.index', compact('user', 'shop'))->with('title', 'Profil Saya');
    }

    // Halaman Settings
    public function settings()
    {
        $user = Auth::user();
        $shop = $user->shop;
        
        return view('seller.mypage.settings', compact('user', 'shop'));
    }

    // Halaman Edit Akun
    public function editAccount()
    {
        $user = Auth::user();
        
        return view('seller.mypage.edit-account', compact('user'))->with('title', 'Edit Akun');
    }

    // Update Akun
    public function updateAccount(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ]);

        $user->name = $request->name;
        $user->phone = $request->phone;

        // Update avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        // Update password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak sesuai']);
            }
            
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->route('seller.mypage.index')
                         ->with('success', 'Akun berhasil diperbarui');
    }

    // ====== TOKO ======

    /**
     * Halaman Form Buka Toko (CREATE)
     */
    public function createShop()
    {
        $user = Auth::user();
        
        // Cek apakah sudah punya toko
        if ($user->shop) {
            return redirect()->route('seller.mypage.index')
                           ->with('error', 'Anda sudah memiliki toko');
        }

        // Ambil data dari seller_request untuk pre-fill form
        $sellerRequest = \App\Models\SellerRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();
        
        return view('seller.mypage.create-shop', compact('sellerRequest'))->with('title', 'Buat Toko');
    }

    /**
     * Simpan Toko Baru (STORE)
     */
    public function storeShop(Request $request)
    {
        $user = Auth::user();
        
        // Validasi user belum punya toko
        if ($user->shop) {
            return redirect()->route('seller.mypage.index')
                           ->with('error', 'Anda sudah memiliki toko');
        }

        $request->validate([
            'name_store' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address_store' => 'required|string|max:1000',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Upload logo
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // Buat toko
        Shop::create([
            'user_id' => $user->id,
            'name_store' => $request->name_store,
            'slug' => Str::slug($request->name_store),
            'description' => $request->description,
            'address_store' => $request->address_store,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'logo' => $logoPath,
            'is_active' => true, // Langsung aktif karena sudah approved jadi seller
        ]);

        return redirect()->route('seller.mypage.index')
                         ->with('success', 'Toko berhasil dibuka!');
    }

    /**
     * Halaman Edit Toko
     */
    public function editShop()
    {
        $user = Auth::user();
        $shop = $user->shop;
        
        if (!$shop) {
            return redirect()->route('seller.mypage.index')
                           ->with('error', 'Toko tidak ditemukan');
        }
        
        return view('seller.mypage.edit-shop', compact('shop'))->with('title', 'Edit Data Toko');
    }

    /**
     * Update Toko
     */
    public function updateShop(Request $request)
    {
        $user = Auth::user();
        $shop = $user->shop;
        
        if (!$shop) {
            return redirect()->route('seller.mypage.index')
                           ->with('error', 'Toko tidak ditemukan');
        }

        $request->validate([
            'name_store' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address_store' => 'required|string|max:1000',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $shop->name_store = $request->name_store;
        $shop->slug = Str::slug($request->name_store);
        $shop->description = $request->description;
        $shop->address_store = $request->address_store;
        $shop->latitude = $request->latitude;
        $shop->longitude = $request->longitude;

        // Update logo
        if ($request->hasFile('logo')) {
            if ($shop->logo) {
                Storage::disk('public')->delete($shop->logo);
            }
            
            $logoPath = $request->file('logo')->store('logos', 'public');
            $shop->logo = $logoPath;
        }

        $shop->save();

        return redirect()->route('seller.mypage.index')
                         ->with('success', 'Data toko berhasil diperbarui');
    }

 
    /**
 * Toggle Status Toko (Aktif/Nonaktif)
 */
/**
 * Toggle Status Toko - By Seller
 */
public function toggleShopStatus()
{
    $user = Auth::user();
    $shop = $user->shop;
    
    if (!$shop) {
        return redirect()->route('seller.mypage.settings')
                       ->with('error', 'Toko tidak ditemukan');
    }

    // CEK: Jika toko dinonaktifkan oleh admin, seller tidak bisa mengaktifkan
    if (!$shop->is_active && $shop->deactivated_by === 'admin') {
        return redirect()->back()
                       ->with('error', 'Toko Anda dinonaktifkan oleh admin. Silakan hubungi admin untuk mengaktifkan kembali.');
    }

    // Toggle status
    $shop->is_active = !$shop->is_active;
    
    // Jika seller menonaktifkan sendiri, tandai sebagai deactivated_by seller
    if (!$shop->is_active) {
        $shop->deactivated_by = 'seller';
    } else {
        // Jika seller mengaktifkan kembali, reset tracking
        $shop->deactivated_by = null;
    }
    
    $shop->save();

    $status = $shop->is_active ? 'diaktifkan' : 'dinonaktifkan';
    
    return redirect()->back()
                     ->with('success', "Toko berhasil {$status}");
}
}